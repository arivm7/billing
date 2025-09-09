<?php


namespace billing\core;
use app\models\AppBaseModel;


class Pagination {

    public int $current_page;
    public int $row_per_page;
    public int $count_rows;
    public int $count_pages;
    public string $uri;
    public string|null $sql;
    public $f_get_page; // 'page'



    /**
     *
     * @param int $page_num         -- номер текущей отображаемой страницы
     * @param int $per_page         -- количество строк на 1 страницу
     * @param int $count_rows       -- Прямое указание полного количества строк даннх
     * @param string|null $sql      -- SQL-запрос для выборки данных. Из него считается количество записей и потом подставляются лимиты для выборки реальных данных.
     * @param string $f_get_page    -- имя _GET поля &page=1
     */
    public function __construct(
            int $page_num = 0,
            int $per_page = 10,
            int $count_rows = 0,
            string|null $sql = null,
            string $f_get_page = 'page')
    {
        $this->row_per_page = $per_page;
        $this->f_get_page = $f_get_page;
        if ($sql) {
            $model = new AppBaseModel();
            $this->count_rows = $model->get_count_by_sql($sql);
        } else {
            $this->count_rows = $count_rows;
        }
        $this->count_pages = $this->getCountPages();
        if ($page_num) {
            $this->current_page = $this->get_current_page($page_num);
        } else {
            $this->current_page = $this->get_current_page((isset($_GET[$f_get_page]) ? (int)$_GET[$f_get_page] : 1));
        }

        $this->sql = $sql;
        $this->uri = $this->getParams();

    }

    public function __toString(): string {
        return $this->getHtml();
    }


    /**
     * Возвращает строки из SQL-запроса с параметрами LIMIT для текущей тсраницы
     * @return array
     */
    function get_rows(): array {
        $model = new AppBaseModel();
        $limit_start = $this->get_sql_limit_start();
        $sql = $this->sql . " LIMIT {$limit_start},{$this->row_per_page}";
        return $model->get_rows_by_sql($sql);
    }


    /**
     * Возвращает количество страниц
     * @return int
     */
    public function getCountPages(): int {
        return ceil($this->count_rows / $this->row_per_page) ?: 1;
    }

    /**
     * Проверяет границы $pageNum и возвращает значение в правильных границах
     * @param int $pageNum
     * @return int
     */
    public function get_current_page(int $pageNum): int {
        if (!$pageNum || $pageNum < 1)      { $pageNum = 1; }
        if ($pageNum > $this->count_pages)   { $pageNum = $this->count_pages; }
        return $pageNum;
    }


    /**
     * Возвращает число, используемое в запросе 'LIMIT <старт>,<количество>'
     * @return int
     */
    public function get_sql_limit_start(): int {
        return ($this->current_page - 1) * $this->row_per_page;
    }



    /**
     * Формирует URL текущей страницы без параметра page, чтобы можно было использовать его,
     * например, для генерации ссылок пагинации (?page=2, ?page=3 и т.д.), не дублируя параметр page.
     * @return type
     */
    public function getParams() {
        /**
         * Делит текущий URL по символу "?" :
         * $url[0] — путь (например, "/admin/users")
         * $url[1] — строка запроса (например, "sort=name&page=3")
         */
        $url = explode('?', $_SERVER['REQUEST_URI']);
        /**
         * — Начинаем собирать новый URL с тем же путём и открываем "?"
         */
        $uri = $url[0] . '?';
        /**
         * Если есть параметры, разбиваем их по "&"
         */
        if (isset($url[1]) && $url[1] != '') {
            $params = explode('&', $url[1]);
            /**
             * Проходимся по параметрам и отбрасываем параметр "page",
             * а остальные добавляем в URL, экранируя "&" как "&amp;" (HTML-специфично).
             * Важно:
             * &amp; — это HTML-сущность для "&". Она нужна, если результат вставляется в HTML (например, в <a href="...">).
             * Если ты используешь URL в заголовках или редиректах, там нужно использовать обычный "&".
             */
            foreach ($params as $value) {
                if (!preg_match("#{$this->f_get_page}=#", $value)) { $uri .= "{$value}&amp;"; }
            }
        }
        return $uri;
    }



