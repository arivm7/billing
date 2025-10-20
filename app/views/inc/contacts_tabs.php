<?php
/**
 *  Project : s1.ri.net.ua
 *  File    : contacts_tabs.php
 *  Path    : app/views/inc/contacts_tabs.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 13 Oct 2025 23:52:04
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Табы для управления дополнительными контактами пользователя.
 * Используется в представлении app/views/My/indexView.php
 * Используемые файлы:
 *      contacts_view.php, -- Просмотр дополнительных контактов
 *      contacts_edit.php, -- Редактирование дополнительных контактов
 *
 * @var array $user Массив с данными пользователя
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\User;
use config\tables\Module;

?>
<!--
Просмотр дополнительных контактов
Module::MOD_MY_CONTACTS убран из отображения, поскольку дополнительные контакты -- это служебная таблица.
Абоненты могут заполнять свои контакты, в форме пользователя.
-->
<?php if (can_view([Module::MOD_CONTACTS])) : ?>
    <h3 class="fs-3 text-center"><?=__('Additional contacts');?></h3>
    <div class="container-fluid">
        <ul class="nav nav-tabs justify-content-end" id="my_tab_contacts_<?=$user[User::F_ID];?>" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link py-1 px-2 active" data-bs-toggle="tab" href="#tab_view_contacts_<?=$user[User::F_ID];?>" role="tab"><small><?=__('View');?></small></a>
            </li>
            <?php if (can_edit([Module::MOD_CONTACTS]) || can_add([Module::MOD_CONTACTS])) : ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link py-1 px-2" data-bs-toggle="tab" href="#tab_edit_contacts_<?=$user[User::F_ID];?>" role="tab"><small><?=__('Edit');?></small></a>
            </li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab_view_contacts_<?=$user[User::F_ID];?>" role="tabpanel">
                <?php require DIR_INC . '/contacts_view.php'; ?>
            </div>
            <?php if (can_edit([Module::MOD_CONTACTS]) || can_add([Module::MOD_CONTACTS])) : ?>
            <div class="tab-pane fade" id="tab_edit_contacts_<?=$user[User::F_ID];?>" role="tabpanel">
                <?php require DIR_INC . '/contacts_edit.php'; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
