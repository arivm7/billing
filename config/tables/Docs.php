<?php
/**
 *  Project : s1.ri.net.ua
 *  File    : Docs.php
 *  Path    : config/tables/Docs.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  @copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Docs.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Docs {

    const URI_LIST = '/docs';
    const URI_VIEW = '/docs/view';
    const URI_EDIT = '/docs/edit';
    const URI_DEL  = '/docs/delete';

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID = 'docs_id';

    /**
     * Имя массива в POST-запросе, в котором приходят данные формы
     */
    public const POST_REC = 'postDoc';

    /**
     * Имя таблицы
     */
    public const TABLE = 'documents';

    // --- Системные поля ---
    public const F_ID                   = 'id';                     // ID документа
    /** @var string имя поля ID автора публикации */
    public const F_AUTHOR_ID            = 'author_id';              // ID автора публикации
    /** @var string имя поля Дата создания новости (UNIX timestamp) */
    public const F_DATE_CREATION        = 'date_creation';          // Дата создания документа
    public const F_DATE_CREATION_STR    = 'date_creation_str';      // Дата создания документа
    /** @var string имя поля Дата публикации новости (UNIX timestamp) */
    public const F_DATE_PUBLICATION     = 'date_publication';       // Дата публикации документа
    public const F_DATE_PUBLICATION_STR = 'date_publication_str';   // Дата публикации документа
    /** @var string имя поля Дата окончания срока действия новости (UNIX timestamp) */
    public const F_DATE_EXPIRATION      = 'date_expiration';        // Дата окончания публикации
    public const F_DATE_EXPIRATION_STR  = 'date_expiration_str';    // Дата окончания публикации
    public const F_AUTO_VISIBLE         = 'auto_visible';           // Автоматическая публикация по дате
    public const F_IS_VISIBLE           = 'is_visible';             // Видимость для всех
    public const F_AUTO_DEL             = 'auto_del';               // Автоматическое удаление по истечении срока
    public const F_IS_DELETED           = 'is_deleted';             // Пометка удалённой записи


    // --- Локализованные заголовки ---
    /** @var string имя поля Заголовок документа (ru) */
    public const F_RU_TITLE             = 'ru_title';
    /** @var string имя поля Заголовок документа (uk) */
    public const F_UK_TITLE             = 'uk_title';
    /** @var string имя поля Document headline (en) */
    public const F_EN_TITLE             = 'en_title';

    // --- Локализованные описания ---
    /** @var string имя поля Описание документа (ru) */
    public const F_RU_DESCRIPTION       = 'ru_description';
    /** @var string имя поля Опис документа (uk) */
    public const F_UK_DESCRIPTION       = 'uk_description';
    /** @var string имя поля Document description (en) */
    public const F_EN_DESCRIPTION       = 'en_description';

    // --- Локализованные тексты ---
    /** @var string имя поля Текст документа (ru) */
    public const F_RU_TEXT              = 'ru_text';
    /** @var string имя поля Текст документа (uk) */
    public const F_UK_TEXT              = 'uk_text';
    /** @var string имя поля Document text (en) */
    public const F_EN_TEXT              = 'en_text';

    // ---- Что отображать при просмотре документа ----
    public const F_IN_VIEW_TITLE        = 'in_view_title';           // Отображать поле _title при просмотре документа
    public const F_IN_VIEW_DESCRIPTION  = 'in_view_description';     // Отображать поле _describtion при просмотре документа
    public const F_IN_VIEW_TEXT         = 'in_view_text';            // Отображать поле _text при просмотре документа

    // --- Служебные поля создания/изменения ---
    public const F_CREATION_UID         = 'creation_uid';       // UID создателя записи
    public const F_CREATION_DATE        = 'creation_date';      // Дата создания записи
    public const F_MODIFIED_UID         = 'modified_uid';       // UID изменившего запись
    public const F_MODIFIED_DATE        = 'modified_date';      // Дата изменения записи



    /* =========================
       Группировка полей
       ========================= */



    /**
     * Список поддерживаемых языков
     */
    public const SUPPORTED_LANGS = ['ru', 'uk', 'en'];

    /**
     * Поля заголовков по языкам
     */
    public const F_TITLES = [
        'ru' => self::F_RU_TITLE,
        'uk' => self::F_UK_TITLE,
        'en' => self::F_EN_TITLE,
    ];

    /**
     * Поля описаний по языкам
     */
    public const F_DESCRIPTIONS = [
        'ru' => self::F_RU_DESCRIPTION,
        'uk' => self::F_UK_DESCRIPTION,
        'en' => self::F_EN_DESCRIPTION,
    ];

    /**
     * Поля текстов по языкам
     */
    public const F_TEXTS = [
        'ru' => self::F_RU_TEXT,
        'uk' => self::F_UK_TEXT,
        'en' => self::F_EN_TEXT,
    ];


    /**
     * Поля формы создания/редактирования
     */
    public const POST_FIELDS = [

        self::F_ID                   => null,
        self::F_AUTHOR_ID            => 0,
        self::F_DATE_CREATION        => null,
        self::F_DATE_CREATION_STR    => null,
        self::F_DATE_PUBLICATION     => null,
        self::F_DATE_PUBLICATION_STR => null,
        self::F_DATE_EXPIRATION      => null,
        self::F_DATE_EXPIRATION_STR  => null,
        self::F_AUTO_VISIBLE         => 1,
        self::F_IS_VISIBLE           => 0,
        self::F_AUTO_DEL             => 0,
        self::F_IS_DELETED           => 0,

        self::F_RU_TITLE             => '',
        self::F_UK_TITLE             => '',
        self::F_EN_TITLE             => '',

        self::F_RU_DESCRIPTION       => '',
        self::F_UK_DESCRIPTION       => '',
        self::F_EN_DESCRIPTION       => '',

        self::F_RU_TEXT              => '',
        self::F_UK_TEXT              => '',
        self::F_EN_TEXT              => '',

        self::F_IN_VIEW_TITLE        => 1,
        self::F_IN_VIEW_DESCRIPTION  => 1,
        self::F_IN_VIEW_TEXT         => 1,

    ];

    /**
     * Поля формы содержащие шаблоны подстановки
     */
    public const TEMPLATE_FIELDS = [

        self::F_RU_TITLE,
        self::F_UK_TITLE,
        self::F_EN_TITLE,

        self::F_RU_DESCRIPTION,
        self::F_UK_DESCRIPTION,
        self::F_EN_DESCRIPTION,

        self::F_RU_TEXT,
        self::F_UK_TEXT,
        self::F_EN_TEXT,

    ];



}