    /**
     * Возвращает html-строку
     * @return string
     */
    public function getHtml(): string {
        $back = null;       // Ссылка "Назад"
        $forwsrd = null;    // Ссылка "Вперёд"
        $startPage = null;  // Ссылка "В начало"
        $endPage = null;    // Ссылка "В конец"
        $page2left = null;  // Вторая страница слева
        $page1left = null;  // Первая страница слева
        $page2right = null; // Вторая страница справа
        $page1right = null; // Первая страница справа

        if ($this->current_page > 1) {
            $back = "<li class='page-item' title='На предыдущую страницу'>"
                    . "<a class='page-link' href='{$this->uri}page=" . ($this->current_page - 1) . "'>&#11178;</a></li>"; // ⮪ &#11178;
        } else {
            $back = "<li class='page-item disabled' title='На предыдущую страницу'>"
                    . "<a class='page-link'>&#11178;</a></li>"; // ⮪ &#11178;
        }

        if ($this->current_page < $this->count_pages) {
            $forwsrd = "<li class='page-item' title='На следующую страницу'>"
                    . "<a class='page-link' href='{$this->uri}page=" . ($this->current_page + 1) . "'>&#11179;</a></li>"; // ⮫ &#11179;
        } else {
            $forwsrd = "<li class='page-item disabled' title='На следующую страницу'>"
                    . "<a class='page-link'>&#11179;</a></li>"; // ⮫ &#11179;
        }

        if ($this->current_page > 1) {
            $startPage = "<li class='page-item' title='На первую страницу'>"
                    . "<a class='page-link' href='{$this->uri}page=1'>|&laquo;</a></li>";
        } else {
            $startPage = "<li class='page-item disabled' title='На первую страницу'>"
                    . "<a class='page-link'>|&laquo;</a></li>";
        }

        if ($this->current_page < $this->count_pages) {
            $endPage = "<li class='page-item' title='На последнюю страницу'>"
                    . "<a class='page-link' href='{$this->uri}page=" . $this->count_pages . "'>&raquo;|</a></li>";
        } else {
            $endPage = "<li class='page-item disabled' title='На последнюю страницу'>"
                    . "<a class='page-link'>&raquo;|</a></li>";
        }

        if ($this->current_page - 2 > 0) {
            $page2left = "<li class='page-item' title='Назад на 2 старницы'>"
                    . "<a class='page-link' href='{$this->uri}page=" . ($this->current_page - 2) . "'>&lt;&lt;</a></li>"; // &laquo;
        } else {
            $page2left = "<li class='page-item disabled' title='Назад на 2 старницы'>"
                    . "<a class='page-link'>&lt;&lt;</a></li>"; // &laquo;
        }

        if ($this->current_page - 1 > 0) {
            $page1left = "<li class='page-item' title='Назад на 1 старницу'>"
                    . "<a class='page-link' href='{$this->uri}page=" . ($this->current_page - 1) . "'>&lt;</a></li>"; // < &lt;
        } else {
            $page1left = "<li class='page-item disabled' title='Назад на 1 старницу'>"
                    . "<a class='page-link'>&lt;</a></li>"; // < &lt;
        }

        if ($this->current_page + 1 <= $this->count_pages) {
            $page1right = "<li class='page-item' title='Вперёд на 1 старницу'>"
                    . "<a class='page-link' href='{$this->uri}page=" . ($this->current_page + 1) . "'>&gt;</a></li>"; // > &gt;
        } else {
            $page1right = "<li class='page-item disabled' title='Вперёд на 1 старницу'>"
                    . "<a class='page-link'>&gt;</a></li>"; // > &gt;
        }

        if ($this->current_page + 2 <= $this->count_pages) {
            $page2right = "<li class='page-item' title='Вперёд на 2 старницы'>"
                    . "<a class='page-link' href='{$this->uri}page=" . ($this->current_page + 2) . "'>&gt;&gt;</a></li>"; // &raquo;
        } else {
            $page2right = "<li class='page-item disabled' title='Вперёд на 2 старницы'>"
                    . "<a class='page-link'>&gt;&gt;</a></li>"; // &raquo;
        }

        return    '<nav>'
                . '<ul class="pagination justify-content-center">'
                . $startPage . $back . $page2left . $page1left
                . '<li class="page-item active"><a class="page-link">'
                . 'Стр. ' . $this->current_page . ' из ' . $this->count_pages . ' | '
                . 'Записи ' . ($this->get_sql_limit_start() + 1) . '-' . ($this->get_sql_limit_start() + $this->row_per_page) . ' из ' . $this->count_rows . '</a></li>'
                . $page1right . $page2right . $forwsrd . $endPage
                . '</ul>'
                . '</nav>';
    }
}
