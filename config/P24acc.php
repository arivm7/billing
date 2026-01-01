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

class P24acc {

    // Приклад відповіді з транзакціями
    // {
    //   "status": "SUCCESS", // ознака успішності відповіді

    //   "code": "400",
    //   "message": "invalid document number",
    //   "requestId": "20240223_131617_286f",
    //   "serviceCode": "PMTSRV0112"

    //   "type": "transactions", // тип відповіді
    //   "exist_next_page": true, // ознака наявності наступної пачки
    //   "next_page_id": "620699370_online", // ідентифікатор наступної пачки (підставляється у followId наступного запиту)
    //   "transactions": [ // масив об’єктів із транзакціями
    //     {
    //       "AUT_MY_CRF": "31451288", // ЄДРПОУ одержувача
    //       "AUT_MY_MFO": "305299", // МФО одержувача
    //       "AUT_MY_ACC": "26184050001514", // рахунок одержувача
    //       "AUT_MY_NAM": "Програмiсти та Ko МСБ-ТЕСТ ТОВ", // назва одержувача
    //       "AUT_MY_MFO_NAME": "АТ КБ \"ПРИВАТБАНК\"", // банк одержувача
    //       "AUT_MY_MFO_CITY": "Київ", // назва міста банку
    //       "AUT_CNTR_CRF": "14360570", // ЄДРПОУ контрагента
    //       "AUT_CNTR_MFO": "305299", // МФО контрагента
    //       "AUT_CNTR_ACC": "70214924104032", // рахунок контрагента
    //       "AUT_CNTR_NAM": "ПРОЦ ВИТР ЗА СТРОК КОШТ СУБ(UAH)", // назва контрагента
    //       "AUT_CNTR_MFO_NAME": "АТ КБ \"ПРИВАТБАНК\"", // назва банку контрагента
    //       "AUT_CNTR_MFO_CITY": "Київ", // назва міста банку
    //       "CCY": "UAH", // валюта
    //       "FL_REAL": "r", // ознака реальності проведення (r,i)
    //       "PR_PR": "r", // стан p - проводиться, t - сторнована, r - проведена, n - забракована
    //       "DOC_TYP": "m", // тип пл. документа
    //       "NUM_DOC": "K0108B1WKX", // номер документа
    //       "DAT_KL": "07.01.2020", // клієнтська дата
    //       "DAT_OD": "07.01.2020", // дата валютування
    //       "OSND": "Нарахування вiдсоткiв згiдно депозитного договору N...", // підстава  платежу
    //       "SUM": "0.01", // сума
    //       "SUM_E": "0.01", // сума в національній валюті (грн)
    //       "REF": "DNCHK0108B1WKX", // референс проведення
    //       "REFN": "1", // № з/п всередині проведення
    //       "TIM_P": "02:58", // час проведення
    //       "DATE_TIME_DAT_OD_TIM_P": "07.01.2020 02:58:00",
    //       "ID": "557091731", // ID транзакції
    //       "TRANTYPE": "C", // тип транзакції дебет/кредит (D, C)
    //       "DLR": "J63DNDSM0XHY5", // референс платежу сервісу, через який створювали платіж (payment_pack_ref - у разі створення платежу через АPI «Автоклієнт»)
    //       "TECHNICAL_TRANSACTION_ID": "557091731_online"
    //             }, {...}
    // ]}
    //
    //       Для виконання вимог НБУ, у відповіді можуть бути також додаткові поля, які відносяться до кінцевого платника/отримувача:
    //       "UETR":"b23aeadc-1ab7-4c34-a005-0f005a059948", // ідентифікатор тразакції
    //       "ULTMT":"N", // тип заповненості реквізитів
    //       "PAYER_ULTMT_NCEO":"", // ЄДРПОУ кінцевого платника
    //       "PAYER_ULTMT_DOCUMENT":"", // серія, номер паспорту кінцевого платника
    //       "PAYER_ULTMT_NAME":"", // назва кінцевого платника
    //       "PAYER_ULTMT_COUNTRY_CODE":"", // код країни нерезидента кінцевого платника
    //       "RECIPIENT_ULTMT_NCEO":"", // ЄДРПОУ кінцевого отримувача
    //       "RECIPIENT_ULTMT_DOCUMENT":"", // серія, номер паспорту кінцевого отримувача
    //       "RECIPIENT_ULTMT_NAME":"", // назва кінцевого отримувача
    //       "RECIPIENT_ULTMT_COUNTRY_CODE":"" // код країни нерезидента кінцевого отримувача
    //       Для податкових платежів також додані нові поля для структурованого призначення платежу:
    //       "STRUCT_CODE":”101”, // код виду сплати
    //       "STRUCT_TYPE":”22080000”, // код бюджетної класифікації
    //       "STRUCT_CATEGORY":”Довільний текст” // Інформація про податкове повідомлення (рішення)
    //       Для ідентифікації унікальності платіжних інструкцій використовуйте конкатенацію полів REF+REFN 

