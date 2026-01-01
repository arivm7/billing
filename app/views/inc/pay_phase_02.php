<?php
/**
 *  Project : my.ri.net.ua
 *  File    : pay_phase_02.php
 *  Path    : app/views/inc/pay_phase_02.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 17 Oct 2025 21:38:36
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Фаза 02 проведения платежа
 * 
 * 2.   На входе есть номер договора
 * 
 *      -- Проверка правильности введённого или выбранного номера договора.
 *         Если abon_id не верен то перейти на п. 1
 * 
 *      -- Для выбранного договора найти акивные прайсовые фрагменты.
 *         Если активных прайсовых фрагментов нет, то искать посление закрытые прайсовые фрагменты.
 * 
 *      -- По найденным прайсовым франгментам подтвердить оплачиваемую услугу.
 *         (поскольку, возможно, прайсовые фрагменты (ПФ) найдены из закрытых, то нужно убедиться, что абонент хочет опачивать именно за них)
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Pay;
use config\tables\PA;
use config\tables\Price;
use config\tables\AbonRest;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * @var int $phase -- Фаза проведения платежа
 * @var string $title -- Заголовок формы
 * @var int $user_id -- ID пользователя, которому оплачивается счёт
 * @var int $abon_id -- ID абонента
 * @var array $pa_list -- оплачиваемые прайсовые фрагмернты
 * @var array $rest -- информация о платёжном состоянии абонента
 */

?>
<h1 class="h5 text-center mb-4"><?=__('Confirm the services you are paying for');?></h1>
<form method="post" action="<?=Pay::URI_PAY;?>" class="row g-3 font-monospace needs-validation" novalidate>


    <?php if (App::isAuth()): ?>
        <table class="table table-striped table-hover table-bordered text-center">
            <thead>
            <tr>
                <th class="align-middle"><?=__('Service activation date');?></th>
                <th class="align-middle"><?=__('End date of the service');?></th>
                <th class="align-middle"><?=__('Subscription fee for the service');?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pa_list as $item): ?>
            <tr>
                <td class="align-middle"><?=date('Y-m-d', (int)$item[PA::F_DATE_START]);?></td>
                <td class="align-middle"><?=($item[PA::F_DATE_END] ? date('Y-m-d', (int)$item[PA::F_DATE_END]) : '____-__-__');?></td>
                <td class="align-middle"><?= $item[Price::TABLE][Price::F_PAY_PER_DAY] * 30 + $item[Price::TABLE][Price::F_PAY_PER_MONTH];?> 
                    <span class="text-secondary fs-7"><?=__('UAH');?>/30<?=__('days');?></span>
                    (<?=h($item[Price::TABLE][Price::F_TITLE]);?>)</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif ?>

    <?php if (App::isAuth() && (App::get_user_id() != $user_id)): ?>
        <div class="alert alert-warning" role="alert">
            <h3 class="alert-heading fs-5"><?=__('The contract number you specified for payment is not yours');?></h3>
        </div>
    <?php endif; ?>

    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading"><?=__('Information about the status of the personal account');?></h4>
        <p><?=__('Personal account balance');?>: <strong><?=number_format($rest[AbonRest::F_REST], 2, '.', ' ');?> <?=__('UAH');?>.</strong></p>
        <p><?=__('Prepaid days');?>: <strong><?=(int)$rest[AbonRest::F_PREPAYED];?> <?=__('days');?></strong></p>
        <hr>
        <div class="mb-0 d-flex align-items-center gap-2">
            <div class="fw-semibold mb-0"><?=__('Recommended amount for payment');?>:</div>
            <div class="input-group input-group-sm" style="max-width:260px;">
                <input
                    type="number"
                    name="<?=Pay::POST_REC;?>[<?=AbonRest::F_AMOUNT;?>]"
                    class="form-control text-end"
                    placeholder="<?=__('Payment amount (recommended by %s)', number_format($rest[AbonRest::F_AMOUNT], 2, '.', ' '));?>"
                    value="<?=h($rest[AbonRest::F_AMOUNT]);?>"
                    min="<?=App::get_config('bank_payment_min');?>"
                    step="0.01"
                    lang="en"
                    required
                >
                <span class="input-group-text"><?=__('UAH');?>.</span>
                <div class="invalid-feedback text-start">
                    <?=__('Enter the correct amount (a number of %s or more)', App::get_config('bank_payment_min'));?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-center gap-2">
        <input type="hidden" name="<?=Pay::POST_REC;?>[phase]" value="<?=h(strval($phase));?>">
        <input type="hidden" name="<?=Pay::POST_REC;?>[abon_id]" value="<?=h(strval($abon_id));?>">
        <a href="<?=Pay::URI_PAY;?>" class="btn btn-secondary w-25" style="min-width:120px;"><?=__('Cancel');?></a>
        <button type="submit" class="btn btn-primary w-75" style="min-width:120px;"><?=__('Continue');?></button>
    </div>
</form>

<script>
// Bootstrap 5 валидация формы
(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

