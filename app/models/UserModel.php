<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : UserModel.php
 *  Path    : app/models/UserModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use config\tables\Firm;
use config\tables\TSUserFirm;
use Valitron\Validator;
use billing\core\PhoneTools;
use config\tables\User;
use config\tables\TP;
use config\tables\TSUserTp;
use config\tables\Contacts;
use billing\core\base\Lang;


/**
 * Время истечение срока действия cookie. Это метка времени Unix.
 * Это значение добавляется к текущей метке времени time() при установке срока действия cookie.
 * Например, выражение time() + 60 * 60 * 24 * 30 установит срок действия cookie, который закончится через 30 дней.
 * Другой вариант — вызвать функцию mktime().
 * Если задать 0 или пропустить аргумент, срок действия cookie закончится с окончанием сессии (при закрытии браузера).
 */
define('COOKIE_EXPIRES', time() + 60 * 60 * 24 * 3);

/**
 * Прошедшее время истечения срока дейсвия куки для её отключения
 */
define('COOKIE_TIMEOFF', time() - 1);


/**
 * Description of UserModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class UserModel extends AppBaseModel{

    public const COOKIE_FIELD_LOGIN = 'login';
    public const COOKIE_FIELD_PASSW = 'passw';
    public const COOKIE_EXPIRES = COOKIE_EXPIRES;
    public const COOKIE_TIMEOFF = COOKIE_TIMEOFF;
    public const COOKIE_PATH = "/";
    public const COOKIE_DOMAIN = URL_DOMAIN;
    public const COOKIE_SECURE = true;
    public const COOKIE_HTTPONLY = true;

    public array $attributes = User::T_FIELDS;

    public array $rules = [
        'required' => [
         // [User::F_LOGIN],
            [User::F_FORM_PASS],
            [User::F_FORM_PASS2],
            [User::F_PHONE_MAIN]
        ],
        'email' => [
            [User::F_EMAIL_MAIN],
        ],
        'lengthMin' => [
            [User::F_LOGIN, 2],
            [User::F_FORM_PASS, 6],
            [User::F_PHONE_MAIN, 10],
        ],
        'equals' => [
            [User::F_FORM_PASS, User::F_FORM_PASS2]
        ],

    ];



    /**
     * Копирование данных из $data в $this->attributes
     * @param array $data
     */
    public function setAttributes(array $data) {
        foreach ($this->attributes as $name => $value) {
            if (isset($data[$name])) {
                $this->attributes[$name] = $data[$name];
            }
        }
    }



    public function validate($data) {
        Validator::lang(Lang::code());
        $v = new Validator($data);
        $v->rules($this->rules);
        if ($v->validate()) {
            return true;
        }
        $this->errors = $v->errors();
        return false;
    }



    /**
     * Проверяет уникальность указанного поля
     * из $this->attributes[$field] в базе в таблице User::TABLE[$field].
     * со значение
     * @param string $field
     * @param bool $emptyIsUnique
     * @return bool
     */
    public function checkUnique(string $table = User::TABLE, string $field = User::F_LOGIN, bool $emptyIsUnique = false) {
        if (!empty($this->attributes[$field])) {
            $sql = "SELECT * FROM `" . $table . "` WHERE `login`='".$this->attributes[$field]."' LIMIT 1";
            $users = $this->get_rows_by_sql($sql);
            if (count($users) > 0 ) {
                $this->errors['unique'][] = 'Этот логин уже занят';
                return false;
            } else {
                return true;
            }
        }
        return $emptyIsUnique;
    }



    /**
     * Проверяет номер телефона в $this->attributes[] и приводит его к полному международному формату.
     * В случае успеха возвращает TRUE и нормализованное значени записывает в $this->attributes[]
     * Ву случае ошибки возвращает FALSE и ошибки зщаписываются в $this->errors['phone_number']
     * @return bool
     */
    public function cleaningPhones(): bool {
        try {
            $cleanPhone = PhoneTools::cleaning($this->attributes[User::F_PHONE_MAIN]);
        } catch (\Exception $e) {
            $this->errors['phone_number'][] = $e->getMessage();
            return false;
        }
        $this->attributes[User::F_PHONE_MAIN] = $cleanPhone;
        return true;
    }



    /**
     * Проверяет аутентификацию (правильность) введённого пародя
     * сверяет входной пароль ссо "старым" (md5) или "новым" (hash) паролем в базе
     * $user_data может быть записью из базы, должна иметь поля паролей:
     * поля нового пароля: $user_data[User::F_PASS_HASH]
     * поля старого пароья: $user_data[User::F_PASS_MD5] + $user_data[User::F_SALT]
     * @param string $password -- открытый текстовый пароль
     * @param array $user_data -- рекорд с данными пользователя
     * @return bool
     */
    protected function checkPassword(string $password, array $user_data): bool {
        if ($user_data[User::F_PASS_HASH]) {
            if (password_verify($password, $user_data[User::F_PASS_HASH])) {
                return true;
            }
        }
        if ($user_data[User::F_PASS_MD5]) {
            if ($this->get_md5_pass($password, $user_data[User::F_SALT]) == $user_data[User::F_PASS_MD5]) {
                return true;
            }
        }
        return false;
    }


    /**
     * Обёртка для сохранения cookies
     * @param string $name
     * @param string $value
     * @param int $expires_or_options
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    static function setcookie(
            string $name,
            string $value = "",
            int $expires_or_options = 0,
            string $path = self::COOKIE_PATH,
            string $domain = self::COOKIE_DOMAIN,
            bool $secure = self::COOKIE_SECURE,
            bool $httponly = self::COOKIE_HTTPONLY): bool
    {
        return \setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
    }



    /**
     * Создаёт пользователя в базе с параметрами из $this->attributes
     * @param array|null $user_data
     * @return bool
     */
    public function userSave(array|null $user_data = null): bool {
        if (!$user_data) {
            $user_data = $this->attributes;
        }
        $userRecord = [
            User::F_ID => null,
            User::F_LOGIN => !empty($this->attributes[User::F_LOGIN]) ? $this->attributes[User::F_LOGIN] : null,
            User::F_PASS_HASH => $this->attributes[User::F_PASS_HASH],
            User::F_NAME_FULL => $this->attributes[User::F_NAME_FULL],
            User::F_NAME_SHORT => $this->attributes[User::F_NAME_SHORT],
            User::F_PHONE_MAIN => $this->attributes[User::F_PHONE_MAIN],
            User::F_EMAIL_MAIN  => $this->attributes[User::F_EMAIL_MAIN],
            User::F_PRAVA => '0',
            User::F_SMS_DO_SEND => '1',
            User::F_EMAIL_DO_SEND => '0',
            User::F_CREATION_UID => User::UID_BILLING,
            User::F_CREATION_DATE => time(),
            User::F_MODIFIED_UID => User::UID_BILLING,
            User::F_MODIFIED_DATE => time(),
        ];
        if ($this->insert_row(table: User::DB_TABLE, row: $userRecord)) {
            $id = $this->lastInsertId();
            $row = $this->get_row_by_id(User::DB_TABLE, $id, User::F_ID);
            if (empty($row[User::F_LOGIN]))     { $row[User::F_LOGIN] = $id; }
            if (empty($row[User::F_NAME_FULL])) { $row[User::F_NAME_FULL] = $id; }
        if (empty($row[User::F_NAME_SHORT]))    { $row[User::F_NAME_SHORT] = $id; }
            debug($row, 'row');
            if ($this->update_row_by_id(User::DB_TABLE, $row, User::F_ID)) {
                $this->errors = [];
                $this->success['signUp'][] = 'Вы успешно зарегистрированы';
                $this->success['signUp'][] = "ID: {$id}";
                $this->success['signUp'][] = "Login: {$row[User::F_LOGIN]}";
                $this->success['signUp'][] = "Phone: {$row[User::F_PHONE_MAIN]}";
                $this->success['signUp'][] = "Email: {$row[User::F_EMAIL_MAIN]}";
                return true;
            } else {
                $this->success = [];
                $this->success['signUp'][] = 'По какой-то причине бновление данных не удалось';
                return false;
            }
        } else {
            $this->success = [];
            $this->success['signUp'][] = 'Запись данных в базу не удалась';
            return false;
        }
        throw new \Exception("Этого не должно быть");
    }



    /**
     * Возвращает из базы ассоциативный массив со списком ТП разрешенных текущему авторизованному пользователю.
     * Если парамерт-фильтр установлен в null, то он не участвует в запросе и выбираются все значения.
     * @param int|null $abon_id
     * @param int|null $status -- 0 — Отключен/демонтирован, 1 — Работает
     * @param int|null $deleted -- ТП физически демонтирована, её больше нет.
     * @param int|null $is_managed -- Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН
     * @param int|null $rang_id -- Ранг узла: 1 — Абонентский узел | 2 — AP | 3 — Агрегатор AP | 4 — Bridge AP | 5 — Bridge Client | 10 — Хостинговая тех. площадка | 100 — Биллинг
     * @return array
     */
    function get_my_tp_list(
            int|null $abon_id = null,
            int|null $status = 1,
            int|null $deleted = null,
            int|null $is_managed = null,
            int|null $rang_id = null): array
    {
        $my = (is_null($abon_id) ? $_SESSION[User::SESSION_USER_REC][User::F_ID] : $abon_id);
        if (!$this->validate_id(table_name: User::TABLE, id_value: $my, field_id: User::F_ID)) {
            throw new \Exception("ID[{$abon_id}] No Valid");
        }
        $sql = "SELECT "
                . "".TP::TABLE.".* "
                . "FROM `".TSUserTp::TABLE."` "
                . "LEFT JOIN ".TP::TABLE." ON ".TSUserTp::TABLE.".".TSUserTp::F_TP_ID." = ".TP::TABLE.".".TP::F_ID." "
                . "WHERE "
                . "(`".TSUserTp::F_USER_ID."`={$my}) "

                . (!is_null($status) ? "AND (`status`={$status})" : "")
                . (!is_null($deleted) ? "AND (`deleted`={$deleted})" : "")
                . (!is_null($is_managed) ? "AND (`is_managed`={$is_managed})" : "")
                . (!is_null($rang_id) ? "AND (`rang_id`={$rang_id})" : "");
        return $this->get_rows_by_sql($sql);
    }



    /**
     *Возвращает список контактов пользователя
     * @param int $user_id
     * @param int|bool|null $has_deleted -- если null, то не используется, иначе участвует в выборке
     * @return array
     */
    function get_contacts(int $user_id, int|bool|null $has_deleted = 0): array {
        return $this->get_rows_by_where(
                table: Contacts::TABLE,
                where: "(`".Contacts::F_USER_ID."`={$user_id}) ".(is_null($has_deleted) ? "" : "AND (`".Contacts::F_IS_HIDDEN."`={$has_deleted})")."",
                order_by: "`".Contacts::F_CREATION_DATE."` DESC");
    }



    /**
     * Возвращает список предприятий, подключённых к пользователю
     * @param int $user_id
     * @return array
     */
    function get_firms(int $user_id): array {
        $sql = "SELECT "
                    . "* "
                . "FROM "
                    . "`".Firm::TABLE."` "
                . "WHERE "
                    . "`".Firm::F_ID."` IN ("
                        . "SELECT `".TSUserFirm::F_FIRM_ID."` FROM `".TSUserFirm::TABLE."` WHERE `".TSUserFirm::F_USER_ID."`={$user_id}"
                    . ")";
        return $this->get_rows_by_sql(sql: $sql);
    }



    /**
     * Возвращает ID пользователя к которому относится контакт
     * @param int $contact_id
     * @return int
     */
    function get_contact_owner(int $contact_id): int {
        $contact = $this->get_row_by_id(table_name: Contacts::TABLE, field_id: Contacts::F_ID, id_value: $contact_id);
        return $contact[Contacts::F_USER_ID];
    }


}