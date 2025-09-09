<?php



namespace config\tables;



class News {

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID = 'news_id';

    /**
     * Имя массива в POST-запросе, в котором приходят данные формы
     */
    public const POST_REC = 'post';

    /**
     * Имя таблицы
     */
    public const TABLE = 'news';

    // --- Системные поля ---
    public const F_ID                   = 'id';                     // ID новости
    /** @var string имя поля ID автора публикации */
    public const F_AUTHOR_ID            = 'author_id';              // ID автора публикации
    /** @var string имя поля Дата создания новости (UNIX timestamp) */
    public const F_DATE_CREATION        = 'date_creation';          // Дата создания новости
    public const F_DATE_CREATION_STR    = 'date_creation_str';      // Дата создания новости
    /** @var string имя поля Дата публикации новости (UNIX timestamp) */
    public const F_DATE_PUBLICATION     = 'date_publication';       // Дата публикации новости
    public const F_DATE_PUBLICATION_STR = 'date_publication_str';   // Дата публикации новости
    /** @var string имя поля Дата окончания срока действия новости (UNIX timestamp) */
    public const F_DATE_EXPIRATION      = 'date_expiration';        // Дата окончания публикации
    public const F_DATE_EXPIRATION_STR  = 'date_expiration_str';    // Дата окончания публикации
    public const F_AUTO_VISIBLE         = 'auto_visible';           // Автоматическая публикация по дате
    public const F_IS_VISIBLE           = 'is_visible';             // Видимость для всех
    public const F_AUTO_DEL             = 'auto_del';               // Автоматическое удаление по истечении срока
    public const F_IS_DELETED           = 'is_deleted';             // Пометка удалённой записи


    // --- Локализованные заголовки ---
    /** @var string имя поля Заголовок новости (ru) */
    public const F_RU_TITLE             = 'ru_title';
    /** @var string имя поля Заголовок новини (uk) */
    public const F_UK_TITLE             = 'uk_title';
    /** @var string имя поля News headline (en) */
    public const F_EN_TITLE             = 'en_title';

    // --- Локализованные описания ---
    /** @var string имя поля Описание новости (ru) */
    public const F_RU_DESCRIPTION       = 'ru_description';
    /** @var string имя поля Опис новини (uk) */
    public const F_UK_DESCRIPTION       = 'uk_description';
    /** @var string имя поля News description (en) */
    public const F_EN_DESCRIPTION       = 'en_description';

    // --- Локализованные тексты ---
    /** @var string имя поля Текст новости (ru) */
    public const F_RU_TEXT              = 'ru_text';
    /** @var string имя поля Текст новини (uk) */
    public const F_UK_TEXT              = 'uk_text';
    /** @var string имя поля News text (en) */
    public const F_EN_TEXT              = 'en_text';

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

    ];



}
