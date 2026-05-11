<?php
use config\tables\Firm;
use config\tables\TP;
?>
<div class="mx-auto align-middle min-w-75 w-auto">
    <form method="post" action="<?= TP::URI_CREATE; ?>">
        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Название') ?></label>
            <div class="col-9">
                <input type="text" class="form-control"
                       name="<?= TP::POST_REC ?>[<?= TP::F_TITLE ?>]"
                       value="<?= h($tp[TP::F_TITLE] ?? '') ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Предприятие') ?></label>
            <div class="col-9">
                <select class="form-select" name="<?= TP::POST_REC ?>[<?= TP::F_FIRM_ID ?>]" required>
                    <option value="0"><?= __('Выберите предприятие') ?></option>
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
            <label class="col-3 col-form-label"><?= __('Управляемая ТП') ?></label>
            <div class="col-9">
                <select class="form-select" name="<?= TP::POST_REC ?>[<?= TP::F_IS_MANAGED ?>]">
                    <option value="1" <?= ((int) ($tp[TP::F_IS_MANAGED] ?? 1) === 1) ? 'selected' : '' ?>><?= __('Да') ?></option>
                    <option value="0" <?= ((int) ($tp[TP::F_IS_MANAGED] ?? 1) === 0) ? 'selected' : '' ?>><?= __('Нет') ?></option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('IP-адрес') ?></label>
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
                <button type="submit" class="btn btn-primary"><?= __('Создать') ?></button>
                <a href="<?= TP::URI_INDEX; ?>" class="btn btn-secondary"><?= __('Вернуться к списку') ?></a>
            </div>
        </div>
    </form>
</div>
