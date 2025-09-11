<?php



namespace config\tables;



class Ppp {

    /**
     * Имя таблицы в базе данных
     */
    public const TABLE = 'ppp_list';

    /* =========================
       Поля таблицы
       ========================= */

    public const F_ID                      = 'id';                  // ID записи
    public const F_FIRM_ID                 = 'firm_id';             // ID предприятия
    public const F_TITLE                   = 'title';               // Название источника приема платежей
    public const F_OWNER_ID                = 'owner_id';            // ID владельца счета/кассы
    public const F_ACTIVE                  = 'active';              // Активен ли ППП
    public const F_ABON_PAYMENTS           = 'abon_payments';       // Показывать в списке для абонентов
    public const F_TYPE_ID                 = 'type_id';             // Тип ППП: 0-хз, 1-Банк, 2-Карта, 3-Терминал
    public const F_NUMBER_PREFIX           = 'number_prefix';       // Префиксный текст для публикации
    public const F_NUMBER                  = 'number';              // Номер счета, карты
    public const F_NUMBER_INFO             = 'number_info';         // Дополнительные данные (ФИО, ИНН и т.п.)
    public const F_NUMBER_PURPOSE          = 'number_purpose';      // Назначение платежа
    public const F_NUMBER_COMMENT          = 'number_comment';      // Комментарий к платежу
    public const F_SMS_PAY_INFO            = 'sms_pay_info';        // Текст для СМС абоненту
    public const F_SUPPORT_PHONES          = 'support_phones';      // Телефоны техподдержки

    public const F_RKO_PERCENT             = 'rko_percent';         // РКО: процент от оборота
    public const F_RKO_FIXED_PM            = 'rko_fixed_pm';        // РКО: фиксированный платеж / мес
    public const F_TAX_PERCENT             = 'tax_percent';         // Налог, %
    public const F_TAX_FIXED_PM            = 'tax_fixed_pm';        // Налог, фиксированный платеж / мес
    public const F_CASHING_COMMISSION      = 'cashing_commission';  // Комиссия при обналичивании

    public const F_API_TYPE                = 'api_type';            // Тип API (p24_card, p24_acc и т.д.)
    public const F_API_ID                  = 'api_id';              // merchant_id | client_id
    public const F_API_PASS                = 'api_pass';            // merchant_pass | client_token
    public const F_API_URL                 = 'api_url';             // api_url

    public const F_API_AUTO_PAY_REG        = 'api_auto_pay_registration';     // Автоматическая регистрация платежей
    public const F_API_AUTO_RET_COMM       = 'api_auto_retunt_comission';     // Округление комиссии до коэффициента

    public const F_API_LIQPAY_PUBLIC       = 'api_liqpay_public';   // LiqPay public
    public const F_API_LIQPAY_PRIVATE      = 'api_liqpay_private';  // LiqPay private
    public const F_API_LIQPAY_URL          = 'api_liqpay_url';      // LiqPay URL
    public const F_API_LIQPAY_RET_COMM     = 'api_liqpay_return_comission';   // Возврат комиссии LiqPay

    public const F_API_24PAY_IDENT         = 'api_24pay_ident';     // Идентификатор в базе Приват24
    public const F_API_24PAY_URL           = 'api_24pay_url';       // URL формы Приват24

    public const F_CREATION_DATE           = 'creation_date';       // Дата создания
    public const F_CREATION_UID            = 'creation_uid';        // Кто создал
    public const F_MODIFIED_DATE           = 'modified_date';       // Дата изменения
    public const F_MODIFIED_UID            = 'modified_uid';        // Кто изменил

    /* =========================
       Группировки / служебные
       ========================= */

    /**
     * Поля с денежными/комиссионными значениями
     */
    public const F_COMMISSIONS = [
        self::F_RKO_PERCENT,
        self::F_RKO_FIXED_PM,
        self::F_TAX_PERCENT,
        self::F_TAX_FIXED_PM,
        self::F_CASHING_COMMISSION,
        self::F_API_AUTO_RET_COMM,
        self::F_API_LIQPAY_RET_COMM,
    ];



}
