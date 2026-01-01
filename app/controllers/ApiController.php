<?php
/*
 *  Project : my.ri.net.ua
 *  File    : ApiController.php
 *  Path    : app/controllers/ApiController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 00:17:09
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

declare(strict_types=1);

namespace app\controllers;

use app\models\AbonModel;
use app\models\AppBaseModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Api;
use config\Icons;
use config\Mik;
use config\tables\Abon;
use config\tables\Module;
use config\tables\PA;
use config\tables\TP;
use DebugView;
use MikrotikApi\MikroLink;
use PAStatus;

require_once DIR_LIBS ."/lang_functions.php";

/**
 * Description of ApiController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class ApiController extends AppBaseController {



//    static array $errors = [];
    private AbonModel $db;



    public function __construct(array $route) {
        parent::__construct($route);
        $this->db = new AbonModel();
    }



    /**
     * Сборка выходной таблицы прайсовых фрагментов PRICES_APPLY из биллинга для просмотра и управления
     * @param array $bil_pa_list
     * @param array $mik_ip_addresses
     * @param array $mik_abon_list
     * @param array $mik_nat_list
     * @param array $mik_trusted_list
     * @param array $mik_arp_list
     * @return array
     */
    function make_pa_out(array &$bill_tables, array &$mik_tables, int $tp_id): array {

        $bil_pa_list        = $bill_tables[Api::BILL_PA_LIST];
        $mik_ip_addresses   = $mik_tables[Api::MIK_IP_LIST];
        $mik_abon_list      = $mik_tables[Api::MIK_ADDR_LIST]['ABON'];
        $mik_nat_list       = $mik_tables[Api::MIK_NAT];
        $mik_trusted_list   = $mik_tables[Api::MIK_ADDR_LIST]['TRUSTED'] ?? [];
        $mik_arp_list       = $mik_tables[Api::MIK_ARP_LIST];
        $mik_leases_list    = $mik_tables[Api::MIK_LEASES];

        $pa_out = array();
        foreach ($bil_pa_list as $pa_one) {

            if (!is_empty($pa_one[PA::F_NET_NAT11])) {
                $nat11_list = Api::get_nat11_rules($mik_nat_list, $pa_one[PA::F_NET_IP], $pa_one[PA::F_NET_NAT11]);
            }

            if ($pa_one[PA::F_NET_IP_SERVICE]) {

                if (!is_empty($pa_one[PA::F_NET_IP])) {
                    $addr_list_records = Api::get_records_from_address_list_by_ip(mik_list: $mik_abon_list, ip: $pa_one[PA::F_NET_IP], disabled: Mik::OFF);
                    $status_ip_on_abon_str = "";
                    if (count($addr_list_records) == 0) {
                        $status_ip_on_abon = false;
                        $status_ip_on_abon_str .= get_html_img(src: Icons::SRC_ERROR, alt: '[ERROR]', title: "IP-адрес [{$pa_one[PA::F_NET_IP]}] не найден в таблице MIK:ABON");
                        // Активного ИП из ПА нет в микротике
                        $status_ip_on_abon_str .= " "
                            . "Активного IP [".$pa_one[PA::F_NET_IP]."] нет в MIK:ABON<br>"
                            // Очистить поле BIL:PA:net_ip
                            . (!empty($pa_one[PA::F_NET_IP])
                                ?   "<a href=/api_run.php"
                                        . "?cmd=set_price_apply_net_ip"
                                        . "&prices_apply_id=".$pa_one[PA::F_ID]
                                        . "&net_ip="
                                        . "><font face=monospace>[-]</font> Очистить поле IP ".$pa_one[PA::F_NET_IP]." в биллинге</a><br>"
                                :   "" )

                            // Добавить в таблицу МИК:АБОН
                            . (validate_ip($pa_one[PA::F_NET_IP])
                                ?   "<a href=/api_run.php"
                                        . "?cmd=add_tp_abon_ip"
                                        . "&tp_id=".$pa_one[PA::F_TP_ID]
                                        . "&ip=".$pa_one[PA::F_NET_IP]
                                        . "&comment=".rawurlencode(trim($pa_one[PA::F_ABON_ID]." ".translit($pa_one[PA::F_NET_NAME])))
                                        . "><font face=monospace>[+]</font> Добавить IP ".$pa_one[PA::F_NET_IP]." на микротик в таблицу ABON</a><br>"
                                :   "" )

                            // Закрыть PA в биллинге
                            . "<a href=/api_run.php"
                                ."?cmd=set_price_apply_date_end"
                                ."&prices_apply_id=".$pa_one[PA::F_ID]
                                ."&date_end=".time()
                                ."><font face=monospace>[/]</font> Закрыть прайс в биллинге</a><br>";

                        // искать АИД на микротике
                        $ip_on_mik_abon_list = Api::get_records_from_address_list_by_aid(mik_list: $mik_abon_list, abon_id: $pa_one[PA::F_ABON_ID]);
                        if (count($ip_on_mik_abon_list) > 0) {
                            $t = array();
                            foreach ($ip_on_mik_abon_list as $ip_abon_rec) {
                                $t[] = [
                                    'ip_stat'   =>  Api::get_status_ip_from_abon_rec($ip_abon_rec),
                                    'ip'        =>  paint($ip_abon_rec[Mik::LIST_ADDRESS], color: (has_enabled_rec($ip_abon_rec) ? GREEN : GRAY), face: 'monospace'),
                                    'comment'   =>  get_str_cut($ip_abon_rec[Mik::LIST_COMMENT]),
                                    'act'       =>  "<a href=/api_run.php"
                                                    . "?cmd=set_price_apply_net_ip"
                                                    . "&prices_apply_id=".$pa_one[PA::F_ID]
                                                    . "&net_ip=".$ip_abon_rec['address']
                                                    . "> Этот IP => PA</a>"
                                ];
                            }
                            $status_ip_on_abon_str .= get_html_table($t, caption: "IP-адреса найденные в MIK:ABON[{$pa_one[PA::F_ABON_ID]}]", show_header: false);
                        } else {
                            $status_ip_on_abon_str .= "<font face=monospace>[ ]</font> ". paint("В таблице MIK:ABON абонентов [{$pa_one[PA::F_ABON_ID]}] не найдено", color: RED).".<br>";
                        }

                    } elseif (count($addr_list_records) == 1) {
                        $status_ip_on_abon = true;
                    } else {
                        $status_ip_on_abon = false;
                        $status_ip_on_abon_str .= get_html_img(src: Icons::SRC_WARN, alt: '[WARN]', title: 'В таблице MIK:ABON найдено '.count($addr_list_records).' IP-адресов. Так не должно быть. Должен быть только 1 адрес');
                    }
                    foreach ($addr_list_records as $rec) {
                        $status_ip_on_abon_str .= Api::get_status_ip_from_abon_rec($rec);
                    }

                } else {
                    $status_ip_on_abon_str = get_html_img(src: Icons::SRC_ERROR, alt: '[ERROR]', title: 'IP-адрес не указан в биллинге в PRICES_APPLY');
                }

                /**
                 * Проверка статуса ИП-адреса в DHCP-Leases
                 */
                if (validate_ip($pa_one[PA::F_NET_IP])) {
                    $r['address'] = $pa_one[PA::F_NET_IP];
                    $status_ip_on_leases = Api::get_status_from_mik_leases_by_mik_ip_rec($mik_leases_list, $r);
                    unset($r);
                } else {
                    $status_ip_on_leases = "";
                }
            }


            $pa_rec[PA::F_ID]       =  url_pa_form_22($pa_one[PA::F_ID]);

            $pa_rec[PA::F_ABON_ID]  = $this->db->get_abon_state_img(abon_id: $pa_one[PA::F_ABON_ID], title_prefix: "PA abon_id: {$pa_one[PA::F_ABON_ID]}\nПроверка статуса Абонета {$pa_one[PA::F_ABON_ID]} по биллингу\n")."&nbsp;"
                                    . url_abon_form($pa_one[PA::F_ABON_ID]);

            $pa_rec['inf']      =  get_str_cut($pa_one['net_name'], max_length:30)."<br>"
                                    . get_html_CHECK(has_check: (bool)$pa_one['price_closed'], title: "Закрыт ли &laquo;Прайсовий фрагмент&raquo;")
                                    . " " . date_Ymd($pa_one['date_start'])." : ".date_Ymd($pa_one['date_end'], value_if_null: "____-__-__")."<br>"
                                    . price_frm($pa_one['prices_id'], has_img: false, target: "_blank");

            $pa_rec['ip_service']    =  get_html_CHECK((bool)$pa_one['net_ip_service'], "IP услуга");

            $pa_rec['nat11']         =  ($pa_one['net_ip_service']
                                        ?   (!is_empty($pa_one['net_nat11'])
                                                ? "<font face=monospace title='&laquo;Белый&raquo; IP-адрес'>{$pa_one['net_nat11']}</font><br>"
                                                    . get_html_check_img(
                                                            status: Api::has_ip_in_address_list($mik_ip_addresses, ip: $pa_one['net_nat11'], disabled: "false"),
                                                            title_true: "Есть в таблице /IP ADDRESS",
                                                            title_false: "НЕТ в таблице /IP ADDRESS")
                                                    . get_html_check_img(
                                                            status: (count($nat11_list) == 2),
                                                            title_true: "Ок. Правил в таблице NAT: &laquo;".count($nat11_list)."&raquo;",
                                                            title_false: "ОШИБКА: Правил в таблице NAT: &laquo;".count($nat11_list)."&raquo;")
                                                : "<font face=monospace color=gray title='Используется НАТ, без внешнего адреса. '>___.___.___.___</font><br>&nbsp;"
                                            )
                                            ."<br>"
                                            . (!is_empty($pa_one['net_on_abon_ip'])
                                                ? "<font face=monospace title='Прямой &laquo;Белый&raquo; IP-адрес \nна оборудовании абонента, \nмимо микротика.'>{$pa_one['net_on_abon_ip']}</font>"
                                                : "<font face=monospace color=gray title='Используется НАТ. \nБез &laquo;Белого&raquo; IP-адреса на оборудовании абонента.'>___.___.___.___</font><br>&nbsp;"
                                            )
                                        :   paint("Не IP-сервис", GRAY)
                                    );

            $pa_rec['trusted']       =  ($pa_one['net_ip_service']
                                        ?   get_html_CHECK((bool)$pa_one['net_ip_trusted'], "&laquo;Доверенный&raquo; &mdash; Не проверяется на флуд.")."<br>"
                                            . get_html_check_img(
                                                    status: $pa_one['net_ip_trusted'] == Api::has_ip_in_address_list($mik_trusted_list, ip: $pa_one[PA::F_NET_IP], address_list_name:"TRUSTED", disabled: "false"),
                                                    title_true: "Соответсвует списку TRUSTED",
                                                    title_false: "НЕ соответсвует списку TRUSTED",
                                                    icon_width: 10, icon_height: 10)
                                        :   paint("Не IP-сервис", GRAY)
                                    );

            if ($pa_one['net_ip_service']) {
                $pa_mac_update_act = "";
                $arp_rec = Api::get_mac_from_arp_list_by_ip(mik_arp_list: $mik_arp_list, ip: $pa_one[PA::F_NET_IP]);
                if (is_array($arp_rec)) {
                    if ($arp_rec['mac-address'] != $pa_one['net_mac']) {
                        if (validate_mac($arp_rec['mac-address'])) {
                            $pa_mac_update_act = "<a href=/api_run.php"
                                                    ."?cmd=set_price_apply_net_mac"
                                                    ."&prices_apply_id=".$pa_one[PA::F_ID]
                                                    ."&net_mac=".$arp_rec['mac-address']
                                                    ."&ref=". rawurlencode($_SERVER['SCRIPT_NAME']."?tp_id=".$tp_id."&_PA#_PA").">MAC ARP => PA</a>";
                        } else {
                            $pa_mac_update_act = paint("МАК не верен", GRAY);
                        }
                    } else {
                        $pa_mac_update_act = paint("МАКи равны", GRAY);
                    }
                } else {
                    $pa_mac_update_act = "";
                }
            }

            $pa_rec['ip']            =  ($pa_one['net_ip_service']
                                        ?   ""
                                            . (is_empty($pa_one['net_on_abon_ip'])
                                                ?   $status_ip_on_abon_str." ".$status_ip_on_leases." ".$pa_mac_update_act."<br>"
                                                    . paint(paint("__________ ", GRAY).$pa_one[PA::F_NET_IP], face: 'monospace') . "<br>"
                                                    . Api::get_status_mac_from_arp_by_ip($mik_arp_list, $pa_one[PA::F_NET_IP]) // ." / ". $row['net_mask']." / ". $row['net_gateway'],
                                                :   (!is_empty($pa_one[PA::F_NET_IP])
                                                        ? paint(get_html_img('/img/icon_error.svg') . " ERROR IP", color: RED,
                                                                title: "IP должен быть в [PA::F_NET_IP] ИЛИ в ['net_on_abon_ip'], \n"
                                                                . "но не в обоих полях одновременно, \n"
                                                                . "поскольку это предполагает разный способ подачи интернета абоненту \n"
                                                                . "и разные настройки оборудования.")
                                                        : paint("ip on abon: ok", GRAY)
                                                        )
                                                )
                                        :   paint("Не IP-сервис", GRAY)
                                    );

            $pa_rec['mac']           =  ($pa_one['net_ip_service']
                                        ?   paint(paint("__________ ", GRAY) . $pa_one['net_mac'], face: "monospace") . "<br>"
                                            . (validate_mac($pa_one['net_mac'])
                                                ? Api::get_status_mac_in_arp($mik_arp_list, $pa_one['net_mac'])
                                                : "<font face=monospace color=gray title='MAC-адрес не указан'>MAC не верен / не указан</font>"
                                              )
                                        :   paint("Не IP-сервис", GRAY)
                                    );
            $pa_out[] = $pa_rec;
        }
        return $pa_out;
    }



    function combineAction() {
        $tp_id = (int)$this->route[F_ALIAS];
        if (!$this->db->validate_id(table_name: TP::TABLE, field_id: TP::F_ID, id_value: $tp_id)) {
            MsgQueue::msg(MsgType::ERROR, __('ID не верен'));
            redirect();
        }

        $bill_rec[Api::BILL_TP] = $this->db->get_tp($tp_id);
        $bill_rec[Api::BILL_PA_LIST] = $this->db->get_prices_apply_by_tp($tp_id);

        $mik = Api::tp_connector(tp: $bill_rec[Api::BILL_TP]);
        if ($mik === false) {
            MsgQueue::msg(MsgType::ERROR, Api::$errors);
            redirect();
        }

        $mik_rec[Api::MIK_IDENTITY]  = Api::get_mik_identity($mik);
        $mik_rec[Api::MIK_IP_LIST]   = Api::get_tp_ip_address_list($mik);
        $mik_rec[Api::MIK_RESOURSE]  = Api::get_tp_resource($mik);
        $mik_rec[Api::MIK_ADDR_LIST] = Api::remake_table_lists(get_aligned_table(Api::get_tp_address_list($mik)));
        $mik_rec[Api::MIK_ARP_LIST]  = get_aligned_table(Api::get_tp_arp_all($mik));
        $mik_rec[Api::MIK_GATES]     = get_aligned_table(Api::get_tp_gateways($mik));
        $mik_rec[Api::MIK_NAT]       = get_aligned_table(Api::get_tp_nat($mik));
        $mik_rec[Api::MIK_LEASES]    = get_aligned_table(Api::get_tp_dhcp_leases_all($mik));


        $out_tables = [
            Api::OUT_PA    => [
                'title'             => '[PA]',
                't'                 => $this->make_pa_out(bill_tables: $bill_rec, mik_tables: $mik_rec, tp_id: $tp_id),
                'caption'           => "<h1>Биллинг: Список прайсовых фрагментов <font color=green>PRICES_APPLY</font></h1>",
                //                     ["id",           "abon_id", "inf", "ip_service",   "nat11",                    "trusted",      "ip",            "mac"]
                'cell_attributes'   => ["align=center", "abon_id", "inf", "align=center", "nat11",                    "align=center", "valign=bottom", "valign=bottom"],
                'col_titles'        => ["PA ID",        "Abon ID", "inf", "IP Service",   "NAT 1:1<br>IP у абонента", "trust",        "ip",            "mac"],
            ],

//            OUT_ARP     =>  ['src_on'=>SRC_ARP_ACT,     'src_off'=>SRC_ARP_OFF,    'txt' => '[ARP]',    'mng_id' => 'out_arp',    'btn_id'=>'btn_put_arp',    'color'=>(isset($_GET[ANCH_ARP])    ? BLACK : GRAY),
//                                'anch'              =>  ANCH_ARP,
//                                't'                 =>  make_arp_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Таблица <font color=green>ARP</font></h1>",
//                                                    //   "aid_abon", "aid_pa", "sw_1",   "sw_comment", "ip",     "fine_1", "address", "mac-stat", "interface", "published",    "aid from ABON",                    "aid from PA",                    "sw from SW",                    "stat1"
//                                'cell_attributes'   =>  ["hidden",   "hidden", "hidden", "hidden",     "hidden", "hidden", "address", "mac-stat", "interface", "align=center", "aid from ABON",                    "aid from PA",                    "sw from SW",                    "stat1"],
//                                'col_titles'        =>  ["aid_abon", "aid_pa", "sw_1", "sw_comment",   "ip",     "fine_1", "address", "mac-stat", "interface", "published",    "aid<br>".paint("from ABON", GRAY), "aid<br>".paint("from PA", GRAY), "sw<br>".paint("ABON/SW", GRAY), "stat1"],
//                            ],
//
//            OUT_ABON    =>  ['src_on'=>SRC_A_ACT,      'src_off'=>SRC_A_OFF,      'txt' => '['.MIK_TABLE_ABON.']',   'mng_id' => 'abon_out',   'btn_id'=>'btn_abon_out',   'color'=>(isset($_GET[ANCH_ABON])   ? BLACK : GRAY),
//                                'anch'              =>  ANCH_ABON,
//                                't'                 =>  make_abon_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Список состояния абонентов таблицы <font color=green>".MIK_TABLE_ABON."</font></h1>",
//                                'cell_attributes'   =>  null,
//                                'col_titles'        =>  null,
//                            ],
//
//            OUT_LEASES  =>  ['src_on'=>SRC_LEASES_ACT, 'src_off'=>SRC_LEASES_OFF, 'txt' => '[LEASES]', 'mng_id' => 'leases_out', 'btn_id'=>'btn_leases_out', 'color'=>(isset($_GET[ANCH_LEASES]) ? BLACK : GRAY),
//                                'anch'              =>  ANCH_LEASES,
//                                't'                 =>  make_dhcp_leases_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Таблица <font color=green>DHCP-LEASES</font></h1>",
//                                //                       "astat", "address", "mac-address", "address_mac",    "last-seen"  "server", "active-address", "comment", "aid_comment", "aid_comment_stat", "aid_abon", "aid_abon_stat",  "aid_ip_pa", "aid_ip_pa_stat", "act", "rename_comment"
//                                'cell_attributes'   =>  ["",      "hidden",  "hidden",      "",               "",          "",       "",               "",        "hidden",      "",                 "hidden",   "",               "hidden",    "",               "",    ""],
//                                'col_titles'        =>  ["stat",  "address", "mac-address", "address<br>mac", "last-seen", "server", "active-address", "comment", "aid_comment", "aid<br>comment",   "aid_abon", "aid<br>ABON IP", "aid_ip_pa", "aid<br>PA IP",   "act", "rename_comment"],
//                            ],
//
//            OUT_NAT     =>  ['src_on'=>SRC_NAT_ACT,    'src_off'=>SRC_NAT_OFF,    'txt' => '[NAT]',    'mng_id' => 'nat_out',    'btn_id'=>'btn_nat_out',    'color'=>(isset($_GET[ANCH_NAT])    ? BLACK : GRAY),
//                                'anch'              =>  ANCH_NAT,
//                                't'                 =>  make_nat_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Таблица <font color=green>NAT</font></h1>",
//                                'cell_attributes'   =>  ["",      "",      "",       "",         "valign=top", "valign=top", "",        "",              "",    "",    ""],
//                                'col_titles'        =>  ["stat1", "chain", "action", "protocol", "in",         "out",        "comment", "bytes|packets", "aid", "act", "rename"],
//                            ],

        ];






        $this->setVariables([
            'out_tables' => $out_tables,
            'mik_rec'    => $mik_rec,
            'bill_rec'   => $bill_rec,
        ]);

        View::setMeta(__('Управление микротиком'));

//        debug($leases, '$leases', debug_view: DebugView::PRINTR, die: 0);
//        debug($nat, '$nat', debug_view: DebugView::PRINTR, die: 0);
//        debug($gates, '$gates', debug_view: DebugView::PRINTR, die: 0);
//        debug($pa, '$pa', debug_view: DebugView::PRINTR, die: 0);
//        debug($address_list, '$address_list', debug_view: DebugView::PRINTR, die: 0);
//        debug($mik_info, '$mik_info', debug_view: DebugView::PRINTR, die: 0);
//        debug($tp, '$tp', debug_view: DebugView::PRINTR, die: 0);

//        debug('end', 'end', die: 1);
    }




    function indexAction() {

        redirect();

//        debug($_GET, '$_GET');
//        debug($_POST, '$_POST');
//        debug($_REQUEST, '$_REQUEST');
//        debug($_COOKIE, '$_COOKIE');

        $ip = '95.158.32.243';
        $login = 'billing';
        $pass = 'sfdjg;sdfjg;sdlfkgjs;dlfkg';
        $ssl = true;
        $port = 18729;

        $mik = Api::mik_connector($ip, $login, $pass, $port, $ssl);

//        $response = $router->exec('/ip/firewall/address-list/print',
//                [
//                    "?list"=>'ABON'
//                ]
//        );

        $response = $mik->exec(
                '/ip/firewall/address-list/add',
                [
                    "list"=>"ABON",
                    "address"=>'10.1.1.1',
                    "comment"=>'test',
                    "disabled"=>"no"
                ]
            );

//        $response = $router->exec('/ip/firewall/address-list/print',
//                [
//                    "?list"=>"ABON",
//                ]
//            );

        debug($response, '$response');

        $mik->disconnect();

        debug('end', 'end', die: 1);
    }



    function cmdAction() {
        // /api/abon-ip?cmd=set&ip=1.1.1.1&ena=1
        // (
        //     [api/abon-ip] => 
        //     [cmd] => cmdena
        //     [tpid] => 68
        //     [ip] => 10.1.4.161
        //     [ena] => 1
        // )
        // debug($_GET, '$_GET');
        // debug($this->route, '$this->route', die:0);

        if (!App::isAuth()) {
            redirect('/');
        }


        if (!can_edit(Module::MOD_PA)) {
            MsgQueue::msg(MsgType::ERROR, __('Нет прав'));
            redirect();
        }


        if (isset($_GET[Api::F_CMD])) {
            $cmd = h($_GET[Api::F_CMD]);
            switch ($cmd) {
                case Api::CMD_IP_ENABLE:
                    if (can_edit(Module::MOD_PA)) {
                        $tp_id  = intval($_GET[Api::F_TP_ID]);
                        $ip     = $_GET[Api::F_IP];
                        $ena    = boolval($_GET[Api::F_ENABLED]);
                        if (Api::set_mik_abon_ip(Api::tp_connector($tp_id), $ip, $ena, true)) {
                            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Статус установлен успешно'));
                            if (Api::$errors) {
                                MsgQueue::msg(MsgType::SUCCESS_AUTO, Api::$errors);
                            }
                        } else {
                            MsgQueue::msg(MsgType::ERROR, __('Ошибка установления статуса IP адреса'));
                            if (Api::$errors) {
                                MsgQueue::msg(MsgType::ERROR, Api::$errors);
                            }
                        }
                    } else {
                        MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                    }
                    redirect();
                    break;
                    
                case Api::CMD_SERV_ENA:
                    /**
                     * Поставить/Снять паузу
                     */
                    if (can_edit(Module::MOD_PA)) {
                        $ena   = (($_GET[Api::F_ENABLED] ?? 0) ? 1 : 0);
                        $force = (($_GET[Api::F_FORCE] ?? 0) ? 1 : 0);
                        $pa_id = intval($_GET[Api::F_PA_ID] ?? 0);
                        $model = new AbonModel();
                        $pa = $model->get_pa($pa_id);
                        $pa_new_id = PaController::enable(pa: $pa, ena: $ena, force: $force);
                        $model->recalc_abon($pa[PA::F_ABON_ID]);
                        if ($pa_new_id && ($pa_new_id != $pa_id)) {
                            redirect(PA::URI_EDIT . '/' . $pa_new_id);
                        }
                    } else {
                        MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                    }
                    redirect();
                    break;

                case Api::CMD_PA_CLONE:
                    /**
                     * Клонировать ПФ
                     */
                    if (can_add(Module::MOD_PA)) {
                        $pa_id = intval($_GET[Api::F_PA_ID] ?? 0);
                        $model = new AbonModel();
                        $pa = $model->get_pa($pa_id);
                        $pa_new_id = PaController::clone(pa: $pa);
                        $model->recalc_abon($pa[PA::F_ABON_ID]);
                        redirect(PA::URI_EDIT . '/' . $pa_new_id);
                    } else {
                        MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                        redirect();
                    }
                    break;

                case Api::CMD_PA_CLOSE:
                    /**
                     * Закрыть ПФ
                     */
                    if (!can_edit(Module::MOD_PA)) {
                        MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                        redirect();
                    }

                    $pa_id  = intval($_GET[Api::F_PA_ID] ?? 0);
                    $abon_off = ($_GET[Api::F_ABON_OFF_ON_TP] ?? 0) ? true : false;
                    PaController::pa_close($pa_id, $abon_off);
                    redirect();
                    break;
                    
                case Api::CMD_PA_DELETE:
                    /**
                     * Удалить ПФ
                     */
                    if (can_del(Module::MOD_PA)) {
                        $pa_id = intval($_GET[Api::F_PA_ID] ?? 0);
                        $model = new AbonModel();
                        $pa = $model->get_pa($pa_id);
                        if (PaController::delete($pa_id)) {
                            MsgQueue::msg(MsgType::SUCCESS, __('Прайсовый фрагмент успешно удалён'));
                            MsgQueue::msg(MsgType::INFO, $pa);
                            $model->recalc_abon($pa[PA::F_ABON_ID]);
                            redirect(Abon::URI_VIEW . '/' . $pa[PA::F_ABON_ID]);
                        } else {
                            redirect();
                        }
                    } else {
                        MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                        redirect();
                    }
                    break;

                case Api::CMD_PASS_DEF:
                    /**
                     * Установить начальный пароль
                     */
                    if (can_edit(Module::MOD_USER_CARD)) {
                        $uid = intval($_GET[Api::F_UID] ?? 0);
                        $model = new AbonModel();
                        $user = $model->get_user($uid);
                        UserController::update_pass($user, 1);
                    } else {
                        MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                    }
                    redirect();
                    break;

                default:
                    # code...
                    break;
            }
        }

        debug($_GET, '$_GET');
        debug($this->route, '$this->route', die:0);
        echo 'abonIpAction(): не обработанная ситуация.';
        die();
    }



}