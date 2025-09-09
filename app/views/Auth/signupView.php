<?php
use config\tables\User;
use config\SessionFields;
$login =    (isset($_SESSION[SessionFields::FORM_DATA][User::F_LOGIN])
                ?   $_SESSION[SessionFields::FORM_DATA][User::F_LOGIN]
                :   ""
            );
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <h2><?= __('Registration | Регистрация');?></h2>
        <form method="post" action="">

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="login-label"><a title='<?= __('Desired field | Желательное поле');?>'><span style="color: gray">*</span>&nbsp;Login</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="Login"
                       title="<?=__('login для авторизации');?>"
                       value="<?=$login;?>"
                       name="<?=User::POST_REC;?>[<?= User::F_LOGIN; ?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_LOGIN;?>]">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="password-label"><a title='<?= __('Required field| Обязательное поле');?>'><span style="color: red">*</span>&nbsp;Pass</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <input type="password" class="form-control"
                       placeholder="Password"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) : "";?>"
                       autocomplete="new-password"
                       name="<?=User::POST_REC;?>[<?=User::F_FORM_PASS;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_FORM_PASS;?>]">
                <input type="password" class="form-control"
                       placeholder="Retype"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS2]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS2]) : "";?>"
                       autocomplete="new-password"
                       name="<?=User::POST_REC;?>[<?=User::F_FORM_PASS2;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_FORM_PASS2;?>]">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="phone_main-label"><a title='<?= __('Required field| Обязательное поле');?>'><span style="color: red">*</span>&nbsp;<?= __('Тел');?>:</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="Номер телефона"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_PHONE_MAIN]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_PHONE_MAIN]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_PHONE_MAIN;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_PHONE_MAIN;?>]">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace">&nbsp;&nbsp;<?= __('Appeal | Обращение');?>&nbsp;</span>
                <input type="text" class="form-control" placeholder="<?= __('Краткое имя');?>"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_NAME_SHORT]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_NAME_SHORT]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_NAME_SHORT;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_NAME_SHORT;?>]">
                <input type="text" class="form-control" placeholder="<?= __('Full name | Полное имя');?>"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_NAME_FULL]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_NAME_FULL]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_NAME_FULL;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_NAME_FULL;?>]">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="mail_main-label">&nbsp;&nbsp;<?= __('Email | Эл. почта');?>&nbsp;</span>
                <input type="text" class="form-control" placeholder="email@domain.com"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_MAIL_MAIN]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_MAIL_MAIN]) : "";?>"
                       name="<?=User::POST_REC;?>[<?=User::F_MAIL_MAIN;?>]"
                       id="<?=User::POST_REC;?>[<?=User::F_MAIL_MAIN;?>]">
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3"><?= __('Register | Зарегистрировать');?></button>
            </div>

        </form>
        <?php if (isset($_SESSION[SessionFields::FORM_DATA])) unset($_SESSION[SessionFields::FORM_DATA]); ?>
    </div>
</div>
