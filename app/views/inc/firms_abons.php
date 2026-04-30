<?php
/**
 *  Project : my.ri.net.ua
 *  File    : firms_abons.php
 *  Path    : app/views/inc/firms_abons.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:19:44
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firms_abons.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\Firm;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="text-secondary text-end">No</th>
                <th><?= __('Abon ID') ?></th>
                <th class="text-secondary text-end">ID</th>
                <th><?= __('Full name') ?></th>
                <th><?= __('Public title') ?></th>
                <th><?= __('Active') ?></th>
                <th><?= __('Deleted') ?></th>
                <th><?= __('Agent') ?></th>
                <th><?= __('Client') ?></th>
                <th class="text-end"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($abon_firms)) : ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-3"><?= __('No entries') ?></td>
                </tr>
            <?php else: ?>
                <?php $rowNo = 0; ?>
                <?php foreach ($abon_firms as $firm): ?>
                    <tr>
                        <td class="text-secondary text-end"><?= ++$rowNo ?></td>
                        <td class="text-end"><?= url_abon_form((int)($firm['abon_id'] ?? 0)) ?></td>
                        <td class="text-secondary text-end"><?= h($firm[Firm::F_ID] ?? '') ?></td>
                        <td><?= h($firm[Firm::F_NAME_LONG] ?? '') ?></td>
                        <td><?= h($firm[Firm::F_NAME_TITLE] ?? '') ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_ACTIVE]) ? '1' : '0' ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_DELETE]) ? '1' : '0' ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_AGENT]) ? '1' : '0' ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_CLIENT]) ? '1' : '0' ?></td>
                        <td class="text-end">
                            <a href="<?= h($uri_edit . '/' . (int) $firm[Firm::F_ID]) ?>"
                               class="btn btn-sm btn-outline-primary"
                               data-bs-toggle="tooltip"
                               title="<?= __('Edit') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>