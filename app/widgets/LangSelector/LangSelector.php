<?php


namespace app\widgets\LangSelector;

use billing\core\App;
use billing\core\base\Lang;

class LangSelector {

    protected string $template;
    public array  $list;
    public array  $curr;



    /**
     * Переопределяет глобальные языковые значения
     * для использования компонетами
     * Должна вызываться ДО отображения самого виджета
     */
    public static function init() {
        App::$app->set_config(Lang::F_LIST, LangSelector::get_list());
        App::$app->set_config(Lang::F_CURR, LangSelector::get_curr());
    }



    public function __construct() {
        $this->template = DIR_WIDGETS . '/LangSelector/templates/def_template.php';
        $this->run();
    }



    protected function run(): void {
        $this->list = App::$app->get_config(Lang::F_LIST);
        $this->curr = App::$app->get_config(Lang::F_CURR);
        echo $this->get_html();
    }



    public static function get_list(): array {
        $list = App::$app->get_config(Lang::F_LIST);
        sort_assoc_by_field(array: $list, field: Lang::F_ORDER);
        return $list;
    }



    public static function get_curr(): array {
        $list = App::$app->get_config(Lang::F_LIST);
        if (isset($_COOKIE[Lang::F_COOK_NAME]) && array_key_exists($_COOKIE[Lang::F_COOK_NAME], $list)) {
            $key = $_COOKIE[Lang::F_COOK_NAME];
        } else {
            $key = key($list);
        }
        $curr = $list[$key];
        $curr[Lang::F_CODE] = $key;
        return $curr;
    }



    protected function get_html() {
        ob_start();
        require_once $this->template;
        return ob_get_clean();
    }



}
