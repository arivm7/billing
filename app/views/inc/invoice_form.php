<?php
/**
 *  Project : my.ri.net.ua
 *  File    : invoice_form.php
 *  Path    : app/views/inc/invoice_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Dec 2025 20:11:16
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of invoice_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\Pagination;
use config\tables\Abon;
use config\tables\Firm;
use config\tables\Invoice;
use config\tables\PA;
use config\tables\User;

/**
 * Переменные полученные из контроллера
 * 
 * @var string $title          -- заголовок страницы
 * @var array $abon            -- запись абонента
 * @var array $user            -- запись пользователя
 * @var array $invoice         -- Счёт/Акт
 * @var array $agent           -- Указанное в счёте предприятие провайдера
 * @var array $contragent      -- Указанное в счёте предприятие абонента
 * @var array $agent_list      -- список предприятий провайдера
 * @var array $contragent_list -- список предприятий абонента
 * 
 * @var array $item  -- Одна запись Счёта
 * 
 */

$item = $invoice;

// debug($contragent_list, '$contragent_list');

$item_contragent = (!empty($contragent)
        ?   $contragent
        :   ($contragent_list[array_key_first($contragent_list)] 
                ? $contragent_list[array_key_first($contragent_list)]
                : [ Firm::F_NAME_SHORT => '-', Firm::F_NAME_LONG => '-', ]
            )
    );

