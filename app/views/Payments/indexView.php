<?php
/*
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Payments/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\User;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

require_once DIR_LIBS . '/form_functions.php';

?>
<div class="container my-3">
    <?php if (can_use([Module::MOD_MY_PAYMENTS, Module::MOD_PAYMENTS])) : ?>
        <h2 class="fs-4 mb-3"><?=__('Select a subscriber connection to view payments')?></h2>
        <h3 class="fs-6 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> |</span> <?=$user[User::F_NAME_FULL];?>:</h3>
        <?php if ($user[Abon::TABLE]) : ?>
            <?php foreach ($user[Abon::TABLE] as $abon) : ?>
                <div class="card">
                  <div class="card-header">
                    <?=$abon[Abon::F_ID];?> :: <?=$abon[Abon::F_ADDRESS];?>
                  </div>
                  <div class="card-body">
                        <?php if ($abon['is_payer']) : ?>
                            <a class="btn btn-outline-primary btn-sm small" href="<?= Pay::URI_MY_LIST . '/' . $abon[Abon::F_ID]; ?>"><?=__('Show payment list');?></a>
                        <?php else: ?>
                            <a class="btn btn-outline-secondary btn-sm small" href="<?= Pay::URI_MY_LIST . '/' . $abon[Abon::F_ID]; ?>"><?=__('Show payment list');?></a>
                        <?php endif; ?>
                  </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class='alert alert-info' role='alert'><?=__('No subscriber connections')?></div>
        <?php endif; ?>
    <?php else : ?>
        <div class='alert alert-info' role='alert'><?=__('No permission to view this module')?></div>
    <?php endif; ?>
</div>