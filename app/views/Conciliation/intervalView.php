<?php
/**
 *  Project : my.ri.net.ua
 *  File    : intervalView.php
 *  Path    : app/views/Conciliation/intervalView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Nov 2025 04:16:28
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of intervalView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\User;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;

require_once DIR_LIBS . '/form_functions.php';

/** 
 * Данны переданные из контроллера
 * 
 * @var array $user 
 * @var array $abon
 * 
 */

?>
<div class="card col-12 col-md-10 col-lg-8">

    <div class="card-header">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-3"><?=__('Select the period for drawing up the Reconciliation Report')?></h2>
                <h3 class="h4 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> |</span> <?=$user[User::F_NAME_FULL];?>:</h3>
            </div>
            <div>
                <!-- Список платежей -->
                <?php if (can_view([Module::MOD_MY_PAYMENTS, Module::MOD_PAYMENTS])) : ?>
                    <a href="<?=Pay::URI_LIST;?>/<?=$abon[Abon::F_ID];?>" class="btn btn-outline-info btn-sm me-1" target="_self" title="<?= __('Full list of subscriber payments'); ?>">
                        <span class="fw-bold">₴₴</span> <?= __('Payments'); ?></a>
                <?php endif; ?>
                <!-- Вернуться в карточку абонента -->
                <?php if (can_use([Module::MOD_ABON])) : ?>
                    <a href="<?=Abon::URI_VIEW;?>/<?=$abon[Abon::F_ID];?>" class="btn btn-outline-info btn-sm" target="_self"><span class="fw-bold">🅐</span> <?= __('Картка'); ?></a> <!-- ⒶⒶⒶ -->
                <?php else: ?>
                    <a href="/my" class="btn btn-outline-info btn-sm" target="_self"><span class="fw-bold">🅐</span> <?= __('Картка'); ?></a> <!-- ⒶⒶ🅐Ⓐ(A) -->
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body">
        <?php require DIR_INC ."/conciliation_intervals.php"; ?>
    </div>
</div>
