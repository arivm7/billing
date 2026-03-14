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
 * Description of invoice_view.php
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
                    <span class="text-secondary">[<span class="text-secondary"><?=num_len($item[Invoice::F_ID], 7);?></span>] <?= __('Счёт-фактра / Акт') ?></span><br>
                    <!-- Контрагент (Абонент) -->
                    <span class="text-secondary">[<span class="text-info"><?=num_len($item[Invoice::F_ABON_ID], 7);?></span>] <?= $abon[Abon::F_ID] ?>. <?= $abon[Abon::F_ADDRESS] ?></span>
                </div>
                <div>
                    <nobr>

                    <!--  Кнопка печати Счёта/Акта -->
                    <button type="button" class="btn btn-sm btn-outline-success me-1 px-1 py-1" 
                        data-bs-toggle="modal" data-bs-target="#printModalForm"
                        title="<?= __('Печать Счёта/Акта разными способами') ?>">
                        <img src="<?= Icons::SRC_ICON_PRINT ?>" alt="Печать" height="28px">
                    </button>

                    <!-- Модальная форма выбора способа печати Счёта/Акта -->
                    <div class="modal fade" id="printModalForm" tabindex="-1" aria-labelledby="printModalFormLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="printModalFormLabel">Выберите форму печати Счёта/Акта</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    
                                    <?php include DIR_INC . '/invoice_print_form.php'; ?>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <!-- <button type="button" class="btn btn-primary">Print</button> -->
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Статус оплачен ли счёт -->
                    <?php if (can_edit(Module::MOD_INVOICES)): ?>
                        <div class="btn btn-sm btn-outline-success me-1 px-1 py-1">
                            <?php if ($item[Invoice::F_IS_PAID]): ?>
                                <img src="<?= Icons::SRC_ICON_UAH_OK ?>" alt="[Ok]" height="26px" title="<?= __('Оплата подтверждена') ?>">
                            <?php else: ?>
                                <?php if (can_edit(Module::MOD_INVOICES)): ?>
                                    <a href="<?= Api::URI_CMD ?>?<?= Api::F_CMD ?>=<?= Api::CMD_INVOICE_PAY_CONFIRM ?>&<?= Api::F_INVOICE_ID ?>=<?= $item[Invoice::F_ID] ?>">
                                        <img src="<?= Icons::SRC_ICON_UAH_QUERY ?>" alt="[?]" height="26px" title="<?= __('Оплата НЕ подтверждена') . CR . __('Нажмите для подтверждения платежа') ?>">
                                    </a>
                                <?php else: ?>
                                    <img src="<?= Icons::SRC_ICON_UAH_QUERY ?>" alt="[?]" height="26px" title="<?= __('Оплата НЕ подтверждена') ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Редактировать -->
                    <?php if (can_edit(Module::MOD_INVOICES)): ?>
                        <a href="<?= Invoice::URI_EDIT ?>/<?= $item[Invoice::F_ID] ?>" class="btn btn-sm btn-outline-success me-1" title="<?= __('Редактировать'); ?>"><i class="bi bi-pencil-square"></i></a>
                    <?php endif; ?>
                    </nobr>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class='table table-striped table-hover table-bordered'>
                <!-- Исполнитель. Провайдер. Агент -->
                <tr title="<?= __('Предприятие-Провайдер') ?>, <?=CR;?><?= __('Предприятие, привязанное к ТП, на котоорой производится обслуживание') ?>.">
                    <td><?= __('Агент') ?></td>
                    <td><span class="text-secondary fs-6 font-monospace"><?= num_len($item[Invoice::F_AGENT_ID], 3) ?></span> <?= $item_agent[Firm::F_NAME_SHORT] ?></td>
                </tr>
                <!-- Заказчик. Абонент. Контрагент -->
                <tr title="<?= __('Предприятие-Абонент') ?>,<?=CR;?><?= __('Предприятие, привязанное к пользователю') ?>.">
                    <td><?= __('Контрагент') ?></td>
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
                                        <span class="text-secondary"><?= __('СФ №') ?></span>
                                        <span class="text-success fs-6 fw-bold"><?=$item[Invoice::F_INV_NO]?></span>
                                    </div>
                                </td>
                                <!-- Дата счёта (строка) -->
                                <td width="32%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Дата счёта') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_INV_DATE_STR]?></span>
                                    </div>
                                </td>
                                <!-- Дата акта (строка) -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Дата акта') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_AKT_DATE_STR]?></span>
                                    </div>
                                </td>
                            </tr>
                            <!-- Предприятие-плательщик -->
                            <tr title="<?= __('Строка [Плательщик] указываемая в счёте') ?>.">
                                <td><span class="text-secondary px-3"><?= __('Плательщик') ?></span></td>
                                <td colspan="2"><span class="text-success px-3"><?=$item[Invoice::F_FIRM_PAYER_STR]?></span></td>
                            </tr>
                            <tr>
                                <!-- Цена за 1 (float) -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Цена за 1') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_COST_1]?></span>
                                    </div>
                                </td>
                                <!-- Кол-во (float) -->
                                <td width="32%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Количество') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_COUNT]?></span>
                                    </div>
                                </td>
                                <!-- Цена всего (float) -->
                                <td width="34%">
                                    <div class='d-flex justify-content-between align-items-center px-3'>
                                        <span class="text-secondary"><?= __('Цена всего') ?></span>
                                        <span class="text-success"><?=$item[Invoice::F_COST_ALL]?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- Назначение платежа -->
                                <td><span class="text-secondary px-3"><?= __('Назначение платежа') ?></span></td>
                                <td colspan="2"><span class="text-success px-3"><?=$item[Invoice::F_TEXT]?></span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>
        <?php if (can_view(Module::MOD_INVOICES)): ?>
        <div class="card-footer m-0">
            <div class='d-flex justify-content-between align-items-center'>
                <div class="text-start">
                    |
                </div>
                <div class="text-end text-secondary font-monospace fs-8">
                    <!-- Кто создал -->
                    <span title="<?= __user(user_id: $item[Invoice::F_CREATION_UID], field:User::F_NAME_FULL) ?>">Creation UID [<span class="text-info"><?=$item[Invoice::F_CREATION_UID]?></span>]</span>
                    <!-- Когда создал -->
                    <span> [<?=date('Y-m-d H:i:s', $item[Invoice::F_CREATION_DATE])?>]</span><br>
                    <!-- Кто изменил -->
                    <span title="<?= __user(user_id: $item[Invoice::F_MODIFIED_UID], field: User::F_NAME_FULL) ?>">Modified UID [<span class="text-info"><?=$item[Invoice::F_MODIFIED_UID]?></span>]</span>
                    <!-- Когда изменил -->
                    <span> [<?=date('Y-m-d H:i:s', $item[Invoice::F_MODIFIED_DATE])?>]</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>