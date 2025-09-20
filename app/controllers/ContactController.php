<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : ContactController.php
 *  Path    : app/controllers/ContactController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;



use app\models\AbonModel;
use billing\core\App;
use billing\core\base\Lang;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\PhoneTools;
use config\tables\Contacts;
use config\tables\Module;
use config\tables\User;
use Valitron\Validator;

/**
 * Description of ContactController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class ContactController extends AppBaseController {



    function validate(array $rec): bool {
        Validator::lang(Lang::code());
        $validator = new Validator($rec);

        $rules = [
            'required' => [
                Contacts::F_TITLE,
                Contacts::F_VALUE,
            ],
            'lengthMin' => [
                [Contacts::F_TITLE, 2],
                [Contacts::F_VALUE, 3],
            ],
            'lengthMax' => [
                [Contacts::F_TITLE, 100],
                [Contacts::F_VALUE, 100],
            ],
        ];
        $validator->rules($rules);

        if ($rec[Contacts::F_TYPE_ID] == Contacts::T_EMAIL) {
            $validator->rule('email', Contacts::F_VALUE);
        }

        $result = true;

        switch ($rec[Contacts::F_TYPE_ID]) {
            case Contacts::T_PHONE:
                try {
                    $phone = PhoneTools::simpleCleaning($rec[Contacts::F_VALUE]);
                } catch (\Exception $exc) {
                    MsgQueue::msg(MsgType::ERROR, $exc->getMessage());
                    $result = false;
                }
                break;

            case Contacts::T_EMAIL:
            case Contacts::T_TELEGRAM:
            case Contacts::T_VIBER:
            case Contacts::T_SIGNAL:
            case Contacts::T_WHATSAPP:
            case Contacts::T_NEXTCLOUD:
            case Contacts::T_IRC:
            case Contacts::T_ADDRESS:
                if ($rec[Contacts::F_TYPE_ID] != Contacts::autoType($rec[Contacts::F_VALUE])) {
                    MsgQueue::msg(MsgType::ERROR, [
                        __('The specified type does not match the content | Указанный тип не соответсвует содержимому'),
                        __('If you are not sure about the data type, it is better to leave the automatic type. | Если не уверены в типе данных, то лучше оставьте автоматический тип')
                    ]);
                    $result = false;
                }
                break;

            default:
                break;
        }

        if(!$validator->validate()) {
            // сохраняем ошибки в сессию
            MsgQueue::msg(MsgType::ERROR, $validator->errors());
            $result = false;
        }
        return $result;
    }



    function addAction() {

        if  (
                isset($_POST[Contacts::POST_REC]) &&
                is_array($_POST[Contacts::POST_REC]) &&
                App::$auth->isAuth
            )
        {
            $model = new AbonModel();
            $rec = $_POST[Contacts::POST_REC];
            if  (
                    (
                        /**
                         * Текущий пользователь владелец записи
                         */
                        $_SESSION[User::SESSION_USER_REC][User::F_ID] == $rec[Contacts::F_USER_ID] &&
                        /**
                         * Имеет право на добавление
                         */
                        can_add([Module::MOD_MY_CONTACTS])
                    ) ||
                    (
                        /**
                         * Это сторонний пользователь
                         * Должен иметь право на добавление в этом модуле
                         */
                        can_add([Module::MOD_CONTACTS])
                    )
                )
            {
                /**
                 * Имеет право на редактирование
                 */
                $row[Contacts::F_IS_HIDDEN] = 0;
                $row[Contacts::F_USER_ID]   = (int)h($rec[Contacts::F_USER_ID]);
                $row[Contacts::F_TYPE_ID]   = ($rec[Contacts::F_TYPE_ID] > Contacts::T_AUTO
                        ? $rec[Contacts::F_TYPE_ID]
                        : Contacts::autoType($rec[Contacts::F_VALUE]));
                $row[Contacts::F_TITLE]     = (empty($rec[Contacts::F_TITLE])
                        ? ($row[Contacts::F_TYPE_ID] > Contacts::T_AUTO
                            ? Contacts::TYPES[$row[Contacts::F_TYPE_ID]]
                            : "")
                        : cleaner_html($rec[Contacts::F_TITLE]));
                $row[Contacts::F_VALUE]     = ($row[Contacts::F_TYPE_ID] == Contacts::T_PHONE
                        ? PhoneTools::simpleCleaning($rec[Contacts::F_VALUE])
                        : $rec[Contacts::F_VALUE]);
                $row[Contacts::F_CREATION_DATE] = time();
                $row[Contacts::F_CREATION_UID] = $_SESSION[User::SESSION_USER_REC][User::F_ID];
                $row[Contacts::F_MODIFIED_DATE] = time();
                $row[Contacts::F_MODIFIED_UID] = $_SESSION[User::SESSION_USER_REC][User::F_ID];

//                debug($row, '$row', die: 1);

                if ($this->validate($row)) {
                    if ($model->insert_row(table: Contacts::TABLE, row: $row)) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Data added successfully | Данные Добавлены успешно') . ' ' . $model->lastInsertId());
                    } else {
                        MsgQueue::msg(MsgType::ERROR, __('Error adding data to the database | Ошибка добавления данных в базу'));
                        MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                    }
                }
            }
        }
        /**
         * НЕ имеет права на редактирование
         */
        redirect();
    }



    function editAction() {
        if  (
                isset($_POST[Contacts::POST_REC]) &&
                is_array($_POST[Contacts::POST_REC]) &&
                App::$auth->isAuth
            )
        {
            $model = new AbonModel();
            $contact_id = (int)$this->route[F_ALIAS];
            if  (
                    (
                        /**
                         * Текущий пользователь владелец записи
                         */
                        $_SESSION[User::SESSION_USER_REC][User::F_ID] == $model->get_contact_owner($contact_id) &&
                        /**
                         * Имеет право на редактирование
                         */
                        can_edit([Module::MOD_MY_CONTACTS])
                    ) ||
                    (
                        /**
                         * Это сторонний пользователь
                         * Должен иметь право на редактировани в этом модуле
                         */
                        can_edit([Module::MOD_CONTACTS])
                    )
                )
            {
                /**
                 * Имеет право на редактирование
                 */
                $rec = $_POST[Contacts::POST_REC];
                $row[Contacts::F_ID]      = $contact_id;
                $row[Contacts::F_TYPE_ID] = ($rec[Contacts::F_TYPE_ID] > Contacts::T_AUTO
                                                ? $rec[Contacts::F_TYPE_ID]
                                                : Contacts::autoType($rec[Contacts::F_TYPE_ID]));
                $row[Contacts::F_TITLE]   = cleaner_html($_POST[Contacts::POST_REC][Contacts::F_TITLE]);
                $row[Contacts::F_VALUE]   = PhoneTools::simpleCleaning($_POST[Contacts::POST_REC][Contacts::F_VALUE]);
                if ($this->validate($row)) {
                    if ($model->update_row_by_id(table: Contacts::TABLE, row: $row, field_id: Contacts::F_ID)) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Data updated successfully | Данные обновлены успешно'));
                    } else {
                        MsgQueue::msg(MsgType::ERROR, __('Error entering data into the database | Ошибка внесения данных в базу'));
                        MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                    }
                }
            }
        }
        redirect();
    }



    function visibleAction() {

        if  (
                isset($_GET[Contacts::F_GET_VISIBLE]) &&
                is_numeric($_GET[Contacts::F_GET_VISIBLE]) &&
                App::$auth->isAuth
            )
        {
            $model = new AbonModel();
            $contact_id = (int)$this->route[F_ALIAS];
            if  (
                    (
                        /**
                         * Текущий пользователь владелец записи
                         */
                        $_SESSION[User::SESSION_USER_REC][User::F_ID] == $model->get_contact_owner($contact_id) &&
                        /**
                         * Имеет право на редактирование
                         */
                        can_edit([Module::MOD_MY_CONTACTS])
                    ) ||
                    (
                        /**
                         * Это сторонний пользователь
                         * Должен иметь право на редактировани в этом модуле
                         */
                        can_edit([Module::MOD_CONTACTS])
                    )
                )
            {
                /**
                 * Имеет право на редактирование
                 */
                $row[Contacts::F_ID]        = $contact_id;
                $row[Contacts::F_IS_HIDDEN] = ($_GET[Contacts::F_GET_VISIBLE] ? 0 : 1);
                if ($model->update_row_by_id(table: Contacts::TABLE, row: $row, field_id: Contacts::F_ID)) {
                    MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Data updated successfully | Данные обновлены успешно'));
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Error entering data into the database | Ошибка внесения данных в базу'));
                    MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                }
            }
        }
        redirect();
    }



    function delAction() {

        if  (
                App::$auth->isAuth
            )
        {
            $model = new AbonModel();
            $contact_id = (int)$this->route[F_ALIAS];
            if  (
                    (
                        /**
                         * Текущий пользователь владелец записи
                         */
                        $_SESSION[User::SESSION_USER_REC][User::F_ID] == $model->get_contact_owner($contact_id) &&
                        /**
                         * Имеет право на редактирование
                         */
                        can_del([Module::MOD_MY_CONTACTS])
                    ) ||
                    (
                        /**
                         * Это сторонний пользователь
                         * Должен иметь право на редактировани в этом модуле
                         */
                        can_del([Module::MOD_CONTACTS])
                    )
                )
            {
                /**
                 * Имеет право на редактирование
                 */
                if ($model->delete_rows_by_field(table: Contacts::TABLE, field_id: Contacts::F_ID, value_id: $contact_id)) {
                    MsgQueue::msg(MsgType::SUCCESS_AUTO, __('The entry was deleted successfully | Запись удалена успешно'));
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Error deleting data | Ошибка удаления данных'));
                    MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                }
            }
        }
        redirect();
    }



}