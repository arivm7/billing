<?php
/**
 *  Project : my.ri.net.ua
 *  File    : doubles_errors_list_pays.php
 *  Path    : app/views/inc/doubles_errors_list_pays.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 10 Apr 2026 16:01:24
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of doubles_errors_list_pays.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



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


<?php foreach ($errors as $type => $error_rec): ?>

    <h3 class="fs-4 mb-0"><span class="text-secondary fs-5">Ошибок: <?= number_format($error_rec['count'], 0, ',', ' ') ?> :: </span><?= $error_rec['title'] ?></h3>

    <?php if (count($error_rec['payments']) > 0): ?>
        <?php $pager = $error_rec['pager']; ?>
        <?php include DIR_INC . '/pager.php'; ?>

        <?php foreach ($error_rec['payments'] as $pay): ?>
            <?php include DIR_INC . '/doubles_errors_one_pay.php' ?>
        <?php endforeach; ?>

        <?php include DIR_INC . '/pager.php'; ?>

        <hr>
    <?php endif; ?>

<?php endforeach; ?>