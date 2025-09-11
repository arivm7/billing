<?php



namespace config\tables;



class PppType {

    /**
     * Имя таблицы в базе данных
     */
    public const TABLE = 'ppp_types';

    /* =========================
       Поля таблицы
       ========================= */

    public const F_ID             = 'id';                 // ID типа ППП

    // Названия по языкам
    public const F_RU_TITLE       = 'ru_title';           // ru - Название типа ППП
    public const F_UK_TITLE       = 'uk_title';           // uk - Назва типу ППП
    public const F_EN_TITLE       = 'en_title';           // en - Title of PPP type

    // Описания по языкам
    public const F_RU_DESCR       = 'ru_description';     // ru - Описание ППП
    public const F_UK_DESCR       = 'uk_description';     // uk - Опис ППП
    public const F_EN_DESCR       = 'en_description';     // en - Description of PPP type

    // Служебные даты и пользователи
    public const F_CREATION_DATE  = 'creation_date';      // Дата создания
    public const F_CREATION_UID   = 'creation_uid';       // Кто создал
    public const F_MODIFIED_DATE  = 'modified_date';      // Дата изменения
    public const F_MODIFIED_UID   = 'modified_uid';       // Кто изменил

    /* =========================
       Группировка полей
       ========================= */

    /**
     * Список поддерживаемых языков
     */
    public const SUPPORTED_LANGS = ['uk', 'ru', 'en'];

    /**
     * Поля названий по языкам
     */
    public const F_TITLE = [
        'uk' => self::F_UK_TITLE,
        'ru' => self::F_RU_TITLE,
        'en' => self::F_EN_TITLE,
    ];

    /**
     * Поля описаний по языкам
     */
    public const F_DESCR = [
        'uk' => self::F_UK_DESCR,
        'ru' => self::F_RU_DESCR,
        'en' => self::F_EN_DESCR,
    ];



}
