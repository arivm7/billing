<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Firm.php
 *  Path    : config/tables/Firm.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Firm.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Firm {

    public const URI_INDEX      = '/firms';
    public const URI_STATUS     = '/firms/status'; // для изменения состояния флагов F_HAS_*


    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID       = 'firm_id';

    /**
     * имя массива в котором в POST-запросе хранятся данные формы
     */
    public const POST_REC       = 'firmRec';

    /**
     * Имя таблицы в базе данных
     */
    public const TABLE          = 'firm_list';

    public const F_ID                   = 'id';                       // ID предприятия
    public const F_HAS_ACTIVE           = 'has_active';              // Предприятие активно (участвует в выписке)
    public const F_HAS_DELETE           = 'has_delete';              // Предприятие считается удалённым
    public const F_HAS_AGENT            = 'has_agent';               // Предприятие-агент (наше)
    public const F_HAS_CLIENT           = 'has_client';              // Предприятие-клиент
    public const F_HAS_ALL_VISIBLE      = 'has_all_visible';         // Видимое для всех
    public const F_HAS_ALL_LINKING      = 'has_all_linking';         // Разрешено подключать всем

    public const F_NAME_SHORT           = 'name_short';              // Краткое название
    public const F_NAME_LONG            = 'name_long';               // Полное название
    public const F_NAME_TITLE           = 'name_title';              // Название сети для рассылки

    public const F_MANAGER_JOB_TITLE    = 'manager_job_title';       // Должность ответственного
    public const F_MANAGER_NAME_SHORT   = 'manager_name_short';      // ФИО
    public const F_MANAGER_NAME_LONG    = 'manager_name_long';       // Фамилия Имя Отчество

    public const F_COD_EDRPOU           = 'cod_EDRPOU';              // ЕДРПОУ
    public const F_COD_IPN              = 'cod_IPN';                 // ИПН

    public const F_REGISTRATION         = 'registration';            // Регистрационные данные

    public const F_ADDRESS_REGISTRATION = 'address_registration';    // Адрес регистрации
    public const F_ADDRESS_OFFICE_FULL  = 'address_office_full';     // Адрес офиса
    public const F_ADDRESS_POST_PERSON  = 'address_post_person';     // От кого (для почты)
    public const F_ADDRESS_POST_INDEX   = 'address_post_index';      // Почтовый индекс
    public const F_ADDRESS_POST_UL      = 'address_post_ul';         // Улица
    public const F_ADDRESS_POST_DOM     = 'address_post_dom';        // Дом, корпус, строение, кв.
    public const F_ADDRESS_POST_SITY    = 'address_post_sity';       // Город
    public const F_ADDRESS_POST_REGION  = 'address_post_region';     // Область / регион
    public const F_ADDRESS_POST_COUNTRY = 'address_post_country';    // Страна
    public const F_ADDRESS_OFFICE_COURIER = 'address_office_courier';// Адрес для курьера

    public const F_OFFICE_PHONES        = 'office_phones';           // Телефоны предприятия

    public const F_BANK_IBAN            = 'bank_IBAN';               // Р/с IBAN
    public const F_BANK_NAME            = 'bank_name';               // Название банка

    public const F_PPP_DEFAULT_ID       = 'ppp_default_id';          // ID ППП по умолчанию

    public const F_CREATION_UID         = 'creation_uid';            // Кто создал запись
    public const F_CREATION_DATE        = 'creation_date';           // Дата создания
    public const F_MODIFIED_UID         = 'modified_uid';            // Кто модифицировал
    public const F_MODIFIED_DATE        = 'modified_date';           // Дата модификации

}