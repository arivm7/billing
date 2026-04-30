<?php
/**
 *  Project : my.ri.net.ua
 *  File    : firms_edit_employees.php
 *  Path    : app/views/inc/firms_edit_employees.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firms_edit_employees.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\Employees;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

?>
<div class="mb-4">
    <h2 class="h5"><?= __('Employees of enterprise') ?></h2>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th><?= __('User ID') ?></th>
                <th><?= __('Job title') ?></th>
                <th><?= __('User info') ?></th>
                <th><?= __('Created') ?></th>
                <th class="text-end"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($employees)) : ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-3"><?= __('No employees') ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td colspan="5">
                            <form method="post" action="<?= h($uri_save_employee) ?>" class="row g-2 align-items-end">
                                <input type="hidden" name="<?= Employees::POST_REC ?>[<?= Employees::F_FIRM_ID ?>]" value="<?= (int) $firm['id'] ?>">
                                <input type="hidden" name="<?= Employees::POST_REC ?>[<?= h($employee_origin_user_id_field) ?>]" value="<?= (int) $employee[Employees::F_USER_ID] ?>">

                                <div class="col-md-2">
                                    <label class="form-label"><?= __('User ID') ?></label>
                                    <input type="number" class="form-control" name="<?= Employees::POST_REC ?>[<?= Employees::F_USER_ID ?>]" value="<?= (int) $employee[Employees::F_USER_ID] ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><?= __('Job title') ?></label>
                                    <input type="text" class="form-control" name="<?= Employees::POST_REC ?>[<?= Employees::F_JOB_TITLE ?>]" value="<?= h((string) ($employee[Employees::F_JOB_TITLE] ?? '')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><?= __('User info') ?></label>
                                    <div class="form-control">
                                        #<?= (int) $employee[Employees::F_USER_ID] ?>
                                        <?php if (!empty($employee['user_login'])) : ?> / <?= h($employee['user_login']) ?><?php endif; ?>
                                        <?php if (!empty($employee['user_name_short'])) : ?> / <?= h($employee['user_name_short']) ?><?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label"><?= __('Created') ?></label>
                                    <div class="form-control">
                                        <?= h((string) ($employee[Employees::F_CREATION_DATE] ?? '')) ?>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="<?= __('Save') ?>">
                                        <i class="bi bi-floppy"></i>
                                    </button>
                                    <a href="<?= h($uri_delete_employee) ?>?<?= Employees::F_FIRM_ID ?>=<?= (int) $firm['id'] ?>&<?= Employees::F_USER_ID ?>=<?= (int) $employee[Employees::F_USER_ID] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       data-bs-toggle="tooltip"
                                       title="<?= __('Delete') ?>"
                                       onclick="return confirm('<?= __('Are you sure') . '?' ?>');">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="5">
                    <form method="post" action="<?= h($uri_save_employee) ?>" class="row g-2 align-items-end">
                        <input type="hidden" name="<?= Employees::POST_REC ?>[<?= Employees::F_FIRM_ID ?>]" value="<?= (int) $firm['id'] ?>">
                        <input type="hidden" name="<?= Employees::POST_REC ?>[<?= h($employee_origin_user_id_field) ?>]" value="">

                        <div class="col-md-2">
                            <label class="form-label"><?= __('New user ID') ?></label>
                            <input type="number" class="form-control" name="<?= Employees::POST_REC ?>[<?= Employees::F_USER_ID ?>]" value="" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= __('Job title') ?></label>
                            <input type="text" class="form-control" name="<?= Employees::POST_REC ?>[<?= Employees::F_JOB_TITLE ?>]" value="">
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success"><?= __('Add employee') ?></button>
                        </div>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
</div>