<?php
/**
 *  Project : my.ri.net.ua
 *  File    : wrongsView.php
 *  Path    : app/views/Payments/wrongsView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 13 Apr 2026 15:49:39
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of wrongsView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */






/**
 * Диспетчер вывода вида для отоблажения ошибок в платежах
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
    <?php include DIR_INC . '/wrongs_list_pays.php'; ?>
<?php endif; ?>

