<?php
/**
 *  Project : my.ri.net.ua
 *  File    : editView.php
 *  Path    : app/views/Acl/editView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:43:18
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of editView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h3 mb-0"><?= h($title) ?></h1>
        <div class="btn-group">
            <a href="<?= DevAclTable::URI_INDEX ?>" class="btn btn-outline-secondary"><?= __('ACL tables | ACL-таблицы | ACL-таблиці') ?></a>
            <a href="<?= DevAclList::URI_INDEX ?><?= empty($record[DevAclList::F_ACL_TABLE_ID]) ? '' : '?' . DevAclList::F_ACL_TABLE_ID . '=' . (int) $record[DevAclList::F_ACL_TABLE_ID] ?>" class="btn btn-outline-secondary"><?= __('Current list | Текущий список | Поточний список') ?></a>
        </div>
    </div>

    <?php require DIR_INC . '/acl_form.php'; ?>
</div>