<?php



namespace app\controllers;



use billing\core\App;
use billing\core\base\Lang;

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
