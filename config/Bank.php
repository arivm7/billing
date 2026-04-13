<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Bank.php
 *  Path    : config/Bank.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Oct 2025 19:53:55
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of Bank.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

namespace config;

use app\controllers\TemplateController;
use app\models\AbonModel;
use billing\core\App;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\TSAbonTmpl;
use config\tables\User;

class Bank
{

    const URI_INDEX = '/bank';
    const URI_GET   = '/bank/get';

    const POST_REC = 'pay_update';
    
    const URI_API_LIST = [
        self::API_TYPE_P24_ACC      => '/bank/p24acc',
        self::API_TYPE_P24_MANUAL   => '/bank/p24manual',
        self::API_TYPE_MONO_CARD    => '/bank/monocard',
    ];

    /**
     * Поддерживаемые АПИ
     */
    const API_TYPE_BANK_CARD  = 'bank_card';
    const API_TYPE_P24_ACC    = 'p24_acc';
    const API_TYPE_P24_LIQPAY = 'p24_liqpay';
    const API_TYPE_P24PAY     = 'p24pay';
    const API_TYPE_P24_MANUAL = 'p24_manual';
    const API_TYPE_MONO_CARD  = 'mono_pay';

    /**
     * Имена $_GET переменных для праметров выборки
     */
    const F_GET_API           = 'api';
    const F_GET_ACC           = 'acc';
    const F_GET_PPP_ID        = 'ppp_id';
    const F_GET_DATE_START    = 'startDate';
    const F_GET_DATE_END      = 'endDate';
    const F_GET_DATE1_TS      = 'ds';
    const F_GET_DATE2_TS      = 'de';
    const F_GET_FOLLOW_ID     = 'followId';
    const F_GET_LIMIT         = 'limit';



    /**
     * Имя поля, в котором хранится массив с именами полей из транзакции в которых нужно проводить поиск по таблице шаблонов
     * чисто служебная плюха, используется в коле один раз.
     */
    const F_SEARCH_FIELDS    = 'SEARCH_FIELDS';
    const F_COMISSION_FIELD  = 'COMISSION_FIELD';


    
    /**
     * имя поля с массивом транзакций (платежей), полученными из банка
     */
    const F_STATEMENTS       = 'statements'; 



    /**
     * имя поля одной транзакции (платежа), полученными из банка
     */
    const F_STATEMENT       = 'statement'; 



    /**
     * имя поля ассоциативного массива с записью для внесения платежа в биллинг.
     * Используется для внесения платежа или для сравнения с внесённым платежом.
     */
    const F_PAY_REC          = 'pay_rec';



    /**
     * Имя поля структуры содержащей запись о найденном платеже результаты поиска платежа в билинге
     * Содержит массив с результатами поиска
     *    F_FOUND_REC = [
     *      F_FOUND_ON_BILLING  = 'on_billing'    : bool,     -- найден в биллинге, платёж уже внесён
     *      F_FOUND_SEARCHED_ON = 'searched_on'   : string,   -- коментарий к результатам поиска
     *      F_FOUND_PAY         = 'pay'           : array,    -- найденный внесённый платёж в базе, соответсвующий транзакции
     *      F_FOUND_ABON        = 'abon'          : array,    -- абонент, для которого этот платёж
     *      F_FOUND_AID_LIST    = 'aid_list'      : array,    -- список предположительных абонентов, к котороым относится платёж
     *      F_FOUND_TEMPLATE    = 'template'      : string    -- предлагаемый шаблон для идентификации таких платежей
     *    ]
     */
    const F_FOUND_REC        = 'found_rec';


    
    /**
     * Поля для возврата результата поиска платежа в биллтнге или поиска абонента, для которого этот платёж.
     */
    const F_FOUND_ON_BILLING  = "on_billing";     // -- найден в биллинге, платёж уже внесён
    const F_FOUND_SEARCHED_ON = "searched_on";    // -- лог-коментарий к результатам поиска
    const F_FOUND_PAY         = "pay";            // -- найденный внесённый платёж в базе, соответсвующий транзакции
    const F_FOUND_ABON        = "abon";           // -- абонент, для которого платёж
    const F_FOUND_AID_LIST    = "aid_list";       // -- строка, список предположительных абонентов, к котороым относится платёж
    const F_FOUND_TEMPLATE    = "template";       // -- шаблон для поиска абонента
    const F_FOUND_TEMPLATE_SAVE = "template_save";  // -- чекбокс для сохранения template



    /**
     * Полный список АПИ
     */
    const API_TYPE_LIST = [
        self::API_TYPE_BANK_CARD,
        self::API_TYPE_P24_ACC,
        self::API_TYPE_P24_LIQPAY,
        self::API_TYPE_P24PAY,
        self::API_TYPE_P24_MANUAL,
        self::API_TYPE_MONO_CARD,
    ];



    /**
     * Список АПИ для ручного внесения поатежей,
     * для которых есть вэб-интерфейс для внесения платежей
     */
    const API_MANUAL_LIST = [
        self::API_TYPE_P24_ACC,
        self::API_TYPE_MONO_CARD,
        self::API_TYPE_P24_MANUAL,
    ];



    /**
     * //Тип вибираемых транзакций дебет/кредит (D, C)
     */
    const TRANSACTION_TYPE_D   = -1; // D "-" (Дебет)
    const TRANSACTION_TYPE_C   =  1; // C "+" (Кредит)
    const TRANSACTION_TYPE_ALL =  0; // Все



    /**
     * Поля таблицы billing.payments и соответствующие поля из тразакции карты Монобанк.
     */
    const MAP_MONOCARD = [
        Pay::F_BANK_NO          => MonoCard::F_BANK_ID, // Банковский номер операции
        Pay::F_DATE             => MonoCard::F_TIME,    // Дата платежа
        Pay::F_PAY_FAKT         => MonoCard::F_AMOUNT,  // Фактическая сумма, пришедшая на счёт
        Pay::F_PAY_ACNT         => MonoCard::F_AMOUNT,  // Сумма платежа, вносимая на ЛС
        Pay::F_REST             => MonoCard::F_BALANCE, // number <int64>  Баланс рахунку в мінімальних одиницях валюти (копійках, центах)
        Pay::F_DESCRIPTION      => [MonoCard::F_DESCRIPTION, MonoCard::F_COMMENT, MonoCard::F_COUNTER_NAME],    // Описание платежа

        /**
         * Поле в котором указана коммисия, если есть
         */
        self::F_COMISSION_FIELD  => MonoCard::F_COMMISSION_RATE,

        /**
         * Поля для поиска номера договора по таблице шаблонов
         */
        self::F_SEARCH_FIELDS   => [MonoCard::F_DESCRIPTION, MonoCard::F_COMMENT, MonoCard::F_COUNTER_NAME],   
    ];



