<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Lang.php
 *  Path    : billing/core/base/Lang.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core\base;

use billing\core\App;
use billing\core\ErrorHandler;
use Exception;

/**
 * Description of Lang.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Lang {

    /*
     * Коды языков
     */
    public const C_RU  = 'ru';
    public const C_UK  = 'uk';
    public const C_EN  = 'en';

    public const F_GET  = 'lang';
    public const F_CODE = 'code';
    public const F_ORDER = 'order';
    public const F_LIST = 'lang_list';
    public const F_CURR = 'lang_curr';
    public const F_COOK_NAME = 'lang';
    public const F_COOK_TIME = 'lang_timeout';
    public const LOG_FILE = 'lang.log';


    public static $lang_data = [];
    public static $lang_layout = [];
    public static $lang_controller = [];
    public static $lang_view = [];



    /**
     * Загрузка файлов словарей в зависимости от контроллера/действия
     * @param array $lang_curr
     * @param array $route
     * @return void
     * @throws Exception
     */
    public static function load(array $lang_curr, array $route): void {
        if (empty(self::$lang_data)) {
            $lang_layout_file = DIR_LANG . "/{$lang_curr[self::F_CODE]}.php";
            $lang_controller_file = DIR_LANG . "/{$lang_curr[self::F_CODE]}".(empty($route[F_PREFIX]) ? "" : "/{$route[F_PREFIX]}")."/{$route[F_CONTROLLER]}/{$lang_curr[self::F_CODE]}.php";
            $lang_view_file   = DIR_LANG . "/{$lang_curr[self::F_CODE]}".(empty($route[F_PREFIX]) ? "" : "/{$route[F_PREFIX]}")."/{$route[F_CONTROLLER]}/{$route[F_ACTION]}.php";


            
            /**
             * Загрузка языкового файла макета
             */
            if (file_exists($lang_layout_file)) {
                self::$lang_layout = require $lang_layout_file;
            } else {
                switch (ErrorHandler::DEBUG && App::$app->get_config('lang_strong_file_existence')) {
                    case 0:
                        break;
                    case 1:
                        error_log(
                            message: "Route: [" . implode("/", $route) . "] :: Lang: [". self::code() . "] :: Lang File not found: [{$lang_layout_file}]\n",
                            message_type: 3,
                            destination: DIR_LOG . '/' . self::LOG_FILE,
                        );
                        break;
                    case 2:
                    default:
                        throw new Exception("Не найден языковой файл Макета | Layout Language file not found: '{$lang_layout_file}'");
                }
            }



            /**
             * Загрузка языкового файла контроллера
             */
            if (file_exists($lang_controller_file)) {
                self::$lang_controller = require $lang_controller_file;
            } else {
                switch (ErrorHandler::DEBUG && App::$app->get_config('lang_strong_file_existence')) {
                    case 0:
                        break;
                    case 1:
                        error_log(
                            message: "Route: [" . implode("/", $route) . "] :: Lang: [". self::code() . "] :: Lang File not found: [{$lang_controller_file}]\n",
                            message_type: 3,
                            destination: DIR_LOG . '/' . self::LOG_FILE,
                        );
                        break;
                    case 2:
                    default:
                        throw new Exception("Не найден языковой файл Контроллера | Controller Language file not found: '{$lang_controller_file}'");
                }
            }



            /**
             * Загрузка языкового файла вида
             */
            if (file_exists($lang_view_file)) {
                self::$lang_view = require $lang_view_file;
            } else {
                switch (ErrorHandler::DEBUG && App::$app->get_config('lang_strong_file_existence')) {
                    case 0:
                        break;
                    case 1:
                        error_log(
                            message: "Route: [" . implode("/", $route) . "] :: Lang: [". self::code() . "] :: Lang File not found: [{$lang_view_file}]\n",
                            message_type: 3,
                            destination: DIR_LOG . '/' . self::LOG_FILE,
                        );
                        break;
                    case 2:
                    default:
                        throw new Exception("Не найден языковой файл Вида | Language file for View not found: '{$lang_view_file}'");
                }
            }
            self::$lang_data = array_merge(self::$lang_layout, self::$lang_view);
        }
    }



    /**
     * Кэш-флагb для отметки уже загруженных включаемых фрагментов
     * @var array
     */
    private static array $LOADED_INCLUDES = [];



    /**
     * Подгружает словарь для inc-файла, который подгружается из вида, но не является самим видом и имеет отдельный словарь.
     * @param string $inc -- требуемый языковой файл, обычно имя includ-файла
     * @return void
     * @throws Exception
     */
    public static function load_inc(string $inc): void {

        // берём только имя файла без расширения
        $inc = pathinfo($inc, PATHINFO_FILENAME);

        if (empty($inc) || isset(self::$LOADED_INCLUDES[$inc])) {
            return;
        }

        $lang_inc_file = DIR_LANG . "/".self::code()."/". DIR_LANG_SUB_INC."/{$inc}.php";
        if (file_exists($lang_inc_file)) {
            $lang_inc = require $lang_inc_file;
            self::$lang_data = array_merge(self::$lang_data, $lang_inc);
            self::$LOADED_INCLUDES[$inc] = true;
        } else {
            switch (ErrorHandler::DEBUG && App::$app->get_config('lang_strong_file_existence')) {
                case 0:
                    break;
                case 1:
                    error_log(
                        message: "Route: [" . array_key_first($_GET) . "] :: Lang: [". self::code() . "] :: Lang File not found: [{$lang_inc_file}]\n",
                        message_type: 3,
                        destination: DIR_LOG . '/' . self::LOG_FILE,
                    );
                    break;
                case 2:
                default:
                    throw new Exception("Не найден языковой файл Формы-Включения | Language file for Include not found: '{$lang_inc_file}'");
            }
        }
    }



    /**
     * Возвращает значение строки перевода из массива по ключу
     * Текущая реализация через sprintf()
     * @param string $key -- ключ для поиска и возврата значения из словаря
     * @param mixed $param -- вставка внутренних значений в строку
     * @param string|null $default -- значение "по умолчанию" если в словаре нет записи
     * @return string
     */
    public static function get(string $key, mixed $param = null, string|null $default = null):string {
        if (!isset(self::$lang_data[$key])) {
            switch (ErrorHandler::DEBUG && App::$app->get_config('lang_strong_file_existence')) {
                case 0:
                    break;
                case 1:
                    error_log(
                        message: "Route: [" . array_key_first($_GET) . "] :: Lang: [". self::code() . "] :: Unknown Lang Key: [{$key}]\n",
                        message_type: 3,
                        destination: DIR_LOG . '/' . self::LOG_FILE,
                    );
                    break;
                case 2:
                default:
                 // throw new Exception("Не найден языковой ключ | Language key not found: '{$key}'");
                    break;
            }
        }
        $str =  (isset(self::$lang_data[$key]) ? self::$lang_data[$key] : ($default ? $default : $key));
        if ($param) {
            return sprintf($str, $param);
        }
        return  h($str);
    }



    public static function code(): string {
        return App::$app->get_config('lang_curr')[self::F_CODE];
    }




}