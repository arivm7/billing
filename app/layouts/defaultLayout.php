<!DOCTYPE html>
<?php
/**
 * @var array  $meta
 * @var string $content
 */
use billing\core\base\Theme;
use billing\core\Db;
use billing\core\ErrorHandler;
use billing\core\Timers;
use config\tables\Module;

use config\tables\User;
use app\widgets\menu\Menu;
use billing\core\base\View;

?>
<html lang="ru" <?=Theme::get();?>>
    <head>
        <meta charset="utf-8">
        <link rel="icon" href="/public/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="/public/favicon.ico" type="image/x-icon" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--<meta http-equiv="X-UA-Compatible" content="IE=edge">-->
        <link href="/public/bootstrap/css/bootstrap.css" rel="stylesheet">
        <script src="/public/bootstrap/js/bootstrap.bundle.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <?=View::getMeta();?>
        <style>
            /* размер текста */
            .fs-7  {font-size:0.75rem!important}
            .fs-8  {font-size:0.5rem!important}
            .fs-9  {font-size:0.25rem!important}
            .fs-10 {font-size:0.15rem!important}
            .fs-11 {font-size:0.1rem!important}

            /* Лэйаут страницы */
            .my-layout {
                display: flex;
            }

            .my-left-menu {
                align-self: flex-start;
                position: sticky;
                top: 20px;
                padding-right: 20px;
            }

            .my-content {
                display: flex;
                flex-direction: column;
                row-gap: 30px;
            }

            .accordion-button.no-arrow::after {
              display: none !important;
            }
        </style>
    </head>
    <body>
        <div class="my-content container-fluid">
            <?php include DIR_INC . '/menuTopView.php'; ?>
            <!--< ?php include DIR_INC . '/nav.php'; ?>-->
            <div class="my-layout">

                <aside class="my-left-menu">

                    <?php new Menu(
                            [
                                'template'       => DIR_WIDGETS . '/menu/templates/menu_template_bootstrap.php',
                                'container'      => 'div',
                                'container_attr' => 'class="accordion fixed-width" id="accordionEx"',
                                'db_table'       => 'menu',
                                'cache_time'     => 1,
                                'cache_key'      => 'menu_bootstrap_' . ($_SESSION[User::SESSION_USER_REC][User::F_ID] ?? 0)
                            ]
                    ); ?>
                    <hr>
                </aside>

                <div class="my-content container-fluid">
                    <!--
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Library</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data</li>
                        </ol>
                    </nav>
                    -->
                    <?php include DIR_INC . '/alerts.php'; ?>
                    <?= $content ?>
                    <hr>
                    <div class="text text-secondary font-monospace fs-6">
                        <table align=right border=0 cellpadding=10 cellspacing=10>
                            <tr>
                                <?php if (Theme::id() == Theme::F_ID_LIGHT) : ?>
                                <td valign=center><a href="/how_to_pay_all.php"><img  src='/img/p24/liqpay/logo_liqpay_main.svg' height=24 alt='LIQPAY' title='Инструкция по оплате через LIQPAY'></a></td>
                                <td valign=center><a href="/how_to_pay_all.php"><img  src='/img/p24/p24/24-pay-mark.svg' style='max-height:60px;' alt='24Pay' title='(24)Pay'></a></td>
                                <?php else : ?>
                                <td valign=center><a href="/how_to_pay_all.php"><img  src='/img/p24/liqpay/logo_liqpay_white_color_book.svg' height=24 alt='LIQPAY' title='Инструкция по оплате через LIQPAY'></a></td>
                                <td valign=center><a href="/how_to_pay_all.php"><img  src='/img/p24/p24/24-pay-mark_border.svg' style='max-height:42px;' alt='24Pay' title='(24)Pay'></a></td>
                                <?php endif; ?>
                                <td valign=center><a href="/how_to_pay_all.php"><img  src='/img/p24/visa/full-color-128x72.png' style='max-height:38px;' alt='VISA' title='VISA'></a></td>
                                <td valign=center><a href="/how_to_pay_all.php"><img  src='/img/p24/mc/mc_symbol.svg' style='max-height:48px;' alt='Mastercard' title='Mastercard'></a></td>
                                <td valign=center> </td>
                                <td><font size="-1">© RI-Network 2006-<?=date('Y');?>.<br>Контакти:<br>+38 (098) 363-35-78, +38 (093) 648-00-09<br>+38 (093) 957-69-44, +38 (050) 268-52-29</font></td>
                            </tr>
                        </table>
                    </div>
                    <?php if (can_view(Module::MOD_WEB_DEBUG)) : ?>
                    <hr>
                    <div class="text text-secondary font-monospace fs-7">
                        <?php
                            Timers::setTimeEnd();
                            $format = "%s %' 10.4f сек.<br>\n";
                            echo str_replace(" ", '&nbsp;', sprintf($format, __('Data preparation time'), Timers::getTimePrepareData()));
                            echo str_replace(" ", '&nbsp;', sprintf($format, __('Data rendering time'),   Timers::getTimeRender()));
                            echo str_replace(" ", '&nbsp;', sprintf($format, __('Page rendering time'),   Timers::getTimeLayout()));
                            echo str_replace(" ", '&nbsp;', sprintf($format, __('TOTAL time'),            Timers::getTimeAll()));
                        ?>
                        <?php
                            if (ErrorHandler::DEBUG) {
                                echo "<hr>countSQL: " . Db::$countSql;
                                // debug(vendor\billing\core\Db::$queriesSql, 'queriesSql: ');
                            }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
        <script type='text/javascript' src='/public/js/main.js'></script>
        <script type='text/javascript' src='/public/dcjqaccordion/js/jquery.cookie.js'></script>
        <script type='text/javascript' src='/public/dcjqaccordion/js/jquery.hoverIntent.minified.js'></script>
        <script type='text/javascript' src='/public/dcjqaccordion/js/jquery.dcjqaccordion.2.7.min.js'></script>
        <?php
            if (!empty($scripts)) {
                foreach ($scripts[0] as $script_one) {
                    echo $script_one;
                }
            }
        ?>
    </body>
</html>
