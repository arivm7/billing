<?php
/**
 *  Project : my.ri.net.ua
 *  File    : addView.php
 *  Path    : app/views/Tp/addView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of addView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Firm;
use config\tables\TP;
?>
<div class="mx-auto align-middle min-w-75 w-auto">
    <form method="post" action="<?= TP::URI_CREATE; ?>">
        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Name | Название | Назва') ?></label>
            <div class="col-9">
                <input type="text" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_TITLE ?>]"
                       value="<?= h($tp[TP::F_TITLE] ?? '') ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Enterprise | Предприятие | Підприємство') ?></label>
            <div class="col-9">
                <select class="form-select" name="<?= TP::POST_REC ?>[<?= TP::F_FIRM_ID ?>]" required>
                    <option value="0"><?= __('Select a company | Выберите предприятие | Виберіть підприємство') ?></option>
                    <?php foreach ($firms as $firm): ?>
                        <option value="<?= (int) $firm[Firm::F_ID] ?>"
                            <?= ((int) ($tp[TP::F_FIRM_ID] ?? 0) === (int) $firm[Firm::F_ID]) ? 'selected' : '' ?>>
                            <?= h($firm[Firm::F_NAME_LONG]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Managed TP | Управляемая ТП | Керована ТП') ?></label>
            <div class="col-9">
                <select class="form-select" name="<?= TP::POST_REC ?>[<?= TP::F_IS_MANAGED ?>]">
                    <option value="1" <?= ((int) ($tp[TP::F_IS_MANAGED] ?? 1) === 1) ? 'selected' : '' ?>><?= __('Yes | Да | Так') ?></option>
                    <option value="0" <?= ((int) ($tp[TP::F_IS_MANAGED] ?? 1) === 0) ? 'selected' : '' ?>><?= __('No | Нет | Ні') ?></option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('IP address | IP-адрес | IP-адреса') ?></label>
            <div class="col-9">
                <input type="text" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_IP ?>]"
                       value="<?= h($tp[TP::F_IP] ?? '') ?>">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= TP::F_MIK_IP ?></label>
            <div class="col-9">
                <input type="text" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_MIK_IP ?>]"
                       value="<?= h($tp[TP::F_MIK_IP] ?? '') ?>">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= TP::F_MIK_PORT ?></label>
            <div class="col-9">
                <input type="number" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_MIK_PORT ?>]"
                       value="<?= h((string) ($tp[TP::F_MIK_PORT] ?? 8728)) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= TP::F_MIK_PORT_SSL ?></label>
            <div class="col-9">
                <input type="number" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_MIK_PORT_SSL ?>]"
                       value="<?= h((string) ($tp[TP::F_MIK_PORT_SSL] ?? 8729)) ?>">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= TP::F_MIK_LOGIN ?></label>
            <div class="col-9">
                <input type="text" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_MIK_LOGIN ?>]"
                       value="<?= h($tp[TP::F_MIK_LOGIN] ?? '') ?>">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= TP::F_MIK_PASSWD ?></label>
            <div class="col-9">
                <input type="text" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_MIK_PASSWD ?>]"
                       value="<?= h($tp[TP::F_MIK_PASSWD] ?? '') ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary"><?= __('Create | Создать | Створити') ?></button>
                <a href="<?= TP::URI_INDEX; ?>" class="btn btn-secondary"><?= __('Return to list | Вернуться к списку | Повернутись до списку') ?></a>
            </div>
        </div>
    </form>
</div>