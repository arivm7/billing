<?php
/*
 *  Project : my.ri.net.ua
 *  File    : mik_raw_tables.php
 *  Path    : app/views/inc/mik_raw_tables.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of mik_raw_tables.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\Mik;
use config\tables\TP;

//
//  $this->setVariables([
//      'mik' => $mik,
//               $mik['identity']
//               $mik['resourse']
//               $mik['addr_list']
//               $mik['gates']
//               $mik['nat']
//               $mik['leases']
//      'tp'  => $tp,
//  ]);
//

/** @var array $out_tables */
/** @var array $mik_rec */
/** @var array $bill_rec */

$mik_addr_list = $mik_rec['addr_list'];

?>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
    <?php foreach ($mik_addr_list as $list => $rows) : ?>
        <li class="nav-item" role="presentation">
            <!-- active -->
            <button class="nav-link"
                    id="tab-<?=$list;?>"
                    data-bs-toggle="tab"
                    data-bs-target="#pane-<?=$list;?>"
                    type="button"
                    role="tab"
                    aria-controls="pane-<?=$list;?>"
                    aria-selected="true">
                <?=$list;?><span class="text-secondary"> [<?=count($rows);?>]</span>
            </button>
        </li>
    <?php endforeach; ?>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content border border-top-0 p-3">
    <?php foreach ($mik_addr_list as $list => $rows) : ?>
        <!-- show active -->
        <div class="tab-pane fade"
             id="pane-<?=$list;?>"
             role="tabpanel"
             aria-labelledby="tab-<?=$list;?>">

            <h2 class="fs-4"><?=$list;?></h2>
            <table class="table table-bordered table-striped table-hover" >
                <tr>
                    <th>.id</th>
                    <!--<th>list</th>-->
                    <th>address</th>
                    <th>comment</th>
                    <th>[creation-time]</th>
                    <th>[timeout]</th>
                    <th>[dynamic]</th>
                </tr>
            <?php foreach ($rows as $item) : ?>
                <tr>
                    <td><?=$item['.id'];?></td>
                    <!--<td><?=$item['list'];?></td>-->
                    <?php if ($item['disabled'] == Mik::OFF) : ?>
                    <td><span class="text-info" title="Адрес активен"><?=$item['address'];?></span></td>
                    <?php else : ?>
                    <td><span class="text-secondary" title="Адрес отключён"><?=$item['address'];?></span></td>
                    <?php endif; ?>
                    <td><?=$item['comment'];?></td>
                    <td><?=$item[Mik::LIST_CREATION_TIME];?></td>
                    <td><?=$item[Mik::LIST_TIMEOUT] ?? '';?></td>
                    <td><?=$item[Mik::LIST_DYNAMIC];?></td>
                </tr>
            <?php endforeach; ?>
            </table>

        </div>
    <?php endforeach; ?>
    </div>