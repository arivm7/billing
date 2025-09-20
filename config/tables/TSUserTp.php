<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : TSUserTp.php
 *  Path    : config/tables/TSUserTp.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of TSUserTp.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class TSUserTp {

    const POST_REC          = 'tsUserTp';

    const TABLE             = 'ts_user_tp';     // 'Таблица связи Пользователя и ТП';

    const F_USER_ID         = 'user_id';        // 'ID Пользователя',
    const F_TP_ID           = 'tp_id';          // 'ID Техплощадки',
    const F_PERCENT_OWNER   = 'percent_owner';  // 'Процент долевого участия (владения и получения дивидендов)'

}