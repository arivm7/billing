<?php



namespace config\tables;



class Pay {


    /**
     * Соответствует ID из таблицы payments_types
     * Фактически аблица типов не нужна,
     * поскольку типов всего три и удобнее их использовать как константы
     */
    const TYPE_MONEY    = 1;    // Денежное пополнение ЛС | Внесение средств на ЛС для оплаты услуг
    const TYPE_CORRECT  = 2;    // Корректировка ЛС       | Начисление для корректировки остатка ЛС, компенсац...
    const TYPE_REQUEST  = 3;    // Начисление за услугу   | Начисление за дополнительную услугу (ремонт, настройка, задолженность за подключение и пр.) как правило, единоразовое начисление.


    const POST_REC = 'payment';

    const TABLE = 'payments';

    const F_ID = "id"; // ID платежа

    const F_AGENT_ID        = "agent_id";       // ID того, кто внёс запись
    const F_ABON_ID         = "abon_id";        // Абонент, на которого зачисляется платеж
    const F_PAY_FAKT        = "pay_fakt";       // Фактическая сумма, пришедшая на счёт
    const F_PAY             = "pay";            // Сумма платежа, вносимая на ЛС
    const F_PAY_DATE        = "pay_date";       // Дата платежа
    const F_PAY_BANK_NO     = "pay_bank_no";    // Банковский номер операции
    const F_PAY_TYPE_ID     = "pay_type_id";    // ИД Типа платежа
    const F_PAY_PPP_ID      = "pay_ppp_id";     // ППП
    const F_PAY_SOURCE_ID   = "pay_sourse_id";  // На какой счёт пришёл платёж
    const F_DESCRIPTION     = "description";    // Описание платежа
    const F_CREATED_DATE    = "created_date";   // Дата создания записи
    const F_CREATED_UID     = "created_uid";    // Юзер, создавший запись
    const F_MODIFIED_DATE   = "modified_date";  // Дата изменения записи
    const F_MODIFIED_UID    = "modified_uid";   // Кто изменил запись


    /*
     * URI для управления платежами
     */

    const URI_LIST = '/admin/payments/list';
    const URI_FORM = '/admin/payments/form';
    const URI_DEL = '/admin/payments/delete';
    const URI_ACCESS = '/admin/payments/access';



}
