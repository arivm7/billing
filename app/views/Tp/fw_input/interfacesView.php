<?php
/**
 *  Project : my.ri.net.ua
 *  File    : interfacesView.php
 *  Path    : app/views/Tp/fw_input/interfacesView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of interfacesView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\FwInput;
use config\tables\TP;



/**
 * Данные из контроллера
 * 
 * @var string $phase
 * @var array $data
 * 
 * Структура массива 
 * $data[
 *      'title'              => (string),
 *      'description'        => (string),
 *      'session'            => (array),
 *      'lists'              => (array),
 *      'interfaces'         => (array),
 *      'lan_name'           => (string),
 *      'wan_name'           => (string),
 *      'has_required_lists' => (bool),
 *      'has_conflict'       => (bool),
 *      'conflicts'          => (array),
 * ]
 * 
 */



$lanName = $data['lan_name'] ?? 'LAN';
$wanName = $data['wan_name'] ?? 'WAN';
$lists = $data['lists'] ?? [];
$interfaces = $data['interfaces'] ?? [];
$conflicts = $data['conflicts'] ?? [];
$hasRequiredLists = !empty($data['has_required_lists']);
$hasConflict = !empty($data['has_conflict']);

/**
 * Подключение файла-заголовка 
 */
$page_title = __('Setting LAN/WAN interface lists | Установка списков интерфейсов LAN / WAN | Встановлення списків інтерфейсів LAN/WAN');
$device_title = $data['title'] ?? '';
$device_description = $data['description'] ?? '';
include __DIR__ . '/header.php';

?>
<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_INTERFACE_LIST ?>">
    <div class="mb-3">
        <div><strong><?= __('Lists found | Найдены списки | Знайдені списки') ?>:</strong> <?= h(implode(', ', $lists)) ?></div>
        <div><strong><?= __('Required | Требуются | Потрібні') ?>:</strong> <?= h($lanName) ?>, <?= h($wanName) ?></div>
    </div>

    <?php if (!$hasRequiredLists): ?>
        <div class="alert alert-warning">
            <?= __('The required LAN/WAN lists are missing. Click apply to create and refill them. | Требуемые списки LAN/WAN отсутствуют. Нажмите выполнить, чтобы создать и заполнить их. | Потрібні списки LAN/WAN відсутні. Натисніть виконати, щоб створити і заповнити їх.') ?>
        </div>
    <?php endif; ?>

    <?php if ($hasConflict): ?>
        <div class="alert alert-danger">
            <?= __('Some interfaces are simultaneously assigned to LAN and WAN. This is invalid and will be corrected by refilling the lists. | Некоторые интерфейсы одновременно включены в LAN и WAN. Это недопустимо и будет исправлено при перезаполнении списков. | Деякі інтерфейси одночасно включені в LAN і WAN. Це неприпустимо і буде виправлено при повторному заповненні списків.') ?>
            <div class="mt-2"><?= h(implode(', ', $conflicts)) ?></div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th><?= __('Interface | Интерфейс | Інтерфейс') ?></th>
                    <th><?= __('Type | Тип | Тип') ?></th>
                    <th><?= __('Running | Работает | Працює') ?></th>
                    <th><?= __('Disabled | Отключен | Вимкнений') ?></th>
                    <th><?= __('List | Список | Список') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interfaces as $iface): ?>
                    <tr>
                        <td><?= h($iface['name']) ?></td>
                        <td><?= h($iface['type']) ?></td>
                        <td><?= h($iface['running']) ?></td>
                        <td><?= h($iface['disabled']) ?></td>
                        <td>
                            <select class="form-select" name="iflist[<?= h($iface['name']) ?>]">
                                <option value="--" <?= $iface['selected'] === '--' ? 'selected' : '' ?>>--</option>
                                <option value="<?= h($wanName) ?>" <?= $iface['selected'] === $wanName ? 'selected' : '' ?>><?= h($wanName) ?></option>
                                <option value="<?= h($lanName) ?>" <?= $iface['selected'] === $lanName ? 'selected' : '' ?>><?= h($lanName) ?></option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::prev(FwInput::PHASE_INTERFACE_LIST) ?>">
                <?= __('Back | Назад | Назад') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_INTERFACE_LIST ?>">
                <?= __('Reread | Перечитать | Перечитати') ?>
            </a>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __('Fix interface lists | Исправить списки интерфейсов | Виправити списки інтерфейсів') ?></button>
            <a class="btn btn-success <?= (!$hasRequiredLists || $hasConflict) ? 'disabled' : '' ?>" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::next(FwInput::PHASE_INTERFACE_LIST) ?>">
                <?= __('Continue | Продолжить | Продовжити') ?>
            </a>
        </div>
    </div>
</form>