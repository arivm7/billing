<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Role.php
 *  Path    : config/tables/Role.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Role.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Role {

    /*
     * Вычисляемые и автоприсваиваемые роли
     */
    const ABON_NONE = 0; // Не абонент. Вообще нет абонентских подключений
    const ABON_ON   = 3; // Абонент, що обслуговується, платник (is_payer), включаючи тих, хто на паузі.
    const ABON_OFF  = 4; // Вимкнений абонент (за заявкою або з іншої причини)



    const POST_REC          = 'role';

    const TABLE             = 'adm_role_list';

    const F_ID              = "id";
    const  _TITLE           =   "_title";       // суффикс поля. Полное поле: язык + суффикс
    const F_UK_TITLE        = "uk_title";
    const F_RU_TITLE        = "ru_title";
    const F_EN_TITLE        = "en_title";
    const  _DESCRIPTION     =   "_description"; // суффикс поля. Полное поле: язык + суффикс
    const F_UK_DESCRIPTION  = "uk_description";
    const F_RU_DESCRIPTION  = "ru_description";
    const F_EN_DESCRIPTION  = "en_description";

    const F_CREATION_UID    = "creation_uid";
    const F_CREATION_DATE   = "creation_date";
    const F_MODIFIED_UID    = "modified_uid";
    const F_MODIFIED_DATE   = "modified_date";

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