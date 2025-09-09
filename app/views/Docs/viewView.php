<?php

use config\tables\Docs;
use config\tables\Module;

use billing\core\base\Lang;

/**
 * @var array $docs Ассоц.массив одной новости из таблицы `docs`
 *                  ключи соответствуют полям таблицы (см. Docs::*).
 * Требуется: Lang::code() -> 'ru' | 'uk' | 'en'
 */
// Текущий язык интерфейса
$lang = Lang::code();

// Универсальный пикер значения по языку с фоллбэком
$pickByLang = function (array $map, array $row, array $order): string {
    foreach ($order as $code) {
        $field = $map[$code] ?? null;
        if ($field && !empty($row[$field])) {
            return (string) $row[$field];
        }
    }
    return '';
};

// Порядок фоллбэка: текущий язык -> остальные поддерживаемые
$fallbackOrder = array_values(array_unique(array_merge([$lang], Docs::SUPPORTED_LANGS)));

// Текстовые поля по текущему языку (с фоллбэком)
$title = $pickByLang(Docs::F_TITLES, $doc, $fallbackOrder);
$desc = $pickByLang(Docs::F_DESCRIPTIONS, $doc, $fallbackOrder);
$text = $pickByLang(Docs::F_TEXTS, $doc, $fallbackOrder);

// Служебные флаги и даты
$pubTs = $doc[Docs::F_DATE_PUBLICATION] ?? null;
$creTs = $doc[Docs::F_DATE_CREATION] ?? null;
$modTs = $doc[Docs::F_MODIFIED_DATE] ?? null;
$expTs = $doc[Docs::F_DATE_EXPIRATION] ?? null;

$isVisible = (int) ($doc[Docs::F_IS_VISIBLE] ?? 0);
$autoVisible = (int) ($doc[Docs::F_AUTO_VISIBLE] ?? 0);
$isDeleted = (int) ($doc[Docs::F_IS_DELETED] ?? 0);

// Если тело новости хранится с доверенным HTML (из админки и уже очищено),
// можно вывести без экранирования. Иначе — экранировать.
$renderTrustedHtml = true;
?>

<div class="container my-4">
    <div class='d-flex justify-content-between align-items-center'>
        <div>
            <?php if ($doc[Docs::F_IN_VIEW_TITLE]) : ?>
                <h1 class="h3 mb-0"><?= cleaner_html($title ?: 'Без заголовка') ?></h1>
            <?php endif; ?>
        </div>
        <div>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($isDeleted): ?>
                    <span class="badge bg-danger"><?= __('Удалено'); ?></span>
                <?php endif; ?>

                <?php if ($isVisible): ?>
                    <span class="badge bg-success"><?= __('Опубликовано'); ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary"><?= __('Скрыто'); ?></span>
                <?php endif; ?>

                <?php if ($autoVisible): ?>
                    <span class="badge bg-info text-dark"><?= __('Автопубликация'); ?></span>
                <?php endif; ?>

                <?php if ($expTs): ?>
                    <span class="badge bg-warning text-dark"><?= __('Действует до'); ?> <?= date('d.m.Y H:i', (int) $expTs) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($doc[Docs::F_IN_VIEW_DESCRIPTION] && $desc): ?>
        <p class="lead"><?= cleaner_html($desc) ?></p>   <!-- nl2br(); -->
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if ($doc[Docs::F_IN_VIEW_TEXT]): ?>
                <?= cleaner_html($text) ?>  <!-- nl2br(); -->
            <?php endif; ?>
        </div>
    </div>

    <div class='d-flex justify-content-between align-items-center'>
        <div>
            <?php if (can_edit(Module::MOD_DOCS)) : ?>
            <div class="mt-3 d-flex gap-2">
                <a
                    href="<?= Docs::URI_EDIT ?>?<?= Docs::F_GET_ID ?>=<?= (int) $doc[Docs::F_ID] ?>"
                    class="btn btn-primary">✏️ <?= __('Редактировать'); ?>
                </a>
                <a href="<?= Docs::URI_LIST ?>" class="btn btn-outline-secondary">← <?= __('К списку'); ?></a>
            </div>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($pubTs || $creTs || $modTs): ?>
                <p class="text-muted small text-end">
                    <?php if ($pubTs): ?>
                        <?= __('Опубликовано'); ?>: <?= date('d.m.Y H:i', (int) $pubTs) ?>
                    <?php else: ?>
                        <?= __('Создано'); ?>: <?= $creTs ? date('d.m.Y H:i', (int) $creTs) : '-' ?>
                    <?php endif; ?>
                    <?php if ($modTs): ?><br><?= __('Обновлено'); ?>: <?= date('d.m.Y H:i', (int) $modTs) ?><?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
