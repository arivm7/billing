<?php

namespace billing\core;


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



    public function query(string $sql, ?array $params = [], int|null $fetchCell = null, int|null $fetchVector = null): array|int|string {
        self::$countSql++;
        $stmt = $this->pdo->prepare($sql);
        self::$queriesSql[] = ['sql' => $sql, 'params' => $params];
        $res = $stmt->execute($params);
        if ($res !== false) {
            if (is_null($fetchCell)) {
                if (is_null($fetchVector)) {
                    return $stmt->fetchAll();
                } else {
                    return $stmt->fetchAll(\PDO::FETCH_COLUMN, (int)$fetchVector);
                }
            } else {
                return $stmt->fetchColumn($fetchCell);
            }
        }
        return [];
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