?>
<div class="card mb-4 w-100 min-w-700">
    <form action="" method="post">
        <div class="card-header fs-7">
            <!-- ID -->
            <span class="text-secondary">[<span class="text-info"><?=num_len($item[Invoice::F_ID], 6);?></span>] <?= __('Счёт-фактра / Акт') ?></span><br>
            <!-- Абонент -->
            <span class="text-secondary">[<span class="text-info"><?=num_len($item[Invoice::F_ABON_ID], 6);?></span>] <?= $abon[Abon::F_ID] ?>. <?= $abon[Abon::F_ADDRESS] ?></span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <!-- ID -->
                <input type="hidden" name="<?= Invoice::POST_REC ?>[<?= Invoice::F_ID ?>]"      value="<?=$item[Invoice::F_ID]?>">
                <!-- Абонент -->
                <input type="hidden" name="<?= Invoice::POST_REC ?>[<?= Invoice::F_ABON_ID ?>]" value="<?=$item[Invoice::F_ABON_ID]?>">

                <!-- Заказчик. Абонент. Контрагент -->
                <div class="col-4" title="<?= __('Предприятие-Абонент') ?>,<?=CR;?><?= __('Предприятие, привязанное к пользователю') ?>.">
                    <label class="form-label" for="<?= Invoice::F_FIRM_CONTRAGENT_ID ?>">
                        <?= __('Контрагент') ?>
                        <span class="text-info small">
                            [<?=$item[Invoice::F_FIRM_CONTRAGENT_ID]." ".$item_contragent[Firm::F_NAME_SHORT] ?>]
                        </span>
                    </label>
                    <select class="form-select" name='<?=Invoice::POST_REC?>[<?= Invoice::F_FIRM_CONTRAGENT_ID ?>]' id="<?= Invoice::F_FIRM_CONTRAGENT_ID ?>" >
                        <option value='0'>-</option>
                        <?php foreach ($contragent_list as $contragent) : ?>
                            <option value="<?= $contragent[Firm::F_ID] ?>" <?= ($contragent[Firm::F_ID] == $item[Invoice::F_FIRM_CONTRAGENT_ID] ? "selected" : "") ?> >
                                <span class="text-secondary">[<?= sprintf("%03d", $contragent[Firm::F_ID]) ?>]</span>&nbsp;&nbsp;
                                <?= $contragent[Firm::F_NAME_SHORT] ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Исполнитель. Провайдер. Агент -->
                <div class="col-4" title="<?= __('Предприятие-Провайдер') ?>, <?=CR;?><?= __('Предприятие, привязанное к ТП, на котоорой производится обслуживание') ?>.">
                    <label class="form-label" for="<?= Invoice::F_FIRM_AGENT_ID ?>" >
                        <?= __('Агент') ?>
                        <span class="text-info small">
                            [<?=$item[Invoice::F_FIRM_AGENT_ID]." ".$agent[Firm::F_NAME_SHORT] ?? "-" ?>]
                        </span>
                    </label>
                    <select class="form-select" id="<?= Invoice::F_FIRM_AGENT_ID ?>" name='<?=Invoice::POST_REC?>[<?= Invoice::F_FIRM_AGENT_ID ?>]'>
                        <option value='0'>-</option>
                        <?php foreach ($agent_list as $agent) : ?>
                            <option value="<?= $agent[Firm::F_ID] ?>" <?= ($agent[Firm::F_ID] == $item[Invoice::F_FIRM_AGENT_ID] ? "selected" : "") ?> >
                                <span class="text-secondary">[<?= sprintf("%03d", $agent[Firm::F_ID]) ?>]</span>&nbsp;&nbsp;
                                <?= $agent[Firm::F_NAME_SHORT] ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <!-- Статус оплачен ли счёт -->
                <div class="col-4 d-flex justify-content-end align-items-center">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input"
                            type="checkbox"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_IS_PAID ?>]"
                            id="<?= Invoice::F_IS_PAID ?>"
                            role="switch"
                            value="1"
                            <?= $item[Invoice::F_IS_PAID] ? "checked" : "" ?>>

                        <label class="form-check-label ms-2" for="<?= Invoice::F_IS_PAID ?>">
                            <?= __('Счёт оплачен') ?>
                        </label>
                    </div>
                </div>

                <!-- Предприятие-плательщик -->
                <div class="col-8">
                    <label for="<?= Invoice::F_FIRM_PAYER_STR ?>" class="form-label"><?= __('Предприятие-плательщик') ?></label>
                    <input type="text" 
                            id="<?= Invoice::F_FIRM_PAYER_STR ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_FIRM_PAYER_STR ?>]" 
                            class="form-control" 
                            value="<?=$item[Invoice::F_FIRM_PAYER_STR]?>" >
                </div>

                <!-- СФ № -->
                <div class="col-4">
                    <label for="<?= Invoice::F_INV_NO ?>" class="form-label"><?= __('Счёт-фактура №') ?></label>
                    <input type="text" 
                            id="<?= Invoice::F_INV_NO ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_INV_NO ?>]" 
                            class="form-control text-center" 
                            value="<?=$item[Invoice::F_INV_NO]?>">
                </div>

                <!-- Дата счёта (строка) -->
                <div class="col-3">
                    <label for="<?= Invoice::F_INV_DATE_STR ?>" class="form-label"><?= __('Дата счёта') ?></label>
                    <input type="text" 
                            id="<?= Invoice::F_INV_DATE_STR ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_INV_DATE_STR ?>]" 
                            class="form-control text-center" 
                            value="<?=$item[Invoice::F_INV_DATE_STR]?>">
                </div>

                <!-- Дата акта (строка) -->
                <div class="col-3">
                    <label for="<?= Invoice::F_AKT_DATE_STR ?>" class="form-label"><?= __('Дата акта') ?></label>
                    <input type="text" 
                            id="<?= Invoice::F_AKT_DATE_STR ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_AKT_DATE_STR ?>]" 
                            class="form-control text-center" 
                            value="<?=$item[Invoice::F_AKT_DATE_STR]?>">
                </div>

                <!-- Цена за 1 (float) -->
                <div class="col-2">
                    <label for="<?= Invoice::F_COST_1 ?>" class="form-label"><?= __('Цена за единицу') ?></label>
                    <input type="number"
                            required
                            min="0"
                            step="0.1"
                            lang="en"
                            inputmode="decimal"
                            id="<?= Invoice::F_COST_1 ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_COST_1 ?>]"
                            class="form-control text-end"
                            value="<?=$item[Invoice::F_COST_1]?>">
                            <div class="invalid-feedback"><?= __('Укажите цену в виде числа') ?></div>
                </div>

                <!-- Кол-во (float) -->
                <div class="col-2">
                    <label for="<?= Invoice::F_COUNT ?>" class="form-label"><?= __('Количество') ?></label>
                    <input type="number"
                            required
                            min="0"
                            step="1.0"
                            lang="en"
                            inputmode="decimal"
                            id="<?= Invoice::F_COUNT ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_COUNT ?>]"
                            class="form-control text-end"
                            value="<?=$item[Invoice::F_COUNT]?>">
                            <div class="invalid-feedback"><?= __('Укажите количество') ?></div>
                </div>

                <!-- Цена всего (float) -->
                <div class="col-2">
                    <label for="<?= Invoice::F_COST_ALL ?>" class="form-label"><?= __('Цена всего') ?></label>
                    <input type="number"
                            required
                            min="0"
                            step="0.1"
                            lang="en"
                            inputmode="decimal"
                            id="<?= Invoice::F_COST_ALL ?>"
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_COST_ALL ?>]"
                            class="form-control text-end"
                            value="<?=$item[Invoice::F_COST_ALL]?>">
                            <div class="invalid-feedback"><?= __('Укажите итоговую цену') ?></div>
                </div>


                <!-- Назначение платежа -->
                <div class="col-12">
                    <label for="<?= Invoice::F_TEXT ?>" class="form-label"><?= __('Назначение платежа') ?></label>
                    <textarea 
                            id="<?= Invoice::F_TEXT ?>"
                            class="form-control" 
                            name="<?=Invoice::POST_REC?>[<?= Invoice::F_TEXT ?>]" 
                            rows="3"><?=$item[Invoice::F_TEXT]?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer m-0">
            <div class='d-flex justify-content-between align-items-center'>
                <div class="text-start">
                    <button type="submit" class="btn btn-sm btn-primary"><?= __('Сохранить'); ?></button>
                    <a class="btn btn-sm btn-secondary ms-1" href="<?= Invoice::URI_LIST ?>/<?= $item[Invoice::F_ABON_ID] ?>"><?= __('Вернуться к списку'); ?></a>
                </div>
                <div class="text-end text-secondary font-monospace fs-8">
                    <!-- Кто создал -->
                    <span title="<?= __user(user_id: $item[Invoice::F_CREATION_UID], field:User::F_NAME_FULL) ?>">Creation UID [<span class="text-info"><?=$item[Invoice::F_CREATION_UID]?></span>]</span>
                    <!-- Когда создал -->
                    <span> [<?=date('Y-m-d H:i:s', $item[Invoice::F_CREATION_DATE])?>]</span><br>
                    <!-- Кто изменил -->
                    <span title="<?= __user(user_id: $item[Invoice::F_MODIFIED_UID], field: User::F_NAME_FULL) ?>">Modified UID [<span class="text-info"><?=$item[Invoice::F_MODIFIED_UID]?></span>]</span>
                    <!-- Когда изменил -->
                    <span> [<?=date('Y-m-d H:i:s', $item[Invoice::F_MODIFIED_DATE])?>]</span>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(() => {
    'use strict'

    const forms = document.querySelectorAll('.needs-validation')

    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})();
</script>