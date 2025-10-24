<?php
/**
 *  Project : my.ri.net.ua
 *  File    : pay_phase_01.php
 *  Path    : app/views/inc/pay_phase_01.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Oct 2025 19:53:42
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Форма первой фазы проведения оплаты:
 * 
 * 1.   Выбор номера договора
 *      или из списка договоров авторизованного абонента 
 *      или из поля ввода номера
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Pay;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * @var int $phase -- Фаза проведения платежа
 * @var string $title -- Заголовок текущей фазы
 * @var array $abon_list -- Список абонентов
 */

?>
<h1 class="h5 text-center mb-4"><?=__('Specify your personal account number');?></h1>
<form method="post" action="<?=Pay::URI_PAY;?>" class="row g-3 font-monospace needs-validation" novalidate>
    <div class="col-12">
        <label for="option_abon_id" class="form-label"><?=__('Select an agreement');?></label>
        <select name="<?=Pay::POST_REC;?>[option_abon_id]" id="option_abon_id" class="form-select">
            <option value="">-- <?=__('The contract number is not selected');?> --</option>
            <?php if (!empty($abon_list) && is_array($abon_list)): ?>
                <?php foreach ($abon_list as $abon): ?>
                    <?php
                        $number = (string)$abon['id'];
                        $padded = sprintf('%7s', $number);
                        $padded_html = preg_replace_callback('/^ +/', function($m){ return str_repeat('<span class="text-secondary">0</span>', strlen($m[0])); }, $padded);
                    ?>
                    <option value="<?= h($abon['id']) ?>">
                        <?= '<span class="font-monospace">' . $padded_html . ' | ' . h($abon['address']) . '</span>' ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled><?=__('The list of contracts is empty');?></option>
            <?php endif; ?>
        </select>
        <div class="form-text"><?=__('If the required contract number is not in the list, use the field below.');?></div>
    </div>

    <div class="col-12">
        <label for="custom_abon_id" class="form-label"><?=__('Enter the contract number (if not in the list)');?></label>
        <input
            type="number"
            name="<?=Pay::POST_REC;?>[custom_abon_id]"
            id="custom_abon_id"
            class="form-control"
            placeholder="<?=__('Enter the contract number');?>"
            value=""
            min="1"
        >
        <div class="invalid-feedback">
            <?=__('Please select or enter the contract number');?>
        </div>
    </div>

    <div class="col-12 text-center">
        <input type="hidden" name="<?=Pay::POST_REC;?>[phase]" value="<?=h(strval($phase));?>">
        <button type="submit" class="btn btn-primary w-100"><?=__('Continue');?></button>
    </div>
</form>

<script>
(function () {
    const sel = document.getElementById('option_abon_id');
    const custom = document.getElementById('custom_abon_id');
    const form = document.querySelector('.needs-validation');

    function sync() {
        if (sel.value) {
            custom.value = '';
            custom.disabled = true;
        } else {
            custom.disabled = false;
        }
    }

    sel.addEventListener('change', sync);
    sync();

    form.addEventListener('submit', function (event) {
        // если оба поля пустые — блокируем отправку
        if (!sel.value && !custom.value) {
            event.preventDefault();
            event.stopPropagation();
            custom.classList.add('is-invalid');
            sel.classList.add('is-invalid');
        } else {
            custom.classList.remove('is-invalid');
            sel.classList.remove('is-invalid');
        }

        form.classList.add('was-validated');
    }, false);
})();
</script>