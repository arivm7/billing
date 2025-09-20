<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : App.php
 *  Path    : billing/core/App.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

use app\models\AuthModel;
use app\widgets\LangSelector\LangSelector;
use app\widgets\Theme\ThemeSelector;
use config\tables\Perm;

/**
 * Description of App.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class App {

    public static Registry $app;
    public static AuthModel $auth;

    public function __construct() {
        session_start();
        self::$app = Registry::instance();
        self::$auth = new AuthModel();
        ThemeSelector::init();
        LangSelector::init();
        /**
         * Роли текущего пользователя записываются в рееср
         * @var array App::$app->permissions -- array[модуль] = разрешение
         */
        Perm::update_permissions();

    }



    /**
     * Обёртка для App::Registry->get_config()
     * @param string $name
     * @return mixed
     */
    static function get_config(string $name): mixed {
        return App::$app->get_config($name);
    }


}