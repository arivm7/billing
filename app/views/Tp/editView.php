<?php
/*
 *  Project : my.ri.net.ua
 *  File    : editView.php
 *  Path    : app/views/Tp/editView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of editView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


/** @var array $firm */
/** @var array $admin_owner */
/** @var array $uplink */
/** @var array $prices */
/** @var array $tp */

use billing\core\App;
use config\tables\Firm;
use config\tables\Price;
use config\tables\TP;
use config\tables\User;
use billing\core\base\Lang;
use config\Icons;
use config\tables\Abon;
use config\tables\Module;

$tp[TP::F_FIRM_NAME] = ($firm ? $firm[Firm::F_NAME_TITLE] . ' | ' . $firm[Firm::F_NAME_SHORT] : "-");
$tp[TP::F_ADMIN_OWNER_NAME] = ($admin_owner ? $admin_owner[User::F_NAME_FULL] : "-");
$tp[TP::F_UPLINK_NAME] = ($uplink ? $uplink[TP::F_TITLE] : "-");

$prices_list = array_column($prices, Price::F_TITLE, Price::F_ID);

$title_label = 
"<div class='d-flex justify-content-between align-items-center'>
    <div>
        <span class='fs-3'>Title</span>
    </div>
    <div>"
    . (can_view(Module::MOD_ABON) 
        ? "<a href=" . Abon::URI_INDEX.'?tp='.$tp[TP::F_ID] . " class='btn btn-sm btn-outline-success' title='" . __('Список абонентов') . "'>
            <img src='" . Icons::SRC_ABON . "' height='22rem' alt='[A]' class='me-0 align-text-bottom'></a>"
        : ""
    )
    . "</div>
</div>";

$coord_label = 
"<div class='d-flex justify-content-between align-items-center'>
    <div>
        Coord Google Maps
    </div>
    <div>"
    . (!empty($tp[TP::F_COORD]) 
        ? "<a href='https://www.google.com.ua/maps/place/".$tp[TP::F_COORD]."/' class='btn btn-sm btn-outline-success p-0 mb-1' title='" . __('Показать на Google Maps') . "' target='_blank'>
            <img src='" . Icons::SRC_ICON_MAPS . "' height='22rem' alt='[A]' class='m-0 align-text-bottom'></a>"
        : ""
    )
    . "</div>
</div>";

// Список полей с типом и дополнительными параметрами
$fields = [
    [TP::F_TITLE                  => ['type'=>'text',        'class'=>'fs-3', 'label'=>$title_label]],
    [TP::F_RANG_ID                => ['type'=>'select_lang', 'col_w'=>'3', 'options'=>TP::TYPES, 'label'=>'Функциональный уровень']],
    [
        TP::F_STATUS              => ['type'=>'select',      'col_w'=>'2', 'options'=>[1=>'Работает',0=>'Отключен']],
        TP::F_IS_MANAGED          => ['type'=>'select',      'col_w'=>'2', 'options'=>[0=>'Не управляемая',1=>'Управляемая'], 'label'=>''],
        TP::F_DELETED             => ['type'=>'select',      'col_w'=>'2', 'options'=>[0=>'Установлена',1=>'Демонтирована'], 'label'=>''],
    ],
    [
        TP::F_ADMIN_OWNER_ID      => ['type'=>'number',      'col_w'=>'2'],
        TP::F_ADMIN_OWNER_NAME    => ['type'=>'label',       'col_w'=>'7', 'label'=>''],
    ],
    [
        TP::F_FIRM_ID             => ['type'=>'number',      'col_w'=>'2'],
        TP::F_FIRM_NAME           => ['type'=>'label',       'col_w'=>'7', 'label'=>''],
    ],
    [TP::F_IP                     => ['type'=>'text',        'col_w'=>'3', 'label'=>'Основной IP адрес доступа']],
    [TP::F_WEB_MANAGEMENT         => ['type'=>'text']],
    [TP::F_URL_ZABBIX             => ['type'=>'text']],
    [
        TP::F_LOGIN               => ['type'=>'text',        'col_w'=>'3', 'title'=>'Логин', 'placeholder'=>'Логин для доступа к TP'],
        TP::F_PASS                => ['type'=>'text',        'col_w'=>'3', 'label'=>'', 'title'=>'Пароль', 'placeholder'=>'Пароль для доступа к TP'],
    ],
    [TP::F_URL                    => ['type'=>'text',        'label'=>'URL хз...', 'title'=>'Какой-то URL. Не понятно для чего его использовать']],
    [TP::F_ADDRESS                => ['type'=>'textarea',    'rows'=>get_count_rows_for_textarea($tp[TP::F_ADDRESS] ?? 2)]],
    [TP::F_COORD                  => ['type'=>'text',       'label'=>$coord_label, 'title'=>'Координаты TP для отображения на Google Maps. Формат: широта,долгота (например: 50.4501,30.5234)']],
    [
        TP::F_UPLINK_ID           => ['type'=>'number',      'col_w'=>'2'],
        TP::F_UPLINK_NAME         => ['type'=>'label',       'col_w'=>'7', 'label'=>''],
    ],
    [TP::F_DEFAULT_PRICE_ID       => ['type'=>'select',      'col_w'=>'3', 'options' => $prices_list]],
    [TP::F_DESCRIPTION            => ['type'=>'textarea',    'rows'=>get_count_rows_for_textarea($tp[TP::F_DESCRIPTION] ?? 3)]],
    [TP::F_COST_PER_M             => ['type'=>'number',      'col_w'=>'3', 'class'=>'text-end', 'step'=>'0.01']],
    [TP::F_COST_PER_M_DESCRIPTION => ['type'=>'textarea',    'rows'=>get_count_rows_for_textarea($tp[TP::F_COST_PER_M_DESCRIPTION] ?? 2)]],
    [TP::F_COST_TP_VALUE          => ['type'=>'number',      'col_w'=>'3', 'class'=>'text-end', 'step'=>'0.01']],
    [TP::F_COST_TP_DESCRIPTION    => ['type'=>'textarea',    'rows'=>get_count_rows_for_textarea($tp[TP::F_COST_TP_DESCRIPTION] ?? 2)]],
    [TP::F_ABON_ID_RANGE_START    => ['type'=>'number',      'col_w'=>'3']],
    [TP::F_ABON_ID_RANGE_END      => ['type'=>'number',      'col_w'=>'3']],
    [TP::F_MIK_IP                 => ['type'=>'text',        'col_w'=>'3']],
    [TP::F_MIK_PORT               => ['type'=>'number',      'col_w'=>'3']],
    [TP::F_MIK_PORT_SSL           => ['type'=>'number',      'col_w'=>'3']],
    [TP::F_MIK_LOGIN              => ['type'=>'text',        'col_w'=>'3']],
    [TP::F_MIK_PASSWD             => ['type'=>'text',        'col_w'=>'3']],
    [TP::F_MIK_FTP_IP             => ['type'=>'text',        'col_w'=>'3']],
    [TP::F_MIK_FTP_PORT           => ['type'=>'number',      'col_w'=>'3']],
    [TP::F_MIK_FTP_LOGIN          => ['type'=>'text',        'col_w'=>'3']],
    [TP::F_MIK_FTP_PASSWD         => ['type'=>'text',        'col_w'=>'3']],
    [TP::F_MIK_FTP_FOLDER         => ['type'=>'text']],
    [TP::F_MIK_FTP_GETPATH        => ['type'=>'text']],
];

