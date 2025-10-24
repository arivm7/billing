<?php
/*
 *  Project : my.ri.net.ua
 *  File    : ThemeSelector.php
 *  Path    : app/widgets/Theme/ThemeSelector.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\widgets\Theme;

use billing\core\App;
use billing\core\base\Theme;

/**
 * Description of ThemeSelector.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class ThemeSelector {



    protected string $template;
    public array  $list;
    public array  $curr;



    /**
     * Переопределяет глобальные языковые значения
     * для использования компонетами словарей
     * Должна вызываться ДО отображения самого виджета
     */
    public static function init() {
        App::$app->set_config(Theme::F_LIST, ThemeSelector::get_list());
        App::$app->set_config(Theme::F_CURR, ThemeSelector::get_curr());
    }



    public function __construct() {
        $this->template = DIR_WIDGETS . '/Theme/templates/def_template.php';
        $this->run();
    }



    protected function run(): void {
        $this->list = App::$app->get_config(Theme::F_LIST);
        $this->curr = App::$app->get_config(Theme::F_CURR);
        echo $this->get_html();
    }



    public static function get_list(): array {
        $list = App::$app->get_config(Theme::F_LIST);
        sort_assoc_by_field(array: $list, field: Theme::F_ORDER);
        return $list;
    }



    public static function get_curr(): array {
        $list = App::$app->get_config(Theme::F_LIST);
        if (isset($_COOKIE[Theme::F_COOK_NAME]) && array_key_exists($_COOKIE[Theme::F_COOK_NAME], $list)) {
            $id = $_COOKIE[Theme::F_COOK_NAME];
        } else {
            $id = key($list);
        }
        $curr = $list[$id];
        $curr[Theme::F_ID] = $id;
        return $curr;
    }



    protected function get_html(): string {
        ob_start();
        require_once $this->template;
        return ob_get_clean();
    }









}