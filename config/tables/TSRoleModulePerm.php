<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : TSRoleModulePerm.php
 *  Path    : config/tables/TSRoleModulePerm.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of TSRoleModulePerm.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class TSRoleModulePerm {

    const POST_REC      = 'tsRoleModulePerm';

    const TABLE         = 'adm_role_module_permissions'; // 'Таблица связи. Принадлежность модулей группам';

    const F_ROLE_ID     = 'role_id';       // 'ID административной группы'
    const F_MODULE_ID   = 'module_id';     // 'ID исполняемого модуля'
    const F_PERMISSIONS = 'permissions';   // 'Права доступа:
                                           // 0 [0000] - Нет доступа
                                           // 1 [0001] - Просмотр
                                           // 2 [0010] - Изменение
                                           // 4 [0100] - Добавление
                                           // 8 [1000] - Удаление'


}