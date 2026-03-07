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

use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Email;
use PHPMailer\PHPMailer\PHPMailer;

class EmailController extends AppBaseController  {



    private PHPMailer $mailer;



    public function __construct($route)
    {
        parent::__construct($route);
        $mail_config = require DIR_CONFIG . '/config_email.php';
       
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8';
        /**
         * Which method to use to send mail.
         * Options: "mail", "sendmail", or "smtp": isMail() | isSendmail() | isSMTP()
         */
        $this->mailer->isSMTP();
        $this->mailer->Host       = $mail_config[Email::CONF_SMTP_HOST];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $mail_config[Email::CONF_USER];
        $this->mailer->Password   = $mail_config[Email::CONF_PASS];
        $this->mailer->SMTPSecure = $mail_config[Email::CONF_SMTP_SECURE];
        $this->mailer->Port       = $mail_config[Email::CONF_SMTP_PORT];
        $this->mailer->setFrom($mail_config[Email::CONF_MAIL_FROM], $mail_config[Email::CONF_MAIL_SENDER_NAME]);
        $this->mailer->addReplyTo($mail_config[Email::CONF_MAIL_RETURN_PATH], $mail_config[Email::CONF_MAIL_SENDER_NAME]);
        $this->mailer->addBCC($mail_config[Email::CONF_MAIL_RETURN_PATH]);
    }    


