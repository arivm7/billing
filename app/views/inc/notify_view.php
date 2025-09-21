<?php
/*
 *  Project : s1.ri.net.ua
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
 * Description of notify_view.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\Pagination;
use config\tables\Notify;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/**
 * @var array $sms  данные одной записи из таблицы sms_list
 *                  ключи: id, abon_id, type_id, date, text, phonenumber, method
 */

/** @var Pagination $pager */

?>
<!-- Перебор подключенных прайсовых фрагментов -->
<?php if ($abon[Notify::TABLE]) : ?>

    <?=$pager ?? "";?>
    <?= get_html_accordion(
                table: $abon[Notify::TABLE],
                file_view: DIR_INC . '/notify_card.php',
                func_get_title: function(array $item) {
                                    return '<span class="text-secondary">' . date('Y-m-d H:i:s', $item[Notify::F_DATE]) . ' :</span>&nbsp;' . h($item[Notify::F_TEXT]);
                        }
        );?>
    <?php if (empty($pager)) : ?>
        <div class="text-center mt-3">
            <span class="badge rounded-pill text-bg-secondary p-2">
                <?= __('Shown notifications');?> <?=count($abon[Notify::TABLE]);?> / <?=$abon[Notify::F_COUNT];?>
            </span>
        </div>
    <?php else : ?>
        <?=$pager ?? "";?>
    <?php endif; ?>
<?php else : ?>
    <br>
    <div class='alert alert-info' role='alert'>
        <?= __('No notifications to display');?>
    </div>
<?php endif; ?>