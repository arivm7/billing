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


    /**
     * Списки (таблицы)
     */
    const L_ABON           = 'ABON';            // [list] => ABON



    /**
     * поля структуры "address_list"
     */

    const LIST_ID             = '.id';             // [.id] => *1
    const LIST_LIST           = 'list';            // [list] => ABON
    const LIST_ADDRESS        = 'address';         // [address] => 0.0.0.0/0
    const LIST_CREATION_TIME  = 'creation-time';   // [creation-time] => jan/23/2024 01:57:02
    const LIST_TIMEOUT        = 'timeout';         // [timeout] =>
    const LIST_DYNAMIC        = 'dynamic';         // [dynamic] => false
    const LIST_DISABLED       = 'disabled';        // [disabled] => true
    const LIST_COMMENT        = 'comment';         // [comment] =>



    /**
     * /system/resource/print
     */

    const RES_UPTIME                   =  'uptime';                    // [uptime] => 3h42m33s
    const RES_VERSION                  =  'version';                   // [version] => 6.49.18 (long-term)
    const RES_BUILD_TIME               =  'build-time';                // [build-time] => Feb/27/2025 15:58:10
    const RES_FACTORY_SOFTWARE         =  'factory-software';          // [factory-software] => 6.40
    const RES_FREE_MEMORY              =  'free-memory';               // [free-memory] => 42102784
    const RES_TOTAL_MEMORY             =  'total-memory';              // [total-memory] => 67108864
    const RES_CPU                      =  'cpu';                       // [cpu] => MIPS 74Kc V4.12
    const RES_CPU_COUNT                =  'cpu-count';                 // [cpu-count] => 1
    const RES_CPU_FREQUENCY            =  'cpu-frequency';             // [cpu-frequency] => 600
    const RES_CPU_LOAD                 =  'cpu-load';                  // [cpu-load] => 18
    const RES_FREE_HDD_SPACE           =  'free-hdd-space';            // [free-hdd-space] => 2756608
    const RES_TOTAL_HDD_SPACE          =  'total-hdd-space';           // [total-hdd-space] => 16777216
    const RES_WRITE_SECT_SINCE_REBOOT  =  'write-sect-since-reboot';   // [write-sect-since-reboot] => 1909
    const RES_WRITE_SECT_TOTAL         =  'write-sect-total';          // [write-sect-total] => 6003566
    const RES_BAD_BLOCKS               =  'bad-blocks';                // [bad-blocks] => 0
    const RES_ARCHITECTURE_NAME        =  'architecture-name';         // [architecture-name] => mipsbe
    const RES_BOARD_NAME               =  'board-name';                // [board-name] => LHG 5
    const RES_PLATFORM                 =  'platform';                  // [platform] => MikroTik



}