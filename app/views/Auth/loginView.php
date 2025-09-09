<?php
use config\tables\User;
use config\SessionFields;
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <h2><?= __('Sign in | Представиться');?></h2>
        <form method="post" action="">

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="login-label"><a title='<?= __('Required field | Обюязательное поле');?>'><span style="color: red">*</span>&nbsp;Login</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <input type="text" class="form-control"
                       placeholder="Login"
                       value="<?=(isset($_SESSION[SessionFields::FORM_DATA][User::F_LOGIN]) ? $_SESSION[SessionFields::FORM_DATA][User::F_LOGIN] : "");?>"
                       name="<?=User::POST_REC;?>[<?= User::F_LOGIN; ?>]"
                       id="login-label">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text font-monospace" id="password-label"><a title='<?= __('Required field | Обюязательное поле');?>'><span style="color: red">*</span>&nbsp;Pass</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <input type="password" class="form-control"
                       placeholder="Password"
                       value="<?= isset($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) ? h($_SESSION[SessionFields::FORM_DATA][User::F_FORM_PASS]) : "";?>"
                       autocomplete="current-password"
                       name="<?=User::POST_REC;?>[<?=User::F_FORM_PASS;?>]"
                       id="password-label]">
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary mb-3"><?= __('Login | Войти');?></button>
            </div>

        </form>
        <?php if (isset($_SESSION[SessionFields::FORM_DATA])) unset($_SESSION[SessionFields::FORM_DATA]); ?>
    </div>
</div>
