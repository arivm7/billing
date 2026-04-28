<?php
/**
 *  Project : my.ri.net.ua
 *  File    : InvoiceController.php
 *  Path    : app/controllers/InvoiceController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 06 Dec 2025 03:15:20
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of InvoiceController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Pagination;
use config\Auth;
use config\AutoCorrect;
use config\Sym;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Firm;
use config\tables\Invoice;
use config\tables\Module;
use config\tables\PA;
use config\tables\TP;
use config\tables\User;
use Valitron\Validator;

require_once DIR_LIBS . '/functions.php';



class InvoiceController extends AppBaseController
{
    

    public static function validate_deep($data): bool {
        
        $model = new AbonModel();
        $rez = true;

        if  (
                empty($data[Invoice::F_ABON_ID]) || 
                !$model->validate_id(Abon::TABLE, $data[Invoice::F_ABON_ID], Abon::F_ID)
            ) 
        {
            $rez = false;
            MsgQueue::msg(MsgType::ERROR, __('F_ABON_ID error'));
        }

        if  (
                !empty($data[Invoice::F_AGENT_ID]) && 
                !$model->validate_id(Firm::TABLE, $data[Invoice::F_AGENT_ID], Firm::F_ID)
            ) 
        {
            $rez = false;
            MsgQueue::msg(MsgType::ERROR, __('F_FIRM_AGENT_ID error'));
        }

        if  (
                !empty($data[Invoice::F_CONTRAGENT_ID]) && 
                !$model->validate_id(Firm::TABLE, $data[Invoice::F_CONTRAGENT_ID], Firm::F_ID)
            ) 
        {
            $rez = false;
            MsgQueue::msg(MsgType::ERROR, __('F_FIRM_CONTRAGENT_ID error'));
        }

        return $rez;
    }



    /**
     * Проверка входных данных от формы
     */
    public static function validate(array $data): bool
    {
        $v = new Validator($data);

        // Правила проверки
        $v->rule('required', [
            Invoice::F_ABON_ID,
            Invoice::F_INV_NO,
            Invoice::F_INV_DATE_STR,
            Invoice::F_AKT_DATE_STR,
            Invoice::F_COST_1,
            Invoice::F_COUNT,
            Invoice::F_COST_ALL,
            Invoice::F_TEXT,
        ])->message('{field} — обязательное поле.');

        $v->rule('integer', [
            Invoice::F_AGENT_ID,
            Invoice::F_CONTRAGENT_ID,
            Invoice::F_ABON_ID,
        ]);

        $v->rule('lengthMax', Invoice::F_INV_NO, App::get_config('inv_max_length_number'));

        $v->rule('numeric', [
            Invoice::F_ID,
            Invoice::F_ABON_ID,
            Invoice::F_COST_1,
            Invoice::F_COUNT,
            Invoice::F_COST_ALL,
            Invoice::F_AGENT_ID,
            Invoice::F_CONTRAGENT_ID,
        ]);

        // Проверка результата
        if (!$v->validate()) {
            MsgQueue::msg(MsgType::ERROR, $v->errors());
            return false;
        }
        return true;
    }



    /**
     * Возвращает строку -- номер счёта для нового счёта, ещё не сформированного в базе,
     * в виде <abon_id>/<порядковый номер>
     * @param int $abon_id
     * @return string
     */
    public static function make_invoice_num(int $abon_id): string {
        $count = self::get_count($abon_id);
        $count++;
        return "{$abon_id}/{$count}";
    }
    


    public static function make_invoice_text(array $abon, int $today = NA): string {
        if ($today == NA) { $today = TODAY(); }
        $inv_body = untemplate(
            App::get_config('inv_body_template'),
            [
                '{ADDRESS}' => $abon['address'],
                '{PORT}' => $abon['id'],
                '{DATE}' => ukr_in_date('M Y', mktime(0, 0, 0, month($today), 1, year($today)))."р.",
            ]);
        return $inv_body;
    }



    public function normalize(array &$data) {

        // 5️⃣ Проставляем флаги по умолчанию, если отсутствуют
        foreach (Invoice::FLAGS as $flag) {
            if (!isset($data[$flag])) {
                $data[$flag] = 0;
            }
        }

        /**
         * Если номер счёта не указан, то сгенерировать автоматический номер счёта.
         * Генерирует на основе abon_id,
         * abon_id должен быть указан и валиден.
         */
        if (empty($data[Invoice::F_INV_NO]) && !empty($data[Invoice::F_ABON_ID])) {
            $data[Invoice::F_INV_NO] = self::make_invoice_num($data[Invoice::F_ABON_ID]);
        }


        /**
         * Если передан пустой ID, то убираем его из обновления
         */
        if (isset($data[Invoice::F_ID]) && ($data[Invoice::F_ID] < 1)) {
            unset($data[Invoice::F_ID]);
        }
        if (isset($data[Invoice::F_AGENT_ID]) && ($data[Invoice::F_AGENT_ID] < 1)) {
            unset($data[Invoice::F_AGENT_ID]);
        }
        if (isset($data[Invoice::F_CONTRAGENT_ID]) && ($data[Invoice::F_CONTRAGENT_ID] < 1)) {
            unset($data[Invoice::F_CONTRAGENT_ID]);
        }


        foreach ($data as $key => $value) {
            // 1️⃣ Если флаг — установить 0 или 1
            if (in_array($key, Invoice::FLAGS, true)) {
                $data[$key] = empty($value) ? 0 : 1;
                // continue;
            }

            // 2️⃣ Если числовое поле — привести к int (все числовые поля — целые)
            if (in_array($key, Invoice::INT_TYPES, true)) {
                $data[$key] = is_numeric($value) ? (int)$value : 0;
                // continue;
            }

            // 2️⃣ Если float поле — привести к float
            if (in_array($key, Invoice::FLOAT_TYPES, true)) {
                $data[$key] = floatval($value);
                // continue;
            }

            // 3️⃣ Если строковое поле — обрезать пробелы
            if (in_array($key, Invoice::STR_TYPES, true)) {
                $data[$key] = trim((string)$value);
                // continue;
            }

            // 3️⃣ Если строковое поле — обрезать пробелы
            if (in_array($key, Invoice::AUTOCORRECT_FIELDS, true)) {
                $data[$key] = AutoCorrect::correct($data[$key]);
                // continue;
            }

            // 4️⃣ Остальное — ооставить как есть
        }
    }



    public static function get_count(int $abon_id): int {
        $sql = "SELECT count(`".Invoice::F_ID."`) AS count_id FROM `".Invoice::TABLE."` WHERE `".Invoice::F_ABON_ID."` = '".$abon_id."'";
        $model = new AbonModel();
        return  $model->query(sql: $sql, fetchCell: 0);
    }



    /**
     * Запись счёта в базу записи счёта в виде заполненного ассоциативного массива с обязательными полями:
     *      Invoice::F_ABON_ID
     *      Invoice::F_AGENT_ID
     *      Invoice::F_CONTRAGENT_ID
     *      Invoice::F_INV_NO
     *      Invoice::F_INV_DATE_STR
     *      Invoice::F_AKT_DATE_STR
     *      Invoice::F_FIRM_PAYER_STR
     *      Invoice::F_COST_1
     *      Invoice::F_COUNT
     *      Invoice::F_COST_ALL
     *      Invoice::F_TEXT
     * @param array $invoice -- заполненный ассоциативный масив счёта
     * @return bool|int
     */
    public static function registration(array $invoice): int|false 
    {
        /**
         * Добавляем поля, не относящиеся прямл к счёту
         */
        $invoice[Invoice::F_IS_PAID]          = 0;
        $invoice[Invoice::F_MODIFIED_DATE]    = time();
        $invoice[Invoice::F_MODIFIED_UID]     = App::get_user_id();
        $invoice[Invoice::F_CREATION_DATE]    = time();
        $invoice[Invoice::F_CREATION_UID]     = App::get_user_id();

        $model = new AbonModel();

        // debug($invoice, '$invoice');

        return $model->insert_row(Invoice::TABLE, $invoice);
    }



    /**
     * Создание Счёт-фактуры (СФ) на месяц, указанный в $today, если $today = NA, то на текущий.
     * Обязательно должен быть указан или $abon_id или $abon.
     * @param int|null $abon_id
     * @param array|null $abon
     * @param int $today -- дата внутри месяца для которого месяца нужно создать СФ
     * @param array|null $agent
     * @param array|null $contragent
     * @throws \Exception
     * @return array -- Возвращает запись СФ
     */
    public static function make_invoice(?int $abon_id = null, ?array $abon = null, int $today = NA, ?array $agent = null, ?array $contragent = null, ?array $rest = null): array {
        if (empty($abon_id) && empty($abon)) {
            throw new \Exception("Не передан абонент. Нужно указать abon_id или запись абонента в массиве abon", 1);
        }

        if($today == NA) { $today = TODAY(); }
        $mail_body = "";

        /**
         * Дата счтета -- начало периода оказания услуги
         */
        $inv_date_str = date("d.m.Y", first_day_month($today)); //01.08.2023

        /**
         * Дата Акта -- конец периода оказания услуги
         */
        $akt_date_str = date("d.m.Y", last_day_month($today)); //31.08.2023

        $model = new AbonModel();

        if (empty($abon)) { $abon = $model->get_abon($abon_id); }
        $user = $model->get_user($abon[Abon::F_USER_ID]);
        
        /**
         * Номер Счёта-фактуры
         */
        $inv_no = self::make_invoice_num($abon[Abon::F_ID]);

        /**
         * Предприятие провайдер
         */
        if (empty($agent)) {
            $agents = $model->get_agents_by_abon_id($abon[Abon::F_ID]);
            $agent = $agents[array_key_first($agents)];
        }

        /**
         * Предприятие клиент
         */
        if (empty($contragent)) { 
            $contragents = $model->get_firms_by_uid_cli($abon[Abon::F_USER_ID]); 
            if(empty($contragents)) {
                $contragents[0][Firm::F_ID] = 0;
                $contragents[0][Firm::F_NAME_LONG] = $user[User::F_NAME_FULL];
                $contragents[0][Firm::F_NAME_SHORT] = $user[User::F_NAME_SHORT];
            }        
            $contragent = $contragents[array_key_first($contragents)];
        }

        
        if (empty($rest)) {
            $rest = $model->get_abon_rest($abon[Abon::F_ID]);
        }
        
        $cost_1 = round(($rest[AbonRest::F_SUM_PP01A] * days_of_month($today)), 2);

        /**
         * Текст Счёта
         */
        $inv_body = self::make_invoice_text($abon, $today);

        $invoice = [
            Invoice::F_AGENT_ID => $agent[Firm::F_ID],
            Invoice::F_CONTRAGENT_ID => $contragent[Firm::F_ID],
            Invoice::F_ABON_ID => $abon[Abon::F_ID],
            Invoice::F_INV_NO => $inv_no,
            Invoice::F_INV_DATE_STR => $inv_date_str,
            Invoice::F_AKT_DATE_STR => $akt_date_str,
            Invoice::F_FIRM_PAYER_STR => $contragent[Firm::F_NAME_SHORT],
            Invoice::F_COST_1 => $cost_1,
            Invoice::F_COUNT => 1,
            Invoice::F_COST_ALL => $cost_1,
            Invoice::F_TEXT => $inv_body,
        ];
        // $inv_id = self::registration(
        //         $agent[Firm::F_ID],
        //         $contragent[Firm::F_ID],
        //         $abon[Abon::F_ID],
        //         $inv_no,
        //         $contragent[Firm::F_NAME_SHORT],
        //         $inv_date_str, 
        //         $akt_date_str,
        //         $cost_1, 1, $cost_1,
        //         $inv_body);

        // if ($inv_id === false) {
        //     throw new \Exception('По какой-то причине Счет-фактура не создана.');
        // }
        return $invoice;
    }


    public static function make_filename(array $invoice, bool|int|null $show_inv = null): string {

        $show_inv = (($show_inv ?? 1) ? 1 : 0);

        $inv_date = (validate_date_str($invoice[Invoice::F_INV_DATE_STR])
            ?   mb_substr($invoice[Invoice::F_INV_DATE_STR], 6, 4)."-".mb_substr($invoice[Invoice::F_INV_DATE_STR], 3, 2)
            :   '____-__') 
            .   '-' . sanitize_filename('No_' . $invoice[Invoice::F_INV_NO] . '_ID' . $invoice[Invoice::F_ID]);

        $act_date = (validate_date_str($invoice[Invoice::F_AKT_DATE_STR])
            ?   mb_substr($invoice[Invoice::F_AKT_DATE_STR], 6, 4)."-".mb_substr($invoice[Invoice::F_AKT_DATE_STR], 3, 2)
            :   '____-__')
            .   '-' . sanitize_filename('No_' . $invoice[Invoice::F_INV_NO] . '_ID' . $invoice[Invoice::F_ID]);

        return  ($show_inv 
                    ? "RILAN-INVOICE-".$invoice[Invoice::F_ABON_ID]."-".$inv_date
                    : "RILAN-AKT-"    .$invoice[Invoice::F_ABON_ID]."-".$act_date
                );

    }



    /**
     * Генерирует pdf и сохраняет его в указанном месте на диске на сервере. 
     * Возвращает путь к сгенерированному файлу.
     * @param array $invoice
     * @param bool|int|null $show_inv
     * @param bool|int|null $show_act
     * @param bool|int|null $show_sht
     * @param mixed $filename
     * @return string
     */
    public static function generate_pdf(array $invoice, bool|int|null $show_inv = null, bool|int|null $show_act = null, bool|int|null $show_sht = null, ?string $filename = null): string {

        $show_inv = (($show_inv ?? 1) ? 1 : 0);
        $show_act = (($show_act ?? 1) ? 1 : 0);
        $show_sht = (($show_sht ?? 1) ? 1 : 0);

        $filename = ($filename ?? InvoiceController::make_filename($invoice, $show_inv) . '.pdf');

        $url = build_url(URL_HOST . Invoice::URI_PRINT . '/' . $invoice[Invoice::F_ID], [
            Invoice::F_URI_INV      => $show_inv,
            Invoice::F_URI_ACT      => $show_act,
            Invoice::F_URI_SHTAMP   => $show_sht,
            Invoice::F_URI_BUTTONS  => 0
        ]);

        $output = DIR_TEMP . "/pdf_gen/{$filename}";
        $cmd = "sudo -u ar /usr/bin/node /var/www/pdfgen/render.js " . escapeshellarg($url) . " " . escapeshellarg($output) . " 2>&1";
        exec($cmd, $lines, $return_var);
        // var_dump($lines, $return_var);

        return $output;

    }



    /**
     * Получить PDF-файл счёта
     * использовать примерно так:
     * https://my.ri.net.ua/invoice/pdf/2429?inv=1&act=1&sht=1
     * @return never
     */
    public function pdfAction() {

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_view(Module::MOD_INVOICES)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав')); 
            self::log_no_rights();
            redirect();
        }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !is_numeric($this->route[F_ALIAS]) ||
                !$model->validate_id(Invoice::TABLE, intval($this->route[F_ALIAS]), Invoice::F_ID)
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID счёта не указан или не верен'));
            redirect();
        }

        /**
         * Запись Счёта/Акта полученная из бызы
         */
        $invoice = $model->get_invoice(intval($this->route[F_ALIAS]));

        $show_inv = (($_GET[Invoice::F_URI_INV] ?? 1) ? 1 : 0);
        $show_act = (($_GET[Invoice::F_URI_ACT] ?? 1) ? 1 : 0);
        $show_sht = (($_GET[Invoice::F_URI_SHTAMP] ?? 0) ? 1 : 0);

        $pdf_file = InvoiceController::generate_pdf(invoice: $invoice, show_inv: $show_inv, show_act: $show_act, show_sht: $show_sht);

        /**
         * Тип передаваемого контента
         */
        header('Content-Type: application/pdf');
        /**
         * Параметры Content-Disposition
         * 
         * inline:      Задаёт, что содержимое должно отображаться в браузере, а не загружаться как файл. 
         *              Это может применяться, например, для изображений или PDF-документов.
         *              header('Content-Disposition: inline; filename="document.pdf"');
         * 
         * attachment:  Указывает, что содержимое должно быть загружено как файл. 
         *              Браузер предложит сохранить файл.
         *              header('Content-Disposition: attachment; filename="document.pdf"');
         * 
         * filename:    Этот параметр позволяет задать имя файла, 
         *              которое будет предложено пользователю при сохранении. 
         */
        header('Content-Disposition: inline; filename="'.basename($pdf_file).'"');
        readfile($pdf_file);
        unlink($pdf_file);

        exit(0);

    }



    public function printAction() {

        // debug($_POST, '$_POST');
        // debug($_GET, '$_GET');
        // debug($this->route, '$this->route');

        // if (!App::isAuth()) {
        //     MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
        //     self::log_unauthorize();
        //     redirect(Auth::URI_LOGIN);
        // }

        // if (!can_view(Module::MOD_INVOICES)) {
        //     MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав')); // !!! регистрировать
        //     self::log_no_rights();
        //     redirect();
        // }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !is_numeric($this->route[F_ALIAS]) ||
                !$model->validate_id(Invoice::TABLE, intval($this->route[F_ALIAS]), Invoice::F_ID)
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID счёта не указан или не верен'));
            redirect();
        }

        /**
         * Запись Счёта/Акта полученная из бызы
         */
        $invoice = $model->get_invoice(intval($this->route[F_ALIAS]));

        $show_inv = (($_GET[Invoice::F_URI_INV] ?? 1) ? 1 : 0);
        $show_act = (($_GET[Invoice::F_URI_ACT] ?? 1) ? 1 : 0);
        $show_sht = (($_GET[Invoice::F_URI_SHTAMP] ?? 0) ? 1 : 0);

        /**
         * Показывать кнопки. По умолчанию 1 -- показывать  
         */
        $show_buttons = (($_GET[Invoice::F_URI_BUTTONS] ?? 1) ? 1 : 0);

        /**
         * Абонент, для которого віписан Счёт/Акт
         */
        $abon = $model->get_abon($invoice[Invoice::F_ABON_ID]);

        /**
         * Пользователь, для которого віписан Счёт/Акт
         */
        $user = $model->get_user($abon[Abon::F_USER_ID]);

        /**
         * Препдриятие-провайдер. 
         * Если в счёте указан, то оно и будет, 
         * если не указано, то для Исполнителя будет выбрано первое предприятие из списка обслуживающих абонента
         */
        $agent_list = $model->get_agent_list($abon[Abon::F_ID]);
        $agent = (!empty($invoice[Invoice::F_AGENT_ID])
                ? $model->get_firm($invoice[Invoice::F_AGENT_ID])
                : $agent_list[array_key_first($agent_list)] ?? []
            );

        /**
         * Предприятие-абонент. 
         * Если в счёте указано, то оно и будет,
         * если не указано, то будет выбрано предприятие, связанное с пользователем, для которого віписан Счёт/Акт
         */
        $contragent = (!empty($invoice[Invoice::F_CONTRAGENT_ID])
                ? $model->get_firm($invoice[Invoice::F_CONTRAGENT_ID])
                : get_rec_firm_from_user($user)
            );

        // debug($agent, '$agent');
        // debug($contragent, '$contragent');

        /**
         * Заголовок страницы, из которого формируется имя файла для сохранения
         */
        $title = InvoiceController::make_filename($invoice, $show_inv);

        View::setMeta($title);
        $this->setVariables([
            'title'           => $title,        // Заголовок страницы, из которого формируется имя файла для сохранения
            'invoice'         => $invoice,      // Запись Счёта/Акта полученная из бызы
            'show_sht'        => $show_sht,     // Флаг: 1|0 -- Показывать штамп и подпись
            'show_inv'        => $show_inv,     // Флаг: 1|0 -- Показывать Счёт
            'show_act'        => $show_act,     // Флаг: 1|0 -- Показывать Акт
            'abon'            => $abon,         // Абонент, для которого віписан Счёт/Акт
            'user'            => $user,         // Пользователь, для которого віписан Счёт/Акт
            'agent'           => $agent,        // Предприятие-провайдер.
            'contragent'      => $contragent,   // Предприятие-абонент.
        ]);

        if ($show_buttons) {
            $this->layout  = 'printButtons';
        } else {
            $this->layout  = 'print';
        }
        // debug($this->view, '$this->view', die:0);
    }



    /**
     * Чтение полей нового счёта из GET параметров
     * @return array
     */
    public static function read_parameters(): array {
        $rec = [];
        foreach (Invoice::FIELDS_NEW_RECORD as $field) {
            if (isset($_GET[$field])) { $rec[$field] = $_GET[$field]; }
        }
        return $rec;
    }



    public function editAction() {

        // debug($_POST, '$_POST');
        // debug($_GET, '$_GET');
        // debug($this->route, '$this->route');

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_edit(Module::MOD_INVOICES)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            self::log_no_rights();
            redirect();
        }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !is_numeric($this->route[F_ALIAS]) ||
                !$model->validate_id(Invoice::TABLE, intval($this->route[F_ALIAS]), Invoice::F_ID)
            ) 
        {
            $invoice = self::read_parameters();
            $this->normalize($invoice);
            // debug($invoice, '$invoice', die:1);
            if (!$model->validate_id(Abon::TABLE, $invoice[Invoice::F_ABON_ID] ?? 0, Abon::F_ID)) {
                MsgQueue::msg(MsgType::ERROR, __('При создании нового счёта нужно обязательно указать правильный ID абонента.'));
                redirect();

            }
        } 
        else
        {
            $invoice = $model->get_invoice(intval($this->route[F_ALIAS]));
        }


        /**
         * Данные форомы редактирования или создания счёта
         */
        if (isset($_POST[Invoice::POST_REC]) && is_array($_POST[Invoice::POST_REC])) {
            $post_rec = $_POST[Invoice::POST_REC];
            $this->normalize($post_rec);
            if ($this::validate($post_rec)) {
                if ($this::validate_deep($post_rec)) {
                    $data = (empty($post_rec[Invoice::F_ID]) 
                                ? $post_rec 
                                : get_diff_fields($post_rec, $invoice, Invoice::F_ID));
                    if ($data) {
                        /**
                         * Данные есть. Вносить в базу
                         */
                        if (empty($data[Invoice::F_ID])) {
                            /**
                             * Новая запись
                             */
                            $data[Invoice::F_CREATION_DATE] = time();
                            $data[Invoice::F_CREATION_UID] = App::get_user_id();
                            $data[Invoice::F_MODIFIED_DATE] = time();
                            $data[Invoice::F_MODIFIED_UID] = App::get_user_id();
                            // debug($data, '$data', die:1);
                            $invoice_id = $model->insert_row(Invoice::TABLE, $data);
                            if ($invoice_id === false) {
                                MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                                redirect();
                            } else {
                                MsgQueue::msg(MsgType::SUCCESS_AUTO, __("Счёт сформирован и добавлен в базу"));
                                redirect(Invoice::URI_EDIT . '/' . $invoice_id);
                            }
                        }
                        else
                        {
                            if ($model->update_row_by_id(Invoice::TABLE, $data, Invoice::F_ID)) {
                                MsgQueue::msg(MsgType::SUCCESS_AUTO, __("Данные внесены"));
                            } else {
                                MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                            }
                        }

                    } else {
                        /**
                         * Данных нет. Просто сообщить
                         */
                        MsgQueue::msg(MsgType::INFO_AUTO, __("нет изменений в данных"));
                    }
                }
            }
            redirect(Invoice::URI_EDIT . '/' . $invoice[Invoice::F_ID]);
        }


        $abon = $model->get_abon($invoice[Invoice::F_ABON_ID]);
        $user = $model->get_user($abon[Abon::F_USER_ID]);
        $agent = $model->get_firm($invoice[Invoice::F_AGENT_ID] ?? 0) ?? [];
        $contragent = $model->get_firm($invoice[Invoice::F_CONTRAGENT_ID] ?? 0) ?? get_rec_firm_from_user($user);
        $agent_list = $model->get_agent_list($abon[Abon::F_ID]);
        $contragent_list = $model->get_firms(user_id: $user[User::F_ID], has_active: 1);
        // debug($invoice, '$invoice');
        // debug($agent, '$agent');
        // debug($contragent, '$contragent');
        // debug($agent_list, '$agent_list');
        // debug($contragent_list, '$contragent_list');

        $title = empty($invoice[Invoice::F_ID]) ? __('Создание нового Счёта-фактуры, Акта') : __('Редактирование Счёта-фактуры, Акта');
        View::setMeta($title);
        $this->setVariables([
            'title'           => $title,
            'invoice'         => $invoice,
            'abon'            => $abon,
            'user'            => $user,
            'agent'           => $agent,
            'contragent'      => $contragent,
            'agent_list'      => $agent_list,
            'contragent_list' => $contragent_list,
        ]);


    }

    
    public function listAction()
    {
        // debug($_POST, '$_POST');
        // debug($_GET, '$_GET');
        // debug($this->route, '$this->route');

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use([Module::MOD_INVOICES, Module::MOD_MY_INVOICES])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            self::log_no_rights();
            redirect();
        }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !is_numeric($this->route[F_ALIAS]) ||
                !$model->validate_id(Abon::TABLE, intval($this->route[F_ALIAS]), Abon::F_ID)
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Абон ID не указан'));
            redirect();
        }

        $abon = $model->get_abon(intval($this->route[F_ALIAS]));
        $user = $model->get_user($abon[Abon::F_USER_ID]);
        $rest = $model->get_abon_rest($abon[Abon::F_ID]);
        $agent_list = $model->get_agent_list($abon[Abon::F_ID]);
        $contragent_list = $model->get_firms(user_id: $user[User::F_ID], has_active: 1);

        // debug($agent_list, '$agent_list');
        // debug($contragent_list, '$client_list');

        $pager = new Pagination(
            per_page: App::get_config('inv_per_page'),
            sql: $model->get_invoices_sql($abon[Abon::F_ID]),
        );

        $invoices = $pager->get_rows();
        // debug($invoices, '$invoices');

        $title = __('Список Счетов, Актов');
        View::setMeta($title);
        $this->setVariables([
            'title'           => $title,
            'abon'            => $abon,
            'user'            => $user,
            'rest'            => $rest,
            'pager'           => $pager,
            'invoices'        => $invoices,
            'agent_list'      => $agent_list,
            'contragent_list' => $contragent_list,
        ]);
    }



}