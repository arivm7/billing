<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AppBaseController.php
 *  Path    : app/controllers/AppBaseController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\widgets\LangSelector\LangSelector;
use billing\core\App;
use billing\core\base\Controller;
use config\tables\User;



/**
 * Базовый контроллер для приложения
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AppBaseController extends Controller{



    /**
     * Имя файла, в который будет писать логи метод log()
     * Можно/нужно переопределять в доченрних классах.
     */
    const LOG_FILENAME = 'controler.log';



    // #[Override]
    function __construct(array $route) {
        parent::__construct($route);

    }


    
    /**
     * Записывает строку лога в файл, если в сессии присутствует информация о текущем пользователе.
     *   - $msg (string) — сообщение для лога.
     *   - $cr (bool) — добавлять в конец переводы строки (по умолчанию true).
     *   - $log_filename (string|null) — имя файла лога (если null, используется константа класса).
     *   - Формирует строку вида: "YYYY-MM-DD HH:MM:SS | user_id | message" и, если $cr === true, добавляет "\n".
     * 
     * @param string $msg
     * @param bool $eol_cr
     * @param string|null $log_filename
     * @param bool $log_url
     * @return bool
     */
    public static function log(string $msg = '', bool|int $eol_cr = true, string|null $log_filename = null, bool|int $log_url = false): bool {
        return 
            error_log(
                message: date('Y-m-d H:i:s') 
                    . " | " . sprintf('%-15s', $_SERVER['REMOTE_ADDR'] ?: 'UNKNOWN') 
                    . ' | ' . sprintf('%6s', App::get_user_id() ?: '-') 
                    . ' | ' . ($log_url ? get_full_request_url() . ($msg ? ' | ' : '') : '')
                //  . sprintf('%6d', App::get_user()[User::F_LOGIN]) . ' | ' 
                    . $msg
                    /**
                     * message_type: 3 -- Сообщение message добавляется в файл, путь к которому указали в параметре destination.
                     * Символ новой строки не добавляется автоматически в конец строки сообщения message.
                     */
                    . ($eol_cr ? "\n" : ""),
                message_type: 3,
                destination: DIR_LOG.'/'.($log_filename ?: static::class::LOG_FILENAME)
            );
    }


    public static function log_unauthorize() {
        self::log(msg: 'ERROR   | -unauth- | ' . get_full_request_url(), log_filename: App::get_config('auth_log_file'));
    }
    
    
}