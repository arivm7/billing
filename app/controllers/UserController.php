<?php
/*
 *  Project : my.ri.net.ua
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
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;

use Config\Auth;
use config\SessionFields;
use config\tables\Module;
use config\tables\User;
use Valitron\Validator;
require_once DIR_LIBS . '/phone_functions.php';

/**
 * Description of UserController
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class UserController extends AppBaseController {


    public function validate_deep(array $user): bool {
        $model = new UserModel();

        /**
         * Login == ID -- это хорошо
         */
        if ($user[User::F_ID] == $user[User::F_LOGIN] ) { return true; }

        /**
         *  Логин должен начинаться с буквы и содержать указаное количество символов: латиница, цифры, '-', '_', '.'";
         */
        if (!preg_match('/'.App::get_config('login_content').'/u', $user[User::F_LOGIN])) {
            MsgQueue::msg(MsgType::ERROR, [
                __("Логин должен начинаться с буквы, содержать %s символов, включающих: латиница, цифры, символы '-', '_', '.'", 
                    App::get_config('login_length_min')."–".App::get_config('login_length_max')),
                __("Если не уверены, то оставьте логин равным номеру договора"),
            ]);
            return false;
        }

        /**
         * Логин не должен встречаться у других пользователей
         */
        $count = $model->get_count(
            User::TABLE, 
            "(`".User::F_LOGIN."`='".$user[User::F_LOGIN]."') AND (`".User::F_ID."`!='".$user[User::F_ID]."')", 
            User::F_ID
        );
        return ($count == 0);
    }

    /**
     * Проверяет данные перед сохранением пользователя
     * Ошибки пишутся в очередь сообщений в сессию
     *
     * @param array $data  Входные данные (например, $_POST['userRec'])
     * @param bool  $isNew true — при создании, false — при обновлении
     * @return boolean
     */
    public function validate(array $data, bool $isNew = false): bool
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
        }, __('должно быть телефоном, username или ссылкой на Telegram'));

        /**
         * Viber обычно не имеет веб-версии, поэтому остаётся проверка: телефон или username
         */
        Validator::addRule('viber_valid', function ($field, $value, $params, $fields) {
            if (empty($value)) {return \true;} // необязательное поле
            return isPhone($value) || isUsername($value);
        }, __('должно быть телефоном или username'));

        /**
         * Jabber
         */
        Validator::addRule('jabber', function ($field, $value, $params, $fields) {
            if (empty($value)) {return \true;} // необязательное поле
            return isJabberFull($value);
        }, __('должно быть корректным Jabber/XMPP адресом'));

        Validator::lang(Lang::code());

        $v = new Validator($data);

        $v->labels([
            User::F_LOGIN            => __('Логин'),
            User::F_FORM_PASS        => __('Новый пароль'),
            User::F_FORM_PASS2       => __('Подтверждение нового пароля'),
            User::F_NAME_SHORT       => __('Отображаемое имя'),
            User::F_EMAIL_MAIN       => __('Эл. почта'),
            User::F_PHONE_MAIN       => __('Телефон'),
            User::F_ADDRESS_INVOICE  => __('Почтовый адрес'),
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
        $v->rule('lengthBetween', User::F_LOGIN, App::get_config('login_length_min'), App::get_config('login_length_max'));
        // проверяется отдельной функцией
        //   ->rule('regex', User::F_LOGIN, '/'.App::get_config('login_content').'/')
        //   ->message('{field} может содержать только латинские буквы, цифры и символы ._- ('.App::get_config('login_content').')');

        // --- ПАРОЛЬ ---
        if (!empty($data[User::F_FORM_PASS])) {
            $v->rule('lengthMin', User::F_FORM_PASS, App::get_config('pass_length_min'))
              ->message(__('Пароль должен содержать не менее $s символов', App::get_config('pass_length_min')));
            $v->rule('equals', User::F_FORM_PASS2, User::F_FORM_PASS)
              ->message(__('Пароли не совпадают'));
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
        /**
         * Как оно работает
         * часть	        описание
         * ^ / $	        начало и конец строки
         * (?:[^<>]+<)?	    необязательная часть до угловой скобки, например: Ирина<
         * [A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}	-- сам email-адрес
         * (?:>)?	        необязательная закрывающая >
         * i	            делает проверку без учёта регистра
         */
        $v->rule('regex', User::F_EMAIL_MAIN, '/^(?:[^<>]+<)?[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}(?:>)?$/i')
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
        if (!$v->validate() || !$this->validate_deep($data)) {
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

        // Устанавливаем Флаги: если чекбокс не пришёл — ставим 0
        foreach (User::T_FLAGS as $field=>$def_value) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = 0;
            } else {
                $data[$field] = ((($data[$field] == 'on') || ($data[$field] == '1') || $data[$field]) ? 1 : 0);
            }
        }

        // Email —  trim (strtolower убрал, поскольку email может быть с именем)
        if (!empty($data[User::F_EMAIL_MAIN])) {
            $data[User::F_EMAIL_MAIN] = trim($data[User::F_EMAIL_MAIN]);
        }

        // Телефон
        if (!empty($data[User::F_PHONE_MAIN])) {
            $data[User::F_PHONE_MAIN] = simpleCleaningPhoneNumber($data[User::F_PHONE_MAIN]);
        }

        // Проверяем наличие месенджеров и установки флагов
        foreach (User::MESSENGERS as $mes) {
            if (empty($data[$mes['field']])) {
                $data[$mes['send']] = 0;
            }
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
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route', die: 0);

        $model = new UserModel();

        if  (
                isset($_POST[User::POST_REC]) && is_array($_POST[User::POST_REC]) &&
                ((int)$_POST[User::POST_REC][User::F_ID] == (int)$this->route[F_ALIAS]) &&
                $model->validate_id(table_name: User::TABLE, field_id: User::F_ID, id_value: (int)$this->route[F_ALIAS])
            ) 
        {

            $user = $model->get_user((int)$this->route[F_ALIAS]);

            // Копируем только разрешённые поля
            foreach (User::FORM_FIELDS as $field=>$def_value) {
                if (array_key_exists($field, $_POST[User::POST_REC])) {
                    $user_rec[$field] = $_POST[User::POST_REC][$field];
                }
            }

            // Нормализация (очистка и форматирование данных)
            $this->normalize($user_rec);

            // Проверка
            if ($this->validate($user_rec)) {

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
                    if ($model->update_row_by_id(table: User::TABLE, row: $user_rec, field_id: User::F_ID)) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, 'Данные внесены');
                    } else {
                        $_SESSION[SessionFields::FORM_DATA][User::POST_REC] = $_POST[User::POST_REC];
                        MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                    }
                }
            } else {
                $_SESSION[SessionFields::FORM_DATA][User::POST_REC] = $_POST[User::POST_REC];
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, 'Данные не переданы или не верны');
        }
        redirect();
    }


    function editAction() { 
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR,__('Авторизуйтесь, пожалуйста'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_edit([Module::MOD_MY_USER_CARD, Module::MOD_USER_CARD])) {
            MsgQueue::msg(MsgType::ERROR,__('Нет прав'));
            redirect();
        }

        $model = new UserModel();

        if  (
                isset($this->route[F_ALIAS]) && is_numeric($this->route[F_ALIAS]) &&
                $model->validate_id(User::TABLE, intval($this->route[F_ALIAS]), User::F_ID)
            )
        {
            $user = $model->get_row_by_id(User::TABLE, intval($this->route[F_ALIAS]), User::F_ID);
            View::setMeta(__('Редактирование карточки пользователя'));
            $this->setVariables([
                'user'=> $user,
            ]);
        } else {    
            MsgQueue::msg(MsgType::ERROR, __('ID не верен или не указан'));
            redirect();
        }
    }




}
