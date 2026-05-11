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
 * Форма редактирования прайсового фрагмента
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
 * @var array $user -- Владелец абонента
 * @var array $abon -- Абонент, которому принадлежит ПФ
 * @var array $item -- ПФ. элемент из функции Аккордеона
 * @var array $pa -- ПФ. элемент из контроллера
 * @var array $price -- Прайс, установленны сейчас в ПФ
 * @var array $tp -- Текущая ТП к которой прикреплена услуга
 * @var array $tp_default_price -- Дефолтный прайс для ТП
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
                    <h3><?= __('Edit price fragment | Редактировать прайсовый фрагмент | Редагувати прайсовий фрагмент') ?> <span class="text-secondary">[<?= $pa[PA::F_ID] ?>]</span></h3>
                    <h5 class="text-secondary fs-6">
                        <span title="User ID"><?= num_len($user[User::F_ID], 6); ?></span> :: 
                        <span title="User Name"><?= h($user[User::F_NAME_SHORT]); ?></span>
                    </h5>
                    <h5 class="text-secondary fs-6">
                        <span title="Абон ID"><?= num_len($abon[Abon::F_ID], 6); ?></span> :: 
                        <span title="Abon Address"><?= h($abon[Abon::F_ADDRESS]); ?>
                    </h5>
                    <h5 class="text-secondary fs-6">
                        <span><?= __('Price fragment | Прайсовый фрагмент | Прайсовий фрагмент') ?>: </span>
                        <span title="PA ID" class="fw-light"><?= $item[PA::F_ID]; ?></span> :: 
                        <span title="PA net name" class="fw-bolder"><?= $pa[PA::F_NET_NAME] ?></span>
                    </h5>
                <?php else: ?>
                    <?= __('New price fragment | Новый прайсовый фрагмент | Новий прайсовий фрагмент'); ?>
                    <h5 class="text-secondary"><span title="User ID"><?= $user[User::F_ID] ?></span> :: <span title="User Name"><?= h($user[User::F_NAME_SHORT]); ?></span> :: <span title="Abon Address"><?= h($abon(Abon::F_ADDRESS)); ?></h5>
                <?php endif; ?>
            </h2>
        </div>
        <form action="" method="post">
        <div class="card-body">
            <input type="hidden" name="<?= PA::POST_REC; ?>[<?= PA::F_ID; ?>]" value="<?= intval($item[PA::F_ID] ?? 0); ?>">

            <?php
            inputRow(label: __('Subscriber ID | Абонент ID | Абонент ID'), post_rec: PA::POST_REC, name: 'abon_id', value: $item[PA::F_ABON_ID] ?? '', type: InputType::NUMBER, l_layout: LabelLayout::H, label_col: 3, input_col: 3);
            selectRow(label: __('Price ID | Прайс ID | Прайс ID') . " [&nbsp;<span class='text-info-emphasis'>" . ($price[Price::F_TITLE] ?? '-') . '</span>&nbsp;]', post_rec: PA::POST_REC, name: 'prices_id', selected_id: $item['prices_id'] ?? '-', data: $prices_list);
            inputRow(label: __('Network name | Сетевое имя | Мережеве ім\'я'), post_rec: PA::POST_REC, name: 'net_name', value: $item['net_name'] ?? '', l_layout: LabelLayout::H, label_col: 3, input_col: 9);
            ?>

            <div class='row mb-3'>
                <div class='col-sm-3'></div>
                <?php
                $w = (
                    (($item['date_end'] ?? 0)  > 0) && ($item['price_closed'] == 0) ||
                    (($item['date_end'] ?? 0) == 0) && ($item['price_closed'] == 1)
                    ? 'text-warning'
                    : ''
                );

                dateRow(label: __('start date | Дата начала | Дата початку'), post_rec: PA::POST_REC, name: 'date_start_str', timestamp: $item['date_start'] ?? null, label_col: 12, input_col: 12, options: "class='col-sm-3'");
                dateRow(label: "<span class={$w}>".__('End date | Дата окончания | Дата закінчення')."</span>", post_rec: PA::POST_REC, name: 'date_end_str', timestamp: $item['date_end'] ?? null, label_col: 12, input_col: 12, options: "class='col-sm-3'");
                checkboxRow(label: "<span class={$w}>".__('Price closed | Прайс закрыт | Прайс закритий')."</span>", post_rec: PA::POST_REC, name: 'price_closed', checked: !empty($item['price_closed']), label_col: 12, input_col: 12, options: "class='col-sm-2'");
                ?>
                <div class='col-sm-1'>:::</div>
            </div>

            <!-- ТП -->
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start"><?=__('Technical site parameters | Параметры технической площадки | Параметри технічного майданчика');?></legend>
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
                    <label for='<?=PA::F_COORD_GMAP;?>' class='col-3 col-form-label'><?=__('Google Maps Coordinates | Координаты Google Maps | Координати Google Maps');?></label>
                    <div class='col-8' title="<?=__('Enter coordinates in the format used on Google Maps | Укажите координаты в формате, используемом на Google-картах | Вкажіть координати у форматі, який використовується на Google-картах');?>" >
                        <input type='text' class='form-control text-center text-secondary' id='<?=PA::F_COORD_GMAP;?>' name='<?=PA::POST_REC;?>[<?=PA::F_COORD_GMAP;?>]' value='<?=h($item[PA::F_COORD_GMAP] ?? '');?>'>
                    </div>
                    <div class='col-1'>
                        <?php if ($item[PA::F_COORD_GMAP]): ?>
                            <a href="https://www.google.com/maps/place/<?=h($item[PA::F_COORD_GMAP] ?? '');?>" target=_blank title="<?=__('Show coordinates on Google Maps page | Показать координаты на странице Google-карт | Показати координати на сторінці карт Google');?>" >
                                <img src="<?=Icons::SRC_ICON_MAPS;?>" height="32rem">
                            </a>
                        <?php else : ?>
                            <span class="form-control" title="<?=__('Coordinates not specified | Координаты не указаны | Координати не вказані');?>" >&nbsp;</span>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>
            <!-- Сетевые параметры -->
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start">
                    <div class='mb-1 row'>
                        <label for='net_ip_service' class='col-3 col-form-label'><?=__('IP service | IP услуга | IP послуга');?></label>
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
                                    <?= ($arp ? Api::get_status_mac_from_arp_rec($arp) : __('No ARP data | Нет данных ARP | Немає даних ARP')); ?> |
                                    <!-- Кнопки -->
                                    <!-- Отключить IP на микротике -->
                                    <?=get_html_btn_abon_ip_turn($item[PA::F_TP_ID], $item[PA::F_NET_IP], 0, options: 'class="btn btn-light p-1"');?>
                                    <!-- Включить IP на микротике -->
                                    <?=get_html_btn_abon_ip_turn($item[PA::F_TP_ID], $item[PA::F_NET_IP], 1, options: 'class="btn btn-light p-1"');?>
                                <?php endif; ?>
                                <?php if ((__pa_age($item)->value & PAStatus::ACTIVE->value) && (__pa_age($item)->value < PAStatus::FUTURE->value)) : ?>
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
                        <legend class="text-info text-start"><?=__('IP issued to the subscriber via Mikrotik | IP выданный абоненту через микротик | IP виданий абоненту через мікротик');?></legend>
                        <div class='mb-1 row'>
                        <?php
                            inputRow(label: __('IP address | IP-адрес | IP-адреса'), post_rec: PA::POST_REC, name: 'net_ip', value: $item['net_ip'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: __('Network mask | Маска сети | Маска мережі'), post_rec: PA::POST_REC, name: 'net_mask', value: $item['net_mask'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: __('Gateway | Шлюз | Шлюз'), post_rec: PA::POST_REC, name: 'net_gateway', value: $item['net_gateway'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            checkboxRow(label: __('IP in trusted | IP в trusted | IP у trusted'), post_rec: PA::POST_REC, name: 'net_ip_trusted', checked: !empty($item['net_ip_trusted']), label_col: 12, input_col: 12, options: "class='col-3'");
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
                        <legend class="text-info text-start"><?=__('White IP via | Белый IP через | Білий IP через');?> NAT-1:1</legend>
                        <?php
                        inputRow(label: 'NAT 1:1', post_rec: PA::POST_REC, name: 'net_nat11', value: $item['net_nat11'] ?? '', label_col: 3, input_col: 3, l_layout: LabelLayout::H);
                        ?>
                    </fieldset>
                    <fieldset class="border mt-4 p-3">
                        <legend class="text-info text-start"><?=__('IP on the subscriber’s equipment, past Mikrotik | IP на оборудовании абонента, мимо микротика | IP на обладнанні абонента, повз мікротика');?></legend>
                        <div class='mb-1 row'>
                            <?php
                            inputRow(label: __('IP on subscriber equipment | IP на оборудовании абонента | IP на устаткуванні абонента'), post_rec: PA::POST_REC, name: 'net_on_abon_ip', value: $item['net_on_abon_ip'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: __('Mask on subscriber equipment | Маска на оборудовании абонента | Маска на обладнанні абонента'), post_rec: PA::POST_REC, name: 'net_on_abon_mask', value: $item['net_on_abon_mask'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            inputRow(label: __('Gateway on the subscriber\'s equipment | Шлюз на оборудовании абонента | Шлюз на обладнанні абонента'), post_rec: PA::POST_REC, name: 'net_on_abon_gate', value: $item['net_on_abon_gate'] ?? '', label_col: 12, input_col: 12, options: "class='col-3'");
                            ?>
                        </div>
                    </fieldset>
                <?php endif; ?>
            </fieldset>
            <!-- Стоимостные значения прайсового фрагмента -->
            <fieldset class="border mt-4 p-3">
                <legend class="text-info text-start small"><?=__('Cost values ​​of this price fragment | Стоимостные значения этого прайсового фрагмента | Вартість цього прайсового фрагмента');?></legend>
                <div class='row mb-1 small'>
                    <div class='col-3'></div>
                    <div class='col-2 text-center'><?=__('Price | Стоимость | Вартість');?><br><?=$item['cost_value'] ?? 0;?></div>
                    <div class='col-2 text-center'><?=__('PPMA (months) | PPMA (мес) | PPMA (міс)');?><br><?=$item['PPMA_value'] ?? 0;?></div>
                    <div class='col-2 text-center'><?=__('PPDA (day) | PPDA (день) | PPDA (день)');?><br><?=$item['PPDA_value'] ?? 0;?></div>
                    <div class='col-3 text-center'><?=__('Recalculation date | Дата перерасчета | Дата перерахунку');?><br><?=!empty($item['cost_date']) ? date(DATE_FORMAT, $item['cost_date']) : '____-__-__';?></div>
                </div>
            </fieldset>
        </div>
        <div class="card-footer text-start">
            <div class="text-secondary font-monospace fs-7">
                <?= __('Changed | Изменён | Змінено'); ?>: <?= !empty($item['modified_date']) ? date(DATE_FORMAT, $item['modified_date']) : '____-__-__'; ?>:
                <?= __('UID | UID | UID'); ?>: <?= $item['modified_uid'] ?? '__'; ?><br>
                <?= __('Created | Создан | Створено'); ?> : <?= !empty($item['creation_date']) ? date(DATE_FORMAT, $item['creation_date']) : '____-__-__'; ?>:
                <?= __('UID | UID | UID'); ?>: <?= $item['creation_uid'] ?? '__'; ?>
            </div>

            <div class="row mb-1">
                <div class="col-3"></div>
                <div class="col-6 text-center">
                    <button type="submit" class="btn btn-primary"><?= __('Save | Сохранить | Зберегти'); ?></button>
                    <a class="btn btn-secondary" href="<?=Abon::URI_VIEW;?>/<?=$item[PA::F_ABON_ID];?>"><span class="fw-bolder">🅐</span> <?= __('To the subscriber card | В карточку абонента | У картку абонента'); ?></a>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

<!-- 
    * ---------------------------------------------------------------------------------------------
    * Начало блока Перевод на другой тариф
-->

<?php if (can_add(Module::MOD_PA)) : ?> 
<?php if (isset($item[PA::F_ID])) : ?> <!-- Редактирование имеющегося ПФ, а не нового -->
<div class="col-12 col-md-10 col-lg-8">
    <div class="card mb-4 w-100 min-w-700">
        <div class="card-header">
            <h2 class="fs-4">
                <h3><?= __('Transfer to another tariff | Перевод на другой тариф | Переведення на інший тариф') ?></h3>
                <h5 class="text-light fs-7">
                    <ol>
                        <li><?=__('Closing the current price fragment (setting the closing date) | Закрытие текущего прайсового фрагмента (установка даты закрытия) | Закриття поточного прайсового фрагмента (встановлення дати закриття)')?></li>
                        <li><?=__('Creating a new open price fragment | Создание нового открытого прайсового фрагмента | Створення нового відкритого прайсового фрагменту')?></li>
                        <li><?=__('The new fragment starts from the specified date, has a new price list and a new technical site | Новый фрагмент начинается с указанной даты, имеет новый прайс и новая техплощадка | Новий фрагмент починається із зазначеної дати, має новий прайс та новий техмайданчик.')?>.</li>
                        <li><?=__('The actual service on the device does not change | Фактическая услуга на устройстве не меняется | Фактична послуга на пристрої не змінюється')?>.</li>
                    </ol>
                </h5>
            </h2>
        </div>
        <div class="card-body">
            <?php if(__pa_age($item) == PAStatus::CURRENT) : ?>
                <?php if($item[PA::F_TP_ID] > 0) : ?>
                    <?php
                    $curr_pp30 = $item[PA::F_PPMA_VALUE] + ($item[PA::F_PPDA_VALUE] * 30);
                    $new_pp30 = $tp_default_price[Price::F_PAY_PER_MONTH] + ($tp_default_price[Price::F_PAY_PER_DAY] * 30);
                    $on_date_str = date("Y")."-".sprintf("%02d", (date("m")+1))."-01";
                    ?>
                    <div class="row">
                        <div class="col-3 text-end"><?=__('Current price | Текущий прайс | Поточний прайс')?>:</div>
                        <div class="col-3"><?= $price[Price::F_TITLE] ?></div>
                        <div class="col-3"><span class="text-secondary font-monospace fs-7"><?= $curr_pp30 ?> <?=__('UAH/30 days | грн/30дней | грн/30 днів')?> | id: <?= $item[PA::F_PRICE_ID] ?></span></div>
                    </div>
                    <div class="row">
                        <div class="col-3 text-end"><?=__('Default price | Дефолтный прайс | Дефолтний прайс')?>:</div>
                        <div class="col-3"><?= $tp_default_price[Price::F_TITLE] ?></div>
                        <div class="col-3"><span class="text-secondary font-monospace fs-7"><?= $new_pp30 ?> <?=__('UAH/30 days | грн/30дней | грн/30 днів')?> | id: <?= $tp_default_price[Price::F_ID] ?></span></div>
                    </div>
                    <div class="row">
                        <div class="col-3 text-end"><?=__('Switch from date | Переключить с даты | Переключити з дати')?>:</div>
                        <div class="col-3"><span class="text-info"><?= $on_date_str ?></span></div>
                        <div class="col-3"></div>
                    </div>
                    <hr>
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <form action="<?= Api::URI_CMD ?>" method="get" target="_self" 
                                  onsubmit="return confirm('<?= __('Confirm | Подтвердите | Підтвердіть') . CR 
                                        . '1. ' . __('Closing the current price fragment | Закрытие текущего прайсового фрагмента | Закриття поточного прайсового фрагменту') . ',' . CR 
                                        . '2. ' . __('Creating a new price fragment | Создание нового прайсового фрагмента | Створення нового прайсового фрагменту') . CR 
                                        . '3. ' . __('Change of tariff and technical platform | Смену тарифа и технической площадки | Зміну тарифу та технічного майданчика'); ?>');"
                                    class="d-flex flex-row flex-nowrap align-items-center gap-2">
                                <input type='hidden' name='<?= Api::F_CMD ?>'       value='<?= Api::CMD_CHANGE_PRICE ?>'>
                                <input type='hidden' name='<?= Api::F_PA_ID ?>'     value='<?= $item[PA::F_ID] ?>'>
                                <input type='date'   name='<?= Api::F_ON_DATE ?>'   value='<?= $on_date_str ?>' size=8 class="form-control form-control-sm w-auto">&nbsp;&nbsp;
                                <?= make_html_select(
                                    data: $prices_list,
                                    name: Api::F_TO_PRICE_ID,
                                    selected_id: $tp_default_price[PA::F_ID],
                                    show_keys: true,
                                    select_opt: "class='form-select form-select-sm w-auto'"
                                ); ?>&nbsp;&nbsp;
                                <?= make_html_select(
                                    data: $tp_list,
                                    name: Api::F_TO_TP_ID,
                                    selected_id: $item[PA::F_TP_ID],
                                    show_keys: true,
                                    select_opt: "class='form-select form-select-sm w-auto'"
                                ); ?>&nbsp;&nbsp;
                                <input type="submit" value="<?=__('Change tariff | Сменить тариф | Змінити тариф')?>" class="btn btn-primary btn-sm w-auto">
                            </form>

                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="alert alert-danger" role="alert">Нужно выбрать TP</div>
                <?php endif; ?>
            <?php else: ?>
                <p class="bukvitca fs-6"><?=__('The attached price list is not active: either in the «past» or in the «future» | Прикреплённый прайс не активный: или в «прошлом», или в «будущем» | Прикріплений прайс не активний: або в «минулому», або в «майбутньому»')?>.</p>
                <p class="bukvitca fs-6"><?=__('Only the current active price fragment can be transfer | Переводить можно только текущий активный прайсовый фрагмент | Перевести можна лише поточний активний прайсовий фрагмент')?>.
                <?=__('First you need to «open» it | Сперва нужно «открыть» его | Спершу потрібно «відкрити» його')?>: 
                <?=__('The «date_end» field must either be cleared or set to today or a later date, and the «Closed:[_]» field must be unchecked. | Поле «date_end» нужно или очистить или установить в сегодняшнюю или более позднюю дату, и нужно снять флажок с поля «Закрыт:[_]» | Поле «date_end» потрібно або очистити або встановити в сьогоднішню або пізню дату, і потрібно зняти прапорець з поля «Закрито:[_]»')?>.</p>
                <p class="bukvitca fs-6"><?=__('If necessary, take into account the activation or deactivation of the actual service on the distribution device | При необходимости, учтите активацию или деактивацию фактической услуги на устройстве раздачи | При необхідності врахуйте активацію або деактивацію фактичної послуги на пристрої роздачі')?>.</p>
            <?php endif; ?>

        </div>
        <div class="card-footer text-start">
            .
        </div>
    </div>
</div>

<?php endif; ?>
<?php endif; ?>

<!--
    * Завершение блока Перевод на другой тариф
    * ---------------------------------------------------------------------------------------------
-->

</div>