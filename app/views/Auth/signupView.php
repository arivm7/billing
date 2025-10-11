<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : signupView.php
 *  Path    : app/views/Auth/signupView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of signupView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\User;
use config\SessionFields;
$login =    (isset($_SESSION[SessionFields::FORM_DATA][User::F_LOGIN])
                ?   $_SESSION[SessionFields::FORM_DATA][User::F_LOGIN]
                :   ""
            );


const labelLogin = 'labelLogin';
const labelPass  = 'labelPass';
const labelPhone = 'labelPhone';
const labelName  = 'labelName';
const labelEmail = 'labelEmail';

$LeftFields = [
    labelLogin => 'Login',
    labelPass  => 'Pass',
    labelPhone => __('Phone number'),
    labelName  => __('Appeal'),
    labelEmail => __('Email'),
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
        <h2><?= __('Registration');?></h2>
        <form method="post" action="">

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="login-label"><a title='<?= __('Desired field');?>'><span style="color: gray">*</span>&nbsp;<?=$LeftFields[labelLogin];?></a>&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="Login"
                       title="<?=__('Name for authorization');?>"
                       value="<?=$login;?>"
                       name="<?=User::POST_REC;?>[<?= User::F_LOGIN; ?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_LOGIN;?>]">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="password-label"><a title='<?= __('Required field');?>'><span style="color: red">*</span>&nbsp;<?=$LeftFields[labelPass];?></a>&nbsp;</span>
                <input type="password" class="form-control"
                       placeholder="Password"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) : "";?>"
                       autocomplete="new-password"
                       name="<?=User::POST_REC;?>[<?=User::F_FORM_PASS;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_FORM_PASS;?>]" required>
                <input type="password" class="form-control"
                       placeholder="Retype"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS2]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS2]) : "";?>"
                       autocomplete="new-password"
                       name="<?=User::POST_REC;?>[<?=User::F_FORM_PASS2;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_FORM_PASS2;?>]" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="phone_main-label"><a title='<?= __('Required field');?>'><span style="color: red">*</span>&nbsp;<?=$LeftFields[labelPhone];?></a>&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="<?=__('Phone number');?>"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_PHONE_MAIN]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_PHONE_MAIN]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_PHONE_MAIN;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_PHONE_MAIN;?>]" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace">&nbsp;&nbsp;<?=$LeftFields[labelName];?>&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="<?= __('Short name');?>"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_NAME_SHORT]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_NAME_SHORT]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_NAME_SHORT;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_NAME_SHORT;?>]">
                <input type="text" class="form-control" placeholder="<?= __('Full name');?>"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_NAME_FULL]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_NAME_FULL]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_NAME_FULL;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_NAME_FULL;?>]">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="mail_main-label">&nbsp;&nbsp;<?=$LeftFields[labelEmail];?>&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="email@domain.com"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_EMAIL_MAIN]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_EMAIL_MAIN]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_EMAIL_MAIN;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_EMAIL_MAIN;?>]">
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3"><?= __('Register');?></button>
            </div>

        </form>
        <?php if (isset($_SESSION[SessionFields::FORM_DATA])) unset($_SESSION[SessionFields::FORM_DATA]); ?>
    </div>
</div>