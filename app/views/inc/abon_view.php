<?php
/** app/views/inc/abon_view.php */
use config\tables\Abon;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * @var array $abon — массив с данными абонента
 *  Ключи соответствуют названиям колонок таблицы `abons`
 */

/** @var array $abon */
/** @var array $item */

/**
 * Поддержка функции Аккордеона
 * в ней передаваемый элемент $item
 */
if (isset($item) && !isset($abon)) {
    $abon = $item;
}
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
                            <div class="col small text-end font-monospace text-nowrap">
                                <?=__('Service boundaries');?>:
                            </div>
                            <div class="col small border text-center font-monospace"  title="<?=__('Number of prepaid days, upon crossing which send warning');?>." >
                                <?= $abon[Abon::F_DUTY_MAX_WARN] ?>
                            </div>
                            <div class="col small border text-center font-monospace" title="<?=__('Number of prepaid days, upon crossing which disable service');?>." >
                                <?= $abon[Abon::F_DUTY_MAX_OFF] ?>
                            </div>
                            <div class="col small border text-center font-monospace" title="<?=__('Automatically disable service');?>." >
                                <?= $abon[Abon::F_DUTY_AUTO_OFF] ? '[x]' : '[&nbsp;]' ?>
                            </div>
                            <div class="col small border text-center font-monospace" title="<?=__('Number of waiting days before disabling');?>." >
                                <?= $abon[Abon::F_DUTY_WAIT_DAYS] ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
