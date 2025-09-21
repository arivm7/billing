<?php
/*
 *  Project : s1.ri.net.ua
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

use app\models\AbonModel;
use config\tables\PA;
use config\tables\TP;
use config\tables\Price;
require_once DIR_LIBS . '/form_functions.php';
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

$model = new AbonModel();
$prices = array_column(
        array: $model->get_rows_by_sql("SELECT `".Price::F_ID."`, `".Price::F_TITLE."` FROM `".Price::TABLE."` WHERE (`".Price::F_ACTIVE."`=1) ORDER BY `".Price::TABLE."`.`".Price::F_TITLE."` ASC"),
        column_key: Price::F_TITLE,
        index_key: Price::F_ID);
$tp_list = array_column(
        array: $model->get_rows_by_sql("SELECT `".TP::F_ID."`, `".TP::F_TITLE."` FROM `".TP::TABLE."` WHERE (`".TP::F_STATUS."`=1) ORDER BY `".TP::TABLE."`.`".TP::F_TITLE."` ASC"),
        column_key: TP::F_TITLE,
        index_key: TP::F_ID);

/** @var array $item */
?>

<div class="container-fluid mt-4">
    <h2><?= isset($item[PA::F_ID]) ? __('Редактировать прайсовый фрагмент для абонента') . ' #' . $item[PA::F_ABON_ID] : __('Новый прайсовый фрагмент'); ?></h2>
    <form action="" method="post">
        <input type="hidden" name="<?=PA::POST_REC;?>[<?=PA::F_ID;?>]" value="<?= intval($item[PA::F_ID] ?? 0); ?>">

        <?php

        inputRow(label: 'Абонент ID', name: 'abon_id', value: $item[PA::F_ABON_ID] ?? '', type: InputType::NUMBER,
                l_layout: LabelLayout::H, label_col: 3, input_col: 2);
        selectRow(label: "Прайс ID [&nbsp;<span class='text-info-emphasis'>".($item[PA::F_PRICE_TITLE] ?? '').'</span>&nbsp;]',
                name: 'prices_id', selected_id: $item['prices_id'] ?? '-', data: $prices);
        inputRow(label: 'Сетевое имя', name: 'net_name', value: $item['net_name'] ?? '',
                l_layout: LabelLayout::H, label_col: 3, input_col: 6);
        ?>

        <div class='mb-3 row'>
            <div class='col-sm-3'></div>
            <?php
            dateRow(label: 'Дата начала', name: 'date_start_str', timestamp: $item['date_start'] ?? null,
                    label_col: 12, input_col: 12, options: "class='col-sm-3'");
            dateRow(label: 'Дата окончания', name: 'date_end_str', timestamp: $item['date_end'] ?? null,
                    label_col: 12, input_col: 12, options: "class='col-sm-3'");
            checkboxRow(label: 'Прайс закрыт', name: 'price_closed', checked: !empty($item['price_closed']),
                    label_col: 12, input_col: 12, options: "class='col-sm-3'");
            ?>
        </div>
        <fieldset class="border p-3">
            <legend class="text-info text-end small">IP услуга</legend>
            <?php
            checkboxRow(label: 'IP услуга', name: 'net_ip_service', checked: !empty($item['net_ip_service']),
                    l_layout: LabelLayout::H);
            ?>
            <fieldset class="border p-3">
                <legend class="text-info text-end small">IP выданный абоненту через микротик.</legend>
                <div class='mb-3 row'>
                    <?php
                    inputRow(label: 'IP-адрес', name: 'net_ip', value: $item['net_ip'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    inputRow(label: 'Маска сети', name: 'net_mask', value: $item['net_mask'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    inputRow(label: 'Шлюз', name: 'net_gateway', value: $item['net_gateway'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    checkboxRow(label: 'IP в trusted', name: 'net_ip_trusted', checked: !empty($item['net_ip_trusted']),
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    ?>
                </div>
                <div class='mb-3 row'>
                    <?php
                    inputRow(label: 'DNS 1', name: 'net_dns1', value: $item['net_dns1'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    inputRow(label: 'DNS 2', name: 'net_dns2', value: $item['net_dns2'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    inputRow(label: 'MAC', name: 'net_mac', value: $item['net_mac'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    ?>
                </div>
            </fieldset>
            <fieldset class="border p-3">
                <legend class="text-info text-end small">Белый IP через NAT-1:1.</legend>
                <?php
                inputRow(label: 'NAT 1:1', name: 'net_nat11', value: $item['net_nat11'] ?? '',
                        label_col: 3, input_col: 3, l_layout: LabelLayout::H);
                ?>
            </fieldset>
            <fieldset class="border p-3">
                <legend class="text-info text-end small">IP на оборудовании абонента, мимо микротика.</legend>
                <div class='mb-3 row'>
                    <?php
                    inputRow(label: 'IP на абоненте', name: 'net_on_abon_ip', value: $item['net_on_abon_ip'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    inputRow(label: 'Маска на абоненте', name: 'net_on_abon_mask', value: $item['net_on_abon_mask'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    inputRow(label: 'Шлюз на абоненте', name: 'net_on_abon_gate', value: $item['net_on_abon_gate'] ?? '',
                            label_col: 12, input_col: 12, options: "class='col-sm-3'");
                    ?>
                </div>
            </fieldset>
        </fieldset>
        <br>
        <fieldset class="border p-3">
            <legend class="text-info text-end small">Параметры ТП</legend>
            <?php
            selectRow(label: "ТП <span class='text-info-emphasis small'>[&nbsp;".($item[PA::F_TP_ID] ?? '').' | '.$model->get_tp($item[PA::F_TP_ID])[TP::F_TITLE].'&nbsp;]</span>',
                    name: PA::F_TP_ID, selected_id: $item[PA::F_TP_ID] ?? '-', data: $tp_list);
            inputRow(label: 'Координаты Google Maps', name: 'coord_gmap', value: $item['coord_gmap'] ?? '',
                    label_col: 3, input_col: 6, l_layout: LabelLayout::H);
            ?>
        </fieldset>
        <fieldset class="border p-3">
            <legend class="text-info text-end small">Стоимостные значения этого прайсового фрагмента</legend>
            <div class='mb-3 row'>
                <div class='col-sm-3'></div>
                <?php
                inputRow(label: 'Стоимость', name: 'cost_value', value: $item['cost_value'] ?? '', type: InputType::NUMBER,
                        label_col: 12, input_col: 12, l_layout: LabelLayout::V, options: "class='col-sm-2'");
                inputRow(label: 'PPMA (мес)', name: 'PPMA_value', value: $item['PPMA_value'] ?? '', type: InputType::NUMBER,
                        label_col: 12, input_col: 12, l_layout: LabelLayout::V, options: "class='col-sm-2'");
                inputRow(label: 'PPDA (день)', name: 'PPDA_value', value: $item['PPDA_value'] ?? '', type: InputType::NUMBER,
                        label_col: 12, input_col: 12, l_layout: LabelLayout::V, options: "class='col-sm-2'");
                dateRow(label: 'Дата перерасчета', name: 'cost_date', timestamp: $item['cost_date'] ?? null,
                        label_col: 12, input_col: 12, l_layout: LabelLayout::V, options: "class='col-sm-3'");
                ?>
            </div>
        </fieldset>
        <?php
        inputRow(label: 'Кем создано (UID)', name: 'creation_uid', value: $item['creation_uid'] ?? '', type: InputType::NUMBER);
        dateRow(label: 'Дата создания', name: 'creation_date', timestamp: $item['creation_date'] ?? null);
        inputRow(label: 'Кем изменено (UID)', name: 'modified_uid', value: $item['modified_uid'] ?? '', type: InputType::NUMBER);
        dateRow(label: 'Дата изменения', name: 'modified_date', timestamp: $item['modified_date'] ?? null);

        ?>

        <div class="mb-3 row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6 text-center">
                <button type="submit" class="btn btn-primary"><?=__('Сохранить');?></button>
            </div>
        </div>
    </form>
</div>