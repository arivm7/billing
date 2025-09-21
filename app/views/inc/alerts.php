<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : alerts.php
 *  Path    : app/views/inc/alerts.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of alerts.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\SessionFields;

    $alerts = [

        // field                      | class-suffix | timeout

        // статические
        [SessionFields::INFO,           'info',        false],
        [SessionFields::ERROR,          'danger',      false],
        [SessionFields::SUCCESS,        'success',     false],

        // авто-закрывающиеся
        [SessionFields::INFO_AUTO,      'info',        SessionFields::INFO_TIMEOUT],
        [SessionFields::ERROR_AUTO,     'danger',      SessionFields::ERROR_TIMEOUT],
        [SessionFields::SUCCESS_AUTO,   'success',     SessionFields::SUCCESS_TIMEOUT],
    ];

    $autoAlerts = [];
?>

<?php
    foreach ($alerts as [$field, $suffix, $auto]) :
        if (isset($_SESSION[$field])) :
            $msg = parce_msg($_SESSION[$field]);
            unset($_SESSION[$field]);

            if ($auto) {
                $autoAlerts[] = ['msg' => $msg, 'suffix' => $suffix, 'timeout' => $auto];
            } else {
?>
        <!-- статический alert -->
        <div class="alert alert-<?= $suffix ?> alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
            }
        endif;
    endforeach;
?>

<?php if ($autoAlerts) : ?>
<!-- контейнер для тостов -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <?php foreach ($autoAlerts as $i => $alert) : ?>
        <div class="toast align-items-center text-bg-<?= $alert['suffix'] ?> border-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true"
             data-bs-delay="<?= $alert['timeout'] ?>">
            <div class="d-flex">
                <div class="toast-body">
                    <?= $alert['msg'] ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Инициализация всех тостов
    document.querySelectorAll('.toast').forEach(toastEl => {
        const t = new bootstrap.Toast(toastEl);
        t.show();
    });
});
</script>