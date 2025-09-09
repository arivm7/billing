<?php
use billing\core\ErrorHandler;
/** @var int|string $errno */
/** @var string $errstr */
/** @var string $errfile */
/** @var int $errline */
/** @var array $errcontext */
/** @var ErrorHandler self */
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Ошибка</title>
        <meta charset="UTF-8">
    </head>
    <body>
        <h1>Произошла ошибка</h1>
        <p><b>Код ошибки:</b> <?= $errno ?></p>
        <p><b>Текст ошибки:</b><pre><?php print_r($errstr); ?></pre></p>
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
