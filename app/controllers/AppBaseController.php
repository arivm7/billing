<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AppBaseController.php
 *  Path    : app/controllers/AppBaseController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\widgets\LangSelector\LangSelector;
use billing\core\App;
use billing\core\base\Controller;
use config\tables\User;



/**
 * Базовый контроллер для приложения
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AppBaseController extends Controller{



    // #[Override]
    function __construct(array $route) {
        parent::__construct($route);

    }
    

    
    public static function log_unauthorize() {
        self::log(msg: 'ERROR   | -unauth- | ' . get_full_request_url(), log_filename: App::get_config('auth_log_file'));
    }


    
    public static function log_no_rights() {
        self::log(msg: 'ERROR  | no rights | ' . get_full_request_url(), log_filename: App::get_config('rights_log_file'));
    }


    
}