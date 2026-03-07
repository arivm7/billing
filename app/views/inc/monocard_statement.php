<?php
/**
 *  Project : my.ri.net.ua
 *  File    : monocard_statement.php
 *  Path    : app/views/inc/monocard_statement.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 06 Feb 2026 00:37:45
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Отображение одной транзакции моно-карты в виде аккордеона
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\base\Lang;
Lang::load_inc(__FILE__);

use config\MonoCard;

/** @var array $statement -- получен из monocardView.php  */

$accordion_id = '_stmt_' . $statement[MonoCard::F_ID]; // уникальный ID
$amount = $statement[MonoCard::F_AMOUNT];
$balance = $statement[MonoCard::F_BALANCE];
$opAmount = $statement[MonoCard::F_OPERATION_AMOUNT];
$commission = $statement[MonoCard::F_COMMISSION_RATE];
$cashback = $statement[MonoCard::F_CASHBACK_AMOUNT];
$time = date('d.m.Y H:i:s', $statement[MonoCard::F_TIME]);
?>


<div class="card mb-3 shadow-sm"  style="width: fit-content;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <small class="text-muted" title="<?= MonoCard::description_field(MonoCard::F_TIME) ?>" ><?= $time ?></small><br>
            <strong title="<?= MonoCard::description_field(MonoCard::F_DESCRIPTION) ?>" ><?= h($statement[MonoCard::F_DESCRIPTION]) ?></strong>
        </div>

        <div class="text-end ms-3">
            <span class="fw-bold <?= $amount < 0 ? 'text-danger' : 'text-success' ?>" title="<?= MonoCard::description_field(MonoCard::F_AMOUNT) ?>" >
                <?= number_format($amount, 2) ?> ₴
            </span>
            <?php if (!empty($statement[MonoCard::F_HOLD])): ?>
                <span class="ms-2 badge bg-warning text-dark" title="<?= MonoCard::description_field(MonoCard::F_HOLD) ?>" >H</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">

        <div title="<?= MonoCard::description_field(MonoCard::F_ID) ?>"><strong>ID:</strong> <span class="font-monospace"><?= $statement[MonoCard::F_ID] ?></span></div>

        <div><strong title="<?= MonoCard::description_field(MonoCard::F_MCC) ?>" >MCC:</strong>
            <?= $statement[MonoCard::F_MCC] ?>
            <?php if ($statement[MonoCard::F_MCC] != $statement[MonoCard::F_ORIGINAL_MCC]): ?>
                <span class="text-muted" title="<?= MonoCard::description_field(MonoCard::F_ORIGINAL_MCC) ?>" >(orig <?= $statement[MonoCard::F_ORIGINAL_MCC] ?>)</span>
            <?php endif; ?>
        </div>

        <div title="<?= MonoCard::description_field(MonoCard::F_OPERATION_AMOUNT) ?>">
            <strong>Amount:</strong> <?= number_format($opAmount, 2) ?> ₴
        </div>

        <div title="<?= MonoCard::description_field(MonoCard::F_COMMISSION_RATE) ?>"><strong>Commission:</strong> <?= number_format($commission, 2) ?> ₴</div>

        <div title="<?= MonoCard::description_field(MonoCard::F_CASHBACK_AMOUNT) ?>"><strong>Cashback:</strong>
            <span class="<?= $cashback > 0 ? 'text-success' : 'text-muted' ?>">
                <?= number_format($cashback, 2) ?> ₴
            </span>
        </div>

        <hr class="my-2">

        <div title="<?= MonoCard::description_field(MonoCard::F_BALANCE) ?>"><strong>Balance after:</strong>
            <span class="fw-bold"><?= number_format($balance, 2) ?> ₴</span>
        </div>

        <?php if (!empty($statement[MonoCard::F_COMMENT])): ?>
            <div class="mt-2">
                <strong title="<?= MonoCard::description_field(MonoCard::F_COMMENT) ?>" >Comment:</strong><br>
                <?= nl2br(h($statement[MonoCard::F_COMMENT])) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($statement[MonoCard::F_COUNTER_NAME])): ?>
            <hr class="my-2">
            <div title="<?= MonoCard::description_field(MonoCard::F_COUNTER_NAME) ?>"><strong>Counterparty:</strong> <?= h($statement[MonoCard::F_COUNTER_NAME]) ?></div>

            <?php if (!empty($statement[MonoCard::F_COUNTER_EDRPOU])): ?>
                <div title="<?= MonoCard::description_field(MonoCard::F_COUNTER_EDRPOU) ?>"><strong>EDRPOU:</strong> <?= $statement[MonoCard::F_COUNTER_EDRPOU] ?></div>
            <?php endif; ?>

            <?php if (!empty($statement[MonoCard::F_COUNTER_IBAN])): ?>
                <div title="<?= MonoCard::description_field(MonoCard::F_COUNTER_IBAN) ?>"><strong>IBAN:</strong>
                    <span class="font-monospace"><?= $statement[MonoCard::F_COUNTER_IBAN] ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

