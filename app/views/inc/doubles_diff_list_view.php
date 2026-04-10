<?php
/**
 *  Project : my.ri.net.ua
 *  File    : doubles_diff_list_view.php
 *  Path    : app/views/inc/doubles_diff_list_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 10 Apr 2026 16:03:27
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of doubles_diff_list_view.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 *  Created : 08 Apr 2026 21:21:49
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вывод разницы записей
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\base\Lang;
use billing\core\Pagination;
use config\tables\Pay;

Lang::load_inc(__FILE__);



/**
 * Данные полученные из контроллера
 * 
 * @var string $title
 * @var array $filter
 * @var array $doubles
 * @var Pagination $pager
 * 
 */


function diff_cmp($v1, $v2, $field) {
    return match ($field) {
        
        Pay::F_PAY_ACNT,
        Pay::F_PAY_FAKT     => (float)$v1 === (float)$v2,

        Pay::F_DATE         => (int)$v1 === (int)$v2,

        default             => (string)$v1 === (string)$v2,

    };
}

?>

<?php foreach ($doubles as $pair): ?>

<?php
    $p1 = $pair[1];
    $p2 = $pair[2];

    $fields = [
        Pay::F_ID            => 'ID',
        Pay::F_DATE          => 'Дата',
        Pay::F_ABON_ID       => 'Абонент',
        Pay::F_PAY_ACNT      => 'Платёж (ЛС)',
        Pay::F_PAY_FAKT      => 'Факт',
        Pay::F_BANK_NO       => 'Bank №',
        Pay::F_TYPE_ID       => 'Тип',
        Pay::F_PPP_ID        => 'ППП',
        Pay::F_AGENT_ID      => 'Agent',
        Pay::F_DESCRIPTION   => 'Описание',
    ];

?>

<div class="card mb-3 shadow-sm">

    <!-- Header -->

        <!-- <tr bgcolor="#ffffcc">
            <th width="20">abon_id</th>
            <th width="60">pay ID | pay TIME | pay BANK_ID</th>
            <th width="20">pay1</th>
            <th width="20">pay2</th>
        </tr> -->
    
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            <?= date('Y-m-d H:i:s', $p1[Pay::F_DATE]) ?>
        </div>

        <div class="text-end">
            <span class="badge bg-secondary">#<?= $p1[Pay::F_ID] ?></span>
            <span class="badge bg-dark">#<?= $p2[Pay::F_ID] ?></span>
        </div>
    </div>

    <!-- Body -->
    <div class="card-body p-2">

        <?php include DIR_INC . '/doubles_diff_pair_view.php' ?>

    </div>

</div>

<?php endforeach; ?>