<?php
/**
 *  Project : my.ri.net.ua
 *  File    : intervalView.php
 *  Path    : app/views/Conciliation/intervalView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Nov 2025 04:16:28
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of intervalView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\User;
use config\tables\Abon;
use config\tables\Module;
use config\Conciliation;
require_once DIR_LIBS . '/form_functions.php';

/** @var array $user */
/** @var array $abon */

?>
<div class="card col-12 col-md-10 col-lg-8">
    <div class="card-header">
        <h2 class="h4 mb-3"><?=__('Select the period for drawing up the Reconciliation Report')?></h2>
        <h3 class="h4 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> |</span> <?=$user[User::F_NAME_FULL];?>:</h3>
    </div>
    <div class="card-body">
        <?php require DIR_INC ."/conciliation_intervals.php"; ?>
    </div>
</div>
