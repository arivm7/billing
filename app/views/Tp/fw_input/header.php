<?php
/**
 *  Project : my.ri.net.ua
 *  File    : header.php
 *  Path    : app/views/Tp/fw_input/header.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of header.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




use billing\core\App;
use config\FwInput;
use config\tables\TP;

/**
 * Переменные переданные формой для этого документа
 * 
 * @var string $page_title
 * @var string $device_title
 * @var string $device_description
 * 
 */

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php foreach (FwInput::PHASES_ORDER as $one_phase): ?>
            <?php if ($one_phase == $phase): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= FwInput::PHASES_TITLES[$one_phase][App::lang()] ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= TP::URI_FW_INPUT.'?'.FwInput::F_GET_PHASE.'='.$one_phase ?>"><?= FwInput::PHASES_TITLES[$one_phase][App::lang()] ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>

    </ol>
</nav>
<?php if (!empty($page_title)): ?>
    <h3 class="fs-4 text-end m-0 p-0 lh-sm"><?= $page_title ?></h3>
<?php endif; ?>
<div class="d-flex justify-content-between align-items-center">
    <div>
        <?php if (!empty($device_title)): ?>
            <h4 class="fs-6 text-end m-0 p-0 lh-sm"><?= __('Device | Устройство | Пристрій') . ': ' ?><span class="fw-semibold text-success-emphasis bg-success-subtle border border-success-subtle rounded-2 px-3 py-2"><?= $device_title ?></span></h4>
        <?php endif; ?>
    </div>
    <div>
        <?php if (!empty($device_description)): ?>
            <h4 class="fs-6 text-end m-0 p-0 lh-sm"><?= $device_description ?></h4>
        <?php endif; ?>
    </div>
</div>    
<?php unset($one_phase, $page_title, $device_title, $device_description); ?>
