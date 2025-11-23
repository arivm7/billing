<?php
/*
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/My/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use config\tables\Abon;
use config\tables\Firm;
use config\tables\Module;
use config\tables\User;
use config\tables\PA;

require_once DIR_LIBS . '/form_functions.php';
require_once DIR_LIBS . '/billing_functions.php';
require_once DIR_LIBS . '/inc_functions.php';

/** @var array $user */
/** @var int $for_abon_id */

/**
 * Главный вид карточки абонента
 */
require DIR_INC . '/user_main.php';

?>
