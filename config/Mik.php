<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Mik.php
 *  Path    : config/Mik.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 16:01:30
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config;

/**
 * Константы относящиеся к полям микротика
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Mik {


    const ON  = 'true';
    const OFF = 'false';

    const YES = 'yes';
    const NO  = 'no';

    

    /**
     * Списки (таблицы) /ip/firewall/address-list/
     */
    const L_ABON                = 'ABON';
    const L_HACKERS             = 'HACKERS';
    const L_DNS                 = 'DNS';
    const L_SERVICES            = 'SERVICES';
    const L_TRUSTED             = 'TRUSTED';
    
    const T_ARP                 = 'ARP';
    const T_LEASES              = 'LEASES';
    const T_NAT11               = 'NAT11';



    /**
     * поля структуры "address_list"
     */

    const F_LIST_ID             = '.id';            // [.id] => *1
    const F_LIST_LIST           = 'list';           // [list] => ABON
    const F_LIST_ADDRESS        = 'address';        // [address] => 0.0.0.0/0
    const F_LIST_CREATION_TIME  = 'creation-time';  // [creation-time] => jan/23/2024 01:57:02
    const F_LIST_TIMEOUT        = 'timeout';        // [timeout] =>
    const F_LIST_DYNAMIC        = 'dynamic';        // [dynamic] => false
    const F_LIST_DISABLED       = 'disabled';       // [disabled] => true
    const F_LIST_COMMENT        = 'comment';        // [comment] =>
    
    /**
     * Вычисляемыве поля
     */
    
    const F_LIST_ENABLED        = 'enabled';        // [enabled] -- вычисляемое поле



    /**
     * /system/resource/print
     */

    const F_RES_UPTIME                   =  'uptime';                    // [uptime] => 3h42m33s
    const F_RES_VERSION                  =  'version';                   // [version] => 6.49.18 (long-term)
    const F_RES_BUILD_TIME               =  'build-time';                // [build-time] => Feb/27/2025 15:58:10
    const F_RES_FACTORY_SOFTWARE         =  'factory-software';          // [factory-software] => 6.40
    const F_RES_FREE_MEMORY              =  'free-memory';               // [free-memory] => 42102784
    const F_RES_TOTAL_MEMORY             =  'total-memory';              // [total-memory] => 67108864
    const F_RES_CPU_NAME                 =  'cpu';                       // [cpu] => MIPS 74Kc V4.12
    const F_RES_CPU_COUNT                =  'cpu-count';                 // [cpu-count] => 1
    const F_RES_CPU_FREQUENCY            =  'cpu-frequency';             // [cpu-frequency] => 600
    const F_RES_CPU_LOAD                 =  'cpu-load';                  // [cpu-load] => 18
    const F_RES_FREE_HDD_SPACE           =  'free-hdd-space';            // [free-hdd-space] => 2756608
    const F_RES_TOTAL_HDD_SPACE          =  'total-hdd-space';           // [total-hdd-space] => 16777216
    const F_RES_WRITE_SECT_SINCE_REBOOT  =  'write-sect-since-reboot';   // [write-sect-since-reboot] => 1909
    const F_RES_WRITE_SECT_TOTAL         =  'write-sect-total';          // [write-sect-total] => 6003566
    const F_RES_BAD_BLOCKS               =  'bad-blocks';                // [bad-blocks] => 0
    const F_RES_ARCHITECTURE_NAME        =  'architecture-name';         // [architecture-name] => mipsbe
    const F_RES_BOARD_NAME               =  'board-name';                // [board-name] => LHG 5
    const F_RES_PLATFORM                 =  'platform';                  // [platform] => MikroTik



    /**
     * Поля массива /ip/arp
     */
    
    const F_ARP_ID        = '.id';          //
    const F_ARP_IP        = 'address';      // 192.168.77.252
    const F_ARP_MAC       = 'mac-address';  // CC:D3:C1:E6:7A:29
    const F_ARP_COMMENT   = 'comment';      // Текст
    const F_ARP_INTERFACE = 'interface';    // bridge
    const F_ARP_DYNAMIC   = 'dynamic';      // =no  D - DYNAMIC
    const F_ARP_DISABLED  = 'disabled';     // =no  X - DISABLED
    const F_ARP_PUBLISHED = 'published';    // =no  P - PUBLISHED
    const F_ARP_COMPLETE  = 'complete';     // =yes C - COMPLETE
    const F_ARP_DHCP      = 'dhcp';         // =yes H - DHCP
    const F_ARP_INVALID   = 'invalid';      // =no  I - INVALID
    const F_ARP_STATUS    = 'status';       // ="failed", "permanent", "reachable", "stale"
    const F_ARP_VRF       = 'vrf';          // =main
    
    const F_ARP_ENA       = 'ena';          // поле для нормализованной записи (скорее всего не поадобится)
    const F_ARP_ENABLED   = 'enabled';      // поле для статистики
    const F_ARP_TOTAL     = 'total';        // поле для статистики
    const F_ARP_INTERFACES = 'interfaces';  // поле для статистики


    /**
     * Константы для полей поиска и обновления
     */
    
    const F_SEARCH_LIST  = 'list';
    const F_SEARCH_IP    = 'ip';
    const F_SEARCH_ENA   = 'ena';
    const F_SEARCH_DESCR = 'descr';
    const F_SEARCH_ID    = 'id';
    
    const F_UPDATE_LIST  = 'list';
    const F_UPDATE_IP    = 'ip';
    const F_UPDATE_ENA   = 'ena';
    const F_UPDATE_DESCR = 'descr';


    /**
     * Константы для полей таблицы статистики адресных листов
     */
    const F_STAT_TOTAL      = 'total';
    const F_STAT_ENABLED    = 'enabled';
    const F_STAT_DISABLED   = 'disabled';
    const F_STAT_DYNAMIC    = 'dynamic';
    const F_STAT_STATIC     = 'static';
    const F_STAT_INVALID    = 'invalid';
    const F_STAT_INTERFACES = 'by_interfaces';
    
    // для F_DHCP_LEASE_STATUS  
    const F_STAT_STATUS     = 'status';     // массив для статистики статусов
    const F_STAT_BLOCKED    = 'blocked';    // статистика записей блокировки
    // [status] => bound, waiting, offered, busy, testing, conflict, declined
    const F_STAT_BOUND      = 'bound';      // связанный
    const F_STAT_WAITING    = 'waiting';    // ожидающий
    const F_STAT_OFFERED    = 'offered';    // предложенный
    const F_STAT_BUSY       = 'busy';       // занятый
    const F_STAT_TESTING    = 'testing';    // тестирующий
    const F_STAT_CONFLICT   = 'conflict';   // конфликт
    const F_STAT_DECLINED   = 'declined';   // отклоненный

    
    
    /**
     * Константы для /ip/address
     */
    
    const F_ADDR_ID         = '.id';
    const F_ADDR_ADDRESS    = 'address';
    const F_ADDR_NETWORK    = 'network';
    const F_ADDR_IP         = 'ip';
    const F_ADDR_PREFIX     = 'mask';
    const F_ADDR_INTERFACE  = 'interface';
    const F_ADDR_COMMENT    = 'comment';
    const F_ADDR_DISABLED   = 'disabled';
    const F_ADDR_ENA        = 'ena';
    const F_ADDR_DYNAMIC    = 'dynamic';
    const F_ADDR_INVALID    = 'invalid';


    /**
     * Константы для NAT
     */

    // .id	chain	action	to-addresses    out-interface-list src-address in-interface-list out-interface	dst-address     to-ports protocol src-address-list in-interface	dst-port log   log-prefix bytes     packets invalid dynamic disabled comment
    // *2	dstnat	netmap	10.1.1.57	*                  *           *                 *              159.224.135.200 *        *        *                *            *        false *          431129145 6362226 false   false   false    NAT 509 FARNSUA
    // *3	srcnat	netmap	159.224.135.200 *                  10.1.1.57   *                 *              *               *        *        *                *            *        false *          21092371  86029   false   false   false    NAT 509 FARNSUA
    
    const F_NAT_ID                  = '.id';
    const F_NAT_CHAIN               = 'chain';
    const F_NAT_ACTION              = 'action';	
    const F_NAT_TO_ADDRESSES        = 'to-addresses';    
    const F_NAT_OUT_INTERFACE_LIST  = 'out-interface-list'; 
    const F_NAT_SRC_ADDRESS         = 'src-address'; 
    const F_NAT_IN_INTERFACE_LIST   = 'in-interface-list'; 
    const F_NAT_OUT_INTERFACE       = 'out-interface';	
    const F_NAT_DST_ADDRESS         = 'dst-address';     
    const F_NAT_TO_PORTS            = 'to-ports'; 
    const F_NAT_PROTOCOL            = 'protocol'; 
    const F_NAT_SRC_ADDRESS_LIST    = 'src-address-list'; 
    const F_NAT_IN_INTERFACE        = 'in-interface';	
    const F_NAT_DST_PORT            = 'dst-port'; 
    const F_NAT_LOG                 = 'log';   
    const F_NAT_LOG_PREFIX          = 'log-prefix';
    const F_NAT_BYTES               = 'bytes';
    const F_NAT_PACKETS             = 'packets'; 
    const F_NAT_INVALID             = 'invalid'; 
    const F_NAT_DYNAMIC             = 'dynamic'; 
    const F_NAT_DISABLED            = 'disabled'; 
    const F_NAT_COMMENT             = 'comment';
    
    const F_NAT_ENA     = 'ena';
    
    
    
    /**
     * Константы для DHCP LEASE
     */
    
    /*
     * [.id] => *5 [address] => 10.1.17.197 [mac-address] => 84:16:F9:91:77:93 [client-id] => 1:84:16:f9:91:77:93
     * [address-lists] => [server] => default [always-broadcast] => true [dhcp-option] => [status] => bound
     * [expires-after] => 8m40s [last-seen] => 1m20s [active-address] => 10.1.17.197 [active-mac-address] => 84:16:F9:91:77:93
     * [active-client-id] => 1:84:16:f9:91:77:93 [active-server] => default [host-name] => TL-WR840N [radius] => false
     * [dynamic] => false [blocked] => false [disabled] => false [comment] => 17101 PivnayaLavka
     */
    
    const F_DHCP_LEASE_ID                   = '.id';                // [.id] => *5
    const F_DHCP_LEASE_ADDRESS              = 'address';            // [address] => 10.1.17.197
    const F_DHCP_LEASE_MAC                  = 'mac-address';        // [mac-address] => 84:16:F9:91:77:93
    const F_DHCP_LEASE_CLIENT_ID            = 'client-id';          // [client-id] => 1:84:16:f9:91:77:93
    const F_DHCP_LEASE_SERVER               = 'server';             // [server] => default
    const F_DHCP_LEASE_STATUS               = 'status';             // [status] => bound, waiting, offered, busy, testing, conflict, declined
    const F_DHCP_LEASE_DYNAMIC              = 'dynamic';            // [dynamic] => false
    const F_DHCP_LEASE_BLOCKED              = 'blocked';            // [blocked] => false
    const F_DHCP_LEASE_DISABLED             = 'disabled';           // [disabled] => false
    const F_DHCP_LEASE_ENA                  = 'ena';                // Вычисляемое поле !disabled
    const F_DHCP_LEASE_COMMENT              = 'comment';            // [comment] => 17101 PivnayaLavka
    
    const F_DHCP_LEASE_HOSTNAME             = 'host-name';          // [host-name] => TL-WR840N
    const F_DHCP_LEASE_ALWAYS_BROADCAST     = 'always-broadcast';   // [always-broadcast] => true
    const F_DHCP_LEASE_ADDRESS_LISTS        = 'address-lists';      // [address-lists] => 
    const F_DHCP_LEASE_DHCP_OPTION          = 'dhcp-option';        // [dhcp-option] => 
    const F_DHCP_LEASE_EXPIRES_AFTER        = 'expires-after';      // [expires-after] => 8m40s
    const F_DHCP_LEASE_LAST_SEEN            = 'last-seen';          // [last-seen] => 1m20s
    const F_DHCP_LEASE_ACTIVE_ADDRESS       = 'active-address';     // [active-address] => 10.1.17.197
    const F_DHCP_LEASE_ACTIVE_MAC_ADDRESS   = 'active-mac-address'; // [active-mac-address] => 84:16:F9:91:77:93
    const F_DHCP_LEASE_ACTIVE_CLIENT_ID     = 'active-client-id';   // [active-client-id] => 1:84:16:f9:91:77:93
    const F_DHCP_LEASE_ACTIVE_SERVER        = 'active-server';      // [active-server] => default
    const F_DHCP_LEASE_RADIUS               = 'radius';             // [radius] => false
    
    
    
    
    
}