<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Monocard.php
 *  Path    : config/Monocard.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 01 Jan 2026 00:28:59
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Интерфейсный класс для работы с API monobank.ua
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace config;

use app\models\AbonModel;
use billing\core\base\Lang;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Abon;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\User;

/*

GET https://api.monobank.ua/personal/statement/{account}/{from}/{to}

Response Schema: application/json
Content type            application/json
[
    {
        "id": "ZuHWzqkKGVo=",                   id	                string          Унікальний id транзакції
        "time": 1554466347,                     time	            number <int64>  Час транзакції в секундах в форматі Unix time
        "description": "Покупка щастя",         description	        string          Опис транзакцій
        "mcc": 7997,                            mcc	                number <int32>  Код типу транзакції (Merchant Category Code), відповідно ISO 18245
        "originalMcc": 7997,                    originalMcc	        number <int32>  Оригінальний код типу транзакції (Merchant Category Code), відповідно ISO 18245
        "hold": false,                          hold	            boolean         Статус блокування суми (детальніше у wiki)
        "amount": -95000,                       amount	            number <int64>  Сума у валюті рахунку в мінімальних одиницях валюти (копійках, центах)
        "operationAmount": -95000,              operationAmount	    number <int64>  Сума у валюті транзакції в мінімальних одиницях валюти (копійках, центах)
        "currencyCode": 980,                    currencyCode	    number <int32>  Код валюти рахунку відповідно ISO 4217
        "commissionRate": 0,                    commissionRate	    number <int64>  Розмір комісії в мінімальних одиницях валюти (копійках, центах)
        "cashbackAmount": 19000,                cashbackAmount	    number <int64>  Розмір кешбеку в мінімальних одиницях валюти (копійках, центах)
        "balance": 10050000,                    balance	            number <int64>  Баланс рахунку в мінімальних одиницях валюти (копійках, центах)
        "comment": "За каву",                   comment	            string          Коментар до переказу, уведений користувачем. Якщо не вказаний, поле буде відсутнім
        "receiptId": "XXXX-XXXX-XXXX-XXXX",     receiptId	        string          Номер квитанції для check.gov.ua. Поле може бути відсутнім
        "invoiceId": "2103.в.27",               invoiceId	        string          Номер квитанції ФОПа, приходить у випадку якщо це операція із зарахуванням коштів
        "counterEdrpou": "3096889974",          counterEdrpou	    string          ЄДРПОУ контрагента, присутній лише для елементів виписки рахунків ФОП
        "counterIban": "UA89899998000035...",   counterIban	        string          IBAN контрагента, присутній лише для елементів виписки рахунків ФОП
        "counterName": "ТОВ «ВОРОНА»"           counterName	        string          Найменування контрагента
    }
]
    
*/


class MonoCard
{

    const URI_CLI_INFO = "https://api.monobank.ua/personal/client-info";



    /**
     * Поля клієнтської інформації
     */
    const F_CLIENT_ID    = 'clientId';      // string  Унікальний ідентифікатор клієнта
    const F_NAME         = 'name';          // string  Ім'я клієнта
    const F_WEB_HOOK_URL = 'webHookUrl';   // string  URL для web hook
    const F_PERMISSIONS  = 'permissions';   // string  
    const F_ACCOUNTS     = 'accounts';      // array   Масив карткових рахунків клієнта



    /**
     * Поля рахунку/картки
     * Поля массива F_ACCOUNTS
     */
    const F_CARD_SEND_ID      = 'sendId';               // string  Унікальний ідентифікатор клиента (F_CLIENT_ID)
    const F_CARD_ID           = 'id';                   // string  Унікальний ідентифікатор рахунку
    const F_CARD_IBAN         = 'iban';                 // string  IBAN рахунку
    const F_CARD_BALANCE      = 'balance';              // number  Баланс рахунку
    const F_CARD_CREDIT_LIMIT = 'creditLimit';          // number  Кредитний ліміт рахунку
    const F_CARD_TYPE         = 'type';                 // string  Тип карты (чёрная, ...)
    const F_CARD_CASHBACK_TYPE = 'cashbackType';        // string  Тип кешбеку
    const F_CARD_CURRENCY_CODE = 'currencyCode';        // number  Код валюти рахунку відповідно ISO 4217
    const F_CARD_MASKED_PAN   = 'maskedPan';            // string  Замаскований номер картки



