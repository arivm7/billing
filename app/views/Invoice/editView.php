<?php
/**
 *  Project : my.ri.net.ua
 *  File    : editView.php
 *  Path    : app/views/Invoice/editView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Dec 2025 20:11:29
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of editView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Переменные полученные из контроллера
 * 
 * @var string $title          -- Заголовок страницы
 * @var array $abon            -- Запись абонента
 * @var array $user            -- Запись пользователя
 * @var array $invoice         -- Счёт/Акт
 * @var array $agent_list      -- Список предприятий провайдера
 * @var array $contragent_list -- Список предприятий абонента
 * 
 */

use config\tables\Abon;
use config\tables\User;

?>

<div class="row justify-content-center">
<div class="col-12 col-md-10 col-lg-8">
    <div class="card mb-4 w-100 min-w-700">
        <div class="card-header mb-3">
            <h3 class="fs-4"><?= $title ?> <span class="text-secondary">[<?= __('Абонент') ?> <?= num_len($abon[Abon::F_ID], 6) ?>]</span></h3>
            <h5 class="text-secondary fs-6">
                <span title="User ID"><?= num_len($user[User::F_ID], 6); ?></span> :: 
                <span title="User Name"><?= h($user[User::F_NAME_SHORT]); ?></span>
            </h5>
            <h5 class="text-secondary fs-6">
                <span title="Абон ID"><?= num_len($abon[Abon::F_ID], 6); ?></span> :: 
                <span title="Abon Address"><?= h($abon[Abon::F_ADDRESS]); ?>
            </h5>
        </div>
        <div class="card-body">
            <?php require DIR_INC . '/invoice_form.php'; ?>
        </div>
        <div class="card-footer">
            |
        </div>
    </div>
</div>
</div>

