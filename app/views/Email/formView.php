<?php
/**
 *  Project : my.ri.net.ua
 *  File    : formView.php
 *  Path    : app/views/Mail/formView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 04 Mar 2026 00:11:20
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use config\Email;

/**
 * Форма отправки одиночного письма по электронной почте
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Данные из контроллера
 * 
 * @var string $title
 * @var string $to
 * @var string $subject
 * @var string $body_text
 * @var string $body_html
 * @var string $attach_path
 * @var string $attach_name
 * @var string $register        -- Регистрация отправленного письма в базе
 * @var int    $abon_id         -- id абонента для которого регистрировать письмо
 * 
 */

if (empty($abon_id)) { $register = 0; }

// debug($_GET, '_GET');
// debug($_POST, '_POST');
// debug([
//     'to' => $to, 
//     'subject'=>$subject, 
//     'body_text'=>$body_text, 
//     'body_html'=>$body_html, 
//     'register'=>$register, 
//     'abon_id'=>$abon_id,
//     'attach_path'=>$attach_path, 
//     'attach_name'=>$attach_name,
//     ], '$to, $subject, $body_text, $body_html, $attach_path, $attach_name');

?>

<div class="container mt-1">
    <h2 class="mb-4"><?= $title ?></h2>
    <form method="post" action="">
        <!-- abon_id -->
        <input type="hidden" name="<?= Email::REC ?>[<?= Email::F_REGISTER_ABON_ID ?>]" value="<?= h($abon_id) ?>">

        <!-- TO -->
        <div class="mb-3">
            <label for="to" class="form-label hover-pointer"><?= __('Кому') ?></label>
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-10 col-lg-9">
                    <input type="email" 
                        class="form-control" 
                        id="to" 
                        name="<?= Email::REC ?>[<?= Email::F_TO ?>]" 
                        value="<?= h($to) ?>"
                        placeholder="<?= __('Можно указать несколько адресов через запятую') ?>" 
                        required>
                </div>
                <!-- Флаг Регистрировать -->
                <div class="col-12 col-md-2 col-lg-3">
                    <div class="form-check ms-2">
                        <input class="form-check-input" 
                            type="checkbox" 
                            name="<?= Email::REC ?>[<?= Email::F_REGISTER ?>]" 
                            id="register_notify"
                            value="1"
                            <?= (empty($abon_id) ? "disabled" : "") ?>
                            <?= $register ? 'checked' : '' ?>>
                        <label class="form-check-label hover-pointer" 
                            for="register_notify" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top"
                            title="<?= __('Регистрировать письмо в базе уведомлений') ?>">
                            <?= __('Регистрировать') ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-text">.</div>
        </div>

        <!-- SUBJECT -->
        <div class="mb-3">
            <label for="subject" class="form-label hover-pointer"><?= __('Тема') ?></label>
            <input type="text" class="form-control" id="subject" name="<?= Email::REC ?>[<?= Email::F_SUBJECT ?>]" value="<?= $subject ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><?= __('Текст письма') ?></label>

            <!-- вкладки -->
            <ul class="nav nav-tabs" id="mailBodyTabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                            id="html-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#html-body"
                            type="button"
                            role="tab">
                        <?= __('HTML-версия') ?> <?= (empty($body_html) ? "" : "&nbsp;<i class='bi bi-circle-fill text-info small' title='Есть текст'></i>") ?>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            id="text-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#text-body"
                            type="button"
                            role="tab">
                        <?= __('Текстовая версия') ?> <?= (empty($body_text) ? "" : "&nbsp;<i class='bi bi-circle-fill text-info small' title='Есть текст'></i>") ?>
                    </button>
                </li>

            </ul>

            <div class="tab-content border border-top-0 p-3">

                <!-- HTML -->
                <div class="tab-pane fade show active"
                    id="html-body"
                    role="tabpanel">
                    <textarea
                        class="form-control editor-text"
                        rows="<?= get_count_rows_for_textarea(html_to_text($body_html)); ?>"
                        name="<?= Email::REC ?>[<?= Email::F_BODY_HTML ?>]"><?= cleaner_html($body_html) ?></textarea>
                </div>

                <!-- TEXT -->
                <div class="tab-pane fade"
                    id="text-body"
                    role="tabpanel">
                    <textarea
                        class="form-control"
                        rows="<?= get_count_rows_for_textarea($body_text); ?>"
                        name="<?= Email::REC ?>[<?= Email::F_BODY_TEXT ?>]"><?= h($body_text) ?></textarea>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary px-3" name="<?= Email::REC ?>[<?= Email::F_DO_SEND ?>]" value="1" ><?= __('Отправить') ?></button>
    </form>
</div>

<!-- TinyMCE (для текстов документов) -->
<script src="/public/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
    tinymce.init({
        selector: 'textarea.editor-text',
        height: 400, // текст — большой редактор
        width: '100%',
        resize: true,
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            'image'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table image | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
    //  toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table image | align            |           numlist bullist                | emoticons charmap | removeformat',
        license_key: 'gpl' // gpl for open source, T8LK:... for commercial
    });
</script>
