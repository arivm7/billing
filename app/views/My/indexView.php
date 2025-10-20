<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/My/indexView.php
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

use billing\core\App;
use config\tables\Abon;
use config\tables\Firm;
use config\tables\Module;
use config\tables\User;
require DIR_LIBS . '/form_functions.php';
/** @var array $user */
?>
<div class="container">
    <?php if (App::$auth->isAuth) : ?>
        <h2 class="fs-3 text-center"><?=$title;?></h2>
        <div class="container-fluid">

            <!-- Грлавный список вкладок -->
            <ul class="nav nav-tabs justify-content-start" id="my_tab_user_abon" role="tablist">
                <!-- Вкладка пользователя -->
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_user_<?=$user[User::F_ID];?>" role="tab"><small><?=__('User card');?></small></a>
                </li>
                <!-- Вкладка Абонентов -->
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_abons_<?=$user[User::F_ID];?>" role="tab"><small><?=__('Abonent services');?></small></a>
                </li>
            </ul>

            <div class="tab-content">

                <!-- Контент Вкладки пользователя -->
                <div class="tab-pane fade show active" id="tab_user_<?=$user[User::F_ID];?>" role="tabpanel">
                    <?php require DIR_INC . '/user_tabs.php'; ?>
                    <hr>
                    <?php require DIR_INC . '/contacts_tabs.php'; ?>

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
                                        },
                                variables:  ['user' => $user]
                                );
                        ?>
                    <?php endif; ?>
                </div>


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
                        <div class='alert alert-info mt-3' role='alert'><?=__('There is no list of abonent connections to display');?></div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>