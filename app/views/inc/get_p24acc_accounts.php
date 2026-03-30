<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_p24acc_accounts.php
 *  Path    : app/views/inc/get_p24acc_accounts.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Mar 2026 11:14:39
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use config\Bank;
use config\P24acc;

include_once DIR_LIBS . '/functions.php';

/**
 * Отображение одного счёта П24
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
 * @var array $accounts     [], Список Банковских карт или рассчётных счетов
 * @var array $data         [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
 * @var int   $date1_ts     int, timestamp, начало периода выборки
 * @var int   $date2_ts     int, timestamp, конец периода выборки
 * @var array $ppp          [], ППП
 * 
 */

// Добавьте эту функцию в начало файла, перед HTML-разметкой
// function renderAccountField($account, $field) {
//     $label = P24acc::acc_field_title($field);
//     $value =    (in_array($field, P24acc::ACC_FIELD_CURRENCY) 
//                     ?   number_format(($account[$field] ?? 0), 2, '.', ' ')
//                     :   ($account[$field] ?? '')
//                 );
//     $tooltip = P24acc::acc_field_description($field);
//     return '<div class="mb-2 d-flex justify-content-between align-items-center">' .
//                '<span class="fw-bold text-muted">' . $label . '</span>' .
//                '<div class="d-flex align-items-center">' .
//                    '<span class="me-2 text-nowrap">' . h($value) . '</span>' .
//                    '<i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $tooltip . '"></i>' .
//                '</div>' .
//            '</div>';
// }

// debug($account, '$account');

?>

<?php foreach ($accounts[P24acc::F_BALANCES] as $account): ?>

    <div class="card mb-4 shadow-sm"  style="width: fit-content;">

        <div class="card-header">
            <h5 class="card-title mb-0">

                <!-- <span class="fw-bold text-muted">Номер рахунку:</span> -->
                <span class="ms-0"><?= h(Bank::format_iban($account[P24acc::F_ACC_NO] ?? '')); ?></span>
                <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="номер рахунку"></i> 

                <!-- <span class="fw-bold text-muted">Найменування рахунку:</span> -->
                <span class="ms-3"><?= h($account[P24acc::F_ACC_NAME_ACC] ?? ''); ?></span>
                <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="найменування рахунку"></i>

                <!-- <span class="fw-bold text-muted">Валюта:</span> -->
                <span class="ms-3"><?= h($account[P24acc::F_ACC_CURRENCY] ?? ''); ?></span>
                <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="&quot;UAH&quot;, валюта"></i>
                

            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    
                    <?= P24acc::renderField($account, P24acc::F_ACC_BALANCE_IN_EQ); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_TURNOVER_DEBT_EQ); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_BALANCE_OUT_EQ); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_TURNOVER_CRED_EQ); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_IS_FINAL_BAL); ?>

                </div>
                
                <div class="col-md-6">
                    
                    <?= P24acc::renderField($account, P24acc::F_ACC_DPD); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_STATE); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_ATP); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_DATE_OPEN_ACC_REG); ?>
                    <?= P24acc::renderField($account, P24acc::F_ACC_DATE_OPEN_ACC_SYS); ?>
                    
                    <?php 
                    if ($account[P24acc::F_ACC_DATE_CLOSE_ACC] != '01.01.1900 00:00:00'): ?>
                        <?= P24acc::renderField($account, P24acc::F_ACC_DATE_CLOSE_ACC); ?>
                    <?php endif; ?>
                    
                    <!-- 
                    <?= P24acc::renderField($account, P24acc::F_ACC_FLMN); ?>
                    <div class="mb-2">
                        <span class="fw-bold text-muted">Залишок вхідний:</span>
                        <span class="ms-2"><?= h($account[P24acc::F_ACC_BALANCE_IN] ?? ''); ?></span>
                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="залишок на рахунку вхідний"></i>
                    </div>  
                    -->
                    
                    <!-- 
                    <div class="mb-2">
                        <span class="fw-bold text-muted">Оборот, дебет:</span>
                        <span class="ms-2"><?= h($account[P24acc::F_ACC_TURNOVER_DEBT] ?? ''); ?></span>
                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="оборот, дебет"></i>
                    </div> 
                    -->
                    
                    <!-- <div class="mb-2">
                        <span class="fw-bold text-muted">Бранч, що залучив контрагента:</span>
                        <span class="ms-2"><?= h($account[P24acc::F_ACC_BGF_IBRN_NM] ?? ''); ?></span>
                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="бранч, що залучив контрагента"></i>
                    </div> -->
                    
                    <!-- 
                    <div class="mb-2">
                        <span class="fw-bold text-muted">Залишок вихідний:</span>
                        <span class="ms-2"><?= h($account[P24acc::F_ACC_BALANCE_OUT] ?? ''); ?></span>
                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="залишок на рахунку вихідний"></i>
                    </div> 
                    -->
                    
                    <!-- 
                    <div class="mb-2">
                        <span class="fw-bold text-muted">Оборот, кредит:</span>
                        <span class="ms-2"><?= h($account[P24acc::F_ACC_TURNOVER_CRED] ?? ''); ?></span>
                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="оборот, кредит"></i>
                    </div> 
                    -->
                    
                    <!-- 
                    <div class="mb-2">
                        <span class="fw-bold text-muted">Бранч рахунку:</span>
                        <span class="ms-2"><?= h($account[P24acc::F_ACC_BRNM] ?? ''); ?></span>
                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="бранч рахунку"></i>
                    </div> 
                    -->
                    
                </div>
            </div>
        </div>
    </div>

<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });
});
</script>
