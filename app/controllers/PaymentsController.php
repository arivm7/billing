<?php
/*
 *  Project : my.ri.net.ua
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
use config\SessionFields;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\PppType;
use config\tables\User;
use DebugView;

/**
 * Description of PaymentsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class PaymentsController extends AppBaseController {


    function deleteAction() {
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        if (!can_del([Module::MOD_PAYMENTS]))  {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
            redirect();
        }

        $model = new AbonModel();
        $pay_id = $this->route[F_ALIAS] ?? 0;
        $pay = $model->get_pay($pay_id);
        $abon_id = $pay[Pay::F_ABON_ID] ?? 0;

        if (empty($pay_id) || !$model->validate_id(table_name: Pay::TABLE, id_value: $pay_id, field_id: Pay::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID платежа не верен'));
            redirect();
        }

        if ($model->delete_rows_by_field(table: Pay::TABLE, value_id: $pay_id, field_id: Pay::F_ID)) {
            // !!! Доавить логирование удаления платежа !!!
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Платёж удалён'));
            $model->recalc_abon($abon_id);
            redirect();
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Ошибка удаления платежа'));
            redirect();
        }
    }


    function normalize(array &$pay) {
        $pay[Pay::F_PAY_FAKT] = floatval($pay[Pay::F_PAY_FAKT]);
        $pay[Pay::F_PAY_ACNT] = floatval($pay[Pay::F_PAY_ACNT]);
        $pay[Pay::F_DATE] = strtotime($pay[Pay::F_DATE_STR]);
        unset($pay[Pay::F_DATE_STR]);
    }


    function validate_deep(array $pay): bool {
        $valid = true;
        $model = new AbonModel();

        $is_new = empty($pay[Pay::F_ID]);

        // const F_ID = "id"; // ID платежа
        if (!empty($pay[Pay::F_ID]) && !$model->validate_id(table_name: Pay::TABLE, id_value: $pay[Pay::F_ID], field_id: Pay::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID платежа не верен'));
            $valid = false;
        }

        // const F_AGENT_ID        = "agent_id";       // ID того, кто внёс запись
        if (empty($pay[Pay::F_AGENT_ID]) || !$model->validate_id(table_name: User::TABLE, id_value: $pay[Pay::F_AGENT_ID], field_id: User::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID Агента не верен'));
            $valid = false;
        }
        
        // const F_ABON_ID         = "abon_id";        // Абонент, на которого зачисляется платеж
        if (empty($pay[Pay::F_ABON_ID]) || !$model->validate_id(table_name: Abon::TABLE, id_value: $pay[Pay::F_ABON_ID], field_id: Abon::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID Абонента не верен'));
            $valid = false;
        }

        // const F_TYPE_ID         = "pay_type_id";    // ИД Типа платежа
        if (empty($pay[Pay::F_TYPE_ID]) || !in_array($pay[Pay::F_TYPE_ID], array_keys(Pay::TYPES_TITLE))) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Тип платежа не верен'));
            $valid = false;
        }

        // const F_PPP_ID          = "pay_ppp_id";     // ППП
        if (empty($pay[Pay::F_PPP_ID]) || !$model->validate_ppp($pay[Pay::F_PPP_ID])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID ППП не верен'));
            $valid = false;
        }

        // const F_DESCRIPTION     = "description";    // Описание платежа
        if (empty(trim($pay[Pay::F_DESCRIPTION]))) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Описание платежа не может быть пустым'));
            $valid = false;
        }
        
        // const F_DATE            = "pay_date";       // Дата платежа
        if (empty($pay[Pay::F_DATE]) || !validate_timestamp($pay[Pay::F_DATE])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Дата платежа не верна'));
            $valid = false;
        }

        // const F_PAY_FAKT        = "pay_fakt";       // Фактическая сумма, пришедшая на счёт
        if (!isset($pay[Pay::F_PAY_FAKT]) || !is_numeric($pay[Pay::F_PAY_FAKT])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Фактическая сумма платежа должна быть числом [с десятичной точкой]'));
            $valid = false;
        }

        // const F_PAY_ACNT        = "pay";            // Сумма платежа, вносимая на ЛС
        if (!isset($pay[Pay::F_PAY_ACNT]) || !is_numeric($pay[Pay::F_PAY_ACNT])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Сумма платежа на ЛС должна быть числом [с десятичной точкой]'));
            $valid = false;
        }

        // const F_BANK_NO         = "pay_bank_no";    // Банковский номер операции
        if (empty(trim($pay[Pay::F_BANK_NO]))) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Номер операции не может быть пустым'));
            $valid = false;
        } elseif ($is_new && $model->get_count(Pay::TABLE, "`".Pay::F_BANK_NO."`='".$pay[Pay::F_BANK_NO]."'", Pay::F_BANK_NO) > 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Банковский номер операции дожен быть уникальным'));
            $valid = false;
        }

        return $valid;
    }



    function paySave(array $pay): never {
        $model = new AbonModel();

        /**
         * Нормализация полей платежа
         */
        $this->normalize($pay);

        /**
         * Валидация полей платежа
         */
        if (!$this->validate_deep($pay)) {
            $_SESSION[SessionFields::FORM_DATA] = $pay;
            redirect();
        }

        if (empty($pay[Pay::F_ID])) {
            /**
             * Создание новой записи платежа в базе
             */
            if (!can_add([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
            $pay[Pay::F_CREATION_DATE] = time();
            $pay[Pay::F_CREATION_UID] = App::get_user_id();
            if ($model->insert_row(Pay::TABLE, $pay)) {
                MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Платеж сохранен'));
                $model->recalc_abon($pay[Pay::F_ABON_ID]);
                redirect(url: Pay::URI_LIST .'/'. $pay[Pay::F_ABON_ID]);
            } else {
                $_SESSION[SessionFields::FORM_DATA] = $pay;
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Ошибка сохранения платежа'));
                redirect();
            }
        } else {
            /**
             * Редактирование имеющейся записи платежа в базе
             */
            if (!can_edit([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
            $pay[Pay::F_MODIFIED_DATE] = time();
            $pay[Pay::F_MODIFIED_UID] = App::get_user_id();
            if ($model->update_row_by_id(table: Pay::TABLE,  row: $pay,  field_id: Pay::F_ID)) {
                MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Платёж обновлен'));
                $model->recalc_abon($pay[Pay::F_ABON_ID]);
                redirect(url: Pay::URI_LIST.'/'.$pay[Pay::F_ABON_ID]);
            } else {
                $_SESSION[SessionFields::FORM_DATA] = $pay;
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Ошибка обновления платежа'));
                redirect();
            }
        }
    }



    public function formAction() {
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        if (isset($_POST[Pay::POST_REC])) {
            /**
             * Обработка, проверка и сохранение платежа
             */
            $this->paySave($_POST[Pay::POST_REC]);
        }

        $model = new AbonModel();
        $pay_id = $this->route[F_ALIAS] ?? 0;
        $abon_id = $_GET[Abon::F_GET_ID] ?? 0;

        if (!$pay_id && !$abon_id) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Не указан ни ID платежа, ни ID абонента'));
            redirect();
        }

        if ($pay_id) {
            /**
             * Редактирование платежа
             */
            if (!$model->validate_id(table_name: Pay::TABLE, id_value: $pay_id, field_id: Pay::F_ID)) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('ID не верен'));
                redirect();
            }
            if (!can_edit([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
            $pay = $model->get_pay($pay_id);
            $title = __('Редактирование платежа');
            $pay_type_id = $pay[Pay::F_TYPE_ID];
        } else {
            /**
             * Новый платёж
             */
            if (!can_add([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
            $pay = [
                Pay::F_ABON_ID => $abon_id,
                Pay::F_TYPE_ID  => Pay::TYPE_MONEY,
                Pay::F_PPP_ID  => PppType::TYPE_CASH_DESK,
            ];
            $title = __('Внесение нового платежа');
            $pay_type_id = Pay::TYPE_MONEY;
        }
        $ppp_list = array_column($model->get_ppp_my(active: 1), Ppp::F_TITLE, Ppp::F_ID);

        View::setMeta($title);
        $this->setVariables([
            'title'=> $title,
            'pay' => $pay,
            'pay_type_id' => $pay_type_id,
            'ppp_list' => $ppp_list,
        ]);

    }



    /**
     * Выбор абонента для просмотра истории платежей (лицевого счёта)
     */
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



    /**
     * Просмотр списка платежей по указанному абоненту (лицевому счёту)
     */
    function listAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
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

        $abon = $model->get_abon($abon_id);
        $user = $model->get_user_by_abon_id($abon_id);
        $user[Abon::REC] = $abon;

        if  (
                // авторизованный пользователь НЕ равен пользователю запрашиваемого лицевого счёта И 
                // авторизованный пользователь НЕ имеет права просматривать модуль MOD_PAYMENTS
                (($user[User::F_ID] != App::get_user_id()) && !can_view([Module::MOD_PAYMENTS])) ||
                // ИЛИ
                // авторизованные пользователь есть владелец просматриваемого лицевого счёта И
                // авторизованный пользователь НЕ имеет права на просмотр модуля MOD_MY_PAYMENTS
                (($user[User::F_ID] == App::get_user_id()) && !can_view([Module::MOD_MY_PAYMENTS]))
            )  
        {
            // отказать в просмотре
            MsgQueue::msg(MsgType::ERROR, __('У Вас нет прав для этого модуля'));
            redirect();
        }

        $pager = new Pagination(
                per_page: App::get_config('payments_per_page'),
                sql: $model->get_sql_payments(abon_id: $abon_id, pay_type: Pay::TYPE_MONEY));
        $payments = $pager->get_rows();

        /**
         * Заполнение вычисляемых полей
         */
        foreach ($payments as &$pay) {
            $model->normalize_pay($pay);
        }

        $this->setVariables([
            'user' => $user,
            'pager' => $pager,
            'payments' => $payments,
        ]);

        View::setMeta(title: __('История платежей'));
    }

}