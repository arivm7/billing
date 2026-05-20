<?php
/**
 *  Project : my.ri.net.ua
 *  File    : neighborsView.php
 *  Path    : app/views/Tp/fw_input/neighborsView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of neighborsView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\FwInput;
use config\tables\TP;

$lanName = $data['lan_name'] ?? 'LAN';
$wanName = $data['wan_name'] ?? 'WAN';
$current = $data['current'] ?? [];
$currentValue = $data['current_value'] ?? '';
$hasRequiredLists = !empty($data['has_required_lists']);
$defaultValue = $data['neighbor_default'] ?? $lanName;



/**
 * Подключение файла-заголовка 
 */
$page_title = __('Specify networks for visibility of this device and scanning of neighboring devices | Указание сетей для видимости этого устроства и сканирования соседних устройств | Вказівка ​​мереж для видимості цього пристрою та сканування сусідніх пристроїв');
$device_title = $data['title'] ?? '';
$device_description = $data['description'] ?? '';
include __DIR__ . '/header.php';

?>
<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_NEIGHBORS ?>">
    <?php if (!$hasRequiredLists): ?>
        <div class="alert alert-danger">
            <?= __('LAN/WAN lists were not found. Return to the previous step and configure interface lists. | Списки LAN/WAN не найдены. Вернитесь на предыдущий шаг и настройте списки интерфейсов. | Списки LAN/WAN не знайдені. Поверніться на попередній крок і налаштуйте списки інтерфейсів.') ?>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <div><strong><?= __('Current discover-interface-list | Текущий discover-interface-list | Поточний discover-interface-list') ?>:</strong> <?= h($currentValue) ?></div>
            <div><strong><?= __('Current status | Текущий статус | Поточний стан') ?>:</strong> <?= h(json_encode($current, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Required discover-interface-list | Требуемый discover-interface-list | Потрібний discover-interface-list') ?></label>
            <div class="col-9">
                <select class="form-select" name="discover_interface_list">
                    <option value="<?= h($lanName) ?>" <?= (($currentValue ?: $defaultValue) === $lanName) ? 'selected' : '' ?>><?= h($lanName) ?></option>
                    <option value="<?= h($wanName) ?>" <?= (($currentValue ?: $defaultValue) === $wanName) ? 'selected' : '' ?>><?= h($wanName) ?></option>
                    <option value="all" <?= (($currentValue ?: $defaultValue) === 'all') ? 'selected' : '' ?>>all</option>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::prev(FwInput::PHASE_NEIGHBORS) ?>">
                <?= __('Back | Назад | Назад') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_NEIGHBORS ?>">
                <?= __('Reread | Перечитать | Перечитати') ?>
            </a>
        </div>
        <div class="d-flex gap-2">
            <?php if ($hasRequiredLists): ?>
                <button type="submit" class="btn btn-primary"><?= __('Apply required state | Применить нужный статус | Застосувати потрібний стан') ?></button>
            <?php endif; ?>
            <a class="btn btn-success" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::next(FwInput::PHASE_NEIGHBORS) ?>">
                <?= __('Continue | Продолжить | Продовжити') ?>
            </a>
        </div>
    </div>
</form>