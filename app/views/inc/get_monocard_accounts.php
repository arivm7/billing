<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_monocard_accounts.php
 *  Path    : app/views/inc/get_monocard_accounts.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Mar 2026 20:12:43
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вывод списка учетных записей (акаунтов банковских карт), связанных с клиентом
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
use billing\core\base\Lang;
Lang::load_inc(__FILE__);


/**
 * Данные приходящие от контроллера
 * 
 * @var array $accounts     [], Список Банковских карт или рассчётных счетов
 * @var array $data         [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
 * @var int $date1_ts       int, timestamp, начало периода выборки
 * @var int $date2_ts       int, timestamp, конец периода выборки
 * @var array $ppp          [], ППП
 * 
 */


?>
<!-- 
 *
 *   Информация о клиенте 
 *
-->
<h2 class="fs-6">Информация по MonoCard</h2>
<h3 class="fs-6"><span class="text-primary" title="[name]"><?=$accounts['name']?></span><span class="text-secondary fs-8" title="[clientId]"> | <?=$accounts['clientId']?></span></h3>

<!-- 
 *
 *   Список банковских карт 
 *
-->
<?php foreach ($accounts[MonoCard::F_ACCOUNTS] as $card): ?>
    <div class="card mb-3 shadow-sm"  style="width: fit-content;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Account
                <?php if ($accounts[MonoCard::F_CLIENT_ID] == $card[MonoCard::F_CARD_SEND_ID]): ?>
                    <span class="badge bg-success ms-2">clientId</span>
                <?php endif; ?>
            </span>
            <span class="text-muted small">
                sendId: <?= $card[MonoCard::F_CARD_SEND_ID] ?>
            </span>
        </div>

        <div class="card-body">
            <ul class="list-group list-group-flush">

                <li class="list-group-item text-secondary">
                    <strong>ID:</strong>
                    <span><?= $card[MonoCard::F_CARD_ID] ?></span>
                </li>

                <li class="list-group-item">
                    <strong>IBAN:</strong>
                    <span class="font-monospace"><?= Bank::format_iban($card[MonoCard::F_CARD_IBAN]) ?></span>
                    <span class="badge bg-secondary ms-2">
                        <?= $card[MonoCard::F_CARD_TYPE] ?>
                    </span>
                </li>

                <li class="list-group-item">
                    <strong>Balance:</strong>
                    <?= sprintf("%.2f", (floatval($card[MonoCard::F_CARD_BALANCE]) - floatval($card[MonoCard::F_CARD_CREDIT_LIMIT]))) ?>
                    <span class="text-muted small ms-2">
                        [
                        <span title="cashbackType"><?= $card[MonoCard::F_CARD_CASHBACK_TYPE] ?></span> |
                        <span title="currencyCode"><?= $card[MonoCard::F_CARD_CURRENCY_CODE] ?></span> |
                        <span title="creditLimit">credit <?= floatval($card[MonoCard::F_CARD_CREDIT_LIMIT]) ?>
                        </span>
                        ]
                    </span>
                </li>

                <?php if (!empty($card[MonoCard::F_CARD_MASKED_PAN])): ?>
                    <li class="list-group-item">
                        <strong>Cards:</strong>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($card[MonoCard::F_CARD_MASKED_PAN] as $pan): ?>
                                <li class="font-monospace text-muted"><?= $pan ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
<?php endforeach; ?>