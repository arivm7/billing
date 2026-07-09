<?php
/**
 *  Project : my.ri.net.ua
 *  File    : invoice_view.php
 *  Path    : app/views/inc/invoice_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Dec 2025 20:10:43
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Отображение записи одного счёта в виде компактной таблицы
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\Api;
use billing\core\Pagination;
use config\Icons;
use config\tables\Abon;
use config\tables\Firm;
use config\tables\Invoice;
use config\tables\Module;
use config\tables\PA;
use config\tables\User;
use config\tables\Pay;


require_once DIR_LIBS . '/compare_functions.php';

/**
 * Переменные полученные из контроллера
 * 
 * @var string $title          -- заголовок страницы
 * @var array $abon            -- запись абонента
 * @var array $user            -- запись пользователя
 * @var array $rest            -- остатки ЛА Абонента
 * @var Pagination $pager      -- страничный навигатор
 * @var array $invoices        -- список счетов
 * @var array $agent_list      -- список предприятий провайдера
 * @var array $contragent_list -- список предприятий абонента
 * 
 * Данные из app/views/Invoice/listView.php
 * 
 * @var array $item  -- Одна запись Счёта
 * 
 */

$item_agent = (isset($agent_list[$item[Invoice::F_AGENT_ID]]) 
        ? $agent_list[$item[Invoice::F_AGENT_ID]]
        : [ Firm::F_NAME_SHORT => '-' ]
    );

$item_contragent = (isset($contragent_list[$item[Invoice::F_CONTRAGENT_ID]]) 
        ? $contragent_list[$item[Invoice::F_CONTRAGENT_ID]]
        : [ Firm::F_NAME_SHORT => '-' ]
    );

