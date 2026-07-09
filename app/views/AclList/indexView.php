<?php
/**
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/AclList/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:43:18
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Module;
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h3 mb-0"><?= h($title) ?></h1>
        <div class="btn-group">
            <a href="<?= DevAclList::URI_INDEX ?>" class="btn btn-outline-secondary"><?= __('ACL records | Записи ACL | Записи ACL') ?></a>
            <?php if (can_add(Module::MOD_SECURITY)): ?>
                <a href="<?= DevAclTable::URI_ADD ?>" class="btn btn-primary"><?= __('Add ACL table | Добавить ACL-таблицу | Додати ACL-таблицю') ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php require DIR_INC . '/acl_tables.php'; ?>
</div>