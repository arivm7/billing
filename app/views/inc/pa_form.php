<?php
/*
 *  Project : my.ri.net.ua
 *  File    : pa_form.php
 *  Path    : app/views/inc/pa_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of pa_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\Api;
use config\Icons;
use config\tables\Abon;
use config\tables\PA;
use config\tables\TP;
use config\tables\Price;
use config\SessionFields;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

require_once DIR_LIBS . '/form_functions.php';

/** 
 * @var array $item -- элемент из функции Аккордеона
 * @var array $pa -- элемент из контроллера
 * @var array $tp -- Текущая ТП к которой прикреплена услуга
 * @var array $arp -- Запись из таблицы ARP микротика со статусом IP-адреса
 * @var array $prices_list -- список прайсов, только названия
 * @var array $tp_list -- список ТП, только названия
 */

/**
 * Данные с предыдущего редактирования
 */
if (empty($item)) { $item = $pa; }
if (isset($_SESSION[SessionFields::FORM_DATA])) {
    $item = $_SESSION[SessionFields::FORM_DATA];
    unset($_SESSION[SessionFields::FORM_DATA]);
}

?>

<div class="row justify-content-center">
<div class="col-12 col-md-10 col-lg-8">
    <div class="card mb-4 w-75">
        <div class="card-header">
            <h2><?= isset($item[PA::F_ID]) ? __('Редактировать прайсовый фрагмент для абонента') . ' #' . $item[PA::F_ABON_ID] : __('Новый прайсовый фрагмент'); ?></h2>
        </div>
        <form action="" method="post">
        <div class="card-body">
            <input type="hidden" name="<?= PA::POST_REC; ?>[<?= PA::F_ID; ?>]" value="<?= intval($item[PA::F_ID] ?? 0); ?>">

            <?php
            inputRow(label: 'Абонент ID', post_rec: PA::POST_REC, name: 'abon_id', value: $item[PA::F_ABON_ID] ?? '', type: InputType::NUMBER, l_layout: LabelLayout::H, label_col: 3, input_col: 3);
            selectRow(label: "Прайс ID [&nbsp;<span class='text-info-emphasis'>" . ($prices_list[$item[PA::F_PRICE_ID]] ?? '-') . '</span>&nbsp;]', post_rec: PA::POST_REC, name: 'prices_id', selected_id: $item['prices_id'] ?? '-', data: $prices_list);
            inputRow(label: 'Сетевое имя', post_rec: PA::POST_REC, name: 'net_name', value: $item['net_name'] ?? '', l_layout: LabelLayout::H, label_col: 3, input_col: 9);
            ?>

            <div class='mb-3 row'>
                <div class='col-sm-3'></div>
                <?php
                $w = (
                    (($item['date_end'] ?? 0)  > 0) && ($item['price_closed'] == 0) ||
                    (($item['date_end'] ?? 0) == 0) && ($item['price_closed'] == 1)
                    ? 'text-warning'
                    : ''
                );

                dateRow(label: 'Дата начала', post_rec: PA::POST_REC, name: 'date_start_str', timestamp: $item['date_start'] ?? null, label_col: 12, input_col: 12, options: "class='col-sm-3'");
                dateRow(label: "<span class={$w}>Дата окончания</span>", post_rec: PA::POST_REC, name: 'date_end_str', timestamp: $item['date_end'] ?? null, label_col: 12, input_col: 12, options: "class='col-sm-3'");
                checkboxRow(label: "<span class={$w}>Прайс закрыт</span>", post_rec: PA::POST_REC, name: 'price_closed', checked: !empty($item['price_closed']), label_col: 12, input_col: 12, options: "class='col-sm-3'");
                ?>
            </div>

            <!-- ТП -->
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start"><?=__('Параметры ТП');?></legend>
                <?php

                    $label = "ТП <span class='text-info fs-7'>[&nbsp;" 
                                . ($tp[TP::F_ID] ?? '-') . ' | ' 
                                . ($tp[TP::F_TITLE] ?? '-') . '&nbsp;]</span>';
                    $title = "---";
                ?>
                <div class='row mb-3'>
                    <label class='col-sm-3 col-form-label'><?=$label;?></label>
                    <div class='col-sm-6' title='<?=$title;?>'>
                        <?=
                            make_html_select(
                                data: $tp_list, 
                                name: PA::POST_REC . "[".PA::F_TP_ID."]", 
                                selected_id: $item[PA::F_TP_ID] ?? '-', 
                                show_keys: true) // , select_opt: "style='min-width: 99%; white-space: nowrap;'"
                        ;?>
                    </div>
                    <div class='col-sm-3 fs-7 text-secondary d-flex align-items-center'>                        
                        <?= '[ '. TP::get_status($tp) . ' ]<br>';?>
                    </div>
                </div>

                <div class='row mb-3'>
                    <label for='<?=PA::F_COORD_GMAP;?>' class='col-sm-3 col-form-label'><?=__('Координаты Google Maps');?></label>
                    <div class='col-sm-8' title="<?=__('Укажите координаты на Google-картах');?>" >
                        <input type='text' class='form-control text-center text-secondary' id='<?=PA::F_COORD_GMAP;?>' name='<?=PA::POST_REC;?>[<?=PA::F_COORD_GMAP;?>]' value='<?=h($item[PA::F_COORD_GMAP] ?? '');?>'>
                    </div>
                    <div class='col-sm-1'>
                        <?php if ($item[PA::F_COORD_GMAP]): ?>
                            <a href="https://www.google.com/maps/place/<?=h($item[PA::F_COORD_GMAP] ?? '');?>" target=_blank title="<?=__('Показать координаты на странице Google-карт');?>" >
                                <img src="<?=Icons::SRC_ICON_MAPS;?>" height="32rem">
                            </a>
                        <?php else : ?>
                            <span class="form-control" title="<?=__('Координаты не указаны');?>" >&nbsp;</span>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>

            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start">
                    <div class='mb-3 row'>
                        <label for='net_ip_service' class='col-sm-3 col-form-label'><?=__('IP услуга');?></label>
                        <div class='col-sm-3 d-flex align-items-center'>
                            <input type='checkbox' class='form-check-input fs-6' id='net_ip_service'
                                name='<?=PA::POST_REC;?>[<?=PA::F_NET_IP_SERVICE;?>]'
                                value='1' <?=($item[PA::F_NET_IP_SERVICE] ? 'checked' : '');?>>
                        </div>
                        <?php if ($item['net_ip_service']) : ?>
                            <div class='col-sm-6 d-flex align-items-center'>
                                <!-- Статус IP-MAC из ARP-таблицы микротика -->
                                <span class="badge text-bg-info mt-3">
                                <?= ($arp ? Api::get_status_mac_from_arp_rec($arp) : 'Нет данных ARP'); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </legend>
                <?php if ($item['net_ip_service']) : ?>
                    <fieldset class="border mt-4 p-3">
                        <legend class="text-info text-start"><?=__('IP выданный абоненту через микротик');?></legend>
                        <div class='mb-3 row'>
                            <!-- Парметры IP -->
                            <?php
                            inputRow(label: 'IP-адрес', post_rec: PA::POST_REC, name: 'net_ip', value: $item['net_ip'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            inputRow(label: 'Маска сети', post_rec: PA::POST_REC, name: 'net_mask', value: $item['net_mask'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            inputRow(label: 'Шлюз', post_rec: PA::POST_REC, name: 'net_gateway', value: $item['net_gateway'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            checkboxRow(label: 'IP в trusted', post_rec: PA::POST_REC, name: 'net_ip_trusted', checked: !empty($item['net_ip_trusted']), label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            ?>
                        </div>
                        <div class='mb-3 row'>
                            <?php
                            inputRow(label: 'DNS 1', post_rec: PA::POST_REC, name: 'net_dns1', value: $item['net_dns1'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            inputRow(label: 'DNS 2', post_rec: PA::POST_REC, name: 'net_dns2', value: $item['net_dns2'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            inputRow(label: 'MAC', post_rec: PA::POST_REC, name: 'net_mac', value: $item['net_mac'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-4'");
                            ?>
                        </div>
                    </fieldset>
                    <fieldset class="border mt-4 p-3">
                        <legend class="text-info text-start"><?=__('Белый IP через NAT-1:1');?></legend>
                        <?php
                        inputRow(label: 'NAT 1:1', post_rec: PA::POST_REC, name: 'net_nat11', value: $item['net_nat11'] ?? '', label_col: 3, input_col: 3, l_layout: LabelLayout::H);
                        ?>
                    </fieldset>
                    <fieldset class="border mt-4 p-3">
                        <legend class="text-info text-start"><?=__('IP на оборудовании абонента, мимо микротика');?></legend>
                        <div class='mb-3 row'>
                            <?php
                            inputRow(label: 'IP на абоненте', post_rec: PA::POST_REC, name: 'net_on_abon_ip', value: $item['net_on_abon_ip'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            inputRow(label: 'Маска на абоненте', post_rec: PA::POST_REC, name: 'net_on_abon_mask', value: $item['net_on_abon_mask'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            inputRow(label: 'Шлюз на абоненте', post_rec: PA::POST_REC, name: 'net_on_abon_gate', value: $item['net_on_abon_gate'] ?? '', label_col: 12, input_col: 12, options: "class='col-sm-3'");
                            ?>
                        </div>
                    </fieldset>
                <?php endif; ?>
            </fieldset>
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start small"><?=__('Стоимостные значения этого прайсового фрагмента');?></legend>
                <div class='row mb-3 small'>
                    <div class='col-sm-3'></div>
                    <div class='col-sm-2 text-center'><?=__('Стоимость');?><br><?=$item['cost_value'] ?? 0;?></div>
                    <div class='col-sm-2 text-center'><?=__('PPMA (мес)');?><br><?=$item['PPMA_value'] ?? 0;?></div>
                    <div class='col-sm-2 text-center'><?=__('PPDA (день)');?><br><?=$item['PPDA_value'] ?? 0;?></div>
                    <div class='col-sm-3 text-center'><?=__('Дата перерасчета');?><br><?=!empty($item['cost_date']) ? date(DATE_FORMAT, $item['cost_date']) : '____-__-__';?></div>
                </div>
            </fieldset>
        </div>
        <div class="card-footer text-start">
            <div class="text-secondary font-monospace fs-7">
                <?= __('Изменён'); ?>: <?= !empty($item['modified_date']) ? date(DATE_FORMAT, $item['modified_date']) : '____-__-__'; ?>:
                <?= __('UID'); ?>: <?= $item['modified_uid'] ?? '__'; ?><br>
                <?= __('Создан'); ?> : <?= !empty($item['creation_date']) ? date(DATE_FORMAT, $item['creation_date']) : '____-__-__'; ?>:
                <?= __('UID'); ?>: <?= $item['creation_uid'] ?? '__'; ?>
            </div>

            <div class="row mb-3">
                <div class="col-sm-3"></div>
                <div class="col-sm-6 text-center">
                    <button type="submit" class="btn btn-primary"><?= __('Сохранить'); ?></button>
                    <a class="btn btn-secondary" href="<?=Abon::URI_VIEW;?>/<?=$item[PA::F_ABON_ID];?>"><?= __('В карточку абонента'); ?></a>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
</div>