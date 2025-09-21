<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : user_view.php
 *  Path    : app/views/inc/user_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:28:50
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of user_view.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
use config\Icons;
use config\tables\User;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $user */
$col1_attr = 'class="text text-secondary text-secondary-emphasis text-end small ps-3 pe-3" style="width: 150pt; white-space: nowrap;"';
$col2_attr = 'class="text text-info text-info-emphasis text-xl-start ps-3 pe-3"';
$col3_attr = 'class="text-center align-middle font-monospace text-sm-start ps-3 pe-3" style="width: 100px; white-space: nowrap;"';
?>
<div class="container-fluid mt-4">
    <table class="table table-hover table-bordered table-striped mt-3">
        <tbody>
            <tr>
                <th <?=$col1_attr;?>><?=__('Login');?></th>
                <td <?=$col2_attr;?> title="ID: <?=h($user[User::F_ID]);?>"><?= h($user[User::F_LOGIN]) ?></td>
            </tr>
            <tr>
                <th <?=$col1_attr;?>><?=__('Name Short');?></th>
                <td <?=$col2_attr;?>><?= cleaner_html($user[User::F_NAME_SHORT]) ?></td>
            </tr>
            <tr>
                <th <?=$col1_attr;?>><?=__('Name Full');?></th>
                <td <?=$col2_attr;?>><?= cleaner_html($user[User::F_NAME_FULL]) ?></td>
            </tr>
            <?php if ($user[User::F_SURNAME]) : ?>
            <tr>
                <th <?=$col1_attr;?>><?=__('Surname');?></th>
                <td <?=$col2_attr;?>><?= cleaner_html($user[User::F_SURNAME]) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($user[User::F_FAMILY]) : ?>
            <tr>
                <th <?=$col1_attr;?>><?=__('Family');?></th>
                <td <?=$col2_attr;?>><?= cleaner_html($user[User::F_FAMILY]) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="2"  class="p-3" >
                    <?=__('Contacts');?>
                    <table class="table table-hover table-bordered table-striped mt-3">
                        <tr>
                            <th <?=$col1_attr;?>><?=__('Phone');?></th>
                            <td <?=$col2_attr;?>>
                                <?= get_html_content_left_right(
                                        left: h($user[User::F_PHONE_MAIN]),
                                        right: url_tel(h($user[User::F_PHONE_MAIN])) . "&nbsp;" . url_sms(h($user[User::F_PHONE_MAIN])));
                                ?>
                            </td>
                            <td <?=$col3_attr;?>><input title="<?=__('Send SMS notifications');?>" type="checkbox" disabled <?= $user[User::F_DO_SEND_SMS] ? 'checked' : '' ?>></td>
                        </tr>
                        <tr>
                            <th <?=$col1_attr;?>><?=__('Email');?></th>
                            <td <?=$col2_attr;?>>
                                <?php
                                    if ($user[User::F_MAIL_MAIN]) {
                                        echo get_html_content_left_right
                                        (
                                                left:  h($user[User::F_MAIL_MAIN]),
                                                right: url_email(email: $user[User::F_MAIL_MAIN], src: Icons::SRC_ICON_EMAIL)
                                        );
                                    }
                                ?>
                            </td>
                            <td <?=$col3_attr;?>><input title="<?=__('Send email notifications');?>" type="checkbox" disabled <?= $user[User::F_DO_SEND_MAIL] ? 'checked' : '' ?>></td>
                        </tr>
                        <tr>
                            <th <?=$col1_attr;?>><?=__('Address for invoices');?></th>
                            <td <?=$col2_attr;?>><?= cleaner_html($user[User::F_ADDRESS_INVOICE]) ?></td>
                            <td <?=$col3_attr;?>><input title="<?=__('Send paper documents');?>" type="checkbox" disabled <?= $user[User::F_DO_SEND_INVOICE] ? 'checked' : '' ?>></td>
                        </tr>
                        <?php if ($user[User::F_TELEGRAM]) : ?>
                        <tr>
                            <th <?=$col1_attr;?>>Telegram</th>
                            <td <?=$col2_attr;?>><?= h($user[User::F_TELEGRAM]) ?></td>
                            <td <?=$col3_attr;?>><input title="<?=__('Use for correspondence and notifications');?>" type="checkbox" disabled <?= $user[User::F_TELEGRAM_DO_SEND] ? 'checked' : '' ?>></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($user[User::F_VIBER]) : ?>
                        <tr>
                            <th <?=$col1_attr;?>>Viber</th>
                            <td <?=$col2_attr;?>><?= h($user[User::F_VIBER]) ?></td>
                            <td <?=$col3_attr;?>><input title="<?=__('Use for correspondence and notifications');?>" type="checkbox" disabled <?= $user[User::F_VIBER_DO_SEND] ? 'checked' : '' ?>></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($user[User::F_JABBER]) : ?>
                        <tr>
                            <th <?=$col1_attr;?>>XMPP/Jabber</th>
                            <td <?=$col2_attr;?>><?= h($user[User::F_JABBER]) ?></td>
                            <td <?=$col3_attr;?>><input title="<?=__('Use for correspondence and notifications');?>" type="checkbox" disabled <?= $user[User::F_JABBER_DO_SEND] ? 'checked' : '' ?>></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>