    /**
     *  statement
     *  (
     *      [id] => LOUN56Amzsp2gaML
     *      [time] => 1672499764
     *      [description] => Від: Костянтин Мухін
     *      [comment] => Порт 19846
     *      [mcc] => 4829
     *      [originalMcc] => 4829
     *      [amount] => 65000
     *      [operationAmount] => 65000
     *      [currencyCode] => 980
     *      [commissionRate] => 0
     *      [cashbackAmount] => 0
     *      [balance] => 143791
     *      [hold] => 1
     *  )
     */



    /**
     * Поля транзакций
     */
    const F_BANK_ID          = 'id'; 	                // string          Унікальний id транзакції
    const F_TIME             = 'time'; 	                // number <int64>  Час транзакції в секундах в форматі Unix time
    const F_DESCRIPTION      = 'description'; 	        // string          Опис транзакції
    const F_MCC              = 'mcc'; 	                // number <int32>  Код типу транзакції (Merchant Category Code), відповідно ISO 18245
    const F_ORIGINAL_MCC     = 'originalMcc'; 	        // number <int32>  Оригінальний код типу транзакції (Merchant Category Code), відповідно ISO 18245
    const F_HOLD             = 'hold'; 	                // boolean         Статус блокування суми (детальніше у wiki)
    const F_AMOUNT           = 'amount'; 	            // number <int64>  Сума у валюті рахунку в мінімальних одиницях валюти (копійках, центах)
    const F_OPERATION_AMOUNT = 'operationAmount'; 	    // number <int64>  Сума у валюті транзакції в мінімальних одиницях валюти (копійках, центах)
    const F_CURRENCY_CODE    = 'currencyCode'; 	        // number <int32>  Код валюти рахунку відповідно ISO 4217
    const F_COMMISSION_RATE  = 'commissionRate'; 	    // number <int64>  Розмір комісії в мінімальних одиницях валюти (копійках, центах)
    const F_CASHBACK_AMOUNT  = 'cashbackAmount'; 	    // number <int64>  Розмір кешбеку в мінімальних одиницях валюти (копійках, центах)
    const F_BALANCE          = 'balance'; 	            // number <int64>  Баланс рахунку в мінімальних одиницях валюти (копійках, центах)
    const F_COMMENT          = 'comment'; 	            // string          Коментар до переказу, уведений користувачем. Якщо не вказаний, поле буде відсутнім
    const F_RECEIPT_ID       = 'receiptId'; 	        // string          Номер квитанції для check.gov.ua. Поле може бути відсутнім
    const F_INVOICE_ID       = 'invoiceId'; 	        // string          Номер квитанції ФОПа, приходить у випадку якщо це операція із зарахуванням коштів
    const F_COUNTER_EDRPOU   = 'counterEdrpou'; 	    // string          ЄДРПОУ контрагента, присутній лише для елементів виписки рахунків ФОП
    const F_COUNTER_IBAN     = 'counterIban'; 	        // string          IBAN контрагента, присутній лише для елементів виписки рахунків ФОП
    const F_COUNTER_NAME     = 'counterName'; 	        // string          Найменування контрагента





    const TEXT_FIELDS = [
        self::F_DESCRIPTION,
        self::F_COMMENT,
        self::F_COUNTER_NAME
    ];



