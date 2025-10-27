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
     * Можно/нужно переотпределять в доченрних классах.
     */
    const LOG_FILENAME = 'controler.log';



    // #[Override]
    function __construct(array $route) {
        parent::__construct($route);

    }



    public function log(string $msg, bool $cr = true, string|null $log_filename = null) {
        if  (
                isset($_SESSION[User::SESSION_USER_REC]) &&
                isset($_SESSION[User::SESSION_USER_REC][User::F_ID]) &&
                isset($_SESSION[User::SESSION_USER_REC][User::F_LOGIN])
            )
        {
            error_log(
                message: date('Y-m-d H:i:s') . " | "
                    . $_SESSION[User::SESSION_USER_REC][User::F_ID] . ' | ' . $_SESSION[User::SESSION_USER_REC][User::F_LOGIN]. " | "
                    . $msg
                    . ($cr ? "\n" : ""),
                /**
                 * "3" -- Сообщение message добавляется в файл, путь к которому указали в параметре destination.
                 * Символ новой строки не добавляется автоматически в конец строки сообщения message.
                 */
                message_type: 3,
                destination: DIR_LOG.'/'.($log_filename ?: static::class::LOG_FILENAME)
            );
        }
    }


}