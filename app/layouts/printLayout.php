<!DOCTYPE html>
<?php
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
