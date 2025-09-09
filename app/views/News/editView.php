<?php
use config\SessionFields;
use config\tables\News;
use config\tables\User;
/** @var array $news */

if (isset($_SESSION[SessionFields::FORM_DATA]) && is_array($_SESSION[SessionFields::FORM_DATA]))
{
    $news = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
}

?>
<div class="container py-4">
    <h2 class="mb-4">Создание / редактирование новости</h2>
    <form action="" method="post">
        <?php if (isset($news[News::F_ID])) : ?>
            <input  type="hidden"
                    value="<?= $news[News::F_ID] ?>"
                    name="<?= News::POST_REC ?>[<?= News::F_ID ?>]">
        <?php endif; ?>
        <input  type="hidden"
                value="<?= $news[News::F_AUTHOR_ID] ?? $_SESSION[User::SESSION_USER_REC][User::F_ID] ?>"
                name="<?= News::POST_REC ?>[<?= News::F_AUTHOR_ID ?>]">
        <ul class="nav nav-tabs" id="newsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="common-tab" data-bs-toggle="tab" data-bs-target="#common" type="button" role="tab">Общая информация</button>
            </li>
            <?php foreach (News::SUPPORTED_LANGS as $lang): ?>
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
                          <input class="form-check-input" type="checkbox" value="1"
                            name="<?= News::POST_REC ?>[<?= News::F_AUTO_VISIBLE ?>]" id="auto_visible"
                            <?= $news[News::F_AUTO_VISIBLE] ? 'checked' : '' ?>>
                          <label class="form-check-label" for="auto_visible">Автоматически показывать с даты публикации</label>
                        </div>

                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1"
                            name="<?= News::POST_REC ?>[<?= News::F_IS_VISIBLE ?>]" id="is_visible"
                            <?= $news[News::F_IS_VISIBLE] ? 'checked' : '' ?>>
                          <label class="form-check-label" for="is_visible">Видима для всех</label>
                        </div>

                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1"
                            name="<?= News::POST_REC ?>[<?= News::F_AUTO_DEL ?>]" id="auto_del"
                            <?= !empty($news[News::F_AUTO_DEL]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="auto_del">Автоматически скрывать по дате окончания</label>
                        </div>

                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" value="1"
                            name="<?= News::POST_REC ?>[<?= News::F_IS_DELETED ?>]" id="is_deleted"
                            <?= !empty($news[News::F_IS_DELETED]) ? 'checked' : '' ?>>
                          <label class="form-check-label" for="is_deleted">Пометить как удалённую</label>
                        </div>
                    </div>
                    <div class="col-3 mb-3">
                        <label class="form-label">Дата публикации</label>
                        <input type="datetime-local"
                            name="<?= News::POST_REC ?>[<?= News::F_DATE_PUBLICATION_STR ?>]"
                            class="form-control"
                            value="<?= ($news[News::F_DATE_PUBLICATION] ? date(format: FORM_DATE_TIME, timestamp: $news[News::F_DATE_PUBLICATION]) : ''); ?>">
                        <br>
                        <label class="form-label">Дата окончания публикации</label>
                        <input type="datetime-local"
                            name="<?= News::POST_REC ?>[<?= News::F_DATE_EXPIRATION_STR ?>]"
                            class="form-control"
                            value="<?= ($news[News::F_DATE_EXPIRATION] ? date(format: FORM_DATE_TIME, timestamp: $news[News::F_DATE_EXPIRATION]) : ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Языковые вкладки -->
            <?php foreach (News::SUPPORTED_LANGS as $lang): ?>
            <div class="tab-pane fade" id="<?= $lang ?>" role="tabpanel">

                <div class="mb-3">
                  <label class="form-label"><?=__('Title');?> (<?= $lang ?>)</label>
                  <input type="text" class="form-control"
                    name="<?= News::POST_REC ?>[<?= News::F_TITLES[$lang] ?>]"
                    value="<?= h($news[News::F_TITLES[$lang]] ?? '') ?>">
                </div>

                <div class="mb-3">
                  <label class="form-label"><?=__('Description');?> (<?= $lang ?>)</label>
                  <textarea class="form-control editor-description" rows="3"
                    name="<?= News::POST_REC ?>[<?= News::F_DESCRIPTIONS[$lang] ?>]"><?= h($news[News::F_DESCRIPTIONS[$lang]] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label"><?=__('Текст новости');?> (<?= $lang ?>)</label>
                  <textarea class="form-control editor-text"
                    name="<?= News::POST_REC ?>[<?= News::F_TEXTS[$lang] ?>]"><?= htmlspecialchars($news[News::F_TEXTS[$lang]] ?? '') ?></textarea>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">💾<?=__('Сохранить');?></button>
          <a href="/news" class="btn btn-secondary"><?=__('Отмена');?></a>
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


