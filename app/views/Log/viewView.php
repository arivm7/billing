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



use config\tables\Module;

/**
 * Данные переданные из контроллера
 * 
 * @var string $title
 * @var string $file_name
 * @var string $content
 * @var bool   $has_html
 * @var int $count_lines
 * 
 */
?>
<div class="container my-4">
    <div id="top"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="display-6"><?= h($file_name) ?></h2>
            <h3 class="fs-5 mb-0"><?= __('Lines read | Прочитано строк | Прочитано рядків') ?>: <?= h($count_lines) ?> | <?= $has_html ? "HTML" : "TEXT" ?></h3>
        </div>
        <div class="d-flex gap-2">
            <a href="/log" class="btn btn-outline-secondary mx-1"><?= __('Back to list') ?></a>
            <a href="#bottom" class="btn btn-outline-secondary ms-1"><?= __('Down') ?></a>
            <?php if (can_del(Module::MOD_LOGS)) : ?>
                <a
                    href="/log/delete?file=<?= rawurlencode($file_name) ?>"
                    class="btn btn-outline-danger ms-1"
                    onclick="return confirm('<?= __('Confirm deletion of the log file | Подтвердите удаление log-файла | Підтвердьте видалення log-файлу') ?>');"
                    title="<?= __('Delete | Удалить | Видалити') . CR . __('Delete this log file | Удалить этот лог-файл | Видалити цей лог-файл'); ?>">
                    <i class="bi bi-x-circle"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($has_html): ?>
            <div class="mb-0 font-monospace fs-7"><?= $content ?></div>
            <?php else: ?>
            <pre><?= $content ?></pre>
            <?php endif; ?>
        </div>
    </div>

    <div id="bottom"></div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <h3 class="fs-5 mb-0"><?= __('Lines read | Прочитано строк | Прочитано рядків') ?>: <?= h($count_lines) ?> | <?= $has_html ? "HTML" : "TEXT" ?></h3>
        </div>
        <div class="d-flex gap-2">
            <a href="/log" class="btn btn-outline-secondary mx-1"><?= __('Back to list') ?></a>
            <a href="#top" class="btn btn-outline-secondary ms-1"><?= __('Up') ?></a>
            <?php if (can_del(Module::MOD_LOGS)) : ?>
                <a
                    href="/log/delete?file=<?= rawurlencode($file_name) ?>"
                    class="btn btn-outline-danger ms-1"
                    onclick="return confirm('<?= __('Confirm deletion of the log file | Подтвердите удаление log-файла | Підтвердьте видалення log-файлу') ?>');"
                    title="<?= __('Delete | Удалить | Видалити') . CR . __('Delete this log file | Удалить этот лог-файл | Видалити цей лог-файл'); ?>">
                    <i class="bi bi-x-circle"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
</div>
