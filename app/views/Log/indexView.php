<?php
/**
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/log/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */


?>
<div class="container my-4">
    <h1 class="display-6 mb-4"><?= h($title) ?></h1>

    <?php if (empty($logs)): ?>
        <div class="alert alert-info"><?= __('Log files not found | Файлы журналов не найдены | Файли журналів не знайдено'); ?></div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($logs as $logGroup): ?>
                <div class="list-group-item">
                    <div class="mb-2">
                        <div class="fw-bold">
                            <a href="/log/view?file=<?= rawurlencode($logGroup['base_file']) ?>#bottom">
                                <?= h($logGroup['base_file']) ?>
                            </a>
                        </div>
                        <div class="text-muted small">
                            <?= __('Size') ?>: <?= number_format((int) $logGroup['size'], 0, '.', ' ') ?> B
                            |
                            <?= __('Modified') ?>: <?= !empty($logGroup['mtime']) ? date('d.m.Y H:i:s', (int) $logGroup['mtime']) : '-' ?>
                        </div>
                    </div>

                    <?php if (!empty($logGroup['archives'])): ?>
                        <ul class="mb-0">
                            <?php foreach ($logGroup['archives'] as $archive): ?>
                                <li>
                                    <a href="/log/view?file=<?= rawurlencode($archive['file_name']) ?>#bottom">
                                        <?= h($archive['file_name']) ?>
                                    </a>
                                    <span class="text-muted small">
                                        | <?= __('Size') ?>: <?= number_format((int) $archive['size'], 0, '.', ' ') ?> B
                                        | <?= __('Modified') ?>: <?= !empty($archive['mtime']) ? date('d.m.Y H:i:s', (int) $archive['mtime']) : '-' ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