    /**
     * Поля таблицы billing.payments и соответствующие поля из тразакции P24_ACC (р/с в Приватбанке)
     */
    const MAP_P24ACC = [
        Pay::F_BANK_NO          => [P24acc::F_REF, P24acc::F_REFN],     // Банковский номер операции
        Pay::F_DATE             => P24acc::F_DATE_TIME_DAT_OD_TIM_P,    // Дата платежа
        Pay::F_PAY_FAKT         => P24acc::F_SUM_E,                     // Фактическая сумма, пришедшая на счёт
        Pay::F_PAY_ACNT         => P24acc::F_SUM_E,                     // Сумма платежа, вносимая на ЛС
        Pay::F_REST             => null,
        Pay::F_DESCRIPTION      => [P24acc::F_OSND, P24acc::F_AUT_CNTR_NAM],    // Описание платежа

        /**
         * Поле в котором указана коммисия, если есть
         */
        self::F_COMISSION_FIELD  => '',

        /**
         * Поля для поиска номера договора по таблице шаблонов
         */
        self::F_SEARCH_FIELDS   => [P24acc::F_OSND, P24acc::F_AUT_CNTR_NAM],
    ];



    /**
     * Поля таблицы billing.payments и соответствующие поля из тразакции карты Приватбанка.
     */
    const MAP_P24CARD = [
        Pay::F_BANK_NO          => P24card::F_BANK_NO,          // Банковский номер операции
        Pay::F_DATE             => P24card::F_DATE,             // Дата платежа
        Pay::F_PAY_FAKT         => P24card::F_AMOUNT,         // Фактическая сумма, пришедшая на счёт
        Pay::F_PAY_ACNT         => P24card::F_AMOUNT,         // Сумма платежа, вносимая на ЛС
        Pay::F_REST             => P24card::F_REST,
        Pay::F_DESCRIPTION      => [P24card::F_DESCRIPTION],    // Описание платежа

        /**
         * Поле в котором указана коммисия, если есть
         */
        self::F_COMISSION_FIELD  => null,

        /**
         * Поля для поиска номера договора по таблице шаблонов
         */
        self::F_SEARCH_FIELDS   => [Pay::F_DESCRIPTION],

    ];
    


    /**
     * Возвращает список транзакций
     * @param array $ppp
     * @param string $date1
     * @param string $date2
     * @param mixed $trantype
     * @throws \Exception
     * @return array
     */
    public static function p24acc_get_transactions(array $ppp, string $date1, string $date2, $trantype = self::TRANSACTION_TYPE_ALL): array {

        $autoclient_id      = $ppp[Ppp::F_API_ID];
        $autoclient_token   = $ppp[Ppp::F_API_PASS];
        $autoclient_acc     = str_replace(" ", "", $ppp[Ppp::F_NUMBER]);
        $autoclient_url     = $ppp[Ppp::F_API_URL];

        $followId           = null;

        $headers = self::p24acc_make_header($autoclient_id, $autoclient_token);

        $transactions = [];
        $iteration     = 0;
        do {
            $iteration++;
            $request = self::p24acc_make_request($autoclient_acc, $date1, $date2, $followId);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPGET,           1); // Использовать метод GET
            curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers);
            curl_setopt($ch, CURLOPT_URL,               $autoclient_url . $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1); // TRUE to return the transfer as a string of the return value of {@see curl_exec()} instead of outputting it directly.
            curl_setopt($ch, CURLOPT_HEADER,            0); // TRUE to include the header in the output.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1); // Проверять SSL сертификат / 1 - проверять, 0 - не проверять

            $res = curl_exec($ch);
            $s = "Статус получения данных из банка: ".(curl_errno($ch)==0?"Ok":"ERROR")." : ".curl_error($ch)."<br>\n";
            MsgQueue::msg(MsgType::INFO_AUTO, $s);
            if(curl_errno($ch)!=0) {
                curl_close($ch);
                throw new \Exception('Ошибка выполнения запроса к банку. Сообщите программисту');
            }
            curl_close($ch);

