<?php

namespace app\models;
use billing\core\base\Model;
use PHPMailer\PHPMailer\Exception;
use config\tables\User;



class AuthModel extends UserModel {

    public bool $isAuth = false;

        function __construct() {
        parent::__construct();
        $this->attributes[User::F_ID] = $this->get_next_id();
        $this->attributes[User::F_LOGIN] = $this->attributes[User::F_ID];
        $this->isAuth = $this->enter();
    }



    function login(): bool {
        $login = !empty(trim($_POST[User::POST_REC][User::F_LOGIN]))  ? trim($_POST[User::POST_REC][User::F_LOGIN])  : null;
        $paswd = !empty(trim($_POST[User::POST_REC][User::F_FORM_PASS])) ? trim($_POST[User::POST_REC][User::F_FORM_PASS]) : null;
        if ($login && $paswd) {
            $row = $this->get_row_by_id(User::TABLE, $login, User::F_LOGIN);
            if ($row && $this->checkPassword($paswd, $row)) {
                self::session_update($row);
                return true;
            }
        }
        return false;
    }


    function session_valid(string $login, string|null $pass_hash, string|null $pass_md5): bool {
        $user = $this->get_user_by_login($login);
        return (($user[User::F_PASS_HASH] == $pass_hash) || ($user[User::F_PASS_MD5] == $pass_md5));
    }



    /**
     * Обновление сессии и куков
     * @param array $user_data
     * @return void
     */
    static function session_update(array $user_data): void {
        $_SESSION[User::SESSION_USER_REC] = $user_data;
        self::setcookie(name: User::F_LOGIN,     value:  $user_data[User::F_LOGIN],                                           expires_or_options: self::COOKIE_EXPIRES);
        self::setcookie(name: User::F_PASS_HASH, value: ($user_data[User::F_PASS_HASH] ? $user_data[User::F_PASS_HASH] : ''), expires_or_options: self::COOKIE_EXPIRES);
        self::setcookie(name: User::F_PASS_MD5,  value: ($user_data[User::F_PASS_MD5]  ? $user_data[User::F_PASS_MD5]  : ''), expires_or_options: self::COOKIE_EXPIRES);
    }



    /**
     * Очистка сессии
     * @return void
     */
    static function session_clear(): void {
        unset($_SESSION[User::SESSION_USER_REC]);
        self::setcookie(name: User::F_LOGIN,     value: '', expires_or_options: self::COOKIE_TIMEOFF);
        self::setcookie(name: User::F_PASS_HASH, value: '', expires_or_options: self::COOKIE_TIMEOFF);
        self::setcookie(name: User::F_PASS_MD5,  value: '', expires_or_options: self::COOKIE_TIMEOFF);
    }



    /**
     * Автоматический вход.
     * Автоматическое продолжение сессии:
     * Проверяет сессию
     * Проверяет куки
     * Все обновляет
     * @return bool
     */
    function enter(): bool {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        Model::$errors = []; //массив для ошибок
        if  (
                isset($_SESSION[User::SESSION_USER_REC]) &&
                $this->session_valid(
                    login:      $_SESSION[User::SESSION_USER_REC][User::F_LOGIN],
                    pass_hash:  $_SESSION[User::SESSION_USER_REC][User::F_PASS_HASH],
                    pass_md5:   $_SESSION[User::SESSION_USER_REC][User::F_PASS_MD5])
            ) {
                $user = $this->get_user_by_login($_SESSION[User::SESSION_USER_REC][User::F_LOGIN]);
                self::session_update($user);
                return true;
        }

        if  (
                isset($_COOKIE[User::F_LOGIN]) &&
                (isset($_COOKIE[User::F_PASS_HASH]) || isset($_COOKIE[User::F_PASS_MD5])) &&
                $this->session_valid(
                        login:      $_COOKIE[User::F_LOGIN],
                        pass_hash:  (isset($_COOKIE[User::F_PASS_HASH]) ? $_COOKIE[User::F_PASS_HASH] : null),
                        pass_md5:   (isset($_COOKIE[User::F_PASS_MD5])  ? $_COOKIE[User::F_PASS_MD5]  : null))
            ) {
                $user = $this->get_user_by_login($_COOKIE[User::F_LOGIN]);
                self::session_update($user);
                return true;
        }

        self::session_clear();
        return false;
    }



//     /**
//      * Регистрация активности пользователя
//      * @param type $id
//      */
//     function lastAct($id) {
//         throw new Exception('Это еще не реализовано');
//        $rows = $this->model->findBySql(
//                "SELECT * FROM `users_actions` WHERE (`user_id`=?) AND (`date_logout` IS NULL) ORDER BY `id` DESC LIMIT 1", [$id]);
//        if (count($rows) == 1) {
//            // Есть активная запись. Нужно просто обновить дату
//            $row = $rows[array_key_first($rows)];
//            $row['date_login'] = time();
////            debug("lastAct row: ", $row);
////            var_dump($row); echo "<hr>";
//            $where = "`id`=" . $row['id'];
//            $this->model->update_row_by_where("users_actions", $row, $where);
//        } else {
//            // Активной записи нет. Добавляем запись.
//            $row['user_id'] = $id;
//            $row['date_login'] = time();
//            $row['date_logout'] = null;
//            $this->model->insert_row("users_actions", $row);
//        }
//     }



}
