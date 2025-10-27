<?php
/*
 *  Project : my.ri.net.ua
 *  File    : index.php
 *  Path    : public/index.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 18:43:59
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



/**
 * Время начала работы скрипта. Для вычисления времени работы.
 */
$timeStart = \microtime(true);

use billing\core\Router;
use billing\core\Timers;

require '../config/dirs.php';
require DIR_CONFIG . '/ini.php';
require DIR_CONFIG . '/colors.php';
require DIR_LIBS   . '/common.php';
require DIR_LIBS   . '/functions.php';


// # префикс и имя папки для админки
const F_PREFIX  = 'prefix';
const PREFIX_VALUE  = 'admin';

const F_CONTROLLER  = 'controller';
const CONTROLLER_SUFFIX = 'Controller';

// # Контроллеры
const CTR_POST = 'Posts';
const CTR_MAIN = 'Main';
const CTR_PAGE = 'Page';

define('CONTROLLERS_NAMESPACE', 'app\controllers\\');

define('F_ACTION', 'action');
define('ACTION_SUFFIX', 'Action');

// # Действия
define('ACT_ADD', 'add');
define('ACT_VIEW', 'view');
define('ACT_INDEX', 'index');

define('F_ALIAS', 'alias');

define('LAYOUT_DEFAULT',        'default');
define('LAYOUT_SUFFIX',         'Layout');

define('VIEW_SUFFIX',           'View');
//define('VIEW_NO_AUTH',          '../noAuth');

define('MODEL_SUFFIX',          'Model');



/**
 * -----------------------------------------------------------------
 * Проверка разрешённого хоста
 */

$ALLOWED_ADDRES = false;
$ALLOWED_ADDRESSES = [
    "0.0.0.0/0",
    '176.36.12.167',
    '176.105.102.234',
    //'176.105.102.234/29',
    //'213.111.124.217/29',
    '95.158.32.243',
];

if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
    foreach ($ALLOWED_ADDRESSES as $cidr) {
        if (ip_in_range(ip: $_SERVER['REMOTE_ADDR'], cidr: $cidr)) {
            $ALLOWED_ADDRES = 1;
            break;
        }
    }
    if (!$ALLOWED_ADDRES) {
        echo $_SERVER['REMOTE_ADDR'] . ' - Не разрешённый IP';
        die;
    }
} else {
    echo $_SERVER['REMOTE_ADDR'] . 'IP скрыт?';
    die;
}

/*
 * -----------------------------------------------------------------
 */



/**
 * Автозагручик Composer'а
 */
require __DIR__ . '/../vendor/autoload.php';

Timers::setTimeStart($timeStart);
unset($timeStart);

/**
 *  Инициализация Реестра App::$app
 */
new billing\core\App;


// Свои правила

Router::add('^dogovir/?$', [F_CONTROLLER => 'docs', F_ACTION => 'view', F_ALIAS => 1]);
Router::add('^rules/?$',   [F_CONTROLLER => 'docs', F_ACTION => 'view', F_ALIAS => 2]);
Router::add('^flood/?$',   [F_CONTROLLER => 'docs', F_ACTION => 'view', F_ALIAS => 3]);

Router::add('^pay/(?P<' . F_ALIAS . '>[0-9]+)$', [
    F_CONTROLLER => 'pay',
    F_ACTION     => ACT_INDEX,
]);

Router::add('^abon/?\?id=[0-9]+$', [F_CONTROLLER => 'abon', F_ACTION => 'form']);
Router::add('^page/(?P<'.F_ACTION.'>[0-9a-z-]+)/(?P<'.F_ALIAS.'>[0-9a-z-]+)$', [F_CONTROLLER => CTR_PAGE]);
Router::add('^page/(?P<'.F_ALIAS.'>[0-9a-z-]+)$', [F_CONTROLLER => CTR_PAGE, F_ACTION => ACT_VIEW]);


/**
 * Дефолтные правила
 * Админ модуль
 */
Router::add('^admin$', [F_CONTROLLER => 'admin', F_ACTION => ACT_INDEX, F_PREFIX => PREFIX_VALUE]);
Router::add('^admin/?(?P<'.F_CONTROLLER.'>[0-9a-z-]+)/?(?P<'.F_ACTION.'>[0-9a-z-]+)?$', [F_PREFIX => PREFIX_VALUE]);
Router::add('^admin/?(?P<'.F_CONTROLLER.'>[0-9a-z-]+)/?(?P<'.F_ACTION.'>[0-9a-z-]+)/(?P<'.F_ALIAS.'>[0-9a-z-]+)?$', [F_PREFIX => PREFIX_VALUE]);

/**
 * Дефолтные правила
 * Пользовательские
 */
Router::add('^(?P<'.F_CONTROLLER.'>[0-9a-z-]+)/(?P<'.F_ACTION.'>[0-9a-z-]+)/(?P<'.F_ALIAS.'>[0-9a-z-]+)$');
Router::add('^$', [F_CONTROLLER => CTR_MAIN, F_ACTION => ACT_INDEX]);
Router::add('^(?P<'.F_CONTROLLER.'>[0-9a-z-]+)/?(?P<'.F_ACTION.'>[0-9a-z-]+)?$');

$QUERY = rtrim($_SERVER['QUERY_STRING'], '/');

Router::dispatch($QUERY);