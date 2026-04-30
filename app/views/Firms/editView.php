<?php
/**
 *  Project : my.ri.net.ua
 *  File    : editView.php
 *  Path    : app/views/Firms/editView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of editView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h3 mb-0"><?= h($title) ?></h1>
        <a href="<?= h($uri_index) ?>" class="btn btn-outline-secondary btn-sm"><?= __('Back to list') ?></a>
    </div>

    <?php require DIR_INC . '/firms_edit_tab.php'; ?>
</div>