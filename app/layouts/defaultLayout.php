<!DOCTYPE html>
<?php
/*
 *  Project : my.ri.net.ua
 *  File    : defaultLayout.php
 *  Path    : app/layouts/defaultLayout.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:19:28
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

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
        <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge"> -->
        <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="/public/bootstrap/icons/font/bootstrap-icons.css">
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> -->
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"> -->

        <?=View::getMeta();?>
        <style>

            /* относительно контейнера */
            .min-w-100 { min-width: 100% !important; }
            .min-w-75  { min-width:  75% !important; }
            .min-w-50  { min-width:  50% !important; }
            .min-w-25  { min-width:  25% !important; }
            .min-w-15  { min-width:  15% !important; }
            .min-w-10  { min-width:  10% !important; }

            .min-w-100px { min-width: 100px !important; }
            .min-w-150 { min-width: 150px !important; }
            .min-w-200 { min-width: 200px !important; }
            .min-w-300 { min-width: 300px !important; }
            .min-w-400 { min-width: 400px !important; }
            .min-w-600 { min-width: 600px !important; }
            .min-w-700 { min-width: 700px !important; }
            .min-w-800 { min-width: 800px !important; }

            .w-10 { width: 10% !important; }
            .w-15 { width: 15% !important; }
            .w-25 { width: 25% !important; }
            .w-50 { width: 50% !important; }
            .w-75 { width: 75% !important; }
            .w-90 { width: 90% !important; }

            /* размер текста */
            .fs-7  {font-size:0.90rem !important; }
            .fs-8  {font-size:0.80rem !important; }
            .fs-9  {font-size:0.70rem !important; }
            .fs-10 {font-size:0.60rem !important; }
            .fs-11 {font-size:0.50rem !important; }
            .fs-12 {font-size:0.40rem !important; }

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

            /* Выравнивание чекбоксов */
            .form-check-align-center { display: flex; align-items: center; }
            .form-check-align-bottom { display: flex; align-items: end; }

            /* Минимальная ширина страницы */
            body {
                min-width: 800px;
            }

            mark {
                padding-left: 0;
                padding-right: 0;
                border-radius: 0.25em;
            }

            .bukvitca::first-letter {
                    font-size: 2em;
                    font-weight: bold;
            }

        </style>
    </head>
    <body>
        <div class="my-content container-fluid">
            <?php include DIR_INC . '/menuTopView.php'; ?>
            <div class="my-layout">

                <aside class="my-left-menu">

                    <?php new Menu(
                            [
                                'template'       => DIR_WIDGETS . '/menu/templates/menu_template_bootstrap.php',
                                'container'      => 'div',
                                'container_attr' => 'class="accordion fixed-width" id="accordionEx"',
                                'cache_time'     => 1,
                                'cache_key'      => 'menu_bootstrap_' . ($_SESSION[User::SESSION_USER_REC][User::F_ID] ?? 0)
                            ]
                    ); ?>
                    <hr>
                </aside>

                <div class="my-content container-fluid min-w-600">
                    <?php include DIR_INC . '/alerts.php'; ?>
                    <?= $content ?>
                    <hr>
                </div>
            </div>

            <div class="my-content container-fluid">
                <div class="d-flex justify-content-end flex-wrap align-items-center text-secondary font-monospace fs-6">
                    <!-- Блок с изображениями -->
                    <div class="d-flex flex-wrap align-items-center me-4 gap-3">
                        <?php $title_for = __('Payment instructions'); ?>
                        <?php if (Theme::id() == Theme::F_ID_LIGHT) : ?>
                            <a href="/pay">
                                <img src='/img/p24/liqpay/logo_liqpay_main.svg' height="24" alt='LIQPAY' title='<?=$title_for;?> LIQPAY'>
                            </a>
                            <a href="/pay">
                                <img src='/img/p24/p24/24-pay-mark.svg' style='max-height:60px;' alt='24Pay' title='<?=$title_for;?> (24)Pay'>
                            </a>
                        <?php else : ?>
                            <a href="/pay">
                                <img src='/img/p24/liqpay/logo_liqpay_white_color_book.svg' height="24" alt='LIQPAY' title='<?=$title_for;?> LIQPAY'>
                            </a>
                            <a href="/pay">
                                <img src='/img/p24/p24/24-pay-mark_border.svg' style='max-height:42px;' alt='24Pay' title='<?=$title_for;?> (24)Pay'>
                            </a>
                        <?php endif; ?>
                        <a href="/pay">
                            <img src='/img/p24/visa/full-color-128x72.png' style='max-height:38px;' alt='VISA' title='<?=$title_for;?> VISA'>
                        </a>
                        <a href="/pay">
                            <img src='/img/p24/mc/mc_symbol.svg' style='max-height:48px;' alt='Mastercard' title='<?=$title_for;?> Mastercard'>
                        </a>
                    </div>

                    <!-- Блок с копирайтом -->
                    <div class="text-start fs-6 mt-2 mt-sm-0">
                        © RI-Network 2006-<?=date('Y');?>.<br>
                        <a href="tel:+380983633578">+38 (098) 363-35-78</a>, <a href="tel:+380936480009">+38 (093) 648-00-09</a><br>
                        <a href="tel:+380939576944">+38 (093) 957-69-44</a>, <a href="tel:+380502685229">+38 (050) 268-52-29</a>
                    </div>
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

        <script src="/public/bootstrap/js/bootstrap.bundle.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
        <script type='text/javascript' src='/public/dcjqaccordion/js/jquery.cookie.js'></script>
        <script type='text/javascript' src='/public/dcjqaccordion/js/jquery.hoverIntent.minified.js'></script>
        <script type='text/javascript' src='/public/dcjqaccordion/js/jquery.dcjqaccordion.2.7.min.js'></script>
        <script type='text/javascript' src='/public/js/main.js'></script>
        <?php
            if (!empty($scripts)) {
                foreach ($scripts[0] as $script_one) {
                    echo $script_one;
                }
            }
        ?>
    </body>
</html>