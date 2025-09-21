<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Abon/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/** @var array $t */
?>
<div class="container">
    <h3>Список Абонентов</h3>
    <br>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(t: $t); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>
<br>

