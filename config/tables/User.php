<?php
/*
 *  Project : my.ri.net.ua
 *  File    : User.php
 *  Path    : config/tables/User.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */


namespace config\tables;

/**
 * Description of User.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class User {

    const URI_EDIT = '/user/edit';
    const URI_UPDATE = '/user/update';

    /**
     * Имя массива для сохранения авторизованной сессии
     */
    const SESSION_USER_REC = 'user';

    /**
     * UID для опрераций от имени биллинговой системы
     */
    const UID_BILLING        = 11;

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    const POST_REC           = 'userRec';

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    const F_GET_ID           = 'user_id';


    /**
     * Имя таблицы в базе
     */
    const TABLE = 'users';

    const F_ID                  = 'id';
    const F_LOGIN               = 'login';              // собственно, сам логин пользователя, varchar(25) 
    const F_PASS_HASH           = 'password2';          // Пароль, шифрованный в 60-байтовый хэш
    const F_PASS_MD5            = 'password';           // пароль хэшированный md5 (устаревший)
    const F_SALT                = 'salt';               // «соль», случайное число используемое для «примеси» к паролю, varchar(3)
    const F_NAME_SHORT          = 'name_short';         // Краткое имя пользователя (отображаемое)
    const F_NAME_FULL           = 'name';               // Полное имя пользователя (отображаемое)
    const F_SURNAME             = 'surname';            // Отчество
    const F_FAMILY              = 'family';             // Фамилия 
    const F_DESCRIPTION         = 'description';        // Дополнительная информация. Служебное поле 
    const F_PHONE_MAIN          = 'phone_main';         // Основной номер телефона
    const F_SMS_DO_SEND         = 'do_send_sms';        // Отправлять автоматические СМС-уведомления в общем списке
    const F_EMAIL_MAIN          = 'mail_main';          // email, для уведомлений
    const F_EMAIL_DO_SEND       = 'do_send_mail';       // Отправлять уведомления и счета электронной почтой
    const F_EMAIL_SEND_HTML     = 'mail_send_html';     // Отправлять письма в формате html
    const F_EMAIL_SEND_PDF      = 'mail_send_pdf';      // Отправлять вложения в формате pdf 
    const F_ADDRESS_INVOICE     = 'address_invoice';    // Адрес доставки бумажных документов, уведомлений, счетов
    const F_INVOICE_DO_SEND     = 'do_send_invoice';    // Доставлять документы и счета в бумажном виде
    const F_JABBER              = 'jabber_main';        // xmpp jabber клиент для отправки сообщений
    const F_JABBER_DO_SEND      = 'jabber_do_send';     // Отправлять сообщения на xmpp jabber
    const F_VIBER               = 'viber';              // Имя учётной записи месенджера Viber
    const F_VIBER_DO_SEND       = 'viber_do_send';      // Отправлять сообщения на Viber
    const F_TELEGRAM            = 'telegram';           // Имя учётной записи месенджера Telegram
    const F_TELEGRAM_DO_SEND    = 'telegram_do_send';   // Отправлять сообщения на Telegram
    const F_SIGNAL              = 'signal_messenger';   // Имя учётной записи месенджера Signal
    const F_SIGNAL_DO_SEND      = 'signal_do_send';     // Отправлять сообщения на Signal
    const F_WHATSAPP            = 'whatsapp';           // Имя учётной записи месенджера WhatsApp
    const F_WHATSAPP_DO_SEND    = 'whatsapp_do_send';   // Отправлять сообщения на WhatsApp
    const F_PRAVA               = 'prava';              // 0 == клиент / пользователь, 1 и более == Административные привилегии
    const F_CREATION_UID        = 'creation_uid';       // Кто создал запись
    const F_CREATION_DATE       = 'creation_date';      // Дата создания записи
    const F_MODIFIED_UID        = 'modified_uid';       // Кто изменил запись
    const F_MODIFIED_DATE       = 'modified_date';      // Дата изменения записи в базе

    /**
     * Поля, которые есть в форме но нет в базе
     */
    const F_FORM_PASS           = 'password_new';
    const F_FORM_PASS2          = 'password_confirm';

    /**
     * Поля формы
     */
    const FORM_FIELDS = [
        self::F_ID               => null,
        self::F_LOGIN            => null,
        self::F_FORM_PASS        => '',
        self::F_FORM_PASS2       => '',
        self::F_NAME_SHORT       => '',
        self::F_NAME_FULL        => '',
        self::F_SURNAME          => '',
        self::F_FAMILY           => '',
        self::F_DESCRIPTION      => '',
        self::F_PHONE_MAIN       => '',
        self::F_SMS_DO_SEND      => 1,
        self::F_EMAIL_MAIN       => '',
        self::F_EMAIL_DO_SEND    => 0,
        self::F_EMAIL_SEND_HTML  => 0,
        self::F_EMAIL_SEND_PDF   => 0,
        self::F_ADDRESS_INVOICE  => '',
        self::F_INVOICE_DO_SEND  => 0,
        self::F_JABBER           => '',
        self::F_JABBER_DO_SEND   => 0,
        self::F_VIBER            => '',
        self::F_VIBER_DO_SEND    => 0,
        self::F_TELEGRAM         => '',
        self::F_TELEGRAM_DO_SEND => 0,
        self::F_SIGNAL           => '',
        self::F_SIGNAL_DO_SEND   => 0,
        self::F_WHATSAPP         => '',
        self::F_WHATSAPP_DO_SEND => 0,
        ];

    /**
     * Поля таблицы, вносимые в базу
     */
    const T_FIELDS = [
        self::F_ID               => null,
        self::F_LOGIN            => null,
        self::F_PASS_HASH        => null,
        self::F_PASS_MD5         => null,
        self::F_SALT             => null,
        self::F_NAME_SHORT       => '',
        self::F_NAME_FULL        => '',
        self::F_SURNAME          => '',
        self::F_FAMILY           => '',
        self::F_PHONE_MAIN       => '',
        self::F_SMS_DO_SEND      => 1,
        self::F_EMAIL_MAIN       => '',
        self::F_EMAIL_DO_SEND    => 0,
        self::F_EMAIL_SEND_HTML  => 0,
        self::F_EMAIL_SEND_PDF   => 0,
        self::F_ADDRESS_INVOICE  => '',
        self::F_INVOICE_DO_SEND  => 0,
        self::F_JABBER           => '',
        self::F_JABBER_DO_SEND   => 0,
        self::F_VIBER            => '',
        self::F_VIBER_DO_SEND    => 0,
        self::F_TELEGRAM         => '',
        self::F_TELEGRAM_DO_SEND => 0,
        self::F_SIGNAL           => '',
        self::F_SIGNAL_DO_SEND   => 0,
        self::F_WHATSAPP         => '',
        self::F_WHATSAPP_DO_SEND => 0,
        self::F_CREATION_UID     => 0,
        self::F_CREATION_DATE    => 0,
        self::F_MODIFIED_UID     => 0,
        self::F_MODIFIED_DATE    => 0,
        ];

    const T_FLAGS = [
        self::F_SMS_DO_SEND      => 1,
        self::F_EMAIL_DO_SEND    => 0,
        self::F_EMAIL_SEND_HTML  => 0,
        self::F_EMAIL_SEND_PDF   => 0,
        self::F_INVOICE_DO_SEND  => 0,
        self::F_JABBER_DO_SEND   => 0,
        self::F_VIBER_DO_SEND    => 0,
        self::F_TELEGRAM_DO_SEND => 0,
        self::F_SIGNAL_DO_SEND   => 0,
        self::F_WHATSAPP_DO_SEND => 0,
        ];

    /* =========================
       Каналы связи (messengers)
       ========================= */

    /**
     * Список всех поддерживаемых мессенджеров.
     * Каждый элемент — массив с полями:
     *   - field : имя поля с идентификатором/адресом
     *   - send  : имя поля-флага "отправлять"
     */
    const MESSENGERS = [
        self::F_JABBER   => [ 'field' => self::F_JABBER,   'send' => self::F_JABBER_DO_SEND ],
        self::F_VIBER    => [ 'field' => self::F_VIBER,    'send' => self::F_VIBER_DO_SEND ],
        self::F_TELEGRAM => [ 'field' => self::F_TELEGRAM, 'send' => self::F_TELEGRAM_DO_SEND ],
        self::F_SIGNAL   => [ 'field' => self::F_SIGNAL,   'send' => self::F_SIGNAL_DO_SEND ],
        self::F_WHATSAPP => [ 'field' => self::F_WHATSAPP, 'send' => self::F_WHATSAPP_DO_SEND ],
    ];

    /**
     * Поля, в которых производится автоподстановка символов
     * типа "<<" -> "«"
     */
    const AUTOREPLACES = [
        self::F_NAME_SHORT,
        self::F_NAME_FULL,
        self::F_SURNAME,
        self::F_FAMILY,
        self::F_DESCRIPTION,
        self::F_ADDRESS_INVOICE,
    ];

}