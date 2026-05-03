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



/**
 * Данные переданные из контроллера
 * 
 * @var string $title
 * @var string $file_name
 * @var string $content
 * @var int $count_lines
 * 
 */
?>
<div class="container my-4">
    <div id="top"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="display-6"><?= h($file_name) ?></h2>
            <h3 class="fs-5 mb-0">Считано строк: <?= h($count_lines) ?></h3>
        </div>
        <div class="d-flex gap-2">
            <a href="/log" class="btn btn-outline-secondary mx-1"><?= __('Back to list') ?></a>
            <a href="#bottom" class="btn btn-outline-secondary ms-1"><?= __('Down') ?></a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-0 font-monospace fs-7"><?= $content ?></div>
        </div>
    </div>

    <div id="bottom"></div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <h3 class="fs-5 mb-0">Считано строк: <?= h($count_lines) ?></h3>
        </div>
        <div class="d-flex gap-2">
            <a href="/log" class="btn btn-outline-secondary mx-1"><?= __('Back to list') ?></a>
            <a href="#top" class="btn btn-outline-secondary ms-1"><?= __('Up') ?></a>
        </div>
    </div>
    
</div>
