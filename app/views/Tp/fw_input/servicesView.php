<?php
/**
 *  Project : my.ri.net.ua
 *  File    : servicesView.php
 *  Path    : app/views/Tp/fw_input/servicesView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of servicesView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\App;
use config\FwInput;
use config\tables\TP;

$serviceRows = $data['service_rows'] ?? [];
$validCertificates = $data['valid_certificates'] ?? [];
$certRequiredOk = !empty($data['cert_required_ok']);
$certName = $data['cert_name'] ?? 'cert1';
$servicesValid = !empty($data['services_valid']);
$servicesErrors = $data['services_errors'] ?? [];



/**
 * Подключение файла-заголовка 
 */
$page_title = __('Configure device services to connect to this device. Disable unnecessary things. Try not to be paranoid | Настройка сервисов устройства для подключения к этому устройства. Отключите лишнее. Постарайтесь без паранои | Налаштування сервісів пристрою для підключення до цього пристрою. Вимкніть зайве. Постарайтеся без параної');
$device_title = $data['title'] ?? '';
$device_description = $data['description'] ?? '';
include __DIR__ . '/header.php';

?>
<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_IP_SERVICES ?>">
    <?php if (!$certRequiredOk): ?>
        <div class="alert alert-danger">
            <?= __('A valid certificate is required before configuring services. Return to the previous step. | Перед настройкой сервисов требуется валидный сертификат. Вернитесь на предыдущий шаг. | Перед налаштуванням сервісів потрібен валідний сертифікат. Поверніться на попередній крок.') ?>
        </div>
    <?php else: ?>
        <?php if (!$servicesValid): ?>
            <div class="alert alert-warning">
                <?php foreach ($servicesErrors as $error): ?>
                    <div><?= h($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th><?= __('Service | Сервис | Сервіс') ?></th>
                        <th><?= __('Current port | Текущий порт | Поточний порт') ?></th>
                        <th><?= __('New port | Новый порт | Новий порт') ?></th>
                        <th><?= __('Enabled | Включён | Увімкнений') ?></th>
                        <th><?= __('Certificate | Сертификат | Сертифікат') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($serviceRows as $row): ?>
                        <tr>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['current_port']) ?></td>
                            <td>
                                <input type="number"
                                       class="form-control"
                                       name="svc[<?= h($row['name']) ?>][port]"
                                       value="<?= h((string) $row['port']) ?>">
                            </td>
                            <td class="text-center">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="svc[<?= h($row['name']) ?>][ena]"
                                       value="1"
                                       <?= !empty($row['enabled']) ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <?php if (in_array($row['name'], App::get_config('services_ssl'), true)): ?>
                                    <select class="form-select" name="svc[<?= h($row['name']) ?>][certificate]">
                                        <?php foreach ($validCertificates as $cert): ?>
                                            <option value="<?= h($cert) ?>" <?= $row['certificate'] === $cert ? 'selected' : '' ?>>
                                                <?= h($cert) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php if (!$validCertificates): ?>
                                            <option value="<?= h($certName) ?>"><?= h($certName) ?></option>
                                        <?php endif; ?>
                                    </select>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::prev(FwInput::PHASE_IP_SERVICES) ?>">
                <?= __('Back | Назад | Назад') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_IP_SERVICES ?>">
                <?= __('Reread | Перечитать | Перечитати') ?>
            </a>
        </div>
        <div class="d-flex gap-2">
            <?php if ($certRequiredOk): ?>
                <button type="submit" class="btn btn-primary"><?= __('Change | Изменить | Змінити') ?></button>
            <?php endif; ?>
            <a class="btn btn-success <?= ($certRequiredOk && $servicesValid) ? '' : 'disabled' ?>" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::next(FwInput::PHASE_IP_SERVICES) ?>">
                <?= __('Continue | Продолжить | Продовжити') ?>
            </a>
        </div>
    </div>
</form>