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
use billing\core\base\Model;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Pagination;
use config\Doubles;
use config\SessionFields;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\PppType;
use config\tables\User;
use DebugView;

/**
 * Интерфейс для работы с платежами в биллинге
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class PaymentsController extends AppBaseController {



    const LOG_FILENAME = 'payments.log';



    function deleteAction() {
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            redirect('/');
        }

        if (!can_del([Module::MOD_PAYMENTS]))  {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
            redirect();
        }

        $model = new AbonModel();
        $pay_id = $this->route[F_ALIAS] ?? 0;
        $pay = $model->get_pay($pay_id);
        $abon_id = $pay[Pay::F_ABON_ID] ?? 0;

        if (empty($pay_id) || !$model->validate_id(table_name: Pay::TABLE, id_value: $pay_id, field_id: Pay::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment ID is incorrect'));
            redirect();
        }

        if ($model->delete_rows_by_field(table: Pay::TABLE, value_id: $pay_id, field_id: Pay::F_ID)) {
            // !!! Доавить логирование удаления платежа !!!
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Payment deleted'));
            MsgQueue::msg(MsgType::INFO, '<span class="text-warning">' . __('Удалённая запись платежа') . ':' . '</span>');
            MsgQueue::msg(MsgType::INFO, $pay);
            if (self::log("Удалённый платёж: \n" . print_r($pay, true), true, App::get_config('payments_log_filename'))) {
                MsgQueue::msg(MsgType::INFO, '<span class="text-warning">' . __('Лог записан') . '</span>');
            } else {
                MsgQueue::msg(MsgType::INFO, '<span class="text-danger">' . __('Не удалось записать лог') . '</span>');
                MsgQueue::msg(MsgType::INFO, '<span class="text-danger">' . __('Если удаление ошибочно, то передайте данные о удалённом платеже програмистам.') . '</span>');
            }
            $model->recalc_abon($abon_id);
            redirect(Pay::URI_LIST . '/' . $abon_id);
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Payment deletion error'));
            redirect();
        }
    }



    /**
     * Выполняет нормализацию (приведение к единому формату) массива данных платежа $pay. 
     * Она изменяет массив по ссылке, то есть напрямую модифицирует переданный аргумент.
     * Числовые данные приводит к числам, строковые данные проходят trim(), дату приеобразовывает в UNIX-timestamp.
     */
    public static function normalize(array &$pay) {
        $pay[Pay::F_PAY_FAKT] = floatval($pay[Pay::F_PAY_FAKT]);
        $pay[Pay::F_PAY_ACNT] = floatval($pay[Pay::F_PAY_ACNT]);
        if ($pay[Pay::F_ABON_ID] === "") { $pay[Pay::F_ABON_ID] = null;  }

        if (!empty($pay[Pay::F_DATE_STR])) {
            $pay[Pay::F_DATE] = strtotime($pay[Pay::F_DATE_STR]);
            unset($pay[Pay::F_DATE_STR]);
        }

        if (!empty($pay[Pay::F_REST])) {
            $pay[Pay::F_REST] = floatval($pay[Pay::F_REST]);
        } else {
            $pay[Pay::F_REST] = null;
        }

        foreach (Pay::INT_FIELDS as $field) {
            if (isset($pay[$field])) {
                $pay[$field] = intval($pay[$field]);
            }
        }

        foreach (Pay::TEXT_FIELDS as $field) {
            if (isset($pay[$field])) {
                $pay[$field] = trim($pay[$field]);
            }
        }
        // debug($pay, '1 $pay', DebugView::DUMP, die:1);
    }



    public static function validate_deep(array $pay): bool {
        $valid = true;
        $model = new AbonModel();

        $is_new = empty($pay[Pay::F_ID]);

        // const F_ID = "id"; // ID платежа
        if (!empty($pay[Pay::F_ID]) && !$model->validate_id(table_name: Pay::TABLE, id_value: $pay[Pay::F_ID], field_id: Pay::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('The payment ID is incorrect | ID платежа не верен | ID платежу не вірний'));
            $valid = false;
        }

        // const F_AGENT_ID        = "agent_id";       // ID того, кто внёс запись
        if (empty($pay[Pay::F_AGENT_ID]) || !$model->validate_id(table_name: User::TABLE, id_value: $pay[Pay::F_AGENT_ID], field_id: User::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid agent ID | ID Агента не верен | ID Агента невірний'));
            $valid = false;
        }

        /**
         * const F_ABON_ID         = "abon_id";        // Абонент, на которого зачисляется платеж
         * Должен быть или реальным абонентом или "0" (0 -- не растпределённый платёж)
         */
        $abon_id = $pay[Pay::F_ABON_ID] ?? null;
        if (
                ($abon_id === null || $abon_id === '') // реально пусто
                ||
                ($abon_id !== 0 && !$model->validate_id(
                    table_name: Abon::TABLE,
                    id_value: $abon_id,
                    field_id: Abon::F_ID
                ))
            )
        {
            MsgQueue::msg(
                MsgType::ERROR,
                __('Invalid subscriber ID | ID Абонента не верен | ID Абонента невірний')
            );
            $valid = false;
        }

        // const F_TYPE_ID         = "pay_type_id";    // ИД Типа платежа
        if (empty($pay[Pay::F_TYPE_ID]) || !in_array($pay[Pay::F_TYPE_ID], array_keys(Pay::TYPES_TITLE))) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid payment type | Тип платежа не верен | Тип платежу невірний'));
            $valid = false;
        }

        // const F_PPP_ID          = "pay_ppp_id";     // ППП
        if (empty($pay[Pay::F_PPP_ID]) || !$model->validate_ppp($pay[Pay::F_PPP_ID])) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid PPP ID | ID ППП не верен | ID ППП невірний'));
            $valid = false;
        }

        // const F_DESCRIPTION     = "description";    // Описание платежа
        if (empty(trim($pay[Pay::F_DESCRIPTION]))) {
            MsgQueue::msg(MsgType::ERROR, __('Payment description cannot be empty | Описание платежа не может быть пустым | Опис платежу не може бути порожнім'));
            $valid = false;
        }

        // const F_DATE            = "pay_date";       // Дата платежа
        if (empty($pay[Pay::F_DATE]) || !validate_timestamp($pay[Pay::F_DATE]) || $pay[Pay::F_DATE] > time()) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid payment date | Дата платежа не верна | Дата платежу невірна'));
            $valid = false;
        }

        // const F_PAY_FAKT        = "pay_fakt";       // Фактическая сумма, пришедшая на счёт
        if (!isset($pay[Pay::F_PAY_FAKT]) || !is_numeric($pay[Pay::F_PAY_FAKT])) {
            MsgQueue::msg(MsgType::ERROR, __('Actual payment amount must be a number [with decimal point] | Фактическая сумма платежа должна быть числом [с десятичной точкой] | Фактична сума платежу повинна бути числом [з десятковою крапкою]'));
            $valid = false;
        }

        // const F_PAY_ACNT        = "pay";            // Сумма платежа, вносимая на ЛС
        if (!isset($pay[Pay::F_PAY_ACNT]) || !is_numeric($pay[Pay::F_PAY_ACNT])) {
            MsgQueue::msg(MsgType::ERROR, __('Payment amount to personal account must be a number [with decimal point] | Сумма платежа на ЛС должна быть числом [с десятичной точкой] | Сума платежу на ОС повинна бути числом [з десятковою крапкою]'));
            $valid = false;
        }

        // const F_BANK_NO         = "pay_bank_no";    // Банковский номер операции
        if (empty(trim($pay[Pay::F_BANK_NO]))) {
            MsgQueue::msg(MsgType::ERROR, __('Transaction number cannot be empty | Номер операции не может быть пустым | Номер операції не може бути порожнім'));
            $valid = false;
        } elseif ($is_new && $model->get_count(Pay::TABLE, "`".Pay::F_BANK_NO."`='".$pay[Pay::F_BANK_NO]."'", Pay::F_BANK_NO) > 0) {
            MsgQueue::msg(MsgType::ERROR, __('Bank transaction number must be unique | Банковский номер операции дожен быть уникальным | Банківський номер операції повинен бути унікальним'));
            $valid = false;
        }

        return $valid;
    }



    /**
     * Проверяет нуждается ли абонент в пересчете остатков при изменении платежа.
     * На вход передаётся массив тех полей, которые будут изменены.
     * Если они входят в список полей RECALC_FIELDS, то возвращается true.
     * @param array $diff_rec -- изменённые поля для записи в базу
     * @return bool
     */
    public static function need_recalc(array $diff_rec): bool {
        foreach (Pay::RECALC_FIELDS as $field) {
            if (array_key_exists($field, $diff_rec)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Сравнивает новую запись платежа с имеющейся в базе
     * и возвращает массив только изменённых полей из списка SAVE_FIELDS
     * SAVE_FIELDS -- список полей записи платежа без служебных данных, на подобие даты создания самой записи в базе и датя изменения в базе.
     * @param array $new_rec -- новая запись платежа
     * @return array -- массив изменённых полей
     */
    public static function get_diff_fields(array $new_rec): array {
        $model = new AbonModel();
        $old_rec = $model->get_row_by_id(table_name: Pay::TABLE, id_value: $new_rec[Pay::F_ID], field_id: Pay::F_ID);
        $diff = [];
        foreach (Pay::SAVE_FIELDS as $key) {
            if (!isset($old_rec[$key]) && isset($new_rec[$key]) || $old_rec[$key] != $new_rec[$key]) {
                $diff[$key] = $new_rec[$key];
            }
        }
        return $diff;
    }



    /**
     * Ищем платежи по шаблону
     * Дата платежа -- обязательный параметр, остальные поля -- не обязательны
     *      BankNo --  Банковский номер операции -- Его нет смысла искать, поскольку он уникальный и всегда вернёт 1 или 0 платежей
     */
    public static function search_pays_sql(array $pay_rec): string
    {
        $sql = "SELECT * "
                . "FROM `".Pay::TABLE."` "
                . "WHERE "
                . "`" . Pay::F_DATE . "`='" . $pay_rec[Pay::F_DATE] . "' "    // Дата платежа. Обязательное поле для поиска
                // Сумма платежа, вносимая на ЛС
                . (isset($pay_rec[Pay::F_PAY_ACNT]) ? "AND `".Pay::F_PAY_ACNT."`='".$pay_rec[Pay::F_PAY_ACNT ]."' " : "")
                // Фактическая сумма, пришедшая на счёт
                . (isset($pay_rec[Pay::F_PAY_FAKT]) ? "AND `".Pay::F_PAY_FAKT."`='".$pay_rec[Pay::F_PAY_FAKT ]."' " : "")
                // ИД Типа платежа
                . (isset($pay_rec[Pay::F_TYPE_ID]) ? "AND `".Pay::F_TYPE_ID       ."`='".$pay_rec[Pay::F_TYPE_ID      ]."' " : "")
                // ППП
                . (isset($pay_rec[Pay::F_PPP_ID]) ? "AND `".Pay::F_PPP_ID        ."`='".$pay_rec[Pay::F_PPP_ID       ]."' " : "")
                // Описание платежа
                . (isset($pay_rec[Pay::F_DESCRIPTION]) ? "AND `".Pay::F_DESCRIPTION   ."` like '%". preg_replace('/\s+/', '%', trim($pay_rec[Pay::F_DESCRIPTION]))."%' " : "")
                ;

        // debug($sql, '$sql', die:1);
        return $sql;
    }



    public static function search_pays(array $pay_rec): array
    {
        $model = new AbonModel();
        $sql = self::search_pays_sql($pay_rec);
        $found_payments = $model->get_rows_by_sql($sql);
        return $found_payments;
    }



    public static function pay_has_exist(array $pay_rec, ?array &$found_payments = null): bool
    {
        $model = new AbonModel();
        $sql = "SELECT `id` "
                . "FROM `".Pay::TABLE."` "
                . "WHERE "
                . "`".Pay::F_ABON_ID       ."`='".$pay_rec[Pay::F_ABON_ID      ]."' "    // Абонент, на которого зачисляется платеж
                .   (
                        round($pay_rec[Pay::F_PAY_FAKT]*100) == 0
                        ?   "AND `".Pay::F_PAY_ACNT."`='".$pay_rec[Pay::F_PAY_ACNT ]."' "    // Сумма платежа, вносимая на ЛС
                        :   "AND `".Pay::F_PAY_FAKT."`='".$pay_rec[Pay::F_PAY_FAKT ]."' "    // Фактическая сумма, пришедшая на счёт
                    )
                . "AND `".Pay::F_DATE          ."`='".$pay_rec[Pay::F_DATE         ]."' "    // Дата платежа
             // . "AND `".Pay::F_REST          ."`='".$pay_rec[Pay::F_REST         ]."' "    // Остаток на счету после данной транзакции (для контроля банка)
             // . "AND `".Pay::F_BANK_NO       ."`='".$pay_rec[Pay::F_BANK_NO      ]."' "    // Банковский номер операции
                . "AND `".Pay::F_TYPE_ID       ."`='".$pay_rec[Pay::F_TYPE_ID      ]."' "    // ИД Типа платежа
                . "AND `".Pay::F_PPP_ID        ."`='".$pay_rec[Pay::F_PPP_ID       ]."' "    // ППП
             // . "AND `".Pay::F_DESCRIPTION   ."` like '%". $model->quote(preg_replace('/\s+/', '%', trim($pay_rec[Pay::F_DESCRIPTION])))."%' "    // Описание платежа
                ;

        // debug($sql, '$sql');
        if (is_null($found_payments))  {
            return $model->get_count_by_sql($sql) > 0;
        } else {
            $found_payments = $model->get_rows_by_sql($sql);
            return count($found_payments) > 0;
        }
    }



    /**
     * Вставка новой записи в биллинг
     * @param array $pay -- массив полей платежа
     * @return int|false -- id вставленной строки или false
     */
    public static function payInsert(array $data): int|false {

        if (!empty($data[Pay::F_ID])) {
            return false;
        }

        $found_payments = [];
        if (self::pay_has_exist($data, $found_payments)) {
            MsgQueue::msg(MsgType::ERROR, __('Такой платеж уже есть в базе'));
            MsgQueue::msg(MsgType::ERROR, $found_payments);
            return false;
        }

        $model = new AbonModel();

        $data[Pay::F_CREATION_DATE] = time();
        $data[Pay::F_CREATION_UID] = App::get_user_id();
        $data[Pay::F_MODIFIED_DATE] = time();
        $data[Pay::F_MODIFIED_UID] = App::get_user_id();

        $id = $model->insert_row(Pay::TABLE, $data);

        if ($id !== false ) {
            $model->price_apply_auto_ON($data[Pay::F_ABON_ID]);
            $model->recalc_abon($data[Pay::F_ABON_ID]);
        }

        return $id;

    }



    /**
     * Обновление записи в биллинге
     * @param array $pay -- массив полей платежа
     * @return bool -- true если запись обновлена, false если нет
     */
    public static function payUpdate(array $data): bool {

        if (empty($data[Pay::F_ID])) {
            return false;
        }

        $pay_diff = self::get_diff_fields($data);
        if (empty($pay_diff)) {
            MsgQueue::msg(MsgType::INFO_AUTO, __('No changes to save | Нет изменений для сохранения | Немає змін для збереження'));
            redirect();
        }
        $pay_diff[Pay::F_ID] = $data[Pay::F_ID];

        $model = new AbonModel();

        $pay_diff[Pay::F_MODIFIED_DATE] = time();
        $pay_diff[Pay::F_MODIFIED_UID] = App::get_user_id();

        if ($model->update_row_by_id(table: Pay::TABLE,  row: $pay_diff,  field_id: Pay::F_ID)) {
            if (self::need_recalc($pay_diff)) { 
                $model->recalc_abon($data[Pay::F_ABON_ID]); 
            }
            return true;
        } else {
            return false;
        }

    }



    function formPaySave(array $pay): never {
        $model = new AbonModel();

        /**
         * Нормализация полей платежа
         */
        $this::normalize($pay);

        /**
         * Валидация полей платежа
         */
        if (!$this::validate_deep($pay)) {
            $_SESSION[SessionFields::FORM_DATA] = $pay;
            redirect();
        }

        if (empty($pay[Pay::F_ID])) {
            /**
             * Создание новой записи платежа в базе
             */
            if (!can_add([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
                redirect();
            }
            
            if (self::payInsert($pay)) {
                MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Payment saved | Платеж сохранен | Платіж збережено'));
                $model->recalc_abon($pay[Pay::F_ABON_ID]);
                redirect(url: Pay::URI_LIST .'/'. $pay[Pay::F_ABON_ID]);
            } else {
                $_SESSION[SessionFields::FORM_DATA] = $pay;
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Payment save error | Ошибка сохранения платежа | Помилка збереження платежу'));
                redirect();
            }
        } else {
            /**
             * Редактирование имеющейся записи платежа в базе
             */
            if (!can_edit([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
                redirect();
            }


            if (self::payUpdate($pay)) {
                MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Payment updated | Платёж обновлен | Платіж оновлено'));
                redirect(url: Pay::URI_LIST.'/'.$pay[Pay::F_ABON_ID]);
            } else {
                $_SESSION[SessionFields::FORM_DATA] = $pay;
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Payment update error | Ошибка обновления платежа | Помилка оновлення платежу'));
                redirect();
            }
        }
    }



    public function formAction() {
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            redirect('/');
        }

        if (isset($_POST[Pay::POST_REC])) {
            /**
             * Обработка, проверка и сохранение платежа
             */
            $this->formPaySave($_POST[Pay::POST_REC]);
        }

        $model = new AbonModel();
        $pay_id = $this->route[F_ALIAS] ?? 0;
        if (!$model->validate_id(table_name: Pay::TABLE, id_value: $pay_id, field_id: Pay::F_ID)) { $pay_id = 0; }
        $abon_id = $_GET[Abon::F_GET_ID] ?? 0;
        if (!$model->validate_id(table_name: Abon::TABLE, id_value: $abon_id, field_id: Abon::F_ID)) { $abon_id = 0; }

        if (!$pay_id && !$abon_id) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Neither the payment ID for editing nor the subscriber ID for creating the payment is specified'));
            redirect();
        }

        if ($pay_id) {
            /**
             * Редактирование платежа
             */
            if (!$model->validate_id(table_name: Pay::TABLE, id_value: $pay_id, field_id: Pay::F_ID)) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment ID is incorrect'));
                redirect();
            }
            if (!can_edit([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
                redirect();
            }
            $pay = $model->get_pay($pay_id);
            $title = __('Editing a payment');
            $pay_type_id = $pay[Pay::F_TYPE_ID];
        } else {
            /**
             * Новый платёж
             */
            if (!can_add([Module::MOD_PAYMENTS]))  {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
                redirect();
            }
            $my_ppp_list = $model->get_ppp_my(1, PppType::TYPE_AGENT);
            $ppp_id = $my_ppp_list ? $my_ppp_list[array_key_first($my_ppp_list)][Ppp::F_ID] : null;
            $pay = [
                Pay::F_ABON_ID => $abon_id,
                Pay::F_TYPE_ID => Pay::TYPE_MONEY,
                Pay::F_PPP_ID  => $ppp_id,
            ];
            unset($my_ppp_list);
            unset($ppp_id);

            $title = __('New payment');
            $pay_type_id = Pay::TYPE_MONEY;
        }
        $ppp_in_pay = ($model->validate_ppp($pay[Pay::F_PPP_ID]) ? $model->get_ppp($pay[Pay::F_PPP_ID]) : null);
        $ppp_list = array_merge($model->get_ppp_my(active: 1), [$ppp_in_pay]);
        $ppp_select_list = array_column($ppp_list, Ppp::F_TITLE, Ppp::F_ID);

        View::setMeta($title);
        $this->setVariables([
            'title'=> $title,
            'pay' => $pay,
            'pay_type_id' => $pay_type_id,
            'ppp_list' => $ppp_select_list,
        ]);

    }



    /**
     * Выбор абонента для просмотра истории платежей (лицевого счёта)
     */
    function indexAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in'));
            redirect();
        }
        if (!can_view([Module::MOD_MY_PAYMENTS, Module::MOD_PAYMENTS]))  {
            MsgQueue::msg(MsgType::ERROR, __('You have no rights for this module'));
            redirect();
        }
        $model = new AbonModel();
        $user = App::get_user();
        $user[Abon::TABLE] = $model->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user[User::F_ID]);

        $this->setVariables([
            'user' => $user,
        ]);

        View::setMeta(title: __('Select subscriber to view payment history'));
    }



    /**
     * Просмотр списка платежей по указанному абоненту (лицевому счёту)
     */
    function listAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in'));
            redirect();
        }

        if (($this->route[F_ALIAS] !== '0') && empty($this->route[F_ALIAS])) {
            MsgQueue::msg(MsgType::ERROR, __('Contract number not specified'));
            redirect();
        }

        $abon_id = (int)$this->route[F_ALIAS];

        $model = new AbonModel();
        if (($abon_id != 0) && !$model->validate_id(table_name: Abon::TABLE, field_id: Abon::F_ID, id_value: $abon_id)) {
            MsgQueue::msg(MsgType::ERROR, '1111' . __('Invalid contract number'));
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
            MsgQueue::msg(MsgType::ERROR, __('You have no rights for this module'));
            redirect();
        }

        $pager = new Pagination(
                per_page: App::get_config('payments_per_page'),
                sql: $model->get_sql_payments(abon_id: $abon_id, pay_type: (can_use(Module::MOD_PAYMENTS) ? null : Pay::TYPE_MONEY)));
        $payments = $pager->get_rows();

        /**
         * Заполнение вычисляемых полей
         */
        foreach ($payments as &$pay) {
            $model->pay_update_fields($pay);
        }

        $this->setVariables([
            'user' => $user,
            'pager' => $pager,
            'payments' => $payments,
        ]);

        View::setMeta(title: __('Payment history'));
    }



    function searchAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in'));
            redirect();
        }

        if (!can_use(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR, __('You have no rights for this module'));
            redirect();
        }
        
        $search_rec = [];

        // Дата платежа. Обязательное поле для поиска
        if (isset($_GET[Pay::F_DATE])) {
            $search_rec[Pay::F_DATE] = $_GET[Pay::F_DATE];
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Не указана дата платежа. Это обязательный параметр'));
            redirect();
        }
        // Сумма платежа, вносимая на ЛС
        if (isset($_GET[Pay::F_PAY_ACNT])) { $search_rec[Pay::F_PAY_ACNT] = $_GET[Pay::F_PAY_ACNT]; }
        // Фактическая сумма, пришедшая на счёт
        if (isset($_GET[Pay::F_PAY_FAKT])) { $search_rec[Pay::F_PAY_FAKT] = $_GET[Pay::F_PAY_FAKT]; }
        // ИД Типа платежа
        if (isset($_GET[Pay::F_TYPE_ID])) { $search_rec[Pay::F_TYPE_ID] = $_GET[Pay::F_TYPE_ID]; }
        // ППП
        if (isset($_GET[Pay::F_PPP_ID])) { $search_rec[Pay::F_PPP_ID] = $_GET[Pay::F_PPP_ID]; }
        // Описание платежа
        if (isset($_GET[Pay::F_DESCRIPTION])) { $search_rec[Pay::F_DESCRIPTION] = $_GET[Pay::F_DESCRIPTION]; }

        $pager = new Pagination(
                per_page: App::get_config('payments_per_page'),
                sql: self::search_pays_sql($search_rec));
        $payments = $pager->get_rows();

        /**
         * Заполнение вычисляемых полей
         */
        $model = new AbonModel();
        foreach ($payments as &$pay) {
            $model->pay_update_fields($pay);
        }

        $this->setVariables([
            'search_rec' => $search_rec,
            'pager' => $pager,
            'payments' => $payments,
        ]);

        View::setMeta(title: __('Поиск платежей'));
    }



    /**
     * Поиск задвоенных платежей
     */
    function doublesAction() {

        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in'));
            redirect();
        }

        if (!can_use(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR, __('You have no rights for this module'));
            redirect();
        }
        

        $model = new AbonModel();


        /**
         * Начальная дата поиска дубликатов платежей
         */
        $filter[Doubles::F_DATE1_TS] = 
                (isset($_POST[Doubles::F_DATE1_STR])
                    ? strtotime(h($_POST[Doubles::F_DATE1_STR]))
                    : time() - App::get_config('duplicates_search_time'));

        // (p1.abon_id = p2.abon_id)
        $filter[Doubles::F_BY_ABON_ID] = 
                (isset($_POST[Doubles::F_BY_ABON_ID])
                    ? (($_POST[Doubles::F_BY_ABON_ID] == 1) ? 1 : 0)
                    : Doubles::BY_ABON_ID_DEFAULT);

        // (p1.pay_type_id = p2.pay_type_id)
        $filter[Doubles::F_BY_PAY_TYPE_ID] = 
                (isset($_POST[Doubles::F_BY_PAY_TYPE_ID])
                    ? (($_POST[Doubles::F_BY_PAY_TYPE_ID] == 1) ? 1 : 0)
                    : Doubles::BY_PAY_TYPE_ID_DEFAULT);

        // (p1.pay_ppp_id = p2.pay_ppp_id)
        $filter[Doubles::F_BY_PAY_PPP_ID] = 
                (isset($_POST[Doubles::F_BY_PAY_PPP_ID])
                    ? (($_POST[Doubles::F_BY_PAY_PPP_ID] == 1) ? 1 : 0)
                    : Doubles::BY_PAY_PPP_ID_DEFAULT);

        // (p1.pay_bank_no = p2.pay_bank_no)
        $filter[Doubles::F_BY_PAY_BANK_NO] = 
                (isset($_POST[Doubles::F_BY_PAY_BANK_NO])
                    ? (($_POST[Doubles::F_BY_PAY_BANK_NO] == 1) ? 1 : 0)
                    : Doubles::BY_PAY_BANK_NO_DEFAULT);

        // (p1.pay_fakt = p2.pay_fakt)
        $filter[Doubles::F_BY_PAY_FAKT] = 
                (isset($_POST[Doubles::F_BY_PAY_FAKT])
                    ? (($_POST[Doubles::F_BY_PAY_FAKT] == 1) ? 1 : 0)
                    : Doubles::BY_PAY_FAKT_DEFAULT);

        // (p1.pay = p2.pay)
        $filter[Doubles::F_BY_PAY_ACNT] = 
                (isset($_POST[Doubles::F_BY_PAY_ACNT])
                    ? (($_POST[Doubles::F_BY_PAY_ACNT] == 1) ? 1 : 0)
                    : Doubles::BY_PAY_ACNT_DEFAULT);

        // (p1.pay > 0)
        $filter[Doubles::F_BY_PAY_ACNT_CREDIT] = 
                (isset($_POST[Doubles::F_BY_PAY_ACNT_CREDIT])
                    ? (($_POST[Doubles::F_BY_PAY_ACNT_CREDIT] == 1) ? 1 : 0)
                    : Doubles::BY_PAY_ACNT_CREDIT_DEFAULT);

        $filter[Doubles::F_BY_PAY_TIME_LVL] =
                (isset($_POST[Doubles::F_BY_PAY_TIME_LVL]) 
                    ? $_POST[Doubles::F_BY_PAY_TIME_LVL] 
                    : Doubles::BY_PAY_TIME_LVL_DEFAULT);

        // (p1.description = p2.description)
        $filter[Doubles::F_BY_DESCR] = 
                (isset($_POST[Doubles::F_BY_DESCR])
                    ? (($_POST[Doubles::F_BY_DESCR] == 'on') ? 1 : 0)
                    : Doubles::BY_DESCR_DEFAULT);

        /**
         * Список ППП по которым искать платежи, 
         */
        $filter[Doubles::F_PPP_LIST] = $model->get_rows_by_sql(Doubles::get_ppp_id_list_sql());

        /**
         * Список ID ППП для поиска платежей
         */
        $filter[Doubles::F_PPP_INCLUDE] = [];

        /**
         * Собираем 
         * @var array $filter[Doubles::F_PPP_LIST]
         * @var array $filter[Doubles::F_PPP_INCLUDE]
         */
        foreach ($filter[Doubles::F_PPP_LIST] as &$ppp) {
            $ppp[Doubles::F_PPP_INCLUDE] = 
                    (isset($_POST[Doubles::F_PPP_INCLUDE])
                        ? ((isset($_POST[Doubles::F_PPP_INCLUDE][$ppp[Ppp::F_ID]]) && ($_POST[Doubles::F_PPP_INCLUDE][$ppp[Ppp::F_ID]] == 1)) 
                            ? 1 
                            : 0)
                        : Doubles::BY_PPP_INCLUDE_AUTOSELECT);
            if ($ppp[Doubles::F_PPP_INCLUDE]) {
                $filter[Doubles::F_PPP_INCLUDE][] = $ppp[Ppp::F_ID];
            }
        }

        if (empty($filter[Doubles::F_PPP_INCLUDE])) {
            MsgQueue::msg(MsgType::ERROR, __('Нужно выбрать один или более ППП'));
            redirect();
        }


        $errors = [

            'date' => [
                'title' => __('Ошибки даты платежа'),
                'sql' => "SELECT * FROM `payments` WHERE "
                            // старые записи
                            . "(`pay_date` < UNIX_TIMESTAMP('".App::get_config('payment_error_before_date')."')) "
                            . "AND ( "
                                . Pay::F_PPP_ID." IN (".implode(',', $filter[Doubles::F_PPP_INCLUDE]).") "
                            . ")",
                'pager' => __('Дата не корректна'),
            ],

            'fields' => [
                'title' => __('Ошибки пустых полей'),
                'sql' => "SELECT * FROM `payments` WHERE \n"
                            . "(".Pay::F_PPP_ID." IN (".implode(',', $filter[Doubles::F_PPP_INCLUDE]).")) "
                            . "AND ( "
                                // пустые поля
                                .    "(`pay_bank_no` IS NULL OR `pay_bank_no` = '') \n"
                                . "OR (`abon_id` IS NULL) \n"
                                . "OR (`pay_type_id` IS NULL OR `pay_type_id` = 0) \n"
                                . "OR (`pay_ppp_id` IS NULL OR `pay_ppp_id` = 0) \n"
                            . ")",
            ],

            'incorrect' => [
                'title' => __('Не корректные суммы платежей'),
                'sql' => "SELECT * FROM `payments` WHERE \n"
                            . "(".Pay::F_PPP_ID." IN (".implode(',', $filter[Doubles::F_PPP_INCLUDE]).")) "
                            . "AND ( "
                                // некорректные суммы
                                . "(`pay_fakt` > 0 AND `pay` = 0) \n"
                            . ")",
            ],

            'empty' => [
                'title' => __('Нулевые платежи'),
                'sql' => "SELECT * FROM `payments` WHERE \n"
                            . "(".Pay::F_PPP_ID." IN (".implode(',', $filter[Doubles::F_PPP_INCLUDE]).")) "
                            . "AND ( "
                                // полностью пустая запись
                                . "(`pay_fakt` = 0 AND `pay` = 0) \n"
                            . ")",
            ],
        ];

        foreach ($errors as $type => &$rec) {
            // debug($type, '$type');
            // debug($rec, '$rec');
            $rec['pager'] = new Pagination(
                    per_page: App::get_config('payment_error_per_page'),
                    sql: $rec['sql'],
                    f_get_page: $type . '_page',
                    anchor_name: $type
                );
            $rec['count'] = $rec['pager']->count_rows;
            $rec['payments'] = $rec['pager']->get_rows();
            foreach ($rec['payments'] as &$pay) { $model->pay_update_fields($pay); }
        }



        /**
         * Массив записей-дубликатов
         */
        $doubles = [];

        if (isset($_POST[Doubles::F_CMD_DO]) &&  $_POST[Doubles::F_CMD_DO] == 1) {
            /**
             * Форма отправлена
             * Ищем дубликаиы
             */

            $sql = 
                "WITH base AS ( \n"
                    . "SELECT \n"
                    . "    ".Pay::F_ID.", \n"
                    . "    ".Pay::F_DATE.", \n"
                    . "    ".Pay::F_ABON_ID.", \n"
                    . "    ".Pay::F_PAY_ACNT.", \n"
                    . "    ".Pay::F_PAY_FAKT.", \n"
                    . "    ".Pay::F_BANK_NO.", \n"
                    . "    ".Pay::F_AGENT_ID.", \n"
                    . "    ".Pay::F_TYPE_ID.", \n"
                    . "    ".Pay::F_PPP_ID.", \n"
                    . "    ".Pay::F_DESCRIPTION.", \n"
                    . "    "."FLOOR(".Pay::F_DATE." / ".Doubles::PAY_TIME_LVLS_TS[$filter[Doubles::F_BY_PAY_TIME_LVL]].") AS ".Doubles::F_DATE_BUCKET." \n"
                    . "FROM ".Pay::TABLE." \n"
                    . "WHERE \n"
                    . "    ".Pay::F_DATE." > ".$filter[Doubles::F_DATE1_TS]." \n"
                    . "    "."AND (".Pay::F_PPP_ID." IN (".implode(',', $filter[Doubles::F_PPP_INCLUDE]).")) \n"
                    . "    ".($filter[Doubles::F_BY_PAY_ACNT_CREDIT] ? "AND (".Pay::F_PAY_ACNT." > 0) \n" : "")
                . ") \n\n"

                . "SELECT \n"
                . "    p1.".Pay::F_ID."          AS ".Doubles::F_P1_ID.", \n"
                . "    p2.".Pay::F_ID."          AS ".Doubles::F_P2_ID." \n"
                . "FROM base p1 \n"
                . "JOIN base p2 \n"
                    /**
                     * p1.id > p2.id означает: 
                     * p1 -- кандидат на дубликат, p2 -- первичный платёж
                     */
                    . "    ON (p1.".Pay::F_ID." > p2.".Pay::F_ID.") \n"
                    . ($filter[Doubles::F_BY_ABON_ID]     ? "    AND (p1.".Pay::F_ABON_ID."  = p2.".Pay::F_ABON_ID.") \n"  : "")
                    . ($filter[Doubles::F_BY_PAY_TYPE_ID] ? "    AND (p1.".Pay::F_TYPE_ID."  = p2.".Pay::F_TYPE_ID.") \n"  : "")
                    . ($filter[Doubles::F_BY_PAY_PPP_ID]  ? "    AND (p1.".Pay::F_PPP_ID."   = p2.".Pay::F_PPP_ID.") \n"   : "")
                    . ($filter[Doubles::F_BY_PAY_BANK_NO] ? "    AND (p1.".Pay::F_BANK_NO."  = p2.".Pay::F_BANK_NO.") \n"  : "")
                    . ($filter[Doubles::F_BY_PAY_FAKT]    ? "    AND (p1.".Pay::F_PAY_FAKT." = p2.".Pay::F_PAY_FAKT.") \n" : "")
                    . ($filter[Doubles::F_BY_PAY_ACNT]    ? "    AND (p1.".Pay::F_PAY_ACNT." = p2.".Pay::F_PAY_ACNT.") \n" : "")
                    . "    AND (p1.".Doubles::F_DATE_BUCKET." = p2.".Doubles::F_DATE_BUCKET.") \n\n"

                . "ORDER BY p1.pay_date DESC ";

            // debug($sql, '$sql');

            $pager = new Pagination(
                    per_page: App::get_config('payments_per_page'),
                    sql: $sql
                );
            $rows = $pager->get_rows();

            foreach ($rows as $row) { 
                $doubles[] = [
                        1 => $model->get_pay($row[Doubles::F_P1_ID]), 
                        2 => $model->get_pay($row[Doubles::F_P2_ID]), 
                    ];
            }

            // debug($doubles, '$doubles');

        }

        // if ($by_descr) {
        //             // (
        //             //     ? "AND ( "
        //             //             . "(LOWER(REPLACE(REPLACE(REPLACE(TRIM(p1.description), ' ', ''), '\t', ''), '\n', '')) = LOWER(REPLACE(REPLACE(REPLACE(TRIM(p2.description), ' ', ''), '\t', ''), '\n', ''))) "
        //             //             . "OR (p1.description LIKE CONCAT('%', REPLACE(TRIM(p2.description), ' ', '%'), '%')) "
        //             //             . "OR (p2.description LIKE CONCAT('%', REPLACE(TRIM(p1.description), ' ', '%'), '%')) "
        //             //         . ") "
        //             //     : "")
        // }

        /**
         * Заполнение вычисляемых полей
         */
        foreach ($doubles as &$rows) {
            foreach ($rows as &$pay) {
                $model->pay_update_fields($pay);
            }
        }

        $title = __("Поиск задвоенных платежей");

        $this->setVariables([
            'title'   => $title,
            'filter'  => $filter,
            'doubles' => $doubles,
            'pager'   => $pager ?? null,
            'errors'    => $errors,
        ]);

        View::setMeta(title: $title);

    }




}