<?php
/**
 *  Project : my.ri.net.ua
 *  File    : ru.php
 *  Path    : app/languages/ru/admin/Security/ru.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026 22:20:51
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of ru.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * dict ru
 * for SecurityController
 */

return [
    /**
     * requireCanUse()
     */
    'Please log in' => 'Авторизуйтесь, пожалуйста',
    'No rights' => 'Нет прав',

    /**
     * requireCanView()
     */
    'No rights to view' => 'Нет прав на просмотр',

    /**
     * requireCanEdit()
     */
    'No rights to edit' => 'Нет прав на редактирование',

    /**
     * requireCanDelete()
     */
    'No rights to delete' => 'Нет прав на удаление',

    /**
     * formatDuration()
     */
    'NULL (forever)' => 'NULL (навсегда)',
    'sec.' => 'сек.',
    'd.' => 'дн.',
    'h.' => 'ч.',
    'min.' => 'мин.',

    /**
     * indexAction()
     */
    'Security module' => 'Модуль безопасности',

    /**
     * editTypeAction()
     */
    'Attack type not found' => 'Тип атаки не найден',
    'Editing attack type' => 'Редактирование типа атаки',

    /**
     * saveTypeAction()
     */
    'No data to save' => 'Нет данных для сохранения',
    'Invalid data' => 'Некорректные данные',
    'Changes saved' => 'Изменения сохранены',
    'Save error' => 'Ошибка сохранения',

    /**
     * deleteBlockedIpAction()
     */
    'Invalid delete parameters' => 'Некорректные параметры удаления',
    'Blocked IP deleted' => 'Блокировка IP удалена',
    'Delete error' => 'Ошибка удаления',

    /**
     * deleteAttackEventAction()
     */
    'Invalid delete parameters' => 'Некорректные параметры удаления',
    'Attack event deleted' => 'Событие атаки удалено',
    'Delete error' => 'Ошибка удаления',
];