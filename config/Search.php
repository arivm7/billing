<?php

namespace config;

use config\tables\Abon;
use config\tables\Pay;
use config\tables\User;

class Search {

    const URI_QUERY = '/search/query';
    const F_QUERY = 'q';

    const F_SEARCH_HERE         = 'search_here';
    const F_TITLE               = 'title';
    const F_TABLE               = 'table';
    const F_SHOW_FIELDS         = 'show_fields';
    const F_COL_TITLES          = 'col_titles';
    const F_ORDER_BY            = 'order_by';
    const F_CELL_ATTRIBUTES     = 'cell_attributes';
    const F_SEARSH_IN_FIELDS    = 'searsh_in_fields';
    const F_REPLACE_FIELDS      = 'replace_fields';
    const F_FIELD               = 'field';
    const F_FUNC                = 'func';

    /**
     * параметр sql запроса LIMIT
     */
    const SEARCH_LIMIT          = '10';
    const SEARCH_LIMIT_FOR_ONE  = '25';



    const SEARCH_PLACES = [

        [
            // 1 -- искать в этой таблице
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Пользователи",
            self::F_TABLE             => User::TABLE,
            self::F_SHOW_FIELDS       => ["id",          "name",       "name_short", "phone_main",   "mail_main",    "address_invoice", "jabber_main",  "viber",        "telegram",     "signal_messenger", "whatsapp"],
            self::F_COL_TITLES        => ["User ID",     "name",       "name_short", "Телефон",      "Эл. почта",    "Почтовый адрес",  "Jabber/XMPP",  "Viber",        "Telegram",     "Signal",           "WhatsApp"],
            self::F_CELL_ATTRIBUTES   => ["align=right", "align=left", "align=left", "align=center", "align=center", "align=center",    "align=center", "align=center", "align=center", "align=center",     "align=center"],
            self::F_SEARSH_IN_FIELDS  => [               "name",       "name_short", "phone_main",   "mail_main",    "address_invoice", "jabber_main",  "viber",        "telegram",     "signal_messenger", "whatsapp"],
            self::F_REPLACE_FIELDS    => [
                [self::F_FIELD => 'id', self::F_FUNC => 'url_user_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Абоненты",
            self::F_TABLE             => Abon::TABLE,
            self::F_SHOW_FIELDS       => ["id",          "user_id",     "address",      "is_payer",     "comments"],
            self::F_COL_TITLES        => ["Abon ID",     "User ID",     "Адрес подкл.", "Плательщик",   "Коментарии"],
            self::F_CELL_ATTRIBUTES   => ["align=right", "align=right", "align=left",   "align=center", "align=left"],
            self::F_SEARSH_IN_FIELDS  => ["is_payer", "address", "comments"],
            self::F_ORDER_BY          => "is_payer DESC, address ASC",
            self::F_REPLACE_FIELDS    => [
                [self::F_FIELD => 'id',         self::F_FUNC => 'url_abon_form'],
                [self::F_FIELD => 'user_id',    self::F_FUNC => 'url_user_form'],
                [self::F_FIELD => 'is_payer',   self::F_FUNC => 'get_html_CHECK']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Платежи",
            self::F_TABLE             => Pay::TABLE,
            self::F_SHOW_FIELDS       => [Pay::F_ID,      Pay::F_ABON_ID, Pay::F_PAY_FAKT, Pay::F_DESCRIPTION],
            self::F_COL_TITLES        => ["Pay ID",       "Abon ID",      "Факт. платёж",  "Описание платежа"],
            self::F_CELL_ATTRIBUTES   => ["align=center", "align=right",  "align=right",   "align=left"],
            self::F_SEARSH_IN_FIELDS  => [Pay::F_DESCRIPTION],
            self::F_ORDER_BY          => "`".Pay::F_ABON_ID."` DESC, `".Pay::F_ID."` DESC",
            self::F_REPLACE_FIELDS    => [
            //     [self::F_FIELD => 'id', self::F_FUNC => 'url_pay_form'],
                [self::F_FIELD => 'abon_id', self::F_FUNC => 'url_abon_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Шаблоны идентификации абонентов",
            self::F_TABLE             => "ts_abons_templates",
            self::F_SHOW_FIELDS       => ["ppp_id",       "abon_id",      "template"],
            self::F_COL_TITLES        => ["ППП ID",       "Abon ID",      "Шаблон абонента"],
            self::F_CELL_ATTRIBUTES   => ["align=center", "align=center", "align=left"],
            self::F_SEARSH_IN_FIELDS  => ["template"],
            self::F_ORDER_BY          => "ppp_id DESC, abon_id DESC, template ASC",
            self::F_REPLACE_FIELDS    => [
            //     [self::F_FIELD => 'ppp_id', self::F_FUNC => 'url_ppp_form_22'],
                [self::F_FIELD => 'abon_id', self::F_FUNC => 'url_abon_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Дополнительные контакты",
            self::F_TABLE             => "phone_numbers",
            self::F_SHOW_FIELDS       => ["id", "is_deleted", "user_id", "phone_title", "phone_number"],
            self::F_COL_TITLES        => ["ID", "Скрыт",      "User ID", "название",    "Контакт"],
            self::F_SEARSH_IN_FIELDS  => ["phone_title", "phone_number"],
            self::F_REPLACE_FIELDS    => [
                [self::F_FIELD => 'is_payer', self::F_FUNC => 'get_html_CHECK'],
                [self::F_FIELD => 'user_id',  self::F_FUNC => 'url_user_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Подключённые прайсовые фрагменты",
            self::F_TABLE             => "prices_apply",
            self::F_SHOW_FIELDS       => ["id",           "abon_id",      "net_name",   "net_nat11",  "net_ip",     "net_mac",    "price_closed", "net_router_id"],
            self::F_COL_TITLES        => ["PA ID",        "Abon ID",      "Имя подкл.", "NAT 1:1",    "ip",         "MAC",        "Закрыт",       "ТП ID"],
            self::F_CELL_ATTRIBUTES   => ["align=center", "align=center", "align=left", "align=left", "align=left", "align=left", "align=center", "align=left"],
            self::F_SEARSH_IN_FIELDS  => ["net_name", "net_on_abon_ip", "net_on_abon_gate", "net_nat11", "net_ip", "net_mask", "net_dns1", "net_dns2", "net_mac"],
            self::F_ORDER_BY          => "abon_id DESC, price_closed DESC, id DESC",
            self::F_REPLACE_FIELDS    => [
            //     [self::F_FIELD => 'id',            self::F_FUNC => 'url_pa_form'],
                [self::F_FIELD => 'abon_id',       self::F_FUNC => 'url_abon_form'],
                [self::F_FIELD => 'price_closed',  self::F_FUNC => 'get_html_CHECK'],
                [self::F_FIELD => 'net_router_id', self::F_FUNC => 'url_tp_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Технические площадки, точки доступа",
            self::F_TABLE             => "tp_list",
            self::F_SHOW_FIELDS       => ["id", "title", "ip", "login", "address", "status", "description", "cost_per_M_description", "cost_tp_description"],
            self::F_SEARSH_IN_FIELDS  => ["title", "ip", "login", "url", "url_zabbix", "address", "status", "web_management", "description", "cost_per_M_description", "cost_tp_description", "script_mik_ip", "script_mik_port", "script_mik_login", "script_ftp_ip", "script_ftp_port", "script_ftp_login", "script_ftp_folder", "script_ftp_getpath"],
            self::F_REPLACE_FIELDS    => [
                [self::F_FIELD => 'id', self::F_FUNC => 'url_tp_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Выписанные Счета-фактуры и Акты",
            self::F_TABLE             => "sf_list",
            self::F_SHOW_FIELDS       => ["id", "user_id", "abon_id", "sf_no", "sf_firm", "sf_text"],
            self::F_SEARSH_IN_FIELDS  => ["sf_text"],
            self::F_REPLACE_FIELDS    => [
                [self::F_FIELD => 'user_id', self::F_FUNC => 'url_user_form'],
                [self::F_FIELD => 'abon_id', self::F_FUNC => 'url_abon_form']
            ]
        ],

        [
            self::F_SEARCH_HERE       => 1,
            self::F_TITLE             => "Предприятия",
            self::F_TABLE             => "firm_list",
            self::F_SHOW_FIELDS       => ["id", "name_short", "name_long", "name_title", "manager_job_title", "manager_name_short", "manager_name_long", "cod_EDRPOU", "cod_IPN", "registration", "address_registration", "address_office_full", "address_post_person", "address_post_ul", "address_post_dom", "address_post_sity", "address_post_region", "address_post_country", "address_office_courier", "office_phones", "bank_IBAN", "bank_name"],
            self::F_SEARSH_IN_FIELDS  => ["name_short", "name_long", "name_title", "manager_job_title", "manager_name_short", "manager_name_long", "cod_EDRPOU", "cod_IPN", "registration", "address_registration", "address_office_full", "address_post_person", "address_post_index", "address_post_ul", "address_post_dom", "address_post_sity", "address_post_region", "address_post_country", "address_office_courier", "office_phones", "bank_IBAN", "bank_name"],
            self::F_REPLACE_FIELDS    => [
                // [self::F_FIELD => 'id', self::F_FUNC => 'url_firm_form']
            ]
        ],

    ];
    
}
