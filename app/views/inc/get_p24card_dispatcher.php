<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_p24card_dispatcher.php
 *  Path    : app/views/inc/get_p24card_dispatcher.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 31 Mar 2026 15:48:14
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Диспетчер для получения платежей по карте P24.
 * Поскольку АПИ нет, то данные получаются копипастом в текстовое поле,
 * которое далее обрабатывается как талица.
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
 *                                  app/views/inc/get_p24acc_accounts.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_p24acc_transaction_card.php
 *                                  app/views/inc/get_pay_rec_form.php
 * 
 *                          app/views/inc/get_p24card_dispatcher.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_p24card_statement.php
 *                                  app/views/inc/get_pay_rec_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




/**
 * Данные приходящие от контроллера
 * 
 * @var array $accounts     [], Банковские карты или рассчётные счета
 * @var array $data         [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
 * @var int   $date1_ts     int, timestamp, начало периода выборки
 * @var int   $date2_ts     int, timestamp, конец периода выборки
 * @var array $ppp          [], ППП
 * 
 */

use config\Bank;
use config\P24card;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

?>

<?php if ($data):  ?>

    <form method="post" action="">

        <?php if (isset($_POST[P24card::F_RAW_TEXT])): ?>
            <input type="hidden" name="<?= P24card::F_RAW_TEXT ?>" value='<?= $_POST[P24card::F_RAW_TEXT]; ?>'>
        <?php endif; ?>

        <?php foreach ($data as $index => $rec) : ?>
            <?php 
                $transaction = $statement = $rec[Bank::F_STATEMENT];
                $pay_rec = $rec[Bank::F_PAY_REC];
                $found_rec = $rec[Bank::F_FOUND_REC];
            ?>
            <div class="row min-w-900">
                <div class="col-4">
                    <?php include DIR_INC . '/get_p24card_statement.php'; ?>
                </div>
                <div class="col-8">
                    <?php include DIR_INC . '/get_pay_rec_form.php'; ?>
                </div>
                <hr>
            </div>
        <?php endforeach; ?>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                Save Selected
            </button>
        </div>

    </form>

<?php else: ?>

    <div class="card text-center w-auto" >
        <form method="post" action="">
            <div class="card-header">
                <h2 class="fs-4 mt-1">Внесение платежей из текстового поля</h2>
            </div>
            <div class="card-body m-3">
                <textarea class="w-100" rows=15 cols=60 name="<?= P24card::F_RAW_TEXT ?>"></textarea>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary px-5">
                    >>>
                </button>
            </div>
        </form>
    </div>

<?php endif ?>
