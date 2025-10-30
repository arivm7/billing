<?php

namespace app\controllers;

use app\models\AppBaseModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Pagination;
use config\Search;
use config\tables\Module;

require_once DIR_LIBS ."/functions.php";

class SearchController extends AppBaseController {

    function queryAction() {
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');

        if  (
                !App::isAuth() ||
                !can_use(Module::MOD_SEARCH)
            ) 
        {
            redirect();
        }

        /**
         * Подготовка строки поиска
         * удаление пробелов по краям
         * удаление двойных пробелов
         * замена пробелов на % для использование в запросах like
         */
        if (isset($_GET[Search::F_QUERY])) {
            $searsh_str = trim($_GET[Search::F_QUERY]);
            if (!is_empty($searsh_str)) {
                $searsh_str = trim(preg_replace('/\s+/', '%', $searsh_str));
            } else {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Поисковая строка не задана'));
                redirect();
            }
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Используйте форму для ввода поисковой строки'));
            redirect();
        }

        /**
         * Одиночная таблица, в которой выполняется поиск, 
         * если не указана, то поиск выполянется по всем таблицам
         */
        $t = (isset($_GET['t']) ? h($_GET['t']) : false);

        $model = new AppBaseModel();

        $found = array();

        foreach (Search::SEARCH_PLACES as $row_search) {

            /**
             * Если указана конкретная таблица, то пропускаем все другие таблицы
             */
            if ($t !== false && $t !== $row_search[Search::F_TABLE]) { continue; }

            /**
             * Если таблица не используется для поиска, то пропускаем
             */
            if (!$row_search[Search::F_SEARCH_HERE]) { continue; }

            /**
             * формирование перечня полей для считывания из таблицы для использования в выражении SELECT
             */
            foreach ($row_search[Search::F_SHOW_FIELDS] as $field_show) {
                $fields_show[] = "`".$field_show."`";
            }

            /**
             * формирование перечня параметров поиска для использования в выражении WHERE
             */
            foreach ($row_search[Search::F_SEARSH_IN_FIELDS] as $field_name) {
                $likes_arr[] = "`".$field_name."` LIKE \"%".$searsh_str."%\"";
            }

            /**
             * Сборка поискового запроса
             */
            $sql = "SELECT "
                    . implode(", ", $fields_show)." "
                    . "FROM "
                    . "`".$row_search[Search::F_TABLE]."` "
                    . "WHERE ". implode(" OR ", $likes_arr)." "
                    . (isset($row_search[Search::F_ORDER_BY]) ? "ORDER BY ".$row_search[Search::F_ORDER_BY] : "");
            unset($fields_show);
            unset($likes_arr);
            // echo "[{$sql}]<br>";
            // echo "Поиск в таблице <font size=+1 color=green>{$row_search['title']}</font>... ";

            /**
             * Поиск
             */
            if ($t === false) {
                /**
                 * Если ищем во всех таблицах
                 */
                $pager = null;
                $count = $model->get_count_by_sql($sql);
                $rows = $model->get_rows_by_sql($sql . ' LIMIT ' . Search::SEARCH_LIMIT);
            } else {
                /**
                 * Если указана одна таблица
                 */
                $pager = new Pagination(per_page: Search::SEARCH_LIMIT_FOR_ONE, sql: $sql);
                $count = $pager->count_rows;
                $rows = $pager->get_rows();
            }

            /**
             * Если поиск что-то нашёл
             */
            if (count($rows) > 0) {

                /**
                 * замена полей на ссылки для удобства навигации
                 */
                foreach ($row_search[Search::F_REPLACE_FIELDS] as $rec) {
                    // debug($rec, 'replace_field_on_table: ');
                    replace_field_on_table($rows, $rec['field'], $rec['func']);
                }

                // debug($rows, '$rows', die: 0);
            }

            $row_search['pager'] = $pager;
            $row_search['count'] = $count;
            $row_search['found'] = $rows;
            $found[] = $row_search;
            unset($count);
            unset($rows);
        }

        View::setMeta(__('Поиск') . ' :: ' . $searsh_str);
        $this->setVariables([
            'query' => $searsh_str,
            'found' => $found,
        ]);

    }


    
}
