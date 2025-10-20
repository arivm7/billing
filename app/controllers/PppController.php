<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : PppController.php
 *  Path    : app/controllers/PppController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\models\AbonModel;
use app\models\AppBaseModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Firm;
use config\tables\Module;
use config\tables\Ppp;
use config\tables\PppType;
use config\tables\User;

/**
 * Description of PppController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class PppController extends AppBaseController{


    function update_ppp($ppp_id, $form_data){
        $this->layout = "";

        if ($ppp_id != $form_data[Ppp::F_ID]) {
            MsgQueue::msg(MsgType::ERROR, __('ppp/form: Несоответствие ID ППП'));
            redirect();
        }

        $model = new AppBaseModel();
        if (!$model->validate_id(table_name: Ppp::TABLE, field_id: Ppp::F_ID, id_value: $ppp_id)) {
            MsgQueue::msg(MsgType::ERROR, __('Не верный ID ППП'));
            redirect();
        }


        /**
         * Обработка булевых флагов: если поля отсутствуют в POST, добавить со значением 0.
         * Также нормализуем возможные значения (on, '1', true => 1; все остальное => 0)
         */
        foreach (Ppp::FLAGS as $flagField) {
            if (!array_key_exists($flagField, $form_data)) {
                $form_data[$flagField] = 0;
            } else {
                // нормализуем значение в 0/1
                $val = $form_data[$flagField];
                $form_data[$flagField] = ($val === '1' || $val === 1 || $val === 'on' || $val === true) ? 1 : 0;
            }
        }        

        /**
         * Обработка URL полей: привести к корректному виду или очистить
         */
        foreach (Ppp::URLS as $urlField) {
            if (isset($form_data[$urlField]) && !empty($form_data[$urlField])) {
                $url = trim($form_data[$urlField]);
                // Найти все JSON-подстроки в фигурных скобках
                $url = preg_replace_callback(
                    '/\{.*?\}/', 
                    function($matches) {
                        return urlencode($matches[0]);
                    },
                    $url
                );
                // проверить корректность URL
                if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                    MsgQueue::msg(MsgType::ERROR, __('Некорректный URL в поле') . ' ' . $urlField);
                    redirect(Ppp::URI_EDIT . '/' . $ppp_id);
                }
                // сохранить очищенный URL
                $form_data[$urlField] = $url;
            } else {
                // очистить поле, если пусто
                $form_data[$urlField] = '';
            }
        }

        // debug($form_data, 'Обновление ППП: подготовленные данные', die: 1);

        /**
         * Обновляем данные ППП
         */
        if ($model->update_row_by_id(table: Ppp::TABLE, row: $form_data, field_id: Ppp::F_ID)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Данные ППП успешно обновлены'));
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Ошибка обновления данных ППП'));
            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
        }
        redirect(Ppp::URI_EDIT . '/' . $ppp_id);
    }



    function indexAction(){

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        if (!can_use([Module::MOD_PPP]))  {
            MsgQueue::msg(MsgType::ERROR, __('У Вас нет прав для этого модуля'));
            redirect();
        }

        isset($_GET['active']) ? $active = (int)$_GET['active'] : $active = null;
        isset($_GET['type_id']) ? $type_id = (int)$_GET['type_id'] : $type_id = null;
        isset($_GET['abon_payments']) ? $abon_payments = (int)$_GET['abon_payments'] : $abon_payments = null;

        $model = new AbonModel();
        $ppp_list = $model->get_ppp_my($active, $type_id, $abon_payments);

        View::setMeta(__('Пункты приёма платежей | Payment acceptance points | Пункти прийому платежів'));
        $this->setVariables([
            'ppp_list'=> $ppp_list,
        ]);

    }


    function editAction(){
        
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        if (!can_use([Module::MOD_PPP]))  {
            MsgQueue::msg(MsgType::ERROR, __('У Вас нет прав для этого модуля'));
            redirect();
        }

        if (empty($this->route[F_ALIAS])) {
            MsgQueue::msg(MsgType::ERROR, __('ppp/form: Не указан ID'));
            redirect();
        }

        $ppp_id = (int)$this->route[F_ALIAS];

        $model = new AppBaseModel();
        if (!$model->validate_id(table_name: Ppp::TABLE, field_id: Ppp::F_ID, id_value: $ppp_id)) {
            MsgQueue::msg(MsgType::ERROR, __('Не верный ID ППП'));
            redirect();
        }

        if (isset($_POST[Ppp::POST_REC]) && is_array($_POST[Ppp::POST_REC])) {
            /**
             * Пришли данные формы
             */
            $form_data = $_POST[Ppp::POST_REC];
            $this->update_ppp(ppp_id: $ppp_id, form_data: $form_data);
        }

        $ppp_item   = $model->get_row_by_id(table_name: Ppp::TABLE, field_id: Ppp::F_ID, id_value: $ppp_id);
        $ppp_types  = $model->get_rows_by_where(table: PppType::TABLE, order_by: PppType::F_UK_TITLE . ' ASC');
        $firms      = $model->get_rows_by_where(table: Firm::TABLE, where: "(`".Firm::F_HAS_ACTIVE."`=1) AND (`".Firm::F_HAS_AGENT."`=1)", order_by: Firm::F_NAME_TITLE . ' ASC');
        $owner      = $model->get_row_by_id(table_name: User::TABLE,  field_id: User::F_ID, id_value: $ppp_item[Ppp::F_OWNER_ID]);

        View::setMeta(__('Редактирование пункта приёма платежей | Edit payment acceptance point | Редагування пункту прийому платежів'));
        $this->setVariables([
            'ppp_item'=> $ppp_item,
            'ppp_types'=> $ppp_types,
            'firms'=> $firms,
            'owner'=> $owner,
        ]);


    }


}