<?php
/**
 *  Project : my.ri.net.ua
 *  File    : invoice_print_form.php
 *  Path    : app/views/inc/invoice_print_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 03 Mar 2026 15:02:56
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Форома вывода на печать Счёта/Акта 
 * в виде HTML-страницы, которая может быть напечатана или сохранена в PDF через браузер.
 * или в виде Api вызова для генерации PDF на сервере и его загрузки.
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\Api;
use billing\core\Pagination;
use config\Icons;
use config\tables\Invoice;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);



/**
 * Переменные полученные из контроллера
 * 
 * @var string $title          -- заголовок страницы
 * @var array $abon            -- запись абонента
 * @var array $user            -- запись пользователя
 * @var array $rest            -- остатки ЛА Абонента
 * @var Pagination $pager      -- страничный навигатор
 * @var array $invoices        -- список счетов
 * @var array $agent_list      -- список предприятий провайдера
 * @var array $contragent_list -- список предприятий абонента
 * 
 * @var array $item  -- Одна запись Счёта
 * 
 */


?>

<!-- 

    Статичные кнопки для печати Счёта/Акта 
    с различными комбинациями отображения: Счёт, Акт, Штамп. 
    И кнопка для генерации PDF. 

-->

<!-- Печатать Счёт=1 Акт=1 Штамп=1 -->
<a href="<?= Invoice::URI_PRINT ?>/<?= $item[Invoice::F_ID] ?>?<?= Invoice::F_URI_INV ?>=1&<?= Invoice::F_URI_ACT ?>=1&<?= Invoice::F_URI_SHTAMP ?>=1" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Показать для вывода на печать: '.CR.'Счёт, Акт и факсимиле'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_INV_ACT_SHTAMP ?>" alt="Счёт-Акт с подписью" height="38px"></a>
    <!-- <i class="bi bi-printer"></i> -->
<!-- Печатать Счёт=1 Акт=1 Штамп=0 -->
<a href="<?= Invoice::URI_PRINT ?>/<?= $item[Invoice::F_ID] ?>?<?= Invoice::F_URI_INV ?>=1&<?= Invoice::F_URI_ACT ?>=1&<?= Invoice::F_URI_SHTAMP ?>=0" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Показать для вывода на печать: '.CR.'Счёт, Акт'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_INV_ACT ?>" alt="Счёт-Акт" height="38px"></a>
<!-- Печатать Счёт=1 Акт=0 Штамп=1 -->
<a href="<?= Invoice::URI_PRINT ?>/<?= $item[Invoice::F_ID] ?>?<?= Invoice::F_URI_INV ?>=1&<?= Invoice::F_URI_ACT ?>=0&<?= Invoice::F_URI_SHTAMP ?>=1" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Показать для вывода на печать: '.CR.'Счёт с факсимиле'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_INV_SHTAMP ?>" alt="Счёт с подписью" height="38px"></a>
<!-- Печатать Счёт=1 Акт=0 Штамп=0 -->
<a href="<?= Invoice::URI_PRINT ?>/<?= $item[Invoice::F_ID] ?>?<?= Invoice::F_URI_INV ?>=1&<?= Invoice::F_URI_ACT ?>=0&<?= Invoice::F_URI_SHTAMP ?>=0" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Показать для вывода на печать: '.CR.'Счёт'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_INV ?>" alt="Счёт" height="38px"></a>
<!-- Печатать Счёт=0 Акт=1 Штамп=1 -->
<a href="<?= Invoice::URI_PRINT ?>/<?= $item[Invoice::F_ID] ?>?<?= Invoice::F_URI_INV ?>=0&<?= Invoice::F_URI_ACT ?>=1&<?= Invoice::F_URI_SHTAMP ?>=1" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Показать для вывода на печать: '.CR.'Акт с факсимиле'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_ACT_SHTAMP ?>" alt="Акт с подписью" height="38px"></a>
<!-- Печатать Счёт=0 Акт=1 Штамп=0 -->
<a href="<?= Invoice::URI_PRINT ?>/<?= $item[Invoice::F_ID] ?>?<?= Invoice::F_URI_INV ?>=0&<?= Invoice::F_URI_ACT ?>=1&<?= Invoice::F_URI_SHTAMP ?>=0" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Показать для вывода на печать: '.CR.'Акт'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_ACT ?>" alt="Акт" height="38px"></a>

<!-- PDF -->
<!-- 
<a href="<?= URL_HOST ?><?= Invoice::URI_PDF ?>/<?= $item[Invoice::F_ID] ?>?inv=1&act=1&sht=1" class="btn btn-sm btn-outline-success me-1 my-1 px-1 py-1" title="<?= __('Сгенерировать PDF'); ?>" target="_blank">
    <img src="<?= Icons::SRC_ICON_PDF ?>" alt="PDF" height="38px">Сгенерировать PDF</a> 
-->



<!-- 

    Форма для выбора параметров генерации PDF 
    При изменении чекбоксов, ссылка для генерации PDF автоматически обновляется с новыми параметрами.

-->

<?php if (can_view(Module::MOD_INVOICES)): ?>
    <div class="card p-3 mt-3 shadow-sm">

        <input type="hidden" id="base_url" value="<?= URL_HOST ?><?= Invoice::URI_PDF ?>/<?= $item[Invoice::F_ID] ?>">

        <div class="form-check">
            <input class="form-check-input parameter-field"
                type="checkbox"
                id="<?= Invoice::F_URI_INV ?>"
                value="1"
                checked>
            <label class="form-check-label" for="<?= Invoice::F_URI_INV ?>">
                Печатать счёт
            </label>
        </div>

        <div class="form-check">
            <input class="form-check-input parameter-field"
                type="checkbox"
                id="<?= Invoice::F_URI_ACT ?>"
                value="1"
                checked>
            <label class="form-check-label" for="<?= Invoice::F_URI_ACT ?>">
                Печатать акт
            </label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input parameter-field"
                type="checkbox"
                id="<?= Invoice::F_URI_SHTAMP ?>"
                value="1"
                checked>
            <label class="form-check-label" for="<?= Invoice::F_URI_SHTAMP ?>">
                Печатать факсимиле
            </label>
        </div>

        <a id="pdf_link"
        href="#"
        target="_blank"
        class="btn btn-sm btn-outline-success">
            <img src="<?= Icons::SRC_ICON_PDF ?>" alt="PDF" height="38">
            Сгенерировать PDF (долго)
        </a>

    </div>
<?php endif; ?>



<script>
    document.addEventListener('DOMContentLoaded', function () {

        const baseUrl = document.getElementById('base_url').value;

        const checkboxes = document.querySelectorAll('.parameter-field');
        const link = document.getElementById('pdf_link');

        function buildUrl() {
            const params = new URLSearchParams();

            params.append('<?= Invoice::F_URI_INV ?>',    document.getElementById('<?= Invoice::F_URI_INV ?>')    . checked ? '1' : '0');
            params.append('<?= Invoice::F_URI_ACT ?>',    document.getElementById('<?= Invoice::F_URI_ACT ?>')    . checked ? '1' : '0');
            params.append('<?= Invoice::F_URI_SHTAMP ?>', document.getElementById('<?= Invoice::F_URI_SHTAMP ?>') . checked ? '1' : '0');

            link.href = baseUrl + '?' + params.toString();
        }

        // Обновлять при изменении чекбокса
        checkboxes.forEach(cb => {
            cb.addEventListener('change', buildUrl);
        });

        // Инициализация при загрузке
        buildUrl();
    });
</script>