<?php
/*
 *  Project : my.ri.net.ua
 *  File    : combineView.php
 *  Path    : app/views/Api/combineView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of combineView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\Api;
use config\Mik;
use config\tables\TP;

//
//    /**
//     * Константы для именования ключей таблиц из базы
//     */
//    public const BILL_TP      = 'tp';
//    public const BILL_PA_LIST = 'pa_list';
//
//    /**
//     * Константы для именования ключей таблиц из микротика
//     */
//    public const MIK_IDENTITY   = 'identity';   // Строка идентификации микротика
//    public const MIK_RESOURSE   = 'resourse';   // таблица технических параметров устройства
//    public const MIK_IP_LIST    = 'ip_list';    // таблицы /ip/address содержит IP-адреса устройства
//    public const MIK_ADDR_LIST  = 'addr_list';  // таблицы адресных листов, содержит подтаблицы по именам листов
//    public const MIK_ARP_LIST   = 'arp_list';   // таблицы адресных листов, содержит подтаблицы по именам листов
//    public const MIK_GATES      = 'gates';      // шлюзы устройтва
//    public const MIK_NAT        = 'nat';        // таблица NAT устройства
//    public const MIK_LEASES     = 'leases';     // таблийца DHCP LEASES (выданных адресов)
//
//
//    /**
//     * Константы для именования ключей выходных таблиц в общем массиве
//     */
//    public const OUT_PA     = 'OUT_PA';
//    public const OUT_ABON   = 'OUT_ABON';
//    public const OUT_LEASES = 'OUT_LEASES';
//    public const OUT_NAT    = 'OUT_NAT';
//    public const OUT_ARP    = 'OUT_ARP';
//

/** @var array $out_tables */
/** @var array $mik_rec */
/** @var array $bill_rec */

//debug($out_tables, '$out_tables', debug_view: DebugView::PRINTR);

$tp = $bill_rec[Api::BILL_TP];

?>
<div class="container-fluid">
    <h1 class="fs-2" title="id ТП в Базе"><span class="text-secondary"><?=$tp[TP::F_ID];?></span> | <span title="Название ТП в Базе"><?=$tp[TP::F_TITLE];?></span> | <span title="Идентификация на оборудовании"><?=$mik_rec['identity'];?></span> |</h1>
    <?php include DIR_INC . '/mik_resource.php'; ?>

    <div class="accordion" id="accCombineMain">
        <!-- Сырые таблицы -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading1">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div><span class="text-success fs-5">Сырые таблицы из микротика</span></div>
                        <div><span class="text-secondary fs-7">Количество таблиц: <?= count($mik_rec['addr_list']); ?>&nbsp;|&nbsp;&nbsp;</span></div>
                    </div>
                </button>
            </h2>
            <div id="collapse1" class="accordion-collapse collapse show"
                 aria-labelledby="heading1" data-bs-parent="#accCombineMain">
                <div class="accordion-body">
                    <?php include DIR_INC . '/mik_raw_tables.php'; ?>
                </div>
            </div>
        </div>
        <!-- Аналитические таблицы -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div><span class="text-success fs-5">Аналитические таблицы</span></div>
                        <div><span class="text-secondary fs-7">Количество таблиц: <?= count($out_tables); ?>&nbsp;|&nbsp;&nbsp;</span></div>
                    </div>
                </button>
            </h2>
            <div id="collapse2" class="accordion-collapse collapse"
                 aria-labelledby="heading2" data-bs-parent="#accCombineMain">
                <div class="accordion-body">
                    <?php include DIR_INC . '/mik_out_tables.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <hr>
</div>




<!--


-->