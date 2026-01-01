<?php
/**
 *  Project : my.ri.net.ua
 *  File    : BankController.php
 *  Path    : app/controllers/BankController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:12:45
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of BankController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Module;
use config\Bank;
use config\P24acc;
use config\tables\Pay;
use config\tables\Ppp;

class BankController extends AppBaseController
{

    /**
     * Статусы выполнения внесений в биллинг
     * @var int
     */
    const DO_STATUS_OK = 1;
    const DO_STATUS_ERROR = -1;
    const DO_STATUS_NA = 0;



    function monocardUpdate(array &$data) {
        debug($data, '$data', die:1);   
    }



    function monocardAction() {

        if (!App::isAuth()) { redirect('/'); }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
            redirect();
        }

        if(isset($_POST[Bank::POST_REC]) && is_array($_POST[Bank::POST_REC])) {
            $post_rec = $_POST[Bank::POST_REC];
            $this->monocardUpdate($post_rec);
        }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !$model->validate_ppp((int)$this->route[F_ALIAS])
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID не верен'));
            redirect();
        }

        $ppp = $model->get_ppp((int)$this->route[F_ALIAS]);

        if (!is_supported_api($ppp, Bank::API_TYPE_MONO_CARD)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ППП не поддерживает этот API'));
            redirect();
        }

        MsgQueue::msg(MsgType::INFO_AUTO, "====Получение данных====");


        if($ppp['owner_id'] != $_SESSION['id']) { die("ППП чужой"); }
        if(!is_supported_api($ppp,  Bank::API_TYPE_MONO_CARD)) { die("ППП не поддерживает нужный АПИ"); }

        // $ppp_type_id        = PPP_TYPE_CARD; // 2 Карта
        // $pay_type_id        = PAY_TYPE_CASH; // 1 - Денежное пополнение ЛС | 2 - Корректировка ЛС | 3 - Начисление за услугу

        // $templates          = get_abons_templates($ppp_id, $templates_size);
        // echo "Размер templates ". sprintf("%.2f", $templates_size/1024/1024)."(".sprintf("%.2f", $templates_size_max/1024/1024).") Mbytes<br>\n";

        // //$last_pay           = get_last_pay_on_ppp($ppp_id);
        // $followId           = (isset($_GET['followId'])  ? $_GET['followId'] : null);
        // $limit              = (isset($_GET['limit'])     ? $_GET['limit']    : 25);
        // $date2              = (isset($_GET['endDate'])
        //                         ? $_GET['endDate']
        //                         : date('Y-m-d'));
        // $date1              = (isset($_GET['startDate'])
        //                         ? $_GET['startDate']
        //                         : date('Y-m-d', time()-DATE_INTERVAL));
        // echo "Даты выборки: <font color=GREEN>".$date1." -- ".$date2."</font><br>\n";

        // $card_token         = $ppp['api_id'];   // 'uBTaoYCANLjLXz44583xHOHfaQrAWgm';
        // $api_url            = $ppp['api_url'];  // 'https://api.monobank.ua/personal/statement/';


        // if (($date1_int = strtotime($date1)) === false) {
        //     die("Не верный формат даты1 [$date1]");
        // }
        // if (($date2_int = strtotime($date2)) === false) {
        //     die("Не верный формат даты2 [$date2]");
        // }
        // $request_url = $api_url.""."0"."/".$date1_int."/".($date2_int+(1*24*60*60));
        // //echo "<pre>d: "; var_dump(htmlentities(str_replace("><", ">\n<", $data))); echo "</pre><hr>";



        // $headers = array
        // (
        //     'Content-type: application/json;charset=UTF-8',
        //     'X-Token: '.$card_token
        // );
        // //echo "<pre>headers: "; var_dump($headers); echo "</pre><hr>";

        // $ch = curl_init("https://api.monobank.ua/personal/client-info");
        // //echo "<pre>ch: "; var_dump($ch); echo "</pre><hr>";
        // curl_setopt($ch, CURLOPT_POST,              0); //Использовать метод POST
        // curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1);
        // curl_setopt($ch, CURLOPT_HEADER,            0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1);

        // $res = curl_exec($ch);
        // $res_decode =  json_decode($res, true);
        // //echo "res(decode):<pre>"; print_r($res_decode); echo "</pre><hr>";
        // /*
        // * ERRORS:
        // * [errorDescription] => Too many requests
        // *
        // */
        // $info=curl_getinfo($ch);
        // //echo "INFO:<pre>"; print_r($info); echo "</pre><hr>";
        // curl_close($ch);
        // echo "<table width=80% border=0 align=center cellpadding=3 cellspacing=2>";
        //     echo "<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'>";
        //         echo "<td>[clientId]</td><td>$res_decode[clientId]</td>";
        //     echo "</tr>";
        //     echo "<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'>";
        //         echo "<td>[name]</td><td>$res_decode[name]</td>";
        //     echo "</tr>";
        //     echo "<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'>";
        //         echo "<td valign=top>[accounts]</td><td>";
        //             echo "<table width=80% border=0 align=center cellpadding=3 cellspacing=2>";
        //             foreach ($res_decode["accounts"] as $account) {
        //                 echo
        //                     "<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'><td>[sendId]</td>"
        //                         . "<td>$account[sendId]".($res_decode["clientId"] == $account["sendId"]?" <font color=green size=-1>[clientId]</font>":"")."</td>"
        //                     ."<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'><td>[id]</td><td>$account[id]</td>"
        //                     ."<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'><td>[iban]</td><td>$account[iban] "
        //                         . "<font color=gray size=-1>[type: $account[type]]</font></td>"
        //                     ."<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR1_VALUE:$COLOR2_VALUE)."'><td>[balance]</td>"
        //                         . "<td>".((floatval($account["balance"]) - floatval($account["creditLimit"]))/100)." "
        //                         . "<font color=gray size=-1>("
        //                         . "<font title='[currencyCode]'>$account[currencyCode]</font> | "
        //                         . "<font title='[cashbackType]'>$account[cashbackType]</font> | "
        //                         . "<font title='[creditLimit] -- Кредитный лимит'>".(floatval($account["creditLimit"])/100)."</font>)"
        //                         . "</font></td>"
        //                     ."";
        //                     foreach ($account["maskedPan"] as $pan) {
        //                         echo "<tr bgcolor='".(is_odd($I_COLOR_STEP++)?$COLOR4_VALUE:$COLOR5_VALUE)."'><td>[maskedPan]</td><td>$pan</td>";
        //                     }
        //             }
        //             echo "</table>";
        //         echo "</td>";
        //     echo "</tr>";
        // echo "</table>";




        // $headers = array
        // (
        //     //'Accept: text/xml,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
        //     //'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
        //     //'Accept-Encoding: deflate',
        //     //'Accept-Charset: utf-8;q=0.7,*;q=0.7',
        //     //'Content-type: text/xml;charset=UTF-8',
        //     'Content-type: application/json;charset=UTF-8',
        //     'X-Token: '.$card_token
        // );
        // //echo "<pre>headers: "; var_dump($headers); echo "</pre><hr>";

        // $ch = curl_init($request_url);
        // //echo "<pre>ch: "; var_dump($ch); echo "</pre><hr>";
        // curl_setopt($ch, CURLOPT_POST,              0); //Использовать метод POST
        // curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers);
        // //curl_setopt($ch, CURLOPT_POSTFIELDS,      $request);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1); //
        // curl_setopt($ch, CURLOPT_HEADER,            0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1);

        // $res = curl_exec($ch);
        // //echo "res:<pre>"; print_r($res); echo "</pre><hr>";
        // $res_decode =  json_decode($res, true);
        // //echo "res(decode):<pre>"; print_r($res_decode); echo "</pre><hr>";
        // //echo "res:<pre>".print_r(htmlentities(str_replace("><", ">\n<", $res)), true)."</pre><hr>";
        // $info=curl_getinfo($ch);
        // //echo "info:<pre>"; print_r($info); echo "</pre><hr>";
        // //echo "error: ".(curl_errno($ch)==0?"<font color=green>Ok</font>":"<font color=red>ERROR</font>")."<br>\n";
        // //echo "error:<pre>".print_r(curl_error($ch), true)."</pre><hr>";
        // curl_close($ch);
        // //echo "error:<pre>".print_r(htmlentities(str_replace("><", ">\n<", $info)), true)."</pre><hr>";
        // //echo "error:<pre>".print_r($info, true)."</pre><hr>";



        // $statements = json_decode($res, true);
        // //echo "<pre>statements:". print_r($statements, true)."<hr>";
        // echo "========================end========================<br><br>\n";
        

        View::setMeta(__('Получение данных карты Монобанка'));

        $this->setVariables([
            'ppp' => $ppp,
            'data' => [],
        ]);



    }



    function p24accUpdate(array &$data) {

        debug($data, '$data', die:1);

        $model = new AbonModel();

        /**
         * Выполнение действий:
         * привязка платежей к абонентам и добавление шаблонов
         */

        $t = array();
        foreach ($data as &$pay) {

            if  (
                    !isset($pay['to_edit']) && 
                    !isset($pay['to_billing']) && 
                    !isset($pay['template_add']) 
                ) 
            { continue; }
            
            if  ( isset($pay['to_edit']) && ($pay['to_edit'] == 1) ) { 
                if (Bank::pay_add(
                            $pay['abon_id'],        //int     Абонент, на которого зачисляется поалёж
                            $pay['pay_fakt'],       //float   Фактическая сумма пришедшая на р/с
                            $pay['pay'],            //float   Сумма платежа вносимая на ЛС
                            date("Y-m-d H:i:s", $pay['pay_date']),       //str     Дата платежа
                            $pay['pay_bank_no'],    //tiny    Банковский номер операции
                            $pay['pay_type_id'],    //int     ИД Типа платежа
                            $pay['pay_ppp_id'],     //int     Изменить Изменить
                            $pay['description']     //text    Краткое описание платежа
                        )) 
                {
                    MsgQueue::msg(MsgType::INFO, "Платёж ID=".$pay['id']." обновлён в биллинге.");
                    $pay['add_status'] = self::DO_STATUS_OK;
                } else {
                    MsgQueue::msg(MsgType::ERROR, "ОШИБКА: Платёж ID=".$pay['id']." не обновлён в биллинге!");
                    $pay['add_status'] = self::DO_STATUS_ERROR;
                }
            }



            //echo "<pre>".print_r($pay, true)."</pre>";
            // $t[] = [
            //     "abon_id"       => (isset($pay['abon_id'])      ? $pay['abon_id']       :"!abon_id"),
            //     "pay_fakt"      => (isset($pay['pay_fakt'])     ? $pay['pay_fakt']      :"!pay_fakt"),
            //     "pay"           => (isset($pay['pay'])          ? $pay['pay']           :"!pay"),
            //     "pay_date"      => (isset($pay['pay_date'])     ? $pay['pay_date']      :"!pay_date"),
            //     "pay_bank_no"   => (isset($pay['pay_bank_no'])  ? $pay['pay_bank_no']   :"!pay_bank_no"),
            //     "pay_type_id"   => (isset($pay['pay_type_id'])  ? $pay['pay_type_id']   :"!pay_type_id"),
            //     "pay_ppp_id"    => (isset($pay['pay_ppp_id'])   ? $pay['pay_ppp_id']    :"!pay_ppp_id"),
            //     "description"   => (isset($pay['description'])  ? $pay['description']   :"!description")
            //                         .((isset($pay['template_add']) and ($pay['template_add']==='on'))
            //                             ?"<br>+ шаблон &laquo;".$pay['template_text']."&raquo;&nbsp;"
            //                                 .(Bank::template_add($pay['pay_ppp_id'], $pay['template_aid'], $pay['template_text'])
            //                                     ?"<font color=green>Ok</font>"
            //                                     :"<font color=red>ERROR</font>"
            //                                 )
            //                             :""
            //                         ),
            //     "act" => "ADD:"
            //         .((isset($pay['to_billing']) AND ($pay['to_billing'] === 'on'))
            //             ?(pay_add(
            //                 $pay['abon_id'],        //int     Абонент, на которого зачисляется поалёж
            //                 $pay['pay_fakt'],       //float   Фактическая сумма пришедшая на р/с
            //                 $pay['pay'],            //float   Сумма платежа вносимая на ЛС
            //                 date("Y-m-d H:i:s", $pay['pay_date']),       //str     Дата платежа
            //                 $pay['pay_bank_no'],    //tiny    Банковский номер операции
            //                 $pay['pay_type_id'],    //int     ИД Типа платежа
            //                 $pay['pay_ppp_id'],     //int     Изменить Изменить
            //                 $pay['description']     //text    Краткое описание платежа
            //                 )
            //                 ?"<font color=green>Ok</font>"
            //                 :"<font color=red>ERROR</font>"
            //             )
            //             :"-"
            //         )
            //         ."<br>"
            //         ."EDIT:"
            //         .((isset($pay['to_edit']) AND ($pay['to_edit'] === 'on'))
            //             ?(pay_update(
            //                 $pay['id'],             //int
            //                 $pay['abon_id'],        //int     Абонент, на которого зачисляется поалёж
            //                 $pay['pay_fakt'],       //float   Фактическая сумма пришедшая на р/с
            //                 $pay['pay'],            //float   Сумма платежа вносимая на ЛС
            //                 date("Y-m-d H:i:s", $pay['pay_date']),       //str     Дата платежа
            //                 $pay['pay_bank_no'],    //tiny    Банковский номер операции
            //                 $pay['pay_type_id'],    //int     ИД Типа платежа
            //                 $pay['pay_ppp_id'],     //int     Изменить Изменить
            //                 $pay['description']     //text    Краткое описание платежа
            //                 )
            //                 ?"<font color=green>Ok</font>"
            //                 :"<font color=red>ERROR</font>"
            //             )
            //             :"-"
            //         ),
            // ];

        }

        /**
         * Вывод таблицы действий
         */
        $t_attributes = "width=80% border=0 align=center cellpadding=3 cellspacing=2";
        $t_col_titles = ["abon_id", "pay_fakt", "pay", "pay_date", "pay_bank_no", "pay_type_id", "pay_ppp_id",  "description", "act"];
        echo get_html_table($t,
                table_attributes: $t_attributes,
                col_titles: $t_col_titles);
        

    }



    function p24accAction() {

        if (!App::isAuth()) { redirect('/'); }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
            redirect();
        }

        if(isset($_POST[Bank::POST_REC]) && is_array($_POST[Bank::POST_REC])) {
            $post_rec = $_POST[Bank::POST_REC];
            $this->p24accUpdate($post_rec);
        }

        $model = new AbonModel();

        if  (
                empty($this->route[F_ALIAS]) ||
                !$model->validate_ppp((int)$this->route[F_ALIAS])
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ID не верен'));
            redirect();
        }

        $ppp = $model->get_ppp((int)$this->route[F_ALIAS]);

        if (!is_supported_api($ppp, Bank::API_TYPE_P24_ACC)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ППП не поддерживает этот API'));
            redirect();
        }

        MsgQueue::msg(MsgType::INFO_AUTO, "====Получение данных====");

        /**
         * Чтение шаблонов распознавания абонентов
         */
        $templates   = $model->get_abons_templates($ppp[Ppp::F_ID]);
        MsgQueue::msg(MsgType::INFO_AUTO, "Размер templates: ".count($templates)." записей");

        /**
         * Тип пополнеиня ЛС для внесения в билинг.
         * 1 - Pay::TYPE_MONEY Денежное пополнение ЛС | 2 - Корректировка ЛС | 3 - Начисление за услугу
         */
        $pay_type_id = Pay::TYPE_MONEY;

        /**
         * Вычисляем даты интервала для получения транзакции
         */
        $last_pay   =   $model->get_last_pay_on_ppp($ppp[Ppp::F_ID]);
        $date1_str  =   (isset($_GET[Bank::F_GET_DATE_START])
                            ? $_GET[Bank::F_GET_DATE_START]
                            : date('d-m-Y', (!is_null($last_pay) ? $last_pay[Pay::F_DATE] : TODAY()) - App::get_config('bank_date_interval')));
        $date2_str  =   (isset($_GET[Bank::F_GET_DATE_END])
                            ? $_GET[Bank::F_GET_DATE_END]
                            : date('d-m-Y'));
        MsgQueue::msg(MsgType::INFO_AUTO, "Даты выборки: [".$date1_str."] - [".$date2_str."]");

        /**
         * Получение транзакций из банка
         */
        $transactions = Bank::p24acc_get_transactions($ppp, $date1_str, $date2_str, trantype: Bank::TRANSACTION_TYPE_C);
        sort_array_by_field($transactions, P24acc::F_DATE_TIME_DAT_OD_TIM_P, false);

        $trns = [];
        foreach ($transactions as $index => $t_row) {
            $found_pays = Bank::p24acc_search_payments_on_billing($t_row, $ppp[Ppp::F_ID], $templates);

            $comission = (str_contains_array($t_row[P24acc::F_OSND], App::get_config('bank_comission_text'))
                            ?   App::get_config('bank_comission_value')
                            :   (str_contains_array($t_row[P24acc::F_OSND], App::get_config('bank_liqpay_ident_text'))
                                    ? Bank::calc_comission($save_pay[Pay::F_PAY_FAKT], $ppp['api_liqpay_return_comission'])
                                    : 0
                                )
                            );
            $save_pay[Pay::F_PAY_FAKT]    = floatval($t_row[P24acc::F_SUM]);
            $save_pay[Pay::F_PAY_ACNT]    = ($comission > 0 ? round(floatval($t_row[P24acc::F_SUM]) + $comission) : floatval($t_row[P24acc::F_SUM]));
            $save_pay[Pay::F_DATE]        = strtotime($t_row[P24acc::F_DATE_TIME_DAT_OD_TIM_P]);
            $save_pay[Pay::F_BANK_NO]     = $t_row[P24acc::F_ID];
            $save_pay[Pay::F_TYPE_ID]     = $pay_type_id;
            $save_pay[Pay::F_PPP_ID]      = $ppp[Ppp::F_ID];
            $save_pay[Pay::F_DESCRIPTION] = $t_row[P24acc::F_OSND];

            $trns[] = [
                'transaction' => $t_row,
                'found_pays' => $found_pays,
                'save_pay' => $save_pay,
            ];
        }

        $unknowns = $model->get_unknown_payments($ppp[Ppp::F_ID]);

        $this->setVariables([
            'date1' => $date1_str,
            'date2' => $date2_str,
            'ppp' => $ppp,
            'transactions' => $trns,
            'unknowns' => $unknowns,
        ]);

        View::setMeta(title: __('Контроль и ручное распределение платежей'));


    }



    function indexAction() {

        if (!App::isAuth()) { redirect('/'); }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
            redirect();
        }

        $model = new AbonModel();

        $ppp_list = [];
        foreach (Bank::API_MANUAL_LIST as $api_type) {
            $ppp_list[$api_type] = $model->get_ppp_list_by_api($api_type);
        }


        $this->setVariables([
            'ppp_list' => $ppp_list,
        ]);

        View::setMeta(title: __('Список ППП для контроля и ручного распределения платежей'));

    }




}