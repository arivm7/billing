<?php
/*
 *  Project : my.ri.net.ua
 *  File    : ThemeController.php
 *  Path    : app/controllers/ThemeController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use billing\core\App;
use billing\core\base\Theme;

/**
 * Description of ThemeController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class ThemeController extends AppBaseController {



    public function changeAction() {
        $id = !empty($_GET[Theme::F_GET]) ? $_GET[Theme::F_GET] : null;
        if ($id) {
            if (array_key_exists(key: $id, array: App::$app->get_config(Theme::F_LIST))) {
                setcookie(name: Theme::F_COOK_NAME, value: $id, expires_or_options: time() + App::$app->get_config(Theme::F_COOK_TIME), path: '/', domain: URL_DOMAIN);
            }
        }
        redirect();
    }



}