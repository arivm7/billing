<?php
use config\MimeTypes;
use config\tables\File;
use config\Icons;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
$heigth = '48px';
$width  = '48px';
/** @var array $file */
if (MimeTypes::MIME_TYPES[$file[File::F_MIME]][MimeTypes::F_BROWSABLE]) {
    $img = File::get_img_tag($file, height: $heigth);
} else {
    $img = MimeTypes::MIME_TYPES[$file[File::F_MIME]][MimeTypes::F_ICON];
}
?>
<div class="d-flex justify-content-between align-items-center">
    <div class="flex-grow-1">
        <div class="row">
            <div class="col-1">
                <a href="<?= File::get_src($file); ?>" target="_blank"><?= $img ?></a>
            </div>
            <div class="col-11">
                <a href="<?= File::get_src($file); ?>" target="_blank" title="<?=File::get_title($file);?>" >
                    <?= h(File::get_description($file)) ?>
                </a>
                <br><span class="text text-secondary"><?= h($file[File::F_ORIGINAL_NAME]) ?></span>
            </div>
        </div>
    </div>
    <div class="d-flex flex-nowrap">
        <div class="row align-items-center">
            <div class="col p-1">
                <a href="<?= File::URI_UPLOAD . '?' . File::F_GET_ID . '=' . $file[File::F_ID] ?>"
                   class="btn btn-primary btn-sm p-1"
                   title="<?= __('Править описание файла'); ?>">
                    <img src="<?= Icons::SRC_ICON_EDIT; ?>" alt="[E]" height="30rem">
                </a>
            </div>
            <div class="col p-1">
                <button class="btn btn-primary btn-sm p-1 copy-btn" data-text='<?= json_encode(File::get_src($file)); ?>'>
                    <img src="<?= Icons::SRC_ICON_CLIPBOARD; ?>" title="<?= __('Скопировать путь файла в clipboard') ?>" alt="[copy]" height="30rem">
                </button>
            </div>
            <div class="col p-1">
                <a href="<?= File::URI_DEL . '?' . File::F_GET_ID . '=' . $file[File::F_ID] ?>"
                   class="btn btn-danger btn-sm p-1"
                   title="Удалиь файл с диска и из базы"
                   onclick="return confirm('<?= __('Удалить файл'); ?> «<?= h($file[File::F_ORIGINAL_NAME]) ?>»?');">
                    <img src="<?= Icons::SRC_ICON_TRASH; ?>" alt="[X]" height="30rem">
                </a>
            </div>
        </div>
    </div>
</div>

