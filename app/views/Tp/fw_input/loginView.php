<?php
/**
 *  Project : my.ri.net.ua
 *  File    : loginView.php
 *  Path    : app/views/Tp/fw_input/loginView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of loginView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\FwInput;
use config\tables\TP;

$form = $data['form'] ?? [];
$tpList = $data['tp_list'] ?? [];


/**
 * Подключение файла-заголовка 
 */
$page_title = __('Connecting to a Mikrotik device for configure Firewall Input | Подключение к устройству Микротик для настройки Firewall Input | Підключення до пристрою Мікротик для налаштування Firewall Input');
$device_title = $data['title'] ?? '';
$device_description = $data['description'] ?? '';
include __DIR__ . '/header.php';
?>
<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_LOGIN ?>">
    <div class="row mb-3">
        <label class="col-3 col-form-label"><?= __('Technical site | Техплощадка | Техмайданчик') ?></label>
        <div class="col-9">
            <select class="form-select" name="fw[tp_id]">
                <option value="0"><?= __('Not selected | Не выбрано | Не вибрано') ?></option>
                <?php foreach ($tpList as $tp): ?>
                    <option value="<?= (int) $tp[TP::F_ID] ?>" <?= ((int) ($form['tp_id'] ?? 0) === (int) $tp[TP::F_ID]) ? 'selected' : '' ?>>
                        <?= h($tp[TP::F_TITLE]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label">IP</label>
        <div class="col-9">
            <input type="text" class="form-control" name="fw[host]" value="<?= h($form['host'] ?? '') ?>">
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label">Port</label>
        <div class="col-3">
            <input type="number" class="form-control" name="fw[port]" value="<?= h((string) ($form['port'] ?? 8729)) ?>">
        </div>
        <label class="col-3 col-form-label"><?= __('SSL | SSL | SSL') ?></label>
        <div class="col-3">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="fw[ssl]" value="1" <?= !empty($form['ssl']) ? 'checked' : '' ?>>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label">Login</label>
        <div class="col-9">
            <input type="text" class="form-control" name="fw[login]" value="<?= h($form['login'] ?? '') ?>">
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-3 col-form-label">Password</label>
        <div class="col-9">
            <input type="text" class="form-control" name="fw[password]" value="<?= h($form['password'] ?? '') ?>">
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><?= __('Connect | Подключиться | Підключитися') ?></button>
    </div>
</form>