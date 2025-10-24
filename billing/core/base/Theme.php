<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Theme.php
 *  Path    : billing/core/base/Theme.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core\base;

use billing\core\App;

/**
 * Description of Theme.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Theme {

    public const F_GET      = 'theme';
    public const F_ID       = 'id';
    public const F_ID_LIGHT = 'light';  // фактические значения схем
    public const F_ID_DARK  = 'dark';   // фактические значения схем
    public const F_CODE     = 'code';
    public const F_LIST     = 'theme_list';
    public const F_CURR     = 'theme_curr';
    public const F_TITLE    = 'title';
    public const F_ORDER    = 'order';
    public const F_COOK_NAME = 'theme';
    public const F_COOK_TIME = 'theme_timeout';
    public const LOG_FILE   = 'theme.log';


    public static $theme_data = [];
    public static $theme_layout = [];
    public static $theme_view = [];



    public static function get(): string {
        return App::$app->get_config(self::F_CURR)[self::F_CODE];
    }

    public static function id(): string {
        return App::$app->get_config(self::F_CURR)[self::F_ID];
    }




}