    const F_RESPONSE_STATUS = 'status';                    // "SUCCESS" | "ERROR" -- ознака успішності відповіді
    const F_RESPONSE_TYPE = 'type';                        // "transactions" -- тип відповіді
    const F_RESPONSE_EXIST_NEXT_PAGE = 'exist_next_page';  // true -- ознака наявності наступної пачки
    const F_RESPONSE_NEXT_PAGE_ID = 'next_page_id';        // "12345678_online" -- ідентифікатор наступної пачки (підставляється у followId наступного запиту)
    const F_TRANSACTIONS = 'transactions';                 // масив об’єктів із транзакціями

    // Поля об'єкта транзакції F_TRANSACTIONS[...]
    const F_AUT_MY_CRF = 'AUT_MY_CRF';                  // ЄДРПОУ одержувача
    const F_AUT_MY_MFO = 'AUT_MY_MFO';                  // МФО одержувача
    const F_AUT_MY_ACC = 'AUT_MY_ACC';                  // рахунок одержувача
    const F_AUT_MY_NAM = 'AUT_MY_NAM';                  // назва одержувача
    const F_AUT_MY_MFO_NAME = 'AUT_MY_MFO_NAME';        // назва банку одержувача
    const F_AUT_MY_MFO_CITY = 'AUT_MY_MFO_CITY';        // назва міста банку
    const F_AUT_CNTR_CRF = 'AUT_CNTR_CRF';              // ЄДРПОУ контрагента
    const F_AUT_CNTR_MFO = 'AUT_CNTR_MFO';              // МФО контрагента
    const F_AUT_CNTR_ACC = 'AUT_CNTR_ACC';              // рахунок контрагента
    const F_AUT_CNTR_NAM = 'AUT_CNTR_NAM';              // назва контрагента
    const F_AUT_CNTR_MFO_NAME = 'AUT_CNTR_MFO_NAME';    // назва банку контрагента
    const F_AUT_CNTR_MFO_CITY = 'AUT_CNTR_MFO_CITY';    // назва міста банку
    const F_CCY = 'CCY';                                // валюта
    const F_FL_REAL = 'FL_REAL';                        // ознака реальності проведення (r,i)
    const F_PR_PR = 'PR_PR';                            // стан p - проводиться, t - сторнована, r - проведена, n - забракована
    const F_DOC_TYP = 'DOC_TYP';                        // "m" -- тип пл. документа
    const F_NUM_DOC = 'NUM_DOC';                        // "K0108B1WKX" -- номер документа
    const F_DAT_KL = 'DAT_KL';                          // "07.01.2020" -- клієнтська дата
    const F_DAT_OD = 'DAT_OD';                          // "07.01.2020" -- дата валютування
    const F_OSND = 'OSND';                              // "Нарахування вiдсоткiв згiдно депозитного договору N..." -- підстава  платежу
    const F_SUM = 'SUM';                                // сума
    const F_SUM_E = 'SUM_E';                            // сума в національній валюті (грн)
    const F_REF = 'REF';                                // "DNCHK0108B1WKX" -- референс проведення
    const F_REFN = 'REFN';                              // "1" -- № з/п всередині проведення
    const F_TIM_P = 'TIM_P';                            // "02:58" -- час проведення
    const F_DATE_TIME_DAT_OD_TIM_P = 'DATE_TIME_DAT_OD_TIM_P';  // "07.01.2020 02:58:00"
    const F_ID = 'ID';                                  // "557091731" -- ID транзакції
    const F_TRANTYPE = 'TRANTYPE';                      // тип транзакції дебет/кредит (D, C)
    const F_DLR = 'DLR';                                // "J63DNDSM0XHY5" -- референс платежу сервісу, через який створювали платіж (payment_pack_ref - у разі створення платежу через АPI «Автоклієнт»)
    const F_TECHNICAL_TRANSACTION_ID = 'TECHNICAL_TRANSACTION_ID';  // "557091731_online"

    // Для виконання вимог НБУ, у відповіді можуть бути також додаткові поля, які відносяться до кінцевого платника/отримувача:
    const F_UETR = 'UETR';    // ідентифікатор тразакції
    const F_ULTMT = 'ULTMT';  // "N" -- тип заповненості реквізитів
    const F_PAYER_ULTMT_NCEO = 'PAYER_ULTMT_NCEO';  // ЄДРПОУ кінцевого платника
    const F_PAYER_ULTMT_DOCUMENT = 'PAYER_ULTMT_DOCUMENT';  // серія, номер паспорту кінцевого платника
    const F_PAYER_ULTMT_NAME = 'PAYER_ULTMT_NAME';  // назва кінцевого платника
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

    const TRANSACTIONS = [
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
        self::F_REF,
        self::F_REFN,
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




}