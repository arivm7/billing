<?php
use config\tables\News;
use billing\core\base\Lang;

/**
 * @var array $news Ассоц.массив одной новости из таблицы `news`
 *                  ключи соответствуют полям таблицы (см. News::*).
 * Требуется: Lang::code() -> 'ru' | 'uk' | 'en'
 */

// Текущий язык интерфейса
$lang = Lang::code();

// Универсальный пикер значения по языку с фоллбэком
$pickByLang = function(array $map, array $row, array $order): string {
    foreach ($order as $code) {
        $field = $map[$code] ?? null;
        if ($field && !empty($row[$field])) {
            return (string)$row[$field];
        }
    }
    return '';
};

// Порядок фоллбэка: текущий язык -> остальные поддерживаемые
$fallbackOrder = array_values(array_unique(array_merge([$lang], News::SUPPORTED_LANGS)));

// Текстовые поля по текущему языку (с фоллбэком)
$title = $pickByLang(News::F_TITLES,       $news, $fallbackOrder);
$desc  = $pickByLang(News::F_DESCRIPTIONS, $news, $fallbackOrder);
$text  = $pickByLang(News::F_TEXTS,        $news, $fallbackOrder);

// Служебные флаги и даты
$pubTs   = $news[News::F_DATE_PUBLICATION] ?? null;
$creTs   = $news[News::F_DATE_CREATION]    ?? null;
$modTs   = $news[News::F_MODIFIED_DATE]    ?? null;
$expTs   = $news[News::F_DATE_EXPIRATION]  ?? null;

$isVisible    = (int)($news[News::F_IS_VISIBLE]   ?? 0);
$autoVisible  = (int)($news[News::F_AUTO_VISIBLE] ?? 0);
$isDeleted    = (int)($news[News::F_IS_DELETED]   ?? 0);

// Если тело новости хранится с доверенным HTML (из админки и уже очищено),
// можно вывести без экранирования. Иначе — экранировать.
$renderTrustedHtml = true;
?>

<div class="container my-4">

  <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
      <h1 class="h3 mb-0"><?= cleaner_html($title ?: 'Без заголовка') ?></h1>

    <div class="d-flex flex-wrap gap-2">
      <?php if ($isDeleted): ?>
        <span class="badge bg-danger">Удалено</span>
      <?php endif; ?>

      <?php if ($isVisible): ?>
        <span class="badge bg-success">Опубликовано</span>
      <?php else: ?>
        <span class="badge bg-secondary">Скрыто</span>
      <?php endif; ?>

      <?php if ($autoVisible): ?>
        <span class="badge bg-info text-dark">Автопубликация</span>
      <?php endif; ?>

      <?php if ($expTs): ?>
        <span class="badge bg-warning text-dark">Действует до <?= date('d.m.Y H:i', (int)$expTs) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($pubTs || $creTs || $modTs): ?>
    <p class="text-muted small">
      <?php if ($pubTs): ?>
        Опубликовано: <?= date('d.m.Y H:i', (int)$pubTs) ?>
      <?php else: ?>
        Создано: <?= $creTs ? date('d.m.Y H:i', (int)$creTs) : '-' ?>
      <?php endif; ?>
      <?php if ($modTs): ?> • Обновлено: <?= date('d.m.Y H:i', (int)$modTs) ?><?php endif; ?>
    </p>
  <?php endif; ?>

  <?php if ($desc): ?>
    <p class="lead"><?= nl2br(cleaner_html($desc)) ?></p>
  <?php endif; ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <?php if ($renderTrustedHtml): ?>
        <?= $text /* доверенный HTML */ ?>
      <?php else: ?>
        <?= nl2br(cleaner_html($text)) ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <a
      href="/news/edit?<?= News::F_GET_ID ?>=<?= (int)$news[News::F_ID] ?>"
      class="btn btn-primary">
      ✏️ Редактировать
    </a>
    <a href="/news" class="btn btn-outline-secondary">← К списку</a>
  </div>

</div>
