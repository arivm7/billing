<?php

namespace app\controllers\admin;

use app\models\AppBaseModel;
use app\models\ModuleModel;
use billing\core\base\Lang;
use billing\core\base\Model;

use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;

use billing\core\Pagination;
use config\Icons;
use config\SessionFields;
use config\tables\Module;
use config\tables\Perm;
use config\tables\Role;
use config\tables\TSRoleModulePerm;
use config\tables\User;

/*
 *
users
id, name, email, ...

roles
id,  (name -- для понятности людям)

modules
id, (name -- для понятности людям)

users_roles (многие-ко-многим)
user_id, role_id

module_role_permissions
module_id, role_id, permission

внутри метода должна быть доступна переменная или константа module_id
в сессии доступна переменная user_id
role_id = select role_id from users_roles where user_id=user_id
permission = select permision from module_role_permissions where role_id=role_id and module_id=module_id
 *
 */



class ModuleController extends AdminBaseController
{



    function indexAction() {
        redirect(Module::URI_LIST);
    }



    function deleteAction() {
        if (can_del(Module::MOD_MODULES)) {
            $model = new ModuleModel();
            $module_id = $this->route[F_ALIAS];
            MsgQueue::msg(MsgType::INFO_AUTO, 'Пробуем удалить модуль ' . $module_id);
            if ($model->validate_id(table_name: Module::TABLE, field_id: Module::F_ID, id_value: $module_id)) {
                MsgQueue::msg(MsgType::INFO_AUTO, 'Проверка наличия записей разрешений для этого модуля...');
                $access_list = $model->get_rows_by_field(table: TSRoleModulePerm::TABLE, field_name: TSRoleModulePerm::F_MODULE_ID, field_value: $module_id);
                if ($access_list) {
                    MsgQueue::msg(MsgType::INFO_AUTO, 'Разрешения есть. Удаляем их...');
                    if ($model->delete_rows_by_field(table: TSRoleModulePerm::TABLE, field_id: TSRoleModulePerm::F_MODULE_ID, value_id: $module_id)) {
                        MsgQueue::msg(MsgType::INFO_AUTO, 'Успешно удалили.');
                    } else {
                        MsgQueue::msg(MsgType::INFO_AUTO, 'Удаление записей с разрешениями не удалось.');
                        MsgQueue::msg(MsgType::INFO_AUTO, 'Прекращаем удаление.');
                        redirect(Module::URI_LIST);
                    }
                } else {
                    MsgQueue::msg(MsgType::INFO_AUTO, 'Разрешения не назначены.');
                }
                MsgQueue::msg(MsgType::INFO_AUTO, 'Ударяем модуль...');
                if ($model->delete_rows_by_field(table: Module::TABLE, field_id: Module::F_ID, value_id: $module_id)) {
                    MsgQueue::msg(MsgType::INFO_AUTO, 'Успешно удалили');
                } else {
                    MsgQueue::msg(MsgType::INFO_AUTO, 'Удаление не удалось');
                }

            } else {
                MsgQueue::msg(MsgType::INFO_AUTO, 'Модуль указан не верно');
            }
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, 'Нет прав для этой операции');
        }
        redirect(Module::URI_LIST);
    }



    function listAction() {
        $model = new AppBaseModel();

        $perPage     = 25;
        $sql         = "SELECT * FROM `" . Module::TABLE . "` WHERE 1 ORDER BY `" . Module::TABLE . "`.`" . Module::F_TITLE[Lang::code()] . "` ASC";
        $pager       = new Pagination(per_page: $perPage, sql: $sql);
        $module_list = $pager->get_rows();

        foreach ($module_list as &$module) {
            $sql = "SELECT COUNT(`" . Perm::F_PERM . "`) as COUNT FROM `" . Perm::TABLE . "` WHERE `" . Perm::F_MODULE_ID . "`={$module[Module::F_ID]}";
            $perm_count = $model->get_count_by_sql($sql, field_count: Perm::F_PERM);
            $t[] = [
                'id' => $module[Module::F_ID],
                'route/api' =>  "<span class='small text-secondary font-monospace'>route: </span>"
                              . "<span class='small text-secondary-emphasis'>{$module[Module::F_ROUTE]}</span><br>"
                              . "<span class='small text-secondary font-monospace'>&nbsp;&nbsp;api: </span>"
                              . "<span class='small text-secondary-emphasis'>{$module['api']}</span>",
                'title'     =>  "<div class='d-flex justify-content-between'>"
                                    . "<div class='d-inline align-items-start'>"
                                        . "<img src='".Icons::SRC_ICON_EDIT."' alt='[E]' width=22>&nbsp;"
                                        . "<a href='" . Module::URI_FORM . "/{$module[Module::F_ID]}'>"
                                            . "<span class='fs-5 text-info-emphasis'>{$module[Module::F_TITLE[Lang::code()]]}</span><br>"
                                        . "</a>"
                                        . "<span class='small text-secondary'>{$module[Module::F_DESCRIPTION[Lang::code()]]}</span>"
                                    . "</div>"
                                    . "<div>"
                                        . "<a href='" . Module::URI_DEL  . "/{$module[Module::F_ID]}' onclick=\"return confirm('Подтвердите удаление записи о модуле');\" ><img src='".Icons::SRC_ICON_TRASH."' alt='[x]' width=22></a>"
                                    . "</div>"
                                . "</div>",
                'perm'      =>  a(text: 'Права&nbsp;доступов', href: Module::URI_ACCESS.'?id='.$module[Module::F_ID], target: TARGET_SELF, title: 'Установка прав Ролей для этого модуля.')."<br>"
                              . "<span class='small text-secondary-emphasis'>записей: " . $perm_count . "</span>",
                'modified'  =>  "<span class='small text-secondary-emphasis'>"
                                . date("d.m.Y H:i:s", $module[Module::F_CREATION_DATE]) ." : ". (!empty($module[Module::F_CREATION_UID]) ? $model->get_user($module[Module::F_CREATION_UID])[User::F_NAME_SHORT]:"-")."<br>"
                                . date("d.m.Y H:i:s", $module[Module::F_MODIFIED_DATE]) ." : ". (!empty($module[Module::F_MODIFIED_UID]) ? $model->get_user($module[Module::F_MODIFIED_UID])[User::F_NAME_SHORT]:"-")."</span>",
            ];
        }

        $this->setVariables(
                [
                    'table' => $t,
                    'pager' => $pager,
                ]);

        View::setMeta(
                title: "Админ :: Список модулей сайта. Редактор прав доступа",
                descr: "Страница редактирования списка модулей и прав доступа к этим модулям для ролей");
    }


    public function formAction() {
        $model = new AppBaseModel();
        if (isset($_POST[Module::POST_REC]) && is_array($_POST[Module::POST_REC])) {
            $row = $_POST[Module::POST_REC];
            if (empty($row[Module::F_ID])) {
                if (isset($row[Module::F_ID])) { unset($row[Module::F_ID]); }
                $row[Module::F_CREATION_UID] = $_SESSION[User::SESSION_USER_REC][User::F_ID];
                $row[Module::F_CREATION_DATE] = time();
                $row[Module::F_MODIFIED_UID] = $_SESSION[User::SESSION_USER_REC][User::F_ID];
                $row[Module::F_MODIFIED_DATE] = time();
                if ($model->insert_row(table: Module::TABLE, row: $row)) {
                    MsgQueue::msg(MsgType::SUCCESS, __('Новый модуль с описанием добавлен.'));
                    $module_id = $model->lastInsertId();
                    MsgQueue::msg(MsgType::SUCCESS, __("ID нового модуля: %s.", $module_id));
                    redirect(Module::URI_FORM . '/' . $module_id );
                } else {
                    MsgQueue::msg(MsgType::ERROR, ["Ошибка внесеня данных.", $model->errorInfo()]);
                    $_SESSION[SessionFields::FORM_DATA] = $_POST[Module::POST_REC];
                    redirect(Module::URI_FORM);
                }
            } else {
                if ($model->update_row_by_id(table: Module::TABLE, row: $row, field_id: Module::F_ID)) {
                    MsgQueue::msg(MsgType::SUCCESS, "Исправлени описания модуля внесены.");
                    redirect(Module::URI_FORM . '/' . $row[Module::F_ID]);
                } else {
                    MsgQueue::msg(MsgType::ERROR, ["Ошибка внесеня данных.", $model->errorInfo()]);
                    $_SESSION[SessionFields::FORM_DATA] = $_POST[Module::POST_REC];
                    redirect(Module::URI_FORM);
                }
            }
        }

        if (isset($this->route[F_ALIAS]) && $model->validate_id(table_name: Module::TABLE, field_id: Module::F_ID, id_value: (int)$this->route[F_ALIAS])) {
            $module = $model->get_row_by_id(table_name: Module::TABLE, field_id: Module::F_ID, id_value: (int)$this->route[F_ALIAS]);

            $this->setVariables([
                'module' => $module
            ]);

            View::setMeta(
                title: __("Редактирование описания модуля"),
            );
        } else {
            View::setMeta(
                title: __("Создание нового модуля"),
            );
        }
    }


    function has_exist_row_role_module(int $module_id, int $role_id): bool {
        $sql = "SELECT * FROM `". Perm::TABLE ."` WHERE `".Perm::F_MODULE_ID."`={$module_id} and `".Perm::F_ROLE_ID."`={$role_id}";
        $model = new AppBaseModel();
        return ($model->get_count_by_sql($sql, field_count: Perm::F_ROLE_ID) > 0);
    }



    function accessAction() {
        View::setMeta(
            title: __("Мета Заголовок страницы"),
            descr: __("Тут должно быть мета описание страницы"),
        );
            $alert = "";
            $model = new AppBaseModel();

            if (isset($_GET[Module::F_ID])) {
                if ($model->validate_id(Module::TABLE, $_GET[Module::F_ID], Module::F_ID)) {

                    if (isset($_POST[Module::POST_REC]) && is_array($_POST[Module::POST_REC])) {
                        $row = $_POST[Module::POST_REC];
                        if ($model->update_row_by_id(Module::TABLE, $row, Module::F_ID)) {
                            $alert .= "Исправлени описания модуля внесены.\n";
                        }
                        redirect();
                    }

                    if (isset($_POST[Perm::POST_REC]) && is_array($_POST[Perm::POST_REC])) {
                        foreach ($_POST[Perm::POST_REC] as $perm_module_id => $perm_module_row) {
                            foreach ($perm_module_row as $role_row) {
                                $permissions = get_permission_value(
                                        view: (isset($role_row[Perm::VIEW_TITLE])),
                                        edit: (isset($role_row[Perm::EDIT_TITLE])),
                                        add:  (isset($role_row[Perm::ADD_TITLE])),
                                        del:  (isset($role_row[Perm::DEL_TITLE])));
                                $row = [
                                    Perm::F_ROLE_ID => $role_row[Perm::F_ROLE_ID],
                                    Perm::F_MODULE_ID => $perm_module_id,
                                    Perm::F_PERM => $permissions
                                ];

                                if ($this->has_exist_row_role_module($row[Perm::F_MODULE_ID], $row[Perm::F_ROLE_ID])) {
//                                    debug("row: ", $row);
                                    if ($model->update_row_by_where(table: Perm::TABLE, row: $row, where: "`".Perm::F_ROLE_ID."`={$row[Perm::F_ROLE_ID]} and `".Perm::F_MODULE_ID."`={$row[Perm::F_MODULE_ID]}")) {
                                        $alert .= "Обновление успешно: {$row[Perm::F_MODULE_ID]}, {$row[Perm::F_ROLE_ID]} обновлён на {$row[Perm::F_PERM]}\n";
                                    } else {
                                        $alert .= "Ошибка обновления: {$row[Perm::F_MODULE_ID]}, {$row[Perm::F_ROLE_ID]} обновлён на {$row[Perm::F_PERM]}\n";
                                    }
                                } else {
                                    if ($model->insert_row(table: Perm::TABLE, row: $row)) {
                                        $alert .= "Добавление успешно: {$row[Perm::F_MODULE_ID]}, {$row[Perm::F_ROLE_ID]} обновлён на {$row[Perm::F_PERM]}\n";
                                    } else {
                                        $alert .= "Ошибка добавления: {$row[Perm::F_MODULE_ID]}, {$row[Perm::F_ROLE_ID]} обновлён на {$row[Perm::F_PERM]}\n";
                                    }
                                }
                            }
                        }
                    }

                    $module_id = $_GET[Module::F_ID];
                    $module = $model->get_row_by_id(Module::TABLE, $module_id, Module::F_ID);
                    $alert .= "Модуль [{$module_id}] {$module[Lang::code().Module::_TITLE]}...\n";

                    $roles = $model->get_rows_by_where(Role::TABLE);

                    foreach ($roles as &$role_row) {
                        $role_id = $role_row[Role::F_ID];
                        $perm_rows = $model->get_rows_by_where(Perm::TABLE, "`".Perm::F_ROLE_ID."`={$role_id} and `".Perm::F_MODULE_ID."`={$module_id}");
                        if (count($perm_rows) == 0) {
                            $perm_value = Perm::NONE_VALUE;
                        } else {
                            $perm_row = $perm_rows[array_key_first($perm_rows)];
                            $perm_value = $perm_row[Perm::F_PERM];
                        }


                        $perm_rec = get_permission_rec($perm_value);

                        $access_check_boxes = array(
                            'ID' =>
                                "<input type=hidden name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::F_ROLE_ID."] value={$role_id}>",
                            Perm::VIEW_TITLE => ($perm_value  & Perm::VIEW_VALUE)
                                ? "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::VIEW_TITLE."] value=1 checked title='". Perm::VIEW_DESCR."'>"
                                : "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::VIEW_TITLE."] value=1         title='". Perm::VIEW_DESCR."'>",
                            Perm::EDIT_TITLE => ($perm_value  & Perm::EDIT_VALUE)
                                ? "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::EDIT_TITLE."] value=1 checked title='". Perm::EDIT_DESCR."'>"
                                : "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::EDIT_TITLE."] value=1         title='". Perm::EDIT_DESCR."'>",
                            Perm::ADD_TITLE  => ($perm_value  & Perm::ADD_VALUE)
                                ? "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::ADD_TITLE."]  value=1 checked title='". Perm::ADD_DESCR."'>"
                                : "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::ADD_TITLE."]  value=1         title='". Perm::ADD_DESCR."'>",
                            Perm::DEL_TITLE  => ($perm_value  & Perm::DEL_VALUE)
                                ? "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::DEL_TITLE."]  value=1 checked title='". Perm::DEL_DESCR."'>"
                                : "<input class=form-check-input type=checkbox name=".Perm::POST_REC."[{$module_id}][{$role_id}][".Perm::DEL_TITLE."]  value=1         title='". Perm::DEL_DESCR."'>",
                        );


                        $role_row['perm_value'] = $perm_value;
                        $role_row['perm_rec'] = [$access_check_boxes];


                    }
                    $this->setVariables([
                        'module' => $module,
                        'roles' => $roles,
                        'alert' => $alert
                    ]);

                } else {
                    throw new \Exception('ModuleController::pravaAction(): ID модуля указан не верно.');
                }
            } else {
                throw new \Exception('ModuleController::pravaAction(): ID модуля не указан.');
            }
    }


}
