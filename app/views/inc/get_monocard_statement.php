<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_monocard_statement.php
 *  Path    : app/views/inc/get_monocard_statement.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Mar 2026 23:12:02
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Отображение одной банковской транзакции моно-карты
 * 
 * app/controllers/BankController.php
 *          public function getAction()
 *                  app/views/Bank/getView.php
 *                          app/views/inc/get_monocard_dispatcher.php
 *                                  app/views/inc/get_monocard_accounts.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_monocard_statement.php
 *                                  app/views/inc/get_pay_rec_form.php
 * 
 *                          app/views/inc/get_p24acc_dispatcher.php
 *                                  app/views/inc/get_p24acc_account.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_p24acc_transaction_card.php
 *                                  app/views/inc/get_pay_rec_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use config\MonoCard;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);



/**
 * 
 * Данные приходящие от контроллера
 * 
 * @var array $accounts     [], Банковские карты или рассчётные счета
 * @var array $data         [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
 * @var int   $date1_ts     int, timestamp, начало периода выборки
 * @var int   $date2_ts     int, timestamp, конец периода выборки
 * @var array $ppp          [], ППП
 * 
 * Данные из get_monocard_dispatcher.php
 * 
 * @var array $statement    Банковская транзакция
 * @var array $pay_rec      Сгенерированная запись для внесени или сравнения
 * @var array $found_rec    Результат поиска записи в биллинге
 * 
 */


// $accordion_id = '_stmt_' . $statement[MonoCard::F_ID]; // уникальный ID
$amount = $statement[MonoCard::F_AMOUNT];
$opAmount = $statement[MonoCard::F_OPERATION_AMOUNT];
$balance = $statement[MonoCard::F_BALANCE];
$commission = $statement[MonoCard::F_COMMISSION_RATE];
$cashback = $statement[MonoCard::F_CASHBACK_AMOUNT];
$currency = $accounts[MonoCard::F_ACCOUNTS][$statement[MonoCard::F_ACCOUNT_ID]][MonoCard::F_CARD_CASHBACK_TYPE]; //  $statement[MonoCard::F_CURRENCY_CODE];
$time_str = date('d.m.Y H:i:s', $statement[MonoCard::F_TIME]);
?>

