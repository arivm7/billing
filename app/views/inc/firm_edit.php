<?php
/*
 *  Project : my.ri.net.ua
 *  File    : firm_edit.php
 *  Path    : app/views/inc/firm_edit.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firm_edit.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Firm;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
$postRec = Firm::POST_REC;
?>

<div class="container mt-4">
    <h2 class="mb-4"><?=__('Edit enterprise');?></h2>
    <form method="post">
        <input type="hidden" name="<?= Firm::POST_REC ?>[<?= Firm::F_ID ?>]" value="<?= (int)($item[Firm::F_ID] ?? 0) ?>">

        <!-- Навигация по вкладкам -->
        <ul class="nav nav-tabs" id="firmTab<?=$item[Firm::F_ID];?>" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-info<?=$item[Firm::F_ID];?>" type="button" role="tab"><?=__('Info');?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-bank<?=$item[Firm::F_ID];?>" type="button" role="tab"><?=__('Bank');?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="office-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-office<?=$item[Firm::F_ID];?>" type="button" role="tab"><?=__('Office');?></button>
            </li>
            <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-status<?=$item[Firm::F_ID];?>" type="button" role="tab"><?=__('Status');?></button>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Контент вкладок -->
        <div class="tab-content border border-top-0 p-3" id="firmTabContent<?=$item[Firm::F_ID];?>">

            <!-- Вкладка Инфо -->
            <div class="tab-pane fade show active" id="tab-edit-info<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $infoFields = [
                        Firm::F_NAME_SHORT => __('Short name'),
                        Firm::F_NAME_LONG => __('Full name'),
                        Firm::F_NAME_TITLE => __('Public enterprise name'),
                        Firm::F_MANAGER_JOB_TITLE => __('Responsible position'),
                        Firm::F_MANAGER_NAME_SHORT => __('Responsible person name'),
                        Firm::F_MANAGER_NAME_LONG => __('Full name'),
                        Firm::F_OFFICE_PHONES => __('Contact phones'),
                    ];
                    foreach ($infoFields as $field => $label) {
                        $val = h($item[$field] ?? '');
                        echo <<<HTML
<div class="col-md-6">
    <label class="form-label">{$label}</label>
    <input type="text" class="form-control" name="{$postRec}[{$field}]" value="{$val}">
</div>
HTML;
                    }
                    ?>
                </div>
            </div>

            <!-- Вкладка Банк -->
            <div class="tab-pane fade" id="tab-edit-bank<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $bankFields = [
                        Firm::F_COD_EDRPOU => __('EDRPOU code'),
                        Firm::F_COD_IPN => __('Tax ID'),
                        Firm::F_REGISTRATION => __('Registration'),
                        Firm::F_ADDRESS_REGISTRATION => __('Registration address'),
                        Firm::F_BANK_IBAN => __('IBAN'),
                        Firm::F_BANK_NAME => __('Bank name'),
                    ];
                    foreach ($bankFields as $field => $label) {
                        $val = h($item[$field] ?? '');
                        $type = $field === Firm::F_REGISTRATION || $field === Firm::F_ADDRESS_REGISTRATION ? 'textarea' : 'input';
                        if ($type === 'textarea') {
                            echo <<<HTML
<div class="col-md-12">
    <label class="form-label w-100">{$label}
    <textarea class="form-control w-100" name="{$postRec}[{$field}]" rows="2">{$val}</textarea></label>
</div>
HTML;
                        } else {
                            echo <<<HTML
<div class="col-md-6">
    <label class="form-label">{$label}
    <input type="text" class="form-control" name="{$postRec}[{$field}]" value="{$val}"></label>
</div>
HTML;
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Вкладка Офис -->
            <div class="tab-pane fade" id="tab-edit-office<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $officeFields = [
                        Firm::F_ADDRESS_OFFICE_FULL => __('Office address'),
                        Firm::F_ADDRESS_POST_PERSON => __('From whom (mail)'),
                        Firm::F_ADDRESS_POST_INDEX => __('Postal code'),
                        Firm::F_ADDRESS_POST_UL => __('Street'),
                        Firm::F_ADDRESS_POST_DOM => __('Building, corp., apt.'),
                        Firm::F_ADDRESS_POST_SITY => __('City'),
                        Firm::F_ADDRESS_POST_REGION => __('Region'),
                        Firm::F_ADDRESS_POST_COUNTRY => __('Country'),
                        Firm::F_ADDRESS_OFFICE_COURIER => __('Courier address'),
                    ];
                    foreach ($officeFields as $field => $label) {
                        $val = htmlspecialchars($item[$field] ?? '');
                        echo <<<HTML
<div class="col-md-6">
    <label class="form-label">{$label}</label>
    <input type="text" class="form-control" name="{$postRec}[{$field}]" value="{$val}">
</div>
HTML;
                    }
                    ?>
                </div>
            </div>

            <!-- Вкладка Статус -->
            <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
            <div class="tab-pane fade" id="tab-edit-status<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $checkboxes = [
                        Firm::F_HAS_ACTIVE => __('Active enterprise'),
                        Firm::F_HAS_DELETE => __('Marked as deleted'),
                        Firm::F_HAS_AGENT => __('Agent enterprise, provider representative'),
                        Firm::F_HAS_CLIENT => __('Client enterprise'),
                        Firm::F_HAS_ALL_VISIBLE => __('Visible to all'),
                        Firm::F_HAS_ALL_LINKING => __('Connectable by all'),
                    ];

                    foreach ($checkboxes as $field => $label) {
                        $checked = !empty($item[$field]) ? 'checked' : '';
                        echo <<<HTML
<div class="col-md-4">
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name='{$postRec}[{$field}]' value="1" {$checked}>
        <label class="form-check-label">{$label}</label>
    </div>
</div>
HTML;
                    }

                    // Статическая информация
                    $readonlyFields = [
                        Firm::F_CREATION_UID => __('Created by (UID)'),
                        Firm::F_CREATION_DATE => __('Creation date'),
                        Firm::F_MODIFIED_UID => __('Modified by (UID)'),
                        Firm::F_MODIFIED_DATE => __('Modification date'),
                    ];
                    echo "<table class='table border w-50'>";
                    foreach ($readonlyFields as $field => $label) {
                        $val = h($item[$field] ?? '');
                        echo "<tr><th>{$label}</th><td>{$val}</td></tr>";
                    }
                    echo "</table>";
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Кнопки -->
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><?=__('Save');?></button>
        </div>
    </form>
</div>