    /**
     * Просто возвращает правильную структуру для 
     * @param mixed $path
     * @param mixed $name
     * @param mixed $encoding
     * @param mixed $type
     * @param mixed $disposition
     * @return array
     */
    public static function attachment_rec(
        $path,
        $name = '',
        $encoding = PHPMailer::ENCODING_BASE64,
        $type = '',
        $disposition = 'attachment'): array
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
     * Отправка электронного письма
     * attachments -- Вложения, массив записей вида:
     *     [
     *         path = '',
     *         name => '',
     *         encoding => self::ENCODING_BASE64,
     *         type => '',
     *         disposition => 'attachment'
     *     ]
     * @param string $to            -- перечень адресатов через запятую
     * @param string $subject       -- тема. Чистый текст
     * @param string $body_text     -- текстовая версия теля письма
     * @param string $body_html     -- html версия тела письма
     * @param array $attachments    -- Массив ассоциативных массивов описывающих вложения
     * @param bool $as_html         -- флаг: отправлять как html, если false то как plain/text
     * @return bool
     */
    public function send(string $to, string $subject, string $body_text = '', string $body_html = '', array $attachments = [], bool $as_html = true): bool
    {
        try {

            $emails = explode(',', $to);
            foreach ($emails as $email) {
                $email = trim($email);
                $this->mailer->addAddress($email);
            }
            
            $this->mailer->Subject = $subject;

            $this->mailer->isHTML($as_html);
            
            if ($as_html) {
                $this->mailer->Body    = $body_html;     // HTML версия
                $this->mailer->AltBody = ($body_text ?: html_to_text($body_text));     // текстовая версия
            } else {
                $this->mailer->Body    = $body_text;     // текстовая версия
            }

            foreach ($attachments as $attach_rec) {
                // вложение
                if (is_file($attach_rec[Email::ATTACH_PATH])) {
                    $this->mailer->addAttachment(
                        path: $attach_rec[Email::ATTACH_PATH],
                        name: $attach_rec[Email::ATTACH_NAME],
                        encoding: $attach_rec[Email::ATTACH_ENCODING],
                        type: $attach_rec[Email::ATTACH_TYPE],
                        disposition: $attach_rec[Email::ATTACH_DISPOSITION]
                    );
                }
            }

            // отправка
            return $this->mailer->send();
        } catch (\Exception $e) {
            // error_log('Mail error: ' . $this->mail->ErrorInfo);
            echo "Mail Error: " . $this->mailer->ErrorInfo . "\n<hr>";
            echo "Error: " . $e->getMessage() . "\n<hr>";
            echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>\n<hr>";
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
     * @param string $to
     * @param bool $error_to_msg_queue
     * @return bool
     */
    public static function validate_to(string $to, bool $error_to_msg_queue = false): bool {
        // Убираем пробелы по краям
        $to = trim($to);

        if ($to === '') {
            if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Field `TO` has empty'); }
            return false;
        }

        // Разбиваем по запятой
        $emails = explode(',', $to);

        foreach ($emails as $email) {
            $email = trim($email);

            // Запрещаем пустые элементы (например: "a@a.com, , b@b.com")
            if ($email === '') {
                if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Empty email: ' . h($email)); }
                return false;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($error_to_msg_queue) { MsgQueue::msg(MsgType::ERROR, 'Error email: ' . h($email)); }
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
    public static function validate_subject(string $subject, bool $error_to_msg_queue = false): bool {
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
    public static function validate_attach(string $path, string $name, bool $error_to_msg_queue = false): bool {

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


    
    public function formAction() {

        // debug($_GET, '_GET');
        // debug($_POST, '_POST');

        $to = (isset($_POST[Email::REC][Email::F_TO]) 
                ? $_POST[Email::REC][Email::F_TO] 
                : (isset($_GET[Email::REC][Email::F_TO]) 
                    ? $_GET[Email::REC][Email::F_TO] 
                    : ""));

        $subject = (isset($_POST[Email::REC][Email::F_SUBJECT]) 
                ? $_POST[Email::REC][Email::F_SUBJECT] 
                : (isset($_GET[Email::REC][Email::F_SUBJECT]) 
                    ? $_GET[Email::REC][Email::F_SUBJECT] 
                    : ""));

        $body_text = (isset($_POST[Email::REC][Email::F_BODY_TEXT]) 
                ? $_POST[Email::REC][Email::F_BODY_TEXT] 
                : (isset($_GET[Email::REC][Email::F_BODY_TEXT]) 
                    ? $_GET[Email::REC][Email::F_BODY_TEXT] 
                    : ""));
                    
        $body_html = (isset($_POST[Email::REC][Email::F_BODY_HTML]) 
                ? $_POST[Email::REC][Email::F_BODY_HTML] 
                : (isset($_GET[Email::REC][Email::F_BODY_HTML]) 
                    ? $_GET[Email::REC][Email::F_BODY_HTML] 
                    : ""));

        $attach_path = (isset($_POST[Email::REC][Email::F_ATTACH_PATH]) 
                ? $_POST[Email::REC][Email::F_ATTACH_PATH] 
                : (isset($_GET[Email::REC][Email::F_ATTACH_PATH]) 
                    ? $_GET[Email::REC][Email::F_ATTACH_PATH] 
                    : ""));

        $attach_name = (isset($_POST[Email::REC][Email::F_ATTACH_NAME]) 
                ? $_POST[Email::REC][Email::F_ATTACH_NAME] 
                : (isset($_GET[Email::REC][Email::F_ATTACH_NAME]) 
                    ? $_GET[Email::REC][Email::F_ATTACH_NAME] 
                    : ""));

        $do_send = (isset($_POST[Email::REC][Email::F_DO_SEND]) 
                ? ($_POST[Email::REC][Email::F_DO_SEND] ? 1 : 0)
                : (isset($_GET[Email::REC][Email::F_DO_SEND]) 
                    ? ($_GET[Email::REC][Email::F_DO_SEND] ? 1 : 0) 
                    : 0));

        // MsgQueue::msg(MsgType::INFO, "do_send: $do_send"); 
        // MsgQueue::msg(MsgType::INFO, "do_send: $do_send"); 

        if  (
                $do_send &&
                self::validate_to($to, true) && 
                self::validate_subject($subject, true) &&
                self::validate_body_text($body_text, true) &&
                self::validate_body_html($body_html, true) &&
                (
                    empty($attach_path) ||
                    (!empty($attach_path) && self::validate_attach($attach_path, $attach_name, true))
                )
            ) 
        {
            debug('Отправка', die:0);
            debug(['to' => $to, 'subject'=>$subject, 'body_text'=>$body_text, 'body_html'=>$body_html, 'attach_path'=>$attach_path, 'attach_name'=>$attach_name], '$to, $subject, $body_text, $body_html, $attach_path, $attach_name');
            debug('Отправка', die:1);
            /// отправка 
            /// всё. мы тут !!!!

        }
        // debug([$to, $subject, $body], 'fields');

        $title = __('Форма отправки уведомления электнонной почтой');

        $this->setVariables([
            'title'=> $title,
            'to' => $to,
            'subject' => $subject,
            'body_text'=>$body_text, 
            'body_html'=>$body_html, 
            'attach_path'=>$attach_path, 
            'attach_name'=>$attach_name
        ]);

        View::setMeta(
            title: $title,
        );
    }


    
    public function testAction() {
        if ($this->send(to: "ariv@meta.ua", subject: "TEST MailController 1", body: "<h1>TEST</h1>", isHtml: true)) {
            echo "Ok<cr>\n";
        } else {
            echo "ERROR<cr>\n";
        }

        exit(0);

    }

}