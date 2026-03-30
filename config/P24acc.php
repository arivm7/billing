<?php
/**
 *  Project : my.ri.net.ua
 *  File    : P24acc.php
 *  Path    : config/P24acc.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:12:53
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of P24acc.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace config;

use billing\core\App;
use billing\core\base\Lang;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Ppp;

class P24acc {

    const F_RESPONSE_STATUS = 'status';                    // "SUCCESS" | "ERROR" -- ознака успішності відповіді
    const F_RESPONSE_TYPE = 'type';                        // "transactions" -- тип відповіді
    const F_RESPONSE_EXIST_NEXT_PAGE = 'exist_next_page';  // true -- ознака наявності наступної пачки
    const F_RESPONSE_NEXT_PAGE_ID = 'next_page_id';        // "12345678_online" -- ідентифікатор наступної пачки (підставляється у followId наступного запиту)
    const F_TRANSACTIONS = 'transactions';                 // масив об’єктів із транзакціями
    const F_BALANCES = 'balances';                         // масив об’єктів із балансами

    // Приклад відповіді з балансами
    //
    // {
    //   "status": "SUCCESS",               // ознака успішності відповіді
    //   "type": "balances",                // тип відповіді
    //   "exist_next_page": true,           // ознака наявності наступної пачки
    //   "next_page_id": "26101050000888",  // ідентифікатор наступної пачки  (підставляється у followId наступного запиту)
    //   "balances": [                      // масив об’єктів із балансами
    //     {
    //       "acc": "UA943052990000026100050001037", // номер рахунку
    //       "currency": "UAH",             // валюта
    //       "balanceIn": "0.00",           // залишок на рахунку вхідний
    //       "balanceInEq": "0.00",         // залишок на рахунку вхідний (екв. у нац. валюті)
    //       "balanceOut": "0.00",          // залишок на рахунку вихідний
    //       "balanceOutEq": "0.00",        // залишок на рахунку вихідний (екв. у нац. валюті)
    //       "turnoverDebt": "0.00",        // оборот, дебет
    //       "turnoverDebtEq": "0.00",      // оборот, дебет (екв. у нац. валюті)
    //       "turnoverCred": "0.00",        // оборот, кредит
    //       "turnoverCredEq": "0.00",      // оборот, кредит (екв. у нац. валюті)
    //       "bgfIBrnm": " ",               // бранч, що залучив контрагента
    //       "brnm": "DNH0",                // бранч рахунку
    //       "dpd": "09.03.2020 00:00:00",  // дата останнього руху за рахунком
    //       "nameACC": "Ko МСБ-ТЕСТ ТОВ",  // найменування рахунку
    //       "state": "l",
    //       "atp": "D",
    //       "flmn": "DN",
    //       "date_open_acc_reg": "28.12.2016 00:00:00",
    //       "date_open_acc_sys": "28.12.2016 00:00:00",
    //       "date_close_acc": "01.01.1900 00:00:00",
    //       "is_final_bal": true
    //     },{...}
    // ]}

    //   "balances": [                      // масив об’єктів із балансами
    //     {
    const F_ACC_NO = "acc";                                 // номер рахунку
    const F_ACC_CURRENCY = "currency";                      // "UAH", валюта
    const F_ACC_BALANCE_IN = "balanceIn";                   // залишок на рахунку вхідний
    const F_ACC_BALANCE_IN_EQ = "balanceInEq";              // залишок на рахунку вхідний (екв. у нац. валюті)
    const F_ACC_BALANCE_OUT = "balanceOut";                 // залишок на рахунку вихідний
    const F_ACC_BALANCE_OUT_EQ = "balanceOutEq";            // залишок на рахунку вихідний (екв. у нац. валюті)
    const F_ACC_TURNOVER_DEBT = "turnoverDebt";             // оборот, дебет
    const F_ACC_TURNOVER_DEBT_EQ = "turnoverDebtEq";        // оборот, дебет (екв. у нац. валюті)
    const F_ACC_TURNOVER_CRED = "turnoverCred";             // оборот, кредит
    const F_ACC_TURNOVER_CRED_EQ = "turnoverCredEq";        // оборот, кредит (екв. у нац. валюті)
    const F_ACC_BGF_IBRN_NM = "bgfIBrnm";                   // бранч, що залучив контрагента (филиал банка, обслуживавший контрагента)
    const F_ACC_BRNM = "brnm";                              // бранч рахунку (филиал банка, обслуживающий счет)
    const F_ACC_DPD = "dpd";                                // дата останнього руху за рахунком
    const F_ACC_NAME_ACC = "nameACC";                       // найменування рахунку

    /**
     * Состояние строки баланса (не операции)
     * В ответе balance/interim поле state относится к типу/состоянию записи баланса, а не к жизненному циклу транзакции.
     * Практически встречающиеся значения:
     * Значение Смысл
     *     f    финальный бухгалтерский остаток (ledger)
     *     l    промежуточный (interim, с холдами/лимитами)
     *     a    доступный баланс (available funds)     * 
     */
    const F_ACC_STATE = "state";                            // стан рахунку 

    /**
     * Тип операции / направление
     * Это поле описывает тип бухгалтерской проводки (Accounting Transaction Parameter).
     * Основные значения:
     * Значение Смысл
     *     D    Debit   уменьшение
     *     C    Credit  увеличение
     *     T    Total   итоговое значение
     */
    const F_ACC_ATP = "atp";                                // Тип операции / направление

    const F_ACC_FLMN = "flmn";                              // код філії
    const F_ACC_DATE_OPEN_ACC_REG = "date_open_acc_reg";    // дата відкриття рахунку в регістру
    const F_ACC_DATE_OPEN_ACC_SYS = "date_open_acc_sys";    // дата відкриття рахунку в системі
    const F_ACC_DATE_CLOSE_ACC = "date_close_acc";          // дата закриття рахунку
    const F_ACC_IS_FINAL_BAL = "is_final_bal";              // чи є кінцевий баланс    
    //     },{...}



    // Приклад відповіді з транзакціями
    // {
    //   "status": "SUCCESS",                                   // ознака успішності відповіді
    //   "type": "transactions",                                // тип відповіді
    //   "exist_next_page": true,                               // ознака наявності наступної пачки
    //   "next_page_id": "620699370_online",                    // ідентифікатор наступної пачки (підставляється у followId наступного запиту)
    //
    //   "transactions": [                                      // масив об’єктів із транзакціями
    //     {
    //       "AUT_MY_CRF": "31451288",                          // ЄДРПОУ одержувача
    //       "AUT_MY_MFO": "305299",                            // МФО одержувача
    //       "AUT_MY_ACC": "26184050001514",                    // рахунок одержувача
    //       "AUT_MY_NAM": "Програмiсти та Ko МСБ-ТЕСТ ТОВ",    // назва одержувача
    //       "AUT_MY_MFO_NAME": "АТ КБ \"ПРИВАТБАНК\"",         // банк одержувача
    //       "AUT_MY_MFO_CITY": "Київ",                         // назва міста банку
    //       "AUT_CNTR_CRF": "14360570",                        // ЄДРПОУ контрагента
    //       "AUT_CNTR_MFO": "305299",                          // МФО контрагента
    //       "AUT_CNTR_ACC": "70214924104032",                  // рахунок контрагента
    //       "AUT_CNTR_NAM": "ПРОЦ ВИТР ЗА СТРОК КОШТ СУБ",     // назва контрагента
    //       "AUT_CNTR_MFO_NAME": "АТ КБ \"ПРИВАТБАНК\"",       // назва банку контрагента
    //       "AUT_CNTR_MFO_CITY": "Київ",                       // назва міста банку
    //       "CCY": "UAH",                                      // валюта
    //       "FL_REAL": "r",                                    // ознака реальності проведення (r,i)
    //       "PR_PR": "r",                                      // стан p - проводиться, t - сторнована, r - проведена, n - забракована
    //       "DOC_TYP": "m",                                    // тип пл. документа
    //       "NUM_DOC": "K0108B1WKX",                           // номер документа
    //       "DAT_KL": "07.01.2020",                            // клієнтська дата
    //       "DAT_OD": "07.01.2020",                            // дата валютування
    //       "OSND": "згiдно договору N...",                    // підстава  платежу
    //       "SUM": "0.01",                                     // сума
    //       "SUM_E": "0.01",                                   // сума в національній валюті (грн)
    //       "REF": "DNCHK0108B1WKX",                           // референс проведення
    //       "REFN": "1",                                       // № з/п всередині проведення
    //       "TIM_P": "02:58",                                  // час проведення
    //       "DATE_TIME_DAT_OD_TIM_P": "07.01.2020 02:58:00",
    //       "ID": "557091731",                                 // ID транзакції -- на самом деле НЕ уникальное поле
    //       "TRANTYPE": "C",                                   // тип транзакції дебет/кредит (D, C)
    //       "DLR": "J63DNDSM0XHY5",                            // референс платежу сервісу, через який створювали платіж (payment_pack_ref - у разі створення платежу через АPI «Автоклієнт»)
    //       "TECHNICAL_TRANSACTION_ID": "557091731_online"
    //     }, {...}
    // ]}
    //
    //       Для виконання вимог НБУ, у відповіді можуть бути також додаткові поля, які відносяться до кінцевого платника/отримувача:
    //
    //       "UETR":"b23aeadc-1ab7-4c34-a005-0f005a059948",     // ідентифікатор тразакції
    //       "ULTMT":"N",                                       // тип заповненості реквізитів
    //       "PAYER_ULTMT_NCEO":"",                             // ЄДРПОУ кінцевого платника
    //       "PAYER_ULTMT_DOCUMENT":"",                         // серія, номер паспорту кінцевого платника
    //       "PAYER_ULTMT_NAME":"",                             // назва кінцевого платника
    //       "PAYER_ULTMT_COUNTRY_CODE":"",                     // код країни нерезидента кінцевого платника
    //       "RECIPIENT_ULTMT_NCEO":"",                         // ЄДРПОУ кінцевого отримувача
    //       "RECIPIENT_ULTMT_DOCUMENT":"",                     // серія, номер паспорту кінцевого отримувача
    //       "RECIPIENT_ULTMT_NAME":"",                         // назва кінцевого отримувача
    //       "RECIPIENT_ULTMT_COUNTRY_CODE":""                  // код країни нерезидента кінцевого отримувача
    //
    //       Для податкових платежів також додані нові поля для структурованого призначення платежу:
    //
    //       "STRUCT_CODE":”101”,                               // код виду сплати
    //       "STRUCT_TYPE":”22080000”,                          // код бюджетної класифікації
    //       "STRUCT_CATEGORY":”Довільний текст”                // Інформація про податкове повідомлення (рішення)
    //
    //       Для ідентифікації унікальності платіжних інструкцій використовуйте конкатенацію полів REF+REFN 
    //




    // Поля об'єкта транзакції F_TRANSACTIONS[...]
    
    const F_ID = 'ID';                                     // "557091731" -- ID транзакції не уникальное поле
    const F_REF = 'REF';                                   // "DNCHK0108B1WKX" -- референс проведення
    const F_REFN = 'REFN';                                 // "1" -- № з/п всередині проведення
    const F_AUT_MY_CRF = 'AUT_MY_CRF';                     // ЄДРПОУ одержувача
    const F_AUT_MY_MFO = 'AUT_MY_MFO';                     // МФО одержувача
    const F_AUT_MY_ACC = 'AUT_MY_ACC';                     // рахунок одержувача
    const F_AUT_MY_NAM = 'AUT_MY_NAM';                     // назва одержувача
    const F_AUT_MY_MFO_NAME = 'AUT_MY_MFO_NAME';           // назва банку одержувача
    const F_AUT_MY_MFO_CITY = 'AUT_MY_MFO_CITY';           // назва міста банку
    const F_AUT_CNTR_CRF = 'AUT_CNTR_CRF';                 // ЄДРПОУ контрагента
    const F_AUT_CNTR_MFO = 'AUT_CNTR_MFO';                 // МФО контрагента
    const F_AUT_CNTR_ACC = 'AUT_CNTR_ACC';                 // рахунок контрагента
    const F_AUT_CNTR_NAM = 'AUT_CNTR_NAM';                 // назва контрагента
    const F_AUT_CNTR_MFO_NAME = 'AUT_CNTR_MFO_NAME';       // назва банку контрагента
    const F_AUT_CNTR_MFO_CITY = 'AUT_CNTR_MFO_CITY';       // назва міста банку
    const F_CCY = 'CCY';                                   // валюта
    const F_FL_REAL = 'FL_REAL';                           // ознака реальності проведення (r,i)
    const F_PR_PR = 'PR_PR';                               // стан p - проводиться, t - сторнована, r - проведена, n - забракована
    const F_DOC_TYP = 'DOC_TYP';                           // "m" -- тип пл. документа
    const F_NUM_DOC = 'NUM_DOC';                           // "K0108B1WKX" -- номер документа
    const F_DAT_KL = 'DAT_KL';                             // "07.01.2020" -- клієнтська дата
    const F_DAT_OD = 'DAT_OD';                             // "07.01.2020" -- дата валютування
    const F_OSND = 'OSND';                                 // "згiдно договору N..." -- підстава  платежу
    const F_SUM = 'SUM';                                   // сума
    const F_SUM_E = 'SUM_E';                               // сума в національній валюті (грн)
    const F_TIM_P = 'TIM_P';                               // "02:58" -- час проведення
    const F_DATE_TIME_DAT_OD_TIM_P = 'DATE_TIME_DAT_OD_TIM_P';  // "07.01.2020 02:58:00"
    const F_TRANTYPE = 'TRANTYPE';                         // тип транзакції дебет/кредит (D, C)
    const F_DLR = 'DLR';                                   // "J63DNDSM0XHY5" -- референс платежу сервісу, через який створювали платіж (payment_pack_ref - у разі створення платежу через АPI «Автоклієнт»)
    const F_TECHNICAL_TRANSACTION_ID = 'TECHNICAL_TRANSACTION_ID';  // "557091731_online"

    // Для виконання вимог НБУ, у відповіді можуть бути також додаткові поля, які відносяться до кінцевого платника/отримувача:

    const F_UETR = 'UETR';                                  // ідентифікатор тразакції
    const F_ULTMT = 'ULTMT';                                // "N" -- тип заповненості реквізитів
    const F_PAYER_ULTMT_NCEO = 'PAYER_ULTMT_NCEO';          // ЄДРПОУ кінцевого платника
    const F_PAYER_ULTMT_DOCUMENT = 'PAYER_ULTMT_DOCUMENT';  // серія, номер паспорту кінцевого платника
    const F_PAYER_ULTMT_NAME = 'PAYER_ULTMT_NAME';          // назва кінцевого платника
    const F_PAYER_ULTMT_COUNTRY_CODE = 'PAYER_ULTMT_COUNTRY_CODE';  // код країни нерезидента кінцевого платника
    const F_RECIPIENT_ULTMT_NCEO = 'RECIPIENT_ULTMT_NCEO';  // ЄДРПОУ кінцевого отримувача
    const F_RECIPIENT_ULTMT_DOCUMENT = 'RECIPIENT_ULTMT_DOCUMENT';  // серія, номер паспорту кінцевого отримувача 
    const F_RECIPIENT_ULTMT_NAME = 'RECIPIENT_ULTMT_NAME';  // назва кінцевого отримувача
    const F_RECIPIENT_ULTMT_COUNTRY_CODE = 'RECIPIENT_ULTMT_COUNTRY_CODE';  // код країни нерезидента кінцевого отримувача

    // Для податкових платежів також додані нові поля для структурованого призначення платежу:

    const F_STRUCT_CODE = 'STRUCT_CODE';          // код виду сплати
    const F_STRUCT_TYPE = 'STRUCT_TYPE';          // код бюджетної класифікації
    const F_STRUCT_CATEGORY = 'STRUCT_CATEGORY';  // Інформація про податкове повідомлення (рішення)

    // Для ідентифікації унікальності платіжних інструкцій використовуйте конкатенацію полів REF+REFN



    /**
     * Список денежных полей для правильного форматирования
     */
    const FIELDS_CURRENCY = [
        self::F_ACC_CURRENCY,
        self::F_ACC_BALANCE_IN,
        self::F_ACC_BALANCE_IN_EQ,
        self::F_ACC_BALANCE_OUT,
        self::F_ACC_BALANCE_OUT_EQ,
        self::F_ACC_TURNOVER_DEBT,
        self::F_ACC_TURNOVER_DEBT_EQ,
        self::F_ACC_TURNOVER_CRED,
        self::F_ACC_TURNOVER_CRED_EQ,
        self::F_SUM,
        self::F_SUM_E,
    ];



    /**
     * Список текстовых полей для правильного форматирования
     */
    const FIELDS_TEXT = [
        self::F_ACC_CURRENCY,               // "UAH", валюта
        self::F_ACC_NAME_ACC,               // найменування рахунку
        self::F_AUT_MY_NAM,                 // назва одержувача
        self::F_AUT_MY_MFO_NAME,            // назва банку одержувача
        self::F_AUT_MY_MFO_CITY,            // назва міста банку
        self::F_AUT_CNTR_CRF,               // ЄДРПОУ контрагента
        self::F_AUT_CNTR_MFO,               // МФО контрагента
        self::F_AUT_CNTR_ACC,               // рахунок контрагента
        self::F_AUT_CNTR_NAM,               // назва контрагента
        self::F_AUT_CNTR_MFO_NAME,          // назва банку контрагента
        self::F_AUT_CNTR_MFO_CITY,          // назва міста банку
        self::F_OSND,                       // "згiдно договору N..." -- підстава  платежу
        self::F_PAYER_ULTMT_NCEO,           // ЄДРПОУ кінцевого платника
        self::F_PAYER_ULTMT_DOCUMENT,       // серія, номер паспорту кінцевого платника
        self::F_PAYER_ULTMT_NAME,           // назва кінцевого платника
        self::F_RECIPIENT_ULTMT_NCEO,       // ЄДРПОУ кінцевого отримувача
        self::F_RECIPIENT_ULTMT_DOCUMENT,   // серія, номер паспорту кінцевого отримувача 
        self::F_RECIPIENT_ULTMT_NAME,       // назва кінцевого отримувача
        self::F_STRUCT_CATEGORY,            // Інформація про податкове повідомлення (рішення)
    ];



    /**
     * Названия полей транзакции на трёх языках
     */
    const TRANSACTION_FIELD_TITLE = [

        /**
         * Поля баланса
         */
        self::F_ACC_NO => [
            'en' => "Account number",
            'ru' => "Номер счёта",
            'uk' => "Номер рахунку",
        ],
        self::F_ACC_CURRENCY => [
            'en' => "Currency",
            'ru' => "Валюта",
            'uk' => "Валюта",
        ],
        self::F_ACC_BALANCE_IN => [
            'en' => "Opening balance",
            'ru' => "Входящий остаток",
            'uk' => "Вхідний залишок",
        ],
        self::F_ACC_BALANCE_IN_EQ => [
            'en' => "Opening balance (national)",
            'ru' => "Входящий остаток (нац.)",
            'uk' => "Вхідний залишок (нац.)",
        ],
        self::F_ACC_BALANCE_OUT => [
            'en' => "Closing balance",
            'ru' => "Исходящий остаток",
            'uk' => "Вихідний залишок",
        ],
        self::F_ACC_BALANCE_OUT_EQ => [
            'en' => "Closing balance (national)",
            'ru' => "Исходящий остаток (нац.)",
            'uk' => "Вихідний залишок (нац.)",
        ],
        self::F_ACC_TURNOVER_DEBT => [
            'en' => "Debit turnover",
            'ru' => "Оборот по дебету",
            'uk' => "Оборот по дебету",
        ],
        self::F_ACC_TURNOVER_DEBT_EQ => [
            'en' => "D turnover (national)",
            'ru' => "Оборот по D (нац.)",
            'uk' => "Оборот по D (нац.)",
        ],
        self::F_ACC_TURNOVER_CRED => [
            'en' => "Credit turnover",
            'ru' => "Оборот по кредиту",
            'uk' => "Оборот по кредиту",
        ],
        self::F_ACC_TURNOVER_CRED_EQ => [
            'en' => "Credit turnover (national)",
            'ru' => "Оборот по кредиту (нац.)",
            'uk' => "Оборот по кредиту (нац.)",
        ],
        self::F_ACC_BGF_IBRN_NM => [
            'en' => "Counterparty branch",
            'ru' => "Филиал, обслуживавший контрагента",
            'uk' => "Філія, що обслуговувала контрагента",
        ],
        self::F_ACC_BRNM => [
            'en' => "Account branch",
            'ru' => "Филиал счёта",
            'uk' => "Філія рахунку",
        ],
        self::F_ACC_DPD => [
            'en' => "Last transaction date",
            'ru' => "Дата последнего движения",
            'uk' => "Дата останнього руху",
        ],
        self::F_ACC_NAME_ACC => [
            'en' => "Account name",
            'ru' => "Наименование счёта",
            'uk' => "Найменування рахунку",
        ],
        self::F_ACC_STATE => [
            'en' => "Balance state",
            'ru' => "Состояние баланса",
            'uk' => "Стан балансу",
        ],
        self::F_ACC_ATP => [
            'en' => "Record type",
            'ru' => "Тип записи",
            'uk' => "Тип запису",
        ],
        self::F_ACC_FLMN => [
            'en' => "Branch code",
            'ru' => "Код филиала",
            'uk' => "Код філії",
        ],
        self::F_ACC_DATE_OPEN_ACC_REG => [
            'en' => "Account in registry",
            'ru' => "Дата в регистре",
            'uk' => "Дата в реєстрі",
        ],
        self::F_ACC_DATE_OPEN_ACC_SYS => [
            'en' => "Account in system",
            'ru' => "Дата в системе",
            'uk' => "Дата в системі",
        ],
        self::F_ACC_DATE_CLOSE_ACC => [
            'en' => "Account closing date",
            'ru' => "Дата закрытия счёта",
            'uk' => "Дата закриття рахунку",
        ],
        self::F_ACC_IS_FINAL_BAL => [
            'en' => "Is final balance",
            'ru' => "Является ли баланс финальным",
            'uk' => "Чи є баланс фінальним",
        ],



        /**
         * Поля транзакции
         */
        self::F_AUT_MY_CRF => [
            'en' => "Recipient EDRPOU",
            'ru' => "ЄДРПОУ получателя",
            'uk' => "ЄДРПОУ отримувача",
        ],
        self::F_AUT_MY_MFO => [
            'en' => "Recipient MFO",
            'ru' => "МФО получателя",
            'uk' => "МФО отримувача",
        ],
        self::F_AUT_MY_ACC => [
            'en' => "Recipient Account",
            'ru' => "Счёт получателя",
            'uk' => "Рахунок отримувача",
        ],
        self::F_AUT_MY_NAM => [
            'en' => "Recipient Name",
            'ru' => "Название получателя",
            'uk' => "Назва отримувача",
        ],
        self::F_AUT_MY_MFO_NAME => [
            'en' => "Recipient Bank Name",
            'ru' => "Название банка получателя",
            'uk' => "Назва банку отримувача",
        ],
        self::F_AUT_MY_MFO_CITY => [
            'en' => "Recipient Bank City",
            'ru' => "Город банка получателя",
            'uk' => "Місто банку отримувача",
        ],
        self::F_AUT_CNTR_CRF => [
            'en' => "Counterparty EDRPOU",
            'ru' => "ЄДРПОУ контрагента",
            'uk' => "ЄДРПОУ контрагента",
        ],
        self::F_AUT_CNTR_MFO => [
            'en' => "Counterparty MFO",
            'ru' => "МФО контрагента",
            'uk' => "МФО контрагента",
        ],
        self::F_AUT_CNTR_ACC => [
            'en' => "Counterparty Account",
            'ru' => "Счёт контрагента",
            'uk' => "Рахунок контрагента",
        ],
        self::F_AUT_CNTR_NAM => [
            'en' => "Counterpart",
            'ru' => "Контрагент",
            'uk' => "Контрагент",
        ],
        self::F_AUT_CNTR_MFO_NAME => [
            'en' => "Counterparty Bank Name",
            'ru' => "Название банка контрагента",
            'uk' => "Назва банку контрагента",
        ],
        self::F_AUT_CNTR_MFO_CITY => [
            'en' => "Counterparty Bank City",
            'ru' => "Город банка контрагента",
            'uk' => "Місто банку контрагента",
        ],
        self::F_CCY => [
            'en' => "Currency",
            'ru' => "Валюта",
            'uk' => "Валюта",
        ],
        self::F_FL_REAL => [
            'en' => "Reality Mark (r,i)",
            'ru' => "Признак реальности (r,i)",
            'uk' => "Ознака реальності (r,i)",
        ],
        self::F_PR_PR => [
            'en' => "Execution status",
            'ru' => "Статус выполнения",
            'uk' => "Стан виконання",
        ],
        self::F_DOC_TYP => [
            'en' => "Document Type",
            'ru' => "Тип документа",
            'uk' => "Тип документа",
        ],
        self::F_NUM_DOC => [
            'en' => "Document Number",
            'ru' => "Номер документа",
            'uk' => "Номер документа",
        ],
        self::F_DAT_KL => [
            'en' => "Client Date",
            'ru' => "Клиентская дата",
            'uk' => "Клієнтська дата",
        ],
        self::F_DAT_OD => [
            'en' => "Posting date",
            'ru' => "Дата проводки",
            'uk' => "Дата валютування",
        ],
        self::F_OSND => [
            'en' => "Payment Purpose",
            'ru' => "Основание платежа",
            'uk' => "Підстава платежу",
        ],
        self::F_SUM => [
            'en' => "Amount",
            'ru' => "Сумма",
            'uk' => "Сума",
        ],
        self::F_SUM_E => [
            'en' => "Amount (UAH)",
            'ru' => "Сумма (грн)",
            'uk' => "Сума (грн)",
        ],
        self::F_REF => [
            'en' => "Transaction Reference",
            'ru' => "Референс проведения",
            'uk' => "Референс проведення",
        ],
        self::F_REFN => [
            'en' => "Sequence Number",
            'ru' => "№ з/п внутри",
            'uk' => "№ з/п всередині",
        ],
        self::F_TIM_P => [
            'en' => "Transaction Time",
            'ru' => "Время проведения",
            'uk' => "Час проведення",
        ],
        self::F_DATE_TIME_DAT_OD_TIM_P => [
            'en' => "Full Date/Time",
            'ru' => "Полная дата/время",
            'uk' => "Повна дата/час",
        ],
        self::F_ID => [
            'en' => "Transaction ID",
            'ru' => "ID транзакции",
            'uk' => "ID транзакції",
        ],
        self::F_TRANTYPE => [
            'en' => "Transaction Type",
            'ru' => "Тип транзакции",
            'uk' => "Тип транзакції",
        ],
        self::F_DLR => [
            'en' => "Payment Service Reference",
            'ru' => "Референс сервиса",
            'uk' => "Референс сервісу",
        ],
        self::F_TECHNICAL_TRANSACTION_ID => [
            'en' => "Technical Transaction ID",
            'ru' => "Технический ID транзакции",
            'uk' => "Технічний ID транзакції",
        ],
        self::F_UETR => [
            'en' => "Transaction UUID (UETR)",
            'ru' => "Идентификатор транзакции (UETR)",
            'uk' => "Ідентифікатор транзакції (UETR)",
        ],
        self::F_ULTMT => [
            'en' => "Details Type",
            'ru' => "Тип заполненности",
            'uk' => "Тип заповненості",
        ],
        self::F_PAYER_ULTMT_NCEO => [
            'en' => "Ultimate Payer EDRPOU",
            'ru' => "ЄДРПОУ конечного плательщика",
            'uk' => "ЄДРПОУ кінцевого платника",
        ],
        self::F_PAYER_ULTMT_DOCUMENT => [
            'en' => "Ultimate Payer Passport",
            'ru' => "Паспорт конечного плательщика",
            'uk' => "Паспорт кінцевого платника",
        ],
        self::F_PAYER_ULTMT_NAME => [
            'en' => "Ultimate Payer Name",
            'ru' => "Название конечного плательщика",
            'uk' => "Назва кінцевого платника",
        ],
        self::F_PAYER_ULTMT_COUNTRY_CODE => [
            'en' => "Ultimate Payer Country Code",
            'ru' => "Код страны нерезидента плательщика",
            'uk' => "Код країни нерезидента платника",
        ],
        self::F_RECIPIENT_ULTMT_NCEO => [
            'en' => "Ultimate Recipient EDRPOU",
            'ru' => "ЄДРПОУ конечного получателя",
            'uk' => "ЄДРПОУ кінцевого отримувача",
        ],
        self::F_RECIPIENT_ULTMT_DOCUMENT => [
            'en' => "Ultimate Recipient Passport",
            'ru' => "Паспорт конечного получателя",
            'uk' => "Паспорт кінцевого отримувача",
        ],
        self::F_RECIPIENT_ULTMT_NAME => [
            'en' => "Ultimate Recipient Name",
            'ru' => "Название конечного получателя",
            'uk' => "Назва кінцевого отримувача",
        ],
        self::F_RECIPIENT_ULTMT_COUNTRY_CODE => [
            'en' => "Ultimate Recipient Country Code",
            'ru' => "Код страны нерезидента получателя",
            'uk' => "Код країни нерезидента отримувача",
        ],
        self::F_STRUCT_CODE => [
            'en' => "Payment Type Code",
            'ru' => "Код вида уплаты",
            'uk' => "Код виду сплати",
        ],
        self::F_STRUCT_TYPE => [
            'en' => "Budget Classification Code",
            'ru' => "Код бюджетной классификации",
            'uk' => "Код бюджетної класифікації",
        ],
        self::F_STRUCT_CATEGORY => [
            'en' => "Tax Decision Info",
            'ru' => "Информация о налоговом решении",
            'uk' => "Інформація про податкове рішення",
        ],
    ];



    public static function field_title(string $field, ?string $lang = null): string
    {
        if (empty($lang)) { $lang = App::lang(); }

        return (empty(self::TRANSACTION_FIELD_TITLE[$field])
                    ?   ''
                    :   self::TRANSACTION_FIELD_TITLE[$field][$lang]
                        ?? self::TRANSACTION_FIELD_TITLE[$field]['ru']
                        ?? self::TRANSACTION_FIELD_TITLE[$field]['uk']
                        ?? self::TRANSACTION_FIELD_TITLE[$field]['en']
                        ?? $field
                );
    }



    /**
     * Названия полей транзакции на трёх языках
     */
    const TRANSACTION_FIELD_DESCRIPTION = [

        /**
         * Поля рассчетніх счетов
         */
        self::F_ACC_NO => [
            'en' => "Account number",
            'ru' => "Номер счёта",
            'uk' => "Номер рахунку",
        ],
        self::F_ACC_CURRENCY => [
            'en' => "Currency",
            'ru' => "Валюта",
            'uk' => "Валюта",
        ],
        self::F_ACC_BALANCE_IN => [
            'en' => "Opening balance",
            'ru' => "Входящий остаток",
            'uk' => "Вхідний залишок",
        ],
        self::F_ACC_BALANCE_IN_EQ => [
            'en' => "Opening balance (national currency equivalent)",
            'ru' => "Входящий остаток (экв. в нац. валюте)",
            'uk' => "Вхідний залишок (екв. у нац. валюті)",
        ],
        self::F_ACC_BALANCE_OUT => [
            'en' => "Closing balance",
            'ru' => "Исходящий остаток",
            'uk' => "Вихідний залишок",
        ],
        self::F_ACC_BALANCE_OUT_EQ => [
            'en' => "Closing balance (national currency equivalent)",
            'ru' => "Исходящий остаток (экв. в нац. валюте)",
            'uk' => "Вихідний залишок (екв. у нац. валюті)",
        ],
        self::F_ACC_TURNOVER_DEBT => [
            'en' => "Debit turnover",
            'ru' => "Оборот по дебету",
            'uk' => "Оборот по дебету",
        ],
        self::F_ACC_TURNOVER_DEBT_EQ => [
            'en' => "Debit turnover (national currency equivalent)",
            'ru' => "Оборот по дебету (экв. в нац. валюте)",
            'uk' => "Оборот по дебету (екв. у нац. валюті)",
        ],
        self::F_ACC_TURNOVER_CRED => [
            'en' => "Credit turnover",
            'ru' => "Оборот по кредиту",
            'uk' => "Оборот по кредиту",
        ],
        self::F_ACC_TURNOVER_CRED_EQ => [
            'en' => "Credit turnover (national currency equivalent)",
            'ru' => "Оборот по кредиту (экв. в нац. валюте)",
            'uk' => "Оборот по кредиту (екв. у нац. валюті)",
        ],
        self::F_ACC_BGF_IBRN_NM => [
            'en' => "Counterparty branch",
            'ru' => "Филиал, обслуживавший контрагента",
            'uk' => "Філія, що обслуговувала контрагента",
        ],
        self::F_ACC_BRNM => [
            'en' => "Account branch",
            'ru' => "Филиал счёта",
            'uk' => "Філія рахунку",
        ],
        self::F_ACC_DPD => [
            'en' => "Last transaction date",
            'ru' => "Дата последнего движения по счёту",
            'uk' => "Дата останнього руху за рахунком",
        ],
        self::F_ACC_NAME_ACC => [
            'en' => "Account name",
            'ru' => "Наименование счёта",
            'uk' => "Найменування рахунку",
        ],
        self::F_ACC_STATE => [
            'en' => "Balance Sheet: f - final balance (ledger) | l - interim (with holds/limits) | a - available funds",
            'ru' => "Состояние баланса: f - финальный бухгалтерский остаток (ledger) | l - промежуточный (interim, с холдами/лимитами) | a - доступный баланс (available funds)",
            'uk' => "Стан балансу: f – фінальний бухгалтерський залишок (ledger) | l – проміжний (interim, з холдами/лімітами) | a - доступний баланс (available funds)",
        ],
        self::F_ACC_ATP => [
            'en' => "Record type: D — debit [-] | C — credit [+] | T — total (summary)",
            'ru' => "Тип записи: D — дебет [-] | C — кредит [+] | T — итог (агрегированное значение)",
            'uk' => "Тип запису: D — дебет [-] | C — кредит [+] | T — підсумок (агреговане значення)",
        ],
        self::F_ACC_FLMN => [
            'en' => "Branch code",
            'ru' => "Код филиала",
            'uk' => "Код філії",
        ],
        self::F_ACC_DATE_OPEN_ACC_REG => [
            'en' => "Account opening date (registry)",
            'ru' => "Дата открытия счёта в регистре",
            'uk' => "Дата відкриття рахунку в реєстрі",
        ],
        self::F_ACC_DATE_OPEN_ACC_SYS => [
            'en' => "Account opening date (system)",
            'ru' => "Дата открытия счёта в системе",
            'uk' => "Дата відкриття рахунку в системі",
        ],
        self::F_ACC_DATE_CLOSE_ACC => [
            'en' => "Account closing date",
            'ru' => "Дата закрытия счёта",
            'uk' => "Дата закриття рахунку",
        ],
        self::F_ACC_IS_FINAL_BAL => [
            'en' => "Is final balance",
            'ru' => "Является ли баланс финальным",
            'uk' => "Чи є баланс фінальним",
        ],



        /**
         * Поля транзакций
         */
        self::F_AUT_MY_CRF => [
            'en' => "Recipient EDRPOU",
            'ru' => "ЄДРПОУ получателя",
            'uk' => "ЄДРПОУ отримувача",
        ],
        self::F_AUT_MY_MFO => [
            'en' => "Recipient MFO",
            'ru' => "МФО получателя",
            'uk' => "МФО отримувача",
        ],
        self::F_AUT_MY_ACC => [
            'en' => "Recipient Account",
            'ru' => "Счёт получателя",
            'uk' => "Рахунок отримувача",
        ],
        self::F_AUT_MY_NAM => [
            'en' => "Recipient Name",
            'ru' => "Название получателя",
            'uk' => "Назва отримувача",
        ],
        self::F_AUT_MY_MFO_NAME => [
            'en' => "Recipient Bank Name",
            'ru' => "Название банка получателя",
            'uk' => "Назва банку отримувача",
        ],
        self::F_AUT_MY_MFO_CITY => [
            'en' => "Recipient Bank City",
            'ru' => "Город банка получателя",
            'uk' => "Місто банку отримувача",
        ],
        self::F_AUT_CNTR_CRF => [
            'en' => "Counterparty EDRPOU",
            'ru' => "ЄДРПОУ контрагента",
            'uk' => "ЄДРПОУ контрагента",
        ],
        self::F_AUT_CNTR_MFO => [
            'en' => "Counterparty MFO",
            'ru' => "МФО контрагента",
            'uk' => "МФО контрагента",
        ],
        self::F_AUT_CNTR_ACC => [
            'en' => "Counterparty Account",
            'ru' => "Счёт контрагента",
            'uk' => "Рахунок контрагента",
        ],
        self::F_AUT_CNTR_NAM => [
            'en' => "Counterparty Name",
            'ru' => "Название контрагента",
            'uk' => "Назва контрагента",
        ],
        self::F_AUT_CNTR_MFO_NAME => [
            'en' => "Counterparty Bank Name",
            'ru' => "Название банка контрагента",
            'uk' => "Назва банку контрагента",
        ],
        self::F_AUT_CNTR_MFO_CITY => [
            'en' => "Counterparty Bank City",
            'ru' => "Город банка контрагента",
            'uk' => "Місто банку контрагента",
        ],
        self::F_CCY => [
            'en' => "Currency",
            'ru' => "Валюта",
            'uk' => "Валюта",
        ],
        self::F_FL_REAL => [
            'en' => "Reality Mark (r,i)",
            'ru' => "Признак реальности (r,i)",
            'uk' => "Ознака реальності (r,i)",
        ],
        self::F_PR_PR => [
            'en' => "Status (p=conducting, t=canceled, r=completed, n=rejected)",
            'ru' => "Статус (p=проводится, t=сторнирована, r=проведена, n=забракована)",
            'uk' => "Стан (p=проводиться, t=сторнована, r=проведена, n=забракована)",
        ],
        self::F_DOC_TYP => [
            'en' => "Payment Document Type",
            'ru' => "Тип платёжного документа",
            'uk' => "Тип платіжного документа",
        ],
        self::F_NUM_DOC => [
            'en' => "Document Number",
            'ru' => "Номер документа",
            'uk' => "Номер документа",
        ],
        self::F_DAT_KL => [
            'en' => "Client Date",
            'ru' => "Клиентская дата",
            'uk' => "Клієнтська дата",
        ],
        self::F_DAT_OD => [
            'en' => "Value Date",
            'ru' => "Дата валютування",
            'uk' => "Дата валютування",
        ],
        self::F_OSND => [
            'en' => "Payment Purpose",
            'ru' => "Основание платежа",
            'uk' => "Підстава платежу",
        ],
        self::F_SUM => [
            'en' => "Amount",
            'ru' => "Сумма",
            'uk' => "Сума",
        ],
        self::F_SUM_E => [
            'en' => "Amount (National Currency)",
            'ru' => "Сумма в нац. валюте (грн)",
            'uk' => "Сума в нац. валюті (грн)",
        ],
        self::F_REF => [
            'en' => "Transaction Reference",
            'ru' => "Референс проведения",
            'uk' => "Референс проведення",
        ],
        self::F_REFN => [
            'en' => "Sequence Number in Transaction",
            'ru' => "№ з/п внутри проведения",
            'uk' => "№ з/п всередині проведення",
        ],
        self::F_TIM_P => [
            'en' => "Transaction Time",
            'ru' => "Время проведения",
            'uk' => "Час проведення",
        ],
        self::F_DATE_TIME_DAT_OD_TIM_P => [
            'en' => "Full Date/Time (DD.MM.YYYY HH:MM:SS)",
            'ru' => "Полная дата/время (ДД.ММ.ГГГГ ЧЧ:ММ:СС)",
            'uk' => "Повна дата/час (ДД.ММ.РРРР ЧЧ:ММ:СС)",
        ],
        self::F_ID => [
            'en' => "Transaction ID (This field is not unique. The bank changed it in March 2026)",
            'ru' => "ID транзакции (Поле не уникальное. Банк его изменял в марте 2026 года)",
            'uk' => "ID транзакції (Поле не унікальне. Банк його змінював у березні 2026 року)",
        ],
        self::F_TRANTYPE => [
            'en' => "Transaction Type (D=Debit, C=Credit)",
            'ru' => "Тип транзакции (D=Дебет, C=Кредит)",
            'uk' => "Тип транзакції (D=Дебет, C=Кредит)",
        ],
        self::F_DLR => [
            'en' => "Payment Service Reference (API payment_pack_ref)",
            'ru' => "Референс сервиса (payment_pack_ref для API)",
            'uk' => "Референс сервісу (payment_pack_ref для API)",
        ],
        self::F_TECHNICAL_TRANSACTION_ID => [
            'en' => "Technical Transaction ID",
            'ru' => "Технический ID транзакции",
            'uk' => "Технічний ID транзакції",
        ],
        self::F_UETR => [
            'en' => "Transaction UUID (UETR)",
            'ru' => "Идентификатор транзакции (UETR)",
            'uk' => "Ідентифікатор транзакції (UETR)",
        ],
        self::F_ULTMT => [
            'en' => "Ultimate Details Type",
            'ru' => "Тип заполненности реквизитов",
            'uk' => "Тип заповненості реквізитів",
        ],
        self::F_PAYER_ULTMT_NCEO => [
            'en' => "Ultimate Payer EDRPOU",
            'ru' => "ЄДРПОУ конечного плательщика",
            'uk' => "ЄДРПОУ кінцевого платника",
        ],
        self::F_PAYER_ULTMT_DOCUMENT => [
            'en' => "Ultimate Payer Passport",
            'ru' => "Паспорт конечного плательщика",
            'uk' => "Паспорт кінцевого платника",
        ],
        self::F_PAYER_ULTMT_NAME => [
            'en' => "Ultimate Payer Name",
            'ru' => "Название конечного плательщика",
            'uk' => "Назва кінцевого платника",
        ],
        self::F_PAYER_ULTMT_COUNTRY_CODE => [
            'en' => "Ultimate Payer Country Code",
            'ru' => "Код страны нерезидента плательщика",
            'uk' => "Код країни нерезидента платника",
        ],
        self::F_RECIPIENT_ULTMT_NCEO => [
            'en' => "Ultimate Recipient EDRPOU",
            'ru' => "ЄДРПОУ конечного получателя",
            'uk' => "ЄДРПОУ кінцевого отримувача",
        ],
        self::F_RECIPIENT_ULTMT_DOCUMENT => [
            'en' => "Ultimate Recipient Passport",
            'ru' => "Паспорт конечного получателя",
            'uk' => "Паспорт кінцевого отримувача",
        ],
        self::F_RECIPIENT_ULTMT_NAME => [
            'en' => "Ultimate Recipient Name",
            'ru' => "Название конечного получателя",
            'uk' => "Назва кінцевого отримувача",
        ],
        self::F_RECIPIENT_ULTMT_COUNTRY_CODE => [
            'en' => "Ultimate Recipient Country Code",
            'ru' => "Код страны нерезидента получателя",
            'uk' => "Код країни нерезидента отримувача",
        ],
        self::F_STRUCT_CODE => [
            'en' => "Payment Type Code",
            'ru' => "Код вида уплаты",
            'uk' => "Код виду сплати",
        ],
        self::F_STRUCT_TYPE => [
            'en' => "Budget Classification Code",
            'ru' => "Код бюджетной классификации",
            'uk' => "Код бюджетної класифікації",
        ],
        self::F_STRUCT_CATEGORY => [
            'en' => "Tax Decision Info",
            'ru' => "Информация о налоговом решении",
            'uk' => "Інформація про податкове рішення",
        ],
    ];



    public static function field_descr(string $field, ?string $lang = null): string
    {
        if (empty($lang)) { $lang = App::lang(); }

        return (empty(self::TRANSACTION_FIELD_DESCRIPTION[$field])
                    ?   ''
                    :   self::TRANSACTION_FIELD_DESCRIPTION[$field][$lang]
                        ?? self::TRANSACTION_FIELD_DESCRIPTION[$field]['ru']
                        ?? self::TRANSACTION_FIELD_DESCRIPTION[$field]['uk']
                        ?? self::TRANSACTION_FIELD_DESCRIPTION[$field]['en']
                        ?? $field
                );
    }



    const TRANSACTION_FIELDS = [
        self::F_REF,
        self::F_REFN,
        self::F_AUT_MY_CRF,
        self::F_AUT_MY_MFO,
        self::F_AUT_MY_ACC,
        self::F_AUT_MY_NAM,
        self::F_AUT_MY_MFO_NAME,
        self::F_AUT_MY_MFO_CITY,
        self::F_AUT_CNTR_CRF,
        self::F_AUT_CNTR_MFO,
        self::F_AUT_CNTR_ACC,
        self::F_AUT_CNTR_NAM,
        self::F_AUT_CNTR_MFO_NAME,
        self::F_AUT_CNTR_MFO_CITY,
        self::F_CCY,
        self::F_FL_REAL,
        self::F_PR_PR,
        self::F_DOC_TYP,
        self::F_NUM_DOC,
        self::F_DAT_KL,
        self::F_DAT_OD,
        self::F_OSND,
        self::F_SUM,
        self::F_SUM_E,
        self::F_TIM_P,
        self::F_DATE_TIME_DAT_OD_TIM_P,
        self::F_ID,
        self::F_TRANTYPE,
        self::F_DLR,
        self::F_TECHNICAL_TRANSACTION_ID,
        self::F_UETR,
        self::F_ULTMT,
        self::F_PAYER_ULTMT_NCEO,
        self::F_PAYER_ULTMT_DOCUMENT,
        self::F_PAYER_ULTMT_NAME,
        self::F_PAYER_ULTMT_COUNTRY_CODE,
        self::F_RECIPIENT_ULTMT_NCEO,
        self::F_RECIPIENT_ULTMT_DOCUMENT,
        self::F_RECIPIENT_ULTMT_NAME,
        self::F_RECIPIENT_ULTMT_COUNTRY_CODE,
        self::F_STRUCT_CODE,
        self::F_STRUCT_TYPE,
        self::F_STRUCT_CATEGORY,
    ];


    /**
     * Имена $_GET переменных для праметров выборки данных из банка
     */
    const F_GET_API           = 'api';
    const F_GET_ACC           = 'acc';
    const F_GET_PPP_ID        = 'ppp_id';
    const F_GET_DATE_START    = 'startDate';
    const F_GET_DATE_END      = 'endDate';
    const F_GET_FOLLOW_ID     = 'followId';
    const F_GET_LIMIT         = 'limit';



    public static function make_header(string $autoclient_id, string $autoclient_token): array {
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



    public static function make_request(string $autoclient_acc, string $date1_ts, string $date2_ts, ?string $followId = null): string {
        return '?'.self::F_GET_ACC.'='.$autoclient_acc
                .'&'.self::F_GET_DATE_START.'='.rawurlencode(date('d-m-Y', $date1_ts))
                .'&'.self::F_GET_DATE_END.'='.rawurlencode(date('d-m-Y', $date2_ts))
                .($followId ? '&'.self::F_GET_FOLLOW_ID.'='.$followId : "")
                .'&'.self::F_GET_LIMIT.'='. App::get_config('bank_limit_per_page');
    }



    public static function get_accounts(array $ppp): array { 



        $autoclient_id      = $ppp[Ppp::F_API_ID];
        $autoclient_token   = $ppp[Ppp::F_API_PASS];
        $autoclient_acc     = str_replace(" ", "", $ppp[Ppp::F_NUMBER]);

        $accounts_url = "https://acp.privatbank.ua/api/statements/balance/final?acc=" . $autoclient_acc;

        $headers = self::make_header($autoclient_id, $autoclient_token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPGET,           1); // Использовать метод GET
        curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers);
        curl_setopt($ch, CURLOPT_URL,               $accounts_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1); // TRUE to return the transfer as a string of the return value of {@see curl_exec()} instead of outputting it directly.
        curl_setopt($ch, CURLOPT_HEADER,            0); // TRUE to include the header in the output.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1); // Проверять SSL сертификат / 1 - проверять, 0 - не проверять

        $res = curl_exec($ch);
        if(curl_errno($ch)!=0) {
            curl_close($ch);
            throw new \Exception('Ошибка выполнения запроса к банку. Сообщите программисту: [' . curl_error($ch) . ']');
        }
        $s = "Данные счетов получены<br>\n";
        MsgQueue::msg(MsgType::INFO, $s);
        curl_close($ch);

        $arr = json_decode($res, true);
        return $arr;

    }



    /**
     * Возвращает список транзакций
     * @param array $ppp
     * @param int $date1_ts
     * @param int $date2_ts
     * @param mixed $trantype
     * @throws \Exception
     * @return array
     */
    public static function get_transactions(array $ppp, int $date1_ts, int $date2_ts, $trantype = Bank::TRANSACTION_TYPE_ALL): array {

        $autoclient_id      = $ppp[Ppp::F_API_ID];
        $autoclient_token   = $ppp[Ppp::F_API_PASS];
        $autoclient_acc     = str_replace(" ", "", $ppp[Ppp::F_NUMBER]);
        $autoclient_url     = $ppp[Ppp::F_API_URL];

        $followId           = null;

        $headers = self::make_header($autoclient_id, $autoclient_token);

        $transactions = [];
        $iteration     = 0;
        do {
            $iteration++;
            $request = self::make_request($autoclient_acc, $date1_ts, $date2_ts, $followId);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPGET,           1); // Использовать метод GET
            curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers);
            curl_setopt($ch, CURLOPT_URL,               $autoclient_url . $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1); // TRUE to return the transfer as a string of the return value of {@see curl_exec()} instead of outputting it directly.
            curl_setopt($ch, CURLOPT_HEADER,            0); // TRUE to include the header in the output.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1); // Проверять SSL сертификат / 1 - проверять, 0 - не проверять

            $res = curl_exec($ch);
            if (curl_errno($ch) != 0) {
                curl_close($ch);
                throw new \Exception('Ошибка выполнения запроса к банку. Сообщите программисту ['.curl_error($ch).']');
            }
            MsgQueue::msg(MsgType::INFO, "Транзакции получены");
            curl_close($ch);

            $arr = json_decode($res, true);
            // echo "ARRAY:<pre>"; var_dump($arr); echo "</pre><hr>";
            //     array(4) {
            //     ["status"]=>
            //     string(5) "ERROR"
            //     ["code"]=>
            //     string(3) "400"
            //     ["message"]=>
            //     string(25) "Wrong parameter startDate"
            //     ["requestId"]=>
            //     string(20) "20260328_035748_0229"
            //     }


            $s  = "Статус ответа банка: ".($arr['status']).(isset($arr['message']) ? " [".paint($arr['message'], color: RED)."]":"")."<br>\n";
            MsgQueue::msg(MsgType::INFO, $s);
            foreach ($arr['transactions'] as $T) {
                switch ($trantype) {
                    case Bank::TRANSACTION_TYPE_ALL:
                        $transactions[] = $T;
                        break;
                    case Bank::TRANSACTION_TYPE_C: // Кредит +
                        if ($T['TRANTYPE'] == 'C') {
                            $transactions[] = $T;
                        }
                        break;
                    case Bank::TRANSACTION_TYPE_D: // Дебет -
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
            $s = "Следующая транзакция: ".($followId ? $followId : "нет")."<br>\n";
            MsgQueue::msg(MsgType::INFO, $s);
        } while ($arr['exist_next_page'] && ($iteration < App::get_config('bank_get_iteration_max')));
        return $transactions;
    }



    public static function renderField(array $rec = [], string $field = '', ?string $label = null, int|string|null $value = null, ?string $tooltip = null) {

        if (is_null($label)) {
            $label = P24acc::field_title($field);
        }
        
        $is_text = false;

        if (is_null($value)) {
            switch (true) {
                case in_array($field, P24acc::FIELDS_CURRENCY):
                    $value = number_format(($rec[$field] ?? 0), 2, '.', ' ');
                    break;
                
                case in_array($field, P24acc::FIELDS_TEXT):
                    $value = h(nl2br($rec[$field] ?? ''));
                    $is_text = true;
                    break;
                
                default:
                    $value = h($rec[$field] ?? '');
                    break;
            }
        }

        if (is_null($tooltip)) {
            $tooltip = P24acc::field_descr($field);
        }

        return  '<div class="mb-3 d-flex justify-content-between align-items-start small">' .
                    '<span class="fw-bold text-muted pe-3" style="min-width: 25%;">' . $label . '</span>' .
                    '<div class="d-flex align-items-start flex-grow-1 justify-content-end">' .
                        '<span class="me-2 '.($is_text ? '' : 'text-nowrap').' text-break text-end">' . $value . '</span>' .
                        '<i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $tooltip . '"></i>' .
                    '</div>' .
                '</div>';
    }






















}