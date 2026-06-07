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
    const F_STAT_ENABLED = 'enabled'  ;
    const F_STAT_DISABLED = 'disabled' ;
    const F_STAT_DYNAMIC = 'dynamic'  ;
    const F_STAT_STATIC = 'static'   ;
    const F_STAT_TOTAL = 'total'    ;
    


}