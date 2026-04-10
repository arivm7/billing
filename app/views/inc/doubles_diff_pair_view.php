<?php
/**
 *  Project : my.ri.net.ua
 *  File    : doubles_diff_pair_view.php
 *  Path    : app/views/inc/doubles_diff_pair_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 10 Apr 2026 16:03:27
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of doubles_diff_pair_view.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\base\Lang;
use billing\core\Pagination;
use config\tables\Pay;
Lang::load_inc(__FILE__);

include_once DIR_LIBS . '/functions.php';
include_once DIR_LIBS . '/inc_functions.php';



/**
 * Данные полученные из контроллера
 * 
 * @var string $title
 * @var array $filter
 * @var array $doubles
 * @var Pagination $pager
 * 
 * Данные из doubles_diff_list_view.php
 * 
 * @var array $p1 -- запись платежа
 * @var array $p2 -- запись платежа
 * 
 */

?>


        <!-- <tr bgcolor="#eeeeee">
            <td><a href="/ad_abon1_card.php?abon_id=368277&amp;show_abon_id" target="_blank">368277</a></td>
            <td nowrap=""><a href="/ad_abon1_pay.php?edit_pay=26456" target="_blank">26456</a> | 2025-09-25 13:44:00 | 4423689840<br><a href="/ad_abon1_pay.php?edit_pay=26455" target="_blank">26455</a> | 2025-09-25 13:44:00 | 4423689861</td>
            <td align="right">
                <nobr>fkt: 372<br>acc: 372</nobr>
            </td>
            <td align="right">
                <nobr>fkt: 372<br>acc: 372</nobr>
            </td>
            <td></td>
        </tr>
        <tr bgcolor="#eeeeee">
            <td colspan="5">
                <font size="-3">
                    <pre>Доступ до мережі інтернет за 10/2025 р. зг. дог. 368277 м. Київ, вул. Махова, без ПДВ<hr>Доступ до мережі інтернет за 10/2025 р. зг. дог. 368277, без ПДВ</pre>
                </font>
            </td>
        </tr> -->


<div class="card mb-2 shadow-sm p-2">

    <!-- ROW 1: основные данные -->
    <div class="row align-items-center">

        <!-- Abon -->
        <div class="col-auto">
            <div>
                <?= url_abon_form($p1[Pay::F_ABON_ID]) ?>
            </div>
            <div>
                <?= url_abon_form($p2[Pay::F_ABON_ID]) ?>
            </div>
        </div>

        <!-- IDs + date + bank -->
        <div class="col">

            <div class="small font-monospace">
                <?= $p1[Pay::F_ID] ?> <?= url_pay_form($p1[Pay::F_ID]) ?>
                | <span class="<?= ($p1[Pay::F_DATE] == $p2[Pay::F_DATE] ? 'text-success' : 'text-warning') ?>">
                    <?= date('Y-m-d H:i:s', $p1[Pay::F_DATE]) ?>
                  </span>
                | BANK_NO: <span title="<?= Pay::field_title(Pay::F_BANK_NO) ?>"><?= $p1[Pay::F_BANK_NO] ?></span>
            </div>

            <div class="small font-monospace">
                <?= $p2[Pay::F_ID] ?> <?= url_pay_form($p2[Pay::F_ID]) ?>
                | <span class="<?= ($p1[Pay::F_DATE] == $p2[Pay::F_DATE] ? 'text-success' : 'text-warning') ?>">
                    <?= date('Y-m-d H:i:s', $p2[Pay::F_DATE]) ?>
                  </span>
                | BANK_NO: <span title="<?= Pay::field_title(Pay::F_BANK_NO) ?>"><?= $p2[Pay::F_BANK_NO] ?></span>
            </div>

        </div>

        <!-- суммы -->
        <div class="col-auto text-end small">

            <div class="<?= ($p1[Pay::F_PAY_FAKT] == $p2[Pay::F_PAY_FAKT] ? 'text-success' : 'text-warning') ?>">
                FAKT: <?= number_format($p1[Pay::F_PAY_FAKT], 2, '.', ' ') ?><br>
                FAKT: <?= number_format($p2[Pay::F_PAY_FAKT], 2, '.', ' ') ?>
            </div>

        </div>

        <div class="col-auto text-end small">

            <div class="<?= ($p1[Pay::F_PAY_ACNT] == $p2[Pay::F_PAY_ACNT] ? 'text-success' : 'text-warning') ?>">
                ACNT: <?= number_format($p1[Pay::F_PAY_ACNT], 2, '.', ' ') ?><br>
                ACNT: <?= number_format($p2[Pay::F_PAY_ACNT], 2, '.', ' ') ?>
            </div>

        </div>

    </div>

    <hr class="my-1">

    <!-- ROW 2: description -->
    <div class="row mt-2">

        <div class="col small font-monospace">

            <div class="<?= ($p1[Pay::F_DESCRIPTION] === $p2[Pay::F_DESCRIPTION] ? 'text-success' : 'text-warning') ?>">
                <?= h($p1[Pay::F_DESCRIPTION]) ?>
            </div>

            <hr class="my-1">

            <div class="<?= ($p1[Pay::F_DESCRIPTION] === $p2[Pay::F_DESCRIPTION] ? 'text-success' : 'text-warning') ?>">
                <?= h($p2[Pay::F_DESCRIPTION]) ?>
            </div>

        </div>

    </div>

</div>        