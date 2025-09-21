<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : editView.php
 *  Path    : app/views/Docs/editView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of editView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\SessionFields;
use config\tables\Docs;
use config\tables\User;
/** @var array $doc */

if (empty($doc)) {
    $doc = Docs::POST_FIELDS;
}

if (isset($_SESSION[SessionFields::FORM_DATA]) && is_array($_SESSION[SessionFields::FORM_DATA]))
{
    $doc = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
}
$doc[Docs::F_DATE_EXPIRATION_STR] = (!empty($doc[Docs::F_DATE_EXPIRATION]) ? date(format: FORM_DATE_TIME, timestamp: $doc[Docs::F_DATE_EXPIRATION]) : '');
$doc[Docs::F_DATE_PUBLICATION_STR] = (!empty($doc[Docs::F_DATE_PUBLICATION]) ? date(format: FORM_DATE_TIME, timestamp: $doc[Docs::F_DATE_PUBLICATION]) : '');

?>
<div class="container py-4">
    <h2 class="mb-4"><?=(isset($doc[Docs::F_ID]) ? __('Редактирование') : __('Создание')) . ' ' . __('документа');?></h2>
    <form action="" method="post">
        <?php if (isset($doc[Docs::F_ID])) : ?>
            <input  type="hidden"
                    value="<?= $doc[Docs::F_ID] ?>"
                    name="<?= Docs::POST_REC ?>[<?= Docs::F_ID ?>]">
        <?php endif; ?>
        <input  type="hidden"
                value="<?= $doc[Docs::F_AUTHOR_ID] ?? $_SESSION[User::SESSION_USER_REC][User::F_ID] ?>"
                name="<?= Docs::POST_REC ?>[<?= Docs::F_AUTHOR_ID ?>]">
        <ul class="nav nav-tabs" id="docsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="common-tab" data-bs-toggle="tab" data-bs-target="#common" type="button" role="tab"><?=__('Общая информация');?></button>
            </li>
            <?php foreach (Docs::SUPPORTED_LANGS as $lang): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-<?=$lang;?>" data-bs-toggle="tab" data-bs-target="#<?=$lang;?>" type="button" role="tab">
                        <?= strtoupper($lang) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content border border-top-0 p-3">
            <!-- Общая информация -->
            <div class="tab-pane fade show active" id="common" role="tabpanel">
                <div class="row">
                    <div class="col mb-3">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1" id="auto_visible"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_AUTO_VISIBLE; ?>]"
                            <?= !empty($doc[Docs::F_AUTO_VISIBLE]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="auto_visible"><?=__('Автоматически показывать с даты публикации');?></label>
                        </div>

                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1" id="is_visible"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_IS_VISIBLE; ?>]"
                            <?= !empty($doc[Docs::F_IS_VISIBLE]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="is_visible"><?=__('Видима для всех');?></label>
                        </div>

                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1" id="auto_del"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_AUTO_DEL; ?>]"
                            <?= !empty($doc[Docs::F_AUTO_DEL]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="auto_del"><?=__('Автоматически скрывать по дате окончания');?></label>
                        </div>

                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" value="1" id="is_deleted"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_IS_DELETED; ?>]"
                            <?= !empty($doc[Docs::F_IS_DELETED]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="is_deleted"><?=__('Пометить как удалённую');?></label>
                        </div>

                        <!-- Параметры отображение при просмотре -->
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1" id="in_view_title"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_IN_VIEW_TITLE; ?>]"
                            <?= !empty($doc[Docs::F_IN_VIEW_TITLE]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="in_view_title"><?=__('Отображать _title при просмотре');?></label>
                        </div>

                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1" id="in_view_description"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_IN_VIEW_DESCRIPTION; ?>]"
                            <?= !empty($doc[Docs::F_IN_VIEW_DESCRIPTION]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="in_view_description"><?=__('Отображать _description при просмотре');?></label>
                        </div>

                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" value="1" id="in_view_text"
                            name="<?= Docs::POST_REC; ?>[<?= Docs::F_IN_VIEW_TEXT; ?>]"
                            <?= !empty($doc[Docs::F_IN_VIEW_TEXT]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="in_view_text"><?=__('Отображать _text при просмотре');?></label>
                        </div>

                    </div>
                    <div class="col-3 mb-3">
                        <label class="form-label"><?=__('Дата публикации');?></label>
                        <input type="datetime-local"
                            name="<?= Docs::POST_REC ?>[<?= Docs::F_DATE_PUBLICATION_STR ?>]"
                            class="form-control"
                            value="<?=$doc[Docs::F_DATE_PUBLICATION_STR];?>">
                        <br>
                        <label class="form-label"><?=__('Дата окончания публикации');?></label>
                        <input type="datetime-local"
                            name="<?= Docs::POST_REC ?>[<?= Docs::F_DATE_EXPIRATION_STR ?>]"
                            class="form-control"
                            value="<?= $doc[Docs::F_DATE_EXPIRATION_STR]; ?>">
                    </div>
                </div>
            </div>

            <!-- Языковые вкладки -->
            <?php foreach (Docs::SUPPORTED_LANGS as $lang): ?>
            <div class="tab-pane fade" id="<?= $lang ?>" role="tabpanel">

                <div class="mb-3">
                    <label class="form-label"><?=__('Title');?> (<?= $lang ?>)</label>
                    <input type="text" class="form-control"
                        name="<?= Docs::POST_REC ?>[<?= Docs::F_TITLES[$lang] ?>]"
                        value="<?= cleaner_html($doc[Docs::F_TITLES[$lang]] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?=__('Description');?> (<?= $lang ?>)</label>
                    <textarea class="form-control editor-description" rows="3"
                        name="<?= Docs::POST_REC ?>[<?= Docs::F_DESCRIPTIONS[$lang] ?>]"><?= cleaner_html($doc[Docs::F_DESCRIPTIONS[$lang]] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?=__('Текст новости');?> (<?= $lang ?>)</label>
                    <textarea class="form-control editor-text"
                        name="<?= Docs::POST_REC ?>[<?= Docs::F_TEXTS[$lang] ?>]"><?= cleaner_html($doc[Docs::F_TEXTS[$lang]] ?? '') ?></textarea>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">💾<?=__('Сохранить');?></button>
            <?php if (isset($doc[Docs::F_ID])) : ?>
            <a href="<?=Docs::URI_VIEW . '?' . Docs::F_GET_ID . '=' . $doc[Docs::F_ID];?>" class="btn btn-secondary"><?=__('Смотреть');?></a>
            <a href="<?=Docs::URI_DEL . '?' . Docs::F_GET_ID . '=' . $doc[Docs::F_ID];?>" class="btn btn-secondary" title="<?=__('ВНИМАНИЕ: Удаление из базы.');?>" onclick="return confirm('Удалить запись?');"  ><?=__('Удалить');?></a>
            <?php endif; ?>
            <a href="<?=Docs::URI_LIST;?>" class="btn btn-secondary"><?=__('Вернутся к списку');?></a>
        </div>

    </form>
</div>
<!-- TinyMCE (для текстов новостей) -->
<script src="/public/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
    tinymce.init({
        selector: 'textarea.editor-description',
        height: 200, // описание — маленький редактор
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            'image'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags image | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        license_key: 'gpl' // gpl for open source, T8LK:... for commercial
    });

    tinymce.init({
        selector: 'textarea.editor-text',
        height: 600, // текст — большой редактор
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            'image'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags image | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        license_key: 'gpl' // gpl for open source, T8LK:... for commercial
    });
</script>

