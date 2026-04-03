<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_p24acc_dispatcher.php
 *  Path    : app/views/inc/get_p24acc_dispatcher.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Mar 2026 13:31:55
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use billing\core\App;
use config\Bank;
use config\P24acc;

/**
 * Диспетчер видов для транзакций Приватбанка
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



?>
<!-- 
*
*   Список банковских счетов
*
-->
<?php include DIR_INC . '/get_p24acc_accounts.php'; ?>



<!-- 
*
*   Навигация по датам
*
-->
<?php include DIR_INC . '/get_navigation.php'; ?>


<!-- =======================FORM======================== -->

<form method="post" action="">

        <?php foreach ($data as $index => $rec) : ?>
            <?php 
                $transaction = $statement = $rec[Bank::F_STATEMENT];
                $pay_rec = $rec[Bank::F_PAY_REC];
                $found_rec = $rec[Bank::F_FOUND_REC];
            ?>

            <div class="row min-w-900">
                <div class="col-4">
                    <?php include DIR_INC . '/get_p24acc_transaction_card.php'; ?>
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

<!-- 
*
*   Навигация по датам
*
-->
<?php include DIR_INC . '/get_navigation.php'; ?>
