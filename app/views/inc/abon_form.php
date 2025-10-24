<?php
/*
 *  Project : my.ri.net.ua
 *  File    : abon_form.php
 *  Path    : app/views/inc/abon_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Форма редактирования данных абонента
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use \config\tables\Abon;
use billing\core\base\Lang;
use config\SessionFields;
Lang::load_inc(__FILE__);

/**
 *  Данные в форму передаются в массиве $abon[]
 *  Поддерживаемые поля описаны константами Abon::F_*
 */

if (isset($_SESSION[SessionFields::FORM_DATA])) {
    $form_data = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
} else {
    $form_data = [];
}

/** @var array $abon */

if (!isset($abon) || !is_array($abon)) {
    throw new Exception('$abon[] -- не передан');
}

$form_data_fn = function(string $field) use ($form_data, $abon): int|float|string {
    return $form_data[$field] ?? $abon[$field] ?? "";
};

?>
<div class="row justify-content-center">
<div class="col-12 col-md-10 col-lg-8">
    <div class="card mb-4 w-75">
        <div class="card-header">
            <h2><?=$form_data_fn(Abon::F_ID) . '<br>' . h($form_data_fn(Abon::F_ADDRESS));?></h2>
        </div>
        <form action="<?=Abon::URI_UPDATE;?>/<?=$form_data_fn(Abon::F_ID);?>" method="post">
            <div class="card-body">
                <input type="hidden" name="<?= Abon::POST_REC;?>[<?= Abon::F_ID;?>]" value="<?= $form_data_fn(Abon::F_ID);?>">

                <div class="mb-3 row">
                    <!-- ID пользователя -->
                    <label for="abon_user_id" class="col-sm-3 col-form-label"><?=__('ID пользователя');?></label>
                    <div class="col-sm-3">
                        <input type="number" class="form-control text-center" id="abon_user_id" name='<?=Abon::POST_REC;?>[<?=Abon::F_USER_ID;?>]' value='<?=intval($form_data_fn(Abon::F_USER_ID));?>' required>
                    </div>
                </div>

                <!-- Адрес подключения -->
                <div class="mb-3 row">
                    <label for="abon_address" class="col-sm-3 col-form-label"><?=__('Адрес подключения');?></label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="abon_address" rows="2" name="<?= Abon::POST_REC;?>[<?= Abon::F_ADDRESS;?>]"><?=($form_data_fn(Abon::F_ADDRESS) ? h($form_data_fn(Abon::F_ADDRESS)) : "");?></textarea>
                    </div>
                </div>

                <!-- Координаты (Google Maps) -->
                <div class="mb-3 row">
                    <label for="abon_coord_gmap" class="col-sm-3 col-form-label">Координаты (Google Maps)</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control text-center text-secondary" id="abon_coord_gmap" name="<?= Abon::POST_REC;?>[<?= Abon::F_COORD_GMAP;?>]" value="<?=($form_data_fn(Abon::F_COORD_GMAP) ? h($form_data_fn(Abon::F_COORD_GMAP)) : "");?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <!-- Дата подключения -->
                    <label for="abon_date_join" class="col-sm-3 col-form-label">Дата подключения</label>
                    <div class="col-sm-3">
                        <input type="date" class="form-control w-auto" id="abon_date_join" name="<?= Abon::POST_REC;?>[<?= Abon::F_DATE_JOIN;?>]" value="<?= $form_data_fn(Abon::F_DATE_JOIN) ? date('Y-m-d', $form_data_fn(Abon::F_DATE_JOIN)) : ''; ?>">
                    </div>
                    <!-- Является плательщиком -->
                    <div class="col-sm-3"></div>
                    <div class="col-sm-3 text-nowrap">
                        <input type="checkbox" class="form-check-input align-middle" id="abon_is_payer" name="<?= Abon::POST_REC;?>[<?= Abon::F_IS_PAYER;?>]" value="1" <?= $form_data_fn(Abon::F_IS_PAYER) ? 'checked' : '';?>>
                        <label for="abon_is_payer" class="col-form-label align-middle text-start">Плательщик</label>
                    </div>
                </div>

                <!-- Примечания -->
                <div class="mb-3 row">
                    <label for="abon_comments" class="col-sm-3 col-form-label">Примечания</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="abon_comments" rows="3" name="<?= Abon::POST_REC;?>[<?= Abon::F_COMMENTS;?>]"><?=($form_data_fn(Abon::F_COMMENTS) ? h($form_data_fn(Abon::F_COMMENTS)) : "");?></textarea>
                    </div>
                </div>

                <!-- duty_max_warn, duty_max_off, duty_auto_off, duty_wait_days — блок задолженности -->
                <div class="row mb-3 text-center text-secondary fs-7 border-1">
                    <div class="col-5 col-sm-5">
                        <div class="form-label">&nbsp;</div>
                        <span class="form-control form-control-sm text-end border-0 text-secondary">Границы обслуживания</span>
                    </div>
                    <!-- Каждая колонка — метка + поле -->
                    <div class="col-4 col-sm-2" title="<?=Abon::DESCRIPTIONS[Abon::F_DUTY_MAX_WARN][Lang::code()];?>">
                        <label for="abon_duty_max_warn" class="form-label">Предупреждение</label>
                        <input type="number" class="form-control form-control-sm text-center text-secondary" id="abon_duty_max_warn" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_MAX_WARN;?>]" value="<?= h($form_data_fn(Abon::F_DUTY_MAX_WARN));?>">
                    </div>
                    <div class="col-4 col-sm-2" title="<?=Abon::DESCRIPTIONS[Abon::F_DUTY_MAX_OFF][Lang::code()];?>">
                        <label for="abon_duty_max_off" class="form-label">Отключение</label>
                        <input type="number" class="form-control form-control-sm text-center text-secondary" id="abon_duty_max_off" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_MAX_OFF;?>]" value="<?= h($form_data_fn(Abon::F_DUTY_MAX_OFF));?>">
                    </div>
                    <div class="col-2 col-sm-1" title="<?=Abon::DESCRIPTIONS[Abon::F_DUTY_AUTO_OFF][Lang::code()];?>">
                        <label for="abon_duty_auto_off" class="form-label">Откл.</label>
                        <div class="d-flex justify-content-center align-items-center mt-1">
                            <input type="checkbox" class="form-check-input" id="abon_duty_auto_off" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_AUTO_OFF;?>]" value="1" <?= $form_data_fn(Abon::F_DUTY_AUTO_OFF) ? 'checked' : '';?>>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2" title="<?=Abon::DESCRIPTIONS[Abon::F_DUTY_WAIT_DAYS][Lang::code()];?>">
                        <label for="abon_duty_wait_days" class="form-label">Ожидание</label>
                        <input type="number" class="form-control form-control-sm text-center text-secondary" id="abon_duty_wait_days" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_WAIT_DAYS;?>]" value="<?= h($form_data_fn(Abon::F_DUTY_WAIT_DAYS));?>">
                    </div>
                </div>
            </div>

            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary px-4"><?= __('Save'); ?></button>
                <?php if ($form_data_fn(Abon::F_USER_ID) == App::get_user_id()) : ?>
                    <a href="/my" class="btn btn-secondary px-4"><?= __('Вернуться'); ?></a>
                <?php else : ?>
                    <a href="<?=Abon::URI_VIEW;?>/<?=$form_data_fn(Abon::F_USER_ID);?>" class="btn btn-secondary px-4"><?= __('Вернуться'); ?></a>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>
</div>

