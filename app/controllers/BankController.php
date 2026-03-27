<?php
/**
 *  Project : my.ri.net.ua
 *  File    : BankController.php
 *  Path    : app/controllers/BankController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:12:45
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of BankController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Module;
use config\Bank;
use config\MonoCard;
use config\P24acc;
use config\SessionFields;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\PppType;

class BankController extends AppBaseController
{

    /**
     * Статусы выполнения внесений в биллинг
     * @var int
     */
    const DO_STATUS_OK = 1;
    const DO_STATUS_ERROR = -1;
    const DO_STATUS_NA = 0;



    function monocardUpdate(array &$data) {

        $model = new AbonModel();
        foreach ($data as $index => $rec) {
            if (($rec[Pay::F_POST_SAVE] ?? 0) == 1) {
                unset($rec[Pay::F_POST_SAVE]);
                /**
                 * Нормализация полей платежа
                 */
                PaymentsController::normalize($rec);

                /**
                 * Валидация полей платежа
                 */
                if (PaymentsController::validate_deep($rec)) {
                    if (PaymentsController::payInsert($rec)) {
                        MsgQueue::msg(MsgType::SUCCESS, __('Платеж внесён') . ': ' 
                                . $rec[Pay::F_ABON_ID] . ' | ' 
                                . $rec[Pay::F_PAY_FAKT] . ' грн.' . ' | ' 
                                . date('Y-m-d H:i:s', $rec[Pay::F_DATE]) . ' | ' 
                                . h($rec[Pay::F_DESCRIPTION] ));
                        $model->recalc_abon($rec[Pay::F_ABON_ID], false);
                    }
                }
            }
        }

    }



    function monocardAction() {

        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');


        if (!App::isAuth()) { 
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            redirect('/'); 
        }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            redirect();
        }



        /**
         * -------------------------------------------------------------------------------
         * Внесение данных в базу
         */
        if(isset($_POST[Bank::POST_REC]) && is_array($_POST[Bank::POST_REC])) {
            $post_rec = $_POST[Bank::POST_REC];
            $this->monocardUpdate($post_rec);
            redirect();
        }
        /**
         * Конец блока внесение данных в базу
         * -------------------------------------------------------------------------------
         */



        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !$model->validate_ppp((int)$this->route[F_ALIAS])
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point ID is incorrect | ID пункта приёма платежей не верен | ID пункту прийому платежів не вірний'));
            redirect();
        }

        $ppp = $model->get_ppp((int)$this->route[F_ALIAS]);

        if (!$model->is_ppp_my($ppp[Ppp::F_ID])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point is not mine | Пункт приема платежей не мой | Пункт прийому платежів не мій'));
            redirect();
        }

        if (!is_supported_api($ppp, Bank::API_TYPE_MONO_CARD)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point does not support this API | Пункт приема платежей не поддерживает этот API | Пункт прийому платежів не підтримує цей API'));
            redirect();
        }

        // MsgQueue::msg(MsgType::INFO, "==== ". __('Getting data | Получение данных | Отримання даних') ." ====");


        // $ppp_type_id        = PPP_TYPE_CARD; // 2 Карта
        // $pay_type_id        = PAY_TYPE_CASH; // 1 - Денежное пополнение ЛС | 2 - Корректировка ЛС | 3 - Начисление за услугу

        /**
         * Чтение шаблонов распознавания абонентов
         */
        $templates   = $model->get_abons_templates($ppp[Ppp::F_ID]);
        MsgQueue::msg(MsgType::INFO, __('Number of entries in the template table | Количество записей в таблице шаблонов | Кількість записів у таблиці шаблонів') . ": " . count($templates));


        $last_pay           = $model->get_last_pay_on_ppp($ppp[Ppp::F_ID]);
        // $followId           = ($_GET['followId']  ?? null);
        // $limit              = ($_GET['limit']     ?? App::get_config('bank_limit_per_page'));
        $date1_str          = ($_GET[Bank::F_GET_DATE_START] ?? date('Y-m-d', time()-App::get_config('bank_date_interval')));
        $date2_str          = ($_GET[Bank::F_GET_DATE_END]   ?? date('Y-m-d'));
        MsgQueue::msg(MsgType::INFO, __("Sampling dates | Даты выборки | Дати вибірки") . ": [".$date1_str."] -- [".$date2_str."]");

        $card_token         = $ppp[Ppp::F_API_ID];   // что-то такое: 'uBrjknkdhfasdfLXz44583xHOHfWgm';
        $api_url            = $ppp[Ppp::F_API_URL];  // что-то такое: 'https://api.monobank.ua/personal/statement/';


        if (($date1_ts = strtotime($date1_str)) === false) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect start date format for the period | Не верный формат даты начала периода | Не вірний формат дати початку періоду'));
            redirect(); // Bank::URI_INDEX
        }
        if (($date2_ts = strtotime($date2_str)) === false) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect end date format for the period | Не верный формат даты окончания периода | Не вірний формат дати закінчення періоду'));
            redirect(); // Bank::URI_INDEX
        }

        // MsgQueue::msg(MsgType::INFO, "==== "  . __('Getting information about the card | Получение информации о карте | Отримання інформації про карту') . " ====");
        $cards_info = MonoCard::get_card_info($card_token);
        if (isset($cards_info['client']['errorDescription'])) {
            /*
            * ERRORS:
            * [errorDescription] => Too many requests
            */
            MsgQueue::msg(MsgType::ERROR, __('Error receiving card information | Ошибка получения информации о карте | Помилка отримання інформації про карту') . ': ' . $cards_info['client']['errorDescription']);
            redirect(); // Bank::URI_INDEX
        }
        // echo "cards_info[client]:<pre>"; print_r($cards_info['client']); echo "</pre><hr>";
        // echo "cards_info[connect]:<pre>"; print_r( $cards_info['connect']); echo "</pre><hr>";

        // MsgQueue::msg(MsgType::INFO, "==== ".__("Getting a bank statement on a card | Получение банковской выписки по карте | Отримання банківської виписки по карті")." ====");
        $data = MonoCard::get_statements(
            $card_token,
            $date1_ts,
            $date2_ts,
            $api_url
        );
        // echo "info:<pre>"; print_r($data['connect']); echo "</pre><hr>";
        // echo "res(decode):<pre>"; print_r($data['statements']); echo "</pre><hr>";

        // MsgQueue::msg(MsgType::INFO, "==== ".__("Getting data from billing | Получение данных из биллинга | Отримання даних з білінгу")." ====");

        foreach ($data[Bank::F_STATEMENTS] as &$statement) {
            /**
             * платежи, внесённые в биллинг с указанным кодом транзакции
             */
            $statement[Bank::F_FOUND_REC] = Bank::search_payments_on_billing($statement, MonoCard::TEXT_FIELDS, $ppp[Ppp::F_ID], $templates); 
            // debug($statement[Bank::F_FOUND_REC], '$statement[Bank::F_FOUND_REC]');
            
            /**
             * Запись для внесения в базу и сравнения с уже внесёнными данными
             */
            $comission = $statement[MonoCard::F_COMMISSION_RATE];
            $pay_rec[Pay::F_PAY_FAKT]    = floatval(get_numeric_part($statement[Bank::F_MAP_MONOCARD[Pay::F_PAY_FAKT]]));
            $pay_rec[Pay::F_PAY_ACNT]    = floatval(get_numeric_part($statement[Bank::F_MAP_MONOCARD[Pay::F_PAY_ACNT]]) + $comission);
            $pay_rec[Pay::F_DATE]        = $statement[Bank::F_MAP_MONOCARD[Pay::F_DATE]];
            $pay_rec[Pay::F_BANK_NO]     = $statement[Bank::F_MAP_MONOCARD[Pay::F_BANK_NO]];
            $pay_rec[Pay::F_TYPE_ID]     = Pay::TYPE_MONEY;
            $pay_rec[Pay::F_AGENT_ID]    = App::get_user_id();
            $pay_rec[Pay::F_PPP_ID]      = $ppp[Ppp::F_ID];
            $pay_rec[Pay::F_DESCRIPTION] = implode(
                ' | ',
                array_filter(
                    array_map(
                        function ($key) use ($statement) {
                            return trim((string)($statement[$key] ?? ''));
                        },
                        Bank::F_MAP_MONOCARD[Pay::F_DESCRIPTION]
                    ),
                    function ($value) {
                        return $value !== '';
                    }
                )
            );

            $statement[Bank::F_PAY_REC] = $pay_rec;
        }
        // MsgQueue::msg(MsgType::INFO, "==== ". __("All data is collected | Все данные собраны | Всі дані зібрані") ." ====");

        View::setMeta(__('Payments to a Monobank card | Платежи на карту Монобанка | Платежі на карту Монобанку'));

        $this->setVariables([
            'cards_info'    => $cards_info, // {client: array, connect: array}
            'data'          => $data,       // {connect: array, statements: array}
            'date1'         => $date1_ts,   // int, timestamp, начало периода выборки
            'date2'         => $date2_ts,   // int, timestamp, конец периода выборки
            'date_last_pay' => $last_pay[Pay::F_DATE], // int, timestamp, Дата последнего зарегистрированного платежа на ППП
            'ppp'           => $ppp,        // array ППП
        ]);

    }



    function p24accUpdate(array &$data) {

        debug($data, '$data', die:1);

        $model = new AbonModel();

        /**
         * Выполнение действий:
         * привязка платежей к абонентам и добавление шаблонов
         */

        $t = array();
        foreach ($data as &$pay) {

            if  (
                    !isset($pay['to_edit']) && 
                    !isset($pay['to_billing']) && 
                    !isset($pay['template_add']) 
                ) 
            { continue; }
            
            if  ( isset($pay['to_edit']) && ($pay['to_edit'] == 1) ) { 
                if (Bank::pay_add(
                            $pay['abon_id'],        //int     Абонент, на которого зачисляется поалёж
                            $pay['pay_fakt'],       //float   Фактическая сумма пришедшая на р/с
                            $pay['pay'],            //float   Сумма платежа вносимая на ЛС
                            date("Y-m-d H:i:s", $pay['pay_date']),       //str     Дата платежа
                            $pay['pay_bank_no'],    //tiny    Банковский номер операции
                            $pay['pay_type_id'],    //int     ИД Типа платежа
                            $pay['pay_ppp_id'],     //int     Изменить Изменить
                            $pay['description']     //text    Краткое описание платежа
                        )) 
                {
                    MsgQueue::msg(MsgType::INFO, "Платёж ID=".$pay['id']." обновлён в биллинге.");
                    $pay['add_status'] = self::DO_STATUS_OK;
                } else {
                    MsgQueue::msg(MsgType::ERROR, "ОШИБКА: Платёж ID=".$pay['id']." не обновлён в биллинге!");
                    $pay['add_status'] = self::DO_STATUS_ERROR;
                }
            }



            //echo "<pre>".print_r($pay, true)."</pre>";
            // $t[] = [
            //     "abon_id"       => (isset($pay['abon_id'])      ? $pay['abon_id']       :"!abon_id"),
            //     "pay_fakt"      => (isset($pay['pay_fakt'])     ? $pay['pay_fakt']      :"!pay_fakt"),
            //     "pay"           => (isset($pay['pay'])          ? $pay['pay']           :"!pay"),
            //     "pay_date"      => (isset($pay['pay_date'])     ? $pay['pay_date']      :"!pay_date"),
            //     "pay_bank_no"   => (isset($pay['pay_bank_no'])  ? $pay['pay_bank_no']   :"!pay_bank_no"),
            //     "pay_type_id"   => (isset($pay['pay_type_id'])  ? $pay['pay_type_id']   :"!pay_type_id"),
            //     "pay_ppp_id"    => (isset($pay['pay_ppp_id'])   ? $pay['pay_ppp_id']    :"!pay_ppp_id"),
            //     "description"   => (isset($pay['description'])  ? $pay['description']   :"!description")
            //                         .((isset($pay['template_add']) and ($pay['template_add']==='on'))
            //                             ?"<br>+ шаблон &laquo;".$pay['template_text']."&raquo;&nbsp;"
            //                                 .(Bank::template_add($pay['pay_ppp_id'], $pay['template_aid'], $pay['template_text'])
            //                                     ?"<font color=green>Ok</font>"
            //                                     :"<font color=red>ERROR</font>"
            //                                 )
            //                             :""
            //                         ),
            //     "act" => "ADD:"
            //         .((isset($pay['to_billing']) AND ($pay['to_billing'] === 'on'))
            //             ?(pay_add(
            //                 $pay['abon_id'],        //int     Абонент, на которого зачисляется поалёж
            //                 $pay['pay_fakt'],       //float   Фактическая сумма пришедшая на р/с
            //                 $pay['pay'],            //float   Сумма платежа вносимая на ЛС
            //                 date("Y-m-d H:i:s", $pay['pay_date']),       //str     Дата платежа
            //                 $pay['pay_bank_no'],    //tiny    Банковский номер операции
            //                 $pay['pay_type_id'],    //int     ИД Типа платежа
            //                 $pay['pay_ppp_id'],     //int     Изменить Изменить
            //                 $pay['description']     //text    Краткое описание платежа
            //                 )
            //                 ?"<font color=green>Ok</font>"
            //                 :"<font color=red>ERROR</font>"
            //             )
            //             :"-"
            //         )
            //         ."<br>"
            //         ."EDIT:"
            //         .((isset($pay['to_edit']) AND ($pay['to_edit'] === 'on'))
            //             ?(pay_update(
            //                 $pay['id'],             //int
            //                 $pay['abon_id'],        //int     Абонент, на которого зачисляется поалёж
            //                 $pay['pay_fakt'],       //float   Фактическая сумма пришедшая на р/с
            //                 $pay['pay'],            //float   Сумма платежа вносимая на ЛС
            //                 date("Y-m-d H:i:s", $pay['pay_date']),       //str     Дата платежа
            //                 $pay['pay_bank_no'],    //tiny    Банковский номер операции
            //                 $pay['pay_type_id'],    //int     ИД Типа платежа
            //                 $pay['pay_ppp_id'],     //int     Изменить Изменить
            //                 $pay['description']     //text    Краткое описание платежа
            //                 )
            //                 ?"<font color=green>Ok</font>"
            //                 :"<font color=red>ERROR</font>"
            //             )
            //             :"-"
            //         ),
            // ];

        }

        /**
         * Вывод таблицы действий
         */
        $t_attributes = "width=80% border=0 align=center cellpadding=3 cellspacing=2";
        $t_col_titles = ["abon_id", "pay_fakt", "pay", "pay_date", "pay_bank_no", "pay_type_id", "pay_ppp_id",  "description", "act"];
        echo get_html_table($t,
                table_attributes: $t_attributes,
                col_titles: $t_col_titles);
        

    }



