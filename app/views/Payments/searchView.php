<?php
/**
 *  Project : my.ri.net.ua
 *  File    : searchView.php
 *  Path    : app/views/Payments/searchView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Apr 2026 21:22:28
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of searchView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


/**
 * Вывод списка найденных платежей
 * 
 * PaymentsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\Pagination;
use config\Icons;
use config\tables\Abon;
use config\tables\Module;
use config\tables\User;
use config\tables\Pay;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * Данные передаваемые из контроллера:
 * @var array $search_rec   -- искомая запись
 * @var Pagination $pager
 * @var array $payments     -- массив найденных записей
 */



/** 
 * @var array $pay_one — одна запись из таблицы payments 
 */


?>
<?php if (can_view([Module::MOD_PAYMENTS])) : ?>
<div class="mx-auto w-auto">

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="fs-6 mb-3">Поиск платежей по шаблону</h3>
        </div>
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                </span><?= debug($search_rec, '$search_rec');?>
            </div>
            <div>
                ?
            </div>
        </div>
    </div>

    <?=$pager;?>
    <table class="table table-bordered table-striped table-hover align-middle min-w-75 w-auto mx-auto">
        <thead>
            <tr>
                <th class="text-center align-middle"><?=__('ID Абонента');?></th>
                <th class="text-center align-middle"><?=__('Pay Date');?></th>
                <th class="text-center align-middle" title="<?=__('Actual received amount');?>"><?=__('Pay Fakt');?></th>
                <th class="text-center align-middle" title="<?=__('Amount credited to account');?>"><?=__('on ACC');?></th>
                <th class="text-center align-middle"><?=__('Description');?></th>
                <th class="text-center align-middle"><?=__('Bank No');?></th>
                <th class="text-center align-middle"><?=__('Service info about payment');?></th>
                <th class="text-center align-middle"><?=__('Действия');?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $pay_one) : ?>
            <tr>
                <td class="text-end align-middle text-nowrap"><?= url_abon_form($pay_one[Pay::F_ABON_ID])?></td>
                <td class="text-center align-middle">
                    <!-- <?= str_replace(' ', '<br>', date('d.m.Y H:i:s', $pay_one[Pay::F_DATE])) ?> 'Y-m-d H:i:s' -->
                    <?=date('d.m.Y H:i:s', $pay_one[Pay::F_DATE])?> <!-- 'Y-m-d H:i:s' -->
                </td>
                <td class="text-end align-middle text-nowrap"><?=number_format($pay_one[Pay::F_PAY_FAKT], 2, '.', ' ')?></td>
                <td class="text-end align-middle text-nowrap"><?=number_format($pay_one[Pay::F_PAY_ACNT], 2, '.', ' ')?></td>
                <td class="text-start align-middle"><?=nl2br(cleaner_html($pay_one[Pay::F_DESCRIPTION]))?></td>
                <td class="text-start align-middle"><?=h($pay_one[Pay::F_BANK_NO])?></td>
                <td class="text-start align-middle text-secondary small">
                    <span title="<?=__('Payment ID in DB');?>">ID: <?=h($pay_one[Pay::F_ID])?></span><br>
                    <span title="<?=__('Who entered payment into DB');?>"><?=h($pay_one[Pay::F_AGENT_ID])?> : <?=h($pay_one[Pay::F_AGENT_TITLE])?></span><br>
                    <span title="<?=__('Payment type');?>"><?=h($pay_one[Pay::F_TYPE_ID])?> : <?=h($pay_one[Pay::F_TYPE_TITLE])?></span><br>
                    <span title="<?=__('PAP through which payment came');?>"><?=h($pay_one[Pay::F_PPP_ID])?> : <?=h($pay_one[Pay::F_PPP_TITLE])?></span><br>
                    <span title="<?=__('Who and when modified record');?>"><?=date('Y-m-d H:i:s', $pay_one[Pay::F_MODIFIED_DATE])?> : <?=h($pay_one[Pay::F_MODIFIED_UID])?></span>
                </td>
                <td class="text-start align-middle">

                    <!-- Редактирование платежа -->
                    <?php if (can_edit(Module::MOD_PAYMENTS)) : ?>
                        <a href="<?=Pay::URI_FORM;?>/<?=h($pay_one[Pay::F_ID]);?>" 
                            class="btn btn-sm btn-outline-info" 
                            title="<?=__('Edit');?>"><img src="<?=Icons::SRC_EDIT_REC;?>" alt="[Edit]" height="22px"></a>
                    <?php endif; ?>
                    
                    <!-- Добавление платежа -->
                    <?php if (can_add([Module::MOD_PAYMENTS])) : ?>
                        <a href="<?=Pay::URI_FORM;?>?<?=Abon::F_GET_ID;?>=<?=h($pay_one[Pay::F_ABON_ID]);?>" class="btn btn-outline-info btn-sm me-1" target="_blank"><span class="fw-bold">+₴</span> <?= __('Добавить платёж'); ?></a>
                    <?php endif; ?>

                    <!-- Перейти в карточку абонента -->
                    <?php if (can_use([Module::MOD_ABON])) : ?>
                        <a href="<?=Abon::URI_VIEW;?>/<?=h($pay_one[Pay::F_ABON_ID]);?>" class="btn btn-outline-info btn-sm" target="_blank"><span class="fw-bold">🅐</span> <?= __('Картка'); ?></a> <!-- ⒶⒶⒶ -->
                    <?php endif; ?>
                    
                    <!-- Удаление платежа -->
                    <?php if (can_del(Module::MOD_PAYMENTS)) : ?>
                        <a href="<?=Pay::URI_DEL;?>/<?=h($pay_one[Pay::F_ID]);?>" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="return confirm(
                                    '[X] <?=__('Удалить этот платёж') . '? | ' . __('Важно') . ': ' . __('Это влияет на рассчёт баланса'); ?>');"
                            title="<?=__('Delete');?>"><img src="<?=Icons::SRC_ICON_TRASH;?>" alt="[Del]" height="22px"></a>
                    <?php endif; ?>

                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?=$pager;?>
</div>
<?php endif; ?>