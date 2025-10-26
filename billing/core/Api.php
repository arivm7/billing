<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Api.php
 *  Path    : billing/core/Api.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 23:07:55
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

use app\models\AppBaseModel;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Icons;
use config\tables\TP;
use MikrotikApi\MikroLink;
use config\Mik;


/**
 * Description of Api.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Api {


    public static $errors = [];


    /**
     * Константы для именования ключей таблиц из базы
     */
    public const BILL_TP      = 'tp';
    public const BILL_PA_LIST = 'pa_list';

    /**
     * Константы для именования ключей таблиц из микротика
     */
    public const MIK_IDENTITY   = 'identity';   // Строка идентификации микротика
    public const MIK_RESOURSE   = 'resourse';   // таблица технических параметров устройства
    public const MIK_IP_LIST    = 'ip_list';    // таблицы /ip/address содержит IP-адреса устройства
    public const MIK_ADDR_LIST  = 'addr_list';  // таблицы адресных листов, содержит подтаблицы по именам листов
    public const MIK_ARP_LIST   = 'arp_list';   // таблицы адресных листов, содержит подтаблицы по именам листов
    public const MIK_GATES      = 'gates';      // шлюзы устройтва
    public const MIK_NAT        = 'nat';        // таблица NAT устройства
    public const MIK_LEASES     = 'leases';     // таблийца DHCP LEASES (выданных адресов)


    /**
     * Константы для именования ключей выходных таблиц в общем массиве
     */
    public const OUT_PA     = 'OUT_PA';
    public const OUT_ABON   = 'OUT_ABON';
    public const OUT_LEASES = 'OUT_LEASES';
    public const OUT_NAT    = 'OUT_NAT';
    public const OUT_ARP    = 'OUT_ARP';



    /**
     * Создаёт объект подключения к микротику
     * Для подключения нужно передать или ID ехплощадки или массив параметров техплощадки.
     * @param int|null $tp_id -- ID техплощадки
     * @param array|null $tp -- Массив с параметрами техплощадки
     * @throws \Exception
     * @return bool|MikroLink -- Возвращаемій объект.
     */
    public static function tp_connector(int|null $tp_id = null, array|null $tp = null): MikroLink|bool  {
        if (is_null($tp_id) && is_null($tp)) {
            throw new \Exception('Нужно указать или ID ТП или массив TP');
        }

        if (is_null($tp)) {
            $model = new AppBaseModel();
            $tp = $model->get_tp($tp_id);
        }
        return self::mik_connector(
                ip:     $tp[TP::F_MIK_IP],
                login:  $tp[TP::F_MIK_LOGIN],
                pass:   $tp[TP::F_MIK_PASSWD],
                port:   $tp[TP::F_MIK_PORT_SSL],
                ssl:    true);
    }



    public static function mik_connector(string $ip, string $login, string $pass, int $port, bool $ssl): MikroLink|false {
        $router = new MikroLink(
            timeout:  1, // Ожидание ответа при подключении
            attempts: 2, // количество попыток подключения
            delay:    0, // Задержка после попытки подключения
            logFile:  DIR_LOG . '/mikrolink.log',
            printLog: false
        );

        set_error_handler(function($errno, $errstr) {
            throw new \RuntimeException($errstr, $errno);
        });

        try {
            if (!$router->connect($ip, $login, $pass, $port, $ssl)) {
                throw new \RuntimeException("Connect failed: " . $router->error_str);
            }
        } catch (\Throwable $e) {
            self::$errors[] = $e->getMessage();
            return false;
        } finally {
            restore_error_handler();
        }

        return $router;
    }





    public static function remake_table_lists(array $address_list): array {

        uasort($address_list, function($a, $b) {
            $cmp = strcasecmp($a[Mik::LIST_LIST], $b[Mik::LIST_LIST]);
            if ($cmp === 0) {
                $cmp = strcasecmp($a[Mik::LIST_ADDRESS], $b[Mik::LIST_ADDRESS]);
                if ($cmp === 0) {
                    $cmp = strcasecmp($a[Mik::LIST_COMMENT] ?? '', $b[Mik::LIST_COMMENT] ?? '');
                    if ($cmp === 0) {
                        $cmp = strcasecmp($a[Mik::LIST_DISABLED], $b[Mik::LIST_DISABLED]);
                    }
                }
            }
            return $cmp;
        });

        $a = [];
        foreach ($address_list as $row) {
            $a[$row[Mik::LIST_LIST]][] = $row;
        }
        return $a;
    }



    public static function has_mik_lease_rec_dinamik(array|null $mik_lease_rec): bool {
        /*
         * [.id] => *5 [address] => 10.1.17.197 [mac-address] => 84:16:F9:91:77:93 [client-id] => 1:84:16:f9:91:77:93
         * [address-lists] => [server] => default [always-broadcast] => true [dhcp-option] => [status] => bound
         * [expires-after] => 8m40s [last-seen] => 1m20s [active-address] => 10.1.17.197 [active-mac-address] => 84:16:F9:91:77:93
         * [active-client-id] => 1:84:16:f9:91:77:93 [active-server] => default [host-name] => TL-WR840N [radius] => false
         * [dynamic] => false [blocked] => false [disabled] => false [comment] => 17101 PivnayaLavka
         *
         */
        // если заблокирован или выключен, то НЕ может быть динамическим
        // if ($mik_lease_rec['blocked'] == 'true') { return false; }
        // if ($mik_lease_rec['disabled'] == 'true') { return false; }
        if (is_null($mik_lease_rec)) { return false; }
        return ($mik_lease_rec['dynamic'] == 'true');
    }



    public static function has_mik_lease_rec_static(array|null $mik_lease_rec): bool {
        /*
         * [.id] => *5 [address] => 10.1.17.197 [mac-address] => 84:16:F9:91:77:93 [client-id] => 1:84:16:f9:91:77:93
         * [address-lists] => [server] => default [always-broadcast] => true [dhcp-option] => [status] => bound
         * [expires-after] => 8m40s [last-seen] => 1m20s [active-address] => 10.1.17.197 [active-mac-address] => 84:16:F9:91:77:93
         * [active-client-id] => 1:84:16:f9:91:77:93 [active-server] => default [host-name] => TL-WR840N [radius] => false
         * [dynamic] => false [blocked] => false [disabled] => false [comment] => 17101 PivnayaLavka
         *
         */
        if (is_null($mik_lease_rec)) { return false; }
        return ($mik_lease_rec['dynamic'] == 'false')
                && ($mik_lease_rec['blocked'] == 'false')
                && ($mik_lease_rec['disabled'] == 'false');
    }



    public static function get_rec_from_leases_list_by_ip(array|null $mik_leases_list, string $ip): array|null {
        /*
         * [.id] => *5 [address] => 10.1.17.197 [mac-address] => 84:16:F9:91:77:93 [client-id] => 1:84:16:f9:91:77:93
         * [address-lists] => [server] => default [always-broadcast] => true [dhcp-option] => [status] => bound
         * [expires-after] => 8m40s [last-seen] => 1m20s [active-address] => 10.1.17.197 [active-mac-address] => 84:16:F9:91:77:93
         * [active-client-id] => 1:84:16:f9:91:77:93 [active-server] => default [host-name] => TL-WR840N [radius] => false
         * [dynamic] => false [blocked] => false [disabled] => false [comment] => 17101 PivnayaLavka
         *
         */
        if (is_null($mik_leases_list)) { return null; }
        if (!(validate_ip($ip) || is_ip_net($ip))) {
            throw new \Exception("IP [{$ip}] не верен");
        }
        foreach ($mik_leases_list as $row) {
            if  (   isset($row['address']) &&
                    ($row['address'] == $ip)
                    && has_enabled_rec($row)
                )
            {
                return $row;
            }
        }
        return null;
    }




    public static function get_nat11_rules(array $nat_list, string|null $ip_inner = null, string|null $ip_outer = null): array {
        $nat11 = array();
        foreach ($nat_list as $nat_row) {
            // .id	chain	action	to-addresses    out-interface-list src-address in-interface-list out-interface	dst-address     to-ports protocol src-address-list in-interface	dst-port log   log-prefix bytes     packets invalid dynamic disabled comment
            // *2	dstnat	netmap	10.1.1.57	*                  *           *                 *              159.224.135.200 *        *        *                *            *        false *          431129145 6362226 false   false   false    NAT 509 FARNSUA
            // *3	srcnat	netmap	159.224.135.200 *                  10.1.1.57   *                 *              *               *        *        *                *            *        false *          21092371  86029   false   false   false    NAT 509 FARNSUA
            if (
                    (
                            ($nat_row['chain']    == 'dstnat')
                         && ($nat_row['action']   == 'netmap')
                         && (is_empty($ip_inner)  ? true : (isset($nat_row['to-addresses']) && ($nat_row['to-addresses'] == $ip_inner)))
                         && (is_empty($ip_outer)  ? (isset($nat_row['dst-address']) && validate_ip($nat_row['dst-address'])) : (isset($nat_row['dst-address'])  && ($nat_row['dst-address']  == $ip_outer)))
                         && ($nat_row['disabled'] != 'true')
                    ) ||
                    (
                            ($nat_row['chain']    == 'srcnat')
                         && ($nat_row['action']   == 'netmap')
                         && (is_empty($ip_outer)  ? (isset($nat_row['dst-address']) && validate_ip($nat_row['dst-address'])) : (isset($nat_row['to-addresses']) && ($nat_row['to-addresses'] == $ip_outer)))
                         && (is_empty($ip_inner)  ? true : (isset($nat_row['src-address'])  && ($nat_row['src-address']  == $ip_inner)))
                         && ($nat_row['disabled'] != 'true')
                    )
               )
            {
                $nat11[] = $nat_row;
            }
        }
        return $nat11;
    }



    /**
     * Возвращает список записей mik:address_list с указанным ip-адресом
     * и с использованием дополнительных фильтров
     * @param array $mik_list -- массив записей типа mik:address_list
     * @param string $ip -- искомый IP
     * @param string|null $list
     * @param string|null $dynamic
     * @param string|null $disabled
     * @return array
     * @throws Exception
     */
    public static function get_records_from_address_list_by_ip(array $mik_list, string|null $ip, string|null $list=null, string|null $dynamic=null, string|null $disabled=null): array
    {
        /*
         * MIK ADDRESS_LIST
         * .id list address creation-time dynamic disabled comment
         */
        $records = array();
        foreach ($mik_list as $row) {
            if (
                    ($row[Mik::LIST_ADDRESS] == $ip)
                    && (is_null($list)     ? true : ($list     == $row[Mik::LIST_LIST]))
                    && (is_null($dynamic)  ? true : ($dynamic  == $row[Mik::LIST_DYNAMIC]))
                    && (is_null($disabled) ? true : ($disabled == $row[Mik::LIST_DISABLED]))
               )
            {
                $records[] = $row;
            }
        }
        return $records;
    }



    /**
     * Возвращает список записей mik:address_list с указанным $abon_id из комментария
     * и с использованием дополнительных фильтров
     *
     * @param array $mik_list
     * @param int $abon_id
     * @param string|null $list
     * @param string|null $dynamic
     * @param string|null $disabled
     * @return array
     */
    public static function get_records_from_address_list_by_aid(array $mik_list, int $abon_id, string|null $list=null, string|null $dynamic=null, string|null $disabled=null): array {
        /*
         *
         * MIK ADDRESS_LIST
         * .id list address creation-time dynamic disabled comment
         *
         */
        $records = array();
        foreach ($mik_list as $row) {
            if (
                    (get_aid_from_str($row[Mik::LIST_COMMENT]) == $abon_id)
                    && (is_null($list)     ? true : ($list     == $row[Mik::LIST_LIST]))
                    && (is_null($dynamic)  ? true : ($dynamic  == $row[Mik::LIST_DYNAMIC]))
                    && (is_null($disabled) ? true : ($disabled == $row[Mik::LIST_DISABLED]))
               )
            {
                $records[] = $row;
            }
        }
        return $records;
    }





    /**
     * на вход получает запись mik:ip_address_list :ABON
     * Возвращает хтмл-сроку с картинкой, обозначающей статус этой записи
     * MIK IP ADDRESS_LIST: .id list address creation-time dynamic disabled comment
     * @param array $address_rec
     * @return string
     */
    public static function get_status_ip_from_abon_rec(array $address_rec): string {
        return  (is_ip_net($address_rec[Mik::LIST_ADDRESS])
                    ? get_html_check_img(has_enabled_rec($address_rec), title_true: "IP-Сеть в таблице ABON включена", title_false: "IP-Сеть в таблице ABON выключена", img_true: '/img/icon_mik_abon_net_on.svg', img_false: '/img/icon_mik_abon_net_off.svg')
                    : get_html_check_img(has_enabled_rec($address_rec), title_true: "IP в таблице ABON включён", title_false: "IP в таблице ABON выключён", img_true: '/img/icon_mik_abon_ip_on.svg', img_false: '/img/icon_mik_abon_ip_off.svg')
                );
    }




    public static function get_status_from_mik_leases_by_mik_ip_rec(array|null $mik_leases, array $mik_ip_rec): string {
        $s = "";
        if (is_null($mik_leases) || is_ip_net($mik_ip_rec['address'])) {
            /**
             * Это сеть.
             * Проверять в DHCP-Leases не нужно
             */
            $s .= get_html_check_img(
                    status: true,
                    title_true: "Это сеть. \nПроверять в DHCP-Leases не нужно.",
                    img_true: Icons::SRC_DHCP_GRAY);
        } else {
            /**
             * Это IP-адрес.
             * Проверка записи в DHCP-Leases
             */
            $lease_rec = Api::get_rec_from_leases_list_by_ip(mik_leases_list: $mik_leases, ip: $mik_ip_rec['address']);

            $s .=   (
                        Api::has_mik_lease_rec_dinamik($lease_rec)
                            ? get_html_img(src: Icons::SRC_DHCP_ERROR, alt: '[ERROR]', color: RED, title: "IP в MIK:DHCP_Leases Динамический, должен быть Статический. \nОшибка регистрации абонента. \nТребуется вмешательство")
                            : get_html_check_img(
                                status: Api::has_mik_lease_rec_static($lease_rec) ,
                                title_true: "Норм. В MIK:DHCP-Leases есть Статичная Активная запись.",
                                title_false: "Ошибка. В MIK:DHCP-Leases НЕТ активной статичной записи привязки к этому IP.",
                                img_true: Icons::SRC_DHCP_OK,
                                img_false: Icons::SRC_DHCP_WARN)
                    );
        }
        return $s;
    }



    private static $CASHE_MAC_IN_ARP_BY_IP = array();



    public static function get_mac_from_arp_list_by_ip(array &$mik_arp_list, null|string $ip): array|string|null {
        if (is_empty($ip)) { return null; }
        if (!validate_ip($ip)) { return "IP [{$ip}] не верен."; }

        if (!array_key_exists($ip, self::$CASHE_MAC_IN_ARP_BY_IP)) {
            self::$CASHE_MAC_IN_ARP_BY_IP[$ip] = null;
            foreach ($mik_arp_list as $row) {
                // ARP: .id	address	mac-address	interface	published	invalid	DHCP	dynamic	complete	disabled
                if($row['address'] == $ip) {
                    self::$CASHE_MAC_IN_ARP_BY_IP[$ip] = $row;
                }
            }
        }
        return self::$CASHE_MAC_IN_ARP_BY_IP[$ip];
    }


    public static function get_mac_from_arp_by_ip(MikroLink $mik, null|string $ip, bool $disconect_on_end = false): array|string|null {

        /**
         * Выполним команду print с фильтром адреса
         * В параметрах массива мы указываем фильтр вида '=address=192.168.88.50', 
         * что аналогично where address=192.168.88.50.
         */
        $response = $mik->exec('/ip/arp/print', ['?address' => $ip]);

        if (!empty($response) && is_array($response)) {
            // Обычно возвращается массив строк-массивов
            $entry = $response[0];
            // echo "IP: " . ($entry['address'] ?? '') . "\n";
            // echo "MAC: " . ($entry['mac-address'] ?? '') . "\n";
            // echo "Interface: " . ($entry['interface'] ?? '') . "\n";
            // echo "Dynamic: " . ($entry['dynamic'] ?? '') . "\n";
        } else {
            $entry = null;
            // echo "Запись ARP по адресу {$ip} не найдена.\n";
        }

        if($disconect_on_end) { $mik->disconnect(); }
        return $entry;
    }
    


    /**
     * Возвращает собственное имя микротика
     * командой /system identity print
     */
    public static function get_mik_identity(MikroLink $mik, bool $disconect_on_end = false): string {
        $rez = $mik->exec('/system/identity/print');
        if (count($rez)>0) {
            if($disconect_on_end) { $mik->disconnect(); }
            return $rez[0]['name'];
        } else {
            self::$errors[] = "Не удалось получить данные идентификации микротика";
            return '';
        }
    }



    /**
     *  /system resource print
     *                  uptime: 3h33m37s
     *                 version: 6.47.1 (stable)
     *              build-time: Jul/08/2020 12:34:22
     *        factory-software: 6.36.1
     *             free-memory: 115.0MiB
     *            total-memory: 256.0MiB
     *                     cpu: MIPS 1004Kc V2.15
     *               cpu-count: 4
     *           cpu-frequency: 880MHz
     *                cpu-load: 26%
     *          free-hdd-space: 3352.0KiB
     *         total-hdd-space: 16.3MiB
     * write-sect-since-reboot: 3860
     *        write-sect-total: 3860
     *              bad-blocks: 0%
     *       architecture-name: mmips
     *              board-name: hEX
     *                platform: MikroTik
     *
     * @param $mik класс, подключенный к микротику
     * @param boolean $disconect_on_end
     * @return array
     */
    static function get_tp_resource(MikroLink $mik, bool $disconect_on_end = false): array|null {
        $rez = $mik->exec('/system/resource/print');
        if ($rez) {
            if($disconect_on_end) { $mik->disconnect(); }
            return $rez[0];
        } else {
            self::$errors[] = "Строка ответа пустая, что странно.";
            return null;
        }
    }



    static function get_tp_ip_address_list(MikroLink $mik, bool $disconect_on_end = false) {
        $rez = $mik->exec('/ip/address/print', array());
        if($disconect_on_end) { $mik->disconnect(); }
        if (!$rez) {
            self::$errors[] = "Роутер не настроен (на роутере нет IP-адресов, что странно).";
        }
        return $rez;
    }



    /**
     * Возвращает список указанной таблицы на микротике
     * Возвращает список из адресной таблицы микротика
     * @param MikroLink $mik -- подключенный АПИ микротика
     * @param string|null $address_list -- строка имена адресного листа
     * @param bool $disconect_on_end -- закрыть подключение к микротику по завершению
     * @return type BOOLEAN
     */
    static function get_tp_address_list(MikroLink $mik, string|null $address_list = null, bool $disconect_on_end = false): array {
        if (empty($address_list)) {
            $rez = $mik->exec('/ip/firewall/address-list/print');
        } else {
            $rez = $mik->exec('/ip/firewall/address-list/print',
                    [
                        "?list"=>$address_list,
                    ]);
        }
        if($disconect_on_end) { $mik->disconnect(); }
        if (!$rez) { self::$errors[] = "Таблица адресов пуста."; }
        return $rez;
    }



    static function get_tp_gateways(MikroLink $mik, bool $disconnect_on_end = false): array {
        $rez = $mik->exec('/ip/route/print',
                [
                    "?dst-address"=>"0.0.0.0/0"
                ]);
        if($disconnect_on_end) { $mik->disconnect(); }
        if (!$rez) {
            self::$errors[] = "Возможно, нет записей в таблице /ip/route, что странно.";
            return [];
        }
        return $rez;
    }



    static function get_tp_firewall_filter_all(MikroLink $mik, bool $disconnect_on_end = false): array {
        $rez = $mik->exec('/ip/firewall/filter/print');
        if($disconnect_on_end) { $mik->disconnect(); }
        if (!$rez) {
            self::$errors[] = "Возможно, нет записей в списке правил, что странно.";
            return [];
        }
        return $rez;
    }



    static function get_tp_nat(MikroLink $mik, bool $disconnect_on_end = false) {
        $rez = $mik->exec('/ip/firewall/nat/print');
        if($disconnect_on_end) { $mik->disconnect(); }
        if (!$rez) {
            self::$errors[] = "Возможно, нет записей в NAT-таблице, что странно.";
            return [];
        }
        return $rez;
    }



    /**
     * Получение данных о выдачах DHCP-сервера
     * @return array of DHCP-leases
     */
    static function get_tp_dhcp_leases_all($mik, bool $disconnect_on_end = false): array {
        $rez = $mik->exec('/ip/dhcp-server/lease/print');
        if($disconnect_on_end) { $mik->disconnect(); }
        if (!$rez) {
            self::$errors[] = "Возможно, нет записей в DHCP-leases-таблице, что странно.";
            return [];
        }
        return $rez;
    }



    static function get_tp_arp_all($mik, bool $disconnect_on_end = false) {
        $rez = $mik->exec('/ip/arp/print');
        if($disconnect_on_end) { $mik->disconnect(); }
        if (count($rez)>0) {
            // v.6 -- "DHCP"
            // v.7 -- "dhcp"
            // "DHCP" --> "dhcp"
            foreach ($rez as &$row) {
                if (array_key_exists("DHCP", $row)) {
                    $row["dhcp"] = $row["DHCP"];
                    unset($row["DHCP"]);
                }
            }
            return $rez;
        } else {
            self::$errors[] = "Возможно, нет записей в АРП-таблице, что странно.";
            return [];
        }
    }



    /**
     * /ip firewall/address-list
     * Есть ли IP в переданном адрес-листе c указанными параметрами
     * @param array $address_list
     * @param string $ip
     * @param string|null $address_list_name
     * @param string|null $disabled
     * @return bool
     */
    public static function has_ip_in_address_list(
            array &$address_list,
            string|null $ip,
            string|null $address_list_name = null,
            string|null $disabled = null): bool {
        if (is_empty($ip)) {
            return false;
        }
        // .id  list    address   creation-time        dynamic disabled comment
        // *259 TRUSTED 10.1.1.57 nov/19/2022 00:23:41 false   false    IP EXT 509 FRANSUA
        foreach ($address_list as $row) {
            if  (
                    ($row['address'] == $ip)
                    && (is_empty($address_list_name) ? true : ($row['list']     == $address_list_name))
                    && (is_null($disabled)           ? true : ($row['disabled'] == $disabled))
                )
            {
                return true;
            }
        }
        return false;
    }



    //print_r($statusAbons[1]); echo "<br>";
    //[.id] [list] [address] [creation-time] [dynamic] [disabled] [comment] [comment_abon_id]
    public static function has_ip_in_address_list_enabled($list, $ip) {
        return self::has_ip_in_address_list(address_list: $list, ip: $ip, disabled: 'false');
    }



    public static function get_status_mac_from_arp_by_ip(array $mik_arp_list, string|null $ip, string $on_not_found = "<font face=monospace color=gray title='MAC-адрес не найден в таблице ARP'>[________] __:__:__:__:__:__</font>"): string {
    //    if ($ip == '172.16.12.120') {
    //        echo "ip: $ip<br>";
    //        echo "ARP<pre>:".print_r($mik_arp_list, true)."</pre><br>";
    //        exit;
    //    }
        if (!is_empty($ip)) {
            if (validate_ip($ip)) {
                // ARP: .id	address	mac-address	interface	published	invalid	DHCP	dynamic	complete	disabled
                $mac_rec = self::get_mac_from_arp_list_by_ip($mik_arp_list, $ip);
                if ($mac_rec) {
                    return (validate_mac($mac_rec['mac-address'])
                            ? self::get_status_mac_from_arp_rec($mac_rec)
                            : "<font color=blue>WARN:</font> IP есть, а МАКа нет");
                } else {
                    return $on_not_found;
                }
                // unset($mac_rec);
            } else {
                if (is_ip_net($ip)) {
                    return "IP-подсеть";
                } else {
                    return "<font color=red>Ошибка:</font> IP-адрес не верен";
                }
            }
        } else {
            return "<font color=red>Ошибка:</font> IP-адрес не указан";
        }
    }



    public static function get_status_mac_in_arp(array $arp_list, string|null $mac): string {
        if (validate_mac($mac)) {
            foreach ($arp_list as $row) {
                // ARP: .id address mac-address interface published invalid DHCP dynamic complete disabled
                if ($row['mac-address'] == $mac) {
                    return self::get_status_mac_from_arp_rec($row, title_prefix: "Поиск MAC-адреса таблице MIK:ARP:\n");
                }
            }
            return paint("[________] __:__:__:__:__:__", color: GRAY, face: "monospace", title: "MIK:ARP: MAC-адрес {$mac} в таблице ARP не найден.");
        }
        return paint("Ошибка:", RED)." MAC-адрес не верен";
    }




    /**
     * Возвращает строку вида "[IDCX] 28:6C:07:CF:F4:F7"
     * @param array $arp_rec
     * @return string
     */
    public static function get_status_mac_from_arp_rec(array $arp_rec, string $title_prefix=""): string {
        /*
         *  ARP REC:
         *  [.id] => *57
         *  [address] => 10.1.1.252
         *  [mac-address] => E4:8D:8C:75:6B:95
         *  [interface] => bridge1
         *  [published] => false
         *  [status] => permanent|stale|reachable (постоянный|устаревший|доступный) // v.7
         *  [invalid] => false
         *  [DHCP] => false
         *  [dynamic] => true
         *  [complete] => true
         *  [disabled] => false
         *  все поля строковые.
         */
        if (!empty($arp_rec['mac-address'])) {
            if (validate_mac($arp_rec['mac-address'])) {
                $title = "[ I|V ] Invalid|Valid \n[ D|S ] Dynamic|Static \n[ C ] Complete \n[ X|E ] Disabled|Enabled";
                //debug("_R:", $arp_rec, "<hr>");
                // ARP: .id address mac-address interface published invalid DHCP dynamic complete disabled
                $idcx = "";
                $idcx .= ($arp_rec['invalid']  == "true"? paint("I", RED)   : paint("V", GREEN));
                $idcx .= ($arp_rec['dynamic']  == "true"? paint("D", GREEN) : paint("S", BLUE));
                $idcx .= ($arp_rec['complete'] == "true"? paint("C", GREEN) : paint("C", RED));
                $idcx .= ($arp_rec['disabled'] == "true"? paint("X", RED)   : paint("E", GREEN));
                $idcx .= ($arp_rec['dhcp'] == "true" ? paint("H", RED, title: 'Из [DHCP] возможно не реальное значение')   : paint("H", GREEN, title: 'Не из [DHCP]'));
                if (isset($arp_rec['status'])) {
                    switch ($arp_rec['status']) {
                        case "permanent": // постоянный
                            $idcx .= paint("[P]", BLUE,  title: "status: permanent / постоянный");
                            break;
                        case "stale": // устаревший
                            $idcx .= paint("[S]", RED,   title: "status: stale / устаревший");
                            break;
                        case "reachable": // доступный
                            $idcx .= paint("[R]", GREEN, title: "status: reachable / доступный");
                            break;
                        case "delay": // задержка
                            $idcx .= paint("[D]", GRAY,  title: "status: delay / задержка");
                            break;
                        default:
                            $idcx .= paint("[_]", GRAY,  title: "status: {$arp_rec['status']}");
                            break;
                    }
                } else {
                    $idcx .= paint("[_]", GRAY,  title: "поля status нет (firmvare v.6.x)");
                }

                return
                    paint("[{$idcx}] "
                        . paint($arp_rec['mac-address'],
                            color:  (
                                        $arp_rec['disabled'] == 'true' ||
                                        (isset($arp_rec['status']) ? ($arp_rec['status'] == 'stale') : 0)
                                        ? GRAY
                                        : BLACK
                                    )
                        ),
                        title: "{$title_prefix}IP: {$arp_rec['address']}\nMAC: {$arp_rec['mac-address']}\n{$title}",
                        face: 'monospace');
            } else {
                return paint(paint("Ошибка:", RED)." MAC-адрес не верен", title: "*  [address] => ".($arp_rec['address'] ?? '')."\n*  [mac-address] => ".($arp_rec['mac-address'] ?? '')."\n*  [interface] => ".($arp_rec['interface'] ?? '')."" );
            }
        } else {
            return paint(paint("Ошибка:", RED)." MAC-адреса нет", title: "*  [address] => ".($arp_rec['address'] ?? '')."\n*  [mac-address] => ".($arp_rec['mac-address'] ?? '')."\n*  [interface] => ".($arp_rec['interface'] ?? '')."" );
        }
    }





















}