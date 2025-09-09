<?php
use config\tables\News;
use billing\core\base\Lang;

// текущий язык
$lang = Lang::code();

// соответствие полей для выбранного языка
$titleField       = News::F_TITLES[$lang];
$descriptionField = News::F_DESCRIPTIONS[$lang];
$textField        = News::F_TEXTS[$lang];
?>
<div class="container my-4">
    <h2 class="display-6 text-start small mb-4"><?=__('Rilan') . ' :: '. __('Новости');?></h2>
    <div class="row">
        <?php include DIR_INC . '/pager.php'; ?>
        <?php foreach ($news as $news_one): ?>
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
                        <?= cleaner_html($news_one[$titleField]) ?>
                    </h5>
                    <div class="card-body d-flex flex-column">


                        <!-- Краткое описание -->
                        <?php if (!empty($news_one[$descriptionField])): ?>
                            <p class="card-text mb-3">
                                <?= nl2br(cleaner_html($news_one[$descriptionField])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                        <div class="card-footer">

                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Кнопка "Подробнее" -->
                                <div class="mt-auto">
                                    <a href="/news/view?<?= News::F_GET_ID ?>=<?= $news_one[News::F_ID] ?>"
                                       class="btn btn-primary btn-sm">
                                        <?= __('Подробнее') ?>
                                    </a>
                                </div>
                                <div>
                                <!-- Дата публикации -->
                                <?php if (!empty($news_one[News::F_DATE_PUBLICATION])): ?>
                                    <p class="text-muted text-end small mb-2">
                                        <i class="bi bi-calendar"></i>
                                        <?= date("d.m.Y H:i", $news_one[News::F_DATE_PUBLICATION]) ?>
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
