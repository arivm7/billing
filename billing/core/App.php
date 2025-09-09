<?php



namespace billing\core;

use app\models\AuthModel;
use app\widgets\LangSelector\LangSelector;
use app\widgets\Theme\ThemeSelector;
use config\tables\Perm;

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

}
