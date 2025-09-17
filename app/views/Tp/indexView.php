<?php
/**
 * @var array $tp_list   Список техплощадок (каждая запись = row из tp_list)
 */

use config\Icons;
use config\tables\TP;
use billing\core\base\Lang;
$num = 0;
?>
<div class="mx-auto w-auto">
    <table class="table table-bordered table-striped table-hover align-middle min-w-75 w-auto mx-auto">

        <thead>
            <tr>
                <th>No</th>
                <th>ID | <?= __('Название') ?> | <?= __('Адрес') ?></th>
                <th><?= __('Статус') ?></th>
                <th><?= __('Управление') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tp_list)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        <?= __('Нет данных для отображения') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tp_list as $tp): ?>
                    <tr>
                        <td><span class="text-secondary text-end text- font-monospace fs-7"><?= ++$num ?></span></td>
                        <td>
                            <div class="row">
                                <div class="col-1 text-end">
                                    <span class="text-secondary font-monospace fs-7"><?= (int)$tp[TP::F_ID] ?></span>
                                </div>
                                <div class="col-11">
                                    <div class="d-flex justify-content-between">
                                        <div class="text-start">
                                            <?= h($tp[TP::F_TITLE]) ?>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-secondary fs-7"><?= TP::get_type_name((int)$tp[TP::F_RANG_ID]) ?></span>
                                        </div>
                                    </div>
                                    <span class="text-secondary fs-7"><?= cleaner_html(nl2br(str_replace("\\n", "<br>", $tp[TP::F_ADDRESS] ?? ''))) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($tp[TP::F_STATUS]): ?>
                                <span class="badge bg-success"><?= __('Работает') ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= __('Отключен') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tp[TP::F_IS_MANAGED]) : ?>
                            <a href="<?=TP::URI_COMBINE.'/'.$tp[TP::F_ID];?>" class="btn btn-sm btn-primary">
                                <img src="<?= Icons::SRC_ICON_MIK_BLUE;?>" height="22rem" title="<?= __('Панель упавления') ?>">
                            </a>
                            <?php else : ?>
                            <div class="btn btn-sm btn-secondary">
                                <img src="<?= Icons::SRC_ICON_MIK_GRAY;?>" height="22rem" title="<?= __('ТП Не управляемая') ?>">
                            </div>
                            <?php endif; ?>
                            <a href="<?= TP::URI_EDIT;?>/<?= (int)$tp[TP::F_ID] ?>" class="btn btn-sm btn-warning">
                                <img src="<?= Icons::SRC_EDIT_REC;?>" height="22rem" title="<?= __('Редактировать параметры ТП') ?>">
                            </a>

                            <?php if ($tp[TP::F_COUNT_PA]) : ?>
                            <div class="btn btn-sm btn-secondary"
                                 title="<?=__('Удалить нельзя, &#10;посколкьу есть полключённые прайсовые фрагменты');?>">
                                <img src="<?= Icons::SRC_ICON_TRASH;?>" height="22rem">
                            </div>
                            <?php else : ?>
                            <a  href="<?=TP::URI_DELETE.'/'.$tp[TP::F_ID];?>"
                                title="<?=__('Можно удалить, &#10;посколкьу нет полключённых прайсовых фрагментов');?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('<?= __('Удалить эту ТП?') ?>');">
                                <img src="<?= Icons::SRC_ICON_TRASH;?>" height="22rem">
                            </a>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
