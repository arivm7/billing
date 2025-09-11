<?php
/** /app/views/inc/menuTopView.php */
use app\widgets\Theme\ThemeSelector;
use billing\core\App;
use config\tables\Module;
use config\tables\User;
use app\widgets\LangSelector\LangSelector;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
if (App::$auth->isAuth) {
    $user = $_SESSION[User::SESSION_USER_REC];
}
$path=strtolower(($this->route[F_PREFIX] ? $this->route[F_PREFIX] . '/' : "") . $this->route[F_CONTROLLER] . '/' . $this->route[F_ACTION]);
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid align-items-center text-end">
        <a class="navbar-brand" href="/" title="<?=__('To the main page');?>" >
            <img src="/public/img/ri_logo2.svg" alt="[RILAN-admin]" title="<?=__('Rilan');?>. <?=__('Subscriber personal cabinet');?>." height="75" class="d-inline-block align-text-top">
        </a>
        <a class="navbar-brand" href="https://my.ri.net.ua/" title="<?=__('Rilan');?>. <?=__('Old version of personal cabinet');?>." >
            <img src="/public/img/ri_icon.ico" alt="[RI-network]" height="40" class="d-inline-block align-text-top">
        </a>
        <div class="ms-auto">
<?php if (App::$auth->isAuth) : ?>
            <ul class="nav nav-pills me-auto">
                <?php if (can_view(Module::MOD_SEARCH)) : ?>
                <li class="nav-item pe-2 d-flex align-items-center">
                    <a class="btn btn-outline-success btn-sm <?=(str_contains($path, 'admin/') ? "active" : "");?>" href="/admin/admin/menuedit">Admin</a>
                </li>
                <li class="nav-item pe-2 d-flex align-items-center">
                    <a class="btn btn-outline-success btn-sm disabled" href="/help">Help</a>
                </li>
                <li class="nav-item pe-2">
                    <form class="d-flex" role="search" method="get" action="/abon/form">
                        <div class="input-group input-group-sm w-auto" style="max-width: 120px;">
                            <input class="form-control form-control-sm" type="search" placeholder="AID/UID" aria-label="Search" name="id" title="<?=__('Contract number (subscriber ID) or user ID');?>." >
                            <button class="btn btn-outline-success btn-sm" type="submit">></button>
                        </div>
                    </form>
                </li>
                <li class="nav-item pe-2">
                    <form class="d-flex" role="search" method="get" action="/search/text">
                        <div class="input-group align-items-center">
                            <input class="form-control form-control-sm" type="search" placeholder="<?=__('Search fragment');?>" aria-label="Search" title="<?=__('Search');?>">
                            <button class="btn btn-outline-success btn-sm" type="submit"><?=__('Search');?></button>
                        </div>
                    </form>
                </li>
                <?php endif; ?>
                <li class="nav-item pe-2">
                    <div class="col-12">
                        <label class="visually-hidden" for="authTopForm">Username</label>
                        <div class="input-group align-items-center">
                            <div class="btn btn-outline-success btn-sm disabled">@</div>
                            <!--<div class="input-group-text input-group-sm">@</div>-->
                            <span class="form-control form-control-sm" id="authTopForm" title="<?=$user[User::F_NAME_FULL];?>"><?=$user[User::F_NAME_SHORT];?></span>
                            <form action="/auth/logout" >
                                <button type="submit" class="btn btn-outline-success btn-sm">Logout</button>
                                <!--<button type="submit" class="input-group-text input-group-sm">Logout</button>-->
                            </form>
                        </div>
                    </div>
                </li>
                <li class="nav-item pe-2"><?php new LangSelector(); ?></li>
                <li class="nav-item pe-2"><?php new ThemeSelector(); ?></li>
            </ul>
<?php else : ?>
            <ul class="nav nav-pills">
                <li class="nav-item pe-2 d-flex align-items-center">
                    <a class="btn btn-outline-success btn-sm <?=($path == 'auth/signup' ? "active" : "");?>" href="/auth/signup"><?=__('Register');?></a>
                </li>
                <li class="nav-item pe-2 d-flex align-items-center">
                    <a class="btn btn-outline-success btn-sm <?=($path == 'auth/login'  ? "active" : "");?>" href="/auth/login"><?=__('Login');?></a>
                </li>
                <li class="nav-item pe-2"><?php new LangSelector(); ?></li>
                <li class="nav-item pe-2"><?php new ThemeSelector(); ?></li>
            </ul>
<?php endif; ?>
        </div>
    </div>
</nav>
