<?php 
/**
 *  Project : my.ri.net.ua
 *  File    : monocard_pay_rec_form.php
 *  Path    : app/views/inc/monocard_pay_rec_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 22 Mar 2026 15:25:09
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вывод вормы одной записи для сохранения транзакции в биллинг.
 * Если запись есть в биллинге то просто выполняется сверка поей.
 * 
 * BankController.php 
 * BankController::monocardAction() -> 
 *      monocardView.php ->
 *              monocard_card_list.php
 *              monocard_statement.php
 *              monocard_pay_rec_form.php (этот)
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




/**
 * Данные переданные из контроллера
 * 
 * @var array{connect:array,client:array} $cards_info
 * @var array{connect:array,statements:array} $data
 * @var int $date1 -- int, timestamp, начало периода выборки
 * @var int $date2 -- int, timestamp, конец периода выборки
 * @var int $date_last_pay -- int, timestamp, Дата последнего зарегистрированного платежа на ППП
 * @var array $ppp
 * 
 * Данные переданные из monocardView.php
 * 
 * @var array $statement    -- одна запись банковской транзакции (переменная цикла)
 * 
 */

use config\Bank;
use config\MonoCard;
use config\tables\Pay;

/**
 * Сгенерированная запись для внесени или сравнения
 */
$pay_rec = &$statement[Bank::F_PAY_REC];

/**
 * Найденная в билинге запись
 * @var array{on_billing: true, 
 *      searched_on: string, 
 *      pay: array, 
 *      abon: array, 
 *      aid_list: array, 
 *      template: string} $found_rec
 */
$found_rec = &$statement[Bank::F_FOUND_REC];

?>

<div class="card mb-3 shadow-sm min-w-400">

    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <!-- PAY ID -->
            <small class="text-muted" title="<?= __('Дата транзакции') ?>" ><?= date('d.m.Y H:i:s', $pay_rec[Pay::F_DATE]) ?></small><br>
            <strong>Pay ID:</strong> <?= MonoCard::get_view_field($index, Pay::F_ID, $statement) ?>
        </div>
        <div>
            <!-- PAY ACNT -->
            <strong>Pay Fakt: </strong><?= MonoCard::get_view_field($index, Pay::F_PAY_FAKT, $statement) ?> ₴<br>
            <strong>Pay Acnt: </strong><?= MonoCard::get_view_field($index, Pay::F_PAY_ACNT, $statement) ?> ₴
        </div>
    </div>

    <div class="card-body">

        <div class="row g-3">
            <!-- Дата операции -->
            <div class="col-3"><strong>Дата транзакции:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_DATE, $statement) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- ABON ID -->
            <div class="col-3"><strong>Abon ID:</strong></div>
            <div class="col-9"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_ABON_ID, $statement) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- BANK NO -->
            <div class="col-3"><strong>Bank No:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_BANK_NO, $statement) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- PAY FAKT -->
            <div class="col-3"><strong>Pay Fakt:</strong></div>
            <div class="col-3"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_PAY_FAKT, $statement) ?></span> ₴</div>
        </div>

        <div class="row g-3">
            <!-- PAY ACNT -->
            <div class="col-3"><strong>Pay Acnt:</strong></div>
            <div class="col-3"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_PAY_ACNT, $statement) ?></span> ₴</div>
        </div>

        <div class="row g-3">
            <!-- DESCRIPTION -->
            <div class="col-3" title="<?= MonoCard::field_descr(MonoCard::F_DESCRIPTION) ?>"><strong>Description:</strong></div>
            <div class="col-9"><span><?= MonoCard::get_view_field($index, Pay::F_DESCRIPTION, $statement) ?></span></div>
        </div>
        
        <hr class="my-2">

        <div class="row g-3">
            <!-- ППП -->
            <div class="col-3"><strong>PAP:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_PPP_ID, $statement) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- TYPE PAY -->
            <div class="col-3"><strong>Type:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_TYPE_ID, $statement) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- AGENT ID -->
            <div class="col-3"><strong>Agent:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= MonoCard::get_view_field($index, Pay::F_AGENT_ID, $statement) ?></span></div>
        </div>

    </div>

</div>