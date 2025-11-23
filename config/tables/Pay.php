<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Pay.php
 *  Path    : config/tables/Pay.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Pay.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Pay {

    /*
     * URI для пользовательских платежей
     */

    const URI_PAY = '/pay';

    /*
     * URI для управления платежами
     */

    const URI_FORM      = '/payments/form';
    const URI_DEL       = '/payments/delete';


    /*
     * URI для личного кабинета абонента
     */
    const URI_MY            = '/payments';
    const URI_LIST          = '/payments/list';



    /**
     * Соответствует ID из таблицы payments_types
     * Фактически аблица типов не нужна,
     * поскольку типов всего три и удобнее их использовать как константы
     */
    const TYPE_MONEY    = 1;    // Денежное пополнение ЛС | Внесение средств на ЛС для оплаты услуг
    const TYPE_CORRECT  = 2;    // Корректировка ЛС       | Начисление для корректировки остатка ЛС, компенсац...
    const TYPE_REQUEST  = 3;    // Начисление за услугу   | Начисление за дополнительную услугу (ремонт, настройка, задолженность за подключение и пр.) как правило, единоразовое начисление.

    const TYPES = [
        self::TYPE_MONEY => [
            'uk' => 'Грошове поповнення ОР',
            'ru' => 'Денежное пополнение ЛС',
            'en' => 'Cash replenishment of personal account',
        ],
        self::TYPE_CORRECT => [
            'uk' => 'Коригування залишку в особистому кабінеті',
            'ru' => 'Корректировка остатка в личном кабинете',
            'en' => 'Adjusting the balance in your personal account',
        ],
        self::TYPE_REQUEST => [
            'uk' => 'Нарахування за додаткову разову послугу',
            'ru' => 'Начисление за дополнительную разовую услугу',
            'en' => 'Charge for additional one-time service',
        ],
    ];

    const POST_REC = 'payment';

    const TABLE = 'payments';

    const F_ID = "id"; // ID платежа

    const F_AGENT_ID        = "agent_id";       // ID того, кто внёс запись
    const F_ABON_ID         = "abon_id";        // Абонент, на которого зачисляется платеж
    const F_PAY_FAKT        = "pay_fakt";       // Фактическая сумма, пришедшая на счёт
    const F_PAY_ACNT        = "pay";            // Сумма платежа, вносимая на ЛС
    const F_DATE            = "pay_date";       // Дата платежа
    const F_DATE_STR        = "pay_date_str";   // Дата платежа в строковом формате
    const F_BANK_NO         = "pay_bank_no";    // Банковский номер операции
    const F_TYPE_ID         = "pay_type_id";    // ИД Типа платежа
    const F_PPP_ID          = "pay_ppp_id";     // ППП
    const F_DESCRIPTION     = "description";    // Описание платежа
    const F_CREATION_DATE   = "created_date";   // Дата создания записи
    const F_CREATION_UID    = "created_uid";    // Юзер, создавший запись
    const F_MODIFIED_DATE   = "modified_date";  // Дата изменения записи
    const F_MODIFIED_UID    = "modified_uid";   // Кто изменил запись

    /*
     * Вычисляемые поля
     */
    // const F_AGENT           = "agent";          // Массив запись User того, кто внёс запись (вычисляемое)
    // const F_PPP             = "ppp";            // Массив запись ППП (вычисляемое)
    // const F_TYPE            = "type";           // Массив запись Типа платежа (вычисляемое)
    const F_AGENT_TITLE     = "agent_title";    // Имя того, кто внёс запись (вычисляемое)
    const F_TYPE_TITLE      = "pay_type_title"; // Имя Типа платежа (вычисляемое)
    const F_PPP_TITLE       = "pay_ppp_title";  // Имя ППП (вычисляемое)



}