    const DESCRIPTIONS = [

        Lang::C_EN => [
            self::F_BANK_ID          => 'Unique transaction id',
            self::F_TIME             => 'Transaction time in seconds in Unix time format',
            self::F_DESCRIPTION      => 'Transaction description',
            self::F_MCC              => 'Transaction type code (Merchant Category Code), according to ISO 18245',
            self::F_ORIGINAL_MCC     => 'Original transaction type code (Merchant Category Code), according to ISO 18245',
            self::F_HOLD             => 'Amount blocking status (more details in wiki)',
            self::F_AMOUNT           => 'Amount in account currency in minimum currency units (kopecks, cents)',
            self::F_OPERATION_AMOUNT => 'Amount in transaction currency in minimum currency units (kopecks, cents)',
            self::F_CURRENCY_CODE    => 'Account currency code according to ISO 4217',
            self::F_COMMISSION_RATE  => 'Commission amount in minimum currency units (kopecks, cents)',
            self::F_CASHBACK_AMOUNT  => 'Cashback amount in minimum currency units (kopecks, cents)',
            self::F_BALANCE          => 'Account balance in minimum currency units (kopecks, cents)',
            self::F_COMMENT          => 'Comment to the transfer entered by the user. If not specified, the field will be missing',
            self::F_RECEIPT_ID       => 'Receipt number for check.gov.ua. The field may be missing',
            self::F_INVOICE_ID       => 'Individual entrepreneur receipt number, comes if this is a transaction with a transfer of funds',
            self::F_COUNTER_EDRPOU   => 'Counterparty\'s EDRPOU, present only for individual entrepreneur account statement elements',
            self::F_COUNTER_IBAN     => 'Counterparty\'s IBAN, present only for individual entrepreneur account statement elements',
            self::F_COUNTER_NAME     => 'Counterparty name',
        ],

        Lang::C_RU => [
            self::F_BANK_ID               => 'Уникальный id транзакции',
            self::F_TIME             => 'Время транзакции в секундах в формате Unix time',
            self::F_DESCRIPTION      => 'Описание транзакции',
            self::F_MCC              => 'Код типа транзакции (Merchant Category Code), согласно ISO 18245',
            self::F_ORIGINAL_MCC     => 'Оригинальный код типа транзакции (Merchant Category Code), согласно ISO 18245',
            self::F_HOLD             => 'Статус блокировки суммы (подробнее в wiki)',
            self::F_AMOUNT           => 'Сумма в валюте счета в минимальных единицах валюты (копейках, центах)',
            self::F_OPERATION_AMOUNT => 'Сумма в валюте транзакции в минимальных единицах валюты (копейках, центах)',
            self::F_CURRENCY_CODE    => 'Код валюты счета согласно ISO 4217',
            self::F_COMMISSION_RATE  => 'Размер комиссии в минимальных единицах валюты (копейках, центах)',
            self::F_CASHBACK_AMOUNT  => 'Размер кэшбека в минимальных единицах валюты (копейках, центах)',
            self::F_BALANCE          => 'Баланс счета в минимальных единицах валюты (копейках, центах)',
            self::F_COMMENT          => 'Комментарий к переводу, введенный пользователем. Если не указан, поле будет отсутствовать',
            self::F_RECEIPT_ID       => 'Номер квитанции для check.gov.ua. Поле может отсутствовать',
            self::F_INVOICE_ID       => 'Номер квитанции ФЛП приходит в случае если это операция с зачислением средств',
            self::F_COUNTER_EDRPOU   => 'ЕГРПОУ контрагента присутствует только для элементов выписки счетов ФЛП',
            self::F_COUNTER_IBAN     => 'IBAN контрагента, присутствует только для элементов выписки счетов ФЛП',
            self::F_COUNTER_NAME     => 'Наименование контрагента',
        ],

        Lang::C_UK => [
            self::F_BANK_ID               => 'Унікальний id транзакції',
            self::F_TIME             => 'Час транзакції в секундах в форматі Unix time',
            self::F_DESCRIPTION      => 'Опис транзакції',
            self::F_MCC              => 'Код типу транзакції (Merchant Category Code), відповідно ISO 18245',
            self::F_ORIGINAL_MCC     => 'Оригінальний код типу транзакції (Merchant Category Code), відповідно ISO 18245',
            self::F_HOLD             => 'Статус блокування суми (детальніше у wiki)',
            self::F_AMOUNT           => 'Сума у валюті рахунку в мінімальних одиницях валюти (копійках, центах)',
            self::F_OPERATION_AMOUNT => 'Сума у валюті транзакції в мінімальних одиницях валюти (копійках, центах)',
            self::F_CURRENCY_CODE    => 'Код валюти рахунку відповідно ISO 4217',
            self::F_COMMISSION_RATE  => 'Розмір комісії в мінімальних одиницях валюти (копійках, центах)',
            self::F_CASHBACK_AMOUNT  => 'Розмір кешбеку в мінімальних одиницях валюти (копійках, центах)',
            self::F_BALANCE          => 'Баланс рахунку в мінімальних одиницях валюти (копійках, центах)',
            self::F_COMMENT          => 'Коментар до переказу, уведений користувачем. Якщо не вказаний, поле буде відсутнім',
            self::F_RECEIPT_ID       => 'Номер квитанції для check.gov.ua. Поле може бути відсутнім',
            self::F_INVOICE_ID       => 'Номер квитанції ФОПа, приходить у випадку якщо це операція із зарахуванням коштів',
            self::F_COUNTER_EDRPOU   => 'ЄДРПОУ контрагента, присутній лише для елементів виписки рахунків ФОП',
            self::F_COUNTER_IBAN     => 'IBAN контрагента, присутній лише для елементів виписки рахунків ФОП',
            self::F_COUNTER_NAME     => 'Найменування контрагента',
        ],
        
    ];



