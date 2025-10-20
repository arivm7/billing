<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Ppp.php
 *  Path    : config/tables/Ppp.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Ppp.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Ppp {

    const URI_INDEX = "/ppp";
    const URI_EDIT = "/ppp/edit";
    const URI_DELETE = "/ppp/delete";

    const POST_REC = "ppp_item";

    /**
     * Имя таблицы в базе данных
     */
    public const TABLE = 'ppp_list';

    /* =========================
       Поля таблицы
       ========================= */

    public const F_ID                      = 'id';                  // ID записи
    public const F_ORDER_NUM                = 'order_num';          // Порядок сортировки
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
        /**
         * Расчетно-кассовое обслуживание счёта, процентное значение
         */
        self::F_RKO_PERCENT=>['title'=>[
            'ru'=>'РКО: процент от платежа',
            'ua'=>'РКО: відсоток від платежу',
            'en'=>'RKO: percent of payment',
        ],'suffix'=>'%'],
        
        /**
         * Расчетно-кассовое обслуживание счёта: фиксированный платеж, грн/мес
         */
        self::F_RKO_FIXED_PM=>['title'=>[
            'ru'=>'РКО: фиксированный платёж / мес',
            'ua'=>'РКО: фіксований платіж / міс',
            'en'=>'RKO: fixed payment / month',
        ],'suffix'=>'грн/мес'],

        /**
         * Налог, процентное значение
         */
        self::F_TAX_PERCENT=>['title'=>[
            'ru'=>'Налог: процент от платежей',
            'ua'=>'Податок: відсоток від платежів',
            'en'=>'Tax: percent of payments',
        ],'suffix'=>'%'],

        /**
         * Налог: фиксированный платеж, грн/мес
         */
        self::F_TAX_FIXED_PM=>['title'=>[
            'ru'=>'Налог: фиксированный платёж / мес',
            'ua'=>'Податок: фіксований платіж / міс',
            'en'=>'Tax: fixed payment / month',
        ],'suffix'=>'грн/мес'],

        /**
         * Комиссия при обналичивании
         */
        self::F_CASHING_COMMISSION=>['title'=>[
            'ru'=>'Комиссия при обналичивании',
            'ua'=>'Комісія при обналичуванні',
            'en'=>'Commission for cashing out',
        ],'suffix'=>'%'],

        /**
         * Округление комиссии до указанного коэффициента
         */
        self::F_API_AUTO_RET_COMM=>['title'=>[
            'ru'=>'Округление комиссии до указанного коэффициента',
            'ua'=>'Округлення комісії до вказаного коефіцієнта',
            'en'=>'Rounding of commission to the specified coefficient',
        ],'suffix'=>'Koef.'],

        /**
         * Коэффициент возврата комиссии LiqPay
         */
        self::F_API_LIQPAY_RET_COMM=>['title'=>[
            'ru'=>'Коэффициент возврата комиссии LiqPay',
            'ua'=>'Коефіцієнт повернення комісії LiqPay',
            'en'=>'LiqPay Commission Refund Rate',
        ],'suffix'=>'Koef.'],
    ];

    /**
     * Список полей-флагов (boolean) для обработки из POST данных
     */
    public const FLAGS = [
        self::F_ACTIVE,
        self::F_ABON_PAYMENTS,
        self::F_API_AUTO_PAY_REG,
    ];

    /**
     * Список поле с URL адресами, для корректного сохранения в базе
     */
    public const URLS = [
        self::F_API_URL,             // api_url
        self::F_API_LIQPAY_URL,      // LiqPay URL
        self::F_API_24PAY_URL,       // URL формы Приват24
    ];  

    /**
     * Шаблоны для подстановки в тексты
     * 
     * IBAN: {NUMBER}\nОтримувач: {NUMBER_INFO}\nПризначення платежу: За послуги доступу до мережі інтернет, дог. {PORT}
     * May contain placeholders    {PORT}, {LOGIN}, {SUM} 
     * Может содержать подстановки {PORT}, {LOGIN}, {SUM}
     * Може містити підстановки    {PORT}, {LOGIN}, {SUM}
    */

    const TMPL_NUMBER = 'NUMBER';
    const TMPL_NUMBER_INFO = 'NUMBER_INFO';
    const TMPL_PORT = 'PORT';
    const TMPL_LOGIN = 'LOGIN';
    const TMPL_SUM = 'SUM';

    public const TEMPLATES = [
        self::TMPL_NUMBER,
        self::TMPL_NUMBER_INFO,
        self::TMPL_PORT,
        self::TMPL_LOGIN,
        self::TMPL_SUM,
    ];

    /**
     * Заменяет шаблоны вида {NUMBER}, {NUMBER_INFO}, {PORT}, {LOGIN}, {SUM}
     * на значения из массива $options. Ключи в $options могут быть в верхнем
     * или нижнем регистре (например: 'NUMBER' или 'number').
     *
     * @param string $text    Текст с шаблонами
     * @param array  $options Ассоциативный массив значений для подстановки
     * @return string Текст с подставленными значениями
     */
    public static function applyTemplates(string $text, array $options = []): string
    {
        if ($text === '' || empty(self::TEMPLATES)) {
            return $text;
        }

        $replacements = [];
        foreach (self::TEMPLATES as $token) {
            $placeholder = '{' . $token . '}';

            if (array_key_exists($token, $options)) {
                $value = $options[$token];
            } elseif (array_key_exists(mb_strtolower($token), $options)) {
                $value = $options[mb_strtolower($token)];
            } else {
                $value = '';
            }

            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $value = (string) $value;
            }

            $replacements[$placeholder] = $value;
        }

        // mb-safe замена через str_replace (т.к. токены ASCII)
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }


}