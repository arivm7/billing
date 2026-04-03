<?php
/**
 *  Project : my.ri.net.ua
 *  File    : invoice_button_print.php
 *  Path    : app/views/inc/invoice_button_print.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 15 Mar 2026 18:15:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Кнопка вывода модальной формы для печати Счетов/Актов различными спобобами.
 * 
 * invoice_view.php -> invoice_button_print.php (этот файл) -> invoice_print_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\Icons;
use config\tables\Invoice;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * Переменные полученные из контроллера
 * 
 * Данные из app/views/inc/invoice_view.php
 * 
 * @var array $item  -- Одна запись Счёта
 * 
 */

?>

<!-- Если есть ID счета, то Счёт есть, и его можно напечатать -->
<?php if (!empty($item[Invoice::F_ID])): ?>

    <!--  Кнопка печати Счёта/Акта -->
    <button type="button" class="btn btn-sm btn-outline-success me-1 px-1 py-1"
        data-bs-toggle="modal" 
        data-bs-target="#printModalForm_<?= $item[Invoice::F_ID] ?>"
        title="<?= __('Печать Счёта/Акта разными способами') ?>">
        <img src="<?= Icons::SRC_ICON_PRINT ?>" alt="Печать" height="28px">
    </button>

    <!-- Модальная форма выбора способа печати Счёта/Акта (уникальная для каждого счёта) -->
    <div class="modal fade" id="printModalForm_<?= $item[Invoice::F_ID] ?>" tabindex="-1" aria-labelledby="printModalFormLabel_<?= $item[Invoice::F_ID] ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="printModalFormLabel_<?= $item[Invoice::F_ID] ?>"><?= __('Выберите форму печати Счёта/Акта') ?> #<?= $item[Invoice::F_ID] ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php include DIR_INC . '/invoice_print_form.php'; ?>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php endif ?>

