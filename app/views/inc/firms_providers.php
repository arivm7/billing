<?php
/**
 *  Project : my.ri.net.ua
 *  File    : firms_providers.php
 *  Path    : app/views/inc/firms_providers.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firms_providers.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\Employees;
use config\tables\Firm;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="text-secondary">No</th>
                <th class="text-secondary">ID</th>
                <th><?= __('Full name | Полное название | Повна назва') ?></th>
                <th><?= __('Public title | Публичный заголовок | Публічний заголовок') ?></th>
                <th><?= __('Job title | Должность | Посада') ?></th>
                <th><?= __('Active | Активный | Активний') ?></th>
                <th><?= __('Deleted | Удалённый | Видалений') ?></th>
                <th><?= __('Agent | Агент | Агент') ?></th>
                <th><?= __('Client | Клиент | Клієнт') ?></th>
                <th class="text-end"><?= __('Actions | Действия | Дії') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($provider_firms)) : ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-3"><?= __('No entries | Нет записей | Немає записів') ?></td>
                </tr>
            <?php else: ?>
                <?php $rowNo = 0; ?>
                <?php foreach ($provider_firms as $firm): ?>
                    <tr>
                        <td class="text-secondary text-end"><?= ++$rowNo ?></td>
                        <td class="text-secondary text-end"><?= h($firm[Firm::F_ID] ?? '') ?></td>
                        <td><?= h($firm[Firm::F_NAME_LONG] ?? '') ?></td>
                        <td><?= h($firm[Firm::F_NAME_TITLE] ?? '') ?></td>
                        <td><?= h($firm[Employees::F_JOB_TITLE] ?? '') ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_ACTIVE]) ? '1' : '0' ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_DELETE]) ? '1' : '0' ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_AGENT]) ? '1' : '0' ?></td>
                        <td><?= !empty($firm[Firm::F_HAS_CLIENT]) ? '1' : '0' ?></td>
                        <td class="text-end">
                            <a href="<?= h($uri_edit . '/' . (int) $firm[Firm::F_ID]) ?>"
                               class="btn btn-sm btn-outline-primary"
                               data-bs-toggle="tooltip"
                               title="<?= __('Edit | Редактировать | Редагувати') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>