<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : PaymentsController.php
 *  Path    : app/controllers/PaymentsController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Pagination;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\User;
use DebugView;

/**
 * Description of PaymentsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class PaymentsController extends AppBaseController {

    function indexAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect();
        }
        if (!can_view([Module::MOD_MY_PAYMENTS, Module::MOD_PAYMENTS]))  {
            MsgQueue::msg(MsgType::ERROR, __('У Вас нет прав для этого модуля'));
            redirect();
        }
        $model = new AbonModel();
        $user = $_SESSION[User::SESSION_USER_REC];
        $user[Abon::TABLE] = $model->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user[User::F_ID]);

        $this->setVariables([
            'user' => $user,
        ]);

        View::setMeta(title: __('Выбор абонента для просмотра истории платежей'));
    }



    function listAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect();
        }

        if (!can_view([Module::MOD_MY_PAYMENTS, Module::MOD_PAYMENTS]))  {
            MsgQueue::msg(MsgType::ERROR, __('У Вас нет прав для этого модуля'));
            redirect();
        }
        if (empty($this->route[F_ALIAS])) {
            MsgQueue::msg(MsgType::ERROR, __('Не указан номер договора'));
            redirect();
        }

        $abon_id = (int)$this->route[F_ALIAS];

        $model = new AbonModel();
        if (!$model->validate_id(table_name: Abon::TABLE, field_id: Abon::F_ID, id_value: $abon_id)) {
            MsgQueue::msg(MsgType::ERROR, __('Номер договора указан не верно'));
            redirect();
        }

        $user = $_SESSION[User::SESSION_USER_REC];
        $user[Abon::REC] = $model->get_abon(id: $abon_id);

        if  (
                $user[User::F_ID] != $user[Abon::REC][Abon::F_USER_ID] &&
                !can_view(Module::MOD_PAYMENTS)
            )
        {
            MsgQueue::msg(MsgType::ERROR, __('Вы не имеете прав на просмотр чужих платежей'));
            redirect();
        }

        $pager = new Pagination(
                per_page: App::get_config('payments_per_page'),
                sql: $model->get_sql_payments(abon_id: $abon_id, pay_type: Pay::TYPE_MONEY));
        $payments = $pager->get_rows();

        /*
         * Заполнение вычисляемых полей
         */
        foreach ($payments as &$pay) {
            $pay[Pay::F_AGENT_TITLE]    = $model->get_user_name_short($pay[Pay::F_AGENT_ID]);
            $pay[Pay::F_TYPE_TITLE]     = Pay::TYPES[$pay[Pay::F_TYPE_ID]][Lang::code()];
            $pay[Pay::F_PPP_TITLE]      = $model->get_ppp_title($pay[Pay::F_PPP_ID]);
        }

        $this->setVariables([
            'user' => $user,
            'pager' => $pager,
            'payments' => $payments,
        ]);

        View::setMeta(title: __('История платежей'));
    }

}