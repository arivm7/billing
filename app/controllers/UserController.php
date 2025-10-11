<?php

/*
 *  Project : s1.ri.net.ua
 *  File    : UserController.php
 *  Path    : app/controllers/UserController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 10 окт. 2025 г. 03:43:42
 *  License : GPL v3
 *
 *  Copyright (C) 2006-2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\models\UserModel;
use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\Model;
use billing\core\MsgQueue;
use billing\core\MsgType;

use config\SessionFields;
use config\tables\User;
use Valitron\Validator;
require_once DIR_LIBS . '/phone_functions.php';

/**
 * Description of UserController
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class UserController extends AppBaseController {



    /**
     * Проверяет данные перед сохранением пользователя
     *
     * @param array $data  Входные данные (например, $_POST['userRec'])
     * @param bool  $isNew true — при создании, false — при обновлении
     * @return array ['success' => bool, 'errors' => array]
     */
    public static function validate(array $data, bool $isNew = false): bool
    {
        // Инициализация валидатора

        /**
         * Telegram может быть:
         *  -- Номер телефона (для регистрации через SMS).
         *  -- Username (например, @username).
         *  -- Веб-ссылка (например, https://t.me/username).
         */
        Validator::addRule('telegram_valid', function ($field, $value, array $params, array $fields) {
            if (empty($value)) {return \true;} // необязательное поле
            return isPhone($value) || isUsername($value) || isTelegramWeb($value);
        }, 'должно быть телефоном, username или ссылкой на Telegram');

        /**
         * Viber обычно не имеет веб-версии, поэтому остаётся проверка: телефон или username
         */
        Validator::addRule('viber_valid', function ($field, $value, $params, $fields) {
            if (empty($value)) {return \true;} // необязательное поле
            return isPhone($value) || isUsername($value);
        }, 'должно быть телефоном или username');

        /**
         * Jabber
         */
        Validator::addRule('jabber', function ($field, $value, $params, $fields) {
            if (empty($value)) {return \true;} // необязательное поле
            return isJabberFull($value);
        }, 'должно быть корректным Jabber/XMPP адресом');

        Validator::lang(Lang::code());

        $v = new Validator($data);

        $v->labels([
            User::F_LOGIN            => 'Логин',
            User::F_FORM_PASS        => 'Новый пароль',
            User::F_FORM_PASS2       => 'Подтверждение нового пароля',
            User::F_NAME_SHORT       => 'Отображаемое имя',
            User::F_EMAIL_MAIN       => 'E-mail',
            User::F_PHONE_MAIN       => 'Телефон',
            User::F_EMAIL_MAIN       => 'Эл. почта',
            User::F_ADDRESS_INVOICE  => 'Почтовый адрес',
        ]);

        // --- ОБЯЗАТЕЛЬНЫЕ ПОЛЯ ---
        $requiredFields = [User::F_LOGIN, User::F_NAME_SHORT];
        if ($isNew) {
            // при создании требуем пароль
            $requiredFields[] = User::F_FORM_PASS;
            $requiredFields[] = User::F_FORM_PASS2;
        }
        $v->rule('required', $requiredFields);

        // --- ЛОГИН ---
        $v->rule('lengthBetween', User::F_LOGIN, App::get_config('login_length_min'), App::get_config('login_length_max'))
          ->rule('regex', User::F_LOGIN, '/'.App::get_config('login_content').'/')
          ->message('{field} может содержать только латинские буквы, цифры и символы ._- ('.App::get_config('login_content').')');

        // --- ПАРОЛЬ ---
        if (!empty($data[User::F_FORM_PASS])) {
            $v->rule('lengthMin', User::F_FORM_PASS, App::get_config('pass_length_min'))
              ->message('Пароль должен содержать минимум '.App::get_config('pass_length_min').' символов');
            $v->rule('equals', User::F_FORM_PASS2, User::F_FORM_PASS)
              ->message('Пароли не совпадают');
        }

        // --- ТЕЛЕФОН ---
        if (!empty($data[User::F_PHONE_MAIN])) {
            $v->rule('regex', User::F_PHONE_MAIN, '/^[0-9+\-\(\)\s]+$/')
              ->message('Телефон может содержать только цифры, +, -, (), и пробелы');
        }

        // --- ОТОБРАЖАЕМОЕ ИМЯ ---
        $v->rule('lengthMax', User::F_NAME_SHORT, 80);

        // --- Флаги ---
        foreach (User::T_FLAGS as $flag => $default) {
            if (isset($data[$flag])) {
                $v->rule('integer', $flag);
                $v->rule('in', $flag, [0, 1])
                  ->message("Поле {$flag} должно быть 0 или 1");
            }
        }


        // Email
        $v->rule('optional', User::F_EMAIL_MAIN);
        $v->rule('email', User::F_EMAIL_MAIN)
          ->message('Неверный формат e-mail');


        /**
         * Viber
         * обычно идентифицируется по номер телефона или имени пользователя.
         */
        $v->rule('optional', User::F_VIBER);
        $v->rule('viber_valid', User::F_VIBER);

        /**
         * Telegram
         */
        $v->rule('optional', User::F_TELEGRAM);
        $v->rule('telegram_valid', User::F_TELEGRAM);

        /**
         * Jabber/XMPP
         */
        $v->rule('optional', User::F_JABBER);
        $v->rule('jabber', User::F_JABBER);

        // --- Проверка ---
        if (!$v->validate()) {
            MsgQueue::msg(type: MsgType::ERROR, message: $v->errors());
            return false;
        }

        return true;
    }



    public function normalize(array &$data) {

        // Убираем лишние пробелы
        foreach (User::T_FIELDS as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        // Email —  trim (strtolower не включил, поскольку email может біть с именем)
        if (!empty($data[User::F_EMAIL_MAIN])) {
            $data[User::F_EMAIL_MAIN] = trim($data[User::F_EMAIL_MAIN]);
        }

        // Телефон
        if (!empty($data[User::F_PHONE_MAIN])) {
            $data[User::F_PHONE_MAIN] = simpleCleaningPhoneNumber($data[User::F_PHONE_MAIN]);
        }
    }



    public function update_new_pass(array &$data) {
        if  (
                !empty($data[User::F_FORM_PASS]) &&
                !empty($data[User::F_FORM_PASS2]) &&
                ($data[User::F_FORM_PASS] == $data[User::F_FORM_PASS2])
            )
        {
            $data[User::F_PASS_HASH] = Model::get_hash_pass(pass: $data[User::F_FORM_PASS]);
            MsgQueue::msg(MsgType::INFO_AUTO, 'Пароль успешно обновлён.');
        }
        unset($data[User::F_FORM_PASS]);
        unset($data[User::F_FORM_PASS2]);
    }



    function updateAction() {
        debug($_GET, '$_GET');
        debug($_POST, '$_POST');
        debug($this->route, '$this->route');

        $db = new UserModel();

        if (isset($_POST[User::POST_REC]) && is_array($_POST[User::POST_REC])) {

            $post_rec = $_POST[User::POST_REC];

            if ($db->validate_id(table_name: User::TABLE, field_id: User::F_ID, id_value: (int)$this->route[F_ALIAS])) {

                $user = $db->get_user((int)$this->route[F_ALIAS]);

                // Копируем только разрешённые поля
                foreach (User::FORM_FIELDS as $field=>$def_value) {
                    if (array_key_exists($field, $post_rec)) {
                        $user_rec[$field] = $post_rec[$field];
                    }
                }

                // Флаги: если чекбокс не пришёл — ставим 0
                foreach (User::T_FLAGS as $field=>$def_value) {
                    if (!array_key_exists($field, $user_rec)) {
                        $user_rec[$field] = 0;
                    }
                }

                // Проверка
                if ($this->validate($user_rec)) {

                    // Нормализация (очистка и форматирование данных)
                    $this->normalize($user_rec);

                    // Проверка и обновление пароля
                    $this->update_new_pass($user_rec);

                    // проверка наличия ID
                    if (empty($user_rec[User::F_ID])) {
                        $user_rec[User::F_ID] = (int)$this->route[F_ALIAS];
                    }

                    // сравнение новой записи и старой
                    $equals = true;
                    foreach ($user_rec as $field => $value) {
                        if ($user[$field] != $value) {
                            $equals = false;
                            break;
                        }
                    }

                    if ($equals) {
                        // Новые данные равны старым данным
                        MsgQueue::msg(MsgType::INFO_AUTO, 'Изменений нет. Нечего вносить в базу.');

                    } else {
                        // Данные различаются
                        if ($db->update_row_by_id(table: User::TABLE, row: $user_rec, field_id: User::F_ID)) {
                            MsgQueue::msg(MsgType::SUCCESS_AUTO, 'Данные внесены');
                        } else {
                            $_SESSION[SessionFields::FORM_DATA][User::POST_REC] = $_POST[User::POST_REC];
                            MsgQueue::msg(MsgType::ERROR, $db->errorInfo());
                        }
                    }
                } else {
                    $_SESSION[SessionFields::FORM_DATA][User::POST_REC] = $_POST[User::POST_REC];
                }
            } else {
                $_SESSION[SessionFields::FORM_DATA][User::POST_REC] = $_POST[User::POST_REC];
                MsgQueue::msg(MsgType::ERROR, 'User ID не верен');
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, 'Данные не переданы');
        }
        redirect();
    }




}
