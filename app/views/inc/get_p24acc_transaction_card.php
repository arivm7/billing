<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_p24acc_transaction_card.php
 *  Path    : app/views/inc/get_p24acc_transaction_card.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Mar 2026 15:17:57
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Карточка банковской транзакции (P24acc / АвтоКлиент ПриватБанк)
 * Отображает все поля транзакции с комментариями
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


use config\P24acc;
use billing\core\base\Lang;
use config\Bank;
use config\MonoCard;

Lang::load_inc(__FILE__);
require_once DIR_LIBS . '/inc_functions.php';


/**
 * Входные данные
 * @var array $transaction -- Ассоциативный массив одной транзакции из банка
 */

if (!isset($transaction) || empty($transaction)) {
    return;
}


?>

<div class="card mb-3 shadow-sm min-w-300">

    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <!-- PAY ID -->
            <small class="text-muted" title="<?= __('Дата транзакции') ?>" ><?= $transaction[P24acc::F_DATE_TIME_DAT_OD_TIM_P] ?></small><br>
            <strong title="<?= P24acc::F_REF .'+'. P24acc::F_REFN ?>">Bank No: </strong> <?= h($transaction[P24acc::F_REF] . $transaction[P24acc::F_REFN]) ?>
        </div>
        <div class="text-end">
            <?= number_format($transaction[P24acc::F_SUM_E], 2, '.', ' ') . ' <span class="text-secondary">' . $transaction[P24acc::F_CCY] . '</span>' ?>
            <?php
            $tranType = ($transaction[P24acc::F_TRANTYPE] ?? '');
            $tranTypeTitle = ($tranType === 'C' ? 'Credit (+' : ($tranType === 'D' ? 'Debit (-' : 'N/A')) . ')';
            echo html_badge($tranType, $tranType, ['C' => BADGE_SUCCESS, 'D' => BADGE_DANGER], title: $tranTypeTitle);
            ?>
        </div>
    </div>

    <div class="card-body">
        
        <!-- ==================== ОСНОВНЫЕ ПОЛЯ ==================== -->
       
        <div class="row mb-2">
            <?= P24acc::renderField($transaction, P24acc::F_DATE_TIME_DAT_OD_TIM_P) ?>
            <?= P24acc::renderField($transaction, P24acc::F_AUT_CNTR_NAM, value_add_class: "bg-body-tertiary px-3 py-2") ?>
            <?= P24acc::renderField(
                label: P24acc::F_REF .'+'. P24acc::F_REFN . " Bank No",
                value: h($transaction[P24acc::F_REF] . $transaction[P24acc::F_REFN]),
                tooltip:  __('Уникальный номер банковской операции (F_REF + F_REFN)'),
                ) ?>
                    <?php
                    $prPr = $transaction[P24acc::F_PR_PR] ?? '';
                    $prPrDescr = [
                        'p' => __('Conducting | Проводится | Проводиться'),
                        't' => __('Canceled | Сторнирована | Сторнована'),
                        'r' => __('Completed | Проведена | Проведена'),
                        'n' => __('Rejected | Забракована | Забракована'),
                    ][$prPr] ?? $prPr;
                    ?>
            <?= P24acc::renderField($transaction, P24acc::F_SUM_E, 
                    value: number_format($transaction[P24acc::F_SUM_E], 2, '.', ' ') . ' <span class="text-secondary">' . $transaction[P24acc::F_CCY] . '</span>') ?>
            <?= P24acc::renderField($transaction, P24acc::F_OSND, value_add_class: "bg-body-tertiary px-3 py-2") ?>
                        <!-- value: '<span class="text-end bg-body-tertiary px-3 py-2">'.h(nl2br($transaction[P24acc::F_OSND] ?? '')).'</span>' -->
            <!-- <hr> -->
            <!-- ==================== Проводка и Контрагент (Аккордеон) ==================== -->
            <div class="accordion accordion-flush" id="accordionDetails<?= $transaction[P24acc::F_REF] ?>">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetails<?= $transaction[P24acc::F_REF] ?>" aria-expanded="false" aria-controls="collapseDetails<?= $transaction[P24acc::F_REF] ?>">
                            <?= __('Детали документа и данные контрагента') ?>
                        </button>
                    </h2>
                    <div id="collapseDetails<?= $transaction[P24acc::F_REF] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionDetails<?= $transaction[P24acc::F_REF] ?>">
                        <div class="accordion-body">
                            <!-- ==================== Проводка ==================== -->
                            <?= P24acc::renderField($transaction, P24acc::F_NUM_DOC,
                                    label: __('Док.') . '/' . __('Стан'),
                                    value: '<span title="'.P24acc::field_descr(P24acc::F_NUM_DOC).'">' . h($transaction[P24acc::F_NUM_DOC]) . '</span> | '
                                         . '<span title="'.P24acc::field_descr(P24acc::F_DOC_TYP).'">' . h($transaction[P24acc::F_DOC_TYP]) . '</span> | '
                                         . '<span title="'.P24acc::field_descr(P24acc::F_FL_REAL).'">' . h($transaction[P24acc::F_FL_REAL]) . '</span> | '
                                         . '<span title="'.$prPrDescr.'">' . html_badge($prPr, $prPr, ['r' => BADGE_SUCCESS, 't' => BADGE_WARNING, 'n' => BADGE_DANGER], bage_na: BADGE_INFO) . '</span> | ',
                                    tooltip:  __('Номер документа и его статус')
                                ) ?>

                            <hr>
                            <!-- ==================== КОНТРАГЕНТ ==================== -->
                            <?= P24acc::renderField($transaction, P24acc::F_AUT_CNTR_CRF) ?>
                            <?= P24acc::renderField($transaction, P24acc::F_AUT_CNTR_MFO) ?>
                            <?= P24acc::renderField($transaction, P24acc::F_AUT_CNTR_ACC,
                                    value: "<nowrap>" . Bank::format_iban($transaction[P24acc::F_AUT_CNTR_ACC]) . "</nowrap>") ?>
                            <?= P24acc::renderField($transaction, P24acc::F_AUT_CNTR_MFO_NAME) ?>
                            <?= P24acc::renderField($transaction, P24acc::F_AUT_CNTR_MFO_CITY) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-footer text-muted">
        <?= P24acc::renderField($transaction, P24acc::F_ID) ?>
    </div>
</div>