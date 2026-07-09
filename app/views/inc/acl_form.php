<?php
/**
 *  Project : my.ri.net.ua
 *  File    : acl_form.php
 *  Path    : app/views/inc/acl_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:45:12
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of acl_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Module;
use config\tables\TP;
?>
<form method="post" action="<?= DevAclList::URI_SAVE ?>" class="card card-body">
    <input type="hidden" name="<?= DevAclList::POST_REC ?>[<?= DevAclList::F_ID ?>]" value="<?= (int) ($record[DevAclList::F_ID] ?? 0) ?>">

    <div class="mb-3">
        <label class="form-label"><?= __('Technical site | Техплощадка | Техмайданчик') ?></label>
        <select name="<?= DevAclList::POST_REC ?>[<?= DevAclList::F_TP_ID ?>]" class="form-select">
            <option value=""><?= __('Global | Глобально | Глобально') ?></option>
            <?php foreach ($tp_list as $tp): ?>
                <option value="<?= (int) $tp[TP::F_ID] ?>" <?= (string) ($record[DevAclList::F_TP_ID] ?? '') === (string) $tp[TP::F_ID] ? 'selected' : '' ?>>
                    <?= h($tp[TP::F_TITLE]) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= __('ACL table | ACL-таблица | ACL-таблиця') ?> *</label>
        <select name="<?= DevAclList::POST_REC ?>[<?= DevAclList::F_ACL_TABLE_ID ?>]" class="form-select" required>
            <option value=""><?= __('Not selected | Не выбрано | Не вибрано') ?></option>
            <?php foreach ($tables as $table): ?>
                <option value="<?= (int) $table[DevAclTable::F_ID] ?>" <?= (string) ($record[DevAclList::F_ACL_TABLE_ID] ?? '') === (string) $table[DevAclTable::F_ID] ? 'selected' : '' ?>>
                    <?= h($table[DevAclTable::F_NAME]) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= __('Address or network | Адрес или сеть | Адреса або мережа') ?> *</label>
        <input type="text" name="<?= DevAclList::POST_REC ?>[<?= DevAclList::F_ADDRESS ?>]" value="<?= h($record[DevAclList::F_ADDRESS] ?? '') ?>" class="form-control font-monospace" required>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= __('Comment | Комментарий | Коментар') ?></label>
        <input type="text" name="<?= DevAclList::POST_REC ?>[<?= DevAclList::F_COMMENT ?>]" value="<?= h($record[DevAclList::F_COMMENT] ?? '') ?>" class="form-control">
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" name="<?= DevAclList::POST_REC ?>[<?= DevAclList::F_ENABLED ?>]" value="1" class="form-check-input" id="acl-enabled" <?= !isset($record[DevAclList::F_ENABLED]) || !empty($record[DevAclList::F_ENABLED]) ? 'checked' : '' ?>>
        <label class="form-check-label" for="acl-enabled"><?= __('Enabled | Включено | Увімкнено') ?></label>
    </div>

    <div class="d-flex gap-2">
        <?php if (($is_new && can_add(Module::MOD_SECURITY)) || (!$is_new && can_edit(Module::MOD_SECURITY))): ?>
            <button type="submit" class="btn btn-primary"><?= __('Save | Сохранить | Зберегти') ?></button>
        <?php endif; ?>
        <a href="<?= DevAclList::URI_INDEX ?>" class="btn btn-outline-secondary"><?= __('Back to records | К списку записей | До списку записів') ?></a>
        <a href="<?= DevAclTable::URI_INDEX ?>" class="btn btn-outline-secondary"><?= __('Back to tables | К списку таблиц | До списку таблиць') ?></a>
    </div>
</form>