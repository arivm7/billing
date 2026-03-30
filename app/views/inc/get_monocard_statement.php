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
$time_str = date('d.m.Y H:i:s', $statement[MonoCard::F_TIME]);
?>

<div class="card mb-3 shadow-sm min-w-200"> <!-- w-100 | style="width: fit-content;" -->
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <small class="text-muted" title="<?= MonoCard::field_descr(MonoCard::F_TIME) ?>" ><?= $time_str ?></small><br>
            <strong title="<?= MonoCard::field_descr(MonoCard::F_DESCRIPTION) ?>" ><?= h($statement[MonoCard::F_DESCRIPTION]) ?></strong>
        </div>

        <div class="text-end ms-3">
            <?php if (!empty($statement[MonoCard::F_HOLD])): ?>
                <span class="ms-2 badge bg-warning text-dark" title="<?= MonoCard::F_HOLD . ' :: ' . MonoCard::field_descr(MonoCard::F_HOLD) ?>" >H</span>
            <?php endif; ?>
            <span class="fw-bold text-nowrap <?= $amount < 0 ? 'text-danger' : 'text-success' ?>" title="<?= MonoCard::F_AMOUNT . ' :: ' . MonoCard::field_descr(MonoCard::F_AMOUNT) ?>" >
                <?= number_format($amount, 2) ?>
            </span> ₴<br>
            <span class="fw-bold text-nowrap <?= $opAmount < 0 ? 'text-danger' : ($amount == $opAmount ? 'text-success' : 'text-warning') ?>" title="<?= MonoCard::F_OPERATION_AMOUNT . ' :: ' . MonoCard::field_descr(MonoCard::F_OPERATION_AMOUNT) ?>" >
                <?= number_format($opAmount, 2) ?>
            </span> ₴
        </div>
    </div>

    <div class="card-body">

        <div title="<?= MonoCard::field_descr(MonoCard::F_BANK_ID) ?>"><strong>ID:</strong> <span class="font-monospace"><?= $statement[MonoCard::F_BANK_ID] ?></span></div>

        <div><strong title="<?= MonoCard::field_descr(MonoCard::F_MCC) ?>" >MCC:</strong>
            <?= $statement[MonoCard::F_MCC] ?>
            <?php if ($statement[MonoCard::F_MCC] != $statement[MonoCard::F_ORIGINAL_MCC]): ?>
                <span class="text-muted" title="<?= MonoCard::field_descr(MonoCard::F_ORIGINAL_MCC) ?>" >(orig <?= $statement[MonoCard::F_ORIGINAL_MCC] ?>)</span>
            <?php endif; ?>
        </div>

        <div title="<?= MonoCard::field_descr(MonoCard::F_OPERATION_AMOUNT) ?>">
            <strong>Amount:</strong> <?= number_format($opAmount, 2) ?> ₴
        </div>

        <div title="<?= MonoCard::field_descr(MonoCard::F_COMMISSION_RATE) ?>"><strong>Commission:</strong> <?= number_format($commission, 2) ?> ₴</div>

        <div title="<?= MonoCard::field_descr(MonoCard::F_CASHBACK_AMOUNT) ?>"><strong>Cashback:</strong>
            <span class="<?= $cashback > 0 ? 'text-success' : 'text-muted' ?>">
                <?= number_format($cashback, 2) ?> ₴
            </span>
        </div>

        <hr class="my-2">

        <div title="<?= MonoCard::field_descr(MonoCard::F_BALANCE) ?>"><strong>Balance after:</strong>
            <span class="fw-bold"><?= number_format($balance, 2) ?> ₴</span>
        </div>

        <?php if (!empty($statement[MonoCard::F_COMMENT])): ?>
            <div class="mt-2">
                <strong title="<?= MonoCard::field_descr(MonoCard::F_COMMENT) ?>" >Comment:</strong><br>
                <?= nl2br(h($statement[MonoCard::F_COMMENT])) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($statement[MonoCard::F_COUNTER_NAME])): ?>
            <hr class="my-2">
            <div title="<?= MonoCard::field_descr(MonoCard::F_COUNTER_NAME) ?>"><strong>Counterparty:</strong> <?= h($statement[MonoCard::F_COUNTER_NAME]) ?></div>

            <?php if (!empty($statement[MonoCard::F_COUNTER_EDRPOU])): ?>
                <div title="<?= MonoCard::field_descr(MonoCard::F_COUNTER_EDRPOU) ?>"><strong>EDRPOU:</strong> <?= $statement[MonoCard::F_COUNTER_EDRPOU] ?></div>
            <?php endif; ?>

            <?php if (!empty($statement[MonoCard::F_COUNTER_IBAN])): ?>
                <div title="<?= MonoCard::field_descr(MonoCard::F_COUNTER_IBAN) ?>"><strong>IBAN:</strong>
                    <span class="font-monospace"><?= $statement[MonoCard::F_COUNTER_IBAN] ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
