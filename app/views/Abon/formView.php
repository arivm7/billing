<?php
/**
 *  Project : my.ri.net.ua
 *  File    : formView.php
 *  Path    : app/views/Abon/formView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Oct 2025 01:08:58
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of formView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use config\Icons;
use config\tables\Abon;
use config\tables\Firm;
use config\tables\Module;
use config\tables\User;
require DIR_LIBS . '/form_functions.php';
/** @var array $user */
?>
<div class="container">
    <?php if (App::$auth->isAuth) : ?>
        <h2 class="fs-3 text-center"><?=__('Abonent personal account');?></h2>
        <div class="container-fluid">

            <!-- Грлавный список вкладок -->
            <ul class="nav nav-tabs justify-content-start" id="my_tab_user_abon" role="tablist">
                <!-- Вкладка просмотр пользователя -->
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_user_view_<?=$user[User::F_ID];?>" role="tab"><img src='<?=Icons::SRC_USER_CARD;?>' title='<?=__('Просмотр данных пользователя');?>' alt="[U]" height="20" /></a>
                </li>
                <!-- Вкладка редактирование пользователя -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab"        href="#tab_user_edit_<?=$user[User::F_ID];?>" role="tab"><img src='<?=Icons::SRC_USER_EDIT;?>' title='<?=__('Редактирование данных пользователя');?>' alt="[E]" height="20" /></a>
                </li>
                <!-- Вкладка Абонентов -->
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab_abons_<?=$user[User::F_ID];?>" role="tab"><small><?=__('Abonent services');?></small></a>
                </li>
            </ul>

            <div class="tab-content">

                <!-- Контент Вкладки просмотр пользователя -->
                <div class="tab-pane fade show active" id="tab_user_view_<?=$user[User::F_ID];?>" role="tabpanel">
                    <?php
                        if (can_edit(Module::MOD_MY_USER_CARD)) {
                            require DIR_INC . '/user_form.php';
                        } elseif (can_view(Module::MOD_MY_USER_CARD)) {
                            require DIR_INC . '/user_view.php';
                        }
                    ?>
                    <hr>
                    <?php if (can_view([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS])) : ?>
                        <h3 class="fs-3 text-center"><?=__('Additional contacts');?></h3>
                        <div class="container-fluid">
                            <ul class="nav nav-tabs justify-content-end" id="my_tab_contacts" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link py-1 px-2 active" data-bs-toggle="tab" href="#tab_view_contacts_<?=$user[User::F_ID];?>" role="tab"><small><?=__('View');?></small></a>
                                </li>
                                <?php if (can_edit([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS]) || can_add([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS])) : ?>
                                <li class="nav-item" role="presentation">
                                  <a class="nav-link py-1 px-2" data-bs-toggle="tab" href="#tab_edit_contacts_<?=$user[User::F_ID];?>" role="tab"><small><?=__('Edit');?></small></a>
                                </li>
                                <?php endif; ?>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tab_view_contacts_<?=$user[User::F_ID];?>" role="tabpanel">
                                    <?php require DIR_INC . '/contacts_view.php'; ?>
                                </div>
                                <?php if (can_edit([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS]) || can_add([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS])) : ?>
                                <div class="tab-pane fade" id="tab_edit_contacts_<?=$user[User::F_ID];?>" role="tabpanel">
                                    <?php require DIR_INC . '/contacts_edit.php'; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!--Просмотр предприятий-->
                    <?php if (can_view(Module::MOD_MY_FIRM) && !empty($user[Firm::TABLE])) : ?>
                        <h3 class="fs-3 text-center"><?=__('Contacts for document exchange');?></h3>
                        <?= get_html_accordion(
                                table: $user[Firm::TABLE],
                                file_view: DIR_INC . '/firm_tabs.php',
                                func_get_title: function(array $firm) {
                                        return get_html_content_left_right(
                                            left:   " :: " . $firm[Firm::F_NAME_LONG] . "",
                                            right:  ($firm[Firm::F_HAS_ACTIVE]
                                                        ? "<span class='badge bg-success'>".__('Works')."</span>"
                                                        : "<span class='badge bg-secondary'>".__('Not used')."</span>"
                                                    ) . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;',
                                            add_class: 'w-100');
                                        }
                                );
                        ?>
                    <?php endif; ?>
                </div>



                <!-- USER EDIT BEGIN -->
                <div class="tab-pane fade" id="tab_user_edit_<?=$user[User::F_ID];?>" role="tabpanel">
                    DIR_INC . /user_form.php';
                    <hr>

                    <!-- Навигация по вкладкам -->
                    <ul class="nav nav-tabs" id="connectionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#tab-contacts" type="button" role="tab">Контакты</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#tab-firms" type="button" role="tab">Предприятия</button>
                        </li>
                    </ul>

                    <!-- Контент вкладок -->
                    <div class="tab-content border border-top-0 p-3" id="firmTabContent">

                        <!-- Вкладка Контакты -->
                        <div class="tab-pane fade show active" id="tab-contacts" role="tabpanel">
                            <div class="row g-3">
                                <?php include DIR_INC . '/contacts_edit.php'; ?>
                            </div>
                        </div>

                        <!-- Вкладка Предприятия -->
                        <div class="tab-pane fade" id="tab-firms" role="tabpanel">
                            <div class="row g-3">
                                <?php
                                    if ($user[Firm::TABLE]) {
                                        echo get_html_accordion(
                                                table: $user[Firm::TABLE],
                                                file_view: DIR_INC . '/firm_edit.php',
                                                func_get_title: function(array $item) {
                                                        return  "[" . $item[Firm::F_NAME_SHORT] . "]<br>"
                                                                . get_firm_status_str($item, '');
                                                }
                                        );
                                        /* $firm = $user[Firm::TABLE][0]; */
                                        /* include DIR_INC . '/firm_edit.php'; */
                                    } else {
                                        echo "<div class='alert alert-info' role='alert'>".__('Нет прикрепленных приедприятий')."</div>";
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- USER EDIT END -->



                <!-- Контент Вкладки Абонентов -->
                <div class="tab-pane fade" id="tab_abons_<?=$user[User::F_ID];?>" role="tabpanel">
                    <!-- Перебор подключенных абонентов -->
                    <div class="container-fluid mt-4">
                    <?php if (!empty($user[Abon::TABLE])) : ?>
                        <?= get_html_accordion(
                                    table: $user[Abon::TABLE],
                                    file_view: DIR_INC . '/abon_card.php',
                                    func_get_title: function(array $abon) {
                                            return get_html_content_left_right(
                                                left:   " :: " . $abon[Abon::F_ADDRESS] . "",
                                                right:  ($abon['is_payer']
                                                            ? "<span class='badge bg-success'>".__('Abonent')."</span>"
                                                            : "<span class='badge bg-secondary'>".__('Off')."</span>"
                                                        ) . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;',
                                                add_class: 'w-100');
                                    },
                                    variables:  ['user' => $user]
                            );
                        ?>
                    <?php else: ?>
                        <br>
                        <div class='alert alert-info' role='alert'><?=__('There is no list of abonent connections to display');?></div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>