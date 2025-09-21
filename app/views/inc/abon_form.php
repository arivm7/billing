<?php
/*
 *  Project : s1.ri.net.ua
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
 * Description of abon_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use \config\tables\Abon;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 *  Данные в форму передаются в массиве $abon[]
 *  Поддерживаемые поля описаны константами Abon::F_*
 */

/** @var array $abon */
?>

<div class="container-fluid mt-4">
    <h2><?=$abon[Abon::F_ID] . '<br>' . h($abon[Abon::F_ADDRESS]);?></h2>
    <form action="" method="post">
        <input type="hidden" name="<?= Abon::POST_REC;?>[<?= Abon::F_ID;?>]" value="<?= $abon[Abon::F_ID];?>">

        <!-- 01 -->
        <div class="mb-3 row">
            <label for="abon_user_id" class="col-sm-3 col-form-label">ID пользователя</label>
            <div class="col-sm-6">
                <input type="number" class="form-control" id="abon_user_id" name='<?=Abon::POST_REC;?>[<?=Abon::F_USER_ID;?>]' value='<?=intval($abon[Abon::F_USER_ID]);?>' required>
            </div>
        </div>

        <!-- 02 -->
        <div class="mb-3 row">
            <label for="abon_address" class="col-sm-3 col-form-label">Адрес подключения</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="abon_address" name="<?= Abon::POST_REC;?>[<?= Abon::F_ADDRESS;?>]" value="<?= h($abon[Abon::F_ADDRESS]);?>">
            </div>
        </div>

        <div class="mb-3 row">
            <label for="abon_coord_gmap" class="col-sm-3 col-form-label">Координаты (Google Maps)</label>
            <div class="col-sm-6">
                <input type="text" class="form-control" id="abon_coord_gmap" name="<?= Abon::POST_REC;?>[<?= Abon::F_COORD_GMAP;?>]" value="<?=($abon[Abon::F_COORD_GMAP] ? h($abon[Abon::F_COORD_GMAP]) : "");?>">
            </div>
        </div>

        <div class="mb-3 row">
            <label for="abon_is_payer" class="col-sm-3 col-form-label">Является плательщиком</label>
            <div class="col-sm-6">
                <input type="checkbox" class="form-check-input" id="abon_is_payer" name="<?= Abon::POST_REC;?>[<?= Abon::F_IS_PAYER;?>]" value="1" <?= $abon[Abon::F_IS_PAYER] ? 'checked' : '';?>>
            </div>
        </div>

        <div class="mb-3 row">
            <label for="abon_date_join" class="col-sm-3 col-form-label">Дата подключения</label>
            <div class="col-sm-6">
                <input type="date" class="form-control" id="abon_date_join" name="<?= Abon::POST_REC;?>[<?= Abon::F_DATE_JOIN;?>]" value="<?= date('Y-m-d', $abon[Abon::F_DATE_JOIN]);?>">
            </div>
        </div>

        <div class="mb-3 row">
            <label for="abon_comments" class="col-sm-3 col-form-label">Примечания</label>
            <div class="col-sm-6">
                <textarea class="form-control" id="abon_comments" name="<?= Abon::POST_REC;?>[<?= Abon::F_COMMENTS;?>]"><?=($abon[Abon::F_COMMENTS] ? h($abon[Abon::F_COMMENTS]) : "");?></textarea>
            </div>
        </div>

        <!-- duty_max_warn, duty_max_off, duty_auto_off, duty_wait_days — блок задолженности -->
        <div class="mb-3 row">
            <label for="abon_duty_max_warn" class="col-sm-3 col-form-label">Предупреждение (дней)</label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="abon_duty_max_warn" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_MAX_WARN;?>]" value="<?= h($abon[Abon::F_DUTY_MAX_WARN]);?>">
            </div>

            <label for="abon_duty_max_off" class="col-sm-3 col-form-label">Отключение (дней)</label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="abon_duty_max_off" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_MAX_OFF;?>]" value="<?= h($abon[Abon::F_DUTY_MAX_OFF]);?>">
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label" for="abon_duty_auto_off">Автоотключение</label>
            <div class="col-sm-3">
                <input type="checkbox" class="form-check-input" id="abon_duty_auto_off" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_AUTO_OFF;?>]" value="1" <?= $abon[Abon::F_DUTY_AUTO_OFF] ? 'checked' : '';?>>
            </div>

            <label for="abon_duty_wait_days" class="col-sm-3 col-form-label">Ожидание (дней)</label>
            <div class="col-sm-3">
                <input type="number" class="form-control" id="abon_duty_wait_days" name="<?= Abon::POST_REC;?>[<?= Abon::F_DUTY_WAIT_DAYS;?>]" value="<?= h($abon[Abon::F_DUTY_WAIT_DAYS]);?>">
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6 text-center">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </form>
</div>