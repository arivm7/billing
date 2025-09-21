<?php
/*
 *  Project : s1.ri.net.ua
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

use config\tables\Firm;
use config\tables\Price;
use config\tables\TP;
use config\tables\User;
use billing\core\base\Lang;

$tp[TP::F_FIRM_NAME] = ($firm ? $firm[Firm::F_NAME_TITLE] . ' | ' . $firm[Firm::F_NAME_SHORT] : "-");
$tp[TP::F_ADMIN_OWNER_NAME] = ($admin_owner ? $admin_owner[User::F_NAME_FULL] : "-");
$tp[TP::F_UPLINK_NAME] = ($uplink ? $uplink[TP::F_TITLE] : "-");

$prices_list = array_column($prices, Price::F_TITLE, Price::F_ID);

// Список полей с типом и дополнительными параметрами
$fields = [
    TP::F_TITLE                     => ['type'=>'text', 'class'=>'fs-3'],
    TP::F_RANG_ID                   => ['type'=>'select_lang', 'col_w'=>'3', 'options'=>TP::TYPES],
    TP::F_STATUS                    => ['type'=>'select',      'col_w'=>'3', 'options'=>[1=>'Работает',0=>'Отключен']],
    TP::F_IS_MANAGED                => ['type'=>'select',      'col_w'=>'3', 'options'=>[0=>'Нет',1=>'Управляемая']],
    TP::F_DELETED                   => ['type'=>'select',      'col_w'=>'3', 'options'=>[0=>'Установлена',1=>'Демонтирована']],
    TP::F_ADMIN_OWNER_NAME          => ['type'=>'label'],
    TP::F_ADMIN_OWNER_ID            => ['type'=>'number',      'col_w'=>'3'],
    TP::F_FIRM_NAME                 => ['type'=>'label'],
    TP::F_FIRM_ID                   => ['type'=>'number',      'col_w'=>'3'],
    TP::F_IP                        => ['type'=>'text',        'col_w'=>'3'],
    TP::F_LOGIN                     => ['type'=>'text',        'col_w'=>'3'],
    TP::F_PASS                      => ['type'=>'text',        'col_w'=>'3'],
    TP::F_URL                       => ['type'=>'text'],
    TP::F_URL_ZABBIX                => ['type'=>'text'],
    TP::F_ADDRESS                   => ['type'=>'textarea',    'rows'=>3],
    TP::F_COORD                     => ['type'=>'text'],
    TP::F_UPLINK_NAME               => ['type'=>'label'],
    TP::F_UPLINK_ID                 => ['type'=>'number',      'col_w'=>'3'],
    TP::F_WEB_MANAGEMENT            => ['type'=>'text'],
    TP::F_DEFAULT_PRICE_ID          => ['type'=>'select',      'col_w'=>'3', 'options' => $prices_list],
    TP::F_DESCRIPTION               => ['type'=>'textarea',    'rows'=>4],
    TP::F_COST_PER_M                => ['type'=>'number',      'col_w'=>'3', 'class'=>'text-end', 'step'=>'0.01'],
    TP::F_COST_PER_M_DESCRIPTION    => ['type'=>'textarea',    'rows'=>2],
    TP::F_COST_TP_VALUE             => ['type'=>'number',      'col_w'=>'3', 'class'=>'text-end', 'step'=>'0.01'],
    TP::F_COST_TP_DESCRIPTION       => ['type'=>'textarea',    'rows'=>2],
    TP::F_ABON_ID_RANGE_START       => ['type'=>'number',      'col_w'=>'3'],
    TP::F_ABON_ID_RANGE_END         => ['type'=>'number',      'col_w'=>'3'],
    TP::F_MIK_IP                    => ['type'=>'text',        'col_w'=>'3'],
    TP::F_MIK_PORT                  => ['type'=>'number',      'col_w'=>'3'],
    TP::F_MIK_PORT_SSL              => ['type'=>'number',      'col_w'=>'3'],
    TP::F_MIK_LOGIN                 => ['type'=>'text',        'col_w'=>'3'],
    TP::F_MIK_PASSWD                => ['type'=>'text',        'col_w'=>'3'],
    TP::F_MIK_FTP_IP                => ['type'=>'text',        'col_w'=>'3'],
    TP::F_MIK_FTP_PORT              => ['type'=>'number',      'col_w'=>'3'],
    TP::F_MIK_FTP_LOGIN             => ['type'=>'text',        'col_w'=>'3'],
    TP::F_MIK_FTP_PASSWD            => ['type'=>'text',        'col_w'=>'3'],
    TP::F_MIK_FTP_FOLDER            => ['type'=>'text'],
    TP::F_MIK_FTP_GETPATH           => ['type'=>'text'],
//  TP::F_CREATION_DATE             => ['type'=>'number'],
//  TP::F_CREATION_UID              => ['type'=>'number'],
//  TP::F_MODIFIED_DATE             => ['type'=>'number'],
//  TP::F_MODIFIED_UID              => ['type'=>'number'],
];

?>
<div class="mx-auto align-middle min-w-75 w-auto">
    <form method="post" action="<?=TP::URI_SAVE;?>/<?=$tp[TP::F_ID];?>">
        <?php foreach($fields as $name=>$opt): ?>
        <?php
            $value = $tp[$name] ?? '';
            $label = str_replace('_',' ', ucfirst($name));
        ?>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?=$label?></label>
            <div class="col-sm-<?=($opt['col_w'] ?? '9');?>">
                <?php if($opt['type']=='text' || $opt['type']=='number'): ?>
                    <input type="<?=$opt['type']?>"
                           class="form-control <?=($opt['class'] ?? '');?>"
                           name="<?=TP::POST_REC?>[<?=$name?>]"
                           value="<?=$value?>"
                           <?=isset($opt['step'])?"step=\"{$opt['step']}\"":''?>>
                <?php elseif($opt['type']=='textarea'): ?>
                    <textarea class="form-control"
                              name="<?=TP::POST_REC?>[<?=$name?>]"
                              rows="<?=($opt['rows']??3)?>"><?=$value?></textarea>
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
        </div>
        <?php endforeach; ?>
        <div class="row mb-3">
            <div class="col-sm-3 col-form-label text-secondary fs-6">
                <?=$tp[TP::F_CREATION_UID].' :: '.date(DATETIME_FORMAT, $tp[TP::F_CREATION_DATE]).'<br>'
                  .$tp[TP::F_MODIFIED_UID].' :: '.date(DATETIME_FORMAT, $tp[TP::F_MODIFIED_DATE]).'';?>
            </div>
            <div class="col-sm-9 mt-3 text-end">
                <button type="submit" class="btn btn-primary"><?=__('Сохранить');?></button>
                <a href="<?=TP::URI_INDEX;?>" class="btn btn-secondary"><?=__('Отмена');?></a>
            </div>
        </div>
    </form>
</div>