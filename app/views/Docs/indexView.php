<?php
/**
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Docs/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 * @copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Список документов (карточки)
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\Icons;
use config\tables\Docs;
use billing\core\base\Lang;
use config\tables\Module;

// текущий язык
$lang = Lang::code();

// соответствие полей для выбранного языка
$titleField       = Docs::F_TITLES[$lang];
$descriptionField = Docs::F_DESCRIPTIONS[$lang];
$textField        = Docs::F_TEXTS[$lang];
?>
<div class="container my-4">
    <div class='d-flex justify-content-between align-items-center'>
        <h2 class="display-6 mb-4"><?=__('Rilan') . ' :: '. __('Documents');?></h2>
        <?php if (can_add(module: Module::MOD_DOCS)) : ?>
        <a href="/docs/edit" class="btn btn-secondary"><?=__('Create a new document');?></a>
        <?php endif; ?>
    </div>
    <div class="row">
        <?php include DIR_INC . '/pager.php'; ?>
        <?php foreach ($docs as $docs_one): ?>
            <!--
            col-md-6 — колонка шириной 6/12 (половина) на средних экранах (≥768px).
            col-lg-4 — колонка шириной 4/12 (треть) на больших экранах (≥992px).
            mb-4 — margin-bottom: 1.5rem (отступ снизу).
            Используется в сетке Bootstrap: на телефонах блок будет занимать всю ширину, на планшете — половину, на десктопе — треть.
            -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-lg">
                    <!-- Заголовок -->
                    <h5 class="card-header"> <!--"card-title"-->
                        <?= cleaner_html($docs_one[$titleField]) ?>
                    </h5>
                    <div class="card-body d-flex flex-column">


                        <!-- Краткое описание -->
                        <?php if (!empty($docs_one[$descriptionField])): ?>
                            <p class="card-text mb-3">
                                <?= nl2br(cleaner_html($docs_one[$descriptionField])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                        <div class="card-footer">

                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Кнопка "Подробнее" -->
                                <div class="mt-auto">
                                    <a href="<?= Docs::URI_VIEW ?>/<?= (int) $docs_one[Docs::F_ID] ?>"
                                       class="btn btn-primary btn-sm"
                                       title="<?= __('Read the document') ?>">
                                        <?= __('Read') ?>
                                    </a>
                                    <?php if (can_edit(module: Module::MOD_DOCS)) : ?>
                                    <a href="<?= Docs::URI_EDIT ?>/<?= (int) $docs_one[Docs::F_ID] ?>"
                                       class="btn btn-primary btn-sm" title="<?= __('Edit a document') ?>"><img src="<?= Icons::SRC_EDIT;?>" height="<?= Icons::ICON_SIZE;?>" ></a>
                                    <?php endif; ?>
                                </div>
                                <div>
                                <!-- Дата публикации -->
                                <?php if (!empty($docs_one[Docs::F_DATE_PUBLICATION])): ?>
                                    <p class="text-muted text-end small mb-2">
                                        <i class="bi bi-calendar"></i>
                                        <?= date("d.m.Y H:i", $docs_one[Docs::F_DATE_PUBLICATION]) ?>
                                    </p>
                                <?php endif; ?>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php include DIR_INC . '/pager.php'; ?>
    </div>
</div>