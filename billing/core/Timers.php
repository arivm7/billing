<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Timers.php
 *  Path    : billing/core/Timers.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

/**
 * Description of Timers.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Timers
{

    public static $start = 0;
    public static $model = 0;
    public static $view = 0;
    public static $end = 0;

    /**
     * Фиксация времени начала скрипта
     * @param float $value
     */
    public static function setTimeStart(float $value = 0): void {
        self::$start = ($value ? $value : \microtime(true));
    }

    /**
     * Фиксация времени завершения обработки данных
     * @param float $value
     */
    public static function setTimeModel(float $value = 0): void {
        self::$model = ($value ? $value : \microtime(true));
    }

    /**
     * Фиксация времени завершения рендера данных,
     * и сохранения в переменную $content
     * @param float $value
     */
    public static function setTimeView(float $value = 0): void {
        self::$view = ($value ? $value : \microtime(true));
    }

    /**
     * Фиксация времени завершения работы скрипта,
     * время завершения отрисовки компоновки страницы (Layout).
     * @param float $value
     */
    public static function setTimeEnd(float $value = 0): void {
        self::$end = ($value ? $value : \microtime(true));
    }

    /**
     * Время подготовки данных
     */
    public static function getTimePrepareData(): float {
        return self::$model - self::$start;
    }

    /**
     * Время отрисовки данных
     */
    public static function getTimeRender(): float {
        return self::$view - self::$model;
    }

    /**
     * Время отрисовки страницы
     */
    public static function getTimeLayout(): float {
        return self::$end - self::$view;
    }

    /**
     * Полное время выполнения скрипта
     */
    public static function getTimeAll(): float {
        return self::$end - self::$start;
    }

}
