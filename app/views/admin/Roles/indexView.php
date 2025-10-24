<?php
/*
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/admin/Roles/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Role;
use billing\core\App;
use billing\core\base\Lang;

/** @var array $table */
?>
<div class="container">
    <h1 class="display-6 ali text-center pb-3"><?=__('Список административных ролей');?></h1>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <table class="table table-hover">
    <?php foreach ($table as $row) : ?>
        <tr>
            <td>
                <div class="container-fluid">
                    <ul class="nav nav-tabs justify-content-end" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                          <a class="nav-link py-1 px-2 active" id="tab1-tab" data-bs-toggle="tab" href="#tab_view_<?=$row[Role::F_ID];?>" role="tab"><small>Смотреть</small></a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link py-1 px-2" id="tab2-tab" data-bs-toggle="tab" href="#tab_edit_<?=$row[Role::F_ID];?>" role="tab"><small>Редактировать</small></a>
                      </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="tab_view_<?=$row[Role::F_ID];?>" role="tabpanel">
                            <table>
                                <tr>
                                    <td class="small align-top text-secondary p-2" title="<?=__('ID административной роли');?>" ><?=$row[Role::F_ID];?>.</td>
                                    <td class="small align-top text-secondary p-2"><?= strtoupper(Lang::code());?>:</td>
                                    <td class="align-top text-nowrap p-2"><?=$row[Role::F_TITLE[Lang::code()]];?></td>
                                    <td class="small align-top text-secondary p-2"><?= strtoupper(Lang::code());?>:</td>
                                    <td class="align-top p-2"><?=$row[Role::F_DESCRIPTION[Lang::code()]];?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="tab_edit_<?=$row[Role::F_ID];?>" role="tabpanel">

                            <form action="" method="post">
                                <input type="hidden" name="<?=Role::POST_REC;?>[<?=Role::F_ID;?>]" value="<?=(int)$row[Role::F_ID];?>">

                                <?php foreach (Role::SUPPORTED_LANGS as $lang) : ?>
                                    <div class="mb-3">
                                        <label for="<?=$lang;?>_title" class="form-label">[<?= strtoupper($lang);?>] <?=__('Название');?>:</label>
                                        <input type="text" class="form-control" id="<?=$lang;?>_title" name="<?=Role::POST_REC;?>[<?=Role::F_TITLE[$lang];?>]"
                                               value="<?= h($row[Role::F_TITLE[$lang]] ?? ''); ?>" required>
                                    </div>
                                <?php endforeach; ?>

                                <?php foreach (Role::SUPPORTED_LANGS as $lang) : ?>
                                    <div class="mb-3">
                                        <label for="<?=$lang;?>_description" class="form-label">[<?= strtoupper($lang);?>] <?=__('Описание');?>:</label>
                                        <textarea class="form-control" id="<?=$lang;?>_description" name="<?=Role::POST_REC;?>[<?=Role::F_DESCRIPTION[$lang];?>]"
                                                  rows="2"><?= h($row[Role::F_DESCRIPTION[$lang]] ?? ''); ?></textarea>
                                    </div>
                                <?php endforeach; ?>

                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            </form>

                        </div>
                    </div>
                </div>
            </td>
            <td class="small text-center align-bottom">
                <a href="/admin/roles/clone?id=<?=$row[Role::F_ID];?>" title="Клонирование этой записи" target="_blank"><img src="/public/img/clone.svg" height="22" alt="[CLONE]"/></a><br>
                <a href="/admin/roles/delete?id=<?=$row[Role::F_ID];?>" title="Удаление этой записи" target="_blank">[x]</a>
            </td>
            <td class="align-bottom">
                <table class="small text-secondary">
                    <tr>
                        <td><?=($row[Role::F_CREATION_DATE] ? date("d.m.Y H:i:s", $row[Role::F_CREATION_DATE]) : "-");?></td>
                        <td><?= $row[Role::F_CREATION_UID];?></td>
                    </tr>
                    <tr>
                        <td><?=($row[Role::F_MODIFIED_DATE] ? date("d.m.Y H:i:s", $row[Role::F_MODIFIED_DATE]) : "-");?></td>
                        <td><?= $row[Role::F_MODIFIED_UID];?></td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <hr>
</div>