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
 * Description of user_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use \config\tables\User;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/**
 *  Данные в форму передаются в массиве $user[]
 *  Поддерживаемые поля описаны константами User::F_*
 *  а так-же в массиве const User::T_FIELDS[User::F_*]
 *  @var array  $user
 */

/** @var array $user */
?>
<div class="container-fluid mt-4">
    <h2>Редактировать пользователя</h2>
    <form action="" method="post">
        <input type="hidden" name="<?=User::POST_REC;?>[<?=User::F_ID;?>]" value="<?=$user[User::F_ID];?>">

        <div class="mb-3 row">
            <label for="user_login" class="col-sm-3 col-form-label">Логин</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_login" name="<?=User::POST_REC;?>[<?=User::F_LOGIN;?>]" value="<?=h($user[User::F_LOGIN]);?>" required>
            </div>
            <div class="col-sm-3">:::</div>
        </div>



        <div class="mb-3 row">
            <div class="col-sm-3">Новый пароль</div>
            <!-- Новый пароль -->
            <div class="col-sm-3">
                <!-- <label for="<?= User::POST_REC;?>_<?= User::F_FORM_PASS;?>" class="form-label">Новый пароль</label>-->
                <input type="password"
                       class="form-control"
                       name="<?= User::POST_REC;?>[<?= User::F_FORM_PASS;?>]"
                       value=""
                       autocomplete="new-password"
                       placeholder="Password">
            </div>

            <!-- Повтор пароля -->
            <div class="col-sm-3">
                <!-- <label for="<?= User::POST_REC;?>_<?= User::F_FORM_PASS2;?>" class="form-label">Повторите пароль</label>-->
                <input type="password"
                       class="form-control"
                       name="<?= User::POST_REC;?>[<?= User::F_FORM_PASS2;?>]"
                       value=""
                       autocomplete="new-password"
                       placeholder="Retype password">
            </div>
            <div class="col-sm-3">:::</div>
        </div>

        <div class="mb-3 row">
            <label for="user_name" class="col-sm-3 col-form-label">Имя</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_name" name="<?=User::POST_REC;?>[<?=User::F_NAME_FULL;?>]" value="<?=h($user[User::F_NAME_FULL]);?>">
            </div>
            <div class="col-sm-3">:::</div>
        </div>

        <div class="mb-3 row">
            <label for="user_name_short" class="col-sm-3 col-form-label">Краткое имя</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_name_short" name="<?=User::POST_REC;?>[<?=User::F_NAME_SHORT;?>]" value="<?=h($user[User::F_NAME_SHORT]);?>">
            </div>
            <div class="col-sm-3">:::</div>
        </div>

        <div class="mb-3 row">
            <label for="user_phone_main" class="col-sm-3 col-form-label">Телефон</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_phone_main" name="<?=User::POST_REC;?>[<?=User::F_PHONE_MAIN;?>]" value="<?=h($user[User::F_PHONE_MAIN]);?>">
            </div>
            <div class="col-sm-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="user_do_send_sms" name="<?=User::POST_REC;?>[<?=User::F_DO_SEND_SMS;?>]" value="1" <?=$user[User::F_DO_SEND_SMS] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_do_send_sms">Отправлять SMS</label>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="user_mail_main" class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-6">
                <input type="email" class="form-control" id="user_mail_main" name="<?= User::POST_REC;?>[<?=User::F_MAIL_MAIN;?>]" value="<?= h($user[User::F_MAIL_MAIN]);?>">
            </div>
            <div class="col-sm-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="user_do_send_mail" name="<?= User::POST_REC;?>[<?=User::F_DO_SEND_MAIL;?>]" value="1" <?=$user[User::F_DO_SEND_MAIL] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_do_send_mail">Отправлять Email</label>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="user_address_invoice" class="col-sm-3 col-form-label">Адрес&nbsp;доставки&nbsp;счетов</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_address_invoice" name="<?= User::POST_REC;?>[<?=User::F_ADDRESS_INVOICE;?>]" value="<?= h($user[User::F_ADDRESS_INVOICE]);?>">
            </div>
            <div class="col-sm-3">
                <div class="form-check" title="Доставлять документы и счета в бумажном виде">
                    <input class="form-check-input" type="checkbox" id="user_do_send_invoice" name="<?= User::POST_REC;?>[<?=User::F_DO_SEND_INVOICE;?>]" value="1" <?=$user[User::F_DO_SEND_INVOICE] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_do_send_invoice">Доставлять документы</label>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="user_jabber_main" class="col-sm-3 col-form-label">Jabber</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_jabber_main" name="<?= User::POST_REC;?>[<?=User::F_JABBER;?>]" value="<?=h($user[User::F_JABBER]);?>">
            </div>
            <div class="col-sm-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="user_jabber_do_send" name="<?= User::POST_REC;?>[<?=User::F_JABBER_DO_SEND;?>]" value="1" <?=$user[User::F_JABBER_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_jabber_do_send">Отправлять на Jabber</label>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="user_viber" class="col-sm-3 col-form-label">Viber</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_viber" name="<?= User::POST_REC;?>[<?=User::F_VIBER;?>]" value="<?=h($user[User::F_VIBER]);?>">
            </div>
            <div class="col-sm-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="user_viber_do_send" name="<?= User::POST_REC;?>[<?=User::F_VIBER_DO_SEND;?>]" value="1" <?=$user[User::F_VIBER_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_viber_do_send">Отправлять на Viber</label>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="user_telegram" class="col-sm-3 col-form-label">Telegram</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="user_telegram" name="<?= User::POST_REC;?>[<?=User::F_TELEGRAM;?>]" value="<?=h($user[User::F_TELEGRAM]);?>">
            </div>
            <div class="col-sm-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="user_telegram_do_send" name="<?= User::POST_REC;?>[<?=User::F_TELEGRAM_DO_SEND;?>]" value="1" <?=$user[User::F_TELEGRAM_DO_SEND] ? 'checked' : '';?>>
                    <label class="form-check-label" for="user_telegram_do_send">Отправлять Telegram</label>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="user_prava" class="col-sm-3 col-form-label">Права доступа</label>
            <div class="col-sm-6">
                <select name="<?=User::POST_REC;?>[<?=User::F_PRAVA;?>]" id="user_prava" class="form-select">
                    <option value="0" <?=$user[User::F_PRAVA] == 0 ? 'selected' : '';?>>Пользователь</option>
                    <option value="1" <?=$user[User::F_PRAVA] == 1 ? 'selected' : '';?>>Администратор</option>
                    <option value="2" <?=$user[User::F_PRAVA] == 2 ? 'selected' : '';?>>Старший администратор</option>
                </select>
            </div>
            <div class="col-sm-3">:::</div>
        </div>

        <div class="mb-3 row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6 text-center">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
            <div class="col-sm-3">:::</div>
        </div>

    </form>
</div>
