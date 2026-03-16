<?php
/**
 *  Project : my.ri.net.ua
 *  File    : listView.php
 *  Path    : app/views/Notice/listView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 15 марта 2026 г.
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Полный перечень всех уведомлений отправленных абоненту.
 * Тут отображён только аккордеон со списком. Сами уведомления выводятся в отдельном файле-виде
 * 
 * NoticeController.php -> listView.php (этот файл) -> notify_card.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\Pagination;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Notify;
use config\tables\User;

require_once DIR_LIBS . '/functions.php';
require_once DIR_LIBS . '/form_functions.php';

/**
 * Список уведомлений абонента
 *
 * @var string $title
 * @var array $abon
 * @var array $user
 * @var array $notice_list
 * @var Pagination $pager
 */

?>

<div class="container w-auto">

    <h2 class="fs-3 mt-3 mb-4"><?= h($title); ?></h2>

    <!-- Информация об абоненте -->
    <div class="card mb-3">
        <div class="card-header">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <?= __('Абонент') ?>: <strong><?= $abon[Abon::F_ID]; ?></strong>
                </div>
                <div>
                    <!-- Информационные уведомления -->
                    <?php if (can_add([Module::MOD_NOTICE])) : ?>
                        <a href="<?=Notify::URI_INFO;?>/<?=$abon[Abon::F_ID];?>" class="btn btn-outline-info btn-sm me-1" target="_self" 
                            title="<?= __('List of Information messages'); ?>"
                            ><span class="fw-bold">SMS</span> <?= __('Informers'); ?></a>
                    <?php endif; ?>
                    <!-- Вернуться в карточку абонента -->
                    <?php if (can_use([Module::MOD_ABON])) : ?>
                        <a href="<?=Abon::URI_VIEW;?>/<?=$abon[Abon::F_ID];?>" class="btn btn-outline-info btn-sm me-1" target="_self"><span class="fw-bold">🅐</span> <?= __('Картка'); ?></a> <!-- ⒶⒶⒶ -->
                    <?php else: ?>
                        <a href="/my" class="btn btn-outline-info btn-sm" target="_self"><span class="fw-bold">🅐</span> <?= __('Картка'); ?></a> <!-- ⒶⒶ🅐Ⓐ(A) -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-1">
                <span class="text-secondary"><?= __('Имя') ?>:</span>
                <?= h($user[User::F_NAME_FULL]); ?>
            </p>
            <p class="mb-1">
                <span class="text-secondary"><?= __('Адрес') ?>:</span>
                <?= h($abon[Abon::F_ADDRESS]); ?>
            </p>
        </div>
    </div>

    <!-- Пагинация сверху -->
    <?php require DIR_INC . '/pager.php'; ?>

    <!-- Список уведомлений с аккордеоном -->
    <?php if (!empty($notice_list)) : ?>
        <?= get_html_accordion(
            table: $notice_list,
            file_view: DIR_INC . '/notify_card.php',
            func_get_title: function(array $item) {
                return '<span class="text-secondary text-nowrap">' . date('Y-m-d H:i:s', $item[Notify::F_DATE]) . ' :</span>&nbsp;' 
                . Notify::type_title($item[Notify::F_TYPE_ID]) . ' : '
                . ($item[Notify::F_TYPE_ID] == Notify::TYPE_EMAIL 
                    ? h($item[Notify::F_SUBJECT]) 
                    : h($item[Notify::F_TEXT]));
            },
            variables: ['user' => $user]
        ); ?>
    <?php else : ?>
        <div class="alert alert-info" role="alert">
            <?= __('Уведомления не найдены'); ?>
        </div>
    <?php endif; ?>

    <!-- Пагинация снизу -->
    <?php require DIR_INC . '/pager.php'; ?>

</div>
