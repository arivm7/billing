<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Controller.php
 *  Path    : billing/core/base/Controller.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core\base;

use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\View;

/**
 * Description of Controller.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
abstract class Controller
{



    /**
     * Текущий маршрут и параметры (Controller, Action, Params)
     * @var array
     */
    public $route = [];



    /**
     * Путь/Имя файла Вида
     * @var string
     */
    public $view;



    /**
     * Имя файла используемого шаблона
     * @var string
     */
    public $layout;



    /**
     * Пользовательские переменные используемые в файле Вида
     * @var array
     */
    public $variables = [];



    public function __construct(array $route)
    {
        $this->route = $route;
        $this->view = $route[F_ACTION];
        Lang::load(App::$app->get_config(Lang::F_CURR), $this->route);
    }



    public function getView() {
        $viewObj = new View(route: $this->route, layout: $this->layout, view: $this->view);
        $viewObj->render($this->variables);
    }



    public function setVariables(array $variables) {
        $this->variables = $variables;
    }



}