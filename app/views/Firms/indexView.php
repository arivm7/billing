<?php
/**
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Firms/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\Module;
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h3 mb-0"><?= h($title) ?></h1>
        <div class="text-muted"><?= __('User ID | ID пользователя | ID користувача') ?>: <?= (int) $list_user_id ?></div>
    </div>

    <ul class="nav nav-tabs" id="firmsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="firms-my-tab" data-bs-toggle="tab" data-bs-target="#firms-my" type="button" role="tab">
                <?= __('My enterprises | Мои предприятия | Мої підприємства') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="firms-providers-tab" data-bs-toggle="tab" data-bs-target="#firms-providers" type="button" role="tab">
                <?= __('Providers | Провайдеры | Провайдери') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="firms-abons-tab" data-bs-toggle="tab" data-bs-target="#firms-abons" type="button" role="tab">
                <?= __('Enterprises of my abonents | Предприятия моих абонентов | Підприємства моїх абонентів') ?>
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 p-3">
        <div class="tab-pane fade show active" id="firms-my" role="tabpanel" aria-labelledby="firms-my-tab">
            <?php require DIR_INC . '/firms_my.php'; ?>
        </div>
        <div class="tab-pane fade" id="firms-providers" role="tabpanel" aria-labelledby="firms-providers-tab">
            <?php require DIR_INC . '/firms_providers.php'; ?>
        </div>
        <div class="tab-pane fade" id="firms-abons" role="tabpanel" aria-labelledby="firms-abons-tab">
            <?php require DIR_INC . '/firms_abons.php'; ?>
        </div>
    </div>
</div>