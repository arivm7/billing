<?php
/*
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/admin/Security/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use config\tables\Module;
?>
<div class="container my-4">
    <h1 class="display-6 mb-4"><?= h($title) ?></h1>


    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="securityTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="blocked-tab" data-bs-toggle="tab" data-bs-target="#blocked" type="button" role="tab">
                <?= __('Blocked IPs') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab">
                <?= __('Attack events') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="types-tab" data-bs-toggle="tab" data-bs-target="#types" type="button" role="tab">
                <?= __('Attack types') ?>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="securityTabsContent">

        <!-- Заблокированные IP -->
        <div class="tab-pane fade show active" id="blocked" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th><?= __('Type') ?></th>
                                <th><?= __('Blocked at') ?></th>
                                <th><?= __('Expires at') ?></th>
                                <th class="text-end"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($blocked_ips)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <?= __('No entries') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($blocked_ips as $blockedIp): ?>
                                    <tr>
                                        <td><?= h($blockedIp['ip']) ?></td>
                                        <td><?= h($blockedIp['event_type_title'] ?? ('#' . $blockedIp['event_type_id'])) ?></td>
                                        <td><?= h($blockedIp['blocked_at_fmt']) ?></td>
                                        <td><?= h($blockedIp['expires_at_fmt']) ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php if (can_del(Module::MOD_SECURITY)) : ?>
                                                <a href="<?= $uri_delete_blocked_ip ?>?ip=<?= rawurlencode($blockedIp['ip']) ?>&event_type_id=<?= (int) $blockedIp['event_type_id'] ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('<?= __('Are you sure') . '?' ?>');">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- События атак -->
        <div class="tab-pane fade" id="events" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th><?= __('Type') ?></th>
                                <th><?= __('Start date') ?></th>
                                <th><?= __('Count') ?></th>
                                <th class="text-end"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attack_events)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <?= __('No entries') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attack_events as $attackEvent): ?>
                                    <tr>
                                        <td><?= h($attackEvent['ip']) ?></td>
                                        <td><?= h($attackEvent['event_type_title'] ?? ('#' . $attackEvent['event_type_id'])) ?></td>
                                        <td><?= h($attackEvent['date_attack_fmt']) ?></td>
                                        <td><?= (int) $attackEvent['count_attacks'] ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php if (can_del(Module::MOD_SECURITY)) : ?>
                                                <a href="<?= $uri_delete_attack_event ?>?ip=<?= rawurlencode($attackEvent['ip']) ?>&event_type_id=<?= (int) $attackEvent['event_type_id'] ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('<?= __('Are you sure') . '?' ?>');">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Типы атак -->
        <div class="tab-pane fade" id="types" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?= __('Title') ?></th>
                                <th><?= __('Threshold') ?></th>
                                <th><?= __('Interval') ?></th>
                                <th><?= __('Blocking time') ?></th>
                                <th><?= __('Description') ?></th>
                                <th class="text-end"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attack_types as $attackType): ?>
                                <tr>
                                    <td><?= (int) $attackType['id'] ?></td>
                                    <td><?= h($attackType['title']) ?></td>
                                    <td><?= (int) $attackType['threshold_count'] ?></td>
                                    <td><?= (int) $attackType['analytical_interval'] ?> sec</td>
                                    <td><?= h($attackType['blocking_time_human']) ?></td>
                                    <td><?= nl2br(h((string) $attackType['description'])) ?></td>
                                    <td class="text-end text-nowrap">
                                        <?php if (can_edit(Module::MOD_SECURITY)) : ?>
                                            <a href="<?= $uri_edit_type ?>?id=<?= (int) $attackType['id'] ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>



</div>
