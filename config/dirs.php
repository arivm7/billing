<?php
/*
 *  Project : my.ri.net.ua
 *  File    : dirs.php
 *  Path    : config/dirs.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Папки структуры проекта.
 * Должны соответствовать физическому размещению на диске сервера
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

define('DIR_ROOT',              '/var/www/s1.ri.net.ua');
define('DIR_WWW',               DIR_ROOT  . '/public');
define('DIR_APP',               DIR_ROOT  . '/app');
define('DIR_CONTROLLERS',       DIR_APP   . '/controllers');
define('DIR_LAYOUTS',           DIR_APP   . '/layouts');
define('DIR_VIEWS',             DIR_APP   . '/views');
define('DIR_LANG',              DIR_APP   . '/languages');
define('DIR_LANG_SUB_INC',      'inc');
define('DIR_WIDGETS',           DIR_APP   . '/widgets');
define('DIR_CONFIG',            DIR_ROOT  . '/config');
define('DIR_CORE',              DIR_ROOT  . '/billing/core');
define('DIR_VENDOR',            DIR_ROOT  . '/vendor');
define('DIR_LIBS',              DIR_ROOT  . '/billing/libs');
define('DIR_INC',               DIR_VIEWS . '/inc');
define('DIR_TEMP',              DIR_ROOT  . '/tmp');
define('DIR_LOG',               DIR_TEMP  . '/log');
define('DIR_CACHE',             DIR_TEMP  . '/cache');
