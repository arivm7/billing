<?php
/*
 *  Project : my.ri.net.ua
 *  File    : uploadView.php
 *  Path    : app/views/Files/uploadView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:25:06
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of uploadView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use config\SessionFields;
use config\tables\File;
use config\tables\User;
require_once DIR_LIBS . '/functions.php';

if (isset($_SESSION[SessionFields::FORM_DATA])) {
    $file = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
}

if (!isset($file)) {
    $file = [];
}


$is_edit = isset($file[File::F_ID]);
$user_id = $_SESSION[User::SESSION_USER_REC][User::F_ID];
$is_public = (isset($file[File::F_IS_PUBLIC]) ? $file[File::F_IS_PUBLIC] : 1);
$title_def = (isset($file[File::F_SUB_TITLE]) ? $file[File::F_SUB_TITLE] : File::TITLE_DEFAULT);
$max_file_size = App::$app->get_config('files_upload_max_filesize');

?>
<div class="container py-4">
    <h2 class="mb-4"><?=  $is_edit ? "Редактирование параметров файла":"Отправка файла на сервер" ?></h2>
    <form action="" method="post" enctype="multipart/form-data" id="uploadForm">
        <input type="hidden" name="<?= File::POST_REC ?>[<?= File::F_USER_ID ?>]" value="<?= $user_id ?>">
        <div class="row mb-3">
            <div class="col-9">
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="<?= File::POST_REC ?>[<?= File::F_ID ?>]" value="<?= $file[File::F_ID] ?>">
                    <div class="input-group">
                        <label class="input-group-text" for="original_name" ><?= __('Новое имя файла'); ?></label>
                        <input type="text" id="original_name"
                               class="form-control"
                               name="<?= File::POST_REC ?>[<?= File::F_ORIGINAL_NAME ?>]"
                               value="<?= (isset($file[File::F_ORIGINAL_NAME]) ? $file[File::F_ORIGINAL_NAME] : ""); ?>" required>
                    </div>
                <?php else : ?>
                    <!-- Поле MAX_FILE_SIZE требуется указывать перед полем загрузки файла -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?= $max_file_size; ?>" />
                    <label for="file" class="form-label">Выберите файл <span class="text-secondary">(не больше <?= round($max_file_size/1024/1024, 2); ?> Мб)</span></label>
                    <input type="file" class="form-control" id="file" name="<?= File::F_ORIGINAL_NAME ?>" required>
                <?php endif; ?>
            </div>
            <div class="col-3 mb-3 d-flex align-items-end">
              <label class="form-check-label me-3" for="checkChecked">Readonly</label>
              <input class="form-check-input me-3" type="checkbox"
                     name="<?= File::POST_REC ?>[<?= File::F_READONLY ?>]"
                     value="1"
                     id="checkChecked"
                     <?= (!empty($file[File::F_READONLY]) ? "checked" : ""); ?>>
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label" for="is_public">Публичный | Пользовательский</label>
                <select class="form-select" name="<?= File::POST_REC ?>[<?= File::F_IS_PUBLIC ?>]" id="is_public">
                    <option value="1" <?=( $is_public ? " selected" : "");?>>Публичный :: Прямой URL для всех</option>
                    <option value="0" <?=(!$is_public ? " selected" : "");?>>Пользовательский :: Через контроллер</option>
                </select>
            </div>

            <div class="col mb-3">
                <label class="form-label" for="sub_title">Группа | Публичная подпапка, </label>
                <select class="form-select" name="<?= File::POST_REC ?>[<?= File::F_SUB_TITLE ?>]" id="sub_title">
                    <?php foreach (File::SUB_DIRS as $title => $sub_dir) : ?>
                        <option value="<?= $title; ?>" <?= ($title == $title_def ? " selected" : ""); ?>><?= $title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php foreach (File::SUPPORTED_LANGS as $code) : ?>
            <div class="mb-3">
                <label class="form-label" for="description_<?=$code;?>" >(<?=strtoupper($code);?>) :: <?=__('Описание файла');?></label>
                <input type="text" id="description_<?=$code;?>"
                       class="form-control"
                       name="<?= File::POST_REC ?>[<?= File::F_DESCRIPTION[$code] ?>]"
                       value="<?=(isset($file[File::F_DESCRIPTION[$code]]) ? $file[File::F_DESCRIPTION[$code]] : "");?>">
            </div>
        <?php endforeach; ?>
        <div  class="d-flex justify-content-between align-items-center" >
            <button type="submit" class="btn btn-primary">Отправить</button>
            <a href="<?= File::URI_INDEX;?>" title="Вернуться к полному списку файлов">Назад к списку файлов</a>
        </div>
    </form>
</div>
<script>
document.getElementById('uploadForm').addEventListener('submit', function (e) {
    const fileInput = document.getElementById('file');
    const file = fileInput.files[0]; // первый выбранный файл

    if (file) {
        const maxSize = <?= $max_file_size; ?>;

        if (file.size > maxSize) {
            e.preventDefault(); // отменяем отправку формы
            alert("Файл слишком большой! Максимальный размер — <?= round($max_file_size/1024/1024, 2); ?> МБ.");
        }
    }
});
</script>