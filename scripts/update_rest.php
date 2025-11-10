<?php
/**
 *  Project : my.ri.net.ua
 *  File    : update_rest.php
 *  Path    : scripts/update_rest.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 04 Nov 2025 07:01:44
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of update_rest.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use app\models\AbonModel;


/**
 * 
 * # в crontab
 * * * * * * CRON=1 /usr/bin/php /path/to/script.php
 * * * * * * /usr/bin/php /path/to/script.php --runner=cron
 * 
 * if (PHP_SAPI === 'cli') {
 *     // запуск из командной строки (cron/ssh/manual)
 * } else {
 *     // запуск из веб (браузер)
 * }
 * 
 * if (!empty($_SERVER['REQUEST_METHOD'])) {
 *     // веб (браузер)
 * } else {
 *     // CLI (cron/terminal)
 * }
 * 
 * 
*/



const APP_NAME = "RI-BILLING";

require __DIR__    . '/../config/dirs.php';
require DIR_CONFIG . '/ini.php';
require DIR_LIBS   . '/common.php';
require DIR_LIBS   . '/functions.php';



/**
 * Автозагручик Composer'а
 */
require __DIR__    . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;



$log = new Logger(APP_NAME);
$log->pushHandler(new SyslogHandler(APP_NAME, LOG_USER, Logger::DEBUG));

$log->info('REST update start');

$model = new AbonModel();

/**
 * Обновление остатков на ЛС всех абонентов (формируется в отдельной таблице `abon_rest`)
*/
if ($model->update_abon_rest()) {
    $msg = 'REST обновление выполнено успешно';
    $log->info($msg, [$model->errorInfo()]);
    echo "{$msg}\n";
    print_r($model->errorInfo());
} else {
    $msg = 'REST Ошибка обновления';
    $log->error($msg, [$model->errorInfo()]);
    echo "{$msg}\n";
    print_r($model->errorInfo());
    $errors = $model->errorInfo();
    foreach ($errors as $key => $err) {
        $msg = "[{$key}] {$err}";
        $log->error($msg);
        echo "{$msg}\n";
    }
}
