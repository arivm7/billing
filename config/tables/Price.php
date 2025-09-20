<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Price.php
 *  Path    : config/tables/Price.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Price.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Price {

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID = 'price_id';

    /**
     * Имя массива, в котором в POST-запросе хранятся данные формы
     */
    public const POST_REC = 'priceRec';

    /**
     * Имя таблицы в базе данных
     */
    public const TABLE = 'prices';

    // Поля таблицы
    public const F_ID             = 'id';              // ID прайса
    public const F_ACTIVE         = 'active';          // Прайс активен
    public const F_TITLE          = 'title';           // Название прайса
    public const F_PAY_PER_DAY    = 'pay_per_day';     // Оплата за день
    public const F_PAY_PER_MONTH  = 'pay_per_month';   // Оплата за месяц
    public const F_DESCRIPTION    = 'description';     // Описание пакета
    public const F_CREATION_DATE  = 'creation_date';   // Дата создания (int)
    public const F_CREATION_UID   = 'creation_uid';    // Кто добавил
    public const F_MODIFIED_DATE  = 'modified_date';   // Когда изменён
    public const F_MODIFIED_UID   = 'modified_uid';    // Кто изменил



}