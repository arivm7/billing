<?php
/**
 *  Project : my.ri.net.ua
 *  File    : acl_tables.php
 *  Path    : app/views/inc/acl_tables.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:45:12
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of acl_tables.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Module;
?>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead>
            <tr>
                <th class="text-end">ID</th>
                <th><?= __('Name | Имя | Імʼя') ?></th>
                <th><?= __('Description | Описание | Опис') ?></th>
                <th class="text-end"><?= __('Records | Записи | Записи') ?></th>
                <th class="text-center"><?= __('Actions | Действия | Дії') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tables)): ?>
                <tr><td colspan="5" class="text-center text-secondary"><?= __('No data | Нет данных | Немає даних') ?></td></tr>
            <?php endif; ?>
            <?php foreach ($tables as $table): ?>
                <tr>
                    <td class="text-end font-monospace"><?= (int) $table[DevAclTable::F_ID] ?></td>
                    <td class="font-monospace"><?= h($table[DevAclTable::F_NAME]) ?></td>
                    <td><?= h($table[DevAclTable::F_DESCRIPTION] ?? '') ?></td>
                    <td class="text-end"><?= (int) ($table['acl_count'] ?? 0) ?></td>
                    <td class="text-center">
                        <a href="<?= DevAclList::URI_INDEX ?>?<?= DevAclList::F_ACL_TABLE_ID ?>=<?= (int) $table[DevAclTable::F_ID] ?>" class="btn btn-sm btn-outline-primary"><?= __('Records | Записи | Записи') ?></a>
                        <?php if (can_edit(Module::MOD_SECURITY)): ?>
                            <a href="<?= DevAclTable::URI_EDIT ?>/<?= (int) $table[DevAclTable::F_ID] ?>" class="btn btn-sm btn-outline-warning"><?= __('Edit | Ред. | Ред.') ?></a>
                        <?php endif; ?>
                        <?php if (can_del(Module::MOD_SECURITY)): ?>
                            <a href="<?= DevAclTable::URI_DELETE ?>/<?= (int) $table[DevAclTable::F_ID] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('Delete ACL table? | Удалить ACL-таблицу? | Видалити ACL-таблицю?') ?>');"><?= __('Delete | Удалить | Видалити') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>