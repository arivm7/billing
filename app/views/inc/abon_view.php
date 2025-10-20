<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : abon_view.php
 *  Path    : app/views/inc/abon_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Карточка абонента
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use app\controllers\AbonController;
use config\tables\Abon;
use config\tables\Module;
use config\tables\AbonRest;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/** @var array $abon — массив с данными абонента. Ключи соответствуют названиям колонок таблицы `abons` */
/** @var array $item */

/**
 * Поддержка функции Аккордеона, в ней передаваемый элемент $item
 */
if (isset($item) && !isset($abon)) {
    $abon = $item;
}

$rest = $abon[AbonRest::TABLE];

/**
 * Добавляет в ассоциативный массив записи абонента поля:
 *   PP30A    -- Активная абонплата за 30 дней
 *   PP01A    -- Активная абонплата за 1 день
 *   REST     -- Остаток на лицевом счету
 *   PREPAYED -- Количество предоплаченных дней
 */
update_rest_fields($rest);

/**
 * Возвращает статус для предупреждения абонента
 * в зависимости от оставшихся предоплаченных дней
 */
$dutyWarn = get_abon_warn_status($rest, $abon);

/**
 * html-атрибуты статуса абонента
 */
$abon_attr = AbonController::attribute_warning[$dutyWarn->name];


/**
 * Формирование отображения предоплаченных дней
 * - если NULL, то "-"
 * - если меньше -1095 (3 года), то "<<<" с подсказкой
 * - если больше +1095 (3 года), то ">>>" с подсказкой
 * - иначе просто число
 */
$prepayed_html = 
    (is_null($rest[AbonRest::F_PREPAYED])
        ?   "-"
        :   ($rest[AbonRest::F_PREPAYED] < -(365*3)
                ? "<span class='small' title='{$rest[AbonRest::F_PREPAYED]} ".__('дней')."'>&lt;&lt;&lt;</span>"
                :   ($rest[AbonRest::F_PREPAYED] > (365*3)
                        ? "<span class='small' title='{$rest[AbonRest::F_PREPAYED]} ".__('дней')."'>&gt;&gt;&gt;</span>"
                        : $rest[AbonRest::F_PREPAYED]
                    )
            )
    );

/**
 * Формирование атрибутов для отображения остатков и границ **обслуживания**
 * - если автоотключение отключено или предоплаченные дни NULL, то серый цвет
 * - если предоплаченные дни меньше границы предупреждения, то цвет INFO
 * - если предоплаченные дни меньше границы отключения, то цвет WARNING
 */
// $attr_warn = 
//     (!$abon[Abon::F_DUTY_AUTO_OFF] || is_null($rest[AbonRest::F_PREPAYED])
//         ? "class='text-secondary'"
//         : ($abon[Abon::F_DUTY_MAX_WARN] > $rest[AbonRest::F_PREPAYED] ? AbonController::attribute_warning[DutyWarn::INFO->name] : "")
//     );
$attr_warn = ($abon[Abon::F_DUTY_MAX_WARN] > $rest[AbonRest::F_PREPAYED] ? AbonController::attribute_warning[DutyWarn::INFO->name] : "");

/**
 * Формирование атрибута для отображения границы **отключения**
 * - если автоотключение отключено или предоплаченные дни NULL, то серый цвет
 * - если предоплаченные дни меньше границы отключения, то цвет WARNING
 */
// $attr_off  = 
//     (!$abon[Abon::F_DUTY_AUTO_OFF] || is_null($rest[AbonRest::F_PREPAYED])
//         ?   "class='text-secondary'"
//         :   ( $abon[Abon::F_DUTY_MAX_OFF] > $rest[AbonRest::F_PREPAYED] 
//                 ? AbonController::attribute_warning[DutyWarn::NEED_OFF->name] 
//                 : ""
//             )
//     );
$attr_off  = 
        ( $abon[Abon::F_DUTY_MAX_OFF] > $rest[AbonRest::F_PREPAYED] 
                ? AbonController::attribute_warning[DutyWarn::NEED_OFF->name] 
                : ""
        );

