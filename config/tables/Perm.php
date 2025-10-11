<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Perm.php
 *  Path    : config/tables/Perm.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

use app\models\AbonModel;
use billing\core\App;

/**
 * Description of Perm.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Perm {

    const POST_REC      = 'perm';

    const TABLE         = 'adm_role_module_permissions';

    const F_ROLE_ID     = 'role_id';     // ID административной группы
    const F_MODULE_ID   = 'module_id';   // ID исполняемого модуля
    const F_PERM        = 'permissions'; // [0000] - Нет доступа | 1 [0001] - Просмотр | 2 [0010] - Изменение | 4 [0100] - Добавление | 8 [1000] - Удаление

    const NONE_VALUE    = 0; //  0 [0b0000] - Нет доступа
    const NONE_TITLE    = 'none';
    const NONE_DESCR    = '0 [0b0000] - Нет доступа';

    const VIEW_VALUE    = 0b0001; //  1 [0b0001] - Просмотр
    const VIEW_TITLE    = 'view';
    const VIEW_DESCR    = '1 [0b0001] - Просмотр';

    const EDIT_VALUE    = 0b0010; //  2 [0b0010] - Изменение
    const EDIT_TITLE    = 'edit';
    const EDIT_DESCR    = '2 [0b0010] - Изменение';

    const ADD_VALUE     = 0b0100; //  4 [0b0100] - Добавление
    const ADD_TITLE     = 'add';
    const ADD_DESCR     = '4 [0b0100] - Добавление';

    const DEL_VALUE     = 0b1000; //  8 [0b1000] - Удаление
    const DEL_TITLE     = 'del';
    const DEL_DESCR     = '8 [0b1000] - Удаление';

    const ALL_VALUE     = 0b1111111111111111;

//    // --- Права доступа (битовые флаги) ---
//    const P_NONE   = 0;    // 0000 - Нет доступа
//    const P_VIEW   = 1;    // 0001 - Просмотр
//    const P_EDIT   = 2;    // 0010 - Изменение
//    const P_ADD    = 4;    // 0100 - Добавление
//    const P_DELETE = 8;    // 1000 - Удаление
//
//    // --- Комбинированные права (для удобства) ---
//    const P_ALL    = self::P_VIEW | self::P_EDIT | self::P_ADD | self::P_DELETE; // 1111 - Все права



    /**
     * Запись в реестр разрешений
     * @param int|null $user_id
     * @return void
     */
    public static function update_permissions(?int $user_id = null): void {

        if (!empty(App::$app->permissions)) { return; }
        App::$app->permissions = self::read_permissions($user_id);

    }



    /**
     * Считывает из бызы Роли для указанного пользователя и разрешения по ним
     * и записывает массив в рееср
     * App::$app->permissions -- array[модуль] = разрешение
     * @param int|null $user_id -- если не указан, то берёт текущего авторизованного пользователя
     */
    public static function read_permissions(?int $user_id = null): array {

        $model = new AbonModel();

        $user_id =  ($model->validate_id(table_name: User::TABLE, field_id: User::F_ID, id_value: $user_id)
                        ?   $user_id
                        :   (isset($_SESSION[User::SESSION_USER_REC])
                                ? $_SESSION[User::SESSION_USER_REC][User::F_ID]
                                : 0
                            )
                    );



        /*
         * Проверяем является ли пользователь абонентом,
         * и если абонент, то активен ли он
         */
        $sql = "SELECT "
                . "CASE "
                    . "WHEN COUNT(*) = 0 THEN ".Role::ABON_NONE." "     // Нет  абонентских подключений
                    . "WHEN SUM(" . Abon::F_IS_PAYER . ") > 0 "         // Есть абонентские подключения
                        . "THEN ".Role::ABON_ON." "                     // Есть IS_PAYER
                        . "ELSE ".Role::ABON_OFF." "                    // Нет  IS_PAYER
                . "END AS abon_role "
                . "FROM " . Abon::TABLE . " "
                . "WHERE " . Abon::F_USER_ID . " = {$user_id}";
//        debug($sql, '$sql1');
        $abon_role = $model->query(sql: $sql, fetchCell: 0);


        /*
         * Альтернативный select
         */

        $sql = "SELECT "
                . TSRoleModulePerm::F_MODULE_ID . ", "
                . "BIT_OR(".TSRoleModulePerm::F_PERMISSIONS.") AS ".TSRoleModulePerm::F_PERMISSIONS." "
                . "FROM "
                    . "".TSRoleModulePerm::TABLE." "
                . "WHERE "
                    . "".TSRoleModulePerm::F_ROLE_ID." IN "
                        . "( "
                            . "SELECT ".TSUserRole::F_ROLE_ID." FROM ".TSUserRole::TABLE." WHERE ".TSUserRole::F_USER_ID." = {$user_id} "
                            . ($abon_role
                                    ? "UNION ALL "
                                    . "SELECT {$abon_role} "
                                    : ""
                              )
                        . ") "
                . "GROUP BY ".TSRoleModulePerm::F_MODULE_ID.";";

//        debug($sql, '$sql2', die: 0);
        $list = $model->get_rows_by_sql(sql: $sql); // , row_id_by: TSRoleModelePerm::F_MODULE_ID, unset_row_id_by: true
        $permissions = [];
        foreach ($list as $row) {
            $permissions[$row[TSRoleModulePerm::F_MODULE_ID]] = $row[TSRoleModulePerm::F_PERMISSIONS];
        }

        return $permissions;
    }



}