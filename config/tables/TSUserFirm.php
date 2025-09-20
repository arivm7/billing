<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : TSUserFirm.php
 *  Path    : config/tables/TSUserFirm.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of TSUserFirm.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class TSUserFirm {

    const POST_REC   = 'tsUF';

    const TABLE      = 'ts_firms_users'; // Таблица связей Предприятий и Пользователей

    const F_USER_ID  = 'user_id';   // ID пользователя
    const F_FIRM_ID  = 'firm_id';   // ID предприятия

}