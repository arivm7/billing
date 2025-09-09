<?php
use config\SessionFields;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var int $module */

if (isset($_SESSION[SessionFields::FORM_DATA])) {
    $module = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
}

?>


    <form class="row" action="" method="post">
        <div class="row g-3">
            <div class="col-md-2">
                <div class="input-group">
                    <label for="input_id" class="input-group-text w-25">ID</label>
                    <input type="text" class="form-control" id="input_id" name="<?=Module::POST_REC;?>[<?=Module::F_ID;?>]" value="<?=$module[Module::F_ID] ?? '';?>" readonly>
                </div>
            </div>
            <div class="col-10 text-center"><h3><?= (isset($module[Module::F_ID]) ? "Редактирование описания модуля" : "Создание нового модуля") ;?></h3></div>
        </div>

        <?php foreach (Module::SUPPORTED_LANGS as $lang) : ?>
            <div class="row g-3">
                <div class="col-md-2"></div>
                <div class="col-md-10">
                    <div class="input-group">
                        <label for="<?=$lang;?>_title" class="input-group-text">[<?=strtoupper($lang);?>] <?=__('Title');?></label>
                        <input id="<?=$lang;?>_title" type="text" class="form-control" name="<?=Module::POST_REC;?>[<?=Module::F_TITLE[$lang];?>]" value="<?=$module[Module::F_TITLE[$lang]] ?? '';?>" required>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach (Module::SUPPORTED_LANGS as $lang) : ?>
            <div class="row g-3">
                <div class="col-12">
                    <div class="input-group">
                        <label for="<?=$lang;?>_descr" class="input-group-text d-flex align-items-start">[<?=strtoupper($lang);?>] <?=__('Description');?></label>
                        <textarea id="<?=$lang;?>_descr" class="form-control" rows="2" name="<?=Module::POST_REC;?>[<?=Module::F_DESCRIPTION[$lang];?>]"><?= $module[Module::F_DESCRIPTION[$lang]] ?? '';?></textarea>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <br>
        <div class="row g-3">
            <div class="col-4">
                <div class="input-group">
                    <label for="input_route" class="input-group-text">route</label>
                    <input type="text" class="form-control" id="input_route" placeholder="контроллер/действие" name="<?=Module::POST_REC;?>[<?=Module::F_ROUTE;?>]" value="<?= $module[Module::F_ROUTE] ?? '';?>">
                </div>
            </div>
            <div class="col-4">
                <div class="input-group">
                    <label for="input_api" class="input-group-text">api</label>
                    <input type="text" class="form-control" id="input_api" placeholder="контроллер/действие?параметры" name="<?=Module::POST_REC;?>[<?=Module::F_API;?>]" value="<?= $module[Module::F_API] ?? '';?>">
                </div>
            </div>
            <div class="col-2">
                <button type="submit" class="btn btn-primary"><?= (isset($module[Module::F_ID]) ? __('Править запись') : __('Создать запись')) ;?></button>
            </div>
            <div class="col-2">
                <a href="<?= Module::URI_LIST;?>" class="btn btn-secondary w-100" >К списку</a>
            </div>
        </div>
    </form>



