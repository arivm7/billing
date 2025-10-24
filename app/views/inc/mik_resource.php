<?php
/*
 *  Project : my.ri.net.ua
 *  File    : mik_resource.php
 *  Path    : app/views/inc/mik_resource.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of mik_resource.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

declare(strict_types=1);
use config\Mik;

/**
 *
    [platform] => MikroTik
    [board-name] => LHG 5
    [architecture-name] => mipsbe

    [cpu] => MIPS 74Kc V4.12
    [cpu-count] => 1
    [cpu-frequency] => 600

    [version] => 6.49.18 (long-term)
    [factory-software] => 6.40
 *
 *  ------------------------------------
 *
    [uptime] => 25m52s

    [free-memory] => 43454464
    [total-memory] => 67108864

    [cpu-load] => 28

    [free-hdd-space] => 2822144
    [total-hdd-space] => 16777216

    [bad-blocks] => 0
 *
 *  ------------------------------------
 *  не использованные
 *
    [build-time] => Feb/27/2025 15:58:10
    [write-sect-since-reboot] => 871
    [write-sect-total] => 6005120
 *
 */

/** @var array $out_tables */
/** @var array $mik_rec */
/** @var array $bill_rec */
$mik_resource = $mik_rec['resourse'];
?>
<table class="table table-bordered table-hover table-striped w-auto" >
    <thead>
        <tr>
            <th colspan="5">
                <?=$mik_resource[Mik::RES_PLATFORM];?>,
                <span class="text-success"><?=$mik_resource[Mik::RES_BOARD_NAME];?></span>,
                <?=$mik_resource[Mik::RES_ARCHITECTURE_NAME];?> |
                CPU: <span class="text-primary"><?=$mik_resource[Mik::RES_CPU];?></span> x<span class="text-success"><?=$mik_resource[Mik::RES_CPU_COUNT];?></span>, <span class="text-success"><?=$mik_resource[Mik::RES_CPU_FREQUENCY];?></span>MHz |
                OS: <font class="text-primary"><?=$mik_resource[Mik::RES_VERSION];?></font> | Factory: <span class="text-success"><?=$mik_resource[Mik::RES_FACTORY_SOFTWARE] ?? "N/A";?></span>
            </th>
        </tr>
        <tr>
            <th>uptime</th>
            <th>free memory, Mb</th>
            <th>cpu-load</th>
            <th>free hdd, Mb</th>
            <th>bad-blocks</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?=$mik_resource[Mik::RES_UPTIME];?></td>
            <td><?=number_format(floatval($mik_resource['free-memory'])/1024/1024, 2, ".", " ")." / "
                    . number_format(floatval($mik_resource['total-memory'])/1024/1024, 2, ".", " ");?></td>
            <td><?=$mik_resource[Mik::RES_CPU_LOAD];?> %</td>
            <td><?=number_format(floatval($mik_resource['free-hdd-space'])/1024/1024, 2, ".", " ")." / "
                    . number_format(floatval($mik_resource['total-hdd-space'])/1024/1024, 2, ".", " ");?></td>
            <td><?=(isset($mik_resource['bad-blocks'])
                            ? "<font color=" . get_this_by_sign($mik_resource['bad-blocks'], "gray", "green", "red") . ">" . $mik_resource['bad-blocks'] . "</font>"
                            : "");?></td>
        </tr>
    </tbody>
</table>
<?php unset($mik_resource); ?>