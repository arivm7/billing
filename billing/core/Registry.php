<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Registry.php
 *  Path    : billing/core/Registry.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

use Exception;


/**
 * Description of Registry.php
 *
 * @property \billing\core\Cache $cache
 * @property \billing\core\ErrorHandler $error_handler
 * @property array $permissions -- array([ID module] => permissions)
 * 
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Registry {

    use TSingletone;

    /**
     * Инициализируется из config.php
     * Далее значения могут меняться/добавляться
     * @var array $config
     */
    protected static $config  = [];

    /**
     * Массив для сохранеия объектов в виеде записи
     * [
     *    'Type' => Type,
     *    'Value' => Value
     * ]
     * @var array $objects
     */
    protected static $objects = [];


    /**
     * Имена полей для сохзранения объектов
     */
    const F_TYPE = 'type';
    const F_VALUE = 'value';

    /**
     * Значений типов сохраняемых объектов
     */
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY  = 'array';
    const TYPE_SCALAR = 'scalar';  // скаляр (int, float, string, bool и т.п.)



    /**
     * Имя массива в сессии для сохранения значений конфигов для конкретных пользователей
     * @var string
     */
    const F_SESSION = 'reg_config';
    const F_SESSION_TIME = 60 * 60 * 24 * 31; // месяц


    protected function __construct() {
        self::$config = require DIR_CONFIG . '/config.php';
        foreach (self::$config['autoload'] as $name => $component) {
            $this->__set($name, $component);
        }
    }



    public function set_config(string $name, mixed $value, bool $to_session = false) {
        self::$config[$name] = $value;
        if ($to_session) {
            $_SESSION[self::F_SESSION][$name] = $value;
        }

    }



    /**
     * Возвращает значение переменной конфига.
     * Значение переменной сперва ищет 
     * в php-сессии авторизованного пользователя, если там нет, то 
     * в куках сайта в этом браузере, если там нет, то 
     * возвращает значение прямо из конфига.
     * @param string $name
     * @throws \Exception
     * @return mixed
     */
    public function get_config(string $name): mixed {
        if (isset(self::$config[$name])) {
            return 
                (isset($_SESSION[self::F_SESSION][$name]) 
                    ?   $_SESSION[self::F_SESSION][$name]
                    :   self::$config[$name]
                );
        }
        throw new Exception("Не верный config-ключ [{$name}] ");
//        return null;
    }



    public static function get_properties(): array {
        return [
            'config'  => self::$config,
            'objects' => self::$objects
        ];
    }



    /**
     * Возврашает сохранённый объект
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed {
        return self::$objects[$name][self::F_VALUE] ?? null;
    }



    /**
     * Сохраняет объект и его тип в Реестре
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void {
        if (!isset(self::$objects[$name])) {
            if (is_string($value) && class_exists($value)) {
                // передали имя класса → создаём объект
                self::$objects[$name] = [
                    self::F_TYPE  => self::TYPE_OBJECT,
                    self::F_VALUE => new $value,
                ];
            } elseif (is_object($value)) {
                // передали уже готовый объект
                self::$objects[$name] = [
                    self::F_TYPE  => self::TYPE_OBJECT,
                    self::F_VALUE => $value,
                ];
            } elseif (is_array($value)) {
                self::$objects[$name] = [
                    self::F_TYPE  => self::TYPE_ARRAY,
                    self::F_VALUE => $value,
                ];
            } else {
                // скаляр (int, float, string, bool и т.п.)
                self::$objects[$name] = [
                    self::F_TYPE  => self::TYPE_SCALAR,
                    self::F_VALUE => $value,
                ];
            }
        }
    }



    /**
     * Возвращает тип сохранённого объекта
     * @param string $name
     * @return string|null
     */
    public function get_type(string $name): ?string {
        return self::$objects[$name][self::F_TYPE] ?? null;
    }


    public static function printList() {
        echo "objects:<pre>";
//        var_dump(self::$config);
        print_r(self::get_properties());
        echo "</pre>";
    }



}