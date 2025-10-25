<?php
/*
 *  Project : my.ri.net.ua
 *  File    : ini.php
 *  Path    : config/ini.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Начальные настройки php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

date_default_timezone_set('Europe/Kiev');
ini_set("session.use_trans_sid", true);
//ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

/**
 * используются только в функции get_http_script()
 */
const REQUEST_SCHEME_DEFAULT = 'https';
const SERVER_NAME_DEFAULT    = 'my.ri.net.ua';
const SERVER_PORT_DEFAULT    = '443';