function getActions() {

        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');


        if (!App::isAuth()) { 
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            redirect('/'); 
        }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            redirect();
        }



        /**
         * -------------------------------------------------------------------------------
         * Внесение данных в базу
         */
        if(isset($_POST[Bank::POST_REC]) && is_array($_POST[Bank::POST_REC])) {
            debug($_POST, '$_POST', die:1);
            $post_rec = $_POST[Bank::POST_REC];
            $this->p24accUpdate($post_rec);
            redirect();
        }
        /**
         * Конец блока внесение данных в базу
         * -------------------------------------------------------------------------------
         */



        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !$model->validate_ppp((int)$this->route[F_ALIAS])
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point ID is incorrect | ID пункта приёма платежей не верен | ID пункту прийому платежів не вірний'));
            redirect();
        }

        $ppp = $model->get_ppp((int)$this->route[F_ALIAS]);

        if (!$model->is_ppp_my($ppp[Ppp::F_ID])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point is not mine | Пункт приема платежей не мой | Пункт прийому платежів не мій'));
            redirect();
        }


        /**
         * Проверка, что ППП поддерживает те Апи, которые поддерживает этот Action
         */
        $api_suported = false;
        foreach ([Bank::API_TYPE_P24_ACC, Bank::API_TYPE_MONO_CARD] as $api_type) {
            if (is_supported_api($ppp, $api_type)) {
                $api_suported = true;
                break;
            }
        }
        if (!$api_suported) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('API type is not supported | Тип API не поддерживается | Тип API не підтримується'));
            redirect();
        }
        unset($api_suported);
        unset($api_type);



        /**
         * Чтение шаблонов распознавания абонентов
         */
        $templates   = $model->get_abons_templates($ppp[Ppp::F_ID]);
        MsgQueue::msg(MsgType::INFO, __('Number of entries in the template table | Количество записей в таблице шаблонов | Кількість записів у таблиці шаблонів') . ": " . count($templates));


        
        /**
         * Вычисляем даты интервала для получения транзакции
         */
        $last_pay = $model->get_last_pay_on_ppp($ppp[Ppp::F_ID]);
        $date1_ts = (isset($_GET[Bank::F_GET_DATE1_TS])
                        ?   $_GET[Bank::F_GET_DATE1_TS]
                        :   (!is_null($last_pay) 
                                ?   $last_pay[Pay::F_DATE] 
                                :   TODAY() - App::get_config('bank_date_interval'))
                    );
        
        $date2_ts = (isset($_GET[Bank::F_GET_DATE2_TS])
                        ?   $_GET[Bank::F_GET_DATE2_TS]
                        :   (($date1_ts + App::get_config('bank_date_interval')) > time() 
                                ?   time()
                                :   $date1_ts + App::get_config('bank_date_interval')
                            )
                    );

        MsgQueue::msg(MsgType::INFO, __("Sampling dates | Даты выборки | Дати вибірки") . ": [". date('Y-m-d', $date1_ts) . "] - [" . date('Y-m-d', $date2_ts) . "]");



        $token   = $ppp[Ppp::F_API_ID];   // что-то такое: 'uBrjknkdhfasdfLXz44583xHOHfWgm';
        $api_url = $ppp[Ppp::F_API_URL];  // что-то такое: 'https://api.monobank.ua/personal/statement/';

        /**
         * Получение дополнительных данных из банка 
         * например для карты: информация о картах
         */
        switch ($ppp[Ppp::F_TYPE_ID]) {
            case PppType::TYPE_CARD :
                switch (true) {
                    case is_supported_api($ppp, Bank::API_TYPE_MONO_CARD):
                        $cards_info = MonoCard::get_card_info($token)['client'];
                        if (isset($cards_info['errorDescription'])) {
                            /*
                            * ERRORS: [errorDescription] == Too many requests
                            */
                            MsgQueue::msg(MsgType::ERROR, __('Error receiving card information | Ошибка получения информации о карте | Помилка отримання інформації про карту') . ': ' . $cards_info['client']['errorDescription']);
                            redirect(); // Bank::URI_INDEX
                        }
                        break;
                    
                    default:
                        # code...
                        break;
                }
                break;
            
            default:
                # code...
                break;
        }

        /**
         * Получение транзакций из банка
         */
        switch (true) {

            case is_supported_api($ppp, Bank::API_TYPE_MONO_CARD):
                $statements = MonoCard::get_statements($token, $date1_ts, $date2_ts, $api_url)[Bank::F_STATEMENTS];
                $map_fields = Bank::F_MAP_MONOCARD;
                break;
            
            default:
                # code...
                break;
        }


        // MsgQueue::msg(MsgType::INFO, "==== ".__("Getting data from billing | Получение данных из биллинга | Отримання даних з білінгу")." ====");

        /**
         * Формирование записи для каждой транзакции включающей:
         *      [Bank::F_STATEMENT] -- саму транзакцию
         *      [Bank::F_FOUND_REC] -- запись с результатами поиска в биллинге
         *      [Bank::F_PAY_REC]   -- запись платежа для сохранения в биллинг или сравнения
         */
        $data = [];
        foreach ($statements as $index => $statement) {

            $data[$index][Bank::F_STATEMENT] = $statement;

            /**
             * платежи, внесённые в биллинг с указанным кодом транзакции
             */
            $data[$index][Bank::F_FOUND_REC] = Bank::search_payments_on_billing($statement, MonoCard::TEXT_FIELDS, $ppp[Ppp::F_ID], $templates); 
            
            /**
             * Запись для внесения в базу и сравнения с уже внесёнными данными
             */
            $comission = $statement[MonoCard::F_COMMISSION_RATE];
            $pay_rec[Pay::F_PAY_FAKT]    = floatval(get_numeric_part($statement[$map_fields[Pay::F_PAY_FAKT]]));
            $pay_rec[Pay::F_PAY_ACNT]    = floatval(get_numeric_part($statement[$map_fields[Pay::F_PAY_ACNT]]) + $comission);
            $pay_rec[Pay::F_DATE]        = $statement[$map_fields[Pay::F_DATE]];
            $pay_rec[Pay::F_BANK_NO]     = $statement[$map_fields[Pay::F_BANK_NO]];
            $pay_rec[Pay::F_TYPE_ID]     = Pay::TYPE_MONEY;
            $pay_rec[Pay::F_AGENT_ID]    = App::get_user_id();
            $pay_rec[Pay::F_PPP_ID]      = $ppp[Ppp::F_ID];
            $pay_rec[Pay::F_DESCRIPTION] = implode(
                ' | ',
                array_filter(
                    array_map(
                        function ($key) use ($statement) {
                            return trim((string)($statement[$key] ?? ''));
                        },
                        $map_fields[Pay::F_DESCRIPTION]
                    ),
                    function ($value) {
                        return $value !== '';
                    }
                )
            );

            $data[$index][Bank::F_PAY_REC] = $pay_rec;
        }
        // MsgQueue::msg(MsgType::INFO, "==== ". __("All data is collected | Все данные собраны | Всі дані зібрані") ." ====");

        View::setMeta(__('Payments to a Monobank card | Платежи на карту Монобанка | Платежі на карту Монобанку'));

        $this->setVariables([
            'cards_info'    => $cards_info, // []
            'data'          => $data,       // [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
            'date1'         => $date1_ts,   // int, timestamp, начало периода выборки
            'date2'         => $date2_ts,   // int, timestamp, конец периода выборки
            'date_last_pay' => $last_pay[Pay::F_DATE], // int, timestamp, Дата последнего зарегистрированного платежа на ППП
            'ppp'           => $ppp,        // array ППП
        ]);

    }



    function p24accAction() {

        if (!App::isAuth()) { redirect('/'); }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
            redirect();
        }

        if(isset($_POST[Bank::POST_REC]) && is_array($_POST[Bank::POST_REC])) {
            $post_rec = $_POST[Bank::POST_REC];
            $this->p24accUpdate($post_rec);
        }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !$model->validate_ppp((int)$this->route[F_ALIAS])
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID не верен'));
            redirect();
        }

        $ppp = $model->get_ppp((int)$this->route[F_ALIAS]);

        if (!is_supported_api($ppp, Bank::API_TYPE_P24_ACC)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ППП не поддерживает этот API'));
            redirect();
        }

        MsgQueue::msg(MsgType::INFO_AUTO, "====Получение данных====");

        /**
         * Чтение шаблонов распознавания абонентов
         */
        $templates   = $model->get_abons_templates($ppp[Ppp::F_ID]);
        MsgQueue::msg(MsgType::INFO_AUTO, "Размер templates: ".count($templates)." записей");

        /**
         * Тип пополнеиня ЛС для внесения в билинг.
         * 1 - Pay::TYPE_MONEY Денежное пополнение ЛС | 2 - Корректировка ЛС | 3 - Начисление за услугу
         */
        $pay_type_id = Pay::TYPE_MONEY;

        /**
         * Вычисляем даты интервала для получения транзакции
         */
        $last_pay   =   $model->get_last_pay_on_ppp($ppp[Ppp::F_ID]);
        $date1_str  =   (isset($_GET[Bank::F_GET_DATE_START])
                            ? $_GET[Bank::F_GET_DATE_START]
                            : date('d-m-Y', (!is_null($last_pay) ? $last_pay[Pay::F_DATE] : TODAY()) - App::get_config('bank_date_interval')));
        $date2_str  =   (isset($_GET[Bank::F_GET_DATE_END])
                            ? $_GET[Bank::F_GET_DATE_END]
                            : date('d-m-Y'));
        MsgQueue::msg(MsgType::INFO_AUTO, "Даты выборки: [".$date1_str."] - [".$date2_str."]");

        /**
         * Получение транзакций из банка
         */
        $transactions = Bank::p24acc_get_transactions($ppp, $date1_str, $date2_str, trantype: Bank::TRANSACTION_TYPE_C);
        sort_array_by_field($transactions, P24acc::F_DATE_TIME_DAT_OD_TIM_P, false);

        $trns = [];
        foreach ($transactions as $index => $t_row) {
            $found_pays = Bank::p24acc_search_payments_on_billing($t_row, $ppp[Ppp::F_ID], $templates);

            $comission = (str_contains_array($t_row[P24acc::F_OSND], App::get_config('bank_comission_text'))
                            ?   App::get_config('bank_comission_value')
                            :   (str_contains_array($t_row[P24acc::F_OSND], App::get_config('bank_liqpay_ident_text'))
                                    ? Bank::calc_comission($save_pay[Pay::F_PAY_FAKT], $ppp['api_liqpay_return_comission'])
                                    : 0
                                )
                            );
            $save_pay[Pay::F_PAY_FAKT]    = floatval($t_row[P24acc::F_SUM]);
            $save_pay[Pay::F_PAY_ACNT]    = ($comission > 0 ? round(floatval($t_row[P24acc::F_SUM]) + $comission) : floatval($t_row[P24acc::F_SUM]));
            $save_pay[Pay::F_DATE]        = strtotime($t_row[P24acc::F_DATE_TIME_DAT_OD_TIM_P]);
            $save_pay[Pay::F_BANK_NO]     = $t_row[P24acc::F_ID];
            $save_pay[Pay::F_TYPE_ID]     = $pay_type_id;
            $save_pay[Pay::F_PPP_ID]      = $ppp[Ppp::F_ID];
            $save_pay[Pay::F_DESCRIPTION] = $t_row[P24acc::F_OSND];

            $trns[] = [
                'transaction' => $t_row,
                'found_pays' => $found_pays,
                'save_pay' => $save_pay,
            ];
        }

        $unknowns = $model->get_unknown_payments($ppp[Ppp::F_ID]);

        $this->setVariables([
            'date1' => $date1_str,
            'date2' => $date2_str,
            'ppp' => $ppp,
            'transactions' => $trns,
            'unknowns' => $unknowns,
        ]);

        View::setMeta(title: __('Контроль и ручное распределение платежей'));


    }



    function indexAction() {

        if (!App::isAuth()) { redirect('/'); }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
            redirect();
        }

        $model = new AbonModel();

        $ppp_list = [];
        foreach (Bank::API_MANUAL_LIST as $api_type) {
            $ppp_list[$api_type] = $model->get_ppp_list_by_api($api_type);
        }


        $this->setVariables([
            'ppp_list' => $ppp_list,
        ]);

        View::setMeta(title: __('Список ППП для контроля и ручного распределения платежей'));

    }




}