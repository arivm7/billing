<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Cache.php
 *  Path    : billing/core/Cache.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

/**
 * Время кэширования страницы в секундах
 */
const TIME_CACHE = 5; /* seconds */



/**
 * Description of Cache.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Cache {
    public function __construct() {
    }


    /**
     * Возвращает имя файла для чтеня/записи кэша
     * @param string $key
     * @return string
     */
    private static function get_file_name(string $key): string {
        return DIR_CACHE . '/' . md5($key) . '.txt';
    }



    /**
     * Записывает данные в кэш на диске
     * @param string $key -- имя
     * @param mixed $data -- кэшируемые данные
     * @param int $seconds -- время актуальности кэша
     * @return true | false
     */
    public function set(string $key, mixed $data, int $seconds = TIME_CACHE): bool {

        $file_name = $this->get_file_name($key);
        $content['data'] = $data;
        $content['end_time'] = time() + $seconds;

        if (file_put_contents($file_name, serialize($content))) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * Возвращает кэшированные данные или, если их нет, то false
     * @param string $key
     * @return mixed | false
     */
    public function get(string $key): mixed {

        $file_name = $this->get_file_name($key);
        if (file_exists($file_name)) {
            $content = unserialize(file_get_contents($file_name));
            if (time() <= $content['end_time']) {
                return $content['data'];
            } else {
                unlink($file_name);
            }
        }
        return false;
    }



    /**
     * Удаляет файл кэша с диска
     * @param string $key
     * @return void
     */
    public function delete(string $key): void {
        $file_name = $this->get_file_name($key);
        if (file_exists($file_name)) {
            unlink($file_name);
        }
    }

}