<div class="card mb-3 shadow-sm min-w-400"> <!-- w-100 | style="width: fit-content;" -->
    <div class="card-header d-flex justify-content-between align-items-center">
        <div style="min-width:100px;">
            <small class="text-muted" title="<?= MonoCard::field_descr(MonoCard::F_TIME) ?>" ><?= $time_str ?></small><br>
            <!-- <strong title="<?= MonoCard::field_descr(MonoCard::F_DESCRIPTION) ?>" ><?= h($statement[MonoCard::F_DESCRIPTION]) ?></strong> -->
            <strong class="text-truncate d-block"
                    title="<?= h($statement[MonoCard::F_DESCRIPTION]) ?>">
                <?= h($statement[MonoCard::F_DESCRIPTION]) ?>
            </strong>

        </div>

        <div class="text-end ms-3 text-nowrap">
            <?php if (!empty($statement[MonoCard::F_HOLD])): ?>
                <span class="ms-2 badge bg-warning text-dark" title="<?= MonoCard::F_HOLD . ' :: ' . MonoCard::field_descr(MonoCard::F_HOLD) ?>" >H</span>
            <?php endif; ?>
            <span class="fw-bold <?= $amount < 0 ? 'text-danger' : 'text-success' ?>" title="<?= MonoCard::F_AMOUNT . ' :: ' . MonoCard::field_descr(MonoCard::F_AMOUNT) ?>" >
                <?= number_format($amount, 2, '.', ' ') ?>
            </span> ₴<br>
            <span class="fw-bold <?= $opAmount < 0 ? 'text-danger' : ($amount == $opAmount ? 'text-success' : 'text-warning') ?>" title="<?= MonoCard::F_OPERATION_AMOUNT . ' :: ' . MonoCard::field_descr(MonoCard::F_OPERATION_AMOUNT) ?>" >
                <?= number_format($opAmount, 2, '.', ' ') ?>
            </span> ₴
        </div>
    </div>

    <div class="card-body">

        <div class='d-flex justify-content-between align-items-center' 
                title="<?= MonoCard::field_descr(MonoCard::F_BANK_ID) ?>">
            <strong>ID (Bank No):</strong>
            <span class="font-monospace"><?= $statement[MonoCard::F_BANK_ID] ?></span>
        </div>

        <div class='d-flex justify-content-between align-items-center' 
                title="<?= MonoCard::field_descr(MonoCard::F_AMOUNT) ?>">
            <strong><?= MonoCard::field_title(MonoCard::F_AMOUNT); ?>:</strong>
            <div>
                <span><?= number_format($amount, 2, '.', ' ') ?></span>
                <span title="<?= MonoCard::field_descr(MonoCard::F_CURRENCY_CODE); ?>"><?= ($currency == 'UAH' ? "₴" : $currency) ?></span>
            </div>
            
        </div>

        <div class='d-flex justify-content-between align-items-center' 
                title="<?= MonoCard::field_descr(MonoCard::F_OPERATION_AMOUNT) ?>">
            <strong><?= MonoCard::field_title(MonoCard::F_OPERATION_AMOUNT); ?>:</strong>
            <span><?= number_format($opAmount, 2, '.', ' ') ?> ₴</span>
        </div>

        <div>
            <strong><?= MonoCard::field_descr(MonoCard::F_DESCRIPTION) ?>:</strong>
            <div class="text-end bg-body-tertiary px-3 py-2">
                <span><?= nl2br(h($statement[MonoCard::F_DESCRIPTION])) ?></span>
            </div>
        </div>

        <div class='d-flex justify-content-between align-items-center' 
                title="<?= MonoCard::field_descr(MonoCard::F_COMMISSION_RATE) ?>">
            <strong>Commission:</strong>
            <span><?= number_format($commission, 2) ?> ₴</span>
        </div>

        <div class='d-flex justify-content-between align-items-center' 
                title="<?= MonoCard::field_descr(MonoCard::F_CASHBACK_AMOUNT) ?>">
            <strong>Cashback:</strong>
            <span class="<?= $cashback > 0 ? 'text-success' : 'text-muted' ?>"><?= number_format($cashback, 2, '.', ' ') ?> ₴</span>
        </div>

        <hr class="my-2">

        <div class='d-flex justify-content-between align-items-center' 
                title="<?= MonoCard::field_descr(MonoCard::F_BALANCE) ?>">
            <strong>Balance after:</strong>
            <span class="fw-bold <?= ($balance < 0 ? "text-warning-emphasis" : "text-success") ?>"><?= number_format($balance, 2, '.', ' ') ?> ₴</span>
        </div>

        <?php if (!empty($statement[MonoCard::F_COMMENT])): ?>
            <div>
                <strong title="<?= MonoCard::field_descr(MonoCard::F_COMMENT) ?>" >Comment:</strong><br>
                <span class="p-3 text-primary-emphasis bg-secondary-subtle border border-secondary-subtle rounded-3"><?= nl2br(h($statement[MonoCard::F_COMMENT])) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($statement[MonoCard::F_COUNTER_NAME])): ?>
            <hr class="my-2">
            <div class='d-flex justify-content-between align-items-center' 
                    title="<?= MonoCard::field_descr(MonoCard::F_COUNTER_NAME) ?>">
                <strong>Counterparty:</strong>
                <span><?= h($statement[MonoCard::F_COUNTER_NAME]) ?></span>
            </div>

            <?php if (!empty($statement[MonoCard::F_COUNTER_EDRPOU])): ?>
                <div class='d-flex justify-content-between align-items-center' 
                        title="<?= MonoCard::field_descr(MonoCard::F_COUNTER_EDRPOU) ?>">
                    <strong>EDRPOU:</strong>
                    <span><?= $statement[MonoCard::F_COUNTER_EDRPOU] ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($statement[MonoCard::F_COUNTER_IBAN])): ?>
                <div class='d-flex justify-content-between align-items-center' 
                        title="<?= MonoCard::field_descr(MonoCard::F_COUNTER_IBAN) ?>">
                    <strong>IBAN:</strong>
                    <span class="font-monospace"><?= $statement[MonoCard::F_COUNTER_IBAN] ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
