<?php
/**
 *  Project : my.ri.net.ua
 *  File    : viewView.php
 *  Path    : app/views/log/viewView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

?>
<div class="container my-4">
    <div id="top"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="display-6 mb-0"><?= h($file_name) ?></h1>
        <div class="d-flex gap-2">
            <a href="/log" class="btn btn-outline-secondary mx-1"><?= __('Back to list') ?></a>
            <a href="#bottom" class="btn btn-outline-secondary ms-1"><?= __('Down') ?></a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <pre class="mb-0"><?= htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
        </div>
    </div>

    <div id="bottom" class="d-flex justify-content-end mt-3">
        <a href="/log" class="btn btn-outline-secondary mx-1"><?= __('Back to list') ?></a>
        <a href="#top" class="btn btn-outline-secondary ms-1"><?= __('Up') ?></a>
    </div>
</div>
