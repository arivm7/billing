<?php
/**
 *  Project : my.ri.net.ua
 *  File    : uk.php
 *  Path    : app/languages/uk/Firms/uk.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 23:45:17
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of uk.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * dict uk
 * for FirmsController
 */

return [
    /**
     * requireUseAccess()
     */
    'Please log in' => 'Будь ласка, увійдіть',
    'No rights' => 'Немає прав',

    /**
     * requireViewAccess()
     */
    'No rights to view' => 'Немає прав на перегляд',

    /**
     * requireEditAccess()
     */
    'No rights to edit' => 'Немає прав на редагування',

    /**
     * requireDeleteAccess()
     */
    'No rights to delete' => 'Немає прав на видалення',

    /**
     * resolveListUserId()
     */
    'Passing user_id is not allowed for this access level' => 'Передача user_id не дозволена для цього рівня доступу',

    /**
     * ensureFirmEditable()
     */
    'No rights to edit this enterprise' => 'Немає прав на редагування цього підприємства',

    /**
     * indexAction()
     */
    'Enterprises and employees' => 'Підприємства та співробітники',

    /**
     * editAction()
     */
    'Enterprise ID is not specified' => 'ID підприємства не вказано',
    'Enterprise not found' => 'Підприємство не знайдено',
    'No rights to view this enterprise' => 'Немає прав на перегляд цього підприємства',
    'Editing enterprise' => 'Редагування підприємства',

    /**
     * saveFirmAction()
     */
    'No data to save' => 'Немає даних для збереження',
    'Invalid enterprise ID' => 'Некоректний ID підприємства',
    'Enterprise not found' => 'Підприємство не знайдено',
    'Enterprise name fields must not be empty' => 'Поля назви підприємства не повинні бути порожніми',
    'Enterprise data saved' => 'Дані підприємства збережено',
    'Failed to save enterprise data' => 'Не вдалося зберегти дані підприємства',

    /**
     * saveEmployeeAction()
     */
    'No employee data to save' => 'Немає даних співробітника для збереження',
    'Invalid employee data' => 'Некоректні дані співробітника',
    'Enterprise not found' => 'Підприємство не знайдено',
    'User not found' => 'Користувача не знайдено',
    'Employee data saved' => 'Дані співробітника збережено',
    'Failed to save employee data' => 'Не вдалося зберегти дані співробітника',

    /**
     * deleteEmployeeAction()
     */
    'Invalid delete parameters' => 'Некоректні параметри видалення',
    'Enterprise not found' => 'Підприємство не знайдено',
    'Employee removed' => 'Співробітника видалено',
    'Failed to remove employee' => 'Не вдалося видалити співробітника',
];