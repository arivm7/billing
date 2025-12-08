<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Module.php
 *  Path    : config/tables/Module.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Module.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Module {

    /**
     *
     * Концепция Модуля.
     *
     * Модуль -- это НЕ класс или метод.
     * Модуль -- это просто номер в таблице прав доступов.
     * Для любого элемента управления или любой части кода можно выполниять проверку вида
     * get_access(module_id) и на основании полученных прав принимать решение о разрешениях.
     * При этом module_id -- это просто номер, он не привязан к конкретным классам/методам.
     * Его можно применять даже при построении списка меню:
     * поскольку меню собирается из базы, то каждый пункт меню можно привязать в базе к определенному модулю,
     * и при сборке меню нужно учитывать права доступа текущего пользователя к этому модулю.
     *
     */

    /*
     * ID Модулей из базы
     *
     * Важно:
     * в случае изменения номера модуля в базе нужно изменить тут
     *
     */
    const MOD_USER_CARD     = 42;   const MOD_MY_USER_CARD    = 23;
    const MOD_CONTACTS      =  3;   const MOD_MY_CONTACTS     = 24;
    const MOD_ABON          = 28;   const MOD_MY_ABON         = 25;
    const MOD_PA            =  6;   const MOD_MY_PA           = 26;
    const MOD_FIRM          = 22;   const MOD_MY_FIRM         = 31;
    const MOD_CONCILIATION  = 35;   const MOD_MY_CONCILIATION = 34;
    const MOD_NOTICE        = 37;   const MOD_MY_NOTICE       = 38;
    const MOD_PAYMENTS      = 40;   const MOD_MY_PAYMENTS     = 41;
    const MOD_INVOICES      = 45;   const MOD_MY_INVOICES     = 46;
    const MOD_FIRM_STATUS   = 33;
    const MOD_MODULES       = 32;
    const MOD_DOCS          = 30;
    const MOD_TP            = 29;
    const MOD_SEARCH        = 36;
    const MOD_WEB_DEBUG     = 39;
    const MOD_PPP           = 43;
    const MOD_ADMIN_MENU    = 27;
    const MOD_MONITORING    = 44;





    const URI_LIST          = '/admin/module/list';
    const URI_FORM          = '/admin/module/form';
    const URI_DEL           = '/admin/module/delete';
    const URI_ACCESS        = '/admin/module/access';

    /*
     * Табличные параметры
     */

    const POST_REC          = 'module';

    const TABLE             = 'adm_module_list';

    const F_ID              = "id";             // ID административного модуля

    const  _TITLE           =   "_title";       // суффикс поля. Полное поле: язык + суффикс
    const F_UK_TITLE        = "uk_title";       // uk - Ім'я модуля
    const F_RU_TITLE        = "ru_title";       // ru - Имя модуля
    const F_EN_TITLE        = "en_title";       // en - Module name

    const  _DESCRIPTION     =   "_description"; // суффикс поля. Полное поле: язык + суффикс
    const F_UK_DESCRIPTION  = "uk_description"; // uk - Опис модуля
    const F_RU_DESCRIPTION  = "ru_description"; // ru - Описание модуля
    const F_EN_DESCRIPTION  = "en_description"; // en - Description of the module

    const F_ROUTE           = "route";          // Маршрут фреймворка сайта "Контроллер/Действие"
    const F_API             = "api";            // Программный доступ к модулю

    const F_CREATION_UID    = "creation_uid";   // Кто создал запись
    const F_CREATION_DATE   = "creation_date";  // Дата создания записи
    const F_MODIFIED_UID    = "modified_uid";   // Кто изменил запись
    const F_MODIFIED_DATE   = "modified_date";  // Дата изменения записи в базе

    /* =========================
       Группировка полей
       ========================= */

    /**
     * Список поддерживаемых языков
     */
    public const SUPPORTED_LANGS = ['uk', 'ru', 'en'];

    /*
     * Поля описаний по языкам
     */

    public const F_TITLE = [
        'uk' => self::F_UK_TITLE,
        'ru' => self::F_RU_TITLE,
        'en' => self::F_EN_TITLE,
    ];

    public const F_DESCRIPTION = [
        'uk' => self::F_UK_DESCRIPTION,
        'ru' => self::F_RU_DESCRIPTION,
        'en' => self::F_EN_DESCRIPTION,
    ];

}