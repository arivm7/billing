<?php
/*
 *  Project : my.ri.net.ua
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
 * Контент вкладки уведомлений в карточке абоннента
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
$type_title = Notify::type_title($notify[Notify::F_TYPE_ID] ?? '-');
$type_descr = Notify::type_descr($notify[Notify::F_TYPE_ID] ?? '-');

/**
 * Проверка административного разрешения
 */
$can_admin = can_view(Module::MOD_NOTICE);

/**
 * Проверка личного разрешения
 */
$can_my    = ($user[User::F_ID] == $_SESSION[User::SESSION_USER_REC][User::F_ID]) && can_view(Module::MOD_MY_NOTICE);
//$can_my    = ($notify[Notify::F_USER_ID] == $_SESSION[User::SESSION_USER_REC][User::F_ID]) && can_view(Module::MOD_MY_NOTIFY);

?>
<?php if ($can_my || $can_admin) : ?>
    <div class="card">
        <div class="card-header">
            <h3 class="fs-5" >
                <?php if ($can_admin) : ?>
                <span class="text-secondary small"><span title="<?= __('ID Уведомления') ?>"><?=h($notify[Notify::F_ID]);?></span> | <span title="<?= __('ID Типа уведомления') ?>"><?= $notify[Notify::F_TYPE_ID] ?></span> | </span>
                <?php endif; ?>
                <?= h($type_descr) ?>
            </h3>
        </div>
        <div class="card-body">

            <table class="table table-bordered table-hover">

                <tr>
                    <th class="p-3 text-nowrap"><?= __('Send date');?></th>
                    <td class="p-3"><?= !empty($notify[Notify::F_DATE]) ? date('Y-m-d H:i:s', $notify[Notify::F_DATE]) : '-' ?></td>
                </tr>

                <?php if (!empty($notify[Notify::F_SUBJECT])) : ?>
                <tr>
                    <th class="p-3 text-nowrap"><?= __('Subject');?></th>
                    <td class="p-3"><?= h($notify[Notify::F_SUBJECT]) ?></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <th class="p-3 text-nowrap"><?= __('Message text');?></th>
                    <td class="p-3"><span class="fs-6 mb-0 text-break"><pre><?= cleaner_html($notify[Notify::F_TEXT]) ?></pre></span></td>
                </tr>
                
                <tr>
                    <th class="p-3 text-nowrap"><?= __('Recipient');?></th>
                    <td class="p-3"><span class="text-secondary">|</span> <?= h($notify[Notify::F_PHONENUMBER]) ?> <span class="text-secondary">|</span> <?= h($notify[Notify::F_EMAIL]) ?> <span class="text-secondary">|</span></td>
                </tr>

                <?php if ($can_admin) : ?>
                <tr>
                    <th class="p-3 text-nowrap"><?= __('Record ID');?></th>
                    <td class="p-3"><?= h($notify[Notify::F_ID]) ?></td>
                </tr>
                <tr>
                    <th class="p-3 text-nowrap"><?= __('Send method');?></th>
                    <td class="p-3"><?= h($notify[Notify::F_METHOD]) ?></td>
                </tr>
                <tr>
                    <th class="p-3 text-nowrap"><?= __('Sender User ID');?></th>
                    <td class="p-3"><?= h($notify[Notify::F_SENDER_ID]) ?></td>
                </tr>
                <?php endif; ?>
            </table>

        </div>
    </div>
<?php endif; ?>