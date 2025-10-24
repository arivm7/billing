<?php
/*
 *  Project : my.ri.net.ua
 *  File    : pager.php
 *  Path    : app/views/inc/pager.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of pager.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

if (!isset($pager)) return;
/** @var \billing\core\Pagination $pager */
?>
<div class="text-center">
    <?php if ($pager->count_pages > 1) : ?>
        <?=$pager;?>
    <?php endif; ?>
</div>