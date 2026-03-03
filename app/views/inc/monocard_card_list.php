<?php
/**
 *  Project : my.ri.net.ua
 *  File    : monocard_card_list.php
 *  Path    : app/views/inc/monocard_card_list.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 12 Feb 2026 09:59:41
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вывод списка учетных записей (акаунтов банковских карт), связанных с клиентом
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\Bank;
use config\MonoCard;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);


?>

<!-- 
*
*   Список банковских карт 
*
-->
<?php foreach ($cards_info['client']['accounts'] as $card): ?>
    <div class="card mb-3 shadow-sm"  style="width: fit-content;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Account
                <?php if ($cards_info['client']['clientId'] == $card[MonoCard::F_CARD_SEND_ID]): ?>
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
                        (
                        <span title="cashbackType"><?= $card[MonoCard::F_CARD_CASHBACK_TYPE] ?></span> |
                        <span title="currencyCode"><?= $card[MonoCard::F_CARD_CURRENCY_CODE] ?></span> |
                        <span title="creditLimit">
                            credit <?= floatval($card[MonoCard::F_CARD_CREDIT_LIMIT]) ?>
                        </span>
                        )
                    </span>
                </li>

                <?php if (!empty($card['maskedPan'])): ?>
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
