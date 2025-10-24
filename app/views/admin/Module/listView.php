<?php
/*
 *  Project : my.ri.net.ua
 *  File    : listView.php
 *  Path    : app/views/admin/Module/listView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of listView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 *  @var string $title
 *  @var array  $table
 */
?>
<div class="container">
    <div class='d-flex justify-content-between mb-3'>
        <h2><?=__('Список модулей сайта');?></h2>
        <a href="/admin/module/form" class="btn btn-info"><?=__('Новый модуль');?></a>
    </div>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(
            t: $table,
            cell_attributes: ["id", "route_api", "valign=top", "perm", "modified"]); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>