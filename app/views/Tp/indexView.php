<?php
/*
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Tp/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * @var array $tp_list   Список техплощадок (каждая запись = row из tp_list)
 */

use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Icons;
use config\tables\Abon;
use config\tables\Module;
use config\tables\TP;
use billing\core\base\Lang;
$num = 0;

if (!can_use(Module::MOD_TP)) {
    MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
    self::log_no_rights();
    redirect();
}
?>
<div class="mx-auto w-auto">
    <?php if (can_add(Module::MOD_TP)) : ?>
        <div class="mb-3 text-end">
            <a href="<?= TP::URI_ADD; ?>" class="btn btn-primary">
                <?= __('Добавить техплощадку') ?>
            </a>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped table-hover align-middle min-w-75 w-auto mx-auto">

        <thead>
            <tr>
                <th class="text-center text-secondary">No</th>
                <th>
                    <div class="row">
                        <div class="col-1 text-end text-secondary">id</div>
                        <div class="col-11 text-start"><?= __('Название') ?> / <?= __('Описание') ?> / <?= __('Адрес') ?></div>
                    </div>
                </th>
                <th class="text-center"><?= __('Статус') ?></th>
                <th class="text-center"><?= __('Управление') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tp_list)): ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary">
                        <?= __('Нет данных для отображения') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tp_list as $tp): ?>
                    <tr>
                        <td class="align-text-top text-end" title="<?= __('№ пп') ?>"><span class="text-secondary font-monospace fs-7 ms-1"><?= ++$num ?>.</span></td>
                        <td>
                            <div class="row">
                                <div class="col-1 text-end" title="<?= __('ID ППП') ?>">
                                    <span class="text-secondary font-monospace fs-7 ms-2"><?= (int)$tp[TP::F_ID] ?>.</span>
                                </div>
                                <div class="col-11">
                                    <!-- <div class="d-flex justify-content-between"> -->
                                    <!-- </div> -->
                                    <div class="text-start">
                                        <?= h($tp[TP::F_TITLE]) ?>
                                    </div>
                                    <div class="text-start">
                                        <span class="text-secondary fs-7"><?= TP::get_type_name((int)$tp[TP::F_RANG_ID]) ?></span>
                                    </div>
                                    <span class="text-secondary fs-7"><?= cleaner_html(nl2br(str_replace("\\n", "<br>", $tp[TP::F_ADDRESS] ?? ''))) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($tp[TP::F_STATUS]): ?>
                                <span class="badge bg-success"><?= __('Работает') ?></span>
                                <div class="text-secondary font-monospace fs-8"><?= $tp[TP::F_IP]; ?></div>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= __('Отключен') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (can_view(Module::MOD_ABON)) : ?>
                                <a href="<?=Abon::URI_INDEX.'?tp='.$tp[TP::F_ID];?>" class="btn btn-sm btn-outline-success my-1" target="_blank" 
                                   title="<?= __('Список абонентов') ?>">
                                    <img src="<?= Icons::SRC_ABON;?>" height="22rem" width="16rem">
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($tp[TP::F_IS_MANAGED]) : ?>
                                <a href="<?=TP::URI_COMBINE.'/'.$tp[TP::F_ID];?>" class="btn btn-sm btn-outline-primary my-1" target="_blank"
                                   title="<?= __('Панель упавления') ?>">
                                    <img src="<?= Icons::SRC_ICON_MIK_BLUE;?>" height="22rem" width="16rem">
                                </a>
                            <?php else : ?>
                                <div class="btn btn-sm btn-outline-secondary my-1" 
                                     title="<?= __('ТП Не управляемая') ?>">
                                    <img src="<?= Icons::SRC_ICON_MIK_GRAY;?>" height="22rem" width="16rem">
                                </div>
                            <?php endif; ?>

                            <a href="<?=$tp[TP::F_WEB_MANAGEMENT_VALUE];?>" class="btn btn-sm btn-outline-primary my-1" target="_blank" 
                               title="<?= __('Управление ТП с помощью web-интерфейса') ?>">
                                <img src="<?= Icons::SRC_ICON_HTTP;?>" height="22rem" width="16rem">
                            </a>
                            
                            <a href="<?= TP::URI_EDIT;?>/<?= (int)$tp[TP::F_ID] ?>" class="btn btn-sm btn-outline-warning my-1" target="_blank"
                               title="<?= __('Редактировать параметры ТП') ?>">
                                <img src="<?= Icons::SRC_EDIT_REC;?>" height="22rem" width="16rem">
                            </a>

                            <?php if (!empty($tp[TP::F_URL_ZABBIX])) : ?>
                                <a href="<?=$tp[TP::F_URL_ZABBIX];?>" class="btn btn-sm btn-outline-primary my-1" target="_blank" 
                                   title="<?= __('Перейти на страницу в системе мониторинга') ?>">
                                    <img src="<?= Icons::SRC_ICON_ZABBIX;?>" height="22rem" width="16rem">
                                </a>
                            <?php else : ?>
                                <div class="btn btn-sm btn-outline-secondary my-1" 
                                   title="<?= __('Страница в системе мониторинга не указана') ?>">
                                    <img src="<?= Icons::SRC_ICON_ZABBIX;?>" height="22rem" width="16rem">
                                </div>
                            <?php endif; ?>

                            <?php if ($tp[TP::F_COUNT_PA]) : ?>
                            <div class="btn btn-sm btn-outline-secondary my-1"
                                 title="<?=__('Удалить нельзя, &#10;посколкьу есть полключённые прайсовые фрагменты');?>">
                                <img src="<?= Icons::SRC_ICON_TRASH;?>" height="22rem" width="16rem">
                            </div>
                            <?php else : ?>
                            <a  href="<?=TP::URI_DELETE.'/'.$tp[TP::F_ID];?>" class="btn btn-sm btn-outline-danger my-1" target="_blank"
                                title="<?=__('Можно удалить, &#10;посколкьу нет полключённых прайсовых фрагментов');?>"
                                onclick="return confirm('<?= __('Удалить эту ТП?') ?>');">
                                <img src="<?= Icons::SRC_ICON_TRASH;?>" height="22rem" width="16rem">
                            </a>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <?php if ($tp[TP::F_STATUS] && $tp[TP::F_IS_MANAGED]) : ?>
                                <a href="<?= '/tp/aclsync/' . (int)$tp[TP::F_ID] . '?list=3'; ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Синхронизировать ACL [HACKERS]') ?>"
                                   onclick="return confirm('<?= __('Синхронизировать таблицу [%s] с базой', 'HACKERS') . '? ' . __('Долго. Может занять до минуты.') ?>');">
                                    <strong>H</strong>
                                </a>
                            
                                <a href="<?= '/tp/aclsync/' . (int)$tp[TP::F_ID] . '?list=4'; ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Синхронизировать ACL [SERVICES]') ?>"
                                   onclick="return confirm('<?= __('Синхронизировать таблицу [%s] с базой', 'SERVICES') . '? ' . __('Долго. Может занять до минуты.') ?>');">
                                    <strong>S</strong>
                                </a>
                            
                                <a href="<?= '/tp/aclsync/' . (int)$tp[TP::F_ID] . '?list=2'; ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Синхронизировать ACL [DNS]') ?>"
                                   onclick="return confirm('<?= __('Синхронизировать таблицу [%s] с базой', 'DNS') . '? ' . __('Долго. Может занять до минуты.') ?>');">
                                    <strong>D</strong>
                                </a>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
