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
use billing\core\SecurityAttackGuard;
use billing\core\Timers;
use billing\core\App;

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
// const CTR_POST = 'Posts';
const CTR_MAIN = 'Main';
// const CTR_PAGE = 'Page';

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
 * Автозагручик Composer'а
 */
require __DIR__ . '/../vendor/autoload.php';



Timers::setTimeStart($timeStart);
unset($timeStart);



/**
 * -----------------------------------------------------------------
 * Проверка запрещённого хоста
 */

require DIR_CONFIG . '/ip_acl.php';

$ALWAYS_ALLOWED_SUBNETS = is_array($ALWAYS_ALLOWED_SUBNETS ?? null) ? $ALWAYS_ALLOWED_SUBNETS : [];
$ONLY_THIS_ADDRESSES = is_array($ONLY_THIS_ADDRESSES ?? null) ? $ONLY_THIS_ADDRESSES : [];
$DENIED_FOREVER_ADDRESSES = is_array($DENIED_FOREVER_ADDRESSES ?? null) ? $DENIED_FOREVER_ADDRESSES : [];

$DENIED_ADDRESS = false;
$TRUSTED_SUBNET_ADDRESS = false;

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

/**
 * Проверяем, чтобю адрес был валидным
 */
if (!filter_var($remoteAddress, FILTER_VALIDATE_IP)) {
    SecurityAttackGuard::logDeniedRequest($remoteAddress ?: 'UNKNOWN', 'INVALID OR HIDDEN IP');
    echo  "<pre>"
            . "EN: IP invalid or hidden. Contact the ISP manager.\n"
            . "UK: IP помилковий або прихований. Зверніться до майстра ділянки.\n"
            . "RU: IP не верен или скрыт. Обратитесь к мастеру участка.\n"
        . '</pre>';
    die;
}



/**
 * Проверяем является ли адрес доверенным
 */
if (filter_var($remoteAddress, FILTER_VALIDATE_IP)) {
    foreach ($ALWAYS_ALLOWED_SUBNETS as $cidr) {
        if (ip_in_range(ip: $remoteAddress, cidr: $cidr)) {
            $TRUSTED_SUBNET_ADDRESS = true; // доверенный
            break;
        }
    }
}



/**
 * Проверяем только если адрес НЕ доверенный
 */
if  (!$TRUSTED_SUBNET_ADDRESS) {

    /**
     * Проверка запроса из разрешённой сети
     * Если адрес не в разрешённой сети -- блокируем.
     */

    $ALLOWED_ADDRESS = false;

    foreach ($ONLY_THIS_ADDRESSES as $cidr) {
        if (ip_in_range(ip: $remoteAddress, cidr: $cidr)) {
            $ALLOWED_ADDRESS = true;
            break;
        }
    }
    if (!$ALLOWED_ADDRESS) {
        echo  "<pre>"
                . "$remoteAddress\n"
                . "EN: Request not from allowed networks. Contact the ISP manager.\n"
                . "UK: Запит не з дозволених мереж. Зверніться до майстра ділянки.\n"
                . "RU: Запрос не из разрешённых сетей. Обратитесь к мастеру участка.\n"
            . '</pre>';
        die;
    }



    /**
     * Проверяем постоянные запреты
     */

    foreach ($DENIED_FOREVER_ADDRESSES as $cidr) {
        if (ip_in_range(ip: $remoteAddress, cidr: $cidr)) {
            $DENIED_ADDRESS = true;
            break;
        }
    }
    if ($DENIED_ADDRESS) {
        SecurityAttackGuard::logDeniedRequest($remoteAddress, 'BLOCKED BY STATIC DENY LIST');
        echo  "<pre>"
                . "EN: Request from a restricted network. Contact the ISP manager.\n"
                . "UK: Запит із заборонених мереж. Зверніться до майстра ділянки.\n"
                . "RU: Запрос из запрещённой сети. Обратитесь к мастеру участка.\n"
            . '</pre>';
        die;
    }

}
    


/**
 * Защита от сканеров
 */
if (!$TRUSTED_SUBNET_ADDRESS) {
    SecurityAttackGuard::enforceRequestAccess($remoteAddress);
}



/**
 * Очищаем по завершению статической и динамической проверки.
 */
unset(
    $DENIED_FOREVER_ADDRESSES,
    $ONLY_THIS_ADDRESSES,
    $ALWAYS_ALLOWED_SUBNETS,
    $DENIED_ADDRESS,
    $ALLOWED_ADDRESS,
    $TRUSTED_SUBNET_ADDRESS,
    $remoteAddress
);



/**
 *  Инициализация Реестра App::$app
 */
new App;



// Свои правила

Router::add('^dogovir/?$', [F_CONTROLLER => 'docs', F_ACTION => 'view', F_ALIAS => 1]);
Router::add('^rules/?$',   [F_CONTROLLER => 'docs', F_ACTION => 'view', F_ALIAS => 2]);
Router::add('^flood/?$',   [F_CONTROLLER => 'docs', F_ACTION => 'view', F_ALIAS => 3]);

Router::add('^conciliation/(?P<' . F_ALIAS . '>[0-9]+)$', [
    F_CONTROLLER => 'conciliation',
    F_ACTION     => ACT_INDEX,
]);

Router::add('^pay/(?P<' . F_ALIAS . '>[0-9]+)$', [
    F_CONTROLLER => 'pay',
    F_ACTION     => ACT_INDEX,
]);

Router::add('^invoice/(?P<' . F_ALIAS . '>[0-9]+)$', [
    F_CONTROLLER => 'invoice',
    F_ACTION     => ACT_INDEX,
]);

// Router::add('^abon/?\?id=[0-9]+$', [F_CONTROLLER => 'abon', F_ACTION => 'form']);
// Router::add('^page/(?P<'.F_ACTION.'>[0-9a-z-]+)/(?P<'.F_ALIAS.'>[0-9a-z-]+)$', [F_CONTROLLER => CTR_PAGE]);
// Router::add('^page/(?P<'.F_ALIAS.'>[0-9a-z-]+)$', [F_CONTROLLER => CTR_PAGE, F_ACTION => ACT_VIEW]);


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