?>
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header">
            <h4><?= __('Subscriber connection parameters') ?></h4>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered table-hover" >
                <!-- ID абонента -->
                <tr>
                    <td><?= __('Contract №'); ?></td>
                    <td><?= h($abon[Abon::F_ID]); ?>
                        <?php if (can_use(Module::MOD_ABON)): ?>
                            <?php if (!empty($abon[Abon::F_ID_HASH])): ?>
                                <small class="text-muted"> | (hash: <?= h($abon[Abon::F_ID_HASH]); ?>)</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- Адрес подключения -->
                <tr>
                    <td><strong><?=__('Connection address');?>:</strong></td>
                    <td><?= cleaner_html($abon[Abon::F_ADDRESS]); ?></td>
                </tr>
                <!-- Координаты Google Maps -->
                <?php if (!empty($abon[Abon::F_COORD_GMAP])): ?>
                    <tr>
                        <td><strong><?=__('Coordinates');?> (Google Maps):</strong></td>
                        <td><a href="https://maps.google.com/?q=<?= urlencode($abon[Abon::F_COORD_GMAP]); ?>" target="_blank"><?= h($abon[Abon::F_COORD_GMAP]); ?></a></td>
                    </tr>
                <?php endif; ?>
                <!-- Дата подключения -->
                <?php if ($abon[Abon::F_DATE_JOIN]) : ?>
                <tr>
                    <td><strong><?=__('Connection date');?>:</strong></td>
                    <td><?= date('d.m.Y', $abon[Abon::F_DATE_JOIN]); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($abon[Abon::F_IS_PAYER]): ?>
                <!-- Остаток на лицевом счете -->
                <tr>
                    <td><strong><?=__('Balance');?>:</strong></td>
                    <td><span <?=$abon_attr;?> title='<?=__('Остаток на лицевом счету.') . CR . '----' . CR . get_description_by_warn($dutyWarn);?>'><?=number_format($rest[AbonRest::F_REST], 2, ",", " ");?></span></td>
                </tr>
                <!-- Количество предоплаченных дней -->
                <tr>
                    <td><strong><?=__('Prepaid days');?>:</strong></td>
                    <td>
                        <span class='text-secondary' title='<?=__('Количество предоплаченных дней');?>' ><?=$prepayed_html;?></span>
                    </td>
                </tr>
                <!-- Сумарная абонплата за месяц -->
                <tr>
                    <td><strong><?=__('Текущая абонплата');?>:</strong></td>
                    <td>
                        <span 
                            <?=($rest[AbonRest::F_SUM_PP30A] ? "" : "class='text-secondary'");?> 
                            title='<?=__('Сумарная абонплата за месяц, включает подневную и помесячную абонплату');?>' >
                            <?=number_format(
                                $rest[AbonRest::F_SUM_PP30A],
                                (abs($rest[AbonRest::F_SUM_PP30A]) < 1 
                                            ? 4 
                                            : (abs($rest[AbonRest::F_SUM_PP30A]) < 10 
                                                ? 2 
                                                : 0)),
                                ",",
                                " ");?> <span class="text-secondary"><?=__('грн/30 дней');?></span>
                        </span>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="card-footer">
            <div class="row container-fluid">
                <div class="col justify-content-start">
                    <!-- Флаг "Плательщик" -->
                    <strong><?=__('Service status');?>:&nbsp;</strong>
                    <?php if ($abon[Abon::F_IS_PAYER]): ?>
                        <span class="badge bg-success"><?=__('Enabled');?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?=__('Disabled');?></span>
                    <?php endif; ?>
                </div>
                <div class="col justify-content-end">
                    <!-- Настройки задолженности -->
                    <?php if ($abon[Abon::F_IS_PAYER]): ?>
                        <div class="row">
                            <div class="col text-end font-monospace text-nowrap">
                                <?=__('Service boundaries');?>:
                            </div>
                            <div class="col border text-center font-monospace"  title="<?=__('Number of prepaid days, upon crossing which send warning');?>." >
                                <span <?=$attr_warn;?>><?=$abon[Abon::F_DUTY_MAX_WARN];?></span>
                            </div>
                            <div class="col border text-center font-monospace" title="<?=__('Number of prepaid days, upon crossing which disable service');?>." >
                                <span <?=$attr_off;?>><?=$abon[Abon::F_DUTY_MAX_OFF];?></span>
                            </div>
                            <div class="col border text-center font-monospace" title="<?=__('Automatically disable service');?>." >
                                <?= $abon[Abon::F_DUTY_AUTO_OFF] ? "[<span class='text-info'>x</span>]" : '[&nbsp;]' ?>
                            </div>
                            <div class="col border text-center font-monospace" title="<?=__('Number of waiting days before disabling');?>." >
                                <?= $abon[Abon::F_DUTY_WAIT_DAYS] ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>