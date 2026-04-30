<?php
/**
 *  Project : my.ri.net.ua
 *  File    : firms_edit_firm.php
 *  Path    : app/views/inc/firms_edit_firm.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firms_edit_firm.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\Firm;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

require_once DIR_LIBS . '/functions.php';

?>
<form method="post" action="<?= h($uri_save_firm) ?>" class="row g-3">
<div class="container mt-3">
    <input type="hidden" name="<?= Firm::POST_REC ?>[<?= Firm::F_ID ?>]" value="<?= (int) ($firm[Firm::F_ID] ?? 0) ?>">

    <!-- ID -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label text-secondary">ID</label>
        </div>
        <div class="col-2">
            <span class="form-control text-secondary">
                <?= (int) ($firm[Firm::F_ID] ?? 0) ?>
            </span>
        </div>
        <div class="col-4">::</div>
        <div class="col-3">::</div>
    </div>

    <!-- Owner -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_owner_id"><?= __('Owner user ID') ?></label>
        </div>
        <div class="col-2">
            <input type="number"
                   class="form-control"
                   id="firm_owner_id"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_OWNER_ID ?>]"
                   value="<?= (int) ($firm[Firm::F_OWNER_ID] ?? 0) ?>"
                   <?= can_edit(Module::MOD_FIRM) ? '' : 'readonly' ?>>
        </div>
        <div class="col-4">:: <?= __user(user_id: $firm[Firm::F_OWNER_ID] ?? 0) ?></div>
        <div class="col-3">::</div>
    </div>

    <!-- Short name -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_name_short"><?= __('Short name') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_name_short"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_NAME_SHORT ?>]"
                   value="<?= h((string) ($firm[Firm::F_NAME_SHORT] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Full name -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_name_long"><?= __('Full name') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_name_long"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_NAME_LONG ?>]"
                   value="<?= h((string) ($firm[Firm::F_NAME_LONG] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Public title -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_name_title"><?= __('Public title') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_name_title"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_NAME_TITLE ?>]"
                   value="<?= h((string) ($firm[Firm::F_NAME_TITLE] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Manager position -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_manager_job_title"><?= __('Responsible position') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_manager_job_title"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_MANAGER_JOB_TITLE ?>]"
                   value="<?= h((string) ($firm[Firm::F_MANAGER_JOB_TITLE] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Manager short name -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_manager_name_short"><?= __('Responsible short name') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_manager_name_short"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_MANAGER_NAME_SHORT ?>]"
                   value="<?= h((string) ($firm[Firm::F_MANAGER_NAME_SHORT] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Manager full name -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_manager_name_long"><?= __('Responsible full name') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_manager_name_long"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_MANAGER_NAME_LONG ?>]"
                   value="<?= h((string) ($firm[Firm::F_MANAGER_NAME_LONG] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- EDRPOU -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_cod_edrpou"><?= __('EDRPOU') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_cod_edrpou"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_COD_EDRPOU ?>]"
                   value="<?= h((string) ($firm[Firm::F_COD_EDRPOU] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Tax ID -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_cod_ipn"><?= __('Tax ID') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_cod_ipn"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_COD_IPN ?>]"
                   value="<?= h((string) ($firm[Firm::F_COD_IPN] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Phones -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_office_phones"><?= __('Office phones') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_office_phones"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_OFFICE_PHONES ?>]"
                   value="<?= h((string) ($firm[Firm::F_OFFICE_PHONES] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- IBAN -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_bank_iban"><?= __('IBAN') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_bank_iban"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_BANK_IBAN ?>]"
                   value="<?= h((string) ($firm[Firm::F_BANK_IBAN] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Bank -->
    <div class="row mb-2 align-items-center">
        <div class="col-3"><label class="form-label" for="firm_bank_name"><?= __('Bank name') ?></label></div>
        <div class="col-6">
            <input type="text" class="form-control" id="firm_bank_name"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_BANK_NAME ?>]"
                   value="<?= h((string) ($firm[Firm::F_BANK_NAME] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Registration -->
    <div class="row mb-2 align-items-start">
        <div class="col-3"><label class="form-label" for="firm_registration"><?= __('Registration') ?></label></div>
        <div class="col-6">
            <textarea class="form-control" id="firm_registration"
                      name="<?= Firm::POST_REC ?>[<?= Firm::F_REGISTRATION ?>]" rows="<?= get_count_rows_for_textarea($firm[Firm::F_REGISTRATION]) ?>"><?= h((string) ($firm[Firm::F_REGISTRATION] ?? '')) ?></textarea>
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Registration address -->
    <div class="row mb-2 align-items-start">
        <div class="col-3"><label class="form-label" for="firm_address_registration"><?= __('Registration address') ?></label></div>
        <div class="col-6">
            <textarea class="form-control" id="firm_address_registration"
                      name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_REGISTRATION ?>]" rows="<?= get_count_rows_for_textarea($firm[Firm::F_ADDRESS_REGISTRATION]) ?>"><?= h((string) ($firm[Firm::F_ADDRESS_REGISTRATION] ?? '')) ?></textarea>
        </div>
        <div class="col-3"></div>
    </div>
    
    <hr>
    
    <!-- Office address full -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_office_full"><?= __('Office address') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_office_full"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_OFFICE_FULL ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_OFFICE_FULL] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Office courier address -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_office_courier"><?= __('Courier address') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_office_courier"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_OFFICE_COURIER ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_OFFICE_COURIER] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Postal person -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_person"><?= __('Postal person') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_person"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_PERSON ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_PERSON] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Postal index -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_index"><?= __('Postal index') ?></label>
        </div>
        <div class="col-2">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_index"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_INDEX ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_INDEX] ?? '')) ?>">
        </div>
        <div class="col-7"></div>
    </div>

    <!-- Street -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_ul"><?= __('Street') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_ul"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_UL ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_UL] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Building -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_dom"><?= __('Building') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_dom"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_DOM ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_DOM] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- City -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_sity"><?= __('City') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_sity"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_SITY ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_SITY] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Region -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_region"><?= __('Region') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_region"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_REGION ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_REGION] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>

    <!-- Country -->
    <div class="row mb-2 align-items-center">
        <div class="col-3">
            <label class="form-label" for="firm_address_post_country"><?= __('Country') ?></label>
        </div>
        <div class="col-6">
            <input type="text"
                   class="form-control"
                   id="firm_address_post_country"
                   name="<?= Firm::POST_REC ?>[<?= Firm::F_ADDRESS_POST_COUNTRY ?>]"
                   value="<?= h((string) ($firm[Firm::F_ADDRESS_POST_COUNTRY] ?? '')) ?>">
        </div>
        <div class="col-3"></div>
    </div>    
    
    <hr>
    
    <div class="col-12">
        <div class="row g-3">
            <?php
            $flagLabels = [
                Firm::F_HAS_ACTIVE      => __('Enterprise is active'),
                Firm::F_HAS_DELETE      => __('Marked as deleted'),
                Firm::F_HAS_AGENT       => __('Agent'),
                Firm::F_HAS_CLIENT      => __('Client'),
                Firm::F_HAS_ALL_VISIBLE => __('Visible to all'),
                Firm::F_HAS_ALL_LINKING => __('Allow linking for all'),
            ];
            foreach ($flagLabels as $field => $label):
            ?>
                <div class="col-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="flag_<?= h($field) ?>" name="<?= Firm::POST_REC ?>[<?= $field ?>]" value="1" <?= !empty($firm[$field]) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="flag_<?= h($field) ?>"><?= h($label) ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted">
            <?= __('Created') ?>: <?= h((string) ($firm[Firm::F_CREATION_DATE] ?? '')) ?> /
            UID <?= h((string) ($firm[Firm::F_CREATION_UID] ?? '')) ?>;
            <?= __('Modified') ?>: <?= h((string) ($firm[Firm::F_MODIFIED_DATE] ?? '')) ?> /
            UID <?= h((string) ($firm[Firm::F_MODIFIED_UID] ?? '')) ?>
        </div>
        <button type="submit" class="btn btn-primary"><?= __('Save enterprise') ?></button>
    </div>
</div>
</form>