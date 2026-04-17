<?php
/*
 *  Project : my.ri.net.ua
 *  File    : editTypeView.php
 *  Path    : app/views/admin/Security/editTypeView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */
?>
<div class="container my-4">
    <h1 class="display-6 mb-4"><?= h($title) ?></h1>

    <form method="post" action="<?= $uri_save_type ?>" class="card shadow-sm">
        <div class="card-body">
            <input type="hidden" name="attack_type[id]" value="<?= (int) $attack_type['id'] ?>">

            <div class="mb-3">
                <label for="title" class="form-label"><?= __('Title') ?></label>
                <input type="text" class="form-control" id="title" name="attack_type[title]" value="<?= h($attack_type['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="threshold_count" class="form-label"><?= __('Threshold count') ?></label>
                <input type="number" min="0" class="form-control" id="threshold_count" name="attack_type[threshold_count]" value="<?= (int) $attack_type['threshold_count'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="analytical_interval" class="form-label"><?= __('Analytical interval, sec.') ?></label>
                <input type="number" min="0" class="form-control" id="analytical_interval" name="attack_type[analytical_interval]" value="<?= (int) $attack_type['analytical_interval'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="blocking_time" class="form-label"><?= __('Blocking time, sec.') ?></label>
                <input type="number" min="0" class="form-control" id="blocking_time" name="attack_type[blocking_time]" value="<?= $attack_type['blocking_time'] === null ? '' : (int) $attack_type['blocking_time'] ?>">
                <div class="form-text"><?= h($attack_type['blocking_time_human']) ?></div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label"><?= __('Description') ?></label>
                <textarea class="form-control" id="description" name="attack_type[description]" rows="<?= get_count_rows_for_textarea((string) ($attack_type['description'] ?? '')) ?>"><?= h((string) ($attack_type['description'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __('Save') ?></button>
            <a href="<?= $uri_index ?>" class="btn btn-secondary"><?= __('Return to list') ?></a>
        </div>
    </form>
</div>