    /**
     * Возвращает описание поля транзакции на выбранном языке
     * 
     * @param string $field Название поля, для которого нужно получить описание
     * @param string|null $lang_code Код языка (например, 'en', 'ru', 'uk'), если не указан, используется текущий язык системы
     * @return string Описание поля на выбранном языке, если описание не найдено, возвращается имя поля
     */
    public static function field_descr(string $field, string|null $lang_code = null): string {
        // Если код языка не задан, используем текущий язык системы
        if ($lang_code === null) {
            $lang_code = Lang::code();
        }
        // Получаем описание поля для указанного языка из массива описаний
        // Если описание не найдено, возвращаем имя поля как есть
        return self::DESCRIPTIONS[$lang_code][$field] ?? $field;
    }



    public static function get_headers(string $card_token): array {
        $headers = array
        (
            //'Accept: text/xml,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
            //'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
            //'Accept-Encoding: deflate',
            //'Accept-Charset: utf-8;q=0.7,*;q=0.7',
            //'Content-type: text/xml;charset=UTF-8',
            'Content-type: application/json;charset=UTF-8',
            'X-Token: '.$card_token
        );
        return $headers;
    }



    /**
     * Нормализует информацию о клиенте, преобразуя значения баланса и кредитного лимита
     * для каждой учетной записи из копеек в рубли/гривни (делит на 100)
     * 
     * @param array &$client_info Массив информации о клиенте, содержащий ключ 'accounts' с данными об учетных записях
     * @return void
     */
    public static function normalize_client_info(array &$client_info): void {
        // Проходит по всем учетным записям клиента и преобразует баланс и кредитный лимит из копеек в рубли
        foreach ($client_info[MonoCard::F_ACCOUNTS] as &$card) {
            $card[self::F_CARD_BALANCE] = floatval($card[self::F_CARD_BALANCE])/100;
            $card[self::F_CARD_CREDIT_LIMIT] = floatval($card[self::F_CARD_CREDIT_LIMIT])/100;
        }
    }



