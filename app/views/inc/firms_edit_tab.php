<?php
/**
 *  Project : my.ri.net.ua
 *  File    : firms_edit_tab.php
 *  Path    : app/views/inc/firms_edit_tab.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:19:44
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firms_edit_tab.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\base\Lang;
Lang::load_inc(__FILE__);


?>
<ul class="nav nav-tabs" id="firmEditTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="firm-data-tab" data-bs-toggle="tab" data-bs-target="#firm-data-pane" type="button" role="tab">
            <?= __('Enterprise') ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="firm-employees-tab" data-bs-toggle="tab" data-bs-target="#firm-employees-pane" type="button" role="tab">
            <?= __('Employees') ?>
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 p-3">
    <div class="tab-pane fade show active" id="firm-data-pane" role="tabpanel" aria-labelledby="firm-data-tab">
        <?php require DIR_INC . '/firms_edit_firm.php'; ?>
    </div>
    <div class="tab-pane fade" id="firm-employees-pane" role="tabpanel" aria-labelledby="firm-employees-tab">
        <?php require DIR_INC . '/firms_edit_employees.php'; ?>
    </div>
</div>