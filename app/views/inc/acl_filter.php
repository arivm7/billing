<?php
/**
 *  Project : my.ri.net.ua
 *  File    : acl_filter.php
 *  Path    : app/views/inc/acl_filter.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:43:18
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of acl_filter.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
?>
<form method="get" action="<?= DevAclList::URI_INDEX ?>" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label"><?= __('ACL table | ACL-таблица | ACL-таблиця') ?></label>
            <select name="<?= DevAclList::F_ACL_TABLE_ID ?>" class="form-select">
                <option value=""><?= __('All | Все | Усі') ?></option>
                <?php foreach ($tables as $table): ?>
                    <option value="<?= (int) $table[DevAclTable::F_ID] ?>" <?= (string) $filter_acl_table_id === (string) $table[DevAclTable::F_ID] ? 'selected' : '' ?>>
                        <?= h($table[DevAclTable::F_NAME]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label"><?= __('Address fragment | Фрагмент адреса | Фрагмент адреси') ?></label>
            <input type="text" name="<?= DevAclList::F_ADDRESS ?>" value="<?= h($filter_address) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary"><?= __('Filter | Фильтр | Фільтр') ?></button>
            <a href="<?= DevAclList::URI_INDEX ?>" class="btn btn-outline-secondary"><?= __('Reset | Сбросить | Скинути') ?></a>
        </div>
    </div>
</form>