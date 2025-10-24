<!DOCTYPE html>
<?php
/*
 *  Project : my.ri.net.ua
 *  File    : errorDevView.php
 *  Path    : app/views/Errors/errorDevView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of errorDevView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\ErrorHandler;
/** @var int|string $errno */
/** @var string $errstr */
/** @var string $errfile */
/** @var int $errline */
/** @var array $errcontext */
/** @var ErrorHandler self */
?>
<html lang="ru">
    <head>
        <title>Ошибка</title>
        <meta charset="UTF-8">
    </head>
    <body>
        <h1>Произошла ошибка</h1>
        <p><b>Код ошибки:</b> <?= $errno ?></p>
        <p><b>Текст ошибки:</b></p><pre><?php print_r($errstr); ?></pre>
        <p><b>Файл:</b> <?= $errfile ?></p>
        <p><b>Строка:</b> <?= $errline ?></p>
        <hr>
        <?php if (self::DUMP_ECHO) : ?>
        <pre>
            <?php print_r($errcontext); ?>
        </pre>
        <!--
        <hr>
        <pre>
            <?php var_dump($errcontext); ?>
        </pre>
        -->
        <hr>
        <?php endif; ?>
    </body>
</html>