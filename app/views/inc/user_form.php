<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : user_form.php
 *  Path    : app/views/inc/user_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Форма редактирования записи из таблицы users
 *
 * Данные в форму передаются в массиве $user[]
 * Поддерживаемые поля описаны константами User::F_*
 * а так-же в массиве констант User::T_FIELDS[User::F_*]
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\SessionFields;
use config\tables\User;
use billing\core\base\Lang;
use config\Icons;

Lang::load_inc(__FILE__);

/** @var array $user */

if (isset($_SESSION[SessionFields::FORM_DATA][User::POST_REC])) {
    $form_data = $_SESSION[SessionFields::FORM_DATA][User::POST_REC];
    unset($_SESSION[SessionFields::FORM_DATA]);
} else {
    $form_data = [];
}

//debug($form_data, '$form_data');
//debug($user, '$user');

$form_data_fn = function(string $field) use ($form_data, $user): int|float|string {
    return $form_data[$field] ?? $user[$field] ?? "";
};

?>
<div class="container-fluid mt-4">
    <h2><?=__('Edit user');?></h2>
    <form id="user_form" action="<?=User::URI_USER_UPDATE;?>/<?=$form_data_fn(User::F_ID);?>" method="post">
        <input type="hidden" name="<?=User::POST_REC;?>[<?=User::F_ID;?>]" value="<?=$form_data_fn(User::F_ID);?>">

        <div class="row mb-3">
            <label for="user_login" class="col-sm-2 col-form-label"><?=__('Login');?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" id="user_login" name="<?=User::POST_REC;?>[<?=User::F_LOGIN;?>]" value="<?=h($form_data_fn(User::F_LOGIN));?>" required>
            </div>
            <div class="col-sm-3">:::</div>
            <div class="col-sm-2">:::</div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-2 col-form-label" for="new_pass">
                <?= __('Password'); ?>
            </label>

            <!-- Новый пароль -->
            <div class="col-sm-3">
                <input type="password"
                       class="form-control"
                       id="new_pass"
                       name="<?= User::POST_REC; ?>[<?= User::F_FORM_PASS; ?>]"
                       value="<?= $form_data_fn(User::F_FORM_PASS); ?>"
                       autocomplete="new-password"
                       placeholder="<?= __('New password'); ?>">
                <div class="invalid-feedback">
                    <?= __('Passwords do not match'); ?>
                </div>
            </div>

            <!-- Повтор пароля -->
            <div class="col-sm-3">
                <input type="password"
                       class="form-control"
                       id="new_pass2"
                       name="<?= User::POST_REC; ?>[<?= User::F_FORM_PASS2; ?>]"
                       value="<?= $form_data_fn(User::F_FORM_PASS2); ?>"
                       autocomplete="new-password"
                       placeholder="<?= __('Retype'); ?>">
                <div class="invalid-feedback">
                    <?= __('Passwords do not match'); ?>
                </div>
            </div>

            <div class="col-sm-4"></div>
        </div>

        <div class="row mb-3">
            <label for="user_name_short" class="col-sm-2 col-form-label"><?=__('Short name');?></label>
            <div class="col-sm-3">
                <input type="text" class="form-control" id="user_name_short" name="<?=User::POST_REC;?>[<?=User::F_NAME_SHORT;?>]" value="<?=h($form_data_fn(User::F_NAME_SHORT));?>">
            </div>
            <div class="col-sm-3">:::</div>
            <div class="col-sm-2">:::</div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_name" class="col-sm-2 col-form-label"><?=__('Full name');?></label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_name" name="<?=User::POST_REC;?>[<?=User::F_NAME_FULL;?>]" value="<?=h($form_data_fn(User::F_NAME_FULL));?>">
            </div>
            <div class="col-sm-2">:::</div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_phone_main" class="col-sm-2 col-form-label"><?=__('Phone number');?></label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_phone_main" name="<?=User::POST_REC;?>[<?=User::F_PHONE_MAIN;?>]" value="<?=h($form_data_fn(User::F_PHONE_MAIN));?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('Send notifications via SMS');?>">
                    <input class="form-check-input" type="checkbox" id="user_do_send_sms" name="<?=User::POST_REC;?>[<?=User::F_SMS_DO_SEND;?>]" value="1" <?=$form_data_fn(User::F_SMS_DO_SEND) ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_do_send_sms">SMS</label>
                </div>
            </div>
            <div class="col-sm-2">::: <?=url_tel(h($user[User::F_PHONE_MAIN])) . "&nbsp;" . url_sms(h($form_data_fn(User::F_PHONE_MAIN)));?></div>
        </div>

        <div class="row mb-3">
            <label for="user_mail_main" class="col-sm-2 col-form-label"><?=__('Email');?></label>
            <div class="col-sm-6">
                <input type="email" class="form-control" id="user_mail_main" name="<?= User::POST_REC;?>[<?=User::F_EMAIL_MAIN;?>]" value="<?= h($form_data_fn(User::F_EMAIL_MAIN));?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('Send notification letters by email');?>">
                    <input class="form-check-input" type="checkbox" id="user_do_send_mail" name="<?= User::POST_REC;?>[<?=User::F_EMAIL_DO_SEND;?>]" value="1" <?=$form_data_fn(User::F_EMAIL_DO_SEND) ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_do_send_mail">Send</label>
                </div>
            </div>
            <div class="col-sm-2">::: <?=url_email(email: $user[User::F_EMAIL_MAIN], src: Icons::SRC_ICON_EMAIL);?></div>
        </div>

        <div class="row mb-3">
            <label for="user_address_invoice" class="col-sm-2 col-form-label"><?=__('Document delivery address');?></label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_address_invoice" name="<?= User::POST_REC;?>[<?=User::F_ADDRESS_INVOICE;?>]" value="<?= h($form_data_fn(User::F_ADDRESS_INVOICE));?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('Deliver documents and invoices in paper/digital form');?>">
                    <input class="form-check-input" type="checkbox" id="user_do_send_invoice" name="<?= User::POST_REC;?>[<?=User::F_INVOICE_DO_SEND;?>]" value="1" <?=$form_data_fn(User::F_INVOICE_DO_SEND) ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_do_send_invoice">Deliver</label>
                </div>
            </div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_viber" class="col-sm-2 col-form-label">Viber</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_viber" name="<?= User::POST_REC;?>[<?=User::F_VIBER;?>]" value="<?=h($user[User::F_VIBER]);?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('To correspond through this messenger');?>">
                    <input class="form-check-input" type="checkbox" id="user_viber_do_send" name="<?= User::POST_REC;?>[<?=User::F_VIBER_DO_SEND;?>]" value="1" <?=$user[User::F_VIBER_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_viber_do_send">Use</label>
                </div>
            </div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_telegram" class="col-sm-2 col-form-label">Telegram</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_telegram" name="<?= User::POST_REC;?>[<?=User::F_TELEGRAM;?>]" value="<?=h($user[User::F_TELEGRAM]);?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('To correspond through this messenger');?>">
                    <input class="form-check-input" type="checkbox" id="user_telegram_do_send" name="<?= User::POST_REC;?>[<?=User::F_TELEGRAM_DO_SEND;?>]" value="1" <?=$user[User::F_TELEGRAM_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_telegram_do_send">Use</label>
                </div>
            </div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_signal" class="col-sm-2 col-form-label">Signal</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_signal" name="<?= User::POST_REC;?>[<?=User::F_SIGNAL;?>]" value="<?=h($user[User::F_SIGNAL]);?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('To correspond through this messenger');?>">
                    <input class="form-check-input" type="checkbox" id="user_signal_do_send" name="<?= User::POST_REC;?>[<?=User::F_SIGNAL_DO_SEND;?>]" value="1" <?=$user[User::F_SIGNAL_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_signal_do_send">Use</label>
                </div>
            </div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_whatsapp" class="col-sm-2 col-form-label">WhatsApp</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_whatsapp" name="<?= User::POST_REC;?>[<?=User::F_WHATSAPP;?>]" value="<?=h($user[User::F_WHATSAPP]);?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('To correspond through this messenger');?>">
                    <input class="form-check-input" type="checkbox" id="user_whatsapp_do_send" name="<?= User::POST_REC;?>[<?=User::F_WHATSAPP_DO_SEND;?>]" value="1" <?=$user[User::F_WHATSAPP_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_whatsapp_do_send">Use</label>
                </div>
            </div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <label for="user_jabber_main" class="col-sm-2 col-form-label">Jabber/XMPP</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_jabber_main" name="<?= User::POST_REC;?>[<?=User::F_JABBER;?>]" value="<?=h($user[User::F_JABBER]);?>">
            </div>
            <div class="col-sm-2">
                <div class="form-check" title="<?=__('To correspond through this messenger');?>">
                    <input class="form-check-input" type="checkbox" id="user_jabber_do_send" name="<?= User::POST_REC;?>[<?=User::F_JABBER_DO_SEND;?>]" value="1" <?=$user[User::F_JABBER_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_jabber_do_send">Use</label>
                </div>
            </div>
            <div class="col-sm-2">:::</div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-2"></div>
            <div class="col-sm-6 text-center">
                <button type="submit" class="btn btn-primary"><?= __('Save'); ?></button>
            </div>
            <div class="col-sm-2">:::</div>
            <div class="col-sm-2">:::</div>
        </div>

    </form>
</div>

<script>
// Поведение:
// При совпадении полей — зелёная рамка.
// При несовпадении — красная рамка и сообщение “Пароли не совпадают”.
// Форма не отправляется, пока не будут одинаковы.
// Если поля пустые — отправка разрешена (например, пользователь не хочет менять пароль).
document.addEventListener('DOMContentLoaded', function() {
    const form  = document.getElementById('user_form');
    const pass1 = document.getElementById('new_pass');
    const pass2 = document.getElementById('new_pass2');

    // Функция проверки совпадения
    function checkMatch(showFeedback = true) {
        const filled = pass1.value.length > 0 || pass2.value.length > 0;
        const match = pass1.value === pass2.value;

        [pass1, pass2].forEach(el => el.classList.remove('is-valid', 'is-invalid'));

        if (filled) {
            if (match) {
                if (showFeedback) [pass1, pass2].forEach(el => el.classList.add('is-valid'));
                return true;
            } else {
                if (showFeedback) [pass1, pass2].forEach(el => el.classList.add('is-invalid'));
                return false;
            }
        }
        return true; // ничего не введено — не блокируем отправку
    }

    // Проверка при вводе
    pass1.addEventListener('input', () => checkMatch());
    pass2.addEventListener('input', () => checkMatch());

    // Проверка при отправке формы
    form.addEventListener('submit', function(e) {
        if (!checkMatch(true)) {
            e.preventDefault();
            e.stopPropagation();

            // Дополнительная фокусировка для UX
            pass2.focus();
        }
    });
});
</script>