?>
<div class="card mb-4 w-100 min-w-700">
    <form action="" method="post">
        <div class="card-header fs-7">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <!-- ID -->
                    <span class="text-secondary">[<span class="text-secondary"><?=num_len($item[Invoice::F_ID], 7);?></span>] <?= __('Invoice / Act | Счёт-фактра / Акт | Рахунок-фактра / Акт') ?></span><br>
                    <!-- Контрагент (Абонент) -->
                    <span class="text-secondary">[<span class="text-info"><?=num_len($item[Invoice::F_ABON_ID], 7);?></span>] <?= $abon[Abon::F_ID] ?>. <?= $abon[Abon::F_ADDRESS] ?></span>
                </div>
                <div>
                    <nobr>

                    <!--  Кнопка печати Счёта/Акта -->
                    <?php 
                        /**
                         * Нужен элемент 
                         * @var array $item -- Ассоциативный массив с данными Счёта
                         */
                        include DIR_INC . '/invoice_button_print.php'; 
                    ?>

                    <!-- Статус оплачен ли счёт -->
                    <?php if (can_edit(Module::MOD_INVOICES)): ?>
                        <div class="btn btn-sm btn-outline-success me-1 px-1 py-1">
                            <?php if ($item[Invoice::F_IS_PAID]): ?>
                                <img src="<?= Icons::SRC_ICON_UAH_OK ?>" alt="[Ok]" height="26px" title="<?= __('Payment confirmed | Оплата подтверждена | Оплату підтверджено') ?>">
                            <?php else: ?>
                                <?php if (can_edit(Module::MOD_INVOICES)): ?>
                                    <a href="<?= Api::URI_CMD ?>?<?= Api::F_CMD ?>=<?= Api::CMD_INVOICE_PAY_CONFIRM ?>&<?= Api::F_INVOICE_ID ?>=<?= $item[Invoice::F_ID] ?>">
                                        <img src="<?= Icons::SRC_ICON_UAH_QUERY ?>" alt="[?]" height="26px" title="<?= __('Payment NOT confirmed | Оплата НЕ подтверждена | Оплата НЕ підтверджена') . CR . __('Click to confirm payment | Нажмите для подтверждения платежа | Натисніть, щоб підтвердити платеж') ?>">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= Icons::SRC_ICON_UAH_QUERY ?>" alt="[?]" height="26px" title="<?= __('Payment NOT confirmed | Оплата НЕ подтверждена | Оплата НЕ підтверджена') ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Редактировать -->
                    <?php if (can_edit(Module::MOD_INVOICES)): ?>
                        <a href="<?= Invoice::URI_EDIT ?>/<?= $item[Invoice::F_ID] ?>" class="btn btn-sm btn-outline-success me-1" title="<?= __('Edit | Редактировать | Редагувати'); ?>"><i class="bi bi-pencil-square"></i></a>
                    <?php endif; ?>
                    </nobr>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class='table table-striped table-hover table-bordered'>
                <!-- Исполнитель. Провайдер. Агент -->
                <tr title="<?= __('Enterprise-Provider | Предприятие-Провайдер | Підприємство-провайдер') ?>, <?=CR;?><?= __('An enterprise tied to a technical site where maintenance is performed | Предприятие, привязанное к технической площадке, на котоорой производится обслуживание | Підприємство, прив\'язане до технічного майданчика, на якому здійснюється обслуговування') ?>.">
                    <td><?= __('Agent | Агент | Агент') ?></td>
                    <td><span class="text-secondary fs-6 font-monospace"><?= num_len($item[Invoice::F_AGENT_ID], 3) ?></span> <?= $item_agent[Firm::F_NAME_SHORT] ?></td>
                </tr>
                <!-- Заказчик. Абонент. Контрагент -->
                <tr title="<?= __('Enterprise-Subscriber | Предприятие-Абонент | Підприємство-Абонент') ?>,<?=CR;?><?= __('User-linked enterprise | Предприятие, привязанное к пользователю | Підприємство, прив\'язане до користувача') ?>.">
                    <td><?= __('Counterparty | Контрагент | Контрагент') ?></td>
                    <td><span class="text-secondary fs-6 font-monospace"><?= num_len($item[Invoice::F_CONTRAGENT_ID], 3) ?></span> <?= $item_contragent[Firm::F_NAME_SHORT] ?></td>
                </tr>
                <!-- -->
                <tr>
                    <td>
                    </td>
                    <td>
                        <table class='table table-striped table-hover table-bordered'>
                            <tr>
                                <!-- СФ № -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Invoice No. | Счёт-фактура № | Рахунок-фактура №') ?></span>
                                        <span class="text-success fs-6 fw-bold"><?=$item[Invoice::F_INV_NO]?></span>
                                    </div>
                                </td>
                                <!-- Дата счёта (строка) -->
                                <td width="32%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Invoice date | Дата счёта | Дата рахунку') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_INV_DATE_STR]?></span>
                                    </div>
                                </td>
                                <!-- Дата акта (строка) -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Act date | Дата акта | Дата акту') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_AKT_DATE_STR]?></span>
                                    </div>
                                </td>
                            </tr>
                            <!-- Предприятие-плательщик -->
                            <tr title="<?= __('Line [Payer] indicated in the invoice | Строка [Плательщик] указываемая в счёте | Рядок [Платник] вказується в рахунку') ?>.">
                                <td><span class="text-secondary px-3"><?= __('Payer | Плательщик | Платник') ?></span></td>
                                <td colspan="2"><span class="text-success px-3"><?=$item[Invoice::F_FIRM_PAYER_STR]?></span></td>
                            </tr>
                            <tr>
                                <!-- Цена за 1 (float) -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Price for 1 | Цена за 1 | Ціна за 1') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_COST_1]?></span>
                                    </div>
                                </td>
                                <!-- Кол-во (float) -->
                                <td width="32%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Quantity | Количество | Кількість') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_COUNT]?></span>
                                    </div>
                                </td>
                                <!-- Цена всего (float) -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Price of everything | Цена всего | Ціна всього') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_COST_ALL]?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- Назначение платежа -->
                                <td><span class="text-secondary px-3"><?= __('Purpose of payment | Назначение платежа | Призначення платежу') ?></span></td>
                                <td colspan="2"><span class="text-success px-3"><?=$item[Invoice::F_TEXT]?></span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>
        <?php if (can_view(Module::MOD_INVOICES)): ?>
        <div class="card-footer m-0">
            <div class='d-flex justify-content-between align-items-end'>
                <div class="w-40 text-start text-secondary font-monospace fs-9">
                    <div>
                        <!-- Кто создал -->
                        <span title="<?= __user(user_id: $item[Invoice::F_CREATION_UID], field:User::F_NAME_FULL) ?>">Creation UID [<span class="text-info"><?=$item[Invoice::F_CREATION_UID]?></span>]</span>
                        <!-- Когда создал -->
                        <span> [<?=date('Y-m-d H:i:s', $item[Invoice::F_CREATION_DATE])?>]</span><br>
                    </div>
                    <div>
                        <!-- Кто изменил -->
                        <span title="<?= __user(user_id: $item[Invoice::F_MODIFIED_UID], field: User::F_NAME_FULL) ?>">Modified UID [<span class="text-info"><?=$item[Invoice::F_MODIFIED_UID]?></span>]</span>
                        <!-- Когда изменил -->
                        <span> [<?=date('Y-m-d H:i:s', $item[Invoice::F_MODIFIED_DATE])?>]</span>
                    </div>
                </div>
                <div class="w-60 text-end text-secondary font-monospace fs-8">
                    <?php if ($item[Invoice::F_PAYMENTS] ?? null): ?>
                        <?php foreach ($item[Invoice::F_PAYMENTS] as $pay): ?>
                            <div class="card shadow-sm">
                              <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="fw-bold"><?= __('Payment | Платёж | Платіж') ?> <a href="<?= Pay::URI_FORM; ?>/<?= $pay[Pay::F_ID]; ?>" target="_blank" title="<?= __('Go to the payment editing form | Перейти в форму редактирования платежа | Перейти до форми редагування платежу') ?>">#<?= $pay[Pay::F_ID]; ?></a></span>
                                        <span class="g-1 small"><?= date('Y-m-d H:i', $pay['pay_date']) ?></span>
                                    </div>
                                    <div>
                                        <?php $class1 = (cmp_float($pay[Pay::F_PAY_FAKT], $item[Invoice::F_COST_ALL]) == 0 ? "bg-success" : "bg-warning"); ?>
                                        <?php $class2 = (cmp_float($pay[Pay::F_PAY_ACNT], $item[Invoice::F_COST_ALL]) == 0 ? "bg-success" : "bg-warning"); ?>
                                        FAKT:&nbsp;<span class="badge <?= $class1; ?>" title="<?= Pay::field_title(Pay::F_PAY_FAKT) ?>"><?= $pay[Pay::F_PAY_FAKT]; ?></span>
                                        ACNT:&nbsp;<span class="badge <?= $class2; ?>" title="<?= Pay::field_title(Pay::F_PAY_ACNT) ?>"><?= $pay[Pay::F_PAY_ACNT]; ?></span>
                                    </div>
                                </div>
                                <hr class="my-1">
                                <div class="small text-muted text-start"><?= $pay['description']; ?></div>
                              </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>
