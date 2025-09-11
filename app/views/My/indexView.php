<?php
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
        <h2 class="fs-3 text-center"><?=__('Abonent personal account');?></h2>
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
                    <?php if (can_view(Module::MOD_MY_USER_CARD)) require DIR_INC . '/user_view.php'; ?>
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
                        <?=get_html_accordion(
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
                            });
                        ?>
                    <?php endif; ?>

                </div>


                <!-- Контент Вкладки Абонентов -->
                <div class="tab-pane fade" id="tab_abons_<?=$user[User::F_ID];?>" role="tabpanel">
                    <!-- Перебор подключенных абонентов -->
                    <div class="container-fluid mt-4">
                    <?php
                        if (!empty($user[Abon::TABLE])) {
                            echo get_html_accordion(
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
                                    }
                            );
                        } else {
                            echo "<br><div class='alert alert-info' role='alert'>".__('There is no list of abonent connections to display')."</div>";
                        }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
