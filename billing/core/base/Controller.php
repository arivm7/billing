<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Controller.php
 *  Path    : billing/core/base/Controller.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core\base;

use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\View;

/**
 * Description of Controller.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
abstract class Controller
{



    /**
     * Текущий маршрут и параметры (Controller, Action, Params)
     * @var array
     */
    public $route = [];



    /**
     * Путь/Имя файла Вида
     * @var string
     */
    public $view;



    /**
     * Имя файла используемого шаблона
     * @var string
     */
    public $layout;



    /**
     * Пользовательские переменные используемые в файле Вида
     * @var array
     */
    public $variables = [];



    /**
     * Имя файла, в который будет писать логи метод log()
     * Можно/нужно переопределять в доченрних классах.
     */
    const LOG_FILENAME = 'controler.log';



    public function __construct(array $route)
    {
        $this->route = $route;
        $this->view = $route[F_ACTION];
        Lang::load(App::$app->get_config(Lang::F_CURR), $this->route);
    }



    public function getView() {
        $viewObj = new View(route: $this->route, layout: $this->layout, view: $this->view);
        $viewObj->render($this->variables);
    }



    public function setVariables(array $variables) {
        $this->variables = $variables;
    }



    /**
     * Формирует стандартную строку журнала.
     *
     * Формат:
     * YYYY-MM-DD HH:MM:SS | IP | USER_ID | URL | MESSAGE
     *
     * Поля IP и URL могут быть отключены параметрами.
     * Идентификатор пользователя выводится фиксированной ширины
     * для удобства чтения логов.
     *
     * @param string   $msg      Текст сообщения.
     * @param bool|int $log_url  Добавлять URL запроса.
     * @param bool|int $log_ip   Добавлять IP-адрес клиента.
     *
     * @return string Сформированная строка для записи в лог.
     */    
    public static function make_message_str(string $msg = '', bool|int $log_url = false, bool|int $log_ip = true): string {
        return  date('Y-m-d H:i:s') 
                . ($log_ip ? " | " . sprintf('%-15s', ($_SERVER['REMOTE_ADDR'] ?? '') ?: 'UNKNOWN IP') : "") 
                . ' | ' . sprintf('%'.App::get_config('port_max_digits').'s', App::get_user_id() ?: '-') 
                . ' | ' . ($log_url ? str_replace(["\r", "\n"], '', get_full_request_url()) . ($msg ? ' | ' : '') : '')
            //  . sprintf('%6d', App::get_user()[User::F_LOGIN]) . ' | ' 
                . $msg;
    }

    
    
    /**
     * Записывает строку лога в файл, если в сессии присутствует информация о текущем пользователе.
     *   - $msg (string) — сообщение для лога.
     *   - $cr (bool) — добавлять в конец переводы строки (по умолчанию true).
     *   - $log_filename (string|null) — имя файла лога (если null, используется константа класса).
     *   - Формирует строку вида: "YYYY-MM-DD HH:MM:SS | user_id | message" и, если $cr === true, добавляет "\n".
     * 
     * @param string $msg
     * @param bool|int $eol_cr
     * @param string|null $log_filename
     * @param bool|int $log_url -- добавить в лог url-строку 
     * @param bool|int $log_ip  -- добавить в лог ip вызывающей стороны 
     * @return bool
     */
    public static function log(string $msg = '', bool|int $eol_cr = true, string|null $log_filename = null, bool|int $log_url = false, bool|int $log_ip = true): bool {
        return 
            error_log(
                message: self::make_message_str(
                        msg:  $msg,
                        log_url:  $log_url,
                        log_ip:  $log_ip
                    )
                    /**
                     * message_type: 3 -- Сообщение message добавляется в файл, путь к которому указали в параметре destination.
                     * Символ новой строки не добавляется автоматически в конец строки сообщения message.
                     */
                    . ($eol_cr ? "\n" : ""),
                message_type: 3,
                destination: DIR_LOG.'/'.($log_filename ?: static::class::LOG_FILENAME)
            );
    }



}