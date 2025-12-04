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
use config\tables\Module;
use config\tables\User;

Lang::load_inc(__FILE__);

require_once DIR_LIBS . '/form_functions.php';
require_once DIR_LIBS . '/inc_functions.php';

/** 
 * @var array $user
 * @var array $abon
 * @var array $item -- ПФ. элемент из функции Аккордеона
 * @var array $pa -- ПФ. элемент из контроллера
 * @var array $price -- Прайс, установленны сейчас в ПФ
 * @var array $tp -- Текущая ТП к которой прикреплена услуга
 * @var bool|null $abon_ip_on -- Enable/Disable статус IP-адреса в таблице ABON
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
    <div class="card mb-4 w-100 min-w-700">
        <div class="card-header">
            <h2 class="fs-4">
                <?php if (isset($item[PA::F_ID])) : ?> 
                    <h3><?= __('Редактировать прайсовый фрагмент') ?> <span class="text-secondary">[<?= $pa[PA::F_ID] ?>]</span></h3>
                    <h5 class="text-secondary fs-6">
                        <span title="User ID"><?= num_len($user[User::F_ID], 6); ?></span> :: 
                        <span title="User Name"><?= h($user[User::F_NAME_SHORT]); ?></span>
                    </h5>
                    <h5 class="text-secondary fs-6">
                        <span title="Абон ID"><?= num_len($abon[Abon::F_ID], 6); ?></span> :: 
                        <span title="Abon Address"><?= h($abon[Abon::F_ADDRESS]); ?>
                    </h5>
                    <h5 class="text-secondary fs-6">
                        <span><?= __('Прайсовый фрагмент') ?>: </span>
                        <span title="PA ID" class="fw-light"><?= $item[PA::F_ID]; ?></span> :: 
                        <span title="PA net name" class="fw-bolder"><?= $pa[PA::F_NET_NAME] ?></span>
                    </h5>
                <?php else: ?>
                    <?= __('Новый прайсовый фрагмент'); ?>
                    <h5 class="text-secondary"><span title="User ID"><?= $user[User::F_ID] ?></span> :: <span title="User Name"><?= h($user[User::F_NAME_SHORT]); ?></span> :: <span title="Abon Address"><?= h($abon(Abon::F_ADDRESS)); ?></h5>
                <?php endif; ?>
            </h2>
        </div>
        <form action="" method="post">
        <div class="card-body">
            <input type="hidden" name="<?= PA::POST_REC; ?>[<?= PA::F_ID; ?>]" value="<?= intval($item[PA::F_ID] ?? 0); ?>">

            <?php
            inputRow(label: 'Абонент ID', post_rec: PA::POST_REC, name: 'abon_id', value: $item[PA::F_ABON_ID] ?? '', type: InputType::NUMBER, l_layout: LabelLayout::H, label_col: 3, input_col: 3);
            selectRow(label: "Прайс ID [&nbsp;<span class='text-info-emphasis'>" . ($price[Price::F_TITLE] ?? '-') . '</span>&nbsp;]', post_rec: PA::POST_REC, name: 'prices_id', selected_id: $item['prices_id'] ?? '-', data: $prices_list);
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
                    <label class='col-3 col-form-label'><?=$label;?></label>
                    <div class='col-6' title='<?=$title;?>'>
                        <?=
                            make_html_select(
                                data: $tp_list, 
                                name: PA::POST_REC . "[".PA::F_TP_ID."]", 
                                selected_id: $item[PA::F_TP_ID] ?? '-', 
                                show_keys: true) // , select_opt: "style='min-width: 99%; white-space: nowrap;'"
                        ;?>
                    </div>
                    <div class='col-3 fs-7 text-secondary d-flex align-items-center'>
                        <?= '[ '. TP::get_status($tp) . ' ]<br>';?>
                    </div>
                </div>

                <!-- Координаты Google Maps -->
                <div class='row mb-1'>
                    <label for='<?=PA::F_COORD_GMAP;?>' class='col-3 col-form-label'><?=__('Координаты Google Maps');?></label>
                    <div class='col-8' title="<?=__('Укажите координаты на Google-картах');?>" >
                        <input type='text' class='form-control text-center text-secondary' id='<?=PA::F_COORD_GMAP;?>' name='<?=PA::POST_REC;?>[<?=PA::F_COORD_GMAP;?>]' value='<?=h($item[PA::F_COORD_GMAP] ?? '');?>'>
                    </div>
                    <div class='col-1'>
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
            <!-- Сетевые параметры -->
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start">
                    <div class='mb-1 row'>
                        <label for='net_ip_service' class='col-3 col-form-label'><?=__('IP услуга');?></label>
                        <div class='col-1 d-flex align-items-center'>
                            <input type='checkbox' class='form-check-input fs-6' id='net_ip_service'
                                name='<?=PA::POST_REC;?>[<?=PA::F_NET_IP_SERVICE;?>]'
                                value='1' <?=($item[PA::F_NET_IP_SERVICE] ? 'checked' : '');?>>
                        </div>
                        <div class='col-8 d-flex align-items-center justify-content-end'>
                            <?php /*if ($item[PA::F_NET_IP_SERVICE] && !$item[PA::F_CLOSED] && $tp[TP::F_STATUS] && $tp[TP::F_IS_MANAGED]) :*/ ?>
                            <?php if (!$item[PA::F_CLOSED] && $item[PA::F_NET_IP_SERVICE]) : ?>
                            <!-- Статус IP-MAC из ARP-таблицы микротика -->
                            <span class="badge text-bg-info mt-3 fs-6">
                                <?php if ($tp[TP::F_IS_MANAGED]) : ?>
                                    <?= get_html_abon_ip_status($abon_ip_on); ?>&nbsp;
                                    <?= ($arp ? Api::get_status_mac_from_arp_rec($arp) : 'Нет данных ARP'); ?> |
                                    <!-- Кнопки -->
                                    <!-- Отключить IP на микротике -->
                                    <?=get_html_btn_abon_ip_turn($item[PA::F_TP_ID], $item[PA::F_NET_IP], 0, options: 'class="btn btn-light p-1"');?>
                                    <!-- Включить IP на микротике -->
                                    <?=get_html_btn_abon_ip_turn($item[PA::F_TP_ID], $item[PA::F_NET_IP], 1, options: 'class="btn btn-light p-1"');?>
                                <?php endif; ?>
                                <?php if (__pa_age($item)->value & PAStatus::ACTIVE->value) : ?>
                                    <!-- Поставить услугу на паузу -->
                                    <?=get_html_btn_serv_ena(pa: $item, ena: 0, options: 'class="btn btn-light p-1"');?>
                                <?php endif; ?>
                                <?php if (__pa_age($item)->value & PAStatus::INACTIVE->value) : ?>
                                    <!-- Снять с паузы услугу -->
                                    <?=get_html_btn_serv_ena(pa: $item, ena: 1, options: 'class="btn btn-light p-1"');?>
                                    <!-- Снять с паузы услугу форсированно, без клонирования прайса -->
                                    <?=get_html_btn_serv_ena(pa: $item, ena: 1, force: 1, options: 'class="btn btn-light p-1"');?>
                                <?php endif; ?>
                                <!-- Клонировать ПФ -->
                                <?=get_html_btn_clone(pa_id: $item[PA::F_ID], options: 'class="btn btn-light p-1"');?>
                                <?php if (can_del(Module::MOD_PA)) : ?>
                                    <!-- Удалить ПФ -->
                                    <?=get_html_btn_pa_delete(pa_id: $item[PA::F_ID], options: 'class="btn btn-light p-1"');?>
                                <?php endif; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </legend>
                <!-- IP услуга -->
                <?php if ($item['net_ip_service']) : ?>
                    <fieldset class="border mt-4 p-3">
                        <!-- Парметры IP -->
                        <legend class="text-info text-start"><?=__('IP выданный абоненту через микротик');?></legend>
                        <div class='mb-1 row'>
                        <?php
                            inputRow(label: 'IP-адрес', post_rec: PA::POST_REC, name: 'net_ip', value: $item['net_ip'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: 'Маска сети', post_rec: PA::POST_REC, name: 'net_mask', value: $item['net_mask'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: 'Шлюз', post_rec: PA::POST_REC, name: 'net_gateway', value: $item['net_gateway'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            checkboxRow(label: 'IP в trusted', post_rec: PA::POST_REC, name: 'net_ip_trusted', checked: !empty($item['net_ip_trusted']), label_col: 12, input_col: 12, options: "class='col-3'");
                            ?>
                        </div>
                        <div class='mb-1 row'>
                            <?php
                            inputRow(label: 'DNS 1', post_rec: PA::POST_REC, name: 'net_dns1', value: $item['net_dns1'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: 'DNS 2', post_rec: PA::POST_REC, name: 'net_dns2', value: $item['net_dns2'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: 'MAC', post_rec: PA::POST_REC, name: 'net_mac', value: $item['net_mac'] ?? '', label_col: 12, input_col: 12, options: "class='col-4'");
                            ?>
                        </div>
                    </fieldset>
                    <!-- Белый IP через NAT-1:1 -->
                    <fieldset class="border mt-4 p-3">
                        <legend class="text-info text-start"><?=__('Белый IP через NAT-1:1');?></legend>
                        <?php
                        inputRow(label: 'NAT 1:1', post_rec: PA::POST_REC, name: 'net_nat11', value: $item['net_nat11'] ?? '', label_col: 3, input_col: 3, l_layout: LabelLayout::H);
                        ?>
                    </fieldset>
                    <fieldset class="border mt-4 p-3">
                        <legend class="text-info text-start"><?=__('IP на оборудовании абонента, мимо микротика');?></legend>
                        <div class='mb-1 row'>
                            <?php
                            inputRow(label: 'IP на абоненте', post_rec: PA::POST_REC, name: 'net_on_abon_ip', value: $item['net_on_abon_ip'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: 'Маска на абоненте', post_rec: PA::POST_REC, name: 'net_on_abon_mask', value: $item['net_on_abon_mask'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: 'Шлюз на абоненте', post_rec: PA::POST_REC, name: 'net_on_abon_gate', value: $item['net_on_abon_gate'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            ?>
                        </div>
                    </fieldset>
                <?php endif; ?>
            </fieldset>
            <!-- Стоимостные значения прайсового фрагмента -->
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start small"><?=__('Стоимостные значения этого прайсового фрагмента');?></legend>
                <div class='row mb-1 small'>
                    <div class='col-3'></div>
                    <div class='col-2 text-center'><?=__('Стоимость');?><br><?=$item['cost_value'] ?? 0;?></div>
                    <div class='col-2 text-center'><?=__('PPMA (мес)');?><br><?=$item['PPMA_value'] ?? 0;?></div>
                    <div class='col-2 text-center'><?=__('PPDA (день)');?><br><?=$item['PPDA_value'] ?? 0;?></div>
                    <div class='col-3 text-center'><?=__('Дата перерасчета');?><br><?=!empty($item['cost_date']) ? date(DATE_FORMAT, $item['cost_date']) : '____-__-__';?></div>
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

            <div class="row mb-1">
                <div class="col-3"></div>
                <div class="col-6 text-center">
                    <button type="submit" class="btn btn-primary"><?= __('Сохранить'); ?></button>
                    <a class="btn btn-secondary" href="<?=Abon::URI_VIEW;?>/<?=$item[PA::F_ABON_ID];?>"><?= __('В карточку абонента'); ?></a>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
</div>