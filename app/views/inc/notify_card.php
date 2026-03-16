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
 * данные одной записи из таблицы sms_list
 * ключи: id, abon_id, type_id, date, text, phonenumber, method
 * 
 *                                  notify_view.php -> notify_card.php (этот файл)
 * NoticeController.php -> listView.php (этот файл) -> notify_card.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use config\tables\Module;
use config\tables\Notify;
use config\tables\User;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/** 
 * Входные данные
 * @var array $item   -- приходит от аккордеона 
 * @var array $notify -- Для использования в этом виде 
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
$can_my    = ($user[User::F_ID] == App::get_user_id() && can_view(Module::MOD_MY_NOTICE));

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

            <div class="table-responsive">
            <table class="table table-bordered table-hover" style="table-layout: fixed; width: 100%;">

                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Send date');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><?= !empty($notify[Notify::F_DATE]) ? date('Y-m-d H:i:s', $notify[Notify::F_DATE]) : '-' ?></td>
                </tr>

                <?php if (!empty($notify[Notify::F_SUBJECT])) : ?>
                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Subject');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><?= h($notify[Notify::F_SUBJECT]) ?></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Message text');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><span class="fs-6 mb-0 text-break"><pre style="white-space: pre-wrap; word-wrap: break-word; margin: 0;"><?= cleaner_html($notify[Notify::F_TEXT]) ?></pre></span></td>
                </tr>

                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Recipient');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><span class="text-secondary">|</span> <?= h($notify[Notify::F_PHONENUMBER]) ?> <span class="text-secondary">|</span> <?= h($notify[Notify::F_EMAIL]) ?> <span class="text-secondary">|</span></td>
                </tr>

                <?php if ($can_admin) : ?>
                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Record ID');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><?= h($notify[Notify::F_ID]) ?></td>
                </tr>
                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Send method');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><?= h($notify[Notify::F_METHOD]) ?></td>
                </tr>
                <tr>
                    <th class="p-3 text-nowrap" style="width: 30%;"><?= __('Sender User ID');?></th>
                    <td class="p-3" style="word-wrap: break-word; overflow-wrap: break-word;"><?= h($notify[Notify::F_SENDER_ID]) ?></td>
                </tr>
                <?php endif; ?>
            </table>
            </div>

        </div>
    </div>
<?php endif; ?>