?>
<div class="mx-auto align-middle min-w-75 w-auto">
    <form method="post" action="<?=TP::URI_SAVE;?>/<?=$tp[TP::F_ID];?>">
        <!-- Перебор полей -->
        <?php foreach($fields as $row): ?>
            <div class="row mb-3">
                <?php foreach($row as $name=>$opt): ?>
                    <?php
                        $value = $tp[$name] ?? '';
                        $label = $opt['label'] ?? str_replace('_',' ', ucfirst($name));
                        $title = $opt['title'] ?? '';
                        if (array_key_exists($name, TP::TEMPLATES_FIELDS)) {
                            $title .= CR.'('.__('Поддерживаемые шаблоны') . ': ' . implode(', ', TP::TEMPLATES) . ' )';
                        }
                        $title = trim($title);
                    ?>
                    <?php if (!empty($label)): ?>
                        <label class="col-3 col-form-label"><?=$label?></label>
                    <?php endif; ?>
                    <div class="col-<?=($opt['col_w'] ?? '9');?>" title="<?=$title?>">
                        <?php if($opt['type']=='text' || $opt['type']=='number'): ?>
                            <input type="<?=$opt['type']?>"
                                class="form-control <?=($opt['class'] ?? '');?>"
                                name="<?=TP::POST_REC?>[<?=$name?>]"
                                value="<?=$value?>"
                                placeholder="<?=($opt['placeholder'] ?? '');?>"
                                <?=isset($opt['step'])?"step=\"{$opt['step']}\"":''?>>
                        <?php elseif($opt['type']=='textarea'): ?>
                            <textarea class="form-control"
                                    name="<?=TP::POST_REC?>[<?=$name?>]"
                                    rows="<?=$opt['rows'] ?? App::get_config('textarea_rows_min')?>"><?=$value?></textarea>
                        <?php elseif($opt['type']=='label'): ?>
                            <label class="col-form-label"><?=$value?></label>
                        <?php elseif($opt['type']=='select'): ?>
                            <select class="form-select" name="<?=TP::POST_REC?>[<?=$name?>]">
                                <?php foreach($opt['options'] as $k=>$v): ?>
                                    <option value="<?=$k?>" <?=($value==$k) ? 'selected' : ''?>><?=$v?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif($opt['type']=='select_lang'): ?>
                            <select class="form-select" name="<?=TP::POST_REC?>[<?=$name?>]">
                                <?php foreach($opt['options'] as $k=>$v): ?>
                                    <?php $label = $v[Lang::code()] ?? ''; ?>
                                    <option value="<?=$k?>" <?=($value==$k) ? 'selected' : ''?>><?=$label?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <div class="row mb-3">
            <div class="col-3 col-form-label text-secondary fs-6">
                <?=$tp[TP::F_CREATION_UID].' :: '.date(DATETIME_FORMAT, $tp[TP::F_CREATION_DATE]).'<br>'
                  .$tp[TP::F_MODIFIED_UID].' :: '.date(DATETIME_FORMAT, $tp[TP::F_MODIFIED_DATE]).'';?>
            </div>
            <div class="col-9 mt-3 text-end">
                <button type="submit" class="btn btn-primary"><?=__('Сохранить');?></button>
                <a href="<?=TP::URI_INDEX;?>" class="btn btn-secondary"><?=__('Вернуться к списку');?></a>
            </div>
        </div>
    </form>
</div>