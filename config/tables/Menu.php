<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Menu.php
 *  Path    : config/tables/Menu.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Menu.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Menu {

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID     = 'menu_id';

    /**
     * Имя массива, в котором в POST-запросе хранятся данные формы
     */
    public const POST_REC     = 'menuRec';

    /**
     * Имя таблицы в базе данных
     */
    public const TABLE            = 'adm_menu';

    public const F_ID             = 'id';               // ID элемента меню
    public const F_ORDER          = 'order_num';        // Порядковый номер элемента меню при отображении
    public const F_MODULE_ID      = 'module_id';        // ID модуля которому относится пункт меню.
                                                        // Для подключения пункта меню в зависимости от прав доступа
    public const F_ANON_VISIBLE   = 'anon_visible';     // Показывать для неавторизованных пользователей
    public const F_VISIBLE        = 'visible';          // Если 0. то скрыт (для технологических целей)

    public const _TITLE           =   '_title';         // фрагмент имени поля для конструкции <lang>_TITLE
    public const F_UK_TITLE       = 'uk_title';         // uk - Назва елемента меню
    public const F_RU_TITLE       = 'ru_title';         // ru - Название элемента меню
    public const F_EN_TITLE       = 'en_title';         // en - The name of the menu element

    public const _DESCR           =   '_description';   // фрагмент имени поля для конструкции <lang>_DESCR
    public const F_UK_DESCR       = 'uk_description';   // uk - Опис пункту меню
    public const F_RU_DESCR       = 'ru_description';   // ru - Описание пункта меню
    public const F_EN_DESCR       = 'en_description';   // en - Description of the menu item

    public const F_PARENT_ID      = 'parent_id';        // ID родительского элемента меню
    public const F_CHILDS         = 'childs';

    public const F_URL            = 'url';              // URL для href=<URL>

    public const F_IS_WIDGET      = 'is_widget';        // Если установлено, то URL содержит имя класса виджета

    public const F_CREATION_UID   = 'creation_uid';     // Кто создал запись
    public const F_CREATION_DATE  = 'creation_date';    // Дата создания записи

    public const F_MODIFIED_UID   = 'modified_uid';     // Кто изменил запись
    public const F_MODIFIED_DATE  = 'modified_date';    // Дата изменения записи

    public const F_DELETE_ID      = 'delete_id';        // Имя переменной для пост-запроса на удаление записи
    public const F_EDIT_ID        = 'edit_id';          // Имя переменной для пост-запроса для редактирования записи

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

    public const F_DESCR = [
        'uk' => self::F_UK_DESCR,
        'ru' => self::F_RU_DESCR,
        'en' => self::F_EN_DESCR,
    ];



}