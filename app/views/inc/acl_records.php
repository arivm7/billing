<?php
/**
 *  Project : my.ri.net.ua
 *  File    : acl_records.php
 *  Path    : app/views/inc/acl_records.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Jul 2026 15:45:12
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of acl_records.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Module;
use config\tables\TP;

$pages = max(1, (int) ceil($total / $per_page));
?>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
        <thead>
            <tr>
                <th class="text-end">ID</th>
                <th><?= __('ACL table | ACL-таблица | ACL-таблиця') ?></th>
                <th><?= __('Technical site | Техплощадка | Техмайданчик') ?></th>
                <th><?= __('Address | Адрес | Адреса') ?></th>
                <th><?= __('Comment | Комментарий | Коментар') ?></th>
                <th class="text-center"><?= __('Enabled | Включено | Увімкнено') ?></th>
                <th class="text-center"><?= __('Actions | Действия | Дії') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="7" class="text-center text-secondary"><?= __('No data | Нет данных | Немає даних') ?></td></tr>
            <?php endif; ?>
            <?php foreach ($records as $row): ?>
                <tr>
                    <td class="text-end font-monospace"><?= (int) $row[DevAclList::F_ID] ?></td>
                    <td><?= h($row['acl_table_name'] ?? '') ?></td>
                    <td>
                        <?php if ($row[DevAclList::F_TP_ID] === null): ?>
                            <span class="badge bg-secondary"><?= __('Global | Глобально | Глобально') ?></span>
                        <?php else: ?>
                            <?= h($row['tp_title'] ?? ('#' . $row[DevAclList::F_TP_ID])) ?>
                        <?php endif; ?>
                    </td>
                    <td class="font-monospace"><?= h($row[DevAclList::F_ADDRESS]) ?></td>
                    <td><?= h($row[DevAclList::F_COMMENT] ?? '') ?></td>
                    <td class="text-center"><?= !empty($row[DevAclList::F_ENABLED]) ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' ?></td>
                    <td class="text-center">
                        <?php if (can_edit(Module::MOD_SECURITY)): ?>
                            <a href="<?= DevAclList::URI_EDIT ?>/<?= (int) $row[DevAclList::F_ID] ?>" class="btn btn-sm btn-outline-warning"><?= __('Edit | Ред. | Ред.') ?></a>
                        <?php endif; ?>
                        <?php if (can_del(Module::MOD_SECURITY)): ?>
                            <a href="<?= DevAclList::URI_DELETE ?>/<?= (int) $row[DevAclList::F_ID] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('Delete record? | Удалить запись? | Видалити запис?') ?>');"><?= __('Delete | Удалить | Видалити') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php
                $query = $_GET;
                $query['page'] = $i;
                ?>
                <li class="page-item <?= $i === (int) $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= DevAclList::URI_INDEX ?>?<?= http_build_query($query) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>