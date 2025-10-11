<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : loginView.php
 *  Path    : app/views/Auth/loginView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of loginView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\User;
use config\SessionFields;

const labelLogin = 'labelLogin';
const labelPass  = 'labelPass';

$LeftFields = [
    labelLogin => __('Login'),
    labelPass  => __('Password'),
];

// 1. Найти максимальную длину строки в символах
$maxLength = 0;
foreach ($LeftFields as $value) {
    $len = mb_strlen($value, 'UTF-8');
    if ($len > $maxLength) {
        $maxLength = $len;
    }
}

// 2. Дополнить каждую строку пробелами до нужной длины
foreach ($LeftFields as $key => $value) {
    $len = mb_strlen($value, 'UTF-8');
    $diff = $maxLength - $len;
    $LeftFields[$key] = $value . str_repeat('&nbsp;', $diff);
}


?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <h2><?= __('Sign in');?></h2>
        <form method="post" action="">

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="login-label">
                    <a title='<?= __('Required field');?>'>
                        <span style="color: red">*</span>&nbsp;<?= $LeftFields[labelLogin] ?>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </span>
                <input type="text" class="form-control"
                       placeholder="<?= __('Login');?>"
                       value="<?=(isset($_SESSION[SessionFields::FORM_DATA][User::F_LOGIN]) ? $_SESSION[SessionFields::FORM_DATA][User::F_LOGIN] : "");?>"
                       autocomplete="username"
                       name="<?=User::POST_REC;?>[<?= User::F_LOGIN; ?>]"
                       id="login-label" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="password-label">
                    <a title='<?= __('Required field');?>'>
                        <span style="color: red">*</span>&nbsp;<?= $LeftFields[labelPass] ?>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </span>
                <input type="password" class="form-control"
                       placeholder="<?= __('Password');?>"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) : "";?>"
                       autocomplete="current-password"
                       name="<?=User::POST_REC;?>[<?=User::F_FORM_PASS;?>]"
                       id="password-label" required>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3"><?= __('Enter');?></button>
            </div>

        </form>
        <?php if (isset($_SESSION[SessionFields::FORM_DATA])) unset($_SESSION[SessionFields::FORM_DATA]); ?>
    </div>
</div>