    /**
     * Summary of get_card_info
     * @param string $token
     * @return array{
     *      client: mixed, 
     *      connect: mixed}
     */
    public static function get_card_info(string $token): array {

        $headers = self::get_headers($token);

        $ch = curl_init( self::URI_CLI_INFO);
        curl_setopt($ch, CURLOPT_POST,              0); //Использовать метод POST
        curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers); // заголовки
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1); // возврат результата в переменную
        curl_setopt($ch, CURLOPT_HEADER,            0); // не включать заголовки в вывод
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1); // проверять SSL сертификат

        $res = curl_exec($ch);
        $bank_cli_info =  json_decode($res, true);
        $connect_info = curl_getinfo($ch);
        curl_close($ch);
        if ($bank_cli_info) {
            self::normalize_client_info($bank_cli_info);
        }
        return [
            'client'  => $bank_cli_info,
            'connect' => $connect_info
        ];
    }        



    public static function normalize_statements(array &$statements): void {
        foreach ($statements as &$statement) {
            $statement[self::F_AMOUNT]           = floatval($statement[self::F_AMOUNT])/100;
            $statement[self::F_OPERATION_AMOUNT]  = floatval($statement[self::F_OPERATION_AMOUNT])/100;
            $statement[self::F_COMMISSION_RATE]   = floatval($statement[self::F_COMMISSION_RATE])/100;
            $statement[self::F_CASHBACK_AMOUNT]   = floatval($statement[self::F_CASHBACK_AMOUNT])/100;
            $statement[self::F_BALANCE]          = floatval($statement[self::F_BALANCE])/100;
            $statement[self::F_CASHBACK_AMOUNT]   = floatval($statement[self::F_CASHBACK_AMOUNT])/100;
        }
    }


    
    /**
     * Получение выписки по операциям
     * @param string $token Токен доступа
     * @param int $date1 Начальная дата (в формате timestamp)
     * @param int $date2 Конечная дата (в формате timestamp)
     * @param string $api_url URL API
     * @return array{connect:mixed,statements:array} Массив с информацией о соединении и выпиской по операциям
     */
    public static function get_statements(string $token, int $date1, int $date2, string $api_url): array {

        $headers = MonoCard::get_headers($token);

        // Формирование URL запроса, добавляем 1 день к конечной дате, чтобы включить date2 в выборку
        $request_url = $api_url . "" . "0" . "/" . $date1 . "/" . ($date2 + (1 * 24 * 60 * 60));

        $ch = curl_init($request_url);
        curl_setopt($ch, CURLOPT_POST, 0); // Не использовать метод POST
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // Тело запроса (закомментировано)
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // HTTP заголовки
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Вернуть результат в переменную
        curl_setopt($ch, CURLOPT_HEADER, 0); // Не включать заголовки в вывод
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // Проверять SSL сертификат

        // Выполнить cURL запрос и получить информацию о соединении и ответ
        $connect_info = curl_getinfo($ch);
        $res = curl_exec($ch);
        $statements = json_decode($res, true);
        curl_close($ch);

        // Нормализовать данные выписки (преобразовать суммы из копеек в гривны)
        self::normalize_statements($statements);

        return [
            'connect' => $connect_info, // Информация о соединении
            'statements' => $statements, // Данные по операциям
        ];
    }



    /**
     * Получает отображаемое содержимое поля просмотра
     * Используется в Виде.
     * Генерирует соответствующий HTML-контент на основе типа поля и статуса платежной записи (найдена ли она в биллинге)
     * 
     * @param string $field Имя поля
     * @param array &$statement Массив оператора, содержащий платежную запись и результаты поиска (ссылка)
     * @return string Возвращает форматированное содержимое поля отображения (HTML-строка)
     */
    public static function get_view_field(int|string $index, string $field, array &$statement): string {

        /**
         * Запись с результатами поиска транзакции в биллинге
         * 
         * @var array{on_billing: true, 
         *      searched_on: string, 
         *      pay: array, 
         *      abon: array, 
         *      aid_list: array, 
         *      template: string} $found_rec
         */
        $found_rec = &$statement[Bank::F_FOUND_REC];

        /**
        * платеж для внесения или сравнения, который соответствует транзакции из монокарты
        */ 
        $pay_rec = &$statement[Bank::F_PAY_REC];

        $s = '';

        switch ($field) {

            case Pay::F_ID:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'.h($found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
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
                            ?   '<span title="'.Pay::field_title($field).'">'.h($found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                            :   '<div class="row g-0 py-1 align-items-center w-100">'
                                    . '<div class="col-9">'
                                        . '<input type="number" class="form-control min-w-100px" name="'.Bank::POST_REC .'['.$index.']['.$field.']" value="' 
                                        . (empty($found_rec[Bank::F_FOUND_ABON]) 
                                                ?   ($pay_rec[$field] ?? '') 
                                                :   $found_rec[Bank::F_FOUND_ABON][Abon::F_ID]
                                            )
                                        . '"> '
                                    . '</div>'
                                    . '<div class="col-3 text-end">'
                                        . '<span class="badge text-bg-warning d-inline-flex align-items-center fs-6 py-2 px-3">'
                                            . '<label class="hover-pointer mb-0" 
                                                for="'.Bank::POST_REC .'['.$index.']['.Pay::F_POST_SAVE.']">'
                                                . __('Save') 
                                            . '</label>'
                                            . '<input class="form-check-input hover-pointer ms-2 m-0" 
                                                type="checkbox"
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
                            ?   '<span title="'.Pay::field_title($field).'">'.number_format($found_rec[Bank::F_FOUND_PAY][$field], 2).'</span>'
                            :   '<span title="'.Pay::field_title($field).'">'.number_format($pay_rec[$field], 2).'</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . ($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_DATE:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'.date('d.m.Y H:i:s', $found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                            :   '<span title="'.Pay::field_title($field).'">'.date('d.m.Y H:i:s', $pay_rec[$field]).'</span>'
                                . '<input type="hidden" name="'.Bank::POST_REC .'['.$index.'][' . $field . ']" value="' . ($pay_rec[$field]) . '"> '
                        );
                break;

            case Pay::F_BANK_NO:
                $s .=   ($found_rec[Bank::F_FOUND_ON_BILLING] 
                            ?   '<span title="'.Pay::field_title($field).'">'.h($found_rec[Bank::F_FOUND_PAY][$field]).'</span>'
                            :   '<span title="'.Pay::field_title($field).'">'.h($pay_rec[$field]).'</span>'
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
                break;
            
            default:
                throw new \Exception("необработанное имя поля: " . $field, 1);
                break;
        }

        // Сравнение полей
        if  (
                $found_rec[Bank::F_FOUND_ON_BILLING] &&
                // Поля, подлежащие сравнению в биллинге и в транзакции
                in_array($field, [Pay::F_DATE, Pay::F_PAY_ACNT, Pay::F_PAY_FAKT, Pay::F_DESCRIPTION])
            ) 
        {
            if ($pay_rec[$field] == $found_rec[Bank::F_FOUND_PAY][$field]) {
                $s = '<span class="text-success">'.$s.'</span>';
            } else {
                $s = '<span class="text-warning" title="В базе: ['.$found_rec[Bank::F_FOUND_PAY][$field].']'.CR
                                                        . 'Транзакция: ['.$pay_rec[$field].']">'.$s.'</span>';
            }
        }
        
        return $s;
        
    }





}
