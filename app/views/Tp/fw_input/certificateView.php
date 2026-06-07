<?php
/**
 *  Project : my.ri.net.ua
 *  File    : certificateView.php
 *  Path    : app/views/Tp/fw_input/certificateView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of certificateView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\FwInput;
use config\tables\TP;
use billing\core\MikrotikDevice1;

$cfg = $data['certificate'] ?? [];
$certName = $data['cert_name'] ?? 'cert1';
$certs = $data['certs'] ?? [];
$currentCert = $data['current_cert'] ?? null;
$isValid = !empty($data['is_valid']);
$assignedServices = $data['assigned_services'] ?? [];
$anyValidCert = !empty($data['any_valid_cert']);
$validCertificates = $data['valid_certificates'] ?? [];



/**
 * Подключение файла-заголовка 
 */
$page_title = __('Setting up an SSL certificate for an encrypted connection | Настройка SSL сертификата для шифрованного подключения | Настроювання SSL сертифіката для шифрованого підключення');
$device_title = $data['title'] ?? '';
$device_description = $data['description'] ?? '';
include __DIR__ . '/header.php';

?>
<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_CERT ?>">
    <div class="mb-3">
        <div><strong><?= __('Target certificate | Целевой сертификат | Цільовий сертифікат') ?>:</strong> <?= h($certName) ?></div>
        <div><strong><?= __('Required attributes | Требуемые атрибуты | Потрібні атрибути') ?>:</strong>
            <?= h(($cfg['country'] ?? '') . ', ' . ($cfg['state'] ?? '') . ', ' . ($cfg['locality'] ?? '') . ', ' . ($cfg['organization'] ?? '')) ?>
        </div>
        <?php if ($validCertificates): ?>
            <div><strong><?= __('Valid certificates | Валидные сертификаты | Валідні сертифікати') ?>:</strong> <?= h(implode(', ', $validCertificates)) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($currentCert): ?>
        <div class="alert <?= $isValid ? 'alert-success' : 'alert-warning' ?>">
            <?= $isValid
                ? __('The certificate is valid | Сертификат валиден | Сертифікат валідний')
                : __('The certificate exists but does not satisfy the requirements | Сертификат существует, но не удовлетворяет требованиям | Сертифікат існує, але не відповідає вимогам') ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <?= __('The target certificate was not found | Целевой сертификат не найден | Цільовий сертифікат не знайдено') ?>
        </div>
    <?php endif; ?>

    <?php if ($assignedServices): ?>
        <div class="alert alert-info">
            <?= __('The certificate is already assigned to some services and if it needs to be replaced, it must be disabled or corrected manually, as this may lead to connection loss | Сертификат уже назначен некоторым сервисам и, если требуется его замена, то он должен быть отключён или исправлен вручную, поскольку это может приветсти к потере соединения | Сертифікат вже призначений деяким сервісам і, якщо потрібна його заміна, він повинен бути вимкнений або виправлений вручну, оскільки це може привести до втрати з\'єднання.') ?>
            <div class="mt-2"><?= h(implode(', ', $assignedServices)) ?></div>
        </div>
    <?php endif; ?>

    <div class="table-responsive mb-3">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th title="name"><?= __('Name | Имя | Ім’я') ?></th>
                    <th title="issued"><?= __('Issued | Подписан | Підписаний') ?></th>
                    <th title="days-valid"><?= __('invalid-after | недействительно после | invalid-після') ?></th>
                    <th title="trusted"><?= __('Trusted | Trusted | Trusted') ?></th>
                    <th title="invalid"><?= __('Invalid | Invalid | Invalid') ?></th>
                    <th title="revoked"><?= __('Revoked | отозван | відкликаний') ?></th>
                    <th title="key-usage"><?= __('Usage | Использование | Використання') ?></th>
                    <th title="status"><?= __('Status | Статус | Статус') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certs as $cert): ?>
                    <tr>
                        <td><?= h((string) ($cert['name'] ?? '')) ?></td>
                        <td><?= (MikrotikDevice1::is_certificate_signed($cert) ? "Yes":"No") ?></td>
                        <td><?= h((string) ($cert['invalid-after'] ?? '')) ?></td>
                        <td><?= h((string) ($cert['trusted'] ?? '')) ?></td>
                        <td><?= h((string) ($cert['invalid'] ?? '')) ?></td>
                        <td><?= h((string) ($cert['revoked'] ?? '')) ?></td>
                        <td><?= h((string) ($cert['key-usage'] ?? '')) ?></td>
                        <td><?= h((string) ($cert['status'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mb-3">
        <div><?= __('Planned action | Планируемое действие | Запланована дія') ?>:</div>
        <div class="font-monospace small">
            /certificate add name=<?= h($certName) ?> common-name=<?= h($certName) ?> key-size=<?= (int) ($cfg['key_size'] ?? 2048) ?> key-usage=<?= h((string) ($cfg['key_usage'] ?? '')) ?> trusted=yes days-valid=<?= (int) ($cfg['days_valid'] ?? 1825) ?>
        </div>
        <div class="font-monospace small">/certificate sign <?= h($certName) ?></div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::prev(FwInput::PHASE_CERT) ?>">
                <?= __('Back | Назад | Назад') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_CERT ?>">
                <?= __('Reread | Перечитать | Перечитати') ?>
            </a>
        </div>
        <div class="d-flex gap-2">
            <?php if (!$isValid && !$assignedServices): ?>
                <button type="submit" class="btn btn-primary"><?= __('Create and sign | Создать и подписать | Створити і підписати') ?></button>
            <?php endif; ?>
            <a class="btn btn-success <?= $anyValidCert ? '' : 'disabled' ?>" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::next(FwInput::PHASE_CERT) ?>">
                <?= __('Continue | Продолжить | Продовжити') ?>
            </a>
        </div>
    </div>
</form>