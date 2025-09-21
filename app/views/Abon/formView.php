<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : formView.php
 *  Path    : app/views/Abon/formView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of formView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use app\models\AbonModel;
use config\tables\Contacts;
use config\Icons;
use config\tables\Abon;
use config\tables\PA;
use config\tables\Firm;
require_once DIR_LIBS . '/form_functions.php';

/** @var array $user */
?>
<div class="container-fluid">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="tab1-tab" data-bs-toggle="tab" href="#tab1" role="tab"><img src='<?= Icons::SRC_USER_CARD; ?>' title='<?= __('Просмотр данных пользователя'); ?>' alt="[U]" height="20" /></a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="tab2-tab" data-bs-toggle="tab" href="#tab2" role="tab"><img src='<?= Icons::SRC_USER_EDIT; ?>' title='<?= __('Редактирование данных пользователя'); ?>' alt="[E]" height="20" /></a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="tab3-tab" data-bs-toggle="tab" href="#tab3" role="tab"><img src='<?= Icons::SRC_ABON; ?>' title='<?= __('Редактирование данных абонента'); ?>' alt="[A]" height="20" /></a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">

        <!-- USER VIEW BEGIN -->
        <div class="tab-pane fade show active" id="tab1" role="tabpanel">
            <?php include DIR_INC . '/user_view.php'; ?>
        </div>
        <!-- USER VIEW END -->

        <!-- USER EDIT BEGIN -->
        <div class="tab-pane fade" id="tab2" role="tabpanel">
            <?php include DIR_INC . '/user_form.php'; ?>
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

        <!-- ABON BEGIN -->
        <div class="tab-pane fade" id="tab3" role="tabpanel">
            <div class="accordion" id="accordion_abon_list">
            <?php foreach ($user[Abon::TABLE] as $index => $abon) : ?>
                <?php $show = ($index == array_key_first($user[Abon::TABLE]));?>
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAbon<?=$abon[Abon::F_ID];?>"
                          aria-expanded="<?=$show ? "true" : "false";?>" aria-controls="collapseAbon<?=$abon[Abon::F_ID];?>">
                      <?=$abon[Abon::F_ID] . '<br>' . h($abon[Abon::F_ADDRESS]);?>
                  </button>
                </h2>
                <div id="collapseAbon<?=$abon[Abon::F_ID];?>" class="accordion-collapse collapse <?=$show ? "show" : "";?>" data-bs-parent="#accordion_abon_list">
                  <div class="accordion-body">
                    <?php include DIR_INC . '/abon_form.php'; ?>

                    <!-- PA BEGIN -->
                    <?= get_html_accordion(
                            table: $abon[PA::TABLE],
                            file_view: DIR_INC . '/pa_form.php',
                            func_get_title: function(array $item) {
                                    return "[" . $item[PA::F_NET_NAME] . "]"
                                            . "<br>"
                                            . "<span  class='font-monospace'>"
                                            . date(format: DATE_FORMAT, timestamp: $item[PA::F_DATE_START]) . " - "
                                            . ($item[PA::F_DATE_END] ? date(format: DATE_FORMAT, timestamp: $item[PA::F_DATE_END]) : "____-__-__") . " | "
                                            . "<span ". get_html_pa_status_attr(AbonModel::get_price_apply_age($item)).">"
                                            . $item[PA::F_PRICE_TITLE]
                                            . "</span>";
                                }
                            ); ?>
                    <!-- PA END -->

                  </div>
                </div>
              </div>
            <?php endforeach; ?>
            </div>
        </div>
        <!-- ABON END -->

    </div>
</div>