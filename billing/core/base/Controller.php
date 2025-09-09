<?php

namespace billing\core\base;

use billing\core\App;
use billing\core\base\Lang;



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
        $viewObj = new \billing\core\base\View(route: $this->route, layout: $this->layout, view: $this->view);
        $viewObj->render($this->variables);
    }



    public function setVariables(array $variables) {
        $this->variables = $variables;
    }



}
