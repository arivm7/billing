<?php
/**
 *  Project : my.ri.net.ua
 *  File    : doublesView.php
 *  Path    : app/views/Payments/doublesView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Apr 2026 21:21:49
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */


/**
 * Диспетчер форм и видов поиска дубликатов
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\Pagination;

// debug($_POST, '$_POST');
// debug($_GET, '$_GET');


/**
 * Данные полученные из контроллера
 * 
 * @var string $title
 * @var array $filter
 * @var array $doubles
 * @var Pagination $pager
 * 
 * Данные для вывода ошибок в платежах
 * @var array $errors
 * 
 * Данные для вывода ошибок в платежах
 * @var string $type
 * @var array  $error_rec
 * 
 * Данные для вывода одного платежа
 * @var array $pay
 * 
 */


?>

<?php if (!empty($errors)): ?>
    <?php include DIR_INC . '/doubles_errors_list_pays.php'; ?>
<?php endif; ?>

<fieldset style='width:60%;'>
    <legend>Поиск задвоенных платежей</legend>
    <form action='' method='post' name='form1' target='_self' >
        <?php include DIR_INC . '/doubles_filters.php' ?>
    </form>
</fieldset>

<hr>

<?php if (!empty($doubles)): ?>
    <?php include DIR_INC . '/doubles_diff_list_view.php' ?>
<?php endif ?>