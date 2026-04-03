<?php 
/**
 *  Project : my.ri.net.ua
 *  File    : get_pay_rec_form.php
 *  Path    : app/views/inc/get_pay_rec_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Mar 2026 23:04:54
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



/**
 * Вывод вормы одной записи для сохранения транзакции в биллинг.
 * Если запись есть в биллинге то просто выполняется сверка поей.
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



use config\Bank;
use config\MonoCard;
use config\tables\Pay;
use billing\core\base\Lang;
use config\Icons;

Lang::load_inc(__FILE__);

require_once DIR_LIBS . '/compare_functions.php';



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
 * @var int|string $index   Идндекс записи в массиве $data
 * @var array $statement    Банковская транзакция
 * @var array $pay_rec      Сгенерированная запись для внесени или сравнения
 * @var array $found_rec    Результат поиска записи в биллинге
 * 
 */

// debug($pay_rec, '$pay_rec');

?>

<div class="card mb-3 shadow-sm min-w-500">

    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <!-- PAY ID -->
            <small class="text-muted" title="<?= __('Дата транзакции') ?>" ><?= date('d.m.Y H:i:s', $pay_rec[Pay::F_DATE]) ?></small><br>
            <strong>Pay ID:</strong> <?= Bank::get_view_field($index, Pay::F_ID, $statement, $found_rec, $pay_rec) ?>
        </div>
        <div>
            <!-- PAY ACNT -->
            <strong>Pay Fakt: </strong><?= Bank::get_view_field($index, Pay::F_PAY_FAKT, $statement, $found_rec, $pay_rec) ?> ₴<br>
            <strong>Pay Acnt: </strong><?= Bank::get_view_field($index, Pay::F_PAY_ACNT, $statement, $found_rec, $pay_rec) ?> ₴
        </div>
    </div>

    <div class="card-body">

        <div class="row g-3">
            <!-- Дата операции -->
            <div class="col-3"><strong>Дата транзакции:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_DATE, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- ABON ID -->
            <div class="col-3"><strong>Abon ID:</strong></div>
            <div class="col-9"><span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_ABON_ID, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- BANK NO -->
            <div class="col-3"><strong>Bank No:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_BANK_NO, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- PAY -->
            <div class="col-3"><strong>Pay:</strong></div>
            <div class="col-9">
                <!-- PAY FAKT -->
                <span class="text-secondary small">FAKT:</span> <span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_PAY_FAKT, $statement, $found_rec, $pay_rec) ?></span> <span class="text-secondary small">₴</span> | 
                <!-- PAY ACNT -->
                <span class="text-secondary small">ACNT:</span> <span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_PAY_ACNT, $statement, $found_rec, $pay_rec) ?></span> <span class="text-secondary small">₴</span>
                <!-- BALANCE -->
                 
                <?php if ($found_rec[Bank::F_FOUND_ON_BILLING]) : ?>
                    <?php if (!is_null($pay_rec[Pay::F_REST])) : ?>
                        <?php if (empty($found_rec[Bank::F_FOUND_PAY][Pay::F_REST])) : ?>
                            | <img class="p-0 mb-1" src="<?= Icons::SRC_WARN ?>" alt="[!!!]" height="22" title="<?= __('Остаток на счету после операции не внесён') ?>">
                        <?php else: ?>
                            <?php $warn = (
                                (cmp_float($found_rec[Bank::F_FOUND_PAY][Pay::F_REST], $pay_rec[Pay::F_REST]) != 0)   
                                    ? 'text-warning' 
                                    : 'text-secondary' ) ?>
                            | <span class="small text-secondary">Balance:</span> <span class="<?= $warn ?> font-monospace"><?= Bank::get_view_field($index, Pay::F_REST, $statement, $found_rec, $pay_rec) ?></span> <span class=" text-secondary small ">₴</span>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (!empty($pay_rec[Pay::F_REST])) : ?>
                        | <span class="small text-secondary">Balance:</span> <span class="text-secondary font-monospace"><?= Bank::get_view_field($index, Pay::F_REST, $statement, $found_rec, $pay_rec) ?></span> <span class=" text-secondary small ">₴</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>


        <div class="row g-3">
            <!-- DESCRIPTION -->
            <div class="col-3" title="<?= Pay::field_title(Pay::F_DESCRIPTION) ?>"><strong>Description:</strong></div>
            <div class="col-9 bg-body-tertiary px-3 py-1 mt-4"><span><?= Bank::get_view_field($index, Pay::F_DESCRIPTION, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>
        
        <?php if (!$found_rec[Bank::F_FOUND_ON_BILLING]): ?>
        <div class="row g-3">
            <!-- SAVE SUFFIX -->
            <div class="col-3" title="<?= Pay::field_title(Pay::F_SAVE_SUFFIX) ?>"><strong>Save Suffix:</strong></div>
            <div class="col-9"><span><?= Bank::get_view_field($index, Pay::F_SAVE_SUFFIX, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>
        <?php endif; ?>
        
        <hr class="my-2">

        <div class="row g-3">
            <!-- ППП -->
            <div class="col-3"><strong>PAP:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_PPP_ID, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- TYPE PAY -->
            <div class="col-3"><strong>Type:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_TYPE_ID, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>

        <div class="row g-3">
            <!-- AGENT ID -->
            <div class="col-3"><strong>Agent:</strong></div>
            <div class="col-6"><span class="font-monospace"><?= Bank::get_view_field($index, Pay::F_AGENT_ID, $statement, $found_rec, $pay_rec) ?></span></div>
        </div>

    </div>

</div>