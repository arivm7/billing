<?php



namespace app\controllers;



use billing\core\App;
use billing\core\base\Theme;

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
