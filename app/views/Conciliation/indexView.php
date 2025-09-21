<?php
/*
 *  Project : s1.ri.net.ua
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
        <h2 class="h4 mb-3"><?=__('Select the period for drawing up the Reconciliation Report | Выберите период составления Акта сверки | Виберіть період складання Акту звіряння')?></h2>
        <h3 class="h4 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> |</span> <?=$user[User::F_NAME_FULL];?>:</h3>
        <?php if ($user[Abon::TABLE]) : ?>
            <?=get_html_accordion(
                    table: $user[Abon::TABLE],
                    file_view: DIR_INC . '/conciliation_intervals.php',
                    func_get_title: function (array $abon) {
                        return get_html_content_left_right(
                        left: "" . num_len($abon[Abon::F_ID], 6) . " :: " . $abon[Abon::F_ADDRESS] . "",
                        right: ($abon['is_payer']
                                    ? "<span class='badge bg-success'>" . __('Abonent | Абонент | Абонент') . "</span>"
                                    : "<span class='badge bg-secondary'>" . __('Disabled | Отключён | Відключений') . "</span>"
                        ) . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;',
                        add_class: 'w-100');
                    }
            );?>
        <?php else : ?>
            <div class='alert alert-info' role='alert'><?=__('There are no subscriber connections | Абоненских подключений нет | Абонентських підключень немає')?></div>
        <?php endif; ?>
    <?php else : ?>
        <div class='alert alert-info' role='alert'><?=__('No information to display | Нет информации для отображения | Немає інформації для відображення')?></div>
    <?php endif; ?>
</div>