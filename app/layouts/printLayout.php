<!DOCTYPE html>
<?php
/*
 *  Project : my.ri.net.ua
 *  File    : printLayout.php
 *  Path    : app/layouts/printLayout.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:19:28
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use billing\core\base\View;
?>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <link rel="icon" href="/public/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="/public/favicon.ico" type="image/x-icon" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= View::getMeta();?>
    </head>
    <body>
        <?= $content ?>
    </body>
</html>