<?php
/**
 *  Project : my.ri.net.ua
 *  File    : ru.php
 *  Path    : app/languages/ru/Firms/ru.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 23:45:17
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
 * for FirmsController
 */

return [
    /**
     * requireUseAccess()
     */
    'Please log in' => 'Пожалуйста, войдите',
    'No rights' => 'Нет прав',

    /**
     * requireViewAccess()
     */
    'No rights to view' => 'Нет прав на просмотр',

    /**
     * requireEditAccess()
     */
    'No rights to edit' => 'Нет прав на редактирование',

    /**
     * requireDeleteAccess()
     */
    'No rights to delete' => 'Нет прав на удаление',

    /**
     * resolveListUserId()
     */
    'Passing user_id is not allowed for this access level' => 'Передача user_id не разрешена для этого уровня доступа',

    /**
     * ensureFirmEditable()
     */
    'No rights to edit this enterprise' => 'Нет прав на редактирование этого предприятия',

    /**
     * indexAction()
     */
    'Enterprises and employees' => 'Предприятия и сотрудники',

    /**
     * editAction()
     */
    'Enterprise ID is not specified' => 'ID предприятия не указан',
    'Enterprise not found' => 'Предприятие не найдено',
    'No rights to view this enterprise' => 'Нет прав на просмотр этого предприятия',
    'Editing enterprise' => 'Редактирование предприятия',

    /**
     * saveFirmAction()
     */
    'No data to save' => 'Нет данных для сохранения',
    'Invalid enterprise ID' => 'Некорректный ID предприятия',
    'Enterprise not found' => 'Предприятие не найдено',
    'Enterprise name fields must not be empty' => 'Поля названия предприятия не должны быть пустыми',
    'Enterprise data saved' => 'Данные предприятия сохранены',
    'Failed to save enterprise data' => 'Не удалось сохранить данные предприятия',

    /**
     * saveEmployeeAction()
     */
    'No employee data to save' => 'Нет данных сотрудника для сохранения',
    'Invalid employee data' => 'Некорректные данные сотрудника',
    'Enterprise not found' => 'Предприятие не найдено',
    'User not found' => 'Пользователь не найден',
    'Employee data saved' => 'Данные сотрудника сохранены',
    'Failed to save employee data' => 'Не удалось сохранить данные сотрудника',

    /**
     * deleteEmployeeAction()
     */
    'Invalid delete parameters' => 'Некорректные параметры удаления',
    'Enterprise not found' => 'Предприятие не найдено',
    'Employee removed' => 'Сотрудник удалён',
    'Failed to remove employee' => 'Не удалось удалить сотрудника',
];