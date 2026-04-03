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
     * Просто возвращает правильную структуру для PHPMailer Attachment
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
     * Регистрация отправленного письма в базе
     * @param int $abon_id -- Абонент, которому отправлено письмо
     * @param array<int, array{email:string,name:string}> $to -- массив записей адресатов в форматесписок адресатов
     * @param string $subject -- тема письма
     * @param string $body -- тело письма
     * @return bool
     */
    function registration(int $abon_id, array $to, string $subject, string $body, string $method = Notify::METHOD_EMAIL_LIST): bool {

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
     * @return bool
     */
    public function send(array $to, string $subject, string $body_text = '', string $body_html = '', array $attachments = [], bool $as_html = true): bool
    {

        // debug(
        //     [
        //         '$to'=>$to, 
        //         '$subject'=>$subject, 
        //         // '$body_text'=>$body_text, 
        //         // '$body_html'=>$body_html, 
        //         // '$attachments'=>$attachments, 
        //         // '$as_html'=>$as_html
        //     ], 
        //     'Отправка', 
        //     die:0
        // );

        try {

            $this->mailer->clearAddresses();

            foreach ($to as $rec) {
                $rec['email'] = trim($rec['email']);
                $rec['name'] = trim($rec['name'] ?? '');
                if (empty($rec['name'])) {
                    $this->mailer->addAddress($rec['email']);
                } else {
                    $this->mailer->addAddress($rec['email'], $rec['name']);
                }
            }
            
            $this->mailer->Subject = $subject;

            $this->mailer->isHTML($as_html);
            
            if ($as_html) {
                $this->mailer->Body    = $body_html;     // HTML версия
                $this->mailer->AltBody = $body_text ?: html_to_text($body_html);     // текстовая версия
            } else {
                $this->mailer->Body    = $body_text;     // текстовая версия
            }

            $this->mailer->clearAttachments();

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

    

    /**
     * Разбирает строку адресов "Кому" в массив для PHPMailer
     * Возвращает массив в котором для каждого email возвращаются отдельно имя и адрес
     * @param string $to_str -- Строка адресов, через запятую, с именами и email
     * @return array<int, array{email:string,name:string}>
     */
    public static function parse_mail_recipients(string $to_str): array
    {
        $result = [];

        // сначала декодируем HTML-сущности
        $to_str = html_entity_decode($to_str, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // разбиваем по запятым вне кавычек
        $parts = preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/u', $to_str);

        foreach ($parts as $part) {

            $part = trim($part);
            if ($part === '') {
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
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
    public static function make_email_subject(array $agents, array $abon): string {
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
    public static function make_email_body(array $agents, array $abon, array $invoices) {

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
    public function listAction() {

        // debug($_GET, '$_GET', die:0);
        // debug($_POST, '$_POST', die:0);

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

        // debug([
        //     '$today'=>$today,
        //     '$autocreate_invoice'=>$autocreate_invoice,
        //     '$do_send'=>$do_send,
        //     '$to_test_send'=>$to_test_send,
        // ], '', die:0);

        /**
         * Отправлять письма
         */
        if ($do_send) {
            // debug($_POST, '_POST');
            // debug($do_send, '$do_send', die:1);

            if (empty($_POST[Email::REC][Abon::TABLE])) {
                MsgQueue::msg(MsgType::INFO, 'Не выбраны абоненты для отправки писем');
            } else {
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
                        if ($this->send(
                                    to: $to, 
                                    subject: $subject, 
                                    body_text: $body_text, 
                                    body_html: $body_html, 
                                    attachments: $attachments, 
                                    as_html: $as_html
                            ))
                        {
                            MsgQueue::msg(MsgType::SUCCESS, 
                                    (boolval($rec[User::TABLE][User::F_EMAIL_SEND_HTML]) ? "HTML" : "TEXT") . ' | ' 
                                    . (boolval($rec[User::TABLE][User::F_EMAIL_SEND_PDF])  ? "PDF"  : "---")  . ' | ' 
                                    . 'Успешно: '
                                    . "{$subject}");
                            /**
                             * Регистрируем в базе
                             */
                            if (!$this->registration($rec[Abon::TABLE][Abon::F_ID], $to, $subject, $body_text)) {
                                MsgQueue::msg(MsgType::ERROR, (empty($to_test_send) ? '' : 'Ошибка регистрации: ') . "{$subject}");
                            }
                        } else {
                            MsgQueue::msg(MsgType::ERROR, 'Ошибка отправки: ' . "{$subject}");
                        }
                    } else {
                        /**
                         * Тестовая отправка абоненту
                         */
                        if ($this->send(
                                    to: [['email'=>$to_test_send]], 
                                    subject: $subject, 
                                    body_text: $body_text, 
                                    body_html: $body_html, 
                                    attachments: $attachments, 
                                    as_html: $as_html
                            ))
                        {
                            MsgQueue::msg(MsgType::SUCCESS, 
                                    (boolval($rec[User::TABLE][User::F_EMAIL_SEND_HTML]) ? "HTML" : "TEXT") . ' | ' 
                                    . (boolval($rec[User::TABLE][User::F_EMAIL_SEND_PDF])  ? "PDF"  : "---")  . ' | ' 
                                    . 'Тест оправлен успешно: '
                                    . "{$subject}");
                        } else {
                            MsgQueue::msg(MsgType::ERROR, 'Ошибка отправки тестового письма: ' . "{$subject}");
                        }
                    }

                    /**
                     * Удаление файлов вложений на сервере
                     */
                    foreach ($attachments as $attachment) {
                        unlink($attachment[Email::ATTACH_PATH]);
                    }
                }
            }

            redirect();

        }
        


        /**
         * Формирование списка для формы
         */
        $list_send = $model->get_full_list_for_email_send($today);
        foreach ($list_send as &$rec) {
            $rec[Email::REC][Email::F_TO] = self::parse_mail_recipients($rec[User::TABLE][User::F_EMAIL_MAIN]);
            // $rec[Email::REC][Email::F_SUBJECT] = self::make_email_subject($rec['agents'], $rec[Abon::TABLE]);
            // $rec[Email::REC][Email::F_BODY_HTML] = self::make_email_body($rec['agents'], $rec[Abon::TABLE], $rec[Invoice::TABLE]);
            $rec[Notify::TABLE] = NoticeController::get_notice_list($rec[Abon::TABLE][Abon::F_ID], Notify::TYPE_EMAIL, $today);
        }

        $title = __('Список рассылки Счетов по электроной почте');

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



    public function formAction() {

        // debug($_GET, '_GET');
        // debug($_POST, '_POST');

        $abon_id = (isset($_POST[Email::REC][Email::F_REGISTER_ABON_ID]) 
                ? $_POST[Email::REC][Email::F_REGISTER_ABON_ID] 
                : (isset($_GET[Email::REC][Email::F_REGISTER_ABON_ID]) 
                    ? $_GET[Email::REC][Email::F_REGISTER_ABON_ID] 
                    : ""));

        $to_str = (isset($_POST[Email::REC][Email::F_TO]) 
                ? $_POST[Email::REC][Email::F_TO] 
                : (isset($_GET[Email::REC][Email::F_TO]) 
                    ? $_GET[Email::REC][Email::F_TO] 
                    : ""));

        $to = EmailController::parse_mail_recipients($to_str);

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

        /**  Флаг: зарегистрировать  письмо в базе уведомлений */
        $register = (isset($_POST[Email::REC][Email::F_REGISTER]) 
                ? ($_POST[Email::REC][Email::F_REGISTER] ? 1 : 0)
                : (isset($_GET[Email::REC][Email::F_REGISTER]) 
                    ? ($_GET[Email::REC][Email::F_REGISTER] ? 1 : 0)
                    : 0));

        $do_send = (isset($_POST[Email::REC][Email::F_DO_SEND]) 
                ? ($_POST[Email::REC][Email::F_DO_SEND] ? 1 : 0)
                : (isset($_GET[Email::REC][Email::F_DO_SEND]) 
                    ? ($_GET[Email::REC][Email::F_DO_SEND] ? 1 : 0) 
                    : 0));

        $title = __('Форма отправки уведомления электнонной почтой');

        // MsgQueue::msg(MsgType::INFO, "do_send: $do_send"); 
        // MsgQueue::msg(MsgType::INFO, "do_send: $do_send"); 

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
            // debug('Отправка', die:0);
            // debug([
            //     'to_str' => $to_str, 
            //     'subject'=>$subject, 
            //     'body_text'=>$body_text, 
            //     'body_html'=>$body_html, 
            //     'register' => $register,
            //     'abon_id'=>$abon_id,
            //     'attach_path'=>$attach_path, 
            //     'attach_name'=>$attach_name], '$to, $subject, $body_text, $body_html, $attach_path, $attach_name');
            // debug('Отправка', die:1);
            $subject = trim($subject);
            $body_text = trim($body_text);
            $body_html = trim($body_html);
            $as_html = !empty($body_html);
            if (empty($body_text)) { $body_text = html_to_text($body_html); }

            if ($this->send(
                        to: $to, 
                        subject: $subject, 
                        body_text: $body_text, 
                        body_html: $body_html, 
                        attachments: [], 
                        as_html: $as_html
                    )
                )
            {
                MsgQueue::msg(MsgType::SUCCESS, 'Успешная отправка письма | ' . ($as_html ? "HTML" : "TEXT") . ' | ' . "{$subject}");
                /**
                 * Регистрируем в базе
                 */
                if ($register) {
                    if ($this->registration(
                            abon_id: $abon_id, 
                            to: $to, 
                            subject: $subject,
                            body: $body_text,
                            method: Notify::METHOD_EMAIL_FORM)) 
                    {
                        MsgQueue::msg(MsgType::SUCCESS, 'Успешная регистрация уведомления | ' . "{$subject}");
                    } else {
                        MsgQueue::msg(MsgType::ERROR, 'Ошибка регистрации уведомления | ' . "{$subject}");
                    }
                }
            } else {
                MsgQueue::msg(MsgType::ERROR, 'Ошибка отправки | ' . ($as_html ? "HTML" : "TEXT") . ' | ' . "{$subject}");
            }

            // redirect();

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
        if ($this->send(
                to: [
                    'name' => 'Ariv',
                    'email' => "ariv@meta.ua"
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