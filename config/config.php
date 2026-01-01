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
     * Автоматически заполняется виджетом ThemeSelector
     */
    'theme_curr' => [],

    /**
     * Время в секундах, на которое выбирается тема
     * для сохранения в куках сайта
     */
    'theme_timeout' => 60 * 60 * 24 * 30, // 30 дней



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
    'lang_timeout' => 60 * 60 * 24 * 7, // 7 дней

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
     * Количество дней паузы, при превышении котрого создаётся новый прайсовый фрагмент.
     * Если количество дней не превышает этого значения, то просто открывается имеющийся 
     * прайсовый фрагмент (PA::F_DATE_END устанавливается в NULL)
     */
    'pa_unpaused_days' => 3, // правильное значение -- 2 !!!

    /**
     * Количество дней паузы, при превышении котрого прайсовый фрагмент не активируется вновь при оплате.
     * Если количество дней превышает это значение, то прайсовый фрагмент остаётся закрытым.
     */
    'pa_no_reactivate_days' => 30,


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


    /**
     * Высота, в количествах строк, редактора коментариев <textarea>
     */
    'textarea_rows_min' => 2,
    'textarea_rows_max' => 10,
    'textarea_approximate_chars_per_line' => 60,
    

    /**
     * Список абонентов.
     * Количество строк в списке
     */
    'abon_per_page' => 15,


    // /**
    //  * Отображаемые ПФ в карточке абонента
    //  */
    // 'pa_show_filter' => [
    //     'active' => 1,
    //     'paused' => 1,
    //     'closed' => 0,
    // ],

    /**
     * Максимальная длина номера договора
     */
    'port_max_digits' => 6,
    

    /**
     * Параметры уведомлений SMS, email и прочие
     */

    'sms_cost1'     => 1.50, //Стоимость 1 СМС, грн
    'sms_chars1sms' => 69,  //Количество символов в 1 СМС.
    'sms_sender'    => '~/bin/sms_sender.sh',
    'sms_command'   => 'echo "{NUM}/{COUNT}. {ABON_ID} | {ADDRESS} | {NAME_SHORT}"\n'
                        . '{SENDER} {PHONE} "{TEXT}"\n',

    /**
     * Счета-фактуры и Акты
     */
    'inv_per_page' => 12,
    // Максимальная длина номера Счёта-фактуры
    'inv_max_length_number' => 24,


    /**
     * 
     */
    'bank_payment_min'        => 50, // сумма минимального платежа
    'bank_date_interval'      => 10*24*60*60, // 10 дней. Для листания в web-форме
    'bank_date_interval_auto' => 2*24*60*60, // 2 дня.  Для скриптов автоматического внесения платежей.
    'bank_http_user_agent'    => "Mozilla/5.0 BASH (Linux x86_64)", // "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.214 Safari/537.36";
    'bank_get_iteration_max'  => 10, // Максимальное количество итераций для выборки транзакций
    'bank_limit_per_page'     => 25,
    'bank_comission_text'     => [
            "за вычетом комиссии банка в размере 3.00грн",
            "утрим. комісія банку 3.00грн"
        ],
    'bank_comission_value'    => 3.00, // Значение комиссии банка в грн
    'bank_liqpay_ident_text'  => "LIQPAY",



];

return $config;