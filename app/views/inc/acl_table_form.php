<?php
/**
 *  Project : my.ri.net.ua
 *  File    : acl_table_form.php
 *  Path    : app/views/inc/acl_table_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:43:18
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of acl_table_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Module;
?>
<form method="post" action="<?= DevAclTable::URI_SAVE ?>" class="card card-body">
    <input type="hidden" name="<?= DevAclTable::POST_REC ?>[<?= DevAclTable::F_ID ?>]" value="<?= (int) ($table[DevAclTable::F_ID] ?? 0) ?>">

    <div class="mb-3">
        <label class="form-label"><?= __('Name | Имя | Імʼя') ?> *</label>
        <input type="text" name="<?= DevAclTable::POST_REC ?>[<?= DevAclTable::F_NAME ?>]" value="<?= h($table[DevAclTable::F_NAME] ?? '') ?>" class="form-control font-monospace" required>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= __('Description | Описание | Опис') ?></label>
        <input type="text" name="<?= DevAclTable::POST_REC ?>[<?= DevAclTable::F_DESCRIPTION ?>]" value="<?= h($table[DevAclTable::F_DESCRIPTION] ?? '') ?>" class="form-control">
    </div>

    <?php if (!$is_new): ?>
        <div class="alert alert-info">
            <?= __('Records in table | Записей в таблице | Записів у таблиці') ?>: <?= (int) $record_count ?>
        </div>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <?php if (($is_new && can_add(Module::MOD_SECURITY)) || (!$is_new && can_edit(Module::MOD_SECURITY))): ?>
            <button type="submit" class="btn btn-primary"><?= __('Save | Сохранить | Зберегти') ?></button>
        <?php endif; ?>
        <a href="<?= DevAclTable::URI_INDEX ?>" class="btn btn-outline-secondary"><?= __('Back to tables | К списку таблиц | До списку таблиць') ?></a>
        <?php if (!empty($table[DevAclTable::F_ID])): ?>
            <a href="<?= DevAclList::URI_INDEX ?>?<?= DevAclList::F_ACL_TABLE_ID ?>=<?= (int) $table[DevAclTable::F_ID] ?>" class="btn btn-outline-secondary"><?= __('Table records | Записи таблицы | Записи таблиці') ?></a>
        <?php endif; ?>
    </div>
</form>