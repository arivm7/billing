<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : notify_card.php
 *  Path    : app/views/inc/notify_card.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of notify_card.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Module;
use config\tables\Notify;
use config\tables\User;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/** @var array $item   -- приходит от аккордеона */
/** @var array $notify -- Для использования в этом виде */

/**
 * данные одной записи из таблицы sms_list
 * ключи: id, abon_id, type_id, date, text, phonenumber, method
 */

$notify = $item;
$type_title = Notify::get_type_title($notify[Notify::F_TYPE_ID] ?? Notify::TYPE_NA);

/**
 * Проверка административного разрешения
 */
$can_admin = can_view(Module::MOD_NOTIFY);

/**
 * Проверка личного разрешения
 */
$can_my    = ($user[User::F_ID] == $_SESSION[User::SESSION_USER_REC][User::F_ID]) && can_view(Module::MOD_MY_NOTIFY);
//$can_my    = ($notify[Notify::F_USER_ID] == $_SESSION[User::SESSION_USER_REC][User::F_ID]) && can_view(Module::MOD_MY_NOTIFY);

?>
<?php if ($can_my || $can_admin) : ?>
    <div class="card">
        <div class="card-header">
            <h3 class="fs-5" >
                <?php if ($can_admin) : ?>
                <span class="text-secondary small"><?=h($notify[Notify::F_ID]);?> | <?= $notify[Notify::F_TYPE_ID] ?> | </span>
                <?php endif; ?>
                <?= h($type_title) ?>
            </h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover table-sm pt-3">
                <tr>
                    <th><?= __('Message text');?></th>
                    <td><pre class="h3 fs-5 mb-0"><?= cleaner_html($notify[Notify::F_TEXT]) ?></pre></td>
                </tr>
                <tr>
                    <th><?= __('Recipient');?></th>
                    <td><?= h($notify[Notify::F_PHONENUMBER]) ?></td>
                </tr>
                <tr>
                    <th><?= __('Send date');?></th>
                    <td><?= !empty($notify[Notify::F_DATE]) ? date('Y-m-d H:i:s', $notify[Notify::F_DATE]) : '-' ?></td>
                </tr>
                <?php if ($can_admin) : ?>
                <tr>
                    <th><?= __('Send method');?></th>
                    <td><?= h($notify[Notify::F_METHOD]) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
<?php endif; ?>