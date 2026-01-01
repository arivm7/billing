<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Db.php
 *  Path    : billing/core/Db.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

/**
 * Description of Db.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Db {

    use TSingletone;

    protected \PDO $pdo;

    public    static int   $countSql   = 0;
    public    static array $queriesSql = [];



    protected function __construct() {
        $db = require DIR_CONFIG . '/config_db.php';
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];
        $this->pdo = new \PDO($db['dsn'], $db['user'], $db['pass'], $options);
    }



    public function execute(string $sql, ?array $params = []): bool {
        self::$countSql++;
        self::$queriesSql[] = $sql;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }



    /**
     * Выполняет PDO-запрос.
     * Ведёт статистику и лог.
     * Возвращает результат в одном из трёх форматов (полный список, вектор, одно значение).
     * Дает безопасный универсальный интерфейс к базе
     * @param string $sql
     * @param mixed $params
     * @param int|null $fetchCell -- Индекс поля, котрое нужно вернуть. Возврат одного значения (scalar), строка или число
     * @param int|null $fetchVector -- Возврат одномерного массива из одной колонки. Вы получаете массив вида: [ value1, value2, value3, ... ]
     * @return array|int|string|false
     * return false — произошла ошибка выполнения запроса. Именно ошибка, а не возврат пустого массива.
     */
    public function query(string $sql, ?array $params = [], int|null $fetchCell = null, int|null $fetchVector = null): array|int|string|false {
        self::$countSql++;
        $stmt = $this->pdo->prepare($sql);
        self::$queriesSql[] = ['sql' => $sql, 'params' => $params];
        $res = $stmt->execute($params);
        if ($res !== false) {
            if (is_null($fetchCell)) {
                if (is_null($fetchVector)) {
                    /**
                     * Возвращаем полный массив строк:
                     * каждая строка — ассоциативный и индексный массив (режим по умолчанию).
                     */
                    return $stmt->fetchAll();
                } else {
                    /**
                     * Возврат одномерного массива из одной колонки
                     * return $stmt->fetchAll(\PDO::FETCH_COLUMN, (int)$fetchVector);
                     * Вы получаете массив вида: [ value1, value2, value3, ... ]
                     */
                    return $stmt->fetchAll(\PDO::FETCH_COLUMN, (int)$fetchVector);
                }
            } else {
                /**
                 * Возврат одного значения (scalar), аналог fetchColumn()
                 * Используется для запросов, где требуется одно значение: 
                 * $count = query("SELECT COUNT(*) FROM table", [], fetchCell: 0);
                 * Результат — строка или число.
                 */
                return $stmt->fetchColumn($fetchCell);
            }
        }
        return false;
    }



    function quote(string $string, int $type = \PDO::PARAM_STR): string|false {
        return $this->pdo->quote($string, $type);
    }



    public function lastInsertId(?string $name = null): string|false {
        return $this->pdo->lastInsertId($name);
    }



    public function errorInfo(): array {
        return $this->pdo->errorInfo();
    }



    public function errorCode(): ?string {
        return $this->pdo->errorCode();
    }



}