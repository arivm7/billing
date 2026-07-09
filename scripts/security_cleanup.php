#!/usr/bin/env php
<?php
/**
 *  Project : my.ri.net.ua
 *  File    : security_cleanup.php
 *  Path    : scripts/security_cleanup.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026 19:10:05
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of security_cleanup.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

declare(strict_types=1);

use billing\core\SecurityAttackGuard;
use billing\core\base\Controller;
use app\models\AuthModel;
use billing\core\App;


require __DIR__ . '/../config/dirs.php';
require DIR_CONFIG . '/ini.php';
require DIR_CONFIG . '/colors.php';
require DIR_LIBS . '/common.php';
require DIR_LIBS . '/functions.php';
require __DIR__ . '/../vendor/autoload.php';

const APP_NAME = "RI-BILLING";
const LOG_ACTION = 'security_cleanup.log';


/**
 * Автозагручик Composer'а
 */
require __DIR__    . '/../vendor/autoload.php';

/**
 *  Инициализация Реестра App::$app
 */
new App;

/**
 * Имитируем авторизацию от пользователя billling
 * $UID = 11;  // billng
 */
$token = '$2y$10$wuMWlo240T7Hi1KmChL6ceWTJT5QQPpXkgxIxo1GM1QmWpDr12bWa';
$model = new AuthModel();
$model->login_by_token($token);



$ev_count = SecurityAttackGuard::cleanup_expired_attack_events();

echo Controller::make_message_str(
    ($ev_count === false
        ? 'Ошибка очистки событий атаки'
        : 'Удалено просроченных событий атак: ' . $ev_count),
    log_ip:  0,
    log_url: 0,
) . PHP_EOL;

$ip_count = SecurityAttackGuard::cleanup_expired_blocked_ip();

echo Controller::make_message_str(
    ($ip_count === false
        ? 'Ошибка очистки просроченных заблокированных адресов'
        : 'Удалено просроченных заблокированных адресов: ' . $ip_count),
    log_ip:  0,
    log_url: 0,
) . PHP_EOL;

