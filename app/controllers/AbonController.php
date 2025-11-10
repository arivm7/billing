<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AbonController.php
 *  Path    : app/controllers/AbonController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */
declare(strict_types=1);

namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Pagination;
use DutyWarn;
use config\Auth;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Contacts;
use config\tables\Firm;
use config\tables\Module;
use config\tables\Price;
use config\tables\Notify;
use config\tables\TP;
use config\tables\TSUserFirm;
use config\tables\PA;
use config\tables\User;
use Valitron\Validator;
use config\SessionFields;

require_once DIR_LIBS . '/datetime_functions.php';
require_once DIR_LIBS . '/compare_functions.php';



/**
 * Description of AbonController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AbonController extends AppBaseController {

    

    /**
     * Возвращает статус для предупреждения абонента
     * в завиимости от оставшихся предоплаченных дней
     * @param array $data -- Ассоциативный масив записи абонента
     * @return DutyWarn
     */
    public static function get_warn_status(array &$data): DutyWarn {
        switch (true) {
            case (is_null($data[AbonRest::F_PREPAYED])):
                return DutyWarn::ON_PAUSE;
                // break;
            case ($data[AbonRest::F_PREPAYED] > $data[Abon::F_DUTY_MAX_WARN]):
                return DutyWarn::NORMAL;
                // break;
            case (($data[AbonRest::F_PREPAYED] <= $data[Abon::F_DUTY_MAX_WARN]) && ($data[AbonRest::F_PREPAYED] > $data[Abon::F_DUTY_MAX_OFF])):
                return DutyWarn::WARN;
                // break;
            case ($data[AbonRest::F_PREPAYED] <= $data[Abon::F_DUTY_MAX_OFF]):
                return DutyWarn::NEED_OFF;
                // break;
            default:
                return DutyWarn::NA;
                // break;
        }
    }



    public const attribute_warning = [
        // bootstrap 5
        // "<span class='badge rounded-pill text-bg-primary'>Primary</span>"
        // "<span class='badge rounded-pill text-bg-secondary'>Secondary</span>"
        // "<span class='badge rounded-pill text-bg-success'>Success</span>"
        // "<span class='badge rounded-pill text-bg-danger'>Danger</span>"
        // "<span class='badge rounded-pill text-bg-warning'>Warning</span>"
        // "<span class='badge rounded-pill text-bg-info'>Info</span>"
        // "<span class='badge rounded-pill text-bg-light'>Light</span>"
        // "<span class='badge rounded-pill text-bg-dark'>Dark</span>"
        DutyWarn::NA->name       => "class='badge rounded-pill text-bg-light'",
        DutyWarn::ON_PAUSE->name => "class='badge rounded-pill text-bg-secondary'",
        DutyWarn::NORMAL->name   => "class='badge rounded-pill text-bg-success'",
        DutyWarn::WARN->name     => "class='badge rounded-pill text-bg-warning'",
        DutyWarn::NEED_OFF->name => "class='badge rounded-pill text-bg-danger'",
        DutyWarn::INFO->name     => "class='badge rounded-pill text-bg-info'",
    ];


    
    public static function get_html_actions(array &$data): string {
        return  '[&nbsp;+PAY&nbsp;]&nbsp; &nbsp;[&nbsp;PAYS&nbsp;]&nbsp; &nbsp;[&nbsp;SMS&nbsp;]&nbsp; &nbsp;[&nbsp;СФ2&nbsp;]';
    }



    public static function get_html_edges(array &$data): string {
        ob_start();
        require DIR_INC . '/abon_edges.php';
        $str = ob_get_clean();
        return  $str;
    }



    public static function get_html_tp_list_with_abon(int $abon_id): string {
        $model = new AbonModel();
        $tp_list = $model->get_tp_list_with_abon($abon_id, closed: 0);
        $html = "<nobr>";
        foreach ($tp_list as &$tp) {
            $html .= $model->url_tp_mik (tp: $tp, icon_width: 16, icon_height: 16, show_gray: true);
            $html .= "&nbsp;";
            $html .= $model->url_tp_form(tp: $tp, has_img: true);
            $html .= "&nbsp;";
            $html .= "<a href=?".make_get_params(['tp'=>$tp[TP::F_ID]])." title='Показать абонентов только по этой ТП '>{$tp[TP::F_TITLE]}</a>";
            $html .= "<br>";
        }
        $html = rtrim($html, "<br>");
        $html .= "</nobr>";
        return $html;
    }


    public static function get_html_info(array &$data): string {
        $model = new AbonModel();
        $U1 = $model->get_user($data[Abon::F_USER_ID]);
        return
            "<table>"
            . "<tr><td valign=top>". $model->url_address_on_map_search($data[Abon::F_ADDRESS])."</td><td>{$data[Abon::F_ADDRESS]}</td></tr>"
            . "<tr><td valign=top>:::</td><td>{$U1[User::F_NAME_SHORT]} | {$U1[User::F_NAME_FULL]}</td></tr>"
            . "<tr><td valign=top>".($U1['do_send_sms']?CHECK1:CHECK0)."</td><td>{$U1['phone_main']}</td></tr>"
            . ((mb_strlen($U1['mail_main']) > 0) || ($U1['do_send_mail'])
                    ? "<tr><td valign=top>".get_html_CHECK( boolval($U1['do_send_mail']))."</td><td>{$U1['mail_main']}</td></tr>"
                    : "")
            . ((mb_strlen($U1['address_invoice']) > 0) || ($U1['do_send_invoice'])
                    ? "<tr><td valign=top>".get_html_CHECK( boolval($U1['do_send_invoice']))."</td><td>{$U1['address_invoice']}</td></tr>"
                    : "")
            . "</table>";

    }



    public static function get_html_url_aid_uid(array &$abon): string {
        $model = new AbonModel();
        return
            '<table width=100%>'
            . '<tr>'
                . '<td align=left><span class="text-secondary small">U:</span></td>'
                . '<td align=right>'.$model->url_user_form($abon[Abon::F_USER_ID]).'</td>'
            . '</tr>'
            . '<tr>'
                . '<td align=left><span class="text-secondary small">A:</span></td>'
                . '<td align=right>'.$model->url_abon_form($abon[Abon::F_ID]).'</td>'
            . '</tr>'
            . '</table>';
    }



    function indexAction() {
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Авторизуйтесь, пожалуйста'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_ABON)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('У вас нет прав для работы с абонентами'));
            redirect();
        }

        $model = new AbonModel();

        /**
         * Фильтры параметра запроса списка абонентов
         */
        $filters = [
            'tp'       => 0,
            'per_page' => 20,
            'is_payer' => 1,
            'order_by' => "(".AbonRest::TABLE.".".AbonRest::F_SUM_PAY." - ".AbonRest::TABLE.".".AbonRest::F_SUM_COST.") ASC"
        ];

        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        if (isset($_GET['tp']) && is_numeric($_GET['tp'])) {
            $filters['tp'] = intval($_GET['tp']);
        }

        /**
         * Список ТП прикреплённых к авторизованному пользователю
         * чтобы получить абонентов только со своих ТП
         */
        if ($filters['tp'] === 0) {
            $tp_list = $model->get_my_tp_list();
        } else {
            if ($model->validate_id(TP::TABLE, intval($filters['tp']), TP::F_ID)) {
                $tp_list = [
                    $model->get_tp(intval($filters['tp'])),
                ];
            } else {
                $tp_list = [];
            }
        }
        

        /**
         * Список ID абонентов с полученных выше техплощадок
         */
        $abon_id_list = $model->get_abons_id_by_tp(tp_list: $tp_list, field_id: TP::F_ID);

        /**
         * Есть ли список абонентов
         */
        if (!empty($abon_id_list)) {
            /**
             * Список абонентов есть
             */

            /**
             * Запрос полной выборки абонентов и объединение с таблицей остатков (rest)
             */
            $sql = "SELECT "
                    . "".Abon::TABLE.".*, "
                    . "".AbonRest::TABLE.".".AbonRest::F_SUM_PAY.", "
                    . "".AbonRest::TABLE.".".AbonRest::F_SUM_COST.", "
                    . "".AbonRest::TABLE.".".AbonRest::F_SUM_PPMA.", "
                    . "".AbonRest::TABLE.".".AbonRest::F_SUM_PPDA." "
                    . "FROM ".Abon::TABLE." "
                    . "JOIN ".AbonRest::TABLE." ON ".Abon::TABLE.".".Abon::F_ID." = ".AbonRest::TABLE.".".AbonRest::F_ABON_ID." "
                    . "WHERE "
                        . "(".Abon::TABLE.".".Abon::F_ID." IN (".implode(',', $abon_id_list).")) "
                        . "and "
                        . "".(isset($filters['is_payer']) && !is_null($filters['is_payer']) ? "(abons.is_payer = {$filters['is_payer']})" : "1")." "
                    . "ORDER BY {$filters['order_by']}";
            // debug($sql, '$sql');
            $pager = new Pagination(per_page: $filters['per_page'], sql: $sql);
            $rows = $pager->get_rows();

            $tp_col_title = ($filters['tp'] === 0
                ? "Все ТП"
                : "<nobr>" . $tp_list[0][TP::F_TITLE] . " | <a href=?".make_get_params(['tp'=>0])." title='Убрать фильтр'>[X]</a></nobr>"
            );

            foreach ($rows as $abon) {

                update_rest_fields($abon);

                $t[] = [
                    'act'     => self::get_html_actions($abon),
                    'uid/aid' => self::get_html_url_aid_uid($abon),
                    'info'    => self::get_html_info($abon),
                    'rest'    => self::get_html_edges($abon),
                    $tp_col_title => self::get_html_tp_list_with_abon($abon[Abon::F_ID]),
                ];
            }
            
        } else {
            /**
             * Списка абонентов нет
             */
            $pager = null;
            $t = [];
        }


        $this->setVariables([
            'pager' => $pager,
            't'     => $t,
        ]);

        View::setMeta(
            title: __("List of subscribers by technical platforms | Список абонентов по техническим площадкам | Список абонентів за технічними майданчиками"),
            descr: __("The list of subscribers by technical sites attached to the current authorized user | Список абонентов по техплощадкам, прикреплённым к текущему авторизованному пользователю | Список абонентів по техмайданчиках, прикріплених до поточного авторизованого користувача"),
        );

    }



    function listAction() {

        if (!App::$auth->isAuth)
        {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_ABON)) {
            MsgQueue::msg(MsgType::ERROR, __('You do not have permission to work with subscribers | У вас нет прав для работы с абонентами | У вас немає прав для роботи з абонентами'));
            redirect();
        }


        define('ROUTE_NAME', 'abon/list');

        $model = new AbonModel();


        $user_id = $_SESSION[User::SESSION_USER_REC][User::F_ID];
        //debug("_GET: ", $_GET, "", false);



        /**
         * Константы комманд для управляющих запросов
         */
        define("CMD_SORT", "sort_by");              // url комманда сортировки
        define("CMD_SHOW_TP", "show_tp");              // url комманда Показать только эту ТП
        define("CMD_SHOW_AB_ACT", "show_ab_act");          // url комманда Показать активных абонентов
        define("CMD_SHOW_AB_OFF", "show_ab_off");          // url комманда Показать врекменно отключенных абонентов, на паузе.
        define("CMD_SHOW_AB_PAY", "show_ab_pay");          // url комманда Показать абонентов плательщиков, НЕ отключенных на всегда.
        define("CMD_SHOW_DO_SEND_SMS", "show_do_send_sms");     // url комманда Показать Абонентов do_send_sms=1
        define("CMD_SHOW_DO_SEND_MAIL", "show_do_send_mail");    // url комманда Показать Абонентов do_send_sms=1
        define("CMD_SHOW_DO_SEND_INVOICE", "show_do_send_invoice"); // url комманда Показать Абонентов do_send_sms=1


        /**
         * Параметры сортировки
         */
        define("BY_ADDRESS_ASC", "by_address_asc");
        define("BY_ADDRESS_DESC", "by_address_desc");
        define("BY_BALANCE_ASC", "by_balance_asc");
        define("BY_BALANCE_DESC", "by_balance_desc");
        define("BY_PREPAYED_ASC", "by_prepayed_asc");
        define("BY_PREPAYED_DESC", "by_prepayed_desc");
        define("BY_PP30A_ASC", "by_pp30a_asc");
        define("BY_PP30A_DESC", "by_pp30a_desc");

        /**
         * Считывание и запоминание параметров отображения
         */
        $show_tp = (isset($_GET[CMD_SHOW_TP]) && $model->validate_id(TP::TABLE, $_GET[CMD_SHOW_TP])
                        ? $_GET[CMD_SHOW_TP]
                        : null
                    ); // echo "show_tp: $show_tp<br>";
        $show_tp_list = (is_null($show_tp) ? null : [$show_tp]);
        $sort_by = (isset($_GET[CMD_SORT]) ? $_GET[CMD_SORT] : null); //echo "sort_by: $sort_by<br>";
        $show_ab_act = (isset($_GET[CMD_SHOW_AB_ACT]) ? intval($_GET[CMD_SHOW_AB_ACT]) : 1); //echo "show_ab_act: $show_ab_act<br>";
        $show_ab_off = (isset($_GET[CMD_SHOW_AB_OFF]) ? intval($_GET[CMD_SHOW_AB_OFF]) : 1); // echo "show_ab_off: $show_ab_off<br>";
        $show_ab_pay = (isset($_GET[CMD_SHOW_AB_PAY]) ? intval($_GET[CMD_SHOW_AB_PAY]) : 1); // echo "show_ab_pay: $show_ab_pay | ".intval(!$show_ab_pay)."<br>";
        $show_do_send_sms = (isset($_GET[CMD_SHOW_DO_SEND_SMS]) ? $_GET[CMD_SHOW_DO_SEND_SMS] : null); // echo "show_do_send_sms: $show_do_send_sms<br>";
        $show_do_send_mail = (isset($_GET[CMD_SHOW_DO_SEND_MAIL]) ? $_GET[CMD_SHOW_DO_SEND_MAIL] : null); // echo "show_do_send_mail: $show_do_send_mail<br>";
        $show_do_send_invoice = (isset($_GET[CMD_SHOW_DO_SEND_INVOICE]) ? $_GET[CMD_SHOW_DO_SEND_INVOICE] : null); // echo "show_do_send_invoice: $show_do_send_invoice<hr>";



        $FLAG_URL_CURRENT           = intval("00000000000000000000000000000001", 2); //echo $FLAG_URL_CURRENT."<br>";
        $FLAG_URL_SORT              = intval("00000000000000000000000000000010", 2); //echo $FLAG_URL_SORT."<br>";
        $FLAG_URL_TP                = intval("00000000000000000000000000000100", 2); //echo $FLAG_URL_TP."<br>";
        $FLAG_URL_SHOW_AB_ACT       = intval("00000000000000000000000000001000", 2); //echo $FLAG_URL_SHOW_AB_ACT."<br>";
        $FLAG_URL_SHOW_AB_OFF       = intval("00000000000000000000000000010000", 2); //echo $FLAG_URL_SHOW_AB_OFF."<br>";
        $FLAG_URL_SHOW_AB_PAY       = intval("00000000000000000000000000100000", 2); //echo $FLAG_URL_SHOW_AB_PAY."<br>";
        $FLAG_URL_DO_SEND_SMS       = intval("00000000000000000000000001000000", 2); //echo $FLAG_URL_DO_SEND_SMS."<br>";
        $FLAG_URL_DO_SEND_MAIL      = intval("00000000000000000000000010000000", 2); //echo $FLAG_URL_DO_SEND_MAIL."<br>";
        $FLAG_URL_DO_SEND_INVOICE   = intval("00000000000000000000000100000000", 2); //echo $FLAG_URL_DO_SEND_INVOICE."<br>";

        $FLAG_ALL                   = intval("11111111111111111111111111111111", 2);

        $FLAG_URL_SHOW_AB_ALL = $FLAG_URL_SHOW_AB_ACT & $FLAG_URL_SHOW_AB_OFF & $FLAG_URL_SHOW_AB_PAY;
        $FLAG_URL_DO_SEND_ALL = $FLAG_URL_DO_SEND_SMS & $FLAG_URL_DO_SEND_MAIL & $FLAG_URL_DO_SEND_INVOICE;

        function flag_off(int $word, int $flag): int {
            return ($word & ~$flag);
        }

        function make_url(int $use = 1 /* $FLAG_URL_CURRENT */, int $set_1 = 0, int $set_0 = 0, int $flag_field = 0, string $value = "") {
            global $FLAG_URL_CURRENT,
            $FLAG_URL_SORT, $sort_by,
            $FLAG_URL_TP, $show_tp,
            $FLAG_URL_SHOW_AB_ACT, $show_ab_act,
            $FLAG_URL_SHOW_AB_OFF, $show_ab_off,
            $FLAG_URL_SHOW_AB_PAY, $show_ab_pay,
            $FLAG_URL_DO_SEND_SMS, $show_do_send_sms,
            $FLAG_URL_DO_SEND_MAIL, $show_do_send_mail,
            $FLAG_URL_DO_SEND_INVOICE, $show_do_send_invoice;

            $url = "";
            //echo "use:$use | $set1 | $set0 <hr>";
            if (($use & $FLAG_URL_CURRENT) > 0) {
                $url .= $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'];
            }
            $url .= "?make";

            if ($use > 0) {
                if (($use & $FLAG_URL_SORT) > 0) {
                    $url .= (is_null($sort_by) ? "" : "&" . CMD_SORT . "=" . $sort_by);
                }
                if (($use & $FLAG_URL_TP) > 0) {
                    $url .= (is_null($show_tp) ? "" : "&" . CMD_SHOW_TP . "=" . $show_tp);
                }
                if (($use & $FLAG_URL_SHOW_AB_ACT) > 0) {
                    $url .= (($show_ab_act == 1) ? "" : "&" . CMD_SHOW_AB_ACT . "=" . $show_ab_act);
                }
                if (($use & $FLAG_URL_SHOW_AB_OFF) > 0) {
                    $url .= (($show_ab_off == 1) ? "" : "&" . CMD_SHOW_AB_OFF . "=" . $show_ab_off);
                }
                if (($use & $FLAG_URL_SHOW_AB_PAY) > 0) {
                    $url .= (($show_ab_pay == 1) ? "" : "&" . CMD_SHOW_AB_PAY . "=" . $show_ab_pay);
                }
                if (($use & $FLAG_URL_DO_SEND_SMS) > 0) {
                    $url .= (is_null($show_do_send_sms) ? "" : "&" . CMD_SHOW_DO_SEND_SMS . "=" . $show_do_send_sms);
                }
                if (($use & $FLAG_URL_DO_SEND_MAIL) > 0) {
                    $url .= (is_null($show_do_send_mail) ? "" : "&" . CMD_SHOW_DO_SEND_MAIL . "=" . $show_do_send_mail);
                }
                if (($use & $FLAG_URL_DO_SEND_INVOICE) > 0) {
                    $url .= (is_null($show_do_send_invoice) ? "" : "&" . CMD_SHOW_DO_SEND_INVOICE . "=" . $show_do_send_invoice);
                }
            }

            if ($set_1 > 0) {
                if (($set_1 & $FLAG_URL_SORT) > 0)              { $url .= "&" . CMD_SORT . "=1"; }
                if (($set_1 & $FLAG_URL_TP) > 0)                { $url .= "&" . CMD_SHOW_TP . "=1"; }
                if (($set_1 & $FLAG_URL_SHOW_AB_ACT) > 0)       { $url .= "&" . CMD_SHOW_AB_ACT . "=1"; }
                if (($set_1 & $FLAG_URL_SHOW_AB_OFF) > 0)       { $url .= "&" . CMD_SHOW_AB_OFF . "=1"; }
                if (($set_1 & $FLAG_URL_SHOW_AB_PAY) > 0)       { $url .= "&" . CMD_SHOW_AB_PAY . "=1"; }
                if (($set_1 & $FLAG_URL_DO_SEND_SMS) > 0)       { $url .= "&" . CMD_SHOW_DO_SEND_SMS . "=1"; }
                if (($set_1 & $FLAG_URL_DO_SEND_MAIL) > 0)      { $url .= "&" . CMD_SHOW_DO_SEND_MAIL . "=1"; }
                if (($set_1 & $FLAG_URL_DO_SEND_INVOICE) > 0)   { $url .= "&" . CMD_SHOW_DO_SEND_INVOICE . "=1"; }
            }


            if ($set_0 > 0) {
                if (($set_0 & $FLAG_URL_SORT) > 0)              { $url .= "&" . CMD_SORT . "=0"; }
                if (($set_0 & $FLAG_URL_TP) > 0)                { $url .= "&" . CMD_SHOW_TP . "=0"; }
                if (($set_0 & $FLAG_URL_SHOW_AB_ACT) > 0)       { $url .= "&" . CMD_SHOW_AB_ACT . "=0"; }
                if (($set_0 & $FLAG_URL_SHOW_AB_OFF) > 0)       { $url .= "&" . CMD_SHOW_AB_OFF . "=0"; }
                if (($set_0 & $FLAG_URL_SHOW_AB_PAY) > 0)       { $url .= "&" . CMD_SHOW_AB_PAY . "=0"; }
                if (($set_0 & $FLAG_URL_DO_SEND_SMS) > 0)       { $url .= "&" . CMD_SHOW_DO_SEND_SMS . "=0"; }
                if (($set_0 & $FLAG_URL_DO_SEND_MAIL) > 0)      { $url .= "&" . CMD_SHOW_DO_SEND_MAIL . "=0"; }
                if (($set_0 & $FLAG_URL_DO_SEND_INVOICE) > 0)   { $url .= "&" . CMD_SHOW_DO_SEND_INVOICE . "=0"; }
            }

            if ($flag_field > 0) {
                if (($flag_field & $FLAG_URL_SORT) > 0)             { $url .= "&" . CMD_SORT . "=" . $value; }
                if (($flag_field & $FLAG_URL_TP) > 0)               { $url .= "&" . CMD_SHOW_TP . "=" . $value; }
                if (($flag_field & $FLAG_URL_SHOW_AB_ACT) > 0)      { $url .= "&" . CMD_SHOW_AB_ACT . "=" . $value; }
                if (($flag_field & $FLAG_URL_SHOW_AB_OFF) > 0)      { $url .= "&" . CMD_SHOW_AB_OFF . "=" . $value; }
                if (($flag_field & $FLAG_URL_SHOW_AB_PAY) > 0)      { $url .= "&" . CMD_SHOW_AB_PAY . "=" . $value; }
                if (($flag_field & $FLAG_URL_DO_SEND_SMS) > 0)      { $url .= "&" . CMD_SHOW_DO_SEND_SMS . "=" . $value; }
                if (($flag_field & $FLAG_URL_DO_SEND_MAIL) > 0)     { $url .= "&" . CMD_SHOW_DO_SEND_MAIL . "=" . $value; }
                if (($flag_field & $FLAG_URL_DO_SEND_INVOICE) > 0)  { $url .= "&" . CMD_SHOW_DO_SEND_INVOICE . "=" . $value; }
            }

            return str_replace("?make", "", str_replace("?make&", "?", $url));
        }

        /**
         * Ссылки для сортировок
         */
        $html_sort_name = ""
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_ADDRESS_ASC) . ">" . CH_TRIANGLE_UP . "</a>"
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_ADDRESS_DESC) . ">" . CH_TRIANGLE_DOWN . "</a>";
        $html_sort_balance = ""
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_BALANCE_ASC) . ">" . CH_TRIANGLE_UP . "</a>"
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_BALANCE_DESC) . ">" . CH_TRIANGLE_DOWN . "</a>";
        $html_sort_prepayed = ""
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_PREPAYED_ASC) . ">" . CH_TRIANGLE_UP . "</a>"
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_PREPAYED_DESC) . ">" . CH_TRIANGLE_DOWN . "</a>";
        $html_sort_pp30a = ""
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_PP30A_ASC) . ">" . CH_TRIANGLE_UP . "</a>"
                . "<a href=" . make_url(use: $FLAG_ALL & ~$FLAG_URL_SORT, flag_field: $FLAG_URL_SORT, value: BY_PP30A_DESC) . ">" . CH_TRIANGLE_DOWN . "</a>";

        /**
         * Считываем ТП привязанные к авторизованному пользователю
         * которые указаны в таблице связи ts_user_tp
         */
        $TP_LIST = indexing_arr($model->get_tp_list_by_uid($user_id, list_tp_id: $show_tp_list)); // , status: true (! если выбирать ли ТП только активные, то невозможно посмотреть абонентов на отклюенных ТП)
        //echo "(1) TP_LIST: <pre>". print_r($TP_LIST, true)."</pre><hr>";


        /**
         * Считываем ВСЕ прайсовые фрагменты prices_apply
         */
        $PA_LIST = indexing_arr($model->get_rows_by_field("prices_apply", "1", "1"));

        /**
         * Считываем ВСЕХ абонентов
         */
        $ABONS_LIST = indexing_arr($model->get_rows_by_where("abons"));

        /**
         * считываем ВСЕХ пользователей
         */
        $USERS_LIST = indexing_arr($model->get_rows_by_field("users"));

        //printf("Получено ТП: %d, PA: %d, Пользователей: %d, Абонентов: %d <br>\n", count($TP_LIST), count($PA_LIST), count($USERS_LIST), count($ABONS_LIST));
        //echo "Время выполнения ".microtime(true) - $time_start."<br>\n";



        /**
         * Добавляем записи PA к записям абонентов
         */
        foreach ($PA_LIST as $PA) {
            $ABONS_LIST[$PA['abon_id']]['PA'][$PA['id']] = $PA;
        }


        /**
         * Удаляем отключённых и чужих:
         * [-] абонентов не плательщиков: 'is_payer'] == 0;
         * [-] без прайсов: count($A['PA']) == 0;
         * [-] без активных прайсов на выбраннх ТП в $TP_LIST.
         * [ ] Проверяем на фильтры
         *      $show_ab_act
         *      $show_ab_off
         *      $show_do_send_sms
         *      $show_do_send_mail
         *      $show_do_send_invoice
         */
        foreach ($ABONS_LIST as $key => $A) {

            /**
             * Оставляем плательщиков абонентов (Фильтр $show_ab_pay)
             */
            if ($A['is_payer'] != $show_ab_pay) {
                unset($ABONS_LIST[$key]);
            } elseif
            (!isset($A['PA']) || (count($A['PA']) == 0)) {
                unset($ABONS_LIST[$key]);
            } else {
                /**
                 * оставляем абонента в списке если:
                 * [+] текущий прайсовый фрагмент в списке ТП
                 * [+] будущий прайсовый фрагмент в списке ТП
                 * [+] если нет активных прайсов, то последний отключенный прайс в списке ТП
                 * [ ] Проверяем на фильтры
                 *      $show_ab_act
                 *      $show_ab_off
                 *      $show_do_send_sms
                 *      $show_do_send_mail
                 *      $show_do_send_invoice
                 * [-] остальных абонентов удаляем из списка
                 */
                $last = $model->get_last_PA($A['id'], $A['PA']);
                $del = true;
                /**
                 * Оставляем активных абонентов (фильтр $show_ab_act)
                 */
                if ($show_ab_act == 1) {
                    foreach ($last['cur'] as $pa_id => $pa_one) {
                        if (isset($TP_LIST[$pa_one['net_router_id']])) {
                            $del = false;
                            break;
                        }
                    }
                    if ($del) {
                        foreach ($last['fut'] as $pa_id => $pa_one) {
                            if (isset($TP_LIST[$pa_one['net_router_id']])) {
                                $del = false;
                                break;
                            }
                        }
                    }
                }
                /**
                 * Оставляем отключенных абонентов (Фильтр $show_ab_off)
                 */
                if ($show_ab_off == 1) {
                    if (is_empty($last['cur']) && is_empty($last['fut']) && $del) {
                        foreach ($last['off'] as $pa_id => $pa_one) {
                            if (isset($TP_LIST[$pa_one['net_router_id']])) {
                                $del = false;
                                break;
                            }
                        }
                    }
                }

                /**
                 * Проверка фильтра СМС
                 */
                if (!$del && !is_null($show_do_send_sms)) {
                    $del = $USERS_LIST[$A['user_id']]['do_send_sms'] != $show_do_send_sms;
                }
                /**
                 * Проверка фильтра MAIL
                 */
                if (!$del && !is_null($show_do_send_mail)) {
                    $del = $USERS_LIST[$A['user_id']]['do_send_mail'] != $show_do_send_mail;
                }
                /**
                 * Проверка фильта INVOICE
                 */
                if (!$del && !is_null($show_do_send_invoice)) {
                    $del = $USERS_LIST[$A['user_id']]['do_send_invoice'] != $show_do_send_invoice;
                }
                if ($del) {
                    unset($ABONS_LIST[$key]);
                }
            }
        }
        //printf("После удаление отключенных и чужих абонентов осталось Абонентов: %d<br>\n", count($ABONS_LIST));
        //echo "Время выполнения ".microtime(true) - $time_start."<br>\n";
        if (count($ABONS_LIST) == 0) {
            echo "<h1>Абонентов нет<h1>";
            //echo "(2) TP_LIST: <pre>". print_r($TP_LIST, true)."</pre><hr>";
            echo "<a href=" . make_url(
                    use: $FLAG_ALL & ~$FLAG_URL_SHOW_AB_PAY,
                    flag_field: $FLAG_URL_SHOW_AB_PAY,
                    value: strval(intval(!$show_ab_pay))
            ) . " title='Показать плательщиков.'>Переключить плательщиков " . (!$show_ab_pay ? CHECK1 : CHECK0) . "</a><br><br>";
            echo "<a href=" . make_url(
                    use: $FLAG_URL_CURRENT | $FLAG_URL_TP
            ) . " title='Сбросить фильтры для ТП [" . $show_tp . "].'>Сбросить фильтры для ТП [" . $show_tp . "]</a>";
            exit;
        }



        /**
         * Формируем текстовую строку из id абонентов вида "(aid,aid,aid,aid)"
         * для запроса к базе и выборки полного списка платежей для этих абонентов.
         */
        $count_abons = 0;
        $A_str_list = "(";
        $first = true;
        foreach ($ABONS_LIST as $A) {
            $count_abons++;
            if ($first) {
                $A_str_list .= strval($A['id']);
                $first = false;
            } else {
                $A_str_list .= "," . strval($A['id']);
            }
        }
        $A_str_list .= ")";
        /**
         * Выборка полного списка платежей для указанного списка абонентов
         */
        $PAY_LIST = $model->get_rows_by_where("payments", "`abon_id` IN " . $A_str_list);
        //echo "Получено записей о платежах: ".count($PAY_LIST)."<br>\n";
        //echo "Время выполнения ".microtime(true) - $time_start."<br>\n";
        //echo "Прикрепляем платежи к абонентам<br>\n";
        foreach ($PAY_LIST as $pay) {
            $ABONS_LIST[$pay['abon_id']]['PAYMENTS'][] = $pay;
        }
        //echo "Время выполнения ".microtime(true) - $time_start."<br>\n";



        /**
         * Добавляем Абонентов к пользователям.
         */
        foreach ($ABONS_LIST as $A) {
            $USERS_LIST[$A['user_id']]['A'][$A['id']] = $A;
        }



        /**
         * Удаляем Пользователей оставшихся без абонентов
         */
        foreach ($USERS_LIST as $UID => $U) {
            if (!isset($U['A']) || (count($U['A']) == 0)) {
                unset($USERS_LIST[$UID]);
            }
        }
        //printf("После удаление пользователей без абонентов осталось Пользователей: %d<br>\n", count($USERS_LIST));
        //echo "Время выполнения ".microtime(true) - $time_start."<br>\n";
        //printf("Считаем для всех абонентов: COST_PA_SUM, PPMA, PPDA, TP, PAYS, PP30A, PP01A, BALANCE, PREPAYED... ");
        foreach ($USERS_LIST as &$U) {
            foreach ($U['A'] as &$A) {
                /**
                 * Все пополнения, вклчая платежи, начисления и компенсации
                 */
                $A['PAYS'] = $model->get_sum_pays_by_abon($A);

                /**
                 * Обновляет поле записи абонента, добавляя в него следющие пола:
                 * float $A['COST_PA_SUM'] -- сумма стоимости всех прайсовых франгментов;
                 * float $A['PPMA']        -- Активный прайс за месяц (Price per Month Active);
                 * float $A['PPDA']        -- Активный прайс за сутки (Price per Day Active);
                 */
                $model->update_abon_sum_edges_PA(A: $A, tp_id: $show_tp);

                /**
                 * Обновляет поле записи абонента, добавляя в него следющие пола:
                 * array $A['TP'] -- массив массивов хтмл-ссылолк на форму редактирования ТР, на которых есть активные прикрепленные прайсы,
                 *                   если все прайсовые фрагменты отключены, то сюда добавляются ТП
                 *                   с послених отключенных прайсовых фрагментов
                 */
                $model->update_abon_list_TP($A,
                        make_url(
                                flag_off($FLAG_ALL, $FLAG_URL_TP)
                        )
                );

                /**
                 * Активная абонплата за 30 дней
                 */
                $A['PP30A'] = floatval($A['PPDA'] * 30.0 + $A['PPMA']);

                /**
                 * Активная абонплата за 1 день
                 */
                $A['PP01A'] = floatval($A['PPMA'] / 30.0 + $A['PPDA']);

                /**
                 * Остаток на лицевом счету
                 */
                $A['BALANCE'] = floatval($A['PAYS'] - $A['COST_PA_SUM']);

                /**
                 * Количество предоплаченных дней
                 */
                $A['PREPAYED'] = (cmp_float($A['PP01A'], 0) == 0 ? null : intval($A['BALANCE'] / $A['PP01A']));
            }
        }
        //printf("посчитали<br>\n");
        //echo "Время выполнения ".microtime(true) - $time_start."<br>\n";



        /**
         * Таблица, в которой собраны только контакты пользователей для информационных сообщений
         */
        $contacts = array();

        /**
         * Формирование отображаемой таблицы
         */
        $print_arr = array();
        foreach ($USERS_LIST as $U1) {

            /**
             * Формирование списка пользователей для рассылок
             */
            $contacts[] = $U1['id'];

            foreach ($U1['A'] as $A1) {

                $row['act'] = "<a name='A{$A1['id']}'><font size=1>"
                        . "[<a href='/ad_abon1_info.php?user_id={$U1['id']}' title='Старая карточка пользователя. \nВ настоящее время не поддерживается \n(не факт, чо будет работать)' target=_blank>INF</a>] "
                        . "[<a href='/ad_abon1_sms_form.php?abon_id={$A1['id']}' target=_blank><font style='color: " . ($U1['do_send_sms'] ? "green" : "silver") . "'>SMS</font></a>] "
                        . "[<a href='/ad_abon1_pay.php?abon_id={$A1['id']}' target=_blank>+PAY</a>] "
                        . "[<a href='/ad_abon1_payments.php?abon_id={$A1['id']}' target=_blank>PAYS</a>] "
                        . "[<a href='/sf_list.php?abon_id={$A1['id']}' target=_blank>СФ2</a>]"
                        . "</font>";
                $row['uid'] = $model->url_user_form(intval($U1['id']));
                $row['aid'] = $model->url_abon_form(intval($A1['id']));
                // $row['stat'] = get_abon_state_img(abon_id: $A1['id']);
                $row['info'] = ""
                        . "<table>"
                        . "<tr><td></td><td>{$A1['address']}</td></tr>"
                        . "<tr><td></td><td>{$U1['name_short']} | {$U1['name']}</td></tr>"
                        . "<tr><td>" . ($U1['do_send_sms'] ? CHECK1 : CHECK0) . "</td><td>{$U1['phone_main']}</td></tr>"
                        . ((mb_strlen($U1['mail_main']) > 0) || ($U1['do_send_mail']) ? "<tr><td>" . get_html_CHECK($U1['do_send_mail']) . "</td><td>{$U1['mail_main']}</td></tr>" : "")
                        . ((mb_strlen($U1['address_invoice']) > 0) || ($U1['do_send_invoice']) ? "<tr><td>" . get_html_CHECK($U1['do_send_invoice']) . "</td><td>{$U1['address_invoice']}</td></tr>" : "")
                        . "</table>";

                switch (true) {
                    case (is_null($A1['PREPAYED'])):
                        $warn_color = 'gray';
                        break;
                    case ($A1['PREPAYED'] > $A1['duty_max_warn']):
                        $warn_color = 'green';
                        break;
                    case (($A1['PREPAYED'] <= $A1['duty_max_warn']) && ($A1['PREPAYED'] > $A1['duty_max_off'])):
                        $warn_color = 'orange';
                        break;
                    case ($A1['PREPAYED'] <= $A1['duty_max_off']):
                        $warn_color = 'red';
                        break;
                    default:
                        $warn_color = 'gray';
                }
                $row['address'] = $A1['address'];
                $row['balance'] = $A1['BALANCE'];
                $row['prepayed'] = $A1['PREPAYED'];
                $row['PPMA'] = $A1['PPMA'];
                $row['PPDA'] = $A1['PPDA'];
                $row['PP30A'] = $A1['PP30A'];
                $row['PP01A'] = $A1['PP01A'];
                $row['balance_prepayed'] = "<font style=\"color: " . $warn_color . "\">" . number_format($A1['BALANCE'], 2, ",", " ") . "</font><br>"
                        . "<font color=gray>" . (is_null($row['prepayed']) ? "x" : $row['prepayed']) . "</font>";
                $row['edges'] = ""
                        . get_html_table(t: [
                            [paint($A1['PPMA'], color: GREEN, title: "Price Per Montch Active \nПрайс за месяц Активный"),
                                paint($A1['PPDA'], color: GREEN, title: "Price Per Day Active \nПрайс за день Активный"),
                                paint($A1['PP30A'], color: GREEN, title: "Price Per 30 Days Active \nПрайс за 30 дней Активный")],
                            [paint($A1['duty_max_warn'], title: "Остаток оплаченных дней, \nпри пересечении которого УВЕДОМЛЯТЬ абонента. \nСпособы уведомления берутся из ABON"),
                                paint($A1['duty_max_off'], title: "Остаток оплаченных дней, \nпри пересечении которого ОТКЛЮЧАТЬ абонента"),
                                paint(($A1['duty_auto_off'] ? CHECK1 : CHECK0), face: 'monospace', size: "-1", title: "[X] -- отключать автоматически \n[_] -- не отключать при уходе в минус.")]
                                ],
                                table_attributes: "width=100% border=0 align='center' cellpadding=3 cellspacing=3",
                                cell_attributes: ["width=33% align=right", "width=33% align=right", "width=33% align=right"],
                                bk_fill: false,
                                show_header: false);
                $row['TP'] = get_html_table(t: $A1['TP'], show_header: false, bk_fill: false);

                /**
                 * нижняя таблица для ручного копмрования данных
                 */
                $wide_rec['uid'] = intval($U1['id']);
                $wide_rec['aid'] = $model->url_abon_form(intval($A1['id']));
                $wide_rec['address'] = $A1['address'];
                $wide_rec['name_short'] = $U1['name_short'];
                $wide_rec['name'] = $U1['name'];
                $wide_rec['phone_main'] = $U1['phone_main'];
                $wide_rec['balance'] = str_replace(".", ",", $A1['BALANCE']);
                $wide_rec['ip'] = $A1['PA'][array_key_last($A1['PA'])]['net_ip'];
                $wide_rec['mac'] = $A1['PA'][array_key_last($A1['PA'])]['net_mac'];
                $wide_rec['mac_fake'] = (is_empty($A1['PA'][array_key_last($A1['PA'])]['net_mac']) ? (is_empty($A1['PA'][array_key_last($A1['PA'])]['net_ip']) ? "" : "00:00:00:00:0"
                        . last_octet_str($A1['PA'][array_key_last($A1['PA'])]['net_ip'])[0]
                        . ":"
                        . last_octet_str($A1['PA'][array_key_last($A1['PA'])]['net_ip'])[1]
                        . last_octet_str($A1['PA'][array_key_last($A1['PA'])]['net_ip'])[2]
                        ) : ""
                        );

                $wide[] = $wide_rec;
                unset($wide_rec);

                $print_arr[] = $row;
                unset($row);
            }
        }

        /**
         * Сортировка выходной таблицы
         */
        switch ($sort_by) {
            case BY_ADDRESS_ASC:
                uasort($print_arr, 'compare_address_asc');
                break;
            case BY_ADDRESS_DESC:
                uasort($print_arr, 'compare_address_desc');
                break;
            case BY_BALANCE_ASC:
                uasort($print_arr, 'compare_balance_asc');
                break;
            case BY_BALANCE_DESC:
                uasort($print_arr, 'compare_balance_desc');
                break;
            case BY_PREPAYED_ASC:
                uasort($print_arr, 'compare_prepayed_asc');
                break;
            case BY_PREPAYED_DESC:
                uasort($print_arr, 'compare_prepayed_desc');
                break;
            case BY_PP30A_ASC:
                uasort($print_arr, 'compare_pp30a_asc');
                break;
            case BY_PP30A_DESC:
                uasort($print_arr, 'compare_pp30a_desc');
                break;
            default:
                uasort($print_arr, 'compare_balance_asc');
        }



        /**
         * Общие статистические данные перед таблицей
         */
        $abon_act = 0;
        $abon_off = 0;
        $PPDA_sum = 0.0;
        $PPMA_sum = 0.0;
        $PP01A_sum = 0.0;
        $PP30A_sum = 0.0;
        foreach ($print_arr as $key => $row) {
            if (is_null($row['prepayed'])) {
                $abon_off++;
            } else {
                $abon_act++;
            }
            $PPDA_sum += $row['PPDA'];
            $PPMA_sum += $row['PPMA'];
            $PP01A_sum += $row['PP01A'];
            $PP30A_sum += $row['PP30A'];
        }

        $stat = sprintf("<pre>  Абонентов активных: %s, отключенных: %s <font size=+1>[</font> PPDA: %s, PPMA: %s, PP01A: %s, PP30A: %s <font size=+1>]</font></pre>",
                paint(number_format($abon_act, 0, ",", "_"), "green"),
                paint(number_format($abon_off, 0, ",", "_"), "red"),
                paint(number_format($PPDA_sum, 0, ",", "_"), "blue"),
                paint(number_format($PPMA_sum, 0, ",", "_"), "blue"),
                paint(number_format($PP01A_sum, 0, ",", "_"), "blue"),
                paint(number_format($PP30A_sum, 0, ",", "_"), "blue"));

        function make_switcher($flag, $value, $label): string {
            global $FLAG_ALL;
            return "<a href=" . make_url(use: $FLAG_ALL & ~$flag, set_1: ($value ? 0 : $flag)) . ">{$label}" . ($value ? CHECK1 : CHECK0) . "</a>";
        }

        /**
         * Формирование таблицы фильтров
         */
        // ▼▲◢◣◥◤⩓⩔⬆⬇⮝⮟🜂🜄🞁🞃🠱🠳🡅🡇
        $filter_buttons['STAT'] = $stat . "";
        $filter_buttons['ALL'] = "<a href=" . make_url() . " title='Сбросить все фильтры. \nПоказать полный список.'>[ / ]</a>";
        $filter_buttons['PAY'] = "<a href=" . make_url(
                        use: $FLAG_ALL & ~$FLAG_URL_SHOW_AB_PAY,
                        flag_field: $FLAG_URL_SHOW_AB_PAY,
                        value: strval(intval(!$show_ab_pay))
                ) . " title='Показать плательщиков.'>" . ($show_ab_pay ? CHECK1 : CHECK0) . "</a>";
        // * Если разрешен показ отключенных абонентов, то отображается выключатель показа активных абонентов
        $filter_buttons['ACT'] = ($show_ab_off && $show_ab_pay ? "<a href="
                . make_url(
                        use: $FLAG_ALL & ~$FLAG_URL_SHOW_AB_ACT,
                        set_1: ($show_ab_act ? 0 : $FLAG_URL_SHOW_AB_ACT),
                        set_0: ($show_ab_act ? $FLAG_URL_SHOW_AB_ACT : 0)
                ) . " title='Включить / отключить \nотображение активных абонентов'>" . ($show_ab_act ? CHECK1 : CHECK0) . "</a>" : "");
        // * Если разрешен показ активных абонентов, то отображается выключатель показа отключённых абонентов
        $filter_buttons['OFF'] = ($show_ab_act && $show_ab_pay ? "<a href="
                . make_url(
                        use: $FLAG_ALL & ~$FLAG_URL_SHOW_AB_OFF,
                        set_1: ($show_ab_off ? 0 : $FLAG_URL_SHOW_AB_OFF),
                        set_0: ($show_ab_off ? $FLAG_URL_SHOW_AB_OFF : 0)
                ) . " title='Включить / отключить \nотображение отключенных абонентов'>" . ($show_ab_off ? CHECK1 : CHECK0) . "</a>" : "");
        $filter_buttons['СМС'] = make_switcher($FLAG_URL_DO_SEND_SMS, $show_do_send_sms, "");
        $filter_buttons['MAIL'] = make_switcher($FLAG_URL_DO_SEND_MAIL, $show_do_send_mail, "");
        $filter_buttons['INVOICE'] = make_switcher($FLAG_URL_DO_SEND_INVOICE, $show_do_send_invoice, "");

        $def_make_url = make_url();
        $this->setVariables([
            'title'              => __('Список абонентов по обслуживаемых ТП'),
            'filter_buttons'     => $filter_buttons,
            'print_arr'          => $print_arr,
            'wide'               => $wide,
            'contacts'           => $contacts,
            'show_tp'            => $show_tp,
            'html_sort_name'     => $html_sort_name,
            'html_sort_balance'  => $html_sort_balance,
            'html_sort_prepayed' => $html_sort_prepayed,
            'html_sort_pp30a'    => $html_sort_pp30a,
            'def_make_url'       => $def_make_url
        ]);

        View::setMeta(
            title: __("Список абонентов по ТП"),
        );

    }



    public function validate_deep(array $data): bool {
        $rezult = true;
        $model = new AbonModel();

        if (!$model->validate_id(Abon::TABLE, $data[Abon::F_ID], Abon::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('ID бонента не верен'));
            $rezult = false;
        }

        if (!$model->validate_id(User::TABLE, $data[Abon::F_USER_ID], User::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('ID пользователя не верен'));
            $rezult = false;
        }

        return $rezult;
    }



    /**
     * Проверяет данные перед сохранением пользователя
     * Ошибки пишутся в очередь сообщений в сессию
     * @param array $data  Входные данные (например, $_POST['userRec'])
     * @param bool  $isNew true — при создании, false — при обновлении
     * @return boolean
     */
    public function validate(array $data): bool
    {
        Validator::lang(Lang::code());

        $v = new Validator($data);

        // --- ОБЯЗАТЕЛЬНЫЕ ПОЛЯ ---
        $v->rule('required', [Abon::F_ID, Abon::F_USER_ID]);
        $v->rule('integer', [Abon::F_ID, Abon::F_USER_ID]);

        // --- Проверка ---
        if (!$v->validate() || !$this->validate_deep($data)) {
            MsgQueue::msg(MsgType::ERROR, $v->errors());
            return false;
        }

        return true;
    }



    public function normalize(array &$data) {

        // Убираем лишние пробелы
        foreach (Abon::FORM_FIELDS as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        // Устанавливаем Флаги: если чекбокс не пришёл — ставим 0
        foreach (Abon::T_FLAGS as $field=>$def_value) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = 0;
            } else {
                $data[$field] = ((($data[$field] == 'on') || ($data[$field] == '1') || $data[$field]) ? 1 : 0);
            }
        }

        // Преобразуем дату из строки в timestamp
        if (isset($data[Abon::F_DATE_JOIN_STR]) && $data[Abon::F_DATE_JOIN_STR] !== '') {
            $timestamp = strtotime($data[Abon::F_DATE_JOIN_STR]);
            if ($timestamp !== false) {
                $data[Abon::F_DATE_JOIN] = $timestamp;
            } else {
                $data[Abon::F_DATE_JOIN] = null;
            }
        }
        unset($data[Abon::F_DATE_JOIN_STR]);

    }
    
    

    function updateAction() {
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route', die: 0);

        $model = new AbonModel();

        if  (
                isset($_POST[Abon::POST_REC]) && is_array($_POST[Abon::POST_REC]) &&
                ((int)$_POST[Abon::POST_REC][Abon::F_ID] == (int)$this->route[F_ALIAS]) &&
                $model->validate_id(table_name: Abon::TABLE, field_id: Abon::F_ID, id_value: (int)$this->route[F_ALIAS])
            ) 
        {

            $abon = $model->get_abon((int)$this->route[F_ALIAS]);

            // Копируем только разрешённые поля
            foreach (Abon::FORM_FIELDS as $field=>$def_value) {
                if (array_key_exists($field, $_POST[Abon::POST_REC])) {
                    $post_rec[$field] = $_POST[Abon::POST_REC][$field];
                }
            }

            // Нормализация (очистка и форматирование данных)
            $this->normalize($post_rec);

            // Проверка
            if ($this->validate($post_rec)) {

                // сравнение новой записи и старой
                $equals = true;
                foreach ($post_rec as $field => $value) {
                    if ($abon[$field] != $value) {
                        $equals = false;
                        break;
                    }
                }

                if ($equals) {
                    // Новые данные равны старым данным
                    MsgQueue::msg(MsgType::INFO_AUTO, 'Изменений нет. Нечего вносить в базу.');

                } else {
                    // Данные различаются
                    if ($model->update_row_by_id(table: Abon::TABLE, row: $post_rec, field_id: Abon::F_ID)) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, 'Данные внесены');
                    } else {
                        $_SESSION[SessionFields::FORM_DATA][Abon::POST_REC] = $post_rec;
                        MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                    }
                }
            } else {
                $_SESSION[SessionFields::FORM_DATA][User::POST_REC] = $post_rec;
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, 'Данные не переданы или не верны');
        }
        redirect();
    }



    function editAction() {
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR,__('Авторизуйтесь, пожалуйста'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_edit([Module::MOD_ABON])) {
            MsgQueue::msg(MsgType::ERROR,__('Нет прав'));
            redirect();
        }

        $model = new AbonModel();

        if  (
                isset($this->route[F_ALIAS]) && is_numeric($this->route[F_ALIAS]) &&
                $model->validate_id(Abon::TABLE, intval($this->route[F_ALIAS]), Abon::F_ID)
            )
        {
            $abon = $model->get_row_by_id(Abon::TABLE, intval($this->route[F_ALIAS]), Abon::F_ID);
            View::setMeta(__('Редактирование карточки пользователя'));
            $this->setVariables([
                'abon'=> $abon,
            ]);
        } else {    
            MsgQueue::msg(MsgType::ERROR, __('ID не верен или не указан'));
            redirect();
        }
    }



    function viewAction() {
        // debug($_GET, '_GET:');
        // debug($_POST, '_POST:');
        // debug($this->route, '$this->route:');

        if (!App::$auth->isAuth)
        {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect(Auth::URI_LOGIN);
        }

        /**
         * Проверяем права доступа к модлю
         */
        if (!can_use(Module::MOD_ABON))
        {
            // !!! Возможно это надо это писать в логи и сообщать
            MsgQueue::msg(MsgType::ERROR, 'Нет прав');
            redirect();
        }

        $model = new AbonModel();
        /**
         * Если пришёл запрос на abon_id, то тут запоминаем на какого абонента был адресован запрос,
         * чтобы его открыть, по возможности, если у пользователя несколько абонентов.
         */
        $for_abon_id = -1;

        if  (
                (isset($this->route[F_ALIAS]) && is_numeric($this->route[F_ALIAS])) ||
                (isset($_GET['id']) && is_numeric($_GET['id']))
            )
        {
            /**
             * Если пришёл ALIAS или id
             */
            $id = (int)($this->route[F_ALIAS] ?? $_GET['id']);

            if ($model->validate_id(table_name: Abon::TABLE, field_id: Abon::F_ID, id_value: $id))
            {
                $for_abon_id = $id;
                $user = $model->get_user_by_abon_id($id);
            }
            elseif ($model->validate_id(table_name: User::TABLE, field_id: User::F_ID, id_value: $id))
            {
                $user = $model->get_user($id);
            }
            else
            {
                // !!! Возможно это надо это писать в логи и сообщать
                MsgQueue::msg(MsgType::ERROR, 'ALIAS ID не верен');
                redirect();
            }
            unset($id);
        }
        else
        {
            /**
             * Проверяем наличие
             * сперва abon_id
             * за тем user_id
             * В любоом случае заполняем данные пользователя
             */
            if      (
                        isset($_GET[Abon::F_GET_ID]) &&
                        is_numeric($_GET[Abon::F_GET_ID]) &&
                        $model->validate_id(table_name: Abon::TABLE, field_id: Abon::F_ID, id_value: intval($_GET[Abon::F_GET_ID]))
                    )
            {
                $for_abon_id = intval($_GET[Abon::F_GET_ID]);
                $user = $model->get_user_by_abon_id(intval($_GET[Abon::F_GET_ID]));
            }
            elseif  (
                        isset($_GET[User::F_GET_ID]) &&
                        is_numeric($_GET[User::F_GET_ID]) &&
                        $model->validate_id(table_name: User::TABLE, field_id: User::F_ID, id_value: intval($_GET[User::F_GET_ID]))
                    )
            {
                $user = $model->get_user(intval($_GET[User::F_GET_ID]));
            }
            else
            {
                // !!! Возможно это надо это писать в логи и сообщать
                MsgQueue::msg(MsgType::ERROR, '?UID/?AID ' . __('Не указаны или не верны'));
                redirect();
            }
        }

        /**
         * Если дошли сюда, значит пользователь идентифицирован и загружен
         * Загружаем абонентов
         */
        $user[Abon::TABLE] = $model->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user[User::F_ID], order_by: "`".Abon::TABLE."`.`".Abon::F_IS_PAYER."` DESC");

        foreach ($user[Abon::TABLE] as &$abon) {

            /**
             * Получение остатков по абоненту и сумм активных прайсовых фрагментов
             */
            $abon[AbonRest::TABLE] = $model->get_row_by_id(table_name: AbonRest::TABLE, id_value: $abon[Abon::F_ID], field_id: AbonRest::F_ABON_ID);
            !is_null($abon[AbonRest::TABLE]) && update_rest_fields($abon[AbonRest::TABLE]);

            /**
             * Подгружаем прайсовые фрагенты
             */
            $abon[PA::TABLE]  = $model->get_pa_by_abon_id($abon[Abon::F_ID]);

            /**
             * Подгружаем названия прайсов, для простоты отображения
             */
            foreach ($abon[PA::TABLE] as &$pa_item) {
                $pa_item[PA::F_PRICE_TITLE] = $model->get_row_by_id(table_name: Price::TABLE, field_id: Price::F_ID, id_value: $pa_item[PA::F_PRICE_ID])[Price::F_TITLE];
            }

            /**
             * Подгружаем уведомления, если есть права
             */
            if (can_use(Module::MOD_NOTIFY)) {

                /** Общее количество записей в базе */
                $abon[Notify::F_COUNT] = $model->get_count_by_sql($model->get_sql_notify_by_abon_id($abon[Abon::F_ID]));

                /** Отображаемые записи */
                $abon[Notify::TABLE] = $model->get_notify_by_abon_id($abon[Abon::F_ID], App::$app->get_config('notify_list_limit'));

            }
        }

        /**
         * Подгружаем контакты, если есть права
         */
        if (can_use(Module::MOD_CONTACTS)) {
            $user[Contacts::TABLE] = $model->get_contacts($user[User::F_ID], null);
        }

        /**
         * Подгружаем предприятия, если есть права
         */
        if (can_use(Module::MOD_FIRM)) {
            $user[Firm::TABLE] = $model->get_firms($user[User::F_ID]);
        }

        /**
         * Подключение формы из модуля My
         * В ней реализовано отображение и редактирование данных пользователя и его абонентов
         * и всего остального, что связано с пользователем
         * с учётом прав доступа  
         */
        $this->view = '../My/index';

        View::setMeta(
                title: __('Форма данных абонента'),
                descr: __('Форма просмотра и редактирования данных абонента, и всего, что связано с абонентом: пользователя, прайсов, контактов, СМС')
            );

        $this->setVariables([
            'title'=> __('Карта Пользователя') . ' [' . $user[User::F_ID].']',
            'user' => $user,
        ]);
    }





}