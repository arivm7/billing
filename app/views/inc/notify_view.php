<?php
/*
 *  Project : my.ri.net.ua
 *  File    : notify_view.php
 *  Path    : app/views/inc/notify_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Контент таба уведомлений в карточке абонента
 * Тут должен быть аккордеон, который включает файл вида для одного уведомления notify_card.php
 * 
 * app/views/inc/notify_view.php (этот файл) -> app/views/inc/notify_card.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\Pagination;
use config\tables\Module;
use config\tables\Notify;
use billing\core\base\Lang;
use config\tables\Abon;

Lang::load_inc(__FILE__);

/**
 * @var array $sms  данные одной записи из таблицы sms_list
 *                  ключи: id, abon_id, type_id, date, text, phonenumber, method
 */

?>

<?php if ($abon[Notify::TABLE]) : ?>

    <?= get_html_accordion(
                table: $abon[Notify::TABLE],
                file_view: DIR_INC . '/notify_card.php',
                func_get_title: function(array $item) {
                        return '<span class="text-secondary text-nowrap">' . date('Y-m-d H:i:s', $item[Notify::F_DATE]) . ' :</span>&nbsp;' 
                        . Notify::type_title($item[Notify::F_TYPE_ID]) . ' : '
                        . ($item[Notify::F_TYPE_ID] == Notify::TYPE_EMAIL 
                            ? h($item[Notify::F_SUBJECT]) 
                            : h($item[Notify::F_TEXT]));
                },
                variables:  ['user' => $user]
        );?>
    <div class='d-flex justify-content-between align-items-center mt-3'>
        <div>
            <!-- Информационные уведомления -->
            <?php if (can_add([Module::MOD_NOTICE])) : ?>
                <a href="<?=Notify::URI_INFO;?>/<?=$abon[Abon::F_ID];?>" 
                    class="btn btn-outline-info btn-sm me-1" target="_self" 
                    title="<?= __('List of Information messages'); ?>"
                    ><span class="fw-bold">SMS</span> <?= __('Informers'); ?></a>
            <?php endif; ?>
            <!-- Все уведомления -->
            <?php if (can_add([Module::MOD_NOTICE])) : ?>
                <a href="<?=Notify::URI_LIST;?>/<?=$abon[Abon::F_ID];?>" 
                    class="btn btn-outline-info btn-sm me-1" target="_self" 
                    title="<?= __('Полный список уведомлений'); ?>"
                    ><span class="fw-bold">Inf</span> <?= __('Список'); ?></a>
            <?php endif; ?>
        </div>
        <div>
            <span class="badge rounded-pill text-bg-secondary p-2">
                <?= __('Shown notifications');?> <?=count($abon[Notify::TABLE]);?> / <?=$abon[Notify::F_COUNT];?>
            </span>
        </div>
    </div>
<?php else : ?>
    <div class='alert alert-info mt-3' role='alert'>
        <?= __('No notifications to display');?>
    </div>
<?php endif; ?>