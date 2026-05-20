<?php
/**
 *  Project : my.ri.net.ua
 *  File    : fwinputView.php
 *  Path    : app/views/Tp/fwinputView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of fwinputView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\FwInput;
use config\tables\TP;

$view = FwInput::VIEWS[$phase] ?? FwInput::VIEWS[FwInput::PHASE_LOGIN];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0"><?= __('Firewall input wizard | Мастер firewall input | Майстер firewall input') ?></h2>
            <div class="text-secondary small"><?= h($phase) ?></div>
        </div>
        <div>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_INDEX ?>"><?= __('Back to TP list | К списку ТП | До списку ТП') ?></a>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        <?php foreach (FwInput::PHASES_ORDER as $phaseName): ?>
            <li class="nav-item">
                <a class="nav-link <?= $phaseName === $phase ? 'active' : '' ?>"
                   href="<?= TP::URI_FW_INPUT . '?phase=' . $phaseName ?>">
                    <?= h($phaseName) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php require APP . '/views/Tp/' . $view . '.php'; ?>
</div>