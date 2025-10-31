<?php
/**
 *  Project : my.ri.net.ua
 *  File    : TestController.php
 *  Path    : app/controllers/TestController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Oct 2025 20:30:19
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of TestController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\controllers\AppBaseController;
use billing\core\App;
use config\tables\Module;

require DIR_LIBS ."/tests.php";

class TestController extends AppBaseController {


  
    public function indexAction() {

        if  (
                App::isAuth() &&
                can_use(Module::MOD_WEB_DEBUG)
            )
        {
            /**
             * ЗАПУСК ТЕСТОВ
             */


            echo "TEST highlight_like_groups: <br>";
            echo h(highlight_like_groups("Отключились потому что через этот интернет не работала касса... хз...", "что%то")) . "<br>";
            echo h(highlight_like_groups("Платит нерегулярно и не верит,что что-то должен", "что%то")) . "<br>";

            die();

            translit_uk_test(0);
            echo "<hr>";
            translit_ru_test(0);
            echo "<hr>";
            detect_language_test(0);
            echo "<hr>";
            translit_test(0);
        }

        die();
    }





}