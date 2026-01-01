<?php
/**
 *  Project : my.ri.net.ua
 *  File    : pay_phase_03.php
 *  Path    : app/views/inc/pay_phase_03.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 18 Oct 2025 14:19:14
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 *      -- Для каждого ППП
 *          -- показать способ оплаты
 *          -- показать кнопку перехода на систему оплаты
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\base\Lang;
use config\tables\Pay;
use config\tables\PA;
use config\tables\Price;
use config\tables\Ppp;
use config\tables\Firm;
use billing\core\base\Theme;
use config\Bank;

Lang::load_inc(__FILE__);

/**
 * @var int $phase -- Фаза проведения платежа
 * @var string $title -- Заголовок формы
 * @var int $abon_id -- ID абонента
 * @var float $amount -- Сумма платежа
 * @var array $firm_list -- Список обслуживающих предприятий
 * @var array $ppp_list -- пункты приёма платежей (ППП)
 */

// debug($ppp_list, '$ppp_list');

?>
<h1 class="h5 text-center mb-4"><?=h($title);?></h1>

<?php if (empty($ppp_list)): ?>
        <p class="badge text-bg-warning">
            <?= __('No payment methods available');?>. <br>
            <?= __('Please contact your master to choose another payment method');?>.
        </p>
<?php else: ?>
    <?php foreach ($ppp_list as $i => $ppp): ?>
        <div class="text-center">

            <!-- Оплата через LiqPay -->

            <?php  if(str_contains($ppp[Ppp::F_API_TYPE], Bank::API_TYPE_P24_LIQPAY)) : ?>
                <?php
                    $liqpay = new LiqPay($ppp[Ppp::F_API_LIQPAY_PUBLIC], $ppp[Ppp::F_API_LIQPAY_PRIVATE]);
                    $html_btn = $liqpay->cnb_form([
                        'version'     => '3',
                        'action'      => 'pay',
                        'amount'      => $amount,
                        'currency'    => 'UAH',
                        'description' => 'Дог. ' . $abon_id,
                        'order_id'    => $abon_id,
                        'language'    => Lang::code(),
                    ]);
                ?>
                <div class="card my-5">
                    <div class="card-header">
                        <h5 class="card-title"><?=__('Personal account replenishment via');?> LiqPay</h5>
                    </div>
                    <div class="card-body">
                        <p><?=__('Personal account replenishment');?>: <?=h($abon_id);?><br>
                           <?=__('For the amount of');?>: <?=h(number_format((float)$amount, 2, ',', ' ') . ' ' . __('UAH') . '.');?></p>
                        <?php if (Theme::id() == Theme::F_ID_LIGHT) : ?>
                        <span class="btn btn-light my-3 px-1">
                        <?php else : ?>
                        <span class="btn btn-dark my-3 px-1">
                        <?php endif; ?>
                            <?= $html_btn; ?>
                        </span>
                        <p><?=__('Payment is usually credited to a personal account within 10 minutes');?>.</p>
                        <p><?=__('If the payment has not been received, please contact the master');?>.</p>
                    </div>
                    <div class="card-footer">
                        <?=__('Technical support phones');?>: <?=url_tel_all($ppp[Ppp::F_SUPPORT_PHONES]);?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Оплата через 24Pay -->

            <?php if(str_contains($ppp[Ppp::F_API_TYPE], Bank::API_TYPE_P24PAY)) : ?>

                <div class="card my-5">
                    <?php $title_for = __('Payment via the Privatbank form'); ?>
                    <div class="card-header">
                        <h5 class="card-title"><?=__('Personal account replenishment via');?> 24Pay</h5>
                    </div>
                    <div class="card-body">
                        <p><?=__('Personal account replenishment');?>: <?=h($abon_id);?><br>
                        <?=__('For the amount of');?>: <?=h(number_format((float)$amount, 2, ',', ' ') . ' ' . __('UAH') . '.');?></p>
                        <?php if (Theme::id() == Theme::F_ID_LIGHT) : ?>
                            <a href="<?= $ppp[Ppp::F_API_24PAY_URL]; ?>" class="btn btn-light my-3 px-1" target="_blank">
                                <img src='/img/p24/p24/24_Pay_Black.svg' style='max-width: 176px; max-height:70px;' alt='24Pay' title='<?=$title_for;?> (24)Pay'>
                            </a>
                        <?php else : ?>
                            <a href="<?= $ppp[Ppp::F_API_24PAY_URL]; ?>" class="btn btn-dark my-3 px-1" target="_blank">
                                <img src='/img/p24/p24/24_Pay_White.svg' style='max-width: 176px; max-height:70px;' alt='24Pay' title='<?=$title_for;?> (24)Pay'>
                            </a>
                        <?php endif; ?>
                        <p><?=__('Payment is usually credited to a personal account within 10 minutes');?>.</p>
                        <p><?=__('If the payment has not been received, please contact the master');?>.</p>
                    </div>
                    <div class="card-footer">
                        <?=__('Technical support phones');?>: <?=url_tel_all($ppp[Ppp::F_SUPPORT_PHONES]);?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Реквизиты для самостоятельной оплаты на расчетный счет -->

            <?php if(str_contains($ppp[Ppp::F_API_TYPE], Bank::API_TYPE_P24_ACC)) : ?>

                <div class="card my-5">
                    <div class="card-header">
                        <h5 class="card-title"><?=__('Details for self-payment to a bank account');?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th class="text-end"><?=__('Recipient');?></th>
                                <td class="text-start">
                                    <?= h($firm_list[$ppp[Ppp::F_FIRM_ID]][Firm::F_NAME_SHORT]); ?>,
                                    <span class="text-muted small text-nowrap">
                                        (<?=__('USRPOU');?>:
                                        <?= h($firm_list[$ppp[Ppp::F_FIRM_ID]][Firm::F_COD_EDRPOU]); ?>.)
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-end"><?=h($ppp[Ppp::F_NUMBER_PREFIX]);?></th>
                                <td class="text-start"><?= h($ppp[Ppp::F_NUMBER]); ?></td>
                            </tr>
                            <tr>
                                <th class="text-end"><?=__('Recipient bank');?></th>
                                <td class="text-start"><?= h($firm_list[$ppp[Ppp::F_FIRM_ID]][Firm::F_BANK_NAME]); ?></td>
                            </tr>
                            <tr>
                                <th class="text-end"><?=__('Purpose of payment');?></th>
                                <td class="text-start"><?=h(Ppp::applyTemplates($ppp[Ppp::F_NUMBER_PURPOSE], [Ppp::TMPL_PORT => $abon_id]));?></td>
                            </tr>
                            <tr>
                                <th class="text-end"><?=__('Recommended amount for payment');?></th>
                                <td class="text-start"><?= h(number_format((float)$amount, 2, ',', '') . ' ' . __('UAH') . '.'); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <?=__('Technical support phones');?>: <?=url_tel_all($ppp[Ppp::F_SUPPORT_PHONES]);?>
                    </div>
                </div>
            <?php endif; ?>

            
            <!-- Реквизиты для самостоятельной оплаты с помощью банковской карты -->

            <?php if(str_contains($ppp[Ppp::F_API_TYPE], Bank::API_TYPE_BANK_CARD)) : ?>
                <div class="card my-5">
                    <div class="card-header">
                        <h5 class="card-title"><?=__('Details for self-payment using a bank card');?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th class="text-end"><?=__('Recipient');?></th>
                                <td class="text-start">
                                    <?= h($firm_list[$ppp[Ppp::F_FIRM_ID]][Firm::F_NAME_SHORT]); ?>,
                                    <span class="text-muted small text-nowrap">
                                        (<?=__('USRPOU');?>:
                                        <?= h($firm_list[$ppp[Ppp::F_FIRM_ID]][Firm::F_COD_EDRPOU]); ?>.)
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-end"><?=h($ppp[Ppp::F_NUMBER_PREFIX]);?></th>
                                <td class="text-start text-nowrap"><?= h($ppp[Ppp::F_NUMBER]); ?></td>
                            </tr>
                            <tr>
                                <th class="text-end"><?=__('Recommended amount for payment');?></th>
                                <td class="text-start"><?= h(number_format((float)$amount, 2, ',', '') . ' ' . __('UAH') . '.'); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><?=__('After payment, inform your master about the payment in a way that is convenient for you');?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <?=__('Technical support phones');?>: <?=url_tel_all($ppp[Ppp::F_SUPPORT_PHONES]);?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>


<div class="col-12 d-flex justify-content-center gap-2">
    <a href="/" class="btn btn-secondary w-25" style="min-width:120px;"><?=__('Return');?></a>
</div>
