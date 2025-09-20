<?php


use config\Api;
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

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button"
                  data-bs-toggle="collapse" data-bs-target="#acc_raw_tables"
                  aria-expanded="false" aria-controls="acc_raw_tables">
              <span class="fs-5">Сырые таблицы из микротика | Количество таблиц: <?= count($mik_rec['addr_list']);?></span>
          </button>
        </h2>
        <div id="acc_raw_tables" class="accordion-collapse collapse">
          <div class="accordion-body">
            <?php include DIR_INC . '/mik_raw_tables.php'; ?>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <?php include DIR_INC . '/mik_out_tables.php'; ?>

</div>




<!--


-->
