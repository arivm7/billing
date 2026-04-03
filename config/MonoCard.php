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
use billing\core\App;
use billing\core\base\Lang;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Abon;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\User;

/*

$accounts:

Array
(
    [clientId] => 8Jieiw7j9D
    [name] => Андрій В.
    [webHookUrl] => 
    [permissions] => psfjm
    [accounts] => Array
        (
            [0] => Array
                (
                    [id] => aXpuHyWasl;dkf;asMkO0A
                    [sendId] => 8Jlskdjf9D
                    [currencyCode] => 980
                    [cashbackType] => UAH
                    [balance] => 125631.09
                    [creditLimit] => 125000
                    [maskedPan] => Array
                        (
                            [0] => 444111******9846
                            [1] => 444111******3107
                        )

                    [type] => black
                    [iban] => UA823220010000026208310755488
                )
            ...

        )

)



GET https://api.monobank.ua/personal/statement/{account_id}/{from}/{to}

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
    const F_WEB_HOOK_URL = 'webHookUrl';    // string  URL для web hook
    const F_PERMISSIONS  = 'permissions';   // string  
    const F_ACCOUNTS     = 'accounts';      // array   Масив карткових рахунків клієнта



    /**
     * Поля рахунку/картки
     * Поля массива F_ACCOUNTS
     */
    const F_CARD_ID             = 'id';                 // string  Унікальний ідентифікатор рахунку
    const F_CARD_SEND_ID        = 'sendId';             // string  Унікальний ідентифікатор клиента (F_CLIENT_ID)
    const F_CARD_IBAN           = 'iban';               // string  IBAN рахунку
    const F_CARD_BALANCE        = 'balance';            // number  Баланс рахунку
    const F_CARD_CREDIT_LIMIT   = 'creditLimit';        // number  Кредитний ліміт рахунку
    const F_CARD_TYPE           = 'type';               // string  Тип карты (чёрная, ...)
    const F_CARD_CASHBACK_TYPE  = 'cashbackType';       // string  Тип кешбеку
    const F_CARD_CURRENCY_CODE  = 'currencyCode';       // number  Код валюти рахунку відповідно ISO 4217
    const F_CARD_MASKED_PAN     = 'maskedPan';          // string  Замаскований номер картки




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

    /**
     * Добавляемое поле
     */
    const F_ACCOUNT_ID       = 'up_account_id';         // string          ID банковской карты или рассчетного счета к которому относится транзакция



    const TEXT_FIELDS = [
        self::F_DESCRIPTION,
        self::F_COMMENT,
        self::F_COUNTER_NAME
    ];



    /**
     * Описания полей
     */
    const FIELD_TITLES = [

        // =========================
        // CLIENT INFO
        // =========================
        self::F_CLIENT_ID => [
            Lang::C_EN => 'Client ID',
            Lang::C_RU => 'ID клиента',
            Lang::C_UK => 'ID клієнта',
        ],

        self::F_NAME => [
            Lang::C_EN => 'Client name',
            Lang::C_RU => 'Имя клиента',
            Lang::C_UK => 'Ім’я клієнта',
        ],

        self::F_WEB_HOOK_URL => [
            Lang::C_EN => 'Webhook URL',
            Lang::C_RU => 'URL вебхука',
            Lang::C_UK => 'URL вебхука',
        ],

        self::F_PERMISSIONS => [
            Lang::C_EN => 'Permissions',
            Lang::C_RU => 'Права доступа',
            Lang::C_UK => 'Права доступу',
        ],

        self::F_ACCOUNTS => [
            Lang::C_EN => 'Accounts list',
            Lang::C_RU => 'Список счетов',
            Lang::C_UK => 'Список рахунків',
        ],


        // =========================
        // ACCOUNT / CARD INFO
        // =========================
        self::F_CARD_ID => [
            Lang::C_EN => 'Account ID',
            Lang::C_RU => 'ID счета',
            Lang::C_UK => 'ID рахунку',
        ],

        self::F_CARD_SEND_ID => [
            Lang::C_EN => 'Client ID',
            Lang::C_RU => 'ID клиента',
            Lang::C_UK => 'ID клієнта',
        ],

        self::F_CARD_IBAN => [
            Lang::C_EN => 'IBAN',
            Lang::C_RU => 'IBAN',
            Lang::C_UK => 'IBAN',
        ],

        self::F_CARD_BALANCE => [
            Lang::C_EN => 'Account balance',
            Lang::C_RU => 'Баланс счета',
            Lang::C_UK => 'Баланс рахунку',
        ],

        self::F_CARD_CREDIT_LIMIT => [
            Lang::C_EN => 'Credit limit',
            Lang::C_RU => 'Кредитный лимит',
            Lang::C_UK => 'Кредитний ліміт',
        ],

        self::F_CARD_TYPE => [
            Lang::C_EN => 'Card type',
            Lang::C_RU => 'Тип карты',
            Lang::C_UK => 'Тип картки',
        ],

        self::F_CARD_CASHBACK_TYPE => [
            Lang::C_EN => 'Cashback type',
            Lang::C_RU => 'Тип кешбэка',
            Lang::C_UK => 'Тип кешбеку',
        ],

        self::F_CARD_CURRENCY_CODE => [
            Lang::C_EN => 'Currency code',
            Lang::C_RU => 'Код валюты',
            Lang::C_UK => 'Код валюти',
        ],

        self::F_CARD_MASKED_PAN => [
            Lang::C_EN => 'Masked PAN',
            Lang::C_RU => 'Замаскированный номер карты',
            Lang::C_UK => 'Замаскований номер картки',
        ],


        // =========================
        // TRANSACTIONS
        // =========================
        self::F_BANK_ID => [
            Lang::C_EN => 'Transaction id',
            Lang::C_RU => 'ID транзакции',
            Lang::C_UK => 'ID транзакції',
        ],

        self::F_TIME => [
            Lang::C_EN => 'Transaction time',
            Lang::C_RU => 'Время транзакции',
            Lang::C_UK => 'Час транзакції',
        ],

        self::F_DESCRIPTION => [
            Lang::C_EN => 'Transaction description',
            Lang::C_RU => 'Описание транзакции',
            Lang::C_UK => 'Опис транзакції',
        ],

        self::F_MCC => [
            Lang::C_EN => 'Merchant Category Code',
            Lang::C_RU => 'Код MCC',
            Lang::C_UK => 'Код MCC',
        ],

        self::F_ORIGINAL_MCC => [
            Lang::C_EN => 'Original Merchant Category Code',
            Lang::C_RU => 'Оригинальный MCC',
            Lang::C_UK => 'Оригінальний MCC',
        ],

        self::F_HOLD => [
            Lang::C_EN => 'Hold status',
            Lang::C_RU => 'Статус холда',
            Lang::C_UK => 'Статус холду',
        ],

        self::F_AMOUNT => [
            Lang::C_EN => 'Amount in account currency',
            Lang::C_RU => 'Сумма в валюте счета',
            Lang::C_UK => 'Сума у валюті рахунку',
        ],

        self::F_OPERATION_AMOUNT => [
            Lang::C_EN => 'Amount in transaction currency',
            Lang::C_RU => 'Сумма в валюте операции',
            Lang::C_UK => 'Сума у валюті операції',
        ],

        self::F_CURRENCY_CODE => [
            Lang::C_EN => 'Currency code',
            Lang::C_RU => 'Код валюты',
            Lang::C_UK => 'Код валюти',
        ],

        self::F_COMMISSION_RATE => [
            Lang::C_EN => 'Commission amount',
            Lang::C_RU => 'Комиссия',
            Lang::C_UK => 'Комісія',
        ],

        self::F_CASHBACK_AMOUNT => [
            Lang::C_EN => 'Cashback amount',
            Lang::C_RU => 'Кешбэк',
            Lang::C_UK => 'Кешбек',
        ],

        self::F_BALANCE => [
            Lang::C_EN => 'Account balance after',
            Lang::C_RU => 'Баланс после операции',
            Lang::C_UK => 'Баланс після операції',
        ],

        self::F_COMMENT => [
            Lang::C_EN => 'User comment',
            Lang::C_RU => 'Комментарий пользователя',
            Lang::C_UK => 'Коментар користувача',
        ],

        self::F_RECEIPT_ID => [
            Lang::C_EN => 'Receipt ID',
            Lang::C_RU => 'ID чека',
            Lang::C_UK => 'ID чека',
        ],

        self::F_INVOICE_ID => [
            Lang::C_EN => 'Invoice ID',
            Lang::C_RU => 'ID инвойса',
            Lang::C_UK => 'ID інвойсу',
        ],

        self::F_COUNTER_EDRPOU => [
            Lang::C_EN => 'Counterparty EDRPOU',
            Lang::C_RU => 'ЕДРПОУ контрагента',
            Lang::C_UK => 'ЄДРПОУ контрагента',
        ],

        self::F_COUNTER_IBAN => [
            Lang::C_EN => 'Counterparty IBAN',
            Lang::C_RU => 'IBAN контрагента',
            Lang::C_UK => 'IBAN контрагента',
        ],

        self::F_COUNTER_NAME => [
            Lang::C_EN => 'Counterparty name',
            Lang::C_RU => 'Название контрагента',
            Lang::C_UK => 'Назва контрагента',
        ],


        // =========================
        // EXTENSION FIELD
        // =========================
        self::F_ACCOUNT_ID => [
            Lang::C_EN => 'Account ID',
            Lang::C_RU => 'ID счета/карты',
            Lang::C_UK => 'ID рахунку/картки',
        ],

    ];




    /**
     * Возвращает описание поля транзакции на выбранном языке
     * 
     * @param string $field Название поля, для которого нужно получить описание
     * @param string|null $lang_code Код языка (например, 'en', 'ru', 'uk'), если не указан, используется текущий язык системы
     * @return string Описание поля на выбранном языке, если описание не найдено, возвращается имя поля
     */
    public static function field_title(string $field, ?string $lang_code = null): string
    {
        if (empty($lang_code)) { $lang_code = App::lang(); }

        return (empty(self::FIELD_TITLES[$field])
                    ?   ''
                    :   self::FIELD_TITLES[$field][$lang_code]
                        ?? self::FIELD_TITLES[$field][Lang::C_RU]
                        ?? self::FIELD_TITLES[$field][Lang::C_UK]
                        ?? self::FIELD_TITLES[$field][Lang::C_EN]
                        ?? $field
                );
    }
    


    const FIELD_DESCRIPTIONS = [

        // =========================
        // CLIENT INFO
        // =========================

        self::F_CLIENT_ID => [
            Lang::C_EN => 'Client ID',
            Lang::C_RU => 'ID клиента',
            Lang::C_UK => 'ID клієнта',
        ],

        self::F_NAME => [
            Lang::C_EN => 'Client name',
            Lang::C_RU => 'Имя клиента',
            Lang::C_UK => 'Ім’я клієнта',
        ],

        self::F_WEB_HOOK_URL => [
            Lang::C_EN => 'Webhook URL',
            Lang::C_RU => 'URL вебхука',
            Lang::C_UK => 'URL вебхука',
        ],

        self::F_PERMISSIONS => [
            Lang::C_EN => 'Permissions',
            Lang::C_RU => 'Права доступа',
            Lang::C_UK => 'Права доступу',
        ],

        self::F_ACCOUNTS => [
            Lang::C_EN => 'Accounts',
            Lang::C_RU => 'Счета',
            Lang::C_UK => 'Рахунки',
        ],


        // =========================
        // CARD / ACCOUNT
        // =========================

        self::F_CARD_ID => [
            Lang::C_EN => 'Account ID',
            Lang::C_RU => 'ID счета',
            Lang::C_UK => 'ID рахунку',
        ],

        self::F_CARD_SEND_ID => [
            Lang::C_EN => 'Client send ID',
            Lang::C_RU => 'ID клиента отправителя',
            Lang::C_UK => 'ID клієнта відправника',
        ],

        self::F_CARD_IBAN => [
            Lang::C_EN => 'IBAN',
            Lang::C_RU => 'IBAN',
            Lang::C_UK => 'IBAN',
        ],

        self::F_CARD_BALANCE => [
            Lang::C_EN => 'Balance',
            Lang::C_RU => 'Баланс',
            Lang::C_UK => 'Баланс',
        ],

        self::F_CARD_CREDIT_LIMIT => [
            Lang::C_EN => 'Credit limit',
            Lang::C_RU => 'Кредитный лимит',
            Lang::C_UK => 'Кредитний ліміт',
        ],

        self::F_CARD_TYPE => [
            Lang::C_EN => 'Card type',
            Lang::C_RU => 'Тип карты',
            Lang::C_UK => 'Тип картки',
        ],

        self::F_CARD_CASHBACK_TYPE => [
            Lang::C_EN => 'Cashback type',
            Lang::C_RU => 'Тип кешбэка',
            Lang::C_UK => 'Тип кешбеку',
        ],

        self::F_CARD_CURRENCY_CODE => [
            Lang::C_EN => 'Currency code',
            Lang::C_RU => 'Код валюты',
            Lang::C_UK => 'Код валюти',
        ],

        self::F_CARD_MASKED_PAN => [
            Lang::C_EN => 'Masked PAN',
            Lang::C_RU => 'Замаскированный номер карты',
            Lang::C_UK => 'Замаскований номер картки',
        ],


        // =========================
        // TRANSACTIONS
        // =========================

        self::F_BANK_ID => [
            Lang::C_EN => 'Unique transaction id',
            Lang::C_RU => 'Уникальный id транзакции',
            Lang::C_UK => 'Унікальний id транзакції',
        ],

        self::F_TIME => [
            Lang::C_EN => 'Transaction time in seconds in Unix time format',
            Lang::C_RU => 'Время транзакции в секундах в формате Unix time',
            Lang::C_UK => 'Час транзакції в секундах в форматі Unix time',
        ],

        self::F_DESCRIPTION => [
            Lang::C_EN => 'Transaction description',
            Lang::C_RU => 'Описание транзакции',
            Lang::C_UK => 'Опис транзакції',
        ],

        self::F_MCC => [
            Lang::C_EN => 'Transaction type code (Merchant Category Code), according to ISO 18245',
            Lang::C_RU => 'Код типа транзакции (Merchant Category Code), согласно ISO 18245',
            Lang::C_UK => 'Код типу транзакції (Merchant Category Code), відповідно ISO 18245',
        ],

        self::F_ORIGINAL_MCC => [
            Lang::C_EN => 'Original transaction type code (Merchant Category Code), according to ISO 18245',
            Lang::C_RU => 'Оригинальный код типа транзакции (Merchant Category Code), согласно ISO 18245',
            Lang::C_UK => 'Оригінальний код типу транзакції (Merchant Category Code), відповідно ISO 18245',
        ],

        self::F_HOLD => [
            Lang::C_EN => 'Amount blocking status (more details in wiki)',
            Lang::C_RU => 'Статус блокировки суммы (подробнее в wiki)',
            Lang::C_UK => 'Статус блокування суми (детальніше у wiki)',
        ],

        self::F_AMOUNT => [
            Lang::C_EN => 'Amount in account currency in minimum currency units (kopecks, cents)',
            Lang::C_RU => 'Сумма в валюте счета в минимальных единицах валюты (копейках, центах)',
            Lang::C_UK => 'Сума у валюті рахунку в мінімальних одиницях валюти (копійках, центах)',
        ],

        self::F_OPERATION_AMOUNT => [
            Lang::C_EN => 'Amount in transaction currency in minimum currency units (kopecks, cents)',
            Lang::C_RU => 'Сумма в валюте транзакции в минимальных единицах валюты (копейках, центах)',
            Lang::C_UK => 'Сума у валюті транзакції в мінімальних одиницях валюти (копійках, центах)',
        ],

        self::F_CURRENCY_CODE => [
            Lang::C_EN => 'Account currency code according to ISO 4217',
            Lang::C_RU => 'Код валюты счета согласно ISO 4217',
            Lang::C_UK => 'Код валюти рахунку відповідно ISO 4217',
        ],

        self::F_COMMISSION_RATE => [
            Lang::C_EN => 'Commission amount in minimum currency units (kopecks, cents)',
            Lang::C_RU => 'Размер комиссии в минимальных единицах валюты (копейках, центах)',
            Lang::C_UK => 'Розмір комісії в мінімальних одиницях валюти (копійках, центах)',
        ],

        self::F_CASHBACK_AMOUNT => [
            Lang::C_EN => 'Cashback amount in minimum currency units (kopecks, cents)',
            Lang::C_RU => 'Размер кэшбека в минимальных единицах валюты (копейках, центах)',
            Lang::C_UK => 'Розмір кешбеку в мінімальних одиницях валюти (копійках, центах)',
        ],

        self::F_BALANCE => [
            Lang::C_EN => 'Account balance in minimum currency units (kopecks, cents)',
            Lang::C_RU => 'Баланс счета в минимальных единицах валюты (копейках, центах)',
            Lang::C_UK => 'Баланс рахунку в мінімальних одиницях валюти (копійках, центах)',
        ],

        self::F_COMMENT => [
            Lang::C_EN => 'Comment to the transfer entered by the user. If not specified, the field will be missing',
            Lang::C_RU => 'Комментарий к переводу, введенный пользователем. Если не указан, поле будет отсутствовать',
            Lang::C_UK => 'Коментар до переказу, уведений користувачем. Якщо не вказаний, поле буде відсутнім',
        ],

        self::F_RECEIPT_ID => [
            Lang::C_EN => 'Receipt number for check.gov.ua. The field may be missing',
            Lang::C_RU => 'Номер квитанции для check.gov.ua. Поле может отсутствовать',
            Lang::C_UK => 'Номер квитанції для check.gov.ua. Поле може бути відсутнім',
        ],

        self::F_INVOICE_ID => [
            Lang::C_EN => 'Individual entrepreneur receipt number, comes if this is a transaction with a transfer of funds',
            Lang::C_RU => 'Номер квитанции ФЛП приходит в случае если это операция с зачислением средств',
            Lang::C_UK => 'Номер квитанції ФОПа, приходить у випадку якщо це операція із зарахуванням коштів',
        ],

        self::F_COUNTER_EDRPOU => [
            Lang::C_EN => 'Counterparty\'s EDRPOU, present only for individual entrepreneur account statement elements',
            Lang::C_RU => 'ЕГРПОУ контрагента присутствует только для элементов выписки счетов ФЛП',
            Lang::C_UK => 'ЄДРПОУ контрагента, присутній лише для елементів виписки рахунків ФОП',
        ],

        self::F_COUNTER_IBAN => [
            Lang::C_EN => 'Counterparty\'s IBAN, present only for individual entrepreneur account statement elements',
            Lang::C_RU => 'IBAN контрагента, присутствует только для элементов выписки счетов ФЛП',
            Lang::C_UK => 'IBAN контрагента, присутній лише для елементів виписки рахунків ФОП',
        ],

        self::F_COUNTER_NAME => [
            Lang::C_EN => 'Counterparty name',
            Lang::C_RU => 'Наименование контрагента',
            Lang::C_UK => 'Найменування контрагента',
        ],

        // =========================
        // ADDITIONAL FIELD
        // =========================

        self::F_ACCOUNT_ID => [
            Lang::C_EN => 'ID of bank card or account related to transaction',
            Lang::C_RU => 'ID банковской карты или счета, к которому относится транзакция',
            Lang::C_UK => 'ID банківської картки або рахунку, до якого відноситься транзакція',
        ],

    ];    




    /**
     * Возвращает описание поля транзакции на выбранном языке
     * 
     * @param string $field Название поля, для которого нужно получить описание
     * @param string|null $lang_code Код языка (например, 'en', 'ru', 'uk'), если не указан, используется текущий язык системы
     * @return string Описание поля на выбранном языке, если описание не найдено, возвращается имя поля
     */
    public static function field_descr(string $field, ?string $lang_code = null): string
    {
        if (empty($lang_code)) { $lang_code = App::lang(); }

        return (empty(self::FIELD_DESCRIPTIONS[$field])
                    ?   ''
                    :   self::FIELD_DESCRIPTIONS[$field][$lang_code]
                        ?? self::FIELD_DESCRIPTIONS[$field][Lang::C_RU]
                        ?? self::FIELD_DESCRIPTIONS[$field][Lang::C_UK]
                        ?? self::FIELD_DESCRIPTIONS[$field][Lang::C_EN]
                        ?? $field
                );
    }



    // const DESCRIPTIONS = [

    //     Lang::C_EN => [
    //         self::F_BANK_ID          => 'Unique transaction id',
    //         self::F_TIME             => 'Transaction time in seconds in Unix time format',
    //         self::F_DESCRIPTION      => 'Transaction description',
    //         self::F_MCC              => 'Transaction type code (Merchant Category Code), according to ISO 18245',
    //         self::F_ORIGINAL_MCC     => 'Original transaction type code (Merchant Category Code), according to ISO 18245',
    //         self::F_HOLD             => 'Amount blocking status (more details in wiki)',
    //         self::F_AMOUNT           => 'Amount in account currency in minimum currency units (kopecks, cents)',
    //         self::F_OPERATION_AMOUNT => 'Amount in transaction currency in minimum currency units (kopecks, cents)',
    //         self::F_CURRENCY_CODE    => 'Account currency code according to ISO 4217',
    //         self::F_COMMISSION_RATE  => 'Commission amount in minimum currency units (kopecks, cents)',
    //         self::F_CASHBACK_AMOUNT  => 'Cashback amount in minimum currency units (kopecks, cents)',
    //         self::F_BALANCE          => 'Account balance in minimum currency units (kopecks, cents)',
    //         self::F_COMMENT          => 'Comment to the transfer entered by the user. If not specified, the field will be missing',
    //         self::F_RECEIPT_ID       => 'Receipt number for check.gov.ua. The field may be missing',
    //         self::F_INVOICE_ID       => 'Individual entrepreneur receipt number, comes if this is a transaction with a transfer of funds',
    //         self::F_COUNTER_EDRPOU   => 'Counterparty\'s EDRPOU, present only for individual entrepreneur account statement elements',
    //         self::F_COUNTER_IBAN     => 'Counterparty\'s IBAN, present only for individual entrepreneur account statement elements',
    //         self::F_COUNTER_NAME     => 'Counterparty name',
    //     ],

    //     Lang::C_RU => [
    //         self::F_BANK_ID          => 'Уникальный id транзакции',
    //         self::F_TIME             => 'Время транзакции в секундах в формате Unix time',
    //         self::F_DESCRIPTION      => 'Описание транзакции',
    //         self::F_MCC              => 'Код типа транзакции (Merchant Category Code), согласно ISO 18245',
    //         self::F_ORIGINAL_MCC     => 'Оригинальный код типа транзакции (Merchant Category Code), согласно ISO 18245',
    //         self::F_HOLD             => 'Статус блокировки суммы (подробнее в wiki)',
    //         self::F_AMOUNT           => 'Сумма в валюте счета в минимальных единицах валюты (копейках, центах)',
    //         self::F_OPERATION_AMOUNT => 'Сумма в валюте транзакции в минимальных единицах валюты (копейках, центах)',
    //         self::F_CURRENCY_CODE    => 'Код валюты счета согласно ISO 4217',
    //         self::F_COMMISSION_RATE  => 'Размер комиссии в минимальных единицах валюты (копейках, центах)',
    //         self::F_CASHBACK_AMOUNT  => 'Размер кэшбека в минимальных единицах валюты (копейках, центах)',
    //         self::F_BALANCE          => 'Баланс счета в минимальных единицах валюты (копейках, центах)',
    //         self::F_COMMENT          => 'Комментарий к переводу, введенный пользователем. Если не указан, поле будет отсутствовать',
    //         self::F_RECEIPT_ID       => 'Номер квитанции для check.gov.ua. Поле может отсутствовать',
    //         self::F_INVOICE_ID       => 'Номер квитанции ФЛП приходит в случае если это операция с зачислением средств',
    //         self::F_COUNTER_EDRPOU   => 'ЕГРПОУ контрагента присутствует только для элементов выписки счетов ФЛП',
    //         self::F_COUNTER_IBAN     => 'IBAN контрагента, присутствует только для элементов выписки счетов ФЛП',
    //         self::F_COUNTER_NAME     => 'Наименование контрагента',
    //     ],

    //     Lang::C_UK => [
    //         self::F_BANK_ID          => 'Унікальний id транзакції',
    //         self::F_TIME             => 'Час транзакції в секундах в форматі Unix time',
    //         self::F_DESCRIPTION      => 'Опис транзакції',
    //         self::F_MCC              => 'Код типу транзакції (Merchant Category Code), відповідно ISO 18245',
    //         self::F_ORIGINAL_MCC     => 'Оригінальний код типу транзакції (Merchant Category Code), відповідно ISO 18245',
    //         self::F_HOLD             => 'Статус блокування суми (детальніше у wiki)',
    //         self::F_AMOUNT           => 'Сума у валюті рахунку в мінімальних одиницях валюти (копійках, центах)',
    //         self::F_OPERATION_AMOUNT => 'Сума у валюті транзакції в мінімальних одиницях валюти (копійках, центах)',
    //         self::F_CURRENCY_CODE    => 'Код валюти рахунку відповідно ISO 4217',
    //         self::F_COMMISSION_RATE  => 'Розмір комісії в мінімальних одиницях валюти (копійках, центах)',
    //         self::F_CASHBACK_AMOUNT  => 'Розмір кешбеку в мінімальних одиницях валюти (копійках, центах)',
    //         self::F_BALANCE          => 'Баланс рахунку в мінімальних одиницях валюти (копійках, центах)',
    //         self::F_COMMENT          => 'Коментар до переказу, уведений користувачем. Якщо не вказаний, поле буде відсутнім',
    //         self::F_RECEIPT_ID       => 'Номер квитанції для check.gov.ua. Поле може бути відсутнім',
    //         self::F_INVOICE_ID       => 'Номер квитанції ФОПа, приходить у випадку якщо це операція із зарахуванням коштів',
    //         self::F_COUNTER_EDRPOU   => 'ЄДРПОУ контрагента, присутній лише для елементів виписки рахунків ФОП',
    //         self::F_COUNTER_IBAN     => 'IBAN контрагента, присутній лише для елементів виписки рахунків ФОП',
    //         self::F_COUNTER_NAME     => 'Найменування контрагента',
    //     ],
        
    // ];



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

        /**
         * Нормализация индекса
         * в качестве индекса используется ID карты
         */
        $client_info[MonoCard::F_ACCOUNTS] = array_column($client_info[MonoCard::F_ACCOUNTS], null, MonoCard::F_CARD_ID);

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

        if (isset($bank_cli_info['errorDescription'])) {
                MsgQueue::msg(MsgType::ERROR, __('Error in the card request | Ошибка в запросе карт | Помилка в запиті карт') . ": " . $bank_cli_info['errorDescription']);
                redirect();
        } else {
            self::normalize_client_info($bank_cli_info);
        }
        // debug($bank_cli_info, '$bank_cli_info');
        return [
            'client'  => $bank_cli_info,
            'connect' => $connect_info
        ];
    }        



    public static function normalize_statements(array &$statements, &$accounts): void {
        foreach ($statements as &$statement) {
            $statement[self::F_AMOUNT]           = floatval($statement[self::F_AMOUNT])/100;
            $statement[self::F_OPERATION_AMOUNT] = floatval($statement[self::F_OPERATION_AMOUNT])/100;
            $statement[self::F_COMMISSION_RATE]  = floatval($statement[self::F_COMMISSION_RATE])/100;
            $statement[self::F_CASHBACK_AMOUNT]  = floatval($statement[self::F_CASHBACK_AMOUNT])/100;
            $statement[self::F_CASHBACK_AMOUNT]  = floatval($statement[self::F_CASHBACK_AMOUNT])/100;
            /**
             * Убираем из баланса кредитный лимит
             */
            $statement[self::F_BALANCE]          = floatval($statement[self::F_BALANCE])/100 - $accounts[self::F_ACCOUNTS][$statement[self::F_ACCOUNT_ID]][self::F_CARD_CREDIT_LIMIT];
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
    public static function get_statements(string $token, array $accounts, int $date1, int $date2, string $api_url): array {

        $headers = MonoCard::get_headers($token);

        // debug($accounts, '$accounts');

        /**
         * Список кредитовых [+] операций по всем картам
         */
        $credit_list_all = [];
        $connect_info = [];

        foreach (($accounts['accounts'] ?? []) as $account) {

            // debug($account, '$account');


            /**
             * Запросы отправляем только на карты клиента
             */
            if ($account[self::F_CARD_SEND_ID] != $accounts[self::F_CLIENT_ID]) { continue; }

            /** 
             * Формирование URL запроса к API монобанка
             * Добавляем 1 день к конечной дате, чтобы включить операции за date2 в выборку
             * Номер счета по умолчанию устанавливается в "0"
             * $request_url = $api_url . "" . "0" . "/" . $date1 . "/" . ($date2 + (1 * 24 * 60 * 60));
             * 
             * Формируем запрос для конкретной карты
             */
            $request_url = $api_url . "" . $account[self::F_CARD_ID] . "/" . $date1 . "/" . ($date2 + (1 * 24 * 60 * 60));

            $ch = curl_init($request_url);
            curl_setopt($ch, CURLOPT_POST, 0); // Не использовать метод POST
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // Тело запроса (закомментировано)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // HTTP заголовки
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Вернуть результат в переменную
            curl_setopt($ch, CURLOPT_HEADER, 0); // Не включать заголовки в вывод
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // Проверять SSL сертификат

            // Выполнить cURL запрос и получить информацию о соединении и ответ
            $connect_info = curl_getinfo($ch);
            $res = curl_exec($ch); // Получение результата запроса
            $statements = json_decode($res, true); // Преобразование JSON ответа в PHP массив
            curl_close($ch);
            
            // debug($res, '$res');
            // debug($statements, '$statements');

            $credit_list = [];
            if (isset($statements['errorDescription'])) {
                MsgQueue::msg(MsgType::ERROR, __('Error in the request | Ошибка в запросе | Помилка в запиті') . ": " . $statements['errorDescription']);
                MsgQueue::msg(MsgType::ERROR, __('For Account | Для карты | Для картки') . ": ");
                MsgQueue::msg(MsgType::ERROR, $account);
            } else { 
                /**
                 * Выбираем только входные платежи [+]
                 */
                foreach ($statements as $statement) {
                    if ($statement[MonoCard::F_AMOUNT] > 0) {

                        /**
                         * Добавляем ID карты для идентификации счёта для которого транзакция
                         */
                        $statement[MonoCard::F_ACCOUNT_ID] = $account[self::F_CARD_ID];

                        $credit_list[] =$statement;
                    }
                }
                /**
                 * Нормализовать данные выписки (преобразовать суммы из копеек в гривны)
                 */
                self::normalize_statements($credit_list, $accounts);

                // debug($credit_list, '$credit_list', die:0);

                /**
                 * Собираем полный список транзакций по всем картам
                 */
                $credit_list_all = array_merge($credit_list_all, $credit_list);

            }

        }

        // debug(0, 0, die:1);

        return [
            'connect' => $connect_info, // Информация о соединении
            Bank::F_STATEMENTS => $credit_list_all, // Данные по операциям по всем картам
        ];
    }








}
