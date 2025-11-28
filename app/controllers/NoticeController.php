<?php
/**
 *  Project : my.ri.net.ua
 *  File    : NoticeController.php
 *  Path    : app/controllers/NoticeController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Nov 2025 22:00:59
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of NoticeController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace App\Controllers;

use app\controllers\AppBaseController;
use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Notice;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Notify;
use config\tables\PA;
use config\tables\Ppp;
use config\tables\TP;
use config\tables\User;

class NoticeController extends AppBaseController
{



    public function indexAction()
    {
        redirect('/');
    }



    public function infoAction()
    {

        /**
         * Проверка наличия авторизации
         */
        if (!App::isAuth()) {   
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        /**
         * Проверка прав
         */
        if (!can_view(Module::MOD_NOTICE)) {   
            MsgQueue::msg(MsgType::ERROR, __('Нет прав'));
            redirect();
        }

        $model = new AbonModel();

        $abon_id = $this->route[F_ALIAS] ?? 0;
        if (!$model->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('ID абонента не верен'));
            redirect();
        }

        $abon = $model->get_abon($abon_id);
        $user = $model->get_user($abon[Abon::F_USER_ID]);

        /**
         * Регистрация сообщений
         */
        if (isset($_POST[Notify::POST_REC]) && is_array($_POST[Notify::POST_REC])) {
            $post_rec = $_POST[Notify::POST_REC];
            if ($abon_id !=  $_POST[Notify::POST_REC][Notify::F_ABON_ID]) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Не верные данные')); // !!! нужно логировать. Возможно вмешательство
                redirect();
            }
            foreach ($post_rec['msg'] as $notice_rec) {
                if (empty($notice_rec['register'])) { continue; }
                $notice = [
                    Notify::F_ABON_ID => $abon_id,
                    Notify::F_TYPE_ID => Notify::TYPE_SMS,
                    Notify::F_PHONENUMBER => $user[User::F_PHONE_MAIN],
                    Notify::F_METHOD => Notify::METHOD_KDE_CONNECT,
                    Notify::F_DATE => time(),
                    Notify::F_TEXT => $notice_rec['text'],
                ];
                if ($model->insert_row(Notify::TABLE, $notice)) {
                    MsgQueue::msg(MsgType::SUCCESS, __('Сообщение зарегистрировано'));
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Ошибка регистрации сообщения') . ': ' . $model->errorInfo());
                }
                redirect();
            }
        }


        $rest = $model->get_abon_rest($abon_id);
        $pa_list = $model->get_pa_active_or_last($abon_id); // нужна проверка: может быть пустым
        if (!empty($pa_list)) {
            $ppp_list = $model->get_ppp_for_pay(from_pa_list: $pa_list);
        } else {
            $ppp_list = [];
        }
        // debug($rest, '$rest');
        // debug($ppp_list, '$ppp_list');

        $title = __('Информация для отправки абоненту') . ' ' . $abon[Abon::F_ID];
        View::setMeta($title);
        $this->setVariables([
            'title' => $title,
            'abon' => $abon,
            'user' => $user,
            'rest' => $rest,
            'pa_list' => $pa_list,
            'ppp_list' => $ppp_list,
        ]);
    }



}