<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Model.php
 *  Path    : billing/core/base/Model.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 19:31:53
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core\base;

use billing\core\Db;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Abon;
use config\SessionFields;
use config\tables\User;

/**
 * Description of Model.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
abstract class Model {

    /**
     * для get_module_id_by_route()
     * возващает это значение если модуль не задан
     */
    public const NO_MODULE  = -1;

    /**
     * Дефолтное имя ID поля
     */
    public const F_ID       = 'id';

    /**
     * Класс подключеня к базе
     * @var Db
     */
    protected           Db $db;

    /**
     * Основная таблица данных
     * @var string
     */
    protected       string $table;
    protected       string $field_id = self::F_ID;

    /**
     * Массив для записи ошибок
     * @var array
     */
    public static    array $errors = [];

    /**
     * Массив записи успешного выполнения
     * @var array
     */
    public static    array $success = [];



    public function __construct() {
        $this->db = Db::instance();
        /**
         *  Резервирование абон=0 для сохранения и отображения нераспределённых платежей
         */
        self::$CACHE_ID_BY_TABLE['abons'][0] = true;
    }



    /**
     * Добавление строки в массив $errors[]
     * для последующего использования errorInfo(), errorsToSession();
     * @param string $msg
     * @return void
     */
    public static function add_error_info(string $msg): void {
        self::$errors[] = $msg;
    }



    /**
     * Добавление строки в массив $success[]
     * для последующего использования successToSession();
     * @param string $msg
     * @return void
     */
    public static function add_success_info(string $msg): void {
        self::$success[] = $msg;
    }



    public function errorsToSession(string $session_field = SessionFields::ERROR) {
//        $errors = '<ul>';
//        foreach ($this->errors as $validator_name => $rows) {
//            foreach ($rows as $item) {
//                $errors .= "<li>{$item}</li>";
//            }
//        }
//        $errors .= '</ul>';
//        $_SESSION[SessionFields::ERROR] = $errors;
        $_SESSION[$session_field] = parce_msg($this->errors);
        $this->errors = [];
    }



    public function successToSession() {
        MsgQueue::msg(MsgType::SUCCESS_AUTO, self::$success);
        self::$success = [];
    }



    /**
     * Возвращает ошибки в записи этого объекта и ошибки базы.
     * (self::$errors + $this->db->errorInfo()
     * @return array
     */
    public function errorInfo(): array {
        $err = array_merge(self::$errors, $this->db->errorInfo());
        self::$errors = [];
        return $err;
    }



    public function errorCode(): ?string {
        return $this->db->errorCode();
    }



    public function lastInsertId(?string $name = null): string|false {
        return $this->db->lastInsertId($name);
    }



    public function execute(string $sql, ?array $params = []): bool {
        return $this->db->execute($sql, $params);
    }



    public function query(string $sql, ?array $params = [], int|null $fetchCell = null, int|null $fetchVector = null): array|int|string {
        return $this->db->query(sql: $sql, params: $params, fetchCell: $fetchCell, fetchVector: $fetchVector);
    }



    public function findAll(): array {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql);
    }



    public function findOne(mixed $fieldValue, string $fieldName = ''): array {
        $fieldName = $fieldName ? : $this->field_id;
        $sql = "SELECT * FROM {$this->table} WHERE $fieldName = ? LIMIT 1";
        return $this->db->query($sql, [$fieldValue]);
    }



    public function findBySql(string $sql, array $params = array()): array {
        return $this->db->query($sql, $params);
    }



    public function findLike(string $findStr, string $fieldName, string $table = ''): array {
        $table = $table ? : $this->table;
        $sql = "SELECT * FROM $table WHERE $fieldName LIKE ?";
        return $this->db->query($sql, ['%'.$findStr.'%']);
    }


    /**
     * Кэш-массив для функции validate_id()
     */
    private static array $CACHE_ID_BY_TABLE = array();

    /**
     * Проверяет в SQL-базе в таблице наличие записи с указанным номером ИД
     * @global array self::$CACHE_ID_BY_TABLE -- Кэш, в котором хранятся результаты запросов
     * @param string $table_name -- простматриваяемая таблица
     * @param int $id_value -- искомое значени ИД
     * @param string $field_id
     * @return bool -- true/false --найдено/нет
     * @throws \Exception -- если ошибка SQL запроса
     */
    public function validate_id(string $table_name, int|string|null $id_value, string $field_id = self::F_ID): bool {
        if (is_empty($id_value)) { return false; }
        if (!((array_key_exists($table_name, self::$CACHE_ID_BY_TABLE)) and (array_key_exists($id_value, self::$CACHE_ID_BY_TABLE[$table_name])))) {
            try {
                $rez = $this->findBySql("SELECT `{$field_id}` FROM `{$table_name}` WHERE `{$field_id}` = {$id_value}");
                self::$CACHE_ID_BY_TABLE[$table_name][$id_value] = (count($rez)==1);
            } catch (\Exception $exc) {
                echo "<pre>" . $exc->getTraceAsString()."</pre><hr>";
                throw new \Exception("validate_id({$table_name}[{$id_value}]): SQL Error: <br>" . print_r($exc->errorInfo, 1) . "<br>");
            }
        }
        return self::$CACHE_ID_BY_TABLE[$table_name][$id_value];
    }



    /**
     * Кэш-массив таблиц и строк
     * для функции get_row_by_id(string $table, int $id)
     */
    private static $CACHE_TABLES_ROWS = array();



    /**
     * Возвращает из кэша строку $id из таблицы $table
     * Если в кэше нет, то читается из базы и пишется в кэш
     * @param string $table_name -- имя таблицы
     * @param int $id_value -- ID строки в таблице
     * @param string $field_id
     * @return array -- запрошенная строка из таблицы
     * @throws \Exception
     */
    public function get_row_by_id(string $table_name, int|string $id_value, string $field_id = self::F_ID): array|null {

        if  (
                !(
                    array_key_exists($table_name, self::$CACHE_TABLES_ROWS) &&
                    array_key_exists($field_id,   self::$CACHE_TABLES_ROWS[$table_name]) &&
                    array_key_exists($id_value,   self::$CACHE_TABLES_ROWS[$table_name][$field_id])
                 )
            )
        {
            try {
                $sql = "SELECT * FROM {$table_name} WHERE `{$field_id}` = '{$id_value}'";
                $rows = $this->findBySql($sql);
                if (count($rows) == 1) {
                    $row = $rows[array_key_first($rows)];
                    self::$CACHE_TABLES_ROWS[$table_name][$field_id][$id_value] = $row;
                } elseif (count($rows) == 0) {
                    self::$CACHE_TABLES_ROWS[$table_name][$field_id][$id_value] = null;
                } else {
                    echo "<h3>Запрос вернул более 1 строки. Должно быть 1 или 0.<h3>";
                    throw new \Exception("get_row_by_id({$table_name}[{$id_value}]): SQL Error: <pre>" . print_r($this->errorInfo(), true) . "</pre><hr>");
                }
            } catch (\Exception $exc) {
                echo "<h2>Ошибка запроса:</h2>" . $exc->getMessage() . "<br>TRACE: <pre>" . print_r($exc->getTrace(), true)  . "</pre><hr>";
                throw new \Exception("get_row_by_id({$table_name}[{$field_id}]=={$id_value}): SQL Error: <pre>" . print_r($this->errorInfo(), true) . "</pre><hr>");
            }
        }
        return self::$CACHE_TABLES_ROWS[$table_name][$field_id][$id_value];
    }



    /**
     * Выполняет запрос к базе и возвращает строки.
     * @param string $sql
     * @param array $params
     * @param string|null $row_id_by -- Указывает поле, устанавливаемое как индексы возвращаемого массива
     * @return array
     * @throws \Exception
     */
    function get_rows_by_sql(string $sql, array $params = array(), string|null $row_id_by = null, bool $unset_row_id_by = false): array {
        try {
            $rows = $this->findBySql($sql, $params);
        } catch (\Exception $exc) {
            throw new \Exception("get_rows_by_sql({$sql}): "
                    . "SQL Error: <pre>" . print_r($this->errorInfo(), true) . "</pre><hr>"
                    . "Trace: <pre>" . $exc->getTraceAsString() . "</pre><hr>");
        }
        if ($row_id_by) {
            $rows_indexed = array();
            foreach ($rows as &$row) {
                $rows_indexed[$row[$row_id_by]] = &$row;
                if ($unset_row_id_by) {
                    unset($row[$row_id_by]);
                }
            }
            return $rows_indexed;
        }
        return $rows;
    }



    /**
     * Возвращает массив строк таблицы, выбранных по выражению в переменной $where.
     * Если указать только имя таблицы, то будет возвращена вся таблица.
     * @param string $table -- таблица, из которой делается віборка
     * @param string $where -- строка с выражением WHERE для SQL запроса
     * @param string $limit
     * @param string $id_alias -- если указать имя, то оно будет назначено алиасом для поля id
     * @param string $order_by -- выражение в разделе ORDER BY
     * @param string|null $row_id_by
     * @return array -- результат выборки таблицы
     */
    function get_rows_by_where(string $table, string|null $where = null, string|int|null $limit = '', string $id_alias="", string|null $order_by = null, string|null $row_id_by = null): array {
        $sql = "SELECT "
                . "*"
                . (is_empty($id_alias) ? "" : ", " . self::F_ID . " AS {$id_alias}")
                . " FROM {$table}"
                . (is_empty($where) ? "" : " WHERE {$where}")
                . (is_empty($order_by) ? "" : " ORDER BY {$order_by}")
                . (is_empty($limit) ? "" : " LIMIT {$limit}")
                ;
        $params = [];
        return $this->get_rows_by_sql(sql: $sql, params: $params, row_id_by: $row_id_by);
    }



    /**
     * Подготавливает SQL-запрос для возврата количества строк в поле COUNT
     * @param string $sql
     * @param string $field_count -- поле, по кторому считать количество
     * @return string
     */
    function get_prepared_sql_for_count(string $sql, string $field_count = self::F_ID): string {
        $sql = preg_replace('/\s+/', ' ', $sql);
        $AS_COUNT = "'/\bAS\s+COUNT\b/i'";
        $SELECT_FROM = '/^SELECT\s+.*?\s+FROM/i';
        $SELECT_COUNT_FROM = "SELECT COUNT(`{$field_count}`) AS COUNT FROM";
        // проверяет, есть ли AS COUNT в любом регистре
        if (!preg_match($AS_COUNT, $sql)) {
            // Заменим всё между SELECT и FROM на COUNT(`id`) AS COUNT
            $sql = preg_replace(pattern: $SELECT_FROM, replacement: $SELECT_COUNT_FROM, subject: $sql);
        }
        return $sql;
    }



    /**
     * Возвращает количество строк в результате SQL запроса
     * @param string $sql
     * @return int
     */
    function get_count_by_sql(string $sql, string $field_count = self::F_ID) {
        $sql = $this->get_prepared_sql_for_count($sql, $field_count);
        $rez = $this->query($sql, []);
        return $rez[0]['COUNT'];
    }



    /**
     * Возвращает количество строк в таблице
     * с применением условия where
     * @param string $table
     * @param string $where
     * @param string $field_count -- поле, по кторому считать количество
     * @return int
     */
    function get_count(string $table, string|null $where = null, string $field_count = self::F_ID): int {
        $sql = "SELECT COUNT(`{$field_count}`) AS COUNT FROM {$table}" . ($where ? " WHERE {$where}" : "");
        return $this->get_count_by_sql($sql);

    }



    /**
     * Возвращает массив строк таблицы, выбранных по значению указанного поля.
     * Если указать только имя таблицы, то будет возвращена вся таблица.
     * @param  string $table -- Имя таблицы
     * @param  string $field_name -- имя поля для выборки
     * @param  string $field_value -- значение поля для выборки
     * @param  string $id_alias -- алиас для ИД-поля, если нужнно
     * @param string $order_by -- выражение в разделе ORDER BY
     * @param int|string|null $limit
     * @return array -- массив с найденными строками
     */
    function get_rows_by_field(string $table, string $field_name = "1", int|string $field_value = "1", string $id_alias="", string|null $order_by = null, int|string|null $limit = null): array {
        return $this->get_rows_by_where(table: $table, where: "$field_name=".$this->quote($field_value), id_alias: $id_alias, order_by: $order_by, limit: $limit);
    }



    /**
     * Возвращает служебную абонентскую запись,
     * используемую для регистрации не распределённых платежей
     * @return array
     */
    function get_abon_0(): array {
        $A = [
            "id" => 0,
            "user_id" => 0,
            "address" => "Запись для регистрации не распределённых платежей",
            "is_payer" => 0,
            "date_join" => 0,
            "comments" => ""
        ];
        return $A;
    }



    /**
     * Возвращает служебную абонентскую запись,
     * используемую для регистрации не распределённых платежей
     * @return array
     */
    function get_user_0(): array {
        $U = [
            User::F_ID         => 0,
            User::F_NAME_FULL  => "Не распределённые платежи",
            User::F_NAME_SHORT => "Не распределённые платежи",
        ];
        return $U;
    }



    /**
     * Кэш-массив для функции get_user_by_login()
     */
    private static array $CACHE_USER_BY_LOGIN = array();



    /**
     * Возвращает запись пользователя по логину
     * @param string $login
     * @return array
     * @throws \Exception
     */
    function get_user_by_login(string $login): array {

        if (!array_key_exists(key: $login, array: self::$CACHE_USER_BY_LOGIN)) {
            $rows = $this->get_rows_by_field(table: User::TABLE, field_name: User::F_LOGIN, field_value: $login);
            if (count($rows) < 1) {
                throw new \Exception("get_user_by_login[{$login}] -- нет такого пользователя");
            } elseif (count($rows) > 1) {
                throw new \Exception("get_user_by_login[{$login}] -- Пользователей более одного");
            } else {
                self::$CACHE_USER_BY_LOGIN[$login] = $rows[array_key_first($rows)];
            }
        }
        return self::$CACHE_USER_BY_LOGIN[$login];
    }



    /**
     * Возвращает запись-массив параметров Пользователя.
     * @param int $id
     * @return array
     * @throws \Exception
     */
    function get_user(int $id): array {
        if ($id === 0) {
            return $this->get_user_0();
        }
        if ($this->validate_id(User::TABLE, $id, User::F_ID)) {
            $user = $this->get_row_by_id(table_name: User::TABLE, id_value: $id, field_id: User::F_ID);
         // /* int    */ $user["id"]                     = $user["id"];
         // /* string */ $user["login"]                  = $user["login"];
         // /* string */ $user["password2"]              = $user["password2"];
         // /* string */ $user["password"]               = $user["password"];
         // /* string */ $user["salt"]                   = $user["salt"];
            /* string */ $user[User::F_NAME_SHORT]       = (string)$user[User::F_NAME_SHORT];
            /* string */ $user[User::F_NAME_FULL]        = (string)$user[User::F_NAME_FULL];
            /* string */ $user[User::F_SURNAME]          = (string)$user[User::F_SURNAME];
            /* string */ $user[User::F_FAMILY]           = (string)$user[User::F_FAMILY];
            /* string */ $user[User::F_PHONE_MAIN]       = (string)$user[User::F_PHONE_MAIN];
            /* int    */ $user[User::F_SMS_DO_SEND]      = (int)   $user[User::F_SMS_DO_SEND];
            /* string */ $user[User::F_EMAIL_MAIN]       = (string)$user[User::F_EMAIL_MAIN];
            /* int    */ $user[User::F_EMAIL_DO_SEND]    = (int)   $user[User::F_EMAIL_DO_SEND];
            /* string */ $user[User::F_ADDRESS_INVOICE]  = (string)$user[User::F_ADDRESS_INVOICE];
            /* int    */ $user[User::F_INVOICE_DO_SEND]  = (int)   $user[User::F_INVOICE_DO_SEND];
            /* string */ $user[User::F_JABBER]           = (string)$user[User::F_JABBER];
            /* int    */ $user[User::F_JABBER_DO_SEND]   = (int)   $user[User::F_JABBER_DO_SEND];
            /* string */ $user[User::F_VIBER]            = (string)$user[User::F_VIBER];
            /* int    */ $user[User::F_VIBER_DO_SEND]    = (int)   $user[User::F_VIBER_DO_SEND];
            /* string */ $user[User::F_TELEGRAM]         = (string)$user[User::F_TELEGRAM];
            /* int    */ $user[User::F_TELEGRAM_DO_SEND] = (int)   $user[User::F_TELEGRAM_DO_SEND];
            /* int    */ $user[User::F_CREATION_UID]     = (int)   $user[User::F_CREATION_UID];
            /* int    */ $user[User::F_CREATION_DATE]    = (int)   $user[User::F_CREATION_DATE];
            /* int    */ $user[User::F_MODIFIED_UID]     = (int)   $user[User::F_MODIFIED_UID];
            /* int    */ $user[User::F_MODIFIED_DATE]    = (int)   $user[User::F_MODIFIED_DATE];
            return $user;
        } else {
            throw new \Exception("get_user(int $id) -- нет такого пользователя");
        }
    }



    /**
     * Обновляет строку в таблице по условию $where, используя для заполнения ассоциативный массив.
     * @param string $table
     * @param array $row
     * @param string $where
     * @return bool
     */
    function update_row_by_where(string $table, array $row, string $where): bool {
        $update_items = array();
        foreach ($row as $key => $value) {
            switch ($value) {
                case 0:
                    $update_items[] = "`$key`=0";
                    break;
                case null:
                    $update_items[] = "`$key`=NULL";
                    break;
                case "":
                    $update_items[] = "`$key`=''";
                    break;
                default:
                    $update_items[] = "`$key`=" . $this->quote($value) . "";
            }
        }

        $sql = "UPDATE `{$table}` SET "
                . implode(", ", $update_items)
                . " WHERE {$where}";
        return $this->execute($sql);

    }



    /**
     * Обновление полей записи в таблице по ИД полю.
     * Обновляются поля `modified_date` и `modified_uid`.
     * @param string $table -- обновляемая таблица
     * @param array $row -- Ассоциативный массив с обновляемыми полями
     * @param string $field_id -- имя ID поля
     * @return bool
     */
    function update_row_by_id(string $table, array $row, string $field_id = self::F_ID): bool {
        if (!$this->validate_id(table_name: $table, field_id: $field_id, id_value: $row[$field_id])) {
            self::add_error_info("No Valid {$table}[{$row[$field_id]}]");
            return false;
        }
        $update_items = array();
        foreach ($row as $key => $value) {
            if  (   ($key == $field_id) ||
                    ($key == 'creation_date') || ($key == 'creation_uid') ||
                    ($key == 'modified_date') || ($key == 'modified_uid')
                )
            {
                continue;
            }

            if     ($value === 0   ) { $update_items[] = "`$key`=0";                            }
            elseif ($value === null) { $update_items[] = "`$key`=NULL";                         }
            elseif ($value === ""  ) { $update_items[] = "`$key`=''";                           }
            else                     { $update_items[] = "`$key`=" . $this->quote($value) . ""; }
        }
        $sql = "UPDATE `{$table}` SET "
                . implode(", ", $update_items)
                . ", "
                . "`modified_date`='" . time() . "', "
                . "`modified_uid`='" . (isset($_SESSION[User::SESSION_USER_REC]) ? $_SESSION[User::SESSION_USER_REC][User::F_ID] : User::UID_BILLING)."' "
                . "WHERE `{$field_id}`={$row[$field_id]}";
//        debug($sql, 'update_row_by_id: $sql', die: 1);
        return $this->execute($sql);
    }



    /**
     * Вставляет строку в таблицу.
     * Поля передаются в ассоциативном массиве.
     * Ключи должны соответсвовать именам полей.
     * Поле 'id' не обязательно, но если оно указано, то оно будет использовано при вставке.
     * @param string $table
     * @param array $row
     * @return int|false -- Успешность встваки строки
     */
    function insert_row(string $table, array $row): int|false {
        $fields = [];
        $values = [];
        foreach ($row as $key => $value) {
            $fields[] = $key;
            $values[] = (is_null($value) ? "NULL" : "" . $this->quote($value) . "");
        }
        $sql = "INSERT INTO `{$table}`(".implode(',', $fields).") VALUES (".implode(',', $values).")";
        $rez = $this->execute($sql);
        if ($rez) {
            return (int)$this->lastInsertId();
        } else {
            return false;
        }
    }


    function quote(string $string, int $type = \PDO::PARAM_STR): string|false {
        return $this->db->quote($string, $type);
    }



    /**
     * Возвращает ID модуля по строке маршрутизатора "контроллер/действие"
     * @param string $route_name -- строка маршрутизатора
     * @param string $field_id -- Имя ID-поля
     * @return int -- ID модуля
     * @throws \Exception -- если количество возвращённых строк более 1.
     */
    function get_module_id_by_route(string $route_name, string $field_id = self::F_ID): int {
        $rows = $this->get_rows_by_field(
                table: 'adm_module_list',
                field_name: "route",
                field_value: $route_name);

        if (count($rows) == 1) {
            return $rows[array_key_first($rows)][$field_id];
        } elseif (count($rows) == 0) {
            return self::NO_MODULE;
        } else {
            echo "<h3>Запрос вернул более 1 строки. Должна быть 1 строка или 0 строк.<h3>";
            throw new \Exception("get_module_id_by_route({$route_name}[{$field_id}]): SQL Error: " . \PDO::errorInfo . "<br>");
        }


    }



    function get_role_list_for_user(int $user_id): array {
        $sql = "SELECT `role_id` FROM `adm_ts_user_role` WHERE `user_id`=?; ";
        $params = [$user_id];
        return $this->get_rows_by_sql($sql, $params);
    }



    // function get_prava_user_module(int $user_id, int $module_id): int {
    //     $prava = ACCESS_NONE;
    //     $role_list = $this->get_role_list_for_user($user_id);
    //     debug("role_list: ", $role_list);
    // }


    /**
     * Возвращает md5-хэшированный пароль для сохранения в базе
     * @param string $pass
     * @param string $salt
     * @return string
     */
    static function get_md5_pass(string $pass, string $salt): string {
        return md5(md5($pass).$salt);
    }



    /**
     * Возвращает 60-байтный хэшированный пароль для сохранения в базе
     * вида "$2y$10$.vGA1O9wmRjrwAVXD98HNOgsNpDczlqm3Jq7KnEd1rVAGv3Fykk1a"
     * @param string $pass
     * @return string
     */
    static function get_hash_pass(string $pass): string {
        return password_hash($pass, PASSWORD_DEFAULT);
    }


    /**
     * Получить следующий ID для таблицы
     * @param string $table
     * @param string $field_id
     * @return int
     * @throws \Exception
     */
    function get_next_id(string $table = User::TABLE, string $field_id = User::F_ID): int {
        $sql = "SELECT MAX($field_id) AS max_id FROM `$table`";
        $row = $this->get_rows_by_sql($sql);
        if (!$row) {
            throw new \Exception("Ошибка выполнения запроса: $sql");
        }
        return $row[0]['max_id'] + 1;
    }



    function delete_rows_by_field(string $table, string $field_id, string $value_id): bool {
        $sql = "DELETE FROM `{$table}` WHERE `{$field_id}`={$value_id}";
        return $this->execute($sql);
    }



}