<?php


namespace app\controllers\admin;
use app\models\AdminUsersModel;
use billing\core\base\View;
use billing\core\Pagination;
use config\tables\User;

class AdminUsersController extends AdminBaseController {

    public function listAction() {
        $title = 'Админ :: Список пользователей';
        View::setMeta('Админ :: Список пользователей', 'Административный режим. Просмотр и редактироване списка пользователей');

        $model = new AdminUsersModel();
        $perPage = 20;
        $total = $model->get_users_count();
        $pageCurrent = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pager = new Pagination($pageCurrent, $perPage, $total);
        $limitStart = $pager->get_sql_limit_start();
        $users = $model->get_users_list(limit_start: $limitStart, count: $perPage);
        foreach ($users as $key => &$row) {
            $row['act'] = a(text: '[E]', href: "/admin/admin-users/user?id=" . $row[User::F_ID]);
        }

        $this->setVariables([
            'title' => $title,
            'users' => $users,
            'params' => [
                'pre_align' => true,
                                     // id    login    password2    password    salt    name_short   name     surname    family    phone_main    do_send_sms    mail_main    do_send_mail    address_invoice    do_send_invoice    jabber_main    jabber_do_send    viber    viber_do_send    telegram    telegram_do_send    prava    creation_uid    creation_date    modified_uid    modified_date    _x_reg_date    reg_user_id    act
                'col_titles' =>       ["id", "login", "password2", "password", "salt", "name_short", "name", "surname", "family", "phone_main", "do_send_sms", "mail_main", "do_send_mail", "address_invoice", "do_send_invoice", "jabber_main", "jabber_do_send", "viber", "viber_do_send", "telegram", "telegram_do_send", "prava", "creation_uid", "creation_date", "modified_uid", "modified_date", "_x_reg_date", "reg_user_id", "act"],
                'child_col_titles' =>  true,
                'cell_attributes' =>  ["id", "login", "hidden",    "hidden", "hidden", "name_short", "name", "hidden",  "hidden", "phone_main", "do_send_sms", "hidden",    "hidden",       "hidden",          "hidden",          "hidden",      "hidden",        "hidden", "hidden",        "hidden",   "hidden",           "prava", "hidden",       "hidden",        "hidden",       "hidden",        "hidden",      "hidden",      "act"],
                'child_cell_attributes' => true,
            ],
            'pager' => $pager,
        ]);
    }



    public function userAction() {
        $title = 'Админ :: Редактирование пользователя';
        View::setMeta('Админ :: Редактирование пользователя', 'Административный режим. Просмотр и редактироване записи пользователя');

        $model = new AdminUsersModel();
        $user_id = isset($_GET['id']) ? (int)$_GET['id'] : -1;
        $user = $model->get_user($user_id);
            debug($_POST, debug_view: \DebugView::PRINTR);

        if (isset($_POST[User::POST_REC]) && is_array($_POST[User::POST_REC]) ) {
            // Есть данные отредактированной формы
            debug($_POST[User::POST_REC], debug_view: \DebugView::PRINTR);
            $post_data = $_POST[User::POST_REC];
            $row = [];
            foreach (User::T_FIELDS as $field => $value) {


            }
//            $row[MenuModel::FIELD_PARENT]   = intval($post_data[MenuModel::FIELD_PARENT]);
//            $row[MenuModel::FIELD_ORDER]    = intval($post_data[MenuModel::FIELD_ORDER]);
//            $row[MenuModel::FIELD_TITLE]    = $post_data[MenuModel::FIELD_TITLE];
//            $row[MenuModel::FIELD_URL]      = $post_data[MenuModel::FIELD_URL];
//            $row[MenuModel::FIELD_DESCR]    = $post_data[MenuModel::FIELD_DESCR];
//
//            if (isset($post_data[MenuModel::FIELD_ID])) {
//                // редактироват имеющуюся строку
//                $row[MenuModel::FIELD_ID]   = $post_data[MenuModel::FIELD_ID];
//                if ($db->update_row_by_id(table: $db->db_table, row: $row)) {
//                    $_SESSION[SessionFields::SUCCESS] = 'Элемент меню исправлен';
//                } else {
//                    $_SESSION[SessionFields::ERROR] = 'Ошибка внесения данных: ' . $db->errorInfo();
//                }
//            } else {
//                // создать новую запись
//                if ($db->insert_row(table: $db->db_table, row: $row)) {
//                    $_SESSION[SessionFields::SUCCESS] = 'Элемент меню Добаавлен';
//                } else {
//                    $_SESSION[SessionFields::ERROR] = 'Ошибка добавления: ' . $db->errorInfo();
//                }
//            }
            redirect();
        }

        $this->setVariables([
            'title' => $title,
            'user' => $user,
        ]);
    }

}
