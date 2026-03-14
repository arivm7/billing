<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Email.php
 *  Path    : config/Email.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 03 Mar 2026 22:19:16
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of Email.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace config;


class Email {

    const URI_FORM = '/email/form';
    const URI_LIST = '/email/list';

    /**
     * Имя массива для передачи данных из/в формы редактирования письма
     */
    const REC = 'email_rec';

    const F_TO = 'to';
    const F_CC = 'cc';
    const F_BCC = 'bcc';
    const F_SUBJECT = 'subject';
    const F_BODY = 'body';
    const F_BODY_TEXT = 'body_text';
    const F_BODY_HTML = 'body_html';
    const F_ATTACH_PATH = 'attach_path';
    const F_ATTACH_NAME = 'attach_name';
    const F_AUTOCREATE_INV  = 'autocreate_inv'; // Флаг: автоматически создавать СФ при отправке уведомленний єлектронной почтой
    const F_TO_TEST  = 'test_send_to'; // Адрес тестовой отправки
    const F_REGISTER  = 'register'; // Флаг: Регистрировать писмо в базе
    const F_REGISTER_ABON_ID  = 'abon_id'; // ID абонента для которого это письмо. Используется только для регистрации.
    const F_DO_SEND = 'do_send'; // Кнопка "Отправить"



    /**
     * Константы для именования полей массива для вложений при использовании PHPMailer
     */
    const ATTACH_PATH = 'path';
    const ATTACH_NAME = 'name';
    const ATTACH_ENCODING = 'encoding';
    const ATTACH_TYPE = 'type';
    const ATTACH_DISPOSITION = 'disposition';


    
    /**
     * Для конфига чтобы небыло опечаток
     */
    const CONF_SMTP_HOST = 'smtp_host';
    const CONF_USER = 'user';
    const CONF_PASS = 'pass';
    const CONF_SMTP_SECURE = 'smtp_secure';
    const CONF_SMTP_PORT = 'smtp_port';
    const CONF_MAIL_FROM = 'email_from';
    const CONF_MAIL_RETURN_PATH = 'email_return_path';
    const CONF_MAIL_SENDER_NAME = 'email_sender_name';

}
