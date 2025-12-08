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
use Config\Auth;
use config\AutoCorrect;
use config\tables\Abon;
use config\tables\Firm;
use config\tables\Invoice;
use config\tables\Module;
use config\tables\PA;
use config\tables\TP;
use config\tables\User;
use Valitron\Validator;

class InvoiceController extends AppBaseController
{
    

    public function validate_deep($data): bool {
        
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
                !empty($data[Invoice::F_FIRM_AGENT_ID]) && 
                !$model->validate_id(Firm::TABLE, $data[Invoice::F_FIRM_AGENT_ID], Firm::F_ID)
            ) 
        {
            $rez = false;
            MsgQueue::msg(MsgType::ERROR, __('F_FIRM_AGENT_ID error'));
        }

        if  (
                !empty($data[Invoice::F_FIRM_CONTRAGENT_ID]) && 
                !$model->validate_id(Firm::TABLE, $data[Invoice::F_FIRM_CONTRAGENT_ID], Firm::F_ID)
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
            Invoice::F_FIRM_AGENT_ID,
            Invoice::F_FIRM_CONTRAGENT_ID,
            Invoice::F_ABON_ID,
        ]);

        $v->rule('lengthMax', Invoice::F_INV_NO, App::get_config('inv_max_length_number'));

        $v->rule('numeric', [
            Invoice::F_ID,
            Invoice::F_ABON_ID,
            Invoice::F_COST_1,
            Invoice::F_COUNT,
            Invoice::F_COST_ALL,
            Invoice::F_FIRM_AGENT_ID,
            Invoice::F_FIRM_CONTRAGENT_ID,
        ]);

        // Проверка результата
        if (!$v->validate()) {
            MsgQueue::msg(MsgType::ERROR, $v->errors());
            return false;
        }
        return true;
    }


    
    public function normalize(array &$data) {

        // 5️⃣ Проставляем флаги по умолчанию, если отсутствуют
        foreach (Invoice::FLAGS as $flag) {
            if (!isset($data[$flag])) {
                $data[$flag] = 0;
            }
        }

        /**
         * Если передан пустой ID, то убираем его из обновления
         */
        if (isset($data[Invoice::F_ID]) && ($data[Invoice::F_ID] < 1)) {
            unset($data[Invoice::F_ID]);
        }
        if (isset($data[Invoice::F_FIRM_AGENT_ID]) && ($data[Invoice::F_FIRM_AGENT_ID] < 1)) {
            unset($data[Invoice::F_FIRM_AGENT_ID]);
        }
        if (isset($data[Invoice::F_FIRM_CONTRAGENT_ID]) && ($data[Invoice::F_FIRM_CONTRAGENT_ID] < 1)) {
            unset($data[Invoice::F_FIRM_CONTRAGENT_ID]);
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
    


    public function printAction() {

        // debug($_POST, '$_POST');
        // debug($_GET, '$_GET');
        // debug($this->route, '$this->route');

        // if (!App::isAuth()) {
        //     MsgQueue::msg(MsgType::ERROR_AUTO, __('Авторизуйтесь, пожалуйста'));
        //     redirect(Auth::URI_LOGIN);
        // }

        // if (!can_view(Module::MOD_INVOICES)) {
        //     MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав')); // !!! регистрировать
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

        $invoice = $model->get_invoice(intval($this->route[F_ALIAS]));

        // invoice/print/1234?shtamp=1&inv=1&akt=1
        $show_inv = (($_GET[Invoice::F_URI_INV] ?? 1) ? 1 : 0);

        $show_act = (($_GET[Invoice::F_URI_ACT] ?? 1) ? 1 : 0);

        $show_sht = (($_GET[Invoice::F_URI_SHTAMP] ?? 0) ? 1 : 0);

        $abon = $model->get_abon($invoice[Invoice::F_ABON_ID]);
        $user = $model->get_user($abon[Abon::F_USER_ID]);

        $agent = (!empty($invoice[Invoice::F_FIRM_AGENT_ID])
                ? $model->get_firm($invoice[Invoice::F_FIRM_AGENT_ID])
                : $model->get_agent_list($abon[Abon::F_ID])
            );

        $contragent = (!empty($invoice[Invoice::F_FIRM_CONTRAGENT_ID])
                ? $model->get_firm($invoice[Invoice::F_FIRM_CONTRAGENT_ID])
                : get_rec_firm_from_user($user)
            );

        // debug($agent, '$agent');
        // debug($contragent, '$contragent');

        $title = "RILAN-INVOICE-".$invoice[Invoice::F_ABON_ID]."-".mb_substr($invoice[Invoice::F_INV_DATE_STR], 6, 4)."-".mb_substr($invoice[Invoice::F_INV_DATE_STR], 3, 2);
        View::setMeta($title);
        $this->setVariables([
            'title'           => $title,
            'invoice'         => $invoice,
            'show_sht'        => $show_sht,
            'show_inv'        => $show_inv,
            'show_act'        => $show_act,
            'abon'            => $abon,
            'user'            => $user,
            'agent'           => $agent,
            'contragent'      => $contragent,
        ]);

        $this->layout  = 'print';

    }



    public function editAction() {

        // debug($_POST, '$_POST');
        // debug($_GET, '$_GET');
        // debug($this->route, '$this->route');

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Авторизуйтесь, пожалуйста'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_edit(Module::MOD_INVOICES)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав')); // !!! регистрировать
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

        $invoice = $model->get_invoice(intval($this->route[F_ALIAS]));

        /**
         * Данные форомы редактирования
         */
        if (isset($_POST[Invoice::POST_REC]) && is_array($_POST[Invoice::POST_REC])) {
            $post_rec = $_POST[Invoice::POST_REC];
            $this->normalize($post_rec);
            if ($this->validate($post_rec)) {
                if ($this->validate_deep($post_rec)) {
                    $data = get_diff_fields($post_rec, $invoice, Invoice::F_ID);
                    if ($data) {
                        /**
                         * Данные есть. Вносить в базу
                         */
                        if ($model->update_row_by_id(Invoice::TABLE, $data, Invoice::F_ID)) {
                            MsgQueue::msg(MsgType::SUCCESS_AUTO, __("Данные внесены"));
                        } else {
                            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
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
        $agent = $model->get_firm($invoice[Invoice::F_FIRM_AGENT_ID]) ?? [];
        $contragent = $model->get_firm($invoice[Invoice::F_FIRM_CONTRAGENT_ID]) ?? get_rec_firm_from_user($user);
        $agent_list = $model->get_agent_list($abon[Abon::F_ID]);
        $contragent_list = $model->get_firms(user_id: $user[User::F_ID], has_active: 1);
        // debug($invoice, '$invoice');
        // debug($agent, '$agent');
        // debug($contragent, '$contragent');
        // debug($agent_list, '$agent_list');
        // debug($contragent_list, '$contragent_list');

        $title = __('Редактирование Счёта-фактуры, Акта');
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
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Авторизуйтесь, пожалуйста'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_INVOICES)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав')); // !!! регистрировать
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