<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Contacts.php
 *  Path    : config/tables/Contacts.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of Contacts.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Contacts {

    const URI_INDEX          = '/contact';
    const URI_ADD            = '/contact/add';
    const URI_EDIT           = '/contact/edit';
    const URI_VISIBLE        = '/contact/visible';
    const URI_DEL            = '/contact/del';

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    const F_GET_ID          = 'contact_id';
    const F_GET_VISIBLE     = 'visible';

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    const POST_REC          = 'contact';

    const TABLE             = 'phone_numbers';  // Имя таблицы

    const F_ID              = 'id';
    const F_USER_ID         = 'user_id';        // ID пользователя которому относится контакт
    const F_TYPE_ID         = 'type_id';        // Тип контакта
    const F_TITLE           = 'phone_title';    // Название контакта (тел., эл. почта, ...)
    const F_VALUE           = 'phone_number';   // Значение самого контакта (+38098..., user@mail...)
    const F_IS_HIDDEN       = 'is_deleted';     // Контакт скрыт, помечен для удаления
    const F_CREATION_UID    = 'creation_uid';   // Юзер, создавший запись
    const F_CREATION_DATE   = 'creation_date';  // Дата создания записис в базе
    const F_MODIFIED_UID    = 'modified_uid';   // Кто изменил запись
    const F_MODIFIED_DATE   = 'modified_date';  // Кто изменил запись

    /*
     * Типы контактов.
     * ВАЖНО: Должны соответсвовать таблице типов из базы.
     */
    const T_AUTO       =  1;
    const T_PHONE      =  2;
    const T_EMAIL      =  3;
    const T_TELEGRAM   =  4;
    const T_VIBER      =  5;
    const T_SIGNAL     =  6;
    const T_WHATSAPP   =  7;
    const T_NEXTCLOUD  =  8;
    const T_IRC        =  9;
    const T_ADDRESS    = 10;

    const TYPES = [
        self::T_AUTO       => 'Auto',
        self::T_PHONE      => 'Phone',
        self::T_EMAIL      => 'Email',
        self::T_TELEGRAM   => 'Telegram',
        self::T_VIBER      => 'Viber',
        self::T_SIGNAL     => 'Signal',
        self::T_WHATSAPP   => 'WhatsApp',
        self::T_NEXTCLOUD  => 'NextCloud',
        self::T_IRC        => 'IRC',
        self::T_ADDRESS    => 'Address',
    ];


    public static function autoType(string $string): string {
        $str = trim($string);

        // ---------- Телефон ----------
        // +7 (999) 123-45-67 или просто 89991234567
        if (preg_match('/^\+?[0-9\-\(\) ]{7,20}$/', $str)) {
            return self::T_PHONE;
        }

        // ---------- Email ----------
        if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
            return self::T_EMAIL;
        }

        // ---------- Telegram ----------
        if (preg_match('/^(?:https?:\/\/t\.me\/|@)[A-Za-z0-9_]{5,32}$/i', $str)) {
            return self::T_TELEGRAM;
        }

        // ---------- Viber ----------
        if (stripos($str, 'viber://') === 0) {
            return self::T_VIBER;
        }

        // ---------- Signal ----------
        // частые форматы: signal.me/#p/+79991234567
        if (preg_match('/^https?:\/\/signal\.me\//i', $str)) {
            return self::T_SIGNAL;
        }

        // ---------- WhatsApp ----------
        // форматы: https://wa.me/79991234567 или whatsapp://send?phone=...
        if (preg_match('/^(https?:\/\/wa\.me\/|whatsapp:\/\/)/i', $str)) {
            return self::T_WHATSAPP;
        }

        // ---------- NextCloud / OwnCloud ----------
        if (preg_match('/nextcloud|owncloud/i', $str)) {
            return self::T_NEXTCLOUD;
        }

        // ---------- IRC / XMPP ----------
        if (preg_match('/^(irc:\/\/|xmpp:)/i', $str)) {
            return self::T_IRC;
        }

        // ---------- Почтовый адрес ----------
        // простая эвристика: содержит буквы + пробелы, но не URL и не email
        if (preg_match('/[a-zа-яё]{3,}/iu', $str) && !preg_match('/https?:\/\//i', $str)) {
            return self::T_ADDRESS;
        }

        // ---------- Неопределено ----------
        return self::T_AUTO;
    }


}