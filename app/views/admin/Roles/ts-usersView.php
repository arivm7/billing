<?php
/*
 *  Project : my.ri.net.ua
 *  File    : ts-usersView.php
 *  Path    : app/views/admin/Roles/ts-usersView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of ts-usersView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * @var array $rows
 */
?>
<div class="container-fluid">
    <h2 class="display-6 ali text-center"><?=__('Редактирование ролей пользователй');?></h2>
    <br>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(t: $rows); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <hr>
</div>