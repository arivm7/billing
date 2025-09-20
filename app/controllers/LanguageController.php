<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : LanguageController.php
 *  Path    : app/controllers/LanguageController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use billing\core\App;
use billing\core\base\Lang;

/**
 * Description of LanguageController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class LanguageController extends AppBaseController {



    public function changeAction() {
        $lang = !empty($_GET[Lang::F_GET]) ? $_GET[Lang::F_GET] : null;
        if ($lang) {
            if (array_key_exists(key: $lang, array: App::$app->get_config(Lang::F_LIST))) {
                setcookie(name: Lang::F_COOK_NAME, value: $lang, expires_or_options: time() + App::$app->get_config(Lang::F_COOK_TIME), path: '/', domain: URL_DOMAIN);
            }
        }
        redirect();
    }



}