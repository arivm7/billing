<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Notify.php
 *  Path    : config/tables/Notify.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

use app\models\AbonModel;
use app\models\AppBaseModel;
use billing\core\base\Lang;

/**
 * Description of Notify.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Notify {

    const URI_INFO = '/notice/info';    /*  /id -- Страница с информацией для отправки абоненту */
    const URI_SMS_LIST = '/notice/sms'; /*  Страница генерации списка СМС-уведомлений */
    const URI_LIST = '/notice/list';    /*  /id --  Список уведомлений для абонента */

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID       = 'sms_id';

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    public const POST_REC       = 'notifyRec';

    /**
     * Имя таблицы в базе
     */
    public const TABLE          = 'sms_list';       // Имя таблицы в базе


    /**
     * Поля базы
     */

    public const F_ID           = 'id';             // ID уведомления
    public const F_ABON_ID      = 'abon_id';        // ID абонента, которому отсылается уведомление
    public const F_TYPE_ID      = 'type_id';        // Тип уведомления: 1 - SMS 2 - Email 3 - ...
    public const F_DATE         = 'date';           // Дата-время отправки уведомления
    public const F_PHONENUMBER  = 'phonenumber';    // Номер телефона, на который отправили СМС
	public const F_EMAIL        = 'email';          // Список адресов email на которые отправлено уведомление
	public const F_SUBJECT      = 'subject';        // Тема уведомления, если предусмотрена, например для email-уведомлений
    public const F_TEXT         = 'text';           // Текст уведомления
    public const F_METHOD       = 'method';         // Метод отправки уведомления: KDEConnect, PHPMailer, скрипт, вэб-служба или что-то ещё
    public const F_SENDER_ID    = 'sender_id';      // ID пользователя от имени которого отправлено уведомление



    public const METHOD_KDE_CONNECT = 'KDE Connect';    // Метод отправки: KDEConnect
    public const METHOD_EMAIL_LIST = 'Email List';     // Метод отправки: PHPMailer -- форма массовой рассылки
    public const METHOD_EMAIL_FORM = 'Email Form';     // Метод отправки: PHPMailer -- форма одиночной отправки письма



    /**
     * вычисляемые поля
     */

    const F_USER_ID             = 'user_id';        // Пользоватеь, к которому относится Абонент, которому относится уведомление
    const F_COUNT               = 'notify_count';   // Общее количество уведомлений
    const F_TODAY               = 'today';          // Дата для функций привязанных ко времени


    /**
     * Флаг-константа для указания Типа электронного писма: информационное, СФ, СФ для полной оплаты до конца месяца.
     */
    const MAIL_TYPE_INFO = 1;           // информационное
    const MAIL_TYPE_INVOICE = 2;        // СФ за текущий месяц
    const MAIL_TYPE_INFO_PRICE_UP = 3;  // СФ для полной оплаты до конца месяца




    /**
     * Фильтры для формирования списка уведомлений
     */

    const FLTR_PREFIX           = 'filer';
    const FLTR_TP_ID            = 'tp_id';
    const FLTR_ABON_ID          = 'abon_id';
    const FLTR_SHOW_PAUSED      = 'show_paused';
    const FLTR_NOT_SEND_DAYS    = 'not_send_days';
    const FLTR_NOT_PAY_DAYS     = 'not_pay_days';
    const FLTR_MAX_COUNT        = 'max_count_sms';
    const FLTR_DO_SCRIPT_SHOW   = 'do_script_show';
    const FLTR_ABON_ID_LIST     = 'abon_id_list';   // Список ID абонентов для которых нужно отправлять СМС. Передаётся из формы списка.



    /**
     * Раздел типов уведомлений
     */

    public const TYPE_NA        = 0;
    public const TYPE_SMS       = 1;
    public const TYPE_EMAIL     = 2;
    public const TYPE_OTHER     = 255;

    public const TYPE_TITLES = [
        self::TYPE_NA    => [
            Lang::C_RU => 'N/A',
            Lang::C_UK => 'N/A',
            Lang::C_EN => 'N/A',
        ],
        self::TYPE_SMS   => [
            Lang::C_RU => 'SMS',
            Lang::C_UK => 'SMS',
            Lang::C_EN => 'SMS',
        ],
        self::TYPE_EMAIL => [
            Lang::C_RU => 'Email',
            Lang::C_UK => 'Email',
            Lang::C_EN => 'Email',
        ],
        self::TYPE_OTHER => [
            Lang::C_RU => 'Другое',
            Lang::C_UK => 'Інше',
            Lang::C_EN => 'Other',
        ],

    ];



    public const TYPE_DESCRIPTIONS = [
        self::TYPE_NA    => [
            Lang::C_RU => 'Тип уведомления не указан',
            Lang::C_UK => 'Тип повідомлення не вказано',
            Lang::C_EN => 'Notification type not specified',
        ],
        self::TYPE_SMS   => [
            Lang::C_RU => 'SMS-Уведомление',
            Lang::C_UK => 'SMS-Повідомлення',
            Lang::C_EN => 'SMS-Notifycation',
        ],
        self::TYPE_EMAIL => [
            Lang::C_RU => 'Эл. почта',
            Lang::C_UK => 'Ел. пошта',
            Lang::C_EN => 'Email',
        ],
        self::TYPE_OTHER => [
            Lang::C_RU => 'Другое',
            Lang::C_UK => 'Інше',
            Lang::C_EN => 'Other',
        ],

    ];



    public static function type_title(?int $type_id): string {
        return self::TYPE_TITLES[$type_id ?? self::TYPE_NA][Lang::code()];
    }



    public static function type_descr(?int $type_id): string {
        return self::TYPE_DESCRIPTIONS[$type_id ?? self::TYPE_NA][Lang::code()];
    }



    public static function save(array $notice): int|false
    {

        // $notice = [
        //     Notify::F_ABON_ID => $abon_id,
        //     Notify::F_TYPE_ID => Notify::TYPE_SMS,
        //     Notify::F_PHONENUMBER => $user[User::F_PHONE_MAIN],
        //     Notify::F_METHOD => Notify::METHOD_KDE_CONNECT,
        //     Notify::F_DATE => time(),
        //     Notify::F_TEXT => $notice_rec['text'],
        // ];

        $model = new AppBaseModel();
        return ($model->insert_row(Notify::TABLE, $notice));
    }



    /**
     * Статусы уведомления абонента
     */
    
    const WARN_IS_OFF = 1;      // УЖЕ ОТКЛЮЧЁН (ПРОВЕРИТЬ)
    const WARN_NEED_OFF = 2;    // ОТКЛЮЧАТЬ
    const WARN_MSG_PAY = 4;     // СООБЩИТЬ О НЕОБХОДИМОСТИ ПЛАТЕЖА
    const WARN_OK = 5;          // НОРМ. Уведомление не требуется

    const WARN_TEXT = [
        self::WARN_IS_OFF => [
            Lang::C_RU => '<span class="badge text-bg-danger">УЖЕ ОТКЛЮЧЁН</span>',
            Lang::C_UK => '<span class="badge text-bg-danger">УЖЕ ВИМКНЕНО</span>',
            Lang::C_EN => '<span class="badge text-bg-danger">ALREADY OFF</span>',
        ],
        self::WARN_NEED_OFF => [
            Lang::C_RU => '<span class="badge text-bg-warning">ОТКЛЮЧАТЬ</span>',
            Lang::C_UK => '<span class="badge text-bg-warning">ВІДКЛЮЧАТИ</span>',
            Lang::C_EN => '<span class="badge text-bg-warning">TURN OFF</span>',
        ],
        self::WARN_MSG_PAY => [
            Lang::C_RU => '<span class="badge text-bg-info">СООБЩИТЬ О НЕОБХОДИМОСТИ ПЛАТЕЖА</span>',
            Lang::C_UK => '<span class="badge text-bg-info">ПОВІДОМИТИ ПРО НЕОБХІДНІСТЬ ПЛАТЕЖУ</span>',
            Lang::C_EN => '<span class="badge text-bg-info">INFORM ABOUT PAYMENT NECESSITY</span>',
        ],
        self::WARN_OK => [
            Lang::C_RU => '<span class="badge text-bg-success">НОРМ. Уведомление не требуется.</span>',
            Lang::C_UK => '<span class="badge text-bg-success">НОРМ. Повідомлення не потрібне.</span>',
            Lang::C_EN => '<span class="badge text-bg-success">NORMAL. No notification required.</span>',
        ],
    ];



    /**
     * Возвращает статус уведомления абонента
     * @param array $rest           -- массив данных абонента из таблицы AbonRest
     * @param int $duty_days_warn   -- количество предоплаченных дней, при пересечении которых, отправлять абоненту предупреждение о необходимости оплаты
     * @param int $duty_days_off    -- количество предоплаченных дней, при пересечении которых, отключать услуги
     * @return int
     */
    public static function get_warn_status(array $rest, int $duty_days_warn, int $duty_days_off) {
        if($rest[AbonRest::F_SUM_PP30A] == 0) {
            return self::WARN_IS_OFF; // УЖЕ ОТКЛЮЧЁН
        } else {
            if($rest[AbonRest::F_PREPAYED] < $duty_days_warn) {
                if($rest[AbonRest::F_PREPAYED] < $duty_days_off) {
                    return self::WARN_NEED_OFF; // ОТКЛЮЧАТЬ
                } else {
                    return self::WARN_MSG_PAY; // СООБЩИТЬ О НЕОБХОДИМОСТИ ПЛАТЕЖА
                }
            } else {
                return self::WARN_OK; // НОРМ. Уведомление не требуется.
            }
        }
    }



    public static function get_warn_message(int $warn_status): string {
        return self::WARN_TEXT[$warn_status][Lang::code()] ?? '';
    }

}