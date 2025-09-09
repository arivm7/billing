<?php
use config\tables\File;
/** @var array $my_files */
/** @var array $pub_files */
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Файлы</h2>
        <a href="<?= File::URI_UPLOAD;?>" class="btn btn-primary">Отправить файл</a>
    </div>

    <!-- Вкладки -->
    <ul class="nav nav-tabs" id="fileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="my-files-tab" data-bs-toggle="tab" href="#my-files" role="tab">Мои файлы</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="public-files-tab" data-bs-toggle="tab" href="#public-files" role="tab">Публичные файлы</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- Мои файлы -->
        <div class="tab-pane fade show active" id="my-files" role="tabpanel">
            <?php if (!empty($my_files)): ?>
            <ul class="list-group">
                    <?php foreach ($my_files as $file): ?>
                        <li class="list-group-item">
                            <a href="<?= $file[File::F_STORED_NAME] ?>" target="_blank">
                                <?= h($file[File::F_ORIGINAL_NAME]) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>У вас пока нет загруженных файлов.</p>
            <?php endif; ?>
        </div>

        <!-- Публичные файлы -->
        <div class="tab-pane fade" id="public-files" role="tabpanel">
            <?php if (!empty($pub_files)): ?>
            <ul class="list-group">
                    <?php foreach ($pub_files as $file): ?>
                        <li class="list-group-item">
                            <a href="<?= $file[File::F_STORED_NAME] ?>" target="_blank">
                                <?= h($file[File::F_ORIGINAL_NAME]) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Нет публичных файлов.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
