<?php
/**
 *  Project : my.ri.net.ua
 *  File    : pay_form.php
 *  Path    : app/views/inc/pay_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Nov 2025 22:05:26
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Форма внесения или редактирования платежа
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\App;
use config\SessionFields;
use config\tables\Pay;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
require_once DIR_LIBS . '/functions.php';

/** Данные из контроллера    */
/** @var string $title       */
/** @var array  $pay         */
/** @var int    $pay_type_id */
/** @var array  $ppp_list    */

$lang = Lang::code();

// Source data:
if (isset($_SESSION[SessionFields::FORM_DATA])) {
    $form_data = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
} else {
    $form_data = [];
}
$pay ??= [];
$pay_type_id ??= 0;
$abon_id = $pay[Pay::F_ABON_ID] ?? 0;

$defaults = [
    Pay::F_DATE_STR => !empty($pay[Pay::F_DATE]) ? date('Y-m-d H:i:s', $pay[Pay::F_DATE]) : date('Y-m-d H:i:s'),
    Pay::F_BANK_NO  => 'WEB'.App::get_user_id().'_'.date('YmdHis'),
    Pay::F_AGENT_ID => App::get_user_id(),
    Pay::F_ABON_ID  => $abon_id,
    Pay::F_TYPE_ID  => $pay_type_id,
    Pay::F_PAY_FAKT => 0.0,
    Pay::F_PAY_ACNT => 0.0,
    Pay::F_DESCRIPTION => '( ' . 'by ' . App::get_user_id() . ' on ' . date('Y-m-d H:i:s') . ' )',
];

// Helper for value
$form_data_fn = function(string $field) use ($form_data, $pay, $defaults): int|float|string {
    return $form_data[$field] ?? $pay[$field] ?? $defaults[$field] ?? '';
};
$wcol1 = 4; // ширина первой колонки
$wcol2 = 12 - $wcol1; // ширина второй колонки
?>
<div class="mx-auto w-auto">
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="text-center fs-4 pt-2"><?=$title?></h3>
        </div>
        <form method="post" class="needs-validation" novalidate>
            <div class="card-body">
                <input type="hidden" name="<?=Pay::POST_REC?>[<?=Pay::F_AGENT_ID?>]" value="<?=$form_data_fn(Pay::F_AGENT_ID)?>">
                <!-- Pay ID -->
                <?php if (!empty($form_data_fn(Pay::F_ID))): ?>
                    <div class="row mb-3 g-3">
                        <label class="col-<?=$wcol1?> col-form-label text-secondary" for="<?=Pay::F_ID?>">Pay ID</label>
                        <div class="col-3">
                            <input type="text" class="form-control min-w-100px text-secondary" id="<?=Pay::F_ID?>" name="<?=Pay::POST_REC?>[<?=Pay::F_ID?>]" value="<?=$form_data_fn(Pay::F_ID)?>" readonly>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- ABON_ID -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_ABON_ID?>">Abon ID</label>
                    <div class="col-3">
                        <input type="number" class="form-control min-w-100px" id="<?=Pay::F_ABON_ID?>" name="<?=Pay::POST_REC?>[<?=Pay::F_ABON_ID?>]" value="<?=$form_data_fn(Pay::F_ABON_ID)?>" required>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
                <!-- PAY_FAKT | Фактическая сумма -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_PAY_FAKT?>">Фактическая сумма</label>
                    <div class="col-3">
                        <input type="number" step="0.01" class="form-control min-w-100px" lang="en" id="<?=Pay::F_PAY_FAKT?>" name="<?=Pay::POST_REC?>[<?=Pay::F_PAY_FAKT?>]" value="<?=$form_data_fn(Pay::F_PAY_FAKT)?>" required>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
                <!-- PAY_ACNT | Сумма на ЛС -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_PAY_ACNT?>">Сумма на ЛС</label>
                    <div class="col-3">
                        <input type="number" step="0.01" class="form-control min-w-100px" lang="en" id="<?=Pay::F_PAY_ACNT?>" name="<?=Pay::POST_REC?>[<?=Pay::F_PAY_ACNT?>]" value="<?=$form_data_fn(Pay::F_PAY_ACNT)?>" required>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
                <!-- DATE | Дата -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_DATE_STR?>">Дата</label>
                    <div class="col-4">
                        <input type="text" class="form-control min-w-200" id="<?=Pay::F_DATE_STR?>" name="<?=Pay::POST_REC?>[<?=Pay::F_DATE_STR?>]" value="<?=$form_data_fn(Pay::F_DATE_STR)?>">
                    </div>
                </div>
                <!-- BANK_NO | Bank No -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_BANK_NO?>">Bank No</label>
                    <div class="col-<?=$wcol2?>">
                        <input type="text" class="form-control min-w-200" id="<?=Pay::F_BANK_NO?>" name="<?=Pay::POST_REC?>[<?=Pay::F_BANK_NO?>]" value="<?=$form_data_fn(Pay::F_BANK_NO)?>" required>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
                <!-- TYPE_ID | Тип операции -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_TYPE_ID?>">Тип операции</label>
                    <div class="col-<?=$wcol2?>">
                        <select class="form-select" id="<?=Pay::F_TYPE_ID?>" name="<?=Pay::POST_REC?>[<?=Pay::F_TYPE_ID?>]" required>
                            <option value="">--</option>
                            <?php foreach (Pay::TYPES as $type_id => $labels): ?>
                                <option value="<?=$type_id?>" <?=($type_id == $form_data_fn(Pay::F_TYPE_ID) ? 'selected' : '')?>><?=$labels[$lang]?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
                <!-- PPP_ID | ППП -->
                <div class="row mb-3 g-3">
                    <label class="col-<?=$wcol1?> col-form-label" for="<?=Pay::F_PPP_ID?>">ППП</label>
                    <div class="col-<?=$wcol2?>">
                        <select class="form-select" id="<?=Pay::F_PPP_ID?>" name="<?=Pay::POST_REC?>[<?=Pay::F_PPP_ID?>]" required>
                            <option value="">--</option>
                            <?php foreach ($ppp_list as $ppp_id => $title): ?>
                                <option value="<?=$ppp_id?>" <?=($ppp_id == $form_data_fn(Pay::F_PPP_ID) ? 'selected' : '')?>><?=$title?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
                <!-- DESCRIPTION | Описание -->
                <div class="row mb-3 g-3">
                    <div class="col-12">
                        <label class="form-label" for="<?=Pay::F_DESCRIPTION?>">Описание</label>
                        <textarea class="form-control" id="<?=Pay::F_DESCRIPTION?>" 
                                name="<?=Pay::POST_REC?>[<?=Pay::F_DESCRIPTION?>]" 
                                rows="<?=get_count_rows_for_textarea($form_data_fn(Pay::F_DESCRIPTION));?>" 
                                required><?=$form_data_fn(Pay::F_DESCRIPTION)?></textarea>
                        <div class="invalid-feedback">Required</div>
                    </div>
                </div>
            </div>
            <!-- Действия -->
            <div class="card-footer text-center">
                <button class="btn btn-primary" type="submit"><?=__('Сохранить')?></button>
                <a href="<?=Pay::URI_LIST;?>/<?=$abon_id;?>" class="btn btn-secondary"><?=__('Вернуться к списку')?></a>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>