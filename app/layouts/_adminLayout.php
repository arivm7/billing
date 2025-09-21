<!DOCTYPE html>
<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : _adminLayout.php
 *  Path    : app/layouts/_adminLayout.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * @var array  $meta
 * @var string $content
 */
use billing\core\Db;
use billing\core\Timers;
use app\widgets\menu\Menu;
?>
<html lang="ru" data-bs-theme="dark">
    <head>
        <meta charset="utf-8">
        <?=\billing\core\base\View::getMeta();?>
        <link rel="icon" href="/public/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="/public/favicon.ico" type="image/x-icon" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--<meta http-equiv="X-UA-Compatible" content="IE=edge">-->
        <link href="/public/bootstrap/css/bootstrap.css" rel="stylesheet">
        <!--<link href="/public/scc/panel.css" rel="stylesheet">-->
        <script src="/public/bootstrap/js/bootstrap.bundle.js"></script>
        <!--<script src="/public/bootstrap/js/bootstrap.js"></script>-->
    </head>
    <body>
        <div class="container">
            <?php include DIR_VIEWS . '/nav.php'; ?>
            <!--< ? php include DIR_VIEWS . '/menuTopView.php'; ? >-->
            <hr>
            <?php new Menu(
                    [
                        'template'       => DIR_WIDGETS . '/menu/templates/menu_template_select.php',
                        'container'      => 'select',
                        'container_attr' => "class='menu'",
                        'db_table'       => 'menu',
                        'cache_time'     => 10,
                        'cache_key'      => 'select-menu'
                    ]
            ); ?>
            <hr>
            <?php new Menu(
                    [
                        'template'       => DIR_WIDGETS . '/menu/templates/menu_template_ul_li.php',
                        'container'      => 'ul',
                        'container_attr' => "class='menu'",
                        'db_table'       => 'menu',
                        'cache_time'     => 10,
                        'cache_key'      => 'ul-menu'
                    ]
            ); ?>
            <hr>
            <?php include DIR_VIEWS . '/alerts.php'; ?>
            <pre>_SESSION:<?= print_r($_SESSION, true); ?></pre>
            <?= $content ?>
            <hr>
            <?php
                Timers::setTimeEnd();
                $format = "%s %' 10.4f сек.<br>\n";
                echo '<font face=monospace>';
                echo str_replace(" ", '&nbsp;', sprintf($format, "Время подготовки данных....", Timers::getTimePrepareData()));
                echo str_replace(" ", '&nbsp;', sprintf($format, "Время отрисовки данных.....", Timers::getTimeRender()));
                echo str_replace(" ", '&nbsp;', sprintf($format, "Время отрисовки страницы...", Timers::getTimeLayout()));
                echo str_replace(" ", '&nbsp;', sprintf($format, "Время ВСЕГО:...............", Timers::getTimeAll()));
                echo '</font>';
            ?>
            <hr>
            <?=
                "countSQL: " . Db::$countSql;
                /* debug(vendor\billing\core\Db::$queriesSql, 'queriesSql: '); */
            ?>
        </div>
        <?php
            foreach ($scripts as $script_one) {
                echo $script_one;
            }
        ?>
    </body>
</html>