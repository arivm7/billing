<?php
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
            <h4><?= __('Параметры абонентского подключеня') ?></h4>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered table-hover" >
                <!-- ID абонента -->
                <tr>
                    <td><?= __('Договор №'); ?></td>
                    <td><?= h($abon['id']); ?>
                        <?php if (can_use(Module::MOD_ABON)): ?>
                            <?php if (!empty($abon['id_hash'])): ?>
                                <small class="text-muted"> | (hash: <?= h($abon['id_hash']); ?>)</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- Адрес подключения -->
                <tr>
                    <td><strong>Адрес подключения:</strong></td>
                    <td><?= nl2br(h($abon['address'])); ?></td>
                </tr>
                <!-- Координаты Google Maps -->
                <?php if (!empty($abon['coord_gmap'])): ?>
                    <tr>
                        <td><strong>Координаты (Google Maps):</strong></td>
                        <td><a href="https://maps.google.com/?q=<?= urlencode($abon['coord_gmap']); ?>" target="_blank"><?= h($abon['coord_gmap']); ?></a></td>
                    </tr>
                <?php endif; ?>
                <!-- Дата подключения -->
                <?php if ($abon['date_join']) : ?>
                <tr>
                    <td><strong>Дата подключения:</strong></td>
                    <td><?= date('d.m.Y', $abon['date_join']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="card-footer">
            <div class="row container-fluid">
                <div class="col justify-content-start">
                    <!-- Флаг "Плательщик" -->
                    <strong>Статус услуги:&nbsp;</strong>
                    <?php if ($abon['is_payer']): ?>
                        <span class="badge bg-success">Подключена</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Отключён</span>
                    <?php endif; ?>
                </div>
                <div class="col justify-content-end">
                    <!-- Настройки задолженности -->
                    <?php if ($abon['is_payer']): ?>
                        <div class="row">
                            <div class="col small text-end font-monospace text-nowrap">
                                Границы обслуживания:
                            </div>
                            <div class="col small border text-center font-monospace"  title="Количество предоплаченных дней, &#10;при пересечении которых, отправлять &#10;предупреждение о необходимоси платежа." >
                                <?= $abon['duty_max_warn'] ?>
                            </div>
                            <div class="col small border text-center font-monospace" title="Количество предоплаченных дней, &#10;при пересечении которых отключать услугу." >
                                <?= $abon['duty_max_off'] ?>
                            </div>
                            <div class="col small border text-center font-monospace" title="Автоматически отключать услугу." >
                                <?= $abon['duty_auto_off'] ? '[x]' : '[&nbsp;]' ?>
                            </div>
                            <div class="col small border text-center font-monospace" title="Количество дней ожидания перед отключением." >
                                <?= $abon['duty_wait_days'] ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
