<?php
/*
 *  Project : my.ri.net.ua
 *  File    : config.php
 *  Path    : config/config.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 19:35:32
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Конфиг сайта
 * Параметры переменных, используемх сайтом
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


$config = [

    /**
     * Классы, экземплвры которых создаются при запуске программы
     */
    'autoload' => [
        'error_handler' => 'billing\core\ErrorHandler',
        'cache'         => 'billing\core\Cache'
    ],



    /**
     * Список тем
     */
    'theme_list' => [
            'light' => [ 'title' => 'Светлая', 'code' => 'data-bs-theme="light"', 'order' => 0 ],
            'dark'  => [ 'title' => 'Тёмная',  'code' => 'data-bs-theme="dark"',  'order' => 1 ],
    ],

    /**
     * Текущая тема.
     * Автоматически заполняется
     */
    'theme_curr' => [],

    /**
     * Время в секундах, на которое выбирается тема
     * для сохранения в куках сайта
     */
    'theme_timeout' => 60*60*24*30, // 30 дней



    /**
     * Список поддерживаемых языков
     * Минимальное поле order -- это язык по умолчанию
     *
     */
    'lang_list' => [
            'uk' => [ 'title' => 'Українська',  'order' => 0 ],
            'ru' => [ 'title' => 'Русский',     'order' => 1 ],
            'en' => [ 'title' => 'English',     'order' => 3 ],
    ],

    /**
     * запись текущего языка, выбранного из массива 'lang_list'
     * Значение перезаписывается виджетом LangSelector
     * Автоматически заполняется строкой из массива 'lang_list'
     */
    'lang_curr' => [],

    /**
     * Время в секундах, на которое выбирается язык
     * для сохранения в куках сайта
     */
    'lang_timeout' => 60*60*24*7, // 7 дней

    /**
     * Строго проверять наличие языкового файла
     * для billing\core\base\Lang.php
     * 0 -- игнорировать
     * 1 -- писать в лог-файл имя отсутствующего файла
     * 2 -- бросать исключение
     */
    'lang_strong_file_existence' => 1,

    /**
     * Максимальный размер отправляемых файлов
     */
    'files_upload_max_filesize' => return_bytes(ini_get('upload_max_filesize')),


    /**
     * Количество последних уведомлений для отображения в карточке абонента
     */
    'notify_list_limit' => 10,

    /**
     * Количество отображаемых платежей на странице
     */
    'payments_per_page' => 10,


    /**
     * Статусы абонента
     * Через сколько дней паузу считать "долгой"
     */
    'LONG_PAUSED_DAYS' => 180,


    /**
     * Параметры логина, если логин отличается от номера договора
     */
    'login_length_min' => 2,
    'login_length_max' => 25,
    /**
     * ВАЖНО: 
     * в фрагменте {1,24} долны быть значения на 1 меньше чем в предыдущих полях 'login_length_min' и 'login_length_max'
     */
    'login_content' => '^[A-Za-z][A-Za-z0-9._-]{1,24}$',


    /**
     * Параметры пароля
     */
    'pass_length_min' => 3,
    'pass_length_max' => 35,


];

return $config;