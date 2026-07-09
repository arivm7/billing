<?php
/**
 *  Project : my.ri.net.ua
 *  File    : manageView.php
 *  Path    : app/views/Tp/manageView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Jun 2026 21:56:27
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вид для управления устройством микротик
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\App;
use config\tables\TP;
use config\tables\PA;
use config\tables\Abon;
use config\tables\User;
use config\tables\Price;

require_once DIR_LIBS . '/inc_functions.php';

/**
 * Данные из контроллера
 * 
 * @var array $out_tables
 * [
 *    [PA::TABLE] = [];
 *    [Abon::TABLE] = [];
 *    [Price::TABLE] = [];
 *    [Mik::T_ARP] = [];
 *    [Mik::T_LEASES] = [];
 *    [Mik::T_NAT11] = []; // !!!
 * ]
 * 
 */

debug($out_tables, '$out_tables');

?>

<div class="mx-auto w-auto">
    <?php $order_no = 0; ?>
    <table <?= TABLE_ATTRIBUTES; ?>>
        <tr>
            <th>No пп</th>
            <th>Header2</th>
        </tr>
    <?php foreach ($out_tables[TP::F_OUT_PA] as $key => $row) : ?>
        <tr>
            <td><?= ++$order_no; ?></td>
            <td>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="font-monospace"><?= date('Y-m-d', $row[PA::TABLE][PA::F_DATE_START]) ?> - <?= $row[PA::TABLE][PA::F_DATE_END] ? date('Y-m-d', $row[PA::TABLE][PA::F_DATE_END]) : App::get_config('pa_date_no') ?></span>
                    </div>
                    <div>
                        <?= $row[Price::TABLE][Price::F_TITLE] ?> <?= url_pa_form($row[PA::TABLE][PA::F_ID]); ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <?= $row[Abon::TABLE][Abon::F_ADDRESS] ?>
                    </div>
                    <div>
                        <?= url_abon_form($row[Abon::TABLE][Abon::F_ID]); ?>
                    </div>
                </div>
                
            </td>
            <td>
                <!--
                [net_ip_service] => 1
                [net_on_abon_ip] => 
                [net_on_abon_mask] => 
                [net_on_abon_gate] => 
                [net_nat11] => 
                [net_ip] => 10.1.10.28
                [net_ip_trusted] => 0
                -->
                <?php if ($row[PA::TABLE][PA::F_NET_IP_SERVICE]): ?>
                
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?= PA::F_NET_IP ?> : <?= $row[PA::TABLE][PA::F_NET_IP] ?>
                        </div>
                        <div>
                            <?= status_ip_abon_img($row['VALIDATE']['ON_ABON']['ON'] ?? null); ?>
                        </div>
                    </div>
                    <?= PA::F_NET_NAT11 ?> : <?= $row[PA::TABLE][PA::F_NET_NAT11] ?><br>
                    <?= PA::F_NET_ON_ABON_IP ?> : <?= $row[PA::TABLE][PA::F_NET_ON_ABON_IP] ?><br>
                    <?= PA::F_NET_IP_TRUSTED ?> : <?= (int)$row[PA::TABLE][PA::F_NET_IP_TRUSTED] ?><br>
                <?php endif ?>
                <pre>
                <?= print_r($row[PA::TABLE] ?? null, true) ?>
                </pre>
            </td>
            <td>
                <pre>
                <?= status_ip_abon_img($row['VALIDATE']['ON_ABON']['ON'] ?? null); ?>
                <?= print_r($row['VALIDATE'] ?? null, true) ?>
                </pre>
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
    
</div>