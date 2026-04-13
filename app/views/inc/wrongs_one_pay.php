<?php
/**
 *  Project : my.ri.net.ua
 *  File    : wrongs_one_pay.php
 *  Path    : app/views/inc/wrongs_one_pay.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 13 Apr 2026 00:01:30
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of wrongs_one_pay.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */





/**
 * Вывод одного платежа с ошибками
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Данные полученные из контроллера
 * 
 * @var string $title
 * @var array $filter
 * @var array $doubles
 * @var Pagination $pager
 * 
 * Данные для вывода ошибок в платежах
 * @var array $errors
 * 
 * Данные для вывода ошибок в платежах
 * @var string $type
 * @var array  $error_rec
 * 
 * Данные для вывода одного платежа
 * @var array $pay
 * 
 */

use config\Icons;
use config\tables\Pay;


if (!function_exists('hl')) {
    function hl(bool $cond): string {
        return $cond ? 'text-danger' : '';
    }
}


$flags = [
    'old_date'      => $pay[Pay::F_DATE] < strtotime('2011-01-01'),

    'bank_no_empty' => empty($pay[Pay::F_BANK_NO]),
    'abon_invalid'  => empty($pay[Pay::F_ABON_ID]),
    'type_invalid'  => empty($pay[Pay::F_TYPE_ID]),
    'ppp_invalid'   => empty($pay[Pay::F_PPP_ID]),

    'bad_amount'    => ($pay[Pay::F_PAY_FAKT] > 0 && $pay[Pay::F_PAY_ACNT] == 0),
    'zero_all'      => ($pay[Pay::F_PAY_FAKT] == 0 && $pay[Pay::F_PAY_ACNT] == 0),
];

// debug($pay, '$pay');

?>

<div class="card mb-2 p-2 shadow-sm">

    <!-- ROW 1 -->
    <div class="row align-items-center small mx-2 mt-1 mb-0 px-2 py-3">

        <!-- ID -->
        <div class="col-auto">
            <a class="btn btn-outline-success px-2 pt-1 pb-2" title='PAY: <?= h(print_r($pay, true)) ?>' href='<?= Pay::URI_FORM .'/'.$pay[Pay::F_ID] ?>' target=_blank ><img src='<?= Icons::SRC_ICON_UAH ?>' alt=PAY width=18 height=18></a>
        </div>

        <!-- DATE -->
        <div class="col-auto">
            <div class="text-secondary">Дата платежа:</div>
            <div class="<?= hl($flags['old_date']) ?>"><?= date('Y-m-d H:i:s', $pay[Pay::F_DATE]) ?></div>
        </div>

        <!-- ABON -->
        <div class="col-2 border-start ms-3 ps-3">
            <div>
                <span class="text-secondary">Abon: </span>
                <span class="<?= hl($flags['abon_invalid']) ?>"><?= $pay[Pay::F_ABON_ID] ? url_abon_form($pay[Pay::F_ABON_ID]) : 'NULL' ?></span>
            </div>
            <div>
                <span><?= $pay[Pay::F_ABON_ADDRESS] ?></span>
            </div>
        </div>

        <!-- BANK -->
        <div class="col-auto border-start ms-3 ps-3">
            <div class="text-secondary">Bank No: </div>
            <div class="<?= hl($flags['bank_no_empty']) ?>"><?= $pay[Pay::F_BANK_NO] ?: 'NO_BANK_NO' ?></div>
        </div>

        <!-- TYPE -->
        <div class="col-2 border-start ms-3 ps-3">
            <div>
                <span class="text-secondary">T: </span>
                <span class="<?= hl($flags['type_invalid']) ?>"><?= $pay[Pay::F_TYPE_ID] ?></span>
            </div>
            <div>
                <span><?= $pay[Pay::F_TYPE_TITLE] ?></span>
            </div>
        </div>

        <!-- PPP -->
        <div class="col-auto border-start ms-3 ps-3">
            <div>
                <span class="text-secondary">ППП: </span>
                <span class="<?= hl($flags['ppp_invalid']) ?>"><?= $pay[Pay::F_PPP_ID] ?></span>
            </div>
            <div>
                <span><?= $pay[Pay::F_PPP_TITLE] ?></span>
            </div>
        </div>

        <!-- AMOUNTS -->
        <div class="col-auto text-end font-monospace border-start ms-4 ps-4">
            <div>
                <span class="text-secondary">Pay FACT:</span>
                <span class="<?= hl($flags['bad_amount'] || $flags['zero_all']) ?>" 
                        style="white-space: pre;"
                        ><?= sprintf('%6.2f', $pay[Pay::F_PAY_FAKT]) ?></span>
            </div>
            <div>
                <span class="text-secondary">Pay ACNT:</span>
                <span class="<?= hl($flags['bad_amount'] || $flags['zero_all']) ?>" 
                        style="white-space: pre;"
                        ><?= sprintf('%6.2f', $pay[Pay::F_PAY_ACNT]) ?></span>
            </div>
        </div>
    </div>

    <!-- ROW 2: description -->
    <div class="row mx-2 my-3 px-2 py-3 border <?= (empty($pay[Pay::F_DESCRIPTION]) ? "bg-body-secondary" : "") ?>">
        <div class="col small font-monospace text-break">
            <?= h($pay[Pay::F_DESCRIPTION]) ?>
        </div>
    </div>

</div>