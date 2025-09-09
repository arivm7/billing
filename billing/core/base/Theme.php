<?php



namespace billing\core\base;



use billing\core\App;

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
