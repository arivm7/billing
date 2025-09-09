<?php
use app\controllers\AuthController;
use app\widgets\LangSelector\LangSelector;
use config\tables\User;
$path=strtolower($this->route[F_CONTROLLER].'/'.$this->route[F_ACTION]);
?>
<ul class="nav nav-pills">
    <li class="nav-item"><a class="nav-link <?=($path == 'main/index' ? "active" : "");?>"  href="/">Home</a></li>
    <li class="nav-item"><a class="nav-link <?=($path == 'abon/index' ? "active" : "");?>"  href="/abon">Abons</a></li>
    <?php if (isset($_SESSION[User::SESSION_USER_REC])) : ?>
    <li class="nav-item"><a class="nav-link"                                                href="/my"><?="[".$_SESSION[User::SESSION_USER_REC][User::F_ID]."] " . $_SESSION[User::SESSION_USER_REC][User::F_LOGIN]; ?></a></li>
    <li class="nav-item"><a class="nav-link"                                                href="/auth/logout">Logout</a></li>
    <?php else : ?>
    <li class="nav-item"><a class="nav-link <?=($path == 'auth/signup' ? "active" : "");?>" href="/auth/signup">Signup</a></li>
    <li class="nav-item"><a class="nav-link <?=($path == 'auth/login' ? "active" : "");?>"  href="/auth/login">Login</a></li>
    <?php endif; ?>

    <li class="nav-item"><a class="nav-link <?=($path == 'admin/index' ? "active" : "");?>" href="/admin">Admin</a></li>
    <li class="nav-item"><a class="nav-link disabled"                                       href="/admin">Help</a></li>
    <li class="nav-item"><span class="nav-link"><?php new LangSelector(); ?></span></li>
</ul>
