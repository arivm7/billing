<?php
use app\models\PAStatus;
use config\tables\PA;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * @var array $item — массив с данными по одной записи prices_apply
 * Ключи соответствуют константам PA::F_*
 */
?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <table <?= TABLE_ATTRIBUTES; ?> >
                <tr>
                    <td align='right'>AID:</td><td><span class="text text-secondary small"><?= h($item[PA::F_ABON_ID]); ?></span><?= ' | ' . __user(abon_id: $item[PA::F_ABON_ID]) . ' | ' . __abon(abon_id: $item[PA::F_ABON_ID]); ?></td>
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
                                    <td align='right'>
                                        <span class="text font-monospace">
                                            <?= $item[PA::F_CLOSED] ? '<span class="badge bg-secondary">[x]</span>' : '<span class="badge bg-success">[ ]</span>' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= __('Начисление'); ?>:</td>
                                    <td><?= number_format($item[PA::F_COST_VALUE], 2, ',', ' ') . ' грн' ?></td>
                                </tr>

                                <?php if (__pa_age($item) == PAStatus::CURRENT) : ?>
                                    <tr>
                                        <td><?= __('Абонплата'); ?>:</td>
                                        <td>
                                            <?= ($item[PA::F_PPMA_VALUE] ? number_format($item[PA::F_PPMA_VALUE], 2, ',', ' ') . " " . __('грн/мес') : ''); ?>
                                            <?= ($item[PA::F_PPDA_VALUE] ? number_format($item[PA::F_PPDA_VALUE], 2, ',', ' ') . " " . __('грн/сут') : ''); ?>
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
            <a href="?<?= PA::F_ID ?>=<?= $item[PA::F_ID] ?>&action=pause"
               class="btn btn-secondary btn-sm"
               onclick="return confirm(__('Отправить запрос на остановку услуги?'))">&#9208; <?= __('Поставить на паузу'); ?></a>
        </div>
    </div>
</div>
