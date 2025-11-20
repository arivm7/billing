<?php
/*
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Conciliation/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:25:06
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\Conciliation;
use config\tables\Abon;
use config\tables\Module;
use config\tables\User;
require_once DIR_LIBS . '/form_functions.php';

?>

<div class="container my-3">
    <?php if (can_use([Module::MOD_MY_CONCILIATION, Module::MOD_CONCILIATION])) : ?>
        <h2 class="h4 mb-3"><?=__('Select the contract number to display the Reconciliation Report')?></h2>
        <h3 class="h4 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> |</span> <?=$user[User::F_NAME_FULL];?>:</h3>
        <?php if ($user[Abon::TABLE]) : ?>
            <?php foreach ($user[Abon::TABLE] as $abon) : ?>
                <div class="card">
                    <div class="card-header">
                        <?=get_html_content_left_right(
                            left: "" . num_len($abon[Abon::F_ID], 6) . " :: " . $abon[Abon::F_ADDRESS] . "",
                            right: ($abon['is_payer']
                                        ? "<span class='badge bg-success'>" . __('Abonent') . "</span>"
                                        : "<span class='badge bg-secondary'>" . __('Disabled') . "</span>"
                            ) . '&nbsp;&nbsp;&nbsp;',
                            add_class: 'w-100');?>
                    </div>
                    <div class="card-body">
                        <?php if ($abon['is_payer']) : ?>
                            <a class="btn btn-outline-primary btn-sm small" href="<?= Conciliation::URI_INTERVALS . '/' . $abon[Abon::F_ID]; ?>"><?=__('Select an interval');?></a>
                        <?php else: ?>
                            <a class="btn btn-outline-secondary btn-sm small" href="<?= Conciliation::URI_INTERVALS . '/' . $abon[Abon::F_ID]; ?>"><?=__('Select an interval');?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class='alert alert-info' role='alert'><?=__('There are no subscriber connections')?></div>
        <?php endif; ?>
    <?php else : ?>
        <div class='alert alert-info' role='alert'><?=__('No information to display')?></div>
    <?php endif; ?>
</div>
