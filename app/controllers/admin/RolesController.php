<?php
/**
 *  Project : my.ri.net.ua
 *  File    : RolesController.php
 *  Path    : app/controllers/admin/RolesController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 13 Oct 2025 00:39:33
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of RolesController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace app\controllers\admin;



use app\models\RolesModel;
use billing\core\App;
use billing\core\base\Model;
use billing\core\base\View;
use billing\core\Pagination;
use billing\core\base\Lang;
use config\Icons;
use config\SessionFields;
use config\tables\TSUserRole;
use config\tables\Role;
use config\tables\User;
require DIR_LIBS . '/form_functions.php';



class RolesController extends AdminBaseController {

    /**
     * Для использования метода log()
     */
    const LOG_FILENAME = 'roles.log';



    public function __construct(array $route) {
        parent::__construct($route);
    }



    public function indexAction() {

        $model = new RolesModel();

        if (isset($_POST[Role::POST_REC]) && is_array($_POST[Role::POST_REC])) {
            $row = [
                Role::F_ID             => intval($_POST[Role::POST_REC][Role::F_ID]),
                Role::F_UK_TITLE       => h($_POST[Role::POST_REC][Role::F_UK_TITLE]),
                Role::F_RU_TITLE       => h($_POST[Role::POST_REC][Role::F_RU_TITLE]),
                Role::F_EN_TITLE       => h($_POST[Role::POST_REC][Role::F_EN_TITLE]),
                Role::F_UK_DESCRIPTION => h($_POST[Role::POST_REC][Role::F_UK_DESCRIPTION]),
                Role::F_RU_DESCRIPTION => h($_POST[Role::POST_REC][Role::F_RU_DESCRIPTION]),
                Role::F_EN_DESCRIPTION => h($_POST[Role::POST_REC][Role::F_EN_DESCRIPTION]),
            ];
            if ($model->update_row_by_id(table: Role::TABLE, row: $row, field_id: Role::F_ID)) {
                $_SESSION[SessionFields::SUCCESS] = __('Данные внесены');
            } else {
                $_SESSION[SessionFields::ERROR] = $model->errorInfo();
            }
            redirect();
        }


        View::setMeta(
                title: __('Список административных ролей'),
                descr: __('Просмотр списка административных ролей, редактирование, добавление и удаление.'));

        $pager = new Pagination(per_page: 10, sql: "SELECT * FROM `".Role::TABLE."` WHERE 1");
        $rows = $pager->get_rows();

        $this->setVariables([
            'table' => $rows,
            'pager' => $pager,
        ]);

    }



    /**
     * Добавление роли
     * @param int $user_id
     * @param int $role_id
     */
    private function tsUserAdd(int $user_id, int $role_id) {
        $model = new RolesModel;
        $this->log("Попытка добавления роли  user[{$user_id}] role[{$role_id}]");
        $sql = "INSERT INTO `".TSUserRole::TABLE."`(`".TSUserRole::F_USER_ID."`, `".TSUserRole::F_ROLE_ID."`) VALUES ('{$user_id}','{$role_id}')";
        if ($model->execute($sql)) {
            $this->log(__("Роль успешно добавлена") . " user[{$user_id}] role[{$role_id}]");
            msg_to_session(__('Роль успешно добавлена'), MSG_HAS_SUCCESS);
        } else {
            $this->log(__("Ошибка добавления роли") . " user[{$user_id}] role[{$role_id}] " . CR . print_r($model->errorInfo(), 1));
            msg_to_session($model->errorInfo(), MSG_HAS_ERROR);
        }
    }



    private function tsUserDel(int $user_id, int $role_id) {
        $model = new RolesModel;
        $this->log("Попытка отключения роли user[{$user_id}] role[{$role_id}]");
        $sql = "DELETE FROM `".TSUserRole::TABLE."` WHERE `".TSUserRole::F_USER_ID."`={$user_id} AND `".TSUserRole::F_ROLE_ID."`={$role_id}";
        if ($model->execute($sql)) {
            $this->log(__("Роль успешно отключена") . " user[{$user_id}] role[{$role_id}]");
            msg_to_session(__('Роль успешно отключена'), MSG_HAS_SUCCESS);
        } else {
            $this->log(__("Ошибка отключения роли") . " user[{$user_id}] role[{$role_id}] " . CR . print_r($model->errorInfo(), 1));
            msg_to_session($model->errorInfo(), MSG_HAS_ERROR);
        }
    }



    public function tsUsersAction() {

        $model = new RolesModel;

        /**
         * Парсинг комманд
         */
        if  (
                /**
                 * Првоеряем наличие команды и правильность параметров
                 */
                isset($_GET['cmd']) &&
                isset($_GET['u']) &&
                isset($_GET['r']) &&
                $model->validate_id(table_name: User::TABLE, id_value: intval($_GET['u']), field_id: User::F_ID) &&
                $model->validate_id(table_name: Role::TABLE, id_value: intval($_GET['r']), field_id: Role::F_ID)
            )
        {
            $u = intval($_GET['u']);
            $r = intval($_GET['r']);

            switch ($_GET['cmd']) {

                /**
                 * Добавление роли
                 */
                case 'add':
                    $this->tsUserAdd($u, $r);
                    break;

                /**
                 * Отключение роли
                 */
                case 'del':
                    $this->tsUserDel($u, $r);
                    break;

                default:
                    break;
            }
            redirect();
        }

        $pager = new Pagination(
                per_page: 10,
                sql: "SELECT * FROM `". User::TABLE."` WHERE 1");
        $users = $pager->get_rows();

        $id_list = [];
        foreach ($users as $row) {
            $id_list[] = $row[User::F_ID];
        }

        /**
         * Полная таблица ролей (скорее всего не нужна)
         */
        $roles = $model->get_rows_by_where(table: Role::TABLE, row_id_by: Role::F_ID);

        /**
         * Таблица ролей для формирования <select>...
         */
        $roles_select = array_column($roles, Lang::code() . Role::_TITLE, Role::F_ID);

        /**
         * Таблица связей Пользователей и Ролей
         */
        $ts = $model->get_rows_by_sql(sql: "SELECT * FROM `adm_user_role` WHERE `user_id` IN (". implode(',', $id_list).")");

        $rows = [];
        foreach ($users as $row) {
            $roles_id_joined = [];
            $roles_list_joined = [];
            foreach ($ts as $pair) {
                if ($pair[TSUserRole::F_USER_ID] == $row[User::F_ID]) {
                    $roles_id_joined[] = $pair[TSUserRole::F_ROLE_ID];
                    $roles_list_joined[] =
                            get_html_content_left_right(
                                    left:   $roles[$pair[TSUserRole::F_ROLE_ID]][Lang::code() . Role::_TITLE],
                                    right:  get_html_form_delete_confirm(
                                                method: 'get',
                                                action: "",
                                                param: [
                                                    'cmd' => 'del',
                                                    'u' => $row[User::F_ID],
                                                    'r' => $pair[TSUserRole::F_ROLE_ID],
                                                ],
                                                attr_button: "class='btn btn-dark btn-sm'",
                                                text: get_html_img(src: Icons::SRC_ICON_TRASH, alt: '[-]', title: __('Delete')))
                            );
                }
            }

            $roles_list_joined[] = "<div class='content-fluid'>"
                                    . "<form action='' method='get'>"
                                    . "<input type=hidden name='cmd' value='add'>"
                                    . "<input type=hidden name='u'   value='{$row[User::F_ID]}'>"
                                    . "<div style='display:flex; width:100%; gap:.5em'>"
                                    . make_html_select(
                                        data: $roles_select,
                                        name: 'r',
                                        excludes_keys: $roles_id_joined,
                                        select_opt: 'style="width: 100%"'
                                    )
                                    . "<button class='btn btn-outline-info btn-sm' type='submit'>[+]</button>"
                                    . "</div>"
                                    . "</form>"
                                    . "</div>";
            $rows[] = [
                User::F_ID          => $row[User::F_ID],
                User::F_LOGIN       => $row[User::F_LOGIN],
                User::F_NAME_SHORT  => $row[User::F_NAME_SHORT],
                User::F_NAME_FULL   => $row[User::F_NAME_FULL],
                'roles'             => $roles_list_joined,
            ];
        }

        View::setMeta(
                title: __('Редактирование ролей пользователй'),
                descr: __('Просмотр и редактирование списка административных ролей, назначенных пользователям.'));

        $this->setVariables([
            'rows'  => $rows,
            'pager' => $pager,
        ]);


    }

}