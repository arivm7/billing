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
/**
 * Данные из контроллера
 * @var array $abon 
 * @var array $rest 
 * */
$warn = AbonController::get_warn_status($abon, $rest);
$attr = AbonController::attribute_warning[$warn->name];
$prepayed = (is_null($rest[AbonRest::F_PREPAYED])
                ?   "-"
                :   ($rest[AbonRest::F_PREPAYED] < -(365*3)
                        ? "<span class='small' title='{$rest[AbonRest::F_PREPAYED]} ".__('days | дней | днів')."'>&lt;&lt;&lt;</span>"
                        :   ($rest[AbonRest::F_PREPAYED] > (365*3)
                                ? "<span class='small' title='{$rest[AbonRest::F_PREPAYED]} ".__('days | дней | днів')."'>&gt;&gt;&gt;</span>"
                                : $rest[AbonRest::F_PREPAYED]
                            )
                    )
            );
$attr_warn = (!$abon[Abon::F_DUTY_AUTO_OFF] || is_null($rest[AbonRest::F_PREPAYED])
        ? "class='text-secondary'"
        : ($abon[Abon::F_DUTY_MAX_WARN] > $rest[AbonRest::F_PREPAYED] ? AbonController::attribute_warning[DutyWarn::INFO->name] : "")) ;
$attr_off  = (!$abon[Abon::F_DUTY_AUTO_OFF] || is_null($rest[AbonRest::F_PREPAYED])
        ? "class='text-secondary'"
        : ($abon[Abon::F_DUTY_MAX_OFF] > $rest[AbonRest::F_PREPAYED] ? AbonController::attribute_warning[DutyWarn::NEED_OFF->name] : ""));
?>
<table class='table table-sm table-bordered table-hover small' style="table-layout: fixed; width: 100pt; max-width: 100%;">
<tr>
    <td nowrap style="text-align: center;" colspan="2">
        <font <?=$attr;?> title='<?=__('Balance on personal account | Остаток на лицевом счету | Залишок на особовому рахунку') . '.' . CR . '----' . CR . get_description_by_warn($warn);?>'><?=number_format($rest[AbonRest::F_REST],2,","," ");?></font>
    </td>
    <td nowrap style="width: 33%; text-align: center;">
        <font color=gray title='<?=__('Number of prepaid days | Количество предоплаченных дней | Кількість передплачених днів');?>' ><?=$prepayed;?></font>
    </td>
</tr>
<tr>
    <td nowrap style="width: 34%; text-align: center;" <?=($rest[AbonRest::F_SUM_PPMA] ? "class='text-success-emphasis'" : "class='text-secondary'");?> title='<?=__('Monthly subscription fee | Абонплата за месяц | Абонплата за місяць');?>'><?=number_format($rest[AbonRest::F_SUM_PPMA],(abs($rest[AbonRest::F_SUM_PPMA]) < 10 ? 2 : 0),","," ");?></td>
    <td nowrap style="width: 33%; text-align: center;" <?=($rest[AbonRest::F_SUM_PPDA] ? "class='text-success-emphasis'" : "class='text-secondary'");?> title='<?=__('Daily subscription fee | Абонплата за сутки | Абонплата за добу');?>'><?=number_format($rest[AbonRest::F_SUM_PPDA],(abs($rest[AbonRest::F_SUM_PPDA]) < 10 ? 2 : 0),","," ");?></td>
    <td nowrap style="width: 33%; text-align: center;" <?=($rest[AbonRest::F_SUM_PP30A] ? "class='text-success-emphasis'" : "class='text-warning'");?> title='<?=__('Total monthly subscription fee | Сумарная абонплата за месяц | Сумарна абонплата за місяць');?>'><?=number_format($rest[AbonRest::F_SUM_PP30A],(abs($rest[AbonRest::F_SUM_PP30A]) < 10 ? 2 : 0),","," ");?></td>
</tr>
<tr>
    <td nowrap style="width: 34%; text-align: center;" title='<?=__('Number of paid days %s, on crossing which notification is required | Число оплаченных дней %s, при пересечении котрого нужно уведомлять | Кількість оплачених днів %s, при перетині якої потрібно повідомляти', CR);?>'><span <?=$attr_warn;?>><?=$abon[Abon::F_DUTY_MAX_WARN];?></span></td>
    <td nowrap style="width: 33%; text-align: center;" title='<?=__('Number of paid days, %s on crossing which service must be disabled | Число оплаченных дней, %s при пересечении котрого нужно отключать | Кількість оплачених днів, %s при перетині якої потрібно відключати', CR);?>'><span <?=$attr_off;?>><?=$abon[Abon::F_DUTY_MAX_OFF];?></span></td>
    <td nowrap style="width: 33%; text-align: center;" title='<?=__('Automatically disable | Автоматически отключать | Автоматично відключати');?>'><?=get_html_CHECK($abon[Abon::F_DUTY_AUTO_OFF]);?></td>
</tr>
</table>
