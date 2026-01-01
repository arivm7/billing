<?php
/*
 *  Project : my.ri.net.ua
 *  File    : pa_view.php
 *  Path    : app/views/inc/pa_view.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of pa_view.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use app\models\AbonModel;
use billing\core\Api;
use config\tables\Module;
use config\tables\PA;
use billing\core\base\Lang;
use config\tables\TP;

Lang::load_inc(__FILE__);

/**
 * @var array $item — массив с данными по одной записи prices_apply
 * Ключи соответствуют константам PA::F_*
 */

// !!! Не нормально. Данные должны приходить готовыми
$model = new AbonModel();
$tp = $model->get_tp($item[PA::F_TP_ID]);

?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <table <?= TABLE_ATTRIBUTES; ?> >
                <tr>
                    <td class="text-end">AID:</td>
                    <td>
                        <div class='d-flex justify-content-between align-items-center'>
                            <div>
                                <span class="text text-secondary small"><?= h($item[PA::F_ABON_ID]); ?></span><?= ' | ' . __user(abon_id: $item[PA::F_ABON_ID]) . ' | ' . __abon(abon_id: $item[PA::F_ABON_ID]); ?>
                            </div>
                            <?php if (can_edit(Module::MOD_PA)) : ?>
                                <!-- Кнопка редактирования ПФ -->
                                <a href="<?=PA::URI_EDIT;?>/<?=$item[PA::F_ID];?>" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-pencil-square"></i> <?= __('Редактировать'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <fieldset class="border-1"><legend><span class="text-secondary small"><?= h($item[PA::F_PRICE_ID]) ?></span> <?= h($item[PA::F_PRICE_TITLE] ?? '') ?></legend>
                            <table <?= TABLE_ATTRIBUTES; ?>>
                                <tr>
                                    <td>
                                        <span class="text font-monospace">
                                            <?= $item[PA::F_DATE_START] ? date(DATE_FORMAT, $item[PA::F_DATE_START]) : '____-__-__' ?> |
                                            <?= $item[PA::F_DATE_END] ? date(DATE_FORMAT, $item[PA::F_DATE_END]) : '____-__-__' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text font-monospace">
                                            <?php if ($item[PA::F_CLOSED]) : ?>
                                                <span class='badge bg-secondary' title='<?=__('Период начисления полностью закрыт');?>'>[x]</span>
                                            <?php else : ?>
                                                <span class='badge bg-success' title='<?=__('Начисление активно или на паузе');?>'>[ ]</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php if (can_use(Module::MOD_PA)) : ?>
                                    <tr>
                                        <td><?= __('Начисление'); ?>:</td>
                                        <td><?= number_format($item[PA::F_COST_VALUE], 2, ',', ' ') . ' грн' ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (__pa_age($item) == \PAStatus::CURRENT) : ?>
                                    <tr>
                                        <td><?= __('Абонплата'); ?>:</td>
                                        <td>
                                            <?= ($item[PA::F_PPMA_VALUE] ? number_format($item[PA::F_PPMA_VALUE], 2, ',', ' ') . " " . __('грн/мес') : ''); ?>
                                            <?= ($item[PA::F_PPDA_VALUE] ? number_format($item[PA::F_PPDA_VALUE], 2, ',', ' ') . " " . __('грн/сут')
                                                . '<span class=\'text-secondary\'> | ' . number_format($item[PA::F_PPDA_VALUE] * 30, 2, ',', ' ') . " " . __('грн/30дней') . '</span>' : ''); ?>
                                        </td>
                                    </tr>
                                    <?php if ($item[PA::F_NET_IP_SERVICE]) : ?>
                                        <?php if ($item[PA::F_NET_NAT11]) : ?>
                                            <tr>
                                                <td>NAT 1:1:</td><td><?= h($item[PA::F_NET_NAT11]) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><?= __('IP'); ?>:</td><td><?= h($item[PA::F_NET_IP]) ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= __('Mask'); ?>:</td><td><?= h($item[PA::F_NET_MASK]) ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= __('Gateway'); ?>:</td><td><?= h($item[PA::F_NET_GATEWAY]) ?></td>
                                        </tr>
                                        <tr>
                                            <td>DNS 1:</td><td><?= h($item[PA::F_NET_DNS1]) ?></td>
                                        </tr>
                                        <tr>
                                            <td>DNS 2:</td><td><?= h($item[PA::F_NET_DNS2]) ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= __('MAC-адрес'); ?>:</td><td><?= h($item[PA::F_NET_MAC]) ?></td>
                                        </tr>
                                        <?php if ($item[PA::F_NET_ON_ABON_IP]) : ?>
                                            <tr>
                                                <td><?= __('IP на оборудовании абонента'); ?>:</td>
                                                <td><?= h($item[PA::F_NET_ON_ABON_IP]) ?> / <?= h($item[PA::F_NET_ON_ABON_MASK]); ?> / <?= h($item[PA::F_NET_ON_ABON_GATE]); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($item[PA::F_COORD_GMAP]): ?>
                                            <tr>
                                                <td>Координаты:</td>
                                                <td><a href="https://maps.google.com/?q=<?= urlencode($item[PA::F_COORD_GMAP]) ?>" target="_blank"><?= h($item[PA::F_COORD_GMAP]) ?></a></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </table>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Панель действий -->
        <div class="card-footer d-flex gap-2">
            <?php if (can_edit(Module::MOD_PA)) : ?>
                <span class="badge text-bg-secondary mt-3 fs-6">
                    <?php if (!$item[PA::F_CLOSED]) : ?>
                        <a href="<?= Api::URI_CMD; ?>?<?=Api::F_CMD;?>=<?=Api::CMD_PA_CLOSE;?>&<?=Api::F_PA_ID;?>=<?= $item[PA::F_ID]; ?>&<?=Api::F_ABON_OFF_ON_TP;?>=0"
                            class="btn btn-outline-info btn-sm"
                            onclick="return confirm('<?=__('Вы точно хотите остановить услугу и закрыть прайсовый фрагмент?');?>')">&#9209; <?= __('Закрыть прайс'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if (!$item[PA::F_CLOSED]) : ?>
                        <!-- Статус IP-MAC из ARP-таблицы микротика -->
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
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>