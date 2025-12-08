<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Invoice.php
 *  Path    : config/tables/Invoice.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 06 Dec 2025 03:15:34
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



namespace config\tables;

/**
 * Класс-обёртка для таблицы выписанных счетов (sf_list)
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Invoice {

    /* --------------------
     * URI контроллера
     * -------------------- */

    const URI_INDEX  = "/invoice";          // список абонентов, из которых нужно выбрать чей список счетов смотреть
    const URI_LIST   = "/invoice/list";     // список счетов указанного абонента
    const URI_PRINT  = "/invoice/print";    // просмотра для печати счета/акта.
    const URI_CREATE = "/invoice/new";
    const URI_EDIT   = "/invoice/edit";
    const URI_DELETE = "/invoice/delete";

    /**
     * имя массива формы (POST)
     */
    const POST_REC = 'post_SF';

    /**
     * имя таблицы
     */
    const TABLE = 'sf_list';


    /* --------------------
     * имена полей таблицы
     * -------------------- */

    const F_ID                  = 'id';                 // ID записи
    const F_FIRM_AGENT_ID       = 'firm_agent_id';      // предприятие-Исполнитель
    const F_FIRM_CONTRAGENT_ID  = 'firm_contragent_id'; // предприятие-Заказчик
    const F_ABON_ID             = 'abon_id';            // ID Абонент
    const F_INV_NO              = 'sf_no';              // СФ №
    const F_INV_DATE_STR        = 'sf_date';            // Дата счёта
    const F_AKT_DATE_STR        = 'akt_date';           // Дата Акта
    const F_FIRM_PAYER_STR      = 'sf_firm';            // Предприятие плательщик
    const F_COST_1              = 'sf_cost_1';          // Цена за 1
    const F_COUNT               = 'sf_count';           // Количество
    const F_COST_ALL            = 'sf_cost_all';        // Цена всего
    const F_TEXT                = 'sf_text';            // Назначение платежа
    const F_IS_PAID             = 'sf_is_paid';         // Счёт оплачен (флаг)

    const F_MODIFIED_UID        = 'modified_uid';       // ID пользователя, изменившего запись
    const F_MODIFIED_DATE       = 'modified_date';      // Дата-время модификации
    const F_CREATION_UID        = 'creation_uid';       // ID пользователя, Кто создал заппись
    const F_CREATION_DATE       = 'creation_date';      // Дата создания записи



    /**
     * Управляющие флаги для отображения отчёта Счёта/Акта
     */

    const F_URI_SHTAMP  = 'sht';    // Отображать штамп и подпись
    const F_URI_INV     = 'inv';    // Отображать Счёт
    const F_URI_ACT     = 'act';    // Отображать Акт



    /* --------------------
     * типы данных
     * -------------------- */

    // флаги (tinyint bool)
    const FLAGS = [
        self::F_IS_PAID,
    ];

    // строковые поля
    const STR_TYPES = [
        self::F_INV_NO,
        self::F_INV_DATE_STR,
        self::F_FIRM_PAYER_STR,
        self::F_TEXT,
        self::F_AKT_DATE_STR,
    ];

    // числовые поля
    const INT_TYPES = [
        self::F_ID,
        self::F_FIRM_CONTRAGENT_ID,
        self::F_FIRM_AGENT_ID,
        self::F_ABON_ID,
    ];

    const FLOAT_TYPES = [
        self::F_COST_1,
        self::F_COUNT,
        self::F_COST_ALL,
    ];

    // строковые поля
    const AUTOCORRECT_FIELDS = [
        self::F_FIRM_PAYER_STR,
        self::F_TEXT,
    ];





    /* --------------------
     * вычисляемые поля
     * -------------------- */

    // const F_ABON_NAME = 'abon_name';     // пример: поле из join
    // const F_USER_LOGIN = 'user_login';   // пример: поле из join

}