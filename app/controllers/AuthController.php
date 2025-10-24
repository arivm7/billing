<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AuthController.php
 *  Path    : app/controllers/AuthController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;
use app\models\AuthModel;
use billing\core\MsgQueue;
use billing\core\MsgType;

use config\Auth;
use config\tables\User;
use config\SessionFields;
use billing\core\base\View;

/**
 * Description of AuthController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AuthController extends AppBaseController{



    function signupAction() {
        View::setMeta(title: "Регистрация нового пользователя");
        $this->setVariables([]);

        if (!empty($_POST[User::POST_REC])) {
            $userModel = new AuthModel();
            $postData = $_POST[User::POST_REC];
            $userModel->setAttributes($postData);

            if (!$userModel->validate($postData) || !$userModel->cleaningPhones() || !$userModel->checkUnique()) {
                $_SESSION[SessionFields::FORM_DATA] = $postData;
                $userModel->errorsToSession();
                redirect();
            }
            $userModel->attributes[User::DB_PASS_HASH] = $userModel->get_hash_pass($userModel->attributes[User::FORM_PASS]);
            if ($userModel->userSave()) {
                $userModel->successToSession();
                redirect(Auth::URI_LOGIN);
            } else {
                $_SESSION[SessionFields::ERROR] = "Что-то пошло не так: Ошибка записи в базу.";
            }
            redirect();
        }
    }



    function loginAction() {

        if (!empty($_POST[User::POST_REC])) {
            $userModel = new AuthModel();
            $postData = $_POST[User::POST_REC];
            $userModel->setAttributes($postData);
            if ($userModel->login()) {
                MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Вы успешно авторизовались'));
                redirect('/');
            } else {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Ошибка авторизации'));
                redirect();
            }
        }
        View::setMeta(
                title: __('Личный абинет') . ' :: ' . __('Авторизация'),
                descr: __('Авторизация уже зарегистрированного пользователя в системе (не абонента, получающего услугу, а именно пользователя сайта. Для сотрудников и абонентов.).'));
        $this->setVariables([]);
    }



    function logoutAction() {
        AuthModel::session_clear();
        redirect(Auth::URI_LOGIN);
    }



}