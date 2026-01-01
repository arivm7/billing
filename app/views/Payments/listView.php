<?php
/*
 *  Project : my.ri.net.ua
 *  File    : listView.php
 *  Path    : app/views/Payments/listView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of listView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use app\controllers\MyController;
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
 * @var array $user
 * @var Pagination $pager
 * @var array $payments
 */

/** 
 * @var array $pay_one — запись из таблицы payments 
 */

$view_all = can_view([Module::MOD_PAYMENTS]);
$view_my = can_view(Module::MOD_MY_PAYMENTS) && $user[User::F_ID] == $user[Abon::REC][Abon::F_USER_ID];

?>
<?php if ($view_all || $view_my) : ?>
<div class="mx-auto w-auto">

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="fs-6 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> ::</span> <?=$user[User::F_NAME_FULL];?>:</h3>
        </div>
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <span class="text text-secondary"><?=num_len($user[Abon::REC][Abon::F_ID], 6);?> ::</span> <?=$user[Abon::REC][Abon::F_ADDRESS];?>
            </div>
            <div>
                <!-- Внесение платежа -->
                <?php if (can_add([Module::MOD_PAYMENTS])) : ?>
                    <a href="<?=Pay::URI_FORM;?>?<?=Abon::F_GET_ID;?>=<?=$user[Abon::REC][Abon::F_ID];?>" class="btn btn-outline-info btn-sm me-1" target="_self"><span class="fw-bold">+₴</span> <?= __('Внести платіж'); ?></a>
                <?php endif; ?>
                <!-- Вернуться в карточку абонента -->
                <?php if (can_use([Module::MOD_ABON])) : ?>
                    <a href="<?=Abon::URI_VIEW;?>/<?=$user[Abon::REC][Abon::F_ID];?>" class="btn btn-outline-info btn-sm" target="_self"><span class="fw-bold">🅐</span> <?= __('Картка'); ?></a> <!-- ⒶⒶⒶ -->
                <?php else: ?>
                    <a href="/my" class="btn btn-outline-info btn-sm" target="_self"><span class="fw-bold">ⒶⒶ🅐Ⓐ(A)</span> <?= __('Картка'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?=$pager;?>
    <table class="table table-bordered table-striped table-hover align-middle min-w-75 w-auto mx-auto">
        <thead>
            <tr>
                <th class="text-center align-middle"><?=__('Pay Date');?></th>
                <th class="text-center align-middle" title="<?=__('Actual received amount');?>"><?=__('Pay Fakt');?></th>
                <?php if ($view_all) : ?>
                <th class="text-center align-middle" title="<?=__('Amount credited to account');?>"><?=__('on ACC');?></th>
                <th class="text-center align-middle"><?=__('Description');?></th>
                <th class="text-center align-middle"><?=__('Bank No');?></th>
                <th class="text-center align-middle"><?=__('Service info about payment');?></th>
                <th class="text-center align-middle"><?=__('Действия');?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $pay_one) : ?>
            <tr>
                <td class="text-center align-middle">
                    <?php if ($view_all) : ?>
                        <?=str_replace(' ', '<br>', date('Y-m-d H:i:s', $pay_one[Pay::F_DATE]))?>
                    <?php else : ?>
                        <?=date('Y-m-d H:i:s', $pay_one[Pay::F_DATE])?>
                    <?php endif; ?>
                </td>
                <td class="text-end align-middle text-nowrap"><?=number_format($pay_one[Pay::F_PAY_FAKT], 2, '.', ' ')?></td>
                <?php if ($view_all) : ?>
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
                    <?php if (can_edit(Module::MOD_PAYMENTS)) : ?>
                        <a href="<?=Pay::URI_FORM;?>/<?=h($pay_one[Pay::F_ID]);?>" 
                            class="btn btn-sm btn-outline-info" 
                            title="<?=__('Edit');?>"><img src="<?=Icons::SRC_EDIT_REC;?>" alt="[Edit]" height="22px"></a>
                    <?php endif; ?>
                    <?php if (can_del(Module::MOD_PAYMENTS)) : ?>
                        <a href="<?=Pay::URI_DEL;?>/<?=h($pay_one[Pay::F_ID]);?>" 
                            class="btn btn-sm btn-outline-danger" 
                            onclick="return confirm('[X] <?=__('Удалить этот платёж?');?>');"
                            title="<?=__('Delete');?>"><img src="<?=Icons::SRC_ICON_TRASH;?>" alt="[Del]" height="22px"></a>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?=$pager;?>
</div>
<?php endif; ?>