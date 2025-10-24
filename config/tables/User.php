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
    const F_LOGIN               = 'login';
    const F_PASS_HASH           = 'password2';
    const F_PASS_MD5            = 'password';
    const F_SALT                = 'salt';
    const F_NAME_SHORT          = 'name_short';
    const F_NAME_FULL           = 'name';
    const F_SURNAME             = 'surname';
    const F_FAMILY              = 'family';
    const F_PHONE_MAIN          = 'phone_main';
    const F_SMS_DO_SEND         = 'do_send_sms';
    const F_EMAIL_MAIN          = 'mail_main';
    const F_EMAIL_DO_SEND       = 'do_send_mail';
    const F_ADDRESS_INVOICE     = 'address_invoice';
    const F_INVOICE_DO_SEND     = 'do_send_invoice';
    const F_JABBER              = 'jabber_main';
    const F_JABBER_DO_SEND      = 'jabber_do_send';
    const F_VIBER               = 'viber';
    const F_VIBER_DO_SEND       = 'viber_do_send';
    const F_TELEGRAM            = 'telegram';
    const F_TELEGRAM_DO_SEND    = 'telegram_do_send';
    const F_SIGNAL              = 'signal_messenger';
    const F_SIGNAL_DO_SEND      = 'signal_do_send';
    const F_WHATSAPP            = 'whatsapp';
    const F_WHATSAPP_DO_SEND    = 'whatsapp_do_send';
    const F_PRAVA               = 'prava';
    const F_CREATION_UID        = 'creation_uid';
    const F_CREATION_DATE       = 'creation_date';
    const F_MODIFIED_UID        = 'modified_uid';
    const F_MODIFIED_DATE       = 'modified_date';

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
        self::F_PHONE_MAIN       => '',
        self::F_SMS_DO_SEND      => 1,
        self::F_EMAIL_MAIN       => '',
        self::F_EMAIL_DO_SEND    => 0,
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


}