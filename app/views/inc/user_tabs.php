<?php
/**
 *  Project : my.ri.net.ua
 *  File    : user_tabs.php
 *  Path    : app/views/inc/user_tabs.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 13 Oct 2025 23:44:45
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Табы для управления пользовательскими данными/карточкой пользователя.
 * Используется в представлении app/views/My/indexView.php
 * Используемые файлы:
 *      user_view.php, -- Просмотр карточки пользователя
 *      user_form.php, -- Редактирование данных пользователя
 *
 * @var array $user Массив с данными пользователя
 * @var array $abon Массив с данными абонента
 * @var float $rest Текущие остатки на лицевом счете абонента
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\User;
use config\tables\Module;

?>
<!-- Табы для управления пользовательскими данными -->
<ul class="nav nav-tabs justify-content-end mt-3" role="tablist">
    <!-- смотреть карточку пользователя -->
    <li class="nav-item" role="presentation">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab_user_view_<?=$user[User::F_ID];?>" role="tab"><small><?=__('View');?></small></a>
    </li>
    <!-- Редактировать данные пользователя -->
    <?php if (can_edit([Module::MOD_MY_USER_CARD, Module::MOD_USER_CARD])) : ?>
    <li class="nav-item" role="presentation">
        <a class="nav-link" data-bs-toggle="tab" href="#tab_user_edit_<?=$user[User::F_ID];?>" role="tab"><small><?=__('Edit');?></small></a>
    </li>
    <?php endif; ?>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab_user_view_<?=$user[User::F_ID];?>" role="tabpanel">
        <!-- <div class="container-fluid mt-4"> -->
            <!--Просмотр карточки пользователя-->
            <?php require DIR_INC . '/user_view.php'; ?>
        <!-- </div> -->
    </div>
    <?php if (can_edit([Module::MOD_MY_USER_CARD, Module::MOD_USER_CARD])) : ?>
    <div class="tab-pane fade" id="tab_user_edit_<?=$user[User::F_ID];?>" role="tabpanel">
        <!-- <div class="container-fluid mt-4"> -->
            <!--Редактирование данных пользователя-->
            <?php require DIR_INC . '/user_form.php'; ?>
        <!-- </div> -->
    </div>
    <?php endif; ?>
</div>