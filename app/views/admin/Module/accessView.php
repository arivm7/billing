<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : accessView.php
 *  Path    : app/views/admin/Module/accessView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of accessView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\base\Lang;
use config\tables\Module;

?>
<div class="container">
    <h2><?=__("Редактирование описания модуля и прав доступа к модулю.");?></h2>

    <div class="accordion" id="accordionFlushExample">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                    Лог
                </button>
            </h2>
            <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                <div class="accordion-body"><?= str_replace("\n", "<br>", rtrim($alert, "\n")) ?></div>
            </div>
        </div>
    </div>

    <div class="alert alert-info text-center" role="alert">
        <h3><span>Модуль:</span><span class="text-secondary">&nbsp;<?= $module[Lang::code().Module::_TITLE] ?>&nbsp;</span></h3>
    </div>

    <?= get_html_table(
            t: [$module],
            cell_attributes: ["id", "uk_title", "ru_title", "en_title", "uk_description", "ru_description", "en_description", "route", "api", "hidden", "hidden", "hidden", "hidden"]
        ); ?>

    <?php /* include DIR_INC . '/module_form.php'; */ ?>

    <br>
    <hr>
    <br>

    <div class="alert alert-info text-center" role="alert">
        <h3>Права доступа групп к модулю <span class="text-secondary"><?= $module[Lang::code().Module::_TITLE] ?></span></h3>
    </div>

    <form class="row g-12" action="" method="post">
        <?= get_html_table(
                t: $roles,
                //                 "id", "uk_title", "ru_title", "en_title", "uk_description", "ru_description", "en_description", "creation_uid", "creation_date", "modified_uid", "modified_date", "access_value", "access_rec"
                cell_attributes:  ["id",
                    (Lang::code() == 'uk' ? "" : "hidden"), // uk_title
                    (Lang::code() == 'ru' ? "" : "hidden"), // ru_title
                    (Lang::code() == 'en' ? "" : "hidden"), // en_title
                    (Lang::code() == 'uk' ? "" : "hidden"), // uk_description
                    (Lang::code() == 'ru' ? "" : "hidden"), // ru_description
                    (Lang::code() == 'en' ? "" : "hidden"), // en_description
                    "hidden", "hidden", "hidden", "hidden", "hidden", "access_rec"],
        ); ?>
        <div class="col-12 text-md-end">
            <button type="submit" class="btn btn-primary">Исправить права доступа</button>
            <a href="<?= Module::URI_LIST;?>" class="btn btn-secondary" >К списку</a>
        </div>
    </form>
</div>

