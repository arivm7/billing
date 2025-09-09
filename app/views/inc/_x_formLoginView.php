<?php
use billing\core\App;
use config\tables\User;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

if (App::$auth->isAuth) {
    $user = $_SESSION[User::SESSION_USER_REC];
}

?>
<?php if (App::$auth->isAuth) : ?>

    <!--<div class="mb-3">-->
        <form class="row row-cols-lg-auto" action="/auth/logout" >
            <div class="col-12">
                <label class="visually-hidden" for="authTopForm"><?= __('Username');?></label>
                <div class="input-group">
                    <div class="input-group-text">@</div>
                    <span class="form-control" id="authTopForm" title="<?=$user[User::F_NAME_FULL];?>"><?=$user[User::F_NAME_SHORT];?></span>
                    <button type="submit" class="input-group-text"><?= __('Logout');?></button>
                </div>
            </div>
        </form>
    <!--</div>-->

<?php else : ?>

    <ul class="nav nav-pills">
        <li class="nav-item"><a class="nav-link <?=($path == 'auth/signup' ? "active" : "");?>" href="/auth/signup"><?= __('Signup');?></a></li>
        <li class="nav-item"><a class="nav-link <?=($path == 'auth/login'  ? "active" : "");?>" href="/auth/login"><?= __('Login');?></a></li>
    </ul>

<?php endif; ?>



