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
use config\FwInput;
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
                <?= __('Add technical site | Добавить техплощадку | Додати техмайданчик') ?>
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
                        <div class="col-11 text-start"><?= __('Name | Название | Назва') ?> / <?= __('Description | Описание | Опис') ?> / <?= __('Address | Адрес | Адреса') ?></div>
                    </div>
                </th>
                <th class="text-center"><?= __('Status | Статус | Статус') ?></th>
                <th class="text-center"><?= __('Control | Управление | Управління') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tp_list)): ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary">
                        <?= __('No data to display | Нет данных для отображения | Немає даних для відображення') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tp_list as $tp): ?>
                    <tr>
                        <td class="align-text-top text-end" title="<?= __('Order No | Порядковый № | Порядковий №') ?>"><span class="text-secondary font-monospace fs-7 ms-1"><?= ++$num ?>.</span></td>
                        <td>
                            <div class="row">
                                <div class="col-1 text-end" title="<?= __('Technical site ID | ID Техплощадки | ID Техмайданчика') ?>">
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
                            <?php if ($tp[TP::F_ACTIVE]): ?>
                                <span class="badge bg-success"><?= __('Works | Работает | Працює') ?></span>
                                <div class="text-secondary font-monospace fs-8"><?= $tp[TP::F_IP]; ?></div>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= __('Disabled | Отключен | Вимкнено') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (can_view(Module::MOD_ABON)) : ?>
                                <a href="<?=Abon::URI_INDEX.'?tp='.$tp[TP::F_ID];?>" class="btn btn-sm btn-outline-success my-1" target="_blank" 
                                   title="<?= __('List of subscribers | Список абонентов | Список абонентів') ?>">
                                    <img src="<?= Icons::SRC_ABON;?>" height="22rem" width="16rem">
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($tp[TP::F_IS_MANAGED]) : ?>
                                <a href="<?=TP::URI_COMBINE.'/'.$tp[TP::F_ID];?>" class="btn btn-sm btn-outline-primary my-1" target="_blank"
                                   title="<?= __('Control panel | Панель упавления | Панель запалення') ?>">
                                    <img src="<?= Icons::SRC_ICON_MIK_BLUE;?>" height="22rem" width="16rem">
                                </a>
                            <?php else : ?>
                                <div class="btn btn-sm btn-outline-secondary my-1" 
                                     title="<?= __('The technical site is not managed | Техническая площадка не управляемая | Технічний майданчик не керований') ?>">
                                    <img src="<?= Icons::SRC_ICON_MIK_GRAY;?>" height="22rem" width="16rem">
                                </div>
                            <?php endif; ?>

                            <a href="<?=$tp[TP::F_WEB_MANAGEMENT_VALUE];?>" class="btn btn-sm btn-outline-primary my-1" target="_blank" 
                               title="<?= __('Managing a technical site using a web interface | Управление техплощадкой с помощью web-интерфейса | Управління техмайданчиком за допомогою web-інтерфейсу') ?>">
                                <img src="<?= Icons::SRC_ICON_HTTP;?>" height="22rem" width="16rem">
                            </a>
                            
                            <a href="<?= TP::URI_EDIT;?>/<?= (int)$tp[TP::F_ID] ?>" class="btn btn-sm btn-outline-warning my-1" target="_blank"
                               title="<?= __('Edit technical site parameters | Редактировать параметры техплощадки | Редагувати параметри техмайданчика') ?>">
                                <img src="<?= Icons::SRC_EDIT_REC;?>" height="22rem" width="16rem">
                            </a>

                            <?php if (!empty($tp[TP::F_URL_ZABBIX])) : ?>
                                <a href="<?=$tp[TP::F_URL_ZABBIX];?>" class="btn btn-sm btn-outline-primary my-1" target="_blank" 
                                   title="<?= __('Go to the page in the monitoring system | Перейти на страницу в системе мониторинга | Перейти на сторінку в системі моніторингу') ?>">
                                    <img src="<?= Icons::SRC_ICON_ZABBIX;?>" height="22rem" width="16rem">
                                </a>
                            <?php else : ?>
                                <div class="btn btn-sm btn-outline-secondary my-1" 
                                   title="<?= __('The page in the monitoring system is not specified | Страница в системе мониторинга не указана | Сторінка в системі моніторингу не вказана') ?>">
                                    <img src="<?= Icons::SRC_ICON_ZABBIX;?>" height="22rem" width="16rem">
                                </div>
                            <?php endif; ?>

                            <?php if ($tp[TP::F_COUNT_PA]) : ?>
                            <div class="btn btn-sm btn-outline-secondary my-1"
                                 title="<?=__('It cannot be deleted because there are half-included price fragments | Удалить нельзя, посколкьу есть полключённые прайсовые фрагменты | Видалити не можна, оскільки є півключені прайсові фрагменти');?>">
                                <img src="<?= Icons::SRC_ICON_TRASH;?>" height="22rem" width="16rem">
                            </div>
                            <?php else : ?>
                            <a  href="<?=TP::URI_DELETE.'/'.$tp[TP::F_ID];?>" class="btn btn-sm btn-outline-danger my-1" target="_blank"
                                title="<?=__('Can be deleted, since there are no half-included price fragments | Можно удалить, посколкьу нет полключённых прайсовых фрагментов | Можна видалити, оскільки немає півключених прайсових фрагментів');?>"
                                onclick="return confirm('<?= __('You definitely want to Delete this technical site | Точно хотите Удалить эту техплощадку | Точно хочете Видалити цей техмайданчик?') ?>');">
                                <img src="<?= Icons::SRC_ICON_TRASH;?>" height="22rem" width="16rem">
                            </a>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <?php if ($tp[TP::F_ACTIVE] && $tp[TP::F_IS_MANAGED]) : ?>
                                <a href="<?= '/tp/aclsync/' . (int)$tp[TP::F_ID] . '?list=3'; ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Synchronize | Синхронизировать | Синхронізувати') ?> ACL [HACKERS]"
                                   onclick="return confirm('<?= __('Synchronize table [%s] with database | Синхронизировать таблицу [%s] с базой | Синхронізувати таблицю [%s] з базою', 'HACKERS') . '? ' . __('For a long time. May take up to a minute | Долго. Может занять до минуты | Довго. Може зайняти до хвилини') ?>');">
                                    <strong>H</strong>
                                </a>
                            
                                <a href="<?= '/tp/aclsync/' . (int)$tp[TP::F_ID] . '?list=4'; ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Synchronize | Синхронизировать | Синхронізувати') ?> ACL [SERVICES]"
                                   onclick="return confirm('<?= __('Synchronize table [%s] with database | Синхронизировать таблицу [%s] с базой | Синхронізувати таблицю [%s] з базою', 'SERVICES') . '? ' . __('For a long time. May take up to a minute | Долго. Может занять до минуты | Довго. Може зайняти до хвилини') ?>');">
                                    <strong>S</strong>
                                </a>
                            
                                <a href="<?= '/tp/aclsync/' . (int)$tp[TP::F_ID] . '?list=2'; ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Synchronize | Синхронизировать | Синхронізувати') ?> ACL [DNS]"
                                   onclick="return confirm('<?= __('Synchronize table [%s] with database | Синхронизировать таблицу [%s] с базой | Синхронізувати таблицю [%s] з базою', 'DNS') . '? ' . __('For a long time. May take up to a minute | Долго. Может занять до минуты | Довго. Може зайняти до хвилини') ?>');">
                                    <strong>D</strong>
                                </a>
                            
                                <a href="<?= TP::URI_FW_INPUT . '?' . FwInput::F_GET_TP_ID . '=' . (int)$tp[TP::F_ID] ?>" class="btn btn-sm btn-outline-primary my-1"
                                   title="<?= __('Go to the setup wizard | Перейти в мастер настройки | Перейти в майстер налаштування') ?> [/ip/firewall/filer]"
                                   target="_blank">
                                    <strong>FW</strong>
                                </a>
                            
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