            $arr = json_decode($res, true);
            //echo "ARRAY:<pre>"; var_dump($arr); echo "</pre><hr>";
            $s  = "Статус ответа банка: ".($arr['status']).(isset($arr['message']) ? " [".paint($arr['message'], color: RED)."]":"")."<br>\n";
            MsgQueue::msg(MsgType::INFO_AUTO, $s);
            foreach ($arr['transactions'] as $T) {
                switch ($trantype) {
                    case self::TRANSACTION_TYPE_ALL:
                        $transactions[] = $T;
                        break;
                    case self::TRANSACTION_TYPE_C: // Кредит +
                        if ($T['TRANTYPE'] == 'C') {
                            $transactions[] = $T;
                        }
                        break;
                    case self::TRANSACTION_TYPE_D: // Дебет -
                        if ($T['TRANTYPE'] == 'D') {
                            $transactions[] = $T;
                        }
                        break;
                    default:
                        throw new \Exception('Не верноый тип транзакции TRANSACTION_TYPE_*: ' . $trantype);
                }
            }
            $followId = ($arr['exist_next_page']
                            ? $arr['next_page_id']
                            : null
                        );
            $s = "Следующая транзакция: ".($followId?$followId:"нет")."<br>\n";
            MsgQueue::msg(MsgType::INFO_AUTO, $s);
        } while ($arr['exist_next_page'] && ($iteration < App::get_config('bank_get_iteration_max')));
        return $transactions;
    }



    public static function p24acc_make_header(string $autoclient_id, string $autoclient_token): array {
        return array
        (
            'User-Agent: '. App::get_config('bank_http_user_agent'),
            'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
            'Accept-Encoding: deflate',
            'Accept-Charset: utf-8;q=0.7,*;q=0.7',
            'id: '.$autoclient_id,
            'token: '.$autoclient_token,
            'Content-Type: application/json;charset=utf8'
        );
    }


    public static function p24acc_make_request(string $autoclient_acc, string $date1, string $date2, ?string $followId = null): string {
        return '?'.self::F_GET_ACC.'='.$autoclient_acc
                .'&'.self::F_GET_DATE_START.'='.rawurlencode($date1)
                .'&'.self::F_GET_DATE_END.'='.rawurlencode($date2)
                .($followId ? '&'.self::F_GET_FOLLOW_ID.'='.$followId : "")
                .'&'.self::F_GET_LIMIT.'='. App::get_config('bank_limit_per_page');
    }



    // /**
    //  * Поиск платежа в биллинге или поиск абонента, для которого этот платеж.
    //  * Поиск в базе проводится только по номеру документа (ID транзакции в банке).
    //  * Возвращает ассоциативный массив:
    //  * [payments]    -- массив найденных платежей, считается найденным, если найден один платёж
    //  * [abon]        -- если платёж не найден, то абонент, для которого этот платёж
    //  * [on_billing]  -- найден в биллинге, платёж уже внесён
    //  * [searched_on] -- коментарий к поиску, где найден платёж или абонент
    //  * @param array $transaction
    //  * @param int $ppp_id
    //  * @param array $templates
    //  * @return array{
    //  *      payments: array,
    //  *      on_billing: bool, 
    //  *      abon: array, 
    //  *      searched_on: string, 
    //  *      template: string}
    //  */
    // public static function p24acc_search_payments_on_billing($transaction, $ppp_id, &$templates): array {
    //     $model = new AbonModel();
    //     $found_pay['payments'] = $model->get_billing_payments_by_no($transaction[P24acc::F_ID], $ppp_id);
    //     // if (count($found_pay['payments']) == 0) {
    //     //     $found_pay['payments'] = $model->get_billing_payments_id_date_pay($transaction[P24acc::F_NUM_DOC], strtotime($transaction[P24acc::F_DATE_TIME_DAT_OD_TIM_P]), get_numeric_part($transaction[P24acc::F_SUM]), $ppp_id, true);
    //     // }

    //     if (count($found_pay['payments']) > 1) {
    //         // Если найдено несколько платежей, то считаем, что НЕ найдено
    //         $id_list = array_column($found_pay['payments'], Pay::F_ID);
    //         foreach ($id_list as &$item) { $item = url_abon_form($item); }
    //         $found_pay['on_billing']  = false;
    //         $found_pay['searched_on'] = 'billing ('.__('Many found | Найдено много | Знайдено багато').': '.implode(", ", $id_list).')';
    //     } elseif (count($found_pay['payments']) == 1) {
    //         $found_pay['abon']        = $model->get_abon($found_pay['payments'][0][Pay::F_ABON_ID]);
    //         $found_pay['on_billing']  = true;
    //         $found_pay['searched_on'] = 'billing by ID';
    //     } else {
    //         $aid = TemplateController::get_abon_id_from_templates($templates, $transaction[P24acc::F_OSND]);
    //         if ($aid > 0) {
    //             $found_pay['abon']        = $model->get_abon($aid);
    //             $found_pay['on_billing']  = false;
    //             $found_pay['searched_on'] = 'Templates (by [' . P24acc::F_OSND .'])';
    //         } else {
    //             $aid = TemplateController::get_abon_id_from_templates($templates, $transaction[P24acc::F_AUT_CNTR_NAM]);
    //             if ($aid > 0) {
    //                 $found_pay['abon']        = $model->get_abon($aid);
    //                 $found_pay['on_billing']  = false;
    //                 $found_pay['searched_on'] = 'Templates (by [' . P24acc::F_AUT_CNTR_NAM . '])';
    //             } else {
    //                 $aid = $model->get_abon_id_from_text($transaction[P24acc::F_OSND]);
    //                 if ($aid > 0) {
    //                     $found_pay['abon']        = $model->get_abon($aid);
    //                     $found_pay['on_billing']  = false;
    //                     $found_pay['searched_on'] = 'from [' . P24acc::F_OSND .']';
    //                 } else {
    //                     $payer_txt = Bank::get_payer_from_text($transaction[P24acc::F_OSND]);
    //                     if (strlen($payer_txt)>0) {
    //                         $aid = $model->get_abon_id_one_by_payer_name($payer_txt);
    //                         if ($aid > 0) {
    //                             $found_pay['abon']        = $model->get_abon($aid);
    //                             $found_pay['on_billing']  = false;
    //                             $found_pay['searched_on'] = 'Payments &laquo;'.$payer_txt.'&raquo;';
    //                             $found_pay['template']    = $payer_txt;
    //                         } else {
    //                             $found_pay['on_billing']  = false;
    //                             $found_pay['searched_on'] = __('Nowhere | Нигде | Ніде');
    //                         }
    //                     } else {
    //                         $found_pay['on_billing']  = false;
    //                         $found_pay['searched_on'] = __('Nowhere | Нигде | Ніде');
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $found_pay;
    // }



    // /**
    //  * Поиск платежа в биллинге или поиск абонента, для которого этот платеж.
    //  * Поиск в базе проводится только по номеру документа (ID транзакции в банке).
    //  * Возвращает ассоциативный массив:
    //  *      F_FOUND_ON_BILLING: true,       -- найден в биллинге, платёж уже внесён
    //  *      F_FOUND_SEARCHED_ON: string,    -- коментарий к результатам поиска
    //  *      F_FOUND_PAY: array,             -- найденный внесённый платёж в базе, соответсвующий транзакции
    //  *      F_FOUND_ABON: array,            -- если платёж не найден, то абонент, для которого этот платёж
    //  *      F_FOUND_AID_LIST: array,        -- список предположительных абонентов, к котороым относится платёж
    //  *      F_FOUND_TEMPLATE: string        -- строка-шаблон для идентификации этого платежа
    //  * @param array $statement      // -- Запись транзакции полученная из банка
    //  * @param array $text_fields    // -- array, имена текстовых полей для анализа плательщика и назначени платежа
    //  * @param int   $ppp_id         // -- ППП к которому относится платёж
    //  * @param array $templates      // -- ссылка на таблицу шаблонов
    //  * @return array{
    //  *      on_billing: true,       // -- найден в биллинге, платёж уже внесён
    //  *      searched_on: string,    // -- коментарий к результатам поиска
    //  *      pay: array,             // -- найденный внесённый платёж в базе, соответсвующий транзакции
    //  *      abon: array,            // -- если платёж не найден, то абонент, для которого этот платёж
    //  *      aid_list: array,        // -- список предположительных абонентов, к котороым относится платёж
    //  *      template: string}       // -- строка шаблон для идентификации этого платежа
    //  */
    // public static function search_payments_on_billing(array &$statement, array $text_fields = [], int $ppp_id = 0, array &$templates = []): array {
    //     $model = new AbonModel();
    //     $found_rec = [];

    //     $found_payments = $model->get_billing_payments_by_no($statement[MonoCard::F_BANK_ID], $ppp_id);

    //     if (count($found_payments) > 1) {
    //         // Если найдено несколько платежей, то считаем, что НЕ найдено
    //         $aid_list = array_column($found_payments, Pay::F_ABON_ID);
    //         foreach ($aid_list as &$item) { $item = $model->url_abon_form($item); }
    //         $found_rec[self::F_FOUND_ON_BILLING]  = false;
    //         $found_rec[self::F_FOUND_SEARCHED_ON] = 'ERROR: on Billing by BankNo ('.__('Many found | Найдено много | Знайдено багато').': '.implode(", ", $aid_list).')';
    //         return $found_rec;
    //     }
        
    //     if (count($found_payments) == 1) {
    //         $found_rec[self::F_FOUND_PAY]         = $found_payments[array_key_first($found_payments)];
    //         $found_rec[self::F_FOUND_ABON]        = $model->get_abon($found_payments[array_key_first($found_payments)][Pay::F_ABON_ID]);
    //         $found_rec[self::F_FOUND_ON_BILLING]  = true;
    //         $found_rec[self::F_FOUND_SEARCHED_ON] = 'billing by ID';
    //         return $found_rec;
    //     }

    //     /**
    //      * Поиск по шаблонам
    //      */
    //     foreach ($text_fields as $field) {
    //         $aid = TemplateController::get_abon_id_from_templates($templates, ($statement[$field] ?? ''));
    //         if ($aid > 0) {
    //             $found_rec[self::F_FOUND_ABON]        = $model->get_abon($aid);
    //             $found_rec[self::F_FOUND_ON_BILLING]  = false;
    //             $found_rec[self::F_FOUND_SEARCHED_ON] = 'Templates (by [' . $statement[$field] . '])';
    //             return $found_rec;
    //         }
    //     }

    //     /**
    //      * Поиск ID в текстовых полях
    //      */
    //     $aid_list = [];
    //     foreach ($text_fields as $field) {
    //         $aid_list = array_unique(array_merge($aid_list, $model->get_abon_id_list_from_text(($statement[$field] ?? '')))); 
    //     }

    //     if (count($aid_list) == 1) {
    //         $found_rec[self::F_FOUND_ABON]        = $model->get_abon($aid_list[array_key_first($aid_list)]);
    //         $found_rec[self::F_FOUND_ON_BILLING]  = false;
    //         $found_rec[self::F_FOUND_SEARCHED_ON] = 'on Text Fields';
    //         return $found_rec;
    //     }

    //     if (count($aid_list) > 1) {
    //         foreach ($aid_list as &$item) { $item = $model->url_abon_form($item); }
    //         $found_rec[self::F_FOUND_ON_BILLING]  = false;
    //         $found_rec[self::F_FOUND_SEARCHED_ON] = 'billing ('.__('Many found | Найдено много | Знайдено багато').': '.implode(" | ", $aid_list).')';
    //         return $found_rec;
    //     }

    //     /**
    //      * Поиск имени плательщика из текстовых полей
    //      * Поиск абонента по имени плательщика
    //      */
    //     foreach ($text_fields as $field) {
    //         $payer_txt = Bank::get_payer_from_text($statement[$field] ?? '');
    //         if (!empty($payer_txt)) {
    //             $aid_list = $model->get_abon_id_list_by_payer_name($payer_txt);
    //             if(count($aid_list) > 0) {
    //                 if (count($aid_list) == 1) {
    //                     $found_rec[self::F_FOUND_ABON]    = $model->get_abon($aid_list[array_key_first($aid_list)]);
    //                 }
    //                 foreach ($aid_list as &$item) { $item = $model->url_abon_form($item); }
    //                 $found_rec[self::F_FOUND_ON_BILLING]  = false;
    //                 $found_rec[self::F_FOUND_SEARCHED_ON] = 'on Payments ('.__('Found | Найдено | Знайдено').': '.implode(" | ", $aid_list).')';
    //                 $found_rec[self::F_FOUND_AID_LIST]    = $aid_list;
    //                 $found_rec[self::F_FOUND_TEMPLATE]    = $payer_txt;
    //                 return $found_rec;
    //             }
    //         }
    //     }
        
    //     $found_rec[self::F_FOUND_ON_BILLING] = false;
    //     $found_rec[self::F_FOUND_SEARCHED_ON] = __('Nowhere | Нигде | Ніде');

    //     // debug($found_rec, '1 $found_rec');

    //     return $found_rec;
    // }




    

    public static function search_payments_on_billing2(array $pay_rec, array &$templates = []): array {
        $model = new AbonModel();
        $found_rec = [];

        $found_payments = $model->get_billing_payments_by_no($pay_rec[Pay::F_BANK_NO], $pay_rec[Pay::F_PPP_ID]);

        if (count($found_payments) > 1) {
            // Если найдено несколько платежей, то считаем, что НЕ найдено
            $aid_list = array_column($found_payments, Pay::F_ABON_ID);
            foreach ($aid_list as &$item) { $item = $model->url_abon_form($item); }
            $found_rec[self::F_FOUND_ON_BILLING]  = false;
            $found_rec[self::F_FOUND_SEARCHED_ON] = 'ERROR: on Billing by BankNo ('.__('Many found | Найдено много | Знайдено багато').': '.implode(", ", $aid_list).')';
            return $found_rec;
        }
        
        if (count($found_payments) == 1) {
            $found_rec[self::F_FOUND_PAY]         = $found_payments[array_key_first($found_payments)];
            $found_rec[self::F_FOUND_ABON]        = $model->get_abon($found_payments[array_key_first($found_payments)][Pay::F_ABON_ID]);
            $found_rec[self::F_FOUND_ON_BILLING]  = true;
            $found_rec[self::F_FOUND_SEARCHED_ON] = 'billing by BANK_NO';
            return $found_rec;
        }

        /**
         * Поиск по шаблонам
         */
        $template = TemplateController::get_abon_rec_from_templates($pay_rec[Pay::F_DESCRIPTION], $templates);
        $aid = $template[TSAbonTmpl::F_ABON_ID] ?? 0;
        if ($aid > 0) {
            $found_rec[self::F_FOUND_ABON]        = $model->get_abon($aid);
            $found_rec[self::F_FOUND_ON_BILLING]  = false;
            $found_rec[self::F_FOUND_SEARCHED_ON] = 'Templates (by [' . $template[TSAbonTmpl::F_TEMPLATE] . '])';
            return $found_rec;
        }
        unset($template);
        unset($aid);

        /**
         * Поиск ID в текстовых полях
         */
        $aid_list = $model->get_abon_id_list_from_text($pay_rec[Pay::F_DESCRIPTION]); 

        if (count($aid_list) == 1) {
            $found_rec[self::F_FOUND_ABON]        = $model->get_abon($aid_list[array_key_first($aid_list)]);
            $found_rec[self::F_FOUND_ON_BILLING]  = false;
            $found_rec[self::F_FOUND_SEARCHED_ON] = 'on Text Fields';
            return $found_rec;
        }

        if (count($aid_list) > 1) {
            foreach ($aid_list as &$item) { $item = $model->url_abon_form($item); }
            $found_rec[self::F_FOUND_ON_BILLING]  = false;
            $found_rec[self::F_FOUND_SEARCHED_ON] = 'billing ('.__('Many found | Найдено много | Знайдено багато').': '.implode(" | ", $aid_list).')';
            return $found_rec;
        }

        /**
         * Поиск имени плательщика из текстовых полей
         * Поиск абонента по имени плательщика
         */
        $payer_txt = Bank::get_payer_from_text($pay_rec[Pay::F_DESCRIPTION]);
        if (!empty($payer_txt)) {
            $aid_list = $model->get_abon_id_list_by_payer_name($payer_txt);
            if(count($aid_list) > 0) {
                    if (count($aid_list) == 1) {
                        $found_rec[self::F_FOUND_ABON]    = $model->get_abon($aid_list[array_key_first($aid_list)]);
                    }
                    foreach ($aid_list as &$item) { $item = $model->url_abon_form($item); }
                    $found_rec[self::F_FOUND_ON_BILLING]  = false;
                    $found_rec[self::F_FOUND_SEARCHED_ON] = 'on Payments ('.__('Found | Найдено | Знайдено').': ['.implode(" | ", $aid_list).'] by ['.$payer_txt.'])';
                    $found_rec[self::F_FOUND_AID_LIST]    = $aid_list;
                    $found_rec[self::F_FOUND_TEMPLATE]    = $payer_txt;
                    return $found_rec;
            }
        }
        
        $found_rec[self::F_FOUND_ON_BILLING] = false;
        $found_rec[self::F_FOUND_SEARCHED_ON] = __('Nowhere | Нигде | Ніде');

        // debug($found_rec, '1 $found_rec');

        return $found_rec;
    }



    /**
     * Извлекает имя плательщика из текста
     * 
     * Функция использует регулярное выражение для поиска имени плательщика в переданном тексте,
     * поддерживает различные слова-идентификаторы плательщика на разных языках
     * 
     * @param string $text Входящий текст
     * @return string Возвращает извлеченное имя плательщика, если не найдено - возвращает пустую строку
     */
    public static function get_payer_from_text(string $text): string {

        // Определяем шаблон слов-идентификаторов плательщика, включающий различные языки и сокращения
        $template_words     = "дог\."
                            . "|Отправитель"
                            . "|ПЛ\-ЩИК"
                            . "|Пл\-щик"
                            . "|Плательщик"
                            . "|Перевод от"
                            . "|От"
                            . "|Від"
                            . "|за послуги інтернет від";

        // Определяем список исключений, чтобы избежать ошибочной интерпретации телефонов или номеров карт
        $template_excludes  = ['Тел.', 'тел.', 'карта'];

        // Определяем шаблон для имени, который соответствует последовательности символов без цифр, запятых, скобок, плюсов, точек и пробелов
        // $template_name   = "[^0-9,(\+\.\s]+\.?";
        $template_name      = "[A-Za-zА-ЯІЇЄҐа-яіїєґ'-]+\.?";

        $subject = trim($text);
        
        // --- 1. Основной паттерн (как было)
        // Шаблон регулярного выражения: находит структуру текста, содержащую идентификатор плательщика с последующим именем
        // Группа 1: идентификатор плательщика; Группа 2: имя плательщика (состоящее из 2-4 слов)
        // Номера групп:           |-------1-------|     |------------2-------------|
        $pattern               = "/($template_words):?\s+((?:$template_name\s*){2,4})/u";

        if (preg_match($pattern, $subject, $matches)) {
            return trim(str_replace($template_excludes, "", $matches[2]));
        }

        // --- 2. Новый fallback: вся строка = ФИО
        $pattern_fullname = '/^[^\d,()+]+(?:\s+[А-ЯІЇЄҐA-Z][а-яіїєґa-z]+|\s+[А-ЯІЇЄҐA-Z]\.)+(?:\s+[А-ЯІЇЄҐA-Z]\.)?$/u';

        if (preg_match($pattern_fullname, $subject)) {
            return $subject;
        }

        return "";


    }



    /**
     * Возвращает сумму комиссии, снятую банком
     * вычисляется из суммы фактически пришедшей и комиссии банка
     * @param float $pay_fact -- фактичеси пришедшая сумма
     * @param float $koefficient -- коэффициент комиссии банка
     * @return float -- сумма денег, снятая банком в качестве комиссии
     */
    public static function calc_comission(float $pay_fact, float | null $koefficient): float {
        return 0.0;
        //return ($koefficient*$pay_fact)/(1-$koefficient);
    }



    public static function get_html_transaction_real(string $param): string {
        switch ($param) {
        case 'r':
            return paint(s: "Р.", color: "GREEN", title: "Ознака реальності проводки(r,i)");
        case 'i':
        default:
            return paint(s: $param, color: "GRAY", title: "Ознака реальності проводки(r,i)");
        }
    }



    public static function get_html_transaction_status(string $param): string {
        $title='Стан p-проводиться, t-сторнирована, r-проведена, n-забракована';
        switch ($param) {
            case 'p':
                return paint(s: "проводиться", color: "BLUE", title: $title);
            case 't':
                return paint(s: "сторнирована", color: "RED", title: $title);
            case 'r':
                return paint(s: "проведена", color: "GREEN", title: $title);
            case 'n':
                return paint(s: "забракована", color: "RED", title: $title);
            default:
                return $param;
        }
    }



    // public static function template_has_exist(int $ppp_id, string $template): bool { 
    //     // !!! нужно уменьшить количество запросов, свести к одному. или оставить как есть.
    //     $model = new AbonModel();
    //     $sql = "SELECT `id` FROM `ts_abons_templates` WHERE `ppp_id`=".$ppp_id." AND `template`='".$template."'";
    //     $count = $model->get_count_by_sql($sql);
    //     return $count > 0;
    // }    



    // public static function template_add(int $ppp_id, int $abon_id, string $template): int|false {
    //     $model = new AbonModel();
    //     // Проверка чтобы такой записи небыло
    //     $template = $model->quote($template);
    //     if(!self::template_has_exist($ppp_id, $template)) {
    //         // добавление новой записи
    //         $row = [
    //             'ppp_id' => $ppp_id,
    //             'abon_id' => $abon_id,
    //             'template' => $template,
    //             'modified_uid' => App::get_user_id(),
    //             'modified_date' => time(),
    //             'created_uid' => App::get_user_id(),
    //             'created_date' => time(),
    //         ];
    //         return $model->insert_row('ts_abons_templates', $row);
    //     } else {
    //         MsgQueue::msg(MsgType::ERROR_AUTO, __('Такая запись шаблона в базе есть'));
    //         return false;
    //     }
    // }



    static function format_iban(string $s): string {
        $s = preg_replace('/\s+/', '', $s); // на всякий случай убираем пробелы

        return
            substr($s, 0, 4) . ' ' .
            substr($s, 4, 6) . ' ' .
            substr($s, 10, 5) . ' ' .
            substr($s, 15);
    }


    static function iban_is_valid(string $iban): bool
    {
        // 1. Убираем пробелы и приводим к верхнему регистру
        $iban = strtoupper(preg_replace('/\s+/', '', $iban));

        // 2. Базовая проверка формата
        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]+$/', $iban)) {
            return false;
        }

        // 3. Проверка длины по стране
        $lengths = [
            'UA' => 29,
            'DE' => 22,
            'PL' => 28,
            'GB' => 22,
            'FR' => 27,
            'IT' => 27,
            // при необходимости дополняется
        ];

        $country = substr($iban, 0, 2);
        if (!isset($lengths[$country]) || strlen($iban) !== $lengths[$country]) {
            return false;
        }

        // 4. Перенос первых 4 символов в конец
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // 5. Замена букв на числа (A=10 ... Z=35)
        $numeric = '';
        foreach (str_split($rearranged) as $char) {
            if (ctype_alpha($char)) {
                $numeric .= ord($char) - 55;
            } else {
                $numeric .= $char;
            }
        }

        // 6. MOD-97 (по стандарту IBAN)
        $mod = 0;
        foreach (str_split($numeric, 9) as $chunk) {
            $mod = intval($mod . $chunk) % 97;
        }

        return $mod === 1;
    }



    /**
     * Метод генерирует соответствующий HTML-вывод на основе типа поля и того, была ли найдена запись в счете
     * Используется в Виде.
     * Генерирует соответствующий HTML-контент на основе типа поля и статуса платежной записи (найдена ли она в биллинге)
     * 
     * @param string $field Имя поля
     * @param array &$statement Массив оператора, содержащий платежную запись и результаты поиска (ссылка)
     * @param array|null $found_rec Массив найденных записей, по умолчанию null
     * @param array|null $pay_rec Массив записей платежей, по умолчанию null
     * @return string Возвращает форматированное содержимое поля отображения (HTML-строка)
     */
    public static function get_view_field(int|string $index, string $field, array &$statement, ?array $found_rec = null, ?array $pay_rec = null): string {

        /**
         * Запись с результатами поиска транзакции в биллинге
         * 
         * @var array{
         *      on_billing: true, 
         *      searched_on: string, 
         *      pay: array, 
         *      abon: array, 
         *      aid_list: array, 
         *      template: string} $found_rec
         */
        if (empty($found_rec)) {
            $found_rec = &$statement[Bank::F_FOUND_REC];
        }
        

        /**
        * платеж для внесения или сравнения, который соответствует транзакции из монокарты
        */ 
        if (empty($pay_rec)) {
            $pay_rec = $statement[Bank::F_PAY_REC];
        }
        

        $s = '';

        switch ($field) {

            case Pay::F_ID:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'.h($found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                                // Редактирование платежа
                                . (can_edit(Module::MOD_PAYMENTS) 
                                    ?   '<a href="'.Pay::URI_FORM.'/'.$found_rec[Bank::F_FOUND_PAY][Pay::F_ID].'" '
                                            . 'class="btn btn-sm btn-outline-info align-items-center fs-8 py-0 px-2 ms-3" '
                                            . 'title="' . __('Редактировать платеж') . '" '
                                            . 'target="_blank">'
                                            . '<span class="fw-bold">₴</span>'
                                            . '</a>'
                                    :   '')
                                        // class="btn btn-sm btn-outline-info" 
                                        // title="__('Edit')"><img src="Icons::SRC_EDIT_REC" alt="[Edit]" height="22px"></a>
                            :   ""
                        );
                break;

            case Pay::F_AGENT_ID:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   intval($found_rec[Bank::F_FOUND_PAY][$field])
                            :   intval($pay_rec[$field]) . '<span class="text-secondary small"> | ' . __user(user_id: intval($pay_rec[$field]), field: User::F_NAME_FULL). '</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC.'['.$index.']['.$field.']" value="' . intval($pay_rec[$field]) . '"> '
                        );
                break;
                
            case Pay::F_ABON_ID:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING]
                            ?   (
                                    $found_rec[Bank::F_FOUND_PAY][$field] > 0
                                        ?   url_abon_form($found_rec[Bank::F_FOUND_PAY][$field])
                                        :   '<span title="'.__('Не распределённый платёж').'" class="text-bg-danger py-1 px-4">'.url_abon_form($found_rec[Bank::F_FOUND_PAY][$field]).'<span>'
                                )

                            :   '<div class="row g-0 py-1 align-items-center w-100">'
                                    . '<div class="col-6">'
                                        . '<input type="number" class="form-control min-w-100px" name="'.Bank::POST_REC .'['.$index.']['.$field.']" value="' 
                                        . (empty($found_rec[Bank::F_FOUND_ABON]) 
                                                ?   ($pay_rec[$field] ?? '') 
                                                :   $found_rec[Bank::F_FOUND_ABON][Abon::F_ID]
                                            )
                                        . '"> '
                                    . '</div>'
                                    . '<div class="col-3">'
                                        // Кнопка карточки абонента
                                        . (can_use([Module::MOD_ABON]) && !empty($found_rec[Bank::F_FOUND_ABON][Abon::F_ID])
                                            ?   '<a href="'.Abon::URI_VIEW.'/'.$found_rec[Bank::F_FOUND_ABON][Abon::F_ID].'" '
                                                    . 'class="btn btn-outline-info align-items-center fs-6 py-1 px-2 ms-2" '
                                                    . 'title="' . __('Перейти в карточку Абонента') . CR . $found_rec[Bank::F_FOUND_ABON][Abon::F_ADDRESS] . '" '
                                                    . 'target="_blank">'
                                                    . '<span class="fw-bold">🅐</span></a><!-- ⒶⒶⒶ -->'
                                            :   '')
                                        // Кнопка Список платежей
                                        . (can_view([Module::MOD_PAYMENTS]) && !empty($found_rec[Bank::F_FOUND_ABON][Abon::F_ID])
                                            ?   '<a href="'.Pay::URI_LIST.'/'.$found_rec[Bank::F_FOUND_ABON][Abon::F_ID].'" '
                                                    . 'class="btn btn-outline-info align-items-center fs-6 px-2 py-1 ms-2" '
                                                    . 'title="' . __('Full list of subscriber payments') . '" '
                                                    . 'target="_blank">'
                                                    . '<span class="fw-bold">₴₴</span>'
                                                    . '</a>'
                                            :   '')
                                        // Кнопка Поиска ОДНОГО платежа
                                        . (can_view([Module::MOD_PAYMENTS]) && !empty($found_rec[Bank::F_FOUND_ABON][Abon::F_ID])
                                            ?   '<a href="'.Pay::URI_SEARCH.'?'.http_build_query([
                                                                Pay::F_ABON_ID => $found_rec[Bank::F_FOUND_ABON][Abon::F_ID],
                                                                Pay::F_DATE => $pay_rec[Pay::F_DATE],
                                                                Pay::F_PAY_FAKT => $pay_rec[Pay::F_PAY_FAKT],
                                                                Pay::F_TYPE_ID => $pay_rec[Pay::F_TYPE_ID],
                                                                Pay::F_PPP_ID => $pay_rec[Pay::F_PPP_ID],
                                                                Pay::F_DESCRIPTION => $pay_rec[Pay::F_DESCRIPTION],
                                                            ]) . '" '
                                                    . 'class="btn btn-outline-info align-items-center fs-6 px-2 py-1 ms-2" '
                                                    . 'title="' . __('Поиск одного платежа среди платежей, уже внесённых в биллинг') . '" '
                                                    . 'target="_blank">'
                                                    . '<span class="fw-bold">?1₴</span>'
                                                    . '</a>'
                                            :   '')
                                        // Кнопка Поиска СПИСКА платежей
                                        . (can_view([Module::MOD_PAYMENTS]) && empty($found_rec[Bank::F_FOUND_ABON][Abon::F_ID])
                                            ?   '<a href="'.Pay::URI_SEARCH.'?'.http_build_query([
                                                                Pay::F_DATE => $pay_rec[Pay::F_DATE],
                                                                Pay::F_PAY_FAKT => $pay_rec[Pay::F_PAY_FAKT],
                                                                Pay::F_TYPE_ID => $pay_rec[Pay::F_TYPE_ID],
                                                                Pay::F_PPP_ID => $pay_rec[Pay::F_PPP_ID],
                                                                Pay::F_DESCRIPTION => $pay_rec[Pay::F_DESCRIPTION],
                                                            ]) . '" '
                                                    . 'class="btn btn-outline-info align-items-center fs-6 px-2 py-1 ms-2" '
                                                    . 'title="' . __('Поиск списка платежей среди платежей, уже внесённых в биллинг') . '" '
                                                    . 'target="_blank">'
                                                    . '<span class="fw-bold">?₴₴</span>'
                                                    . '</a>'
                                            :   '')
                                    . '</div>'
                                    . '<div class="col-3 text-end">'
                                        . '<span class="btn btn-warning d-inline-flex align-items-center fs-6 py-1 px-3">'
                                     // . '<span class="badge text-bg-warning d-inline-flex align-items-center fs-6 py-2 px-3">'
                                            . '<label class="hover-pointer mb-0" 
                                                for="'.Bank::POST_REC .'['.$index.']['.Pay::F_POST_SAVE.']">'
                                                . __('Save') 
                                            . '</label>'
                                            . '<input class="form-check-input hover-pointer ms-2 m-0" 
                                                type="checkbox"
                                                '.(!empty($found_rec[Bank::F_FOUND_ABON][Abon::F_ID]) ? "checked" : "").'
                                                id="'.Bank::POST_REC .'['.$index.'][' . Pay::F_POST_SAVE . ']" 
                                                name="'.Bank::POST_REC .'['.$index.'][' . Pay::F_POST_SAVE . ']" 
                                                value="1">'
                                        . '</span>'

                                    . '</div>'
                                    . '<div class="col-12">'
                                        . '<label class="form-check-label me-2 text-secondary">'.$found_rec[Bank::F_FOUND_SEARCHED_ON].'</label>'
                                    . '</div>'
                                    // . (!empty($found_rec[Bank::F_FOUND_AID_LIST]) 
                                    //         ?   '<div class="col-12">'
                                    //                 . '<label class="form-check-label me-2 text-secondary">' . implode(' | ', $found_rec[Bank::F_FOUND_AID_LIST]) . '</label>'
                                    //             . '</div>'
                                    //         :   ""
                                    //   )
                                . '</div>'
                        );
                break;

            case Pay::F_PAY_FAKT:
            case Pay::F_PAY_ACNT:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'.number_format($found_rec[Bank::F_FOUND_PAY][$field], 2, '.', ' ').'</span>'
                            :   '<span title="'.Pay::field_title($field).'">'.number_format($pay_rec[$field], 2, '.', ' ').'</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . ($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_REST:
                // debug($statement, '$statement');
                // debug($found_rec, '$found_rec');
                // debug($pay_rec, '$pay_rec');
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span class="text-nowrap">'.(!is_null($found_rec[Bank::F_FOUND_PAY][$field]) ? number_format($found_rec[Bank::F_FOUND_PAY][$field], 2, '.', ' '):"-" ).'</span>'
                            :   (!empty($pay_rec[$field]) 
                                    ?   '<span title="'.Pay::field_title($field).'">'.number_format($pay_rec[$field], 2, '.', ' ').'</span>'
                                        . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . ($pay_rec[$field]) . '"> '
                                    :   "") 
                                
                        );
                break;

            case Pay::F_DATE:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span class="text-nowrap">'.date('d.m.Y H:i:s', $found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                            :   '<span class="text-nowrap">'.date('d.m.Y H:i:s', $pay_rec[$field]).'</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . ($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_BANK_NO:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span class="text-nowrap" title="'.Pay::field_title($field).'">'.h($found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                            :   '<span class="text-nowrap" title="'.Pay::field_title($field).'">'.h($pay_rec[$field]).'</span>'
                                . '<button type="button" '
                                    . 'class="btn btn-outline-info btn-sm align-items-center fs-8 py-0 px-2 ms-3 copy-btn" data-text="'.h($pay_rec[$field]).'">'
                                    . '<img src="'.Icons::SRC_ICON_CLIPBOARD.'" title="'.__('Скопировать Номер транзакции в clipboard').'" alt="[copy]" height="16">'
                                . '</button>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . h($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_TYPE_ID:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'.h($found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                                . '<span class="text-secondary small"> | ' . Pay::type_title($found_rec[Bank::F_FOUND_PAY][$field]). '</span>'
                            :   '<span title="'.Pay::field_title($field).'">'.h($pay_rec[$field]).'</span>'
                                . '<span class="text-secondary small"> | ' . Pay::type_title($pay_rec[$field]). '</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . h($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_PPP_ID:
                $model = new AbonModel();
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'
                                . h($found_rec[Bank::F_FOUND_PAY][$field])
                                . '</span>'
                                . '<span class="text-secondary small"> | ' . $model->get_ppp_title($found_rec[Bank::F_FOUND_PAY][$field]) . '</span>'
                            :   '<span title="'.Pay::field_title($field).'">'
                                . h($pay_rec[$field])
                                . '</span>'
                                . '<span class="text-secondary small"> | '. $model->get_ppp_title($pay_rec[$field]) .'</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . h($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_DESCRIPTION:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   h($found_rec[Bank::F_FOUND_PAY][$field])
                            :   '<textarea class="form-control"  
                                    name="'.Bank::POST_REC.'['.$index.']['.Pay::F_DESCRIPTION.']" 
                                    rows="'.get_count_rows_for_textarea($pay_rec[Pay::F_DESCRIPTION], 2).'" 
                                    required>'.$pay_rec[Pay::F_DESCRIPTION].'</textarea>'


                        );
                if (!empty($found_rec[Bank::F_FOUND_TEMPLATE])) {
                    $s .=   '<div class="row g-0 py-1 align-items-center w-100">'
                                . '<div class="col-9">'
                                    . '<input type="text" class="form-control min-w-100px" '
                                    . 'name="'.Bank::POST_REC .'['.$index.']['.Bank::F_FOUND_TEMPLATE.']" '
                                    . 'value="' . $found_rec[Bank::F_FOUND_TEMPLATE] . '"> '
                                . '</div>'
                                . '<div class="col-3 text-end">'
                                    . '<span class="btn btn-outline-warning d-inline-flex align-items-center fs-6 py-1 px-3">'
                                        . '<label class="hover-pointer mb-0" 
                                            for="'.Bank::POST_REC .'['.$index.']['.Bank::F_FOUND_TEMPLATE_SAVE.']">'
                                            . __('Save') 
                                        . '</label>'
                                        . '<input class="form-check-input hover-pointer ms-2 m-0" 
                                            type="checkbox"
                                            id="'  .Bank::POST_REC .'['.$index.'][' . Bank::F_FOUND_TEMPLATE_SAVE . ']" 
                                            name="'.Bank::POST_REC .'['.$index.'][' . Bank::F_FOUND_TEMPLATE_SAVE . ']" 
                                            value="1">'
                                    . '</span>'
                                . '</div>'
                            . '</div>';
                }
                break;
            
            case Pay::F_SAVE_DESCR_SUFFIX:
                $s .=   ($pay_rec[Pay::F_SAVE_DESCR_SUFFIX] 
                            ?   '<span title="'.Pay::field_title($field).'">'.h($pay_rec[$field]).'</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC.'['.$index.']['.$field.']" value="' . h($pay_rec[$field]) . '"> '
                            :   ''
                        );
                break;

            default:
                throw new \Exception("необработанное имя поля: " . $field, 1);
                break;
        }

        // Сравнение полей

        /**
         * Поля, подлежащие прямому сравнению в биллинге и в транзакции
         */
        $fields_for_equal = [Pay::F_DATE, Pay::F_PAY_ACNT, Pay::F_PAY_FAKT, Pay::F_REST];

        /**
         * Поля сравниваемые по вхождению фрагмента
         */
        $fields_for_contain = [Pay::F_DESCRIPTION];

        if  (
                $found_rec[Bank::F_FOUND_ON_BILLING] &&
                in_array($field, array_merge($fields_for_equal, $fields_for_contain))
            ) 
        {
            $equal = false;
            if  (
                    /**
                     * Поля, подлежащие прямому сравнению в биллинге и в транзакции
                     */
                    in_array($field, $fields_for_equal)
                )
            {
                if ($pay_rec[$field] == $found_rec[Bank::F_FOUND_PAY][$field]) {
                    $equal = true;
                }
            }
            
            if  (
                    /**
                     * Поля сравниваемые по вхождению фрагмента
                     */
                    in_array($field, $fields_for_contain)
                )
            {
                if (str_contains($found_rec[Bank::F_FOUND_PAY][$field], $pay_rec[$field])) {
                    $equal = true;
                }
            }

            if ($equal) {
                $s = '<span class="text-success">'.$s.'</span>';
            } else {

                // debug(['$found_rec'=>$found_rec, '$pay_rec'=>$pay_rec]);

                $s = '<span class="text-warning" title="'
                                . 'В базе: '.CR
                                . ''.$found_rec[Bank::F_FOUND_PAY][$field].''.CR.CR
                                . 'Транзакция: '.CR
                                . ''.$pay_rec[$field].'">'.$s.'</span>';
            }
        }
        
        return $s;
        
    }



    /**
     * Формирует строку банковского номера (платёжного документа).
     * 
     * Формат: {дата}{сумма_факт}{знак_остатка}{сумма_остатка}
     * где:
     *   - дата: YYYYMMDDHHMMSS
     *   - суммы: в копейках, дополненные ведущими нулями до заданной длины
     *   - знак остатка: '+' если остаток положительный, '-' если отрицательный
     * 
     * @param int $date_ts временная метка даты формирования
     * @param float $pay_fact фактическая сумма платежа
     * @param float $pay_rest остаток суммы (положительный/отрицательный)
     * @return string строка банковского номера
     */
    public static function make_bank_no(int $date_ts, float $pay_fact, float $pay_rest) { 
        return  date('YmdHis', $date_ts) 
                . num_len(round($pay_fact * 100), App::get_config('bank_pay_num_len'))
                . ((sign($pay_rest) == 1) ? '+' : '-')
                . num_len(round(abs($pay_rest) * 100), App::get_config('bank_pay_num_len'));
    }





}