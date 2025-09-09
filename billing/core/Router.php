<?php


namespace billing\core;

use billing\core\Timers;



class Router {

    protected static $routes = [];
    protected static $route = [];



    public static function add(string $urlPath, array $route = []) {
        self::$routes[$urlPath] = $route;
    }



    public static function getRoutes(): array {
        return self::$routes;
    }



    public static function getRoute(): array {
        return self::$route;
    }



    protected static function matchRoute(string $urlPath): bool {
        $matches = [];
        foreach (self::$routes as $pattern => $route) {
            if (preg_match(pattern: "#$pattern#i", subject: $urlPath, matches: $matches)) {
                foreach ($matches as $k => $v) {
                    if (is_string($k)) {
                        $route[$k] = $v;
                    }
                }

                /**
                 * Привоит имя контроллера к виду "ИмяКласса"
                 */
                $route[F_CONTROLLER] = self::upperCamelCase($route[F_CONTROLLER]);

                /**
                 * Если действие не указано, то дествием является index
                 */
                if (!isset($route[F_ACTION])) {
                    $route[F_ACTION] = ACT_INDEX;
                }

                // prefix for admin controllers
                if (!isset($route[F_PREFIX])) {
                    $route[F_PREFIX] = '';     // если поля нет, то создать его
                }

                self::$route = $route;
                return true;
            }
        }
        return false;
    }



    /**
     * Перенаправляет URL по корректному маршруту
     * @param string $urlPath -- входящий URL
     * @return void
     */
    public static function dispatch(string $urlPath) {

        $urlPath = self::removeQueryString($urlPath);

        if (self::matchRoute($urlPath)) {

            /**
             * Полный путь к классу контроллера,
             * не к файлу, а именно к классу: "пространство_имён\ИмяКласса"
             */
            $controllerPathClass = CONTROLLERS_NAMESPACE . (self::$route[F_PREFIX] ? self::$route[F_PREFIX]."\\" : "") . self::$route[F_CONTROLLER] . CONTROLLER_SUFFIX;
            if (class_exists($controllerPathClass)) {
                $controllerObj = new $controllerPathClass(self::$route);
                $action = self::lowerCamelCase(self::$route[F_ACTION]) . ACTION_SUFFIX;

                /**
                 * проброс дополнительных параметров в $_GET
                 */
                foreach (self::$route as $key => $value) {
                    if  (
                            // чтобы не затирать служебные поля
                            !in_array($key, [F_PREFIX, F_CONTROLLER, F_ACTION, F_ALIAS]) &&
                            !isset($_GET[$key])
                        )
                    {
                        $_GET[$key] = $value;
                    }
                }

                if (method_exists(object_or_class: $controllerObj, method: $action)) {
                    $controllerObj->$action();

                    /**
                     * Фиксирование времени получения данных
                     */
                    Timers::setTimeModel();

                    $controllerObj->getView();
                } else {
                    throw new \Exception("Действие [<b>$controllerPathClass::$action</b>] отсутствует", 404);
                }
            } else {
                throw new \Exception("Контроллер [<b>$controllerPathClass</b>] не найден.", 404);
            }
        } else {
            throw new \Exception("[{$urlPath}]Страница не найдена.", 404);
        }
    }



    /**
     * Провращает строку вида "имя-класса" в "ИмяКласса"
     * @param string $nameClass
     * @return string
     */
    protected static function upperCamelCase(string $nameClass): string {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $nameClass)));
    }



    /**
     * Провращает строку вида "имя-класса" в "имяКласса"
     * @param string $nameClass
     * @return string
     */
    protected static function lowerCamelCase(string $nameClass): string {
        return lcfirst(self::upperCamelCase($nameClass));
    }



    /**
     * Из URL-строки возвращает толко фрагмент вида 'контроллер/действие'
     * @param type $urlPath
     * @return string
     */
    protected static function removeQueryString($urlPath): string {
        if ($urlPath) {
            $params = explode(separator: '&', string: $urlPath, limit: 2);
            if (false === strpos($params[0], '=')) {
                return rtrim($params[0], '/');
            } else {
                return '';
            }
        }
        return '';
    }

}
