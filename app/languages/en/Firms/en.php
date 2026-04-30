<?php
/**
 *  Project : my.ri.net.ua
 *  File    : en.php
 *  Path    : app/languages/en/Firms/en.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 23:46:14
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of en.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * dict en
 * for FirmsController
 */

return [
    /**
     * requireUseAccess()
     */
    'Please log in' => 'Please log in',
    'No rights' => 'No rights',

    /**
     * requireViewAccess()
     */
    'No rights to view' => 'No rights to view',

    /**
     * requireEditAccess()
     */
    'No rights to edit' => 'No rights to edit',

    /**
     * requireDeleteAccess()
     */
    'No rights to delete' => 'No rights to delete',

    /**
     * resolveListUserId()
     */
    'Passing user_id is not allowed for this access level' => 'Passing user_id is not allowed for this access level',

    /**
     * ensureFirmEditable()
     */
    'No rights to edit this enterprise' => 'No rights to edit this enterprise',

    /**
     * indexAction()
     */
    'Enterprises and employees' => 'Enterprises and employees',

    /**
     * editAction()
     */
    'Enterprise ID is not specified' => 'Enterprise ID is not specified',
    'Enterprise not found' => 'Enterprise not found',
    'No rights to view this enterprise' => 'No rights to view this enterprise',
    'Editing enterprise' => 'Editing enterprise',

    /**
     * saveFirmAction()
     */
    'No data to save' => 'No data to save',
    'Invalid enterprise ID' => 'Invalid enterprise ID',
    'Enterprise not found' => 'Enterprise not found',
    'Enterprise name fields must not be empty' => 'Enterprise name fields must not be empty',
    'Enterprise data saved' => 'Enterprise data saved',
    'Failed to save enterprise data' => 'Failed to save enterprise data',

    /**
     * saveEmployeeAction()
     */
    'No employee data to save' => 'No employee data to save',
    'Invalid employee data' => 'Invalid employee data',
    'Enterprise not found' => 'Enterprise not found',
    'User not found' => 'User not found',
    'Employee data saved' => 'Employee data saved',
    'Failed to save employee data' => 'Failed to save employee data',

    /**
     * deleteEmployeeAction()
     */
    'Invalid delete parameters' => 'Invalid delete parameters',
    'Enterprise not found' => 'Enterprise not found',
    'Employee removed' => 'Employee removed',
    'Failed to remove employee' => 'Failed to remove employee',
];