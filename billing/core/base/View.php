<?php

namespace billing\core\base;

use billing\core\App;
use billing\core\ErrorHandler;
use billing\core\Timers;


/**
 * Description of View
 *
 * @author ar
 */
class View {


    public const META_TITLE = "title";
    const META_DESCR = "descr";
    const META_KEYWORDS = "keywords";


    public static $meta = [
        self::META_TITLE => "",
        self::META_DESCR => "",
        self::META_KEYWORDS => ""
    ];



    /**
     * Массив скриптов из контента.
     * Сохраняется сюда функцией extractScripts()
     * Должны выводиться в виде или в компоновке как-то так:
     * <?php foreach ($scripts as $script) { echo $script; } ?>
     * @var type
     */
    public $scripts;


    /**
     * Текущий маршрут м параметры (Controller, Action, Params)
     * @var array
     */
    public $route = [];



    /**
     * Текущий вид
     * @var string
     */
    public $view;



    /**
     * Текущий шаблон
     * @var string
     */
    public $layout;



    public function __construct($route, $layout = '', $view = '') {
        $this->route = $route;

        if ($layout === false) {
            $this->layout = false;
        } else {
            $this->layout = $layout ?: LAYOUT_DEFAULT;
        }

        $this->view = $view;
    }


    /**
     * Рисует страницу
     * Сперва рисует в буфер Вид
     *      extract($variables);
     *      ob_start();
     *      require $viewFile; (используются переменные из $variables)
     *      $content = ob_get_clean();
     * За тем рисует Компоновку
     *      require $layoutFile; (используется переменнае $content)
     * @param array $variables
     */
    public function render(array $variables) {
        /**
         * Загрузка языковых словарей
         */
        Lang::load(App::$app->get_config(Lang::F_CURR), $this->route);

        /**
         * extract($variables):
         * @var string $title
         * @var array $posts
         * @var array $meta
         */
        if (is_array($variables)) {
            extract($variables);
        }


        $view_file = DIR_VIEWS . ($this->route[F_PREFIX] ? "/{$this->route[F_PREFIX]}" : "") . "/{$this->route[F_CONTROLLER]}/{$this->view}" . VIEW_SUFFIX . ".php";

        ob_start();

        if (is_file($view_file)) {
            require $view_file;
        } else {
            throw new \Exception("Вид [<b>{$view_file}</b>] не найден.", 404);
        }

        $content = ob_get_clean();

        /**
         * Фиксирование времени вывода данных на странице
         */
        Timers::setTimeView();

        if (false !== $this->layout) {
            $layout_file = DIR_LAYOUTS . "/{$this->layout}".LAYOUT_SUFFIX.".php";
            if (is_file($layout_file)) {
                $content = $this->extractScripts($content);
                $scripts = [];
                if (!empty($this->scripts[0])) {
                    $scripts = $this->scripts;
                }
                require $layout_file;
            } else {
                throw new \Exception("Шаблон компоновки (layout) [<b>{$layout_file}</b>] не найден ", 404);
            }
        }
    }



    /**
     * Извлекает все теги <script>...</script> из HTML-контента и возвращает "очищенный" от них текст.
     * Сами скрипты при этом сохраняются в свойство $this->scripts.
     * @param type $content
     * @return type
     */
    public function extractScripts($content) {
        /**
         * Шаблон регулярного выражения:
         * #<script.*?>.*?</script># — ищет любой тег <script>...</script>
         * s — "dot matches newline" (. захватывает также \n)
         * i — нечувствительность к регистру (<SCRIPT> тоже сработает)
         * .*? — «ленивое» совпадение, чтобы захватить минимально возможный текст до </script>.
         */
        $pattern = "#<script.*?>.*?</script>#si";
        /**
         * Находит все совпадения и сохраняет их в свойство $this->scripts в виде массива.
         */
        preg_match_all($pattern, $content, $this->scripts);
        if (!empty($this->scripts)) {
            /**
             * Если найдены какие-либо <script>-теги, то они удаляются из исходного контента с помощью preg_replace.
             */
            $content = preg_replace($pattern, '', $content);
        }
        /**
         * Возвращается HTML очищенный от <script> тегов.
         */
        return $content;
    }



    public static function setMeta(string $title = '', string $descr = '', string $keywords = ''): void {
        self::$meta[self::META_TITLE] = $title;
        self::$meta[self::META_DESCR] = $descr;
        self::$meta[self::META_KEYWORDS] = $keywords;
    }



    public static function getMeta(): string {
        return    "<title>" . self::$meta[self::META_TITLE] . "</title>"
                . "<meta name=description content='" . self::$meta[self::META_DESCR] . "'>"
                . "<meta name=keywords content='" . self::$meta[self::META_KEYWORDS] . "'>";
    }



    public static function printMeta(): void {
        echo self::getMeta();
    }



    /**
     * Выводит в браузер указанный файл
     * или выводит ошибку, если файл не найден.
     * @param string $file
     * @return void
     * @throws \Exception
     */
    public function includePart(string $file): void {
        $file = DIR_VIEWS . "/{$file}"; // inc/file.php
        if (is_file($file)) {
            require $file;
        } else {
            $msg = "Файл вставки [<b>{$file}</b>] не найден.";
            if (ErrorHandler::DEBUG) {
                throw new \Exception($msg, 404);
            } else {
                echo "Ошибка: {$msg}";
            }
        }
    }



}
