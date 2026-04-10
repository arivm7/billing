<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_p24card_statement.php
 *  Path    : app/views/inc/get_p24card_statement.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 31 Mar 2026 15:48:14
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



/**
 * Отображение одной банковской транзакции Карты Приватбанка
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



use config\P24card;
use config\tables\Pay;
use billing\core\base\Lang;
use config\Icons;

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
 * Данные из get_p24card_dispatcher.php.php
 * 
 * @var array $statement    Банковская транзакция
 * @var array $pay_rec      Сгенерированная запись для внесени или сравнения
 * @var array $found_rec    Результат поиска записи в биллинге
 * 
 */



// $accordion_id = '_stmt_' . $statement[P24card::F_ID]; // уникальный ID
$amount = $statement[P24card::F_AMOUNT];
$opAmount = $statement[P24card::F_AMOUNT_TRANSACTION];
$balance = $statement[P24card::F_REST];
$date_str = $statement[P24card::F_DATE_STR]; // date('d.m.Y H:i:s', $statement[P24card::F_DATE]);
?>

<div class="card mb-3 shadow-sm min-w-200"> <!-- w-100 | style="width: fit-content;" -->
    <div class="card-header d-flex justify-content-between align-items-center">
        <div style="min-width:100px;">
            <small class="text-muted" title="<?= P24card::field_title(P24card::F_DATE) ?>" ><?= $date_str ?></small><br>
            <strong class="text-truncate d-block" 
                    title="<?= P24card::field_title(P24card::F_DESCRIPTION) ?>" ><?= h($statement[P24card::F_DESCRIPTION]) ?></strong>
        </div>

        <div class="text-end ms-3 text-nowrap">
            <span class="fw-bold font-monospace <?= $amount < 0 ? 'text-danger' : 'text-success' ?>" title="<?= P24card::F_AMOUNT . ' :: ' . P24card::field_title(P24card::F_AMOUNT) ?>" >
                <?= number_format($amount, 2, '.', ' ') ?><span title="CURRENCY: <?= h($statement[P24card::F_CURRENCY]) ?>"> <?= (h($statement[P24card::F_CURRENCY])=='UAH' ? "₴":h($statement[P24card::F_CURRENCY])) ?></span></span>
            <br>
            <span class="fw-bold font-monospace <?= $opAmount < 0 ? 'text-danger' : ($amount == $opAmount ? 'text-success' : 'text-warning') ?>" title="<?= P24card::F_AMOUNT_TRANSACTION . ' :: ' . P24card::field_title(P24card::F_AMOUNT_TRANSACTION) ?>" >
                <?= number_format($opAmount, 2, '.', ' ') ?><span title="CURRENCY: <?= h($statement[P24card::F_CURRENCY]) ?>"> <?= (h($statement[P24card::F_CURRENCY])=='UAH' ? "₴":h($statement[P24card::F_CURRENCY])) ?></span></span>
        </div>
    </div>

    <div class="card-body">

        <div class='d-flex justify-content-between align-items-center' title="<?= P24card::field_title(P24card::F_BANK_NO) ?>">
            <strong>Bank No:</strong> 
            <span class="font-monospace"><?= $statement[P24card::F_BANK_NO] ?></span>
        </div>

        <div class='d-flex justify-content-between align-items-center' title="<?= P24card::field_title(P24card::F_AMOUNT_TRANSACTION) ?>">
            <strong>Amount:</strong>
            <span class="font-monospace"><?= number_format($opAmount, 2, '.', ' ') ?> ₴</span>
        </div>

        <div>
            <strong><?= P24card::field_title(P24card::F_DESCRIPTION) ?>:</strong>
            <div class="text-end bg-body-tertiary px-3 py-2">
                <span><?= h(nl2br($statement[P24card::F_DESCRIPTION])) ?></span>
            </div>
        </div>


        <hr class="my-2">

        <div class='d-flex justify-content-between align-items-center' title="<?= P24card::field_title(P24card::F_REST) ?>">
            <strong><?= P24card::field_title(P24card::F_REST) ?>:</strong>
            <div>
                <button type="button" 
                    class="btn btn-outline-info btn-sm align-items-center fs-8 py-0 px-2 me-2 copy-btn" data-text="<?= json_encode($balance) ?>">
                    <img src="<?= Icons::SRC_ICON_CLIPBOARD ?>" title="<?= __('Скопировать остаток на счету после транзакции в clipboard') ?>" alt="[copy]" height="16">
                </button>
                <span class="fw-bold font-monospace <?= $balance < 0 ? 'text-danger' : 'text-success' ?>"><?= sign($balance) < 0 ? "-" /* &#8722; */ :"+" ?></span><span class="text-secondary font-monospace"><?= number_format(abs($balance), 2, '.', ' ') ?> ₴</span>
            </div>
        </div>

    </div>
</div>