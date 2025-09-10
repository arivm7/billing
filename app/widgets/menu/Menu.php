<?php

namespace app\widgets\menu;
use app\models\MenuModel;
use config\tables\Menu as M;
use billing\core\App;


class Menu {

    protected string   $db_table        = M::TABLE;

    protected string   $template        = __DIR__ . '/templates/menu_template_ul_li.php';
    protected string   $container       = 'ul';             // ul | select | div
    protected string   $container_attr  = "class='my-accordion-menu'";
    protected string   $cache_key       = 'default-menu';   // имя ключа для кэширования
    protected int      $cache_time      = 10;               // Время кэширования в секундах. 0 -- не кэшировать

    protected MenuModel $db;
    protected array    $data;
    protected array    $tree;
    protected string   $menuHtml;


    /**
     * Создаёт объект,
     * устанавливает параметрв,
     * рендерит html-сроку
     * и сразу выводит.
     * @param array $options -- массив с новыми значениями полей класса: [ 'tpl', 'container', 'db_table', 'time_cache', ...]
     */
    public function __construct(array $options = []) {
        $this->db = new MenuModel(db_table: $this->db_table);
        $this->setOptions($options);
        $this->render();
        $this->output();
    }


    function update_access(array $tree): array {
        $output = [];
        foreach ($tree as $key => $item) {
            if (!$item[M::F_VISIBLE]) { continue; }
            if (!$item[M::F_ANON_VISIBLE] && !App::$auth->isAuth ) { continue; }
            if (!$item[M::F_ANON_VISIBLE] && !can_use($item[M::F_MODULE_ID])) { continue; }
            $output[] = $item;
        }
        return $output;
    }

    protected function render(): void {
        if ($this->cache_time > 0) {
            $cache = App::$app->cache->get($this->cache_key);
            if ($cache !== false) {
                $this->menuHtml = $cache;
                return; // кеш найден — рендерить не нужно
            }
        }


        // Кеш отключён или не найден — строим заново
        $this->data = $this->db->get_menu_raw(table_name: $this->db_table);
        $this->tree = $this->db->get_menu_tree(data_raw: $this->data);
        $this->tree = $this->db->get_menu_tree(data_raw: $this->data);
        $this->tree = $this->update_access($this->tree);
        $this->menuHtml = $this->get_html($this->tree);

        // Если кеш включён — сохранить результат
        if ($this->cache_time > 0) {
            App::$app->cache->set(key: $this->cache_key, data: $this->menuHtml, seconds: $this->cache_time);
        }
    }



    protected function output(): void {
        echo "<{$this->container} {$this->container_attr}>";
            echo $this->menuHtml;
        echo "</{$this->container}>";

    }



    /**
     * поля класса для замены значения:
     * [ 'tpl', 'container', 'db_table', 'time_cache', ...]
     * @param array $options
     */
    function setOptions(array $options): void {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                throw new \Exception("Поля [$key] в этом классе нет.", 501);
            }
        }
    }



    protected function get_html(array $subTree, string $tab = ''): string {
        $str = '';
        $lang = App::$app->get_config('lang_curr')['code'];
        foreach ($subTree as $id => $item) {
            $item[M::_TITLE] = $item["{$lang}" . M::_TITLE];
            $item[M::_DESCR] = $item["{$lang}" . M::_DESCR];
            $str .= $this->item_to_template($item, $tab, $id);
        }
        return $str;
    }



    protected function item_to_template(array $item, string $tab, int $id): string {
        ob_start();
        require $this->template;
        return ob_get_clean();
    }

}
