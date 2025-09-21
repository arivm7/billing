<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Files/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\Icons;
use config\MimeTypes;
use config\tables\File;
/** @var array $my_files */
/** @var array $pub_files */
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Файлы</h2>
        <a href="<?= File::URI_UPLOAD; ?>" class="btn btn-primary">Отправить файл</a>
    </div>

    <!-- Вкладки -->
    <ul class="nav nav-tabs" id="fileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="public-files-tab" data-bs-toggle="tab" href="#public-files" role="tab">Публичные файлы</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="my-files-tab" data-bs-toggle="tab" href="#my-files" role="tab">Мои файлы</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- Публичные файлы -->
        <div class="tab-pane fade show active" id="public-files" role="tabpanel">
            <?php if (!empty($pub_files)): ?>
                <?php
                // группировка по F_SUB_TITLE
                $grouped = [];
                foreach ($pub_files as $file) {
                    $grouped[$file[File::F_SUB_TITLE]][] = $file;
                }
                ?>
                <?php foreach ($grouped as $sub_title => $files): ?>
                    <h5 class="mt-3 mb-2"><?= h($sub_title ?: 'Прочее') ?></h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($files as $file): ?>
                            <li class="list-group-item">
                                <?php include DIR_INC . '/file_index_one.php';?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет публичных файлов.</p>
            <?php endif; ?>
        </div>

        <!-- Мои файлы -->
        <div class="tab-pane fade" id="my-files" role="tabpanel">
            <?php if (!empty($my_files)): ?>
                <ul class="list-group">
                    <?php foreach ($my_files as $file): ?>
                        <li class="list-group-item">
                            <?php include DIR_INC . '/file_index_one.php';?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>У вас пока нет загруженных файлов.</p>
            <?php endif; ?>
        </div>

    </div>
</div>
<!-- контейнер тостов не обязателен в разметке — создаётся скриптом при необходимости -->
<script>
(function(){
  const toastContainerId = 'toastContainer';
  let container = document.getElementById(toastContainerId);
  if (!container) {
    container = document.createElement('div');
    container.id = toastContainerId;
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = 1080;
    document.body.appendChild(container);
  }

  function getTextFromBtn(btn){
    const v = btn.getAttribute('data-text');
    try { return JSON.parse(v); } catch { return v; }
  }

  async function copyText(text){
    // Требует HTTPS и современных браузеров
    await navigator.clipboard.writeText(text);
  }

  function showToast(title, body, delay = 3000){
    const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).slice(2,8);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
      <div id="${id}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">${title}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${body}</div>
      </div>
    `.trim();
    const toastEl = wrapper.firstElementChild;
    container.appendChild(toastEl);
    const bsToast = new bootstrap.Toast(toastEl, { delay, autohide: true });
    bsToast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;
    const text = getTextFromBtn(btn);
    try {
      await copyText(text);
      showToast('Успех', 'Текст скопирован в буфер обмена');
    } catch (err) {
      showToast('Ошибка', 'Не удалось скопировать текст', 5000);
    }
  });
})();
</script>