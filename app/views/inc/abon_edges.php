<?php
/*
 *  Project : my.ri.net.ua
 *  File    : abon_edges.php
 *  Path    : app/views/inc/abon_edges.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Маленькая таблица 3х3 
 * показывает граничные значения остатков и параметров обслуживания.
 * Используется в контроллере просмотра списка абонентов AbonController.php,
 * при формировании списка абонентов,
 * для отображения остатков на ЛС и границ обслуживания абонента.
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use app\controllers\AbonController;
use config\tables\Abon;
use config\tables\AbonRest;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var AbonController $this */
/** @var array $data */
$warn = AbonController::get_warn_status($data);
$attr = AbonController::attribute_warning[$warn->name];
$prepayed = (is_null($data[AbonRest::F_PREPAYED])
                ?   "-"
                :   ($data[AbonRest::F_PREPAYED] < -(365*3)
                        ? "<span class='small' title='{$data[AbonRest::F_PREPAYED]} ".__('дней')."'>&lt;&lt;&lt;</span>"
                        :   ($data[AbonRest::F_PREPAYED] > (365*3)
                                ? "<span class='small' title='{$data[AbonRest::F_PREPAYED]} ".__('дней')."'>&gt;&gt;&gt;</span>"
                                : $data[AbonRest::F_PREPAYED]
                            )
                    )
            );
$attr_warn = (!$data[Abon::F_DUTY_AUTO_OFF] || is_null($data[AbonRest::F_PREPAYED])
        ? "class='text-secondary'"
        : ($data[Abon::F_DUTY_MAX_WARN] > $data[AbonRest::F_PREPAYED] ? AbonController::attribute_warning[DutyWarn::INFO->name] : "")) ;
$attr_off  = (!$data[Abon::F_DUTY_AUTO_OFF] || is_null($data[AbonRest::F_PREPAYED])
        ? "class='text-secondary'"
        : ($data[Abon::F_DUTY_MAX_OFF] > $data[AbonRest::F_PREPAYED] ? AbonController::attribute_warning[DutyWarn::NEED_OFF->name] : ""));
?>
<table class='table table-sm table-bordered table-hover small' style="table-layout: fixed; width: 100pt; max-width: 100%;">
<tr>
    <td nowrap style="text-align: center;" colspan="2">
        <font <?=$attr;?> title='<?=__('Остаток на лицевом счету.') . CR . '----' . CR . get_description_by_warn($warn);?>'><?=number_format($data[AbonRest::F_REST],2,","," ");?></font>
    </td>
    <td nowrap style="width: 33%; text-align: center;">
        <font color=gray title='<?=__('Количество предоплаченных дней');?>' ><?=$prepayed;?></font>
    </td>
</tr>
<tr>
    <td nowrap style="width: 34%; text-align: center;" <?=($data[AbonRest::F_SUM_PPMA] ? "" : "class='text-secondary'");?> title='<?=__('Абонплата за месяц');?>'><?=number_format($data[AbonRest::F_SUM_PPMA],(abs($data[AbonRest::F_SUM_PPMA]) < 10 ? 2 : 0),","," ");?></td>
    <td nowrap style="width: 33%; text-align: center;" <?=($data[AbonRest::F_SUM_PPDA] ? "" : "class='text-secondary'");?> title='<?=__('Абонплата за сутки');?>'><?=number_format($data[AbonRest::F_SUM_PPDA],(abs($data[AbonRest::F_SUM_PPDA]) < 10 ? 2 : 0),","," ");?></td>
    <td nowrap style="width: 33%; text-align: center;" <?=($data[AbonRest::F_SUM_PP30A] ? "" : "class='text-secondary'");?> title='<?=__('Сумарная абонплата за месяц');?>'><?=number_format($data[AbonRest::F_SUM_PP30A],(abs($data[AbonRest::F_SUM_PP30A]) < 10 ? 2 : 0),","," ");?></td>
</tr>
<tr>
    <td nowrap style="width: 34%; text-align: center;" title='<?=__('Число оплаченных дней, %s при пересечении котрого нужно уведомлять', CR);?>'><span <?=$attr_warn;?>><?=$data[Abon::F_DUTY_MAX_WARN];?></span></td>
    <td nowrap style="width: 33%; text-align: center;" title='<?=__('Число оплаченных дней, %s при пересечении котрого нужно отключать', CR);?>'><span <?=$attr_off;?>><?=$data[Abon::F_DUTY_MAX_OFF];?></span></td>
    <td nowrap style="width: 33%; text-align: center;" title='<?=__('Автоматически отключать');?>'><?=get_html_CHECK($data[Abon::F_DUTY_AUTO_OFF]);?></td>
</tr>
</table>