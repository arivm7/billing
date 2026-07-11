<?php
/**
 *  Project : my.ri.net.ua
 *  File    : EmailController.php
 *  Path    : app/controllers/EmailController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 03 Mar 2026 22:17:55
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Контроллер почтовой подсистемы
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace app\controllers;

use app\controllers\NoticeController;
use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Email;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Firm;
use config\tables\Invoice;
use config\tables\Notify;
use config\tables\User;
use DebugView;
use PHPMailer\PHPMailer\PHPMailer;

class EmailController extends AppBaseController  {

    

    public function __construct($route)
    {
        parent::__construct($route);
    }    



    public static function init_mailer(): PHPMailer
    {
        $mail_config = require DIR_CONFIG . '/config_email.php';
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        /**
         * Which method to use to send mail.
         * Options: "mail", "sendmail", or "smtp": isMail() | isSendmail() | isSMTP()
         */
        $mailer->isSMTP();
        $mailer->Host       = $mail_config[Email::CONF_SMTP_HOST];
        $mailer->SMTPAuth   = true;
        $mailer->Username   = $mail_config[Email::CONF_USER];
        $mailer->Password   = $mail_config[Email::CONF_PASS];
        $mailer->SMTPSecure = $mail_config[Email::CONF_SMTP_SECURE];
        $mailer->Port       = $mail_config[Email::CONF_SMTP_PORT];
        $mailer->setFrom($mail_config[Email::CONF_MAIL_FROM], $mail_config[Email::CONF_MAIL_SENDER_NAME]);
        $mailer->addReplyTo($mail_config[Email::CONF_MAIL_RETURN_PATH], $mail_config[Email::CONF_MAIL_SENDER_NAME]);
        $mailer->addBCC($mail_config[Email::CONF_MAIL_RETURN_PATH]);
        return $mailer;
    }

    
    
    /**
     * Просто возвращает правильную структуру для PHPMailer Attachment
     * @param string $path
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @param string $disposition
     * @return array
     */
    public static function attachment_rec(
            string $path,
            string $name = '',
            string $encoding = PHPMailer::ENCODING_BASE64,
            string $type = '',
            string $disposition = 'attachment'): array
    {
        return [
            Email::ATTACH_PATH => $path,
            Email::ATTACH_NAME => $name,
            Email::ATTACH_ENCODING => $encoding,
            Email::ATTACH_TYPE => $type,
            Email::ATTACH_DISPOSITION => $disposition
        ];

    }



    /**
     * Регистрация отправленного письма в базе
     * @param int $abon_id -- Абонент, которому отправлено письмо
     * @param array<int, array{email:string,name:string}> $to -- массив записей адресатов в форматесписок адресатов
     * @param string $subject -- тема письма
     * @param string $body -- тело письма
     * @return bool
     */
    public static function registration(int $abon_id, array $to, string $subject, string $body, string $method = Notify::METHOD_EMAIL_LIST): bool
    {

        $model = new AbonModel();
        if (!$model->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __("abon_id не верен"));
            return false;
        }

        $rec = [
            Notify::F_ABON_ID       => $abon_id,
            Notify::F_TYPE_ID       => Notify::TYPE_EMAIL,     
            Notify::F_DATE          => time(),
            Notify::F_EMAIL         => implode(',', array_column($to, 'email')),
            Notify::F_SUBJECT       => $subject,
            Notify::F_TEXT          => h(html_to_text($body)),
            Notify::F_METHOD        => h($method),
            Notify::F_SENDER_ID     => App::get_user_id(),
        ];

        if (Notify::save($rec) === false) {
            MsgQueue::msg(MsgType::ERROR, __("Не удалось зарегистрировать email"));
            return false;
        } else {
            return true;
        }
    }



    /**
     * Отправка электронного письма
     * attachments -- Вложения, массив записей вида:
     *     [
     *         path = '',
     *         name => '',
     *         encoding => self::ENCODING_BASE64,
     *         type => '',
     *         disposition => 'attachment'
     *     ]
     * @param array<int, array{email:string,name:string}> $to -- массив адресатов
     * @param string $subject       -- тема. Чистый текст
     * @param string $body_text     -- текстовая версия тела письма
     * @param string $body_html     -- html версия тела письма
     * @param array $attachments    -- Массив ассоциативных массивов описывающих вложения
     * @param bool $as_html         -- флаг: отправлять как html, если false то как plain/text
     * @param PHPMailer|null $mailer
     * @param string $log
     * @return bool
     */
    public static function send(
            array $to, 
            string $subject, 
            string $body_text = '', 
            string $body_html = '', 
            array $attachments = [], 
            bool $as_html = true, 
            PHPMailer|null $mailer = null,
            string &$log = ''): bool
    {
        
        if ($mailer === null) {
            $mailer = self::init_mailer();
        }

        try {

            $mailer->clearAddresses();

            foreach ($to as $rec) {
                $rec['email'] = trim($rec['email']);
                $rec['name'] = trim($rec['name'] ?? '');
                if (empty($rec['name'])) {
                    $mailer->addAddress($rec['email']);
                } else {
                    $mailer->addAddress($rec['email'], $rec['name']);
                }
            }
            
            $mailer->Subject = $subject;

            $mailer->isHTML($as_html);
            
            if ($as_html) {
                $mailer->Body    = $body_html;     // HTML версия
                $mailer->AltBody = $body_text ?: html_to_text($body_html);     // текстовая версия
            } else {
                $mailer->Body    = $body_text;     // текстовая версия
            }

            $mailer->clearAttachments();

            foreach ($attachments as $attach_rec) {
                // вложение
                if (is_file($attach_rec[Email::ATTACH_PATH])) {
                    $mailer->addAttachment(
                        path: $attach_rec[Email::ATTACH_PATH],
                        name: $attach_rec[Email::ATTACH_NAME],
                        encoding: $attach_rec[Email::ATTACH_ENCODING],
                        type: $attach_rec[Email::ATTACH_TYPE],
                        disposition: $attach_rec[Email::ATTACH_DISPOSITION]
                    );
                }
            }

            // отправка
            return $mailer->send();
        } catch (\Exception $e) {
            $log .= "Mail Error: " . $mailer->ErrorInfo . "\n";
            $log .= "Error: " . $e->getMessage() . "\n";
            $log .= "Trace: " . $e->getTraceAsString() . "\n";
            return false;
        }
    }
    


    /**
     * проверка поля to, где допускается список email-адресов через запятую.
     * Логика:
     *      поле не пустое
     *      адреса разделены запятой
     *      пробелы обрезаются
     *      каждый адрес валидируется через filter_var()
     *      пустые элементы запрещены
     *      если хотя бы один адрес невалидный → false
     * @param string $to_str
     * @param bool $error_to_msg_queue
     * @return bool
     */
    public static function validate_to(string $to_str, bool $error_to_msg_queue = false): bool
    {
        // Убираем пробелы по краям
        $to_str = trim($to_str);

        if ($to_str === '') {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Field `TO` has empty'); }
            return false;
        }

        
        // Разбиваем по запятой
        $emails = explode(',', $to_str);

        foreach ($emails as $email) {
            $email = trim($email);

            /**
             * Разбираем в массив [[имя, адрес], ]
             * в данном случае, поэлементно, чтоыб выявить ошибки элементов
             */
            $to = self::parse_mail_recipients($email, $error_to_msg_queue);
            
            
            if (!$to) {
                return false;
            }
        }

        return true;
    }



    /**
     * валидация темы письма.
     * Логика:
     *     строка не пустая после trim()
     *     не состоит только из пробелов
     *     ограничение длины (рекомендуется для email-заголовков)
     *     без управляющих символов (защита от header injection)
     * @param string $subject
     * @param bool $error_to_msg_queue
     * @return bool
     */
    public static function validate_subject(string $subject, bool $error_to_msg_queue = false): bool
    {
        // Убираем пробелы по краям
        $subject = trim($subject);

        // Не допускаем пустую строку
        if ($subject === '') {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Тема не может быть пустой'); }
            return false;
        }

        // Ограничение длины (RFC рекомендует разумные пределы)
        // Обычно 255 более чем достаточно
        if (mb_strlen($subject) > 255) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Тема не может быть длинее 255 символов'); }
            return false;
        }

        // Запрет управляющих символов (защита от инъекции заголовков)
        if (preg_match('/[\r\n\t]/', $subject)) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Тема не может содержать управляющих сиволов'); }
            return false;
        }

        return true;
    }


    
    /**
     * Валидация body_text
     * Это обычный текст, поэтому требования простые:
     * Проверить:
     *      строка
     *      не пустая
     *      разумный размер (Указан в конфиге)
     * @param string $text
     * @param bool $error_to_msg_queue
     * @param bool $may_be_empty -- текст может быть пустым
     * @return bool
     */
    public static function validate_body_text(string $text, bool $error_to_msg_queue = false, bool $may_be_empty = true): bool
    {
        $text = trim($text);

        if (!$may_be_empty && ($text === '')) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, "Текст пуст"); }
            return false;
        }

        // ограничение размера (например 100k)
        if (mb_strlen($text) > App::get_config('email_max_body_text')) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, "Превышена максимальная длина текста " . App::get_config('email_max_body_text') . " символов" ); }
            return false;
        }

        return true;
    }



    /**
     * Валидация body_html
     * С HTML всё сложнее.
     * Важно понимать:
     *      HTML нельзя "валидировать" регуляркой.
     *      содержит HTML, поскольку body html должен содержать тэги <html> и <body>
     * Нужно проверять:
     *      это строка
     *      не пустая
     *      размер (из конфига)
     * @param string $html
     * @return bool
     */
    public static function validate_body_html(string $html, bool $error_to_msg_queue = false): bool
    {
        $html = trim($html);

        if ($html === '') {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, "Текст пуст"); }
            return false;
        }

        if (!is_html($html)) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, "Это не html текст"); }
            return false;
        }

        if (mb_strlen($html) > App::get_config('email_max_body_html')) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, "Превышена максимальная длина текста " . App::get_config('email_max_body_html') . " символов" ); }
            return false;
        }

        return true;
    }



    /**
     * Для вложений логика валидации обычно включает:
     * -- строка пути
     * -- не пустая
     * -- файл существует
     * -- это именно файл (не каталог)
     * -- разумный размер
     * -- файл читаем
     * проверка имени файла:
     * -- Убирает пробелы по краям.
     * -- Проверяет на пустую строку.
     * -- Разрешает только буквы, цифры, пробел, -, _, .
     * -- Ограничивает длину до 255 символов.
     * @param string $path
     * @param string $name
     * @param bool $error_to_msg_queue
     * @return bool
     */
    public static function validate_attach(string $path, string $name, bool $error_to_msg_queue = false): bool
    {

        // --- Проверка пути ---
        $path = trim($path);

        if ($path === '') {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Не указан путь к файлу вложения'); }
            return false;
        }

        if (!file_exists($path)) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Файл вложения не найден'); }
            return false;
        }

        if (!is_file($path)) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Указанный путь не является файлом'); }
            return false;
        }

        if (!is_readable($path)) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Файл вложения недоступен для чтения'); }
            return false;
        }

        $max_size = App::get_config('email_max_file_size');
        if (filesize($path) > $max_size) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Размер вложения превышает допустимый ' . $max_size); }
            return false;
        }

        // --- Проверка имени файла ---
        $name = trim($name);

        if ($name === '') {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Имя файла вложения не может быть пустым'); }
            return false;
        }

        // Разрешенные символы: буквы, цифры, пробел, -, _, точка
        if (!preg_match('/^[a-zA-Z0-9\s\-\_\.]+$/u', $name)) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Имя файла содержит недопустимые символы'); }
            return false;
        }

        // Ограничение длины имени файла (обычно 255 символов)
        $email_max_filename_length = App::get_config('email_max_filename_length');
        if (mb_strlen($name) > $email_max_filename_length) {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Имя файла слишком длинное (максимум '.$email_max_filename_length.' символов)'); }
            return false;
        }

        return true;
    }

    

    /**
     * Разбирает строку адресов "Кому" в массив для PHPMailer
     * Возвращает массив в котором для каждого email возвращаются отдельно имя и адрес
     * @param string $to_str -- Строка адресов, через запятую, с именами и email
     * @return array<int, array{email:string,name:string}>
     */
    public static function parse_mail_recipients(string $to_str, bool $error_to_msg_queue = false): array
    {
        $result = [];

        // сначала декодируем HTML-сущности
        $to_str = html_entity_decode($to_str, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // разбиваем по запятым вне кавычек
        $parts = preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/u', $to_str);

        foreach ($parts as $part) {

            $part = trim($part);
            if ($part === '') {
                if ($error_to_msg_queue) { 
                    MsgQueue::msg(MsgType::ERROR, 'parse_mail_recipients: ' . __('Address list item is empty | Элемент списка адресов пуст | Елемент списку адрес порожній')); 
                }
                continue;
            }

            $email = '';
            $name  = '';

            // ищем email
            if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $part, $m)) {

                $email = $m[0];

                // имя — всё что осталось после email
                $name = trim(str_replace($email, '', $part));
                $name = trim($name, "<> \t\n\r\0\x0B\"'");
            }

            if (!$email) {
                if ($error_to_msg_queue) { 
                    MsgQueue::msg(MsgType::ERROR, 'parse_mail_recipients: ' . __('Email address is empty | Адрес электроной почты пуст | Адреса електронної пошти пуста')); 
                }
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($error_to_msg_queue) { 
                    MsgQueue::msg(MsgType::ERROR, 'parse_mail_recipients: ' . __('The email address is not valid | Адрес электронной посты не валидный | Адреса електронної пости не валідна') . ' [<' . $name . '>' . $email . ']');
                }
                continue;
            }

            $result[] = [
                'email' => $email,
                'name'  => $name
            ];
        }

        return $result;
    }



    /**
     * Возвращает строку с темой email сообщения для отправки счёта
     * @param array $agents -- Массив предприятий провайдеров
     * @param array $abon -- Запись абонента для которого отправляется уведомление
     * @return string
     */
    public static function make_email_subject(array $agents, array $abon): string
    {
        $subject = untemplate(
                App::get_config('email_inv_subject_template'),
                [
                    '{AGENT_TITLE}' => $agents[array_key_first($agents)][Firm::F_NAME_TITLE],
                    '{PORT}' => $abon[Abon::F_ID],
                    '{ADDRESS}' => $abon[Abon::F_ADDRESS],
                ]
            );
        return $subject;
    }



    /**
     * Возвращает текстовую строку для вставки в тело письма.
     * Если СФ несколько, то описание и ссылку для каждого СФ.
     * @param array $agent -- Предприятие провайдер от когорого отправляется письмо
     * @param array $abon -- Абонент для которого отправляется уведомление
     * @param array $invoices -- Список счетов, ссылки на котороые нужно отправить
     * @return string
     */
    public static function make_email_body(array $agents, array $abon, array $invoices)
    {

        $header = "<p>Доброго дня.</p>"
                . "<p>Рахунок за послуги доступу до мережі інтернет.</p>"
                . "<p>Особовий рахунок: <font color=#008b8b>".$abon['id']."</font> | <font color=#008b8b>".$abon['address']."</font></p>";

        $text = "<br>";

        // debug($invoices, '$invoices');
        foreach ($invoices as $invoice) {
            $text .= "<p>";
            $text .= $invoice[Invoice::F_TEXT]."<br>";
            $text .= "на суму ".$invoice[Invoice::F_COST_ALL]." грн.<br>";
            $url = "https://my.ri.net.ua/invoice/print/".$invoice[Invoice::F_ID]."?inv=1&act=1&sht=1";
            $text .= "<a href='{$url}' target='_blank'>{$url}</a>";
            $text .= "</p>";
        }

        $agent = $agents[array_key_first($agents)];
        $footer = "<br>"
                . "<p><font size='-1' color=gray>----<br>"
                . "З повагою,<br>"
                . ($agent[Firm::F_NAME_TITLE] ?: $agent[Firm::F_NAME_SHORT]) . ",<br>"
                . ($agent[Firm::F_OFFICE_PHONES] ? $agent[Firm::F_OFFICE_PHONES] . ",<br>" : "")
                . "<a href='https://my.ri.net.ua' target='_blank'>https://my.ri.net.ua</a><br>"
                . "</font></p>";
        return $header . $text . $footer;
    }


    
    /**
     * Список рассылки уведомлений по email
     * @return void
     */
    public function listAction()
    {

        $model = new AbonModel();

        $today = 
                (isset($_GET[Notify::F_TODAY]) ? intval($_GET[Notify::F_TODAY]) : TODAY());

        $autocreate_invoice = 
                (isset($_POST[Email::REC][Email::F_AUTOCREATE_INV]) ? intval($_POST[Email::REC][Email::F_AUTOCREATE_INV]) : 1 );

        $do_send = 
                (isset($_POST[Email::REC][Email::F_DO_SEND]) ? intval($_POST[Email::REC][Email::F_DO_SEND]) : 0 );

        $user_my = App::get_user();

        $to_test_send = trim(
                (isset($_POST[Email::REC][Email::F_TO_TEST]) 
                    ?   $_POST[Email::REC][Email::F_TO_TEST] 
                    :   (!empty($user_my[User::F_EMAIL_MAIN]) 
                            ?   $user_my[User::F_EMAIL_MAIN]
                            :   App::get_config('email_to_debug')
                        )
                )
            );

        /**
         * Отправлять письма
         */
        if ($do_send) {
            // debug($_POST, '_POST');
            // debug($do_send, '$do_send', die:1);

            if (empty($_POST[Email::REC][Abon::TABLE])) {
                MsgQueue::msg(MsgType::INFO, 'Не выбраны абоненты для отправки писем');
            } else {
                
                /**
                 * Инициализация объекта для отправки почты
                 */
                $mailer = self::init_mailer();
                $mailer->SMTPKeepAlive = true;   // держать соединение открытым между send()
                
                foreach ($_POST[Email::REC][Abon::TABLE] as $abon_id => $send) {
                    $rec = $model->get_rec_for_email_send($abon_id, $today);
                    if (count($rec[Invoice::TABLE]) == 0) {
                        /**
                         * Счетов нет
                         */
                        if ($autocreate_invoice) {
                            /**
                             * Создание нового счёта
                             */
                            $inv = InvoiceController::make_invoice(
                                abon:       $rec[Abon::TABLE],
                                today:      $today,
                                agent:      $rec['agents'][array_key_first($rec['agents'])],
                                contragent: $rec['contragents'][array_key_first($rec['contragents'])],
                                rest:       $rec[AbonRest::TABLE]
                            );
                            $inv_id = InvoiceController::registration($inv);
                            if ($inv_id === false) {
                                throw new \Exception("Ошибка записи счёта в базу", 1);
                            }
                            $rec[Invoice::TABLE][] = $model->get_invoice($inv_id);

                        } else {
                            /**
                             * Автосоздание счёта отключено -- пропускаем
                             */
                            continue;
                        }
                    }
                    $to         = self::parse_mail_recipients($rec[User::TABLE][User::F_EMAIL_MAIN]);
                    $subject    = self::make_email_subject($rec['agents'], $rec[Abon::TABLE]);
                    $body_html  = self::make_email_body($rec['agents'], $rec[Abon::TABLE], $rec[Invoice::TABLE]);
                    $body_text  = html_to_text($body_html);
                    $as_html    = boolval($rec[User::TABLE][User::F_EMAIL_SEND_HTML]);

                    $attachments = [];
                    
                    try { // для корректного удаления файлов вложений в случае исключения
                        
                        if ($rec[User::TABLE][User::F_EMAIL_SEND_PDF]) {
                            foreach ($rec[Invoice::TABLE] as $invoice) {
                                $show_inv = 1; // !!! эти параметры нужно откуда-то брать
                                $show_act = 1; // !!! эти параметры нужно откуда-то брать
                                $show_sht = 1; // !!! эти параметры нужно откуда-то брать
                                $path = InvoiceController::generate_pdf($invoice, $show_inv, $show_act, $show_sht);
                                $name = InvoiceController::make_filename($invoice, $show_inv);
                                MsgQueue::msg(MsgType::INFO, 'Создан файл PDF: ' . $name);
                                $attachments[] = 
                                    self::attachment_rec(
                                        path: $path,
                                        name: $name,
                                        type: 'application/pdf',
                                    );
                            }
                        }

                        if (empty($to_test_send)) {
                            /**
                             * Реальная отправка абоненту
                             */
                            $log_send = '';
                            if (self::send(
                                        to: $to, 
                                        subject: $subject, 
                                        body_text: $body_text, 
                                        body_html: $body_html, 
                                        attachments: $attachments, 
                                        as_html: $as_html,
                                        mailer: $mailer,
                                        log: $log_send
                                ))
                            {
                                MsgQueue::msg(MsgType::SUCCESS, 
                                        '<span class="font-monospace">'
                                        . (boolval($rec[User::TABLE][User::F_EMAIL_SEND_HTML]) ? "HTML" : "TEXT") . ' | ' 
                                        . (boolval($rec[User::TABLE][User::F_EMAIL_SEND_PDF])  ? "PDF"  : "---")  . ' | ' 
                                        . 'Успешно: '
                                        . "{$subject}")
                                        . '</span>';
                                /**
                                 * Регистрируем в базе
                                 */
                                if (!self::registration($rec[Abon::TABLE][Abon::F_ID], $to, $subject, $body_text)) {
                                    MsgQueue::msg(MsgType::ERROR, (empty($to_test_send) ? '' : __('Registration error | Ошибка регистрации | Помилка реєстрації') . ': ') . "{$subject}");
                                }
                            } else {
                                MsgQueue::msg(MsgType::ERROR, __('Send error | Ошибка отправки | Помилка відправлення') . ': ' . "{$subject}" . ($log_send ? "<pre>{$log_send}</pre>" : ''));
                            }
                        } else {
                            /**
                             * Тестовая отправка абоненту
                             */
                            $log_send = '';
                            if (self::send(
                                        to: [['email'=>$to_test_send]], 
                                        subject: $subject, 
                                        body_text: $body_text, 
                                        body_html: $body_html, 
                                        attachments: $attachments, 
                                        as_html: $as_html,
                                        mailer: $mailer,
                                        log: $log_send
                                ))
                            {
                                MsgQueue::msg(MsgType::SUCCESS, 
                                        (boolval($rec[User::TABLE][User::F_EMAIL_SEND_HTML]) ? "HTML" : "TEXT") . ' | ' 
                                        . (boolval($rec[User::TABLE][User::F_EMAIL_SEND_PDF])  ? "PDF"  : "---")  . ' | ' 
                                        . 'Тест оправлен успешно: '
                                        . "{$subject}");
                            } else {
                                MsgQueue::msg(MsgType::ERROR, __('Error sending test email | Ошибка отправки тестового письма | Помилка надсилання тестового листа') . ': ' . "{$subject}" . ($log_send ? "<pre>{$log_send}</pre>" : ''));
                            }
                        }

                        
                    } finally { // Для удаления файлов вложении на сервере даже в случае исключения в процессе обработки

                        /**
                         * Удаление файлов вложений на сервере
                         */
                        foreach ($attachments as $attachment) {
                            @unlink($attachment[Email::ATTACH_PATH]);
                        }
                        
                    }


                }
                $mailer->smtpClose();            // закрыть явно после цикла
            }

            redirect();

        }
        


        /**
         * Формирование списка для формы
         */
        $list_send = $model->get_full_list_for_email_send($today);
        foreach ($list_send as &$rec) {
            $rec[Email::REC][Email::F_TO] = self::parse_mail_recipients($rec[User::TABLE][User::F_EMAIL_MAIN]);
            $rec[Notify::TABLE] = NoticeController::get_notice_list($rec[Abon::TABLE][Abon::F_ID], Notify::TYPE_EMAIL, $today);
        }
        unset($rec);

        $title = __('Email invoice distribution list | Список рассылки счетов по электроной почте | Список розсилки рахунків електронною поштою');

        $this->setVariables([
            'title'=> $title,
            'today'=>$today,
            'to_test_send' =>  $to_test_send,
            'autocreate_invoice' => $autocreate_invoice,
            'list_send' => $list_send,

        ]);

        View::setMeta(
            title: $title,
        );


    }

    

    private static function input(string $key, string|int $default = ''): mixed
    {
        return $_POST[Email::REC][$key] ?? $_GET[Email::REC][$key] ?? $default;
    }    


    
    public function formAction() {

        $abon_id = self::input(Email::F_REGISTER_ABON_ID, "");

        $to_str = self::input(Email::F_TO, "");

        $to = EmailController::parse_mail_recipients($to_str);

        $subject = self::input(Email::F_SUBJECT, "");

        $body_text = self::input(Email::F_BODY_TEXT, "");
                    
        $body_html = self::input(Email::F_BODY_HTML, "");

        $attach_path = self::input(Email::F_ATTACH_PATH, "");

        $attach_name = self::input(Email::F_ATTACH_NAME, "");

        /**  Флаг: зарегистрировать  письмо в базе уведомлений */
        $register = self::input(Email::F_REGISTER, 0) ? 1 : 0;

        /**  Флаг: Отправлять это письмо */
        $do_send = self::input(Email::F_DO_SEND, 0) ? 1 : 0;

        $title = __('Email notification form | Форма отправки уведомления электнонной почтой | Форма надсилання повідомлення електронною поштою');

        /**
         * Отправка письма
         */
        if  (
                $do_send &&
                self::validate_to($to_str, true) && 
                self::validate_subject($subject, true) &&
                self::validate_body_text($body_text, true) &&
                self::validate_body_html($body_html, true) &&
                (
                    empty($attach_path) ||
                    (!empty($attach_path) && self::validate_attach($attach_path, $attach_name, true))
                )
            ) 
        {
            $subject = trim($subject);
            $body_text = trim($body_text);
            $body_html = trim($body_html);
            $as_html = !empty($body_html);
            if (empty($body_text)) { $body_text = html_to_text($body_html); }
            $log_send = '';
            if (self::send(
                        to: $to, 
                        subject: $subject, 
                        body_text: $body_text, 
                        body_html: $body_html, 
                        attachments: [], 
                        as_html: $as_html,
                        log: $log_send
                    )
                )
            {
                MsgQueue::msg(MsgType::SUCCESS, __('Successful sending of email | Успешная отправка электронного письма | Успішне надсилання електронного листа') . ' | ' . ($as_html ? "HTML" : "TEXT") . ' | ' . "{$subject}");
                /**
                 * Регистрируем в базе
                 */
                if ($register) {
                    if (self::registration(
                            abon_id: $abon_id, 
                            to: $to, 
                            subject: $subject,
                            body: $body_text,
                            method: Notify::METHOD_EMAIL_FORM)) 
                    {
                        MsgQueue::msg(MsgType::SUCCESS, __('Successful notification registration | Успешная регистрация уведомления | Успішна реєстрація повідомлення') . ' | ' . "{$subject}");
                    } else {
                        MsgQueue::msg(MsgType::ERROR, __('Notification registration error | Ошибка регистрации уведомления | Помилка реєстрації повідомлення') . ' | ' . "{$subject}");
                    }
                }
            } else {
                MsgQueue::msg(MsgType::ERROR, 'Ошибка отправки | ' . ($as_html ? "HTML" : "TEXT") . ' | ' . "{$subject}" . ($log_send ? "<pre>{$log_send}</pre>" : ''));
            }

            redirect();

        }



        $this->setVariables([
            'title'=> $title,
            'to' => $to_str,
            'subject' => $subject,
            'body_text'=>$body_text, 
            'body_html'=>$body_html, 
            'attach_path'=>$attach_path, 
            'attach_name'=>$attach_name,
            'register' => $register,
            'abon_id' => $abon_id,
        ]);

        View::setMeta(
            title: $title,
        );
    }


    
    public function testAction() 
    {
        if (self::send(
                to: [
                        ['name' => 'Ariv', 'email' => "ariv@meta.ua"],
                    ], 
                subject: "TEST MailController 1", 
                body_text: "TEST", 
                body_html: "<h1>TEST</h1>", 
                attachments: [], 
                as_html: true)) 
        {
            echo "Ok<cr>\n";
        } else {
            echo "ERROR<cr>\n";
        }

        exit(0);

    }

}