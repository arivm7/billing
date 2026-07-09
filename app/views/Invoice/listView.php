<?php
/**
 *  Project : my.ri.net.ua
 *  File    : listView.php
 *  Path    : app/views/Invoice/listView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Dec 2025 20:11:22
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of listView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



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
 */

use billing\core\Pagination;
use app\controllers\InvoiceController;
use config\tables\Abon;
use config\tables\Invoice;
use config\tables\Module;
use config\tables\User;

?>

<div class="row justify-content-center">
<div class="col-12 col-md-10 col-lg-8">
    <div class="card mb-4 w-100 min-w-700">
        <div class="card-header mb-3">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h3 class="fs-4"><?= $title ?> <span class="text-secondary">[AID <?= num_len($abon[Abon::F_ID], 6) ?>]</span></h3>
                    <h5 class="text-secondary fs-6">
                        <span title="User ID"><?= num_len($user[User::F_ID], 6); ?></span> :: 
                        <span title="User Name"><?= h($user[User::F_NAME_SHORT]); ?></span>
                    </h5>
                    <h5 class="text-secondary fs-6">
                        <span title="Абон ID"><?= num_len($abon[Abon::F_ID], 6); ?></span> :: 
                        <span title="Abon Address"><?= h($abon[Abon::F_ADDRESS]); ?>
                    </h5>

                </div>
                <div class="ms-1 text-end">
                    <?php if (can_use(Module::MOD_USER_CARD)): ?>
                        <a href="<?=Invoice::URI_EDIT;?>?<?= Invoice::F_ABON_ID ?>=<?=$abon[Abon::F_ID];?>&<?= Invoice::F_INV_DATE_STR ?>=<?= date('d.m.Y'); ?>" class="btn btn-outline-info btn-sm" target="_blank" title="<?= __('Generate new invoice | Сформировать новый Счёт | Створити новий рахунок'); ?>"><i class="bi bi-receipt"></i> <?= __('New invoice | Новый счёт | Новий рахунок'); ?></a>
                        <a href="<?=Abon::URI_VIEW;?>/<?=$abon[Abon::F_ID];?>" class="btn btn-outline-info btn-sm" target="_self" title="<?= __('Go to subscriber card | Перейти к карточке абонента | Перейти до картки абонента'); ?>"><span class="fw-bold">🅐</span> <?= __('Card | Карта | Картка'); ?></a> <!-- ⒶⒶ🅐Ⓐ(A) -->
                        <br>
                        <?php foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $month_no) : ?>
                            <?php
//                                if ($month_no == 7) { echo '<br>'; }
                                $rec[Invoice::F_ABON_ID] = $abon[Abon::F_ID];
                                $rec[Invoice::F_INV_TODAY] = mktime(0, 0, 0, $month_no, 1, date('Y'));
                                $query = http_build_query($rec);
                            ?>
                            <a href="<?=Invoice::URI_EDIT;?>?<?= $query ?>" class="btn btn-outline-info btn-sm mt-1" target="_blank" title="<?= __('Generate new invoice | Сформировать новый Счёт | Створити новий рахунок'); ?>"><i class="bi bi-receipt"></i> <?= $month_no; ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="/" class="btn btn-outline-info btn-sm" target="_self" title="<?= __('Return to subscriber card | Вернуться в карточку абонента | Повернутися до картки абонента'); ?>"><span class="fw-bold">🅐</span> <?= __('Card | Карта | Картка'); ?></a> <!-- ⒶⒶ🅐Ⓐ(A) -->
                    <?php endif; ?>

                </div>
                
            </div>

        </div>
        <?php require DIR_INC . '/pager.php'; ?>
        <div class="card-body">
            <?php foreach ($invoices as $item) : ?>
                <?php require DIR_INC . '/invoice_view.php'; ?>
                <!-- <?php // require DIR_INC . '/invoice_form.php'; ?> -->
            <?php endforeach; ?>
        </div>
        <?php require DIR_INC . '/pager.php'; ?>
        <div class="card-footer">
            |
        </div>
    </div>
</div>
</div>

