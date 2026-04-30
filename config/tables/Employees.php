<?php
/*
 *  Project : my.ri.net.ua
 *  File    : FirmEmployee.php
 *  Path    : config/tables/FirmEmployee.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 31 Dec 2025
 *  License : GPL v3
 */

namespace config\tables;

/**
 * Обёртка таблицы firm_employees
 *
 * Связь пользователей с предприятиями
 */
class Employees
{

    /* =========================
       URI
       ========================= */

    public const URI_LIST   = 'firms/employees';
    // public const URI_CREATE = '';
    // public const URI_DELETE = '';

    /* =========================
       POST
       ========================= */

    /**
     * Имя массива в POST-запросе
     */
    public const POST_REC = 'post_employee';

    /* =========================
       Таблица
       ========================= */

    public const TABLE = 'ts_firms_users';

    /* =========================
       Поля таблицы
       ========================= */

    public const F_USER_ID        = 'user_id';        // ID пользователя
    public const F_FIRM_ID        = 'firm_id';        // ID предприятия
    public const F_JOB_TITLE      = 'job_title';      // Должность
    public const F_CREATION_UID   = 'creation_uid';   // Кто создал запись
    public const F_CREATION_DATE  = 'creation_date';  // Дата создания записи

    /* =========================
       Группировка полей
       ========================= */

    /**
     * Числовые поля
     */
    public const NUM_TYPES = [
        self::F_USER_ID,
        self::F_FIRM_ID,
        self::F_CREATION_UID,
        self::F_CREATION_DATE,
    ];

    /* =========================
       Служебные наборы
       ========================= */

    /**
     * Поля, формирующие уникальную связь
     * (пользователь ↔ предприятие)
     */
    public const UNIQUE_PAIR = [
        self::F_USER_ID,
        self::F_FIRM_ID,
    ];



}
