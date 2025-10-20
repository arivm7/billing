<?php
/**
 *  Project : s1.ri.net.ua
 *  File    : AdminController.php
 *  Path    : app/controllers/admin/AdminController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Oct 2025 01:45:35
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of AdminController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace app\controllers\admin;



use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;

use config\Icons;
use app\models\MenuModel;
use config\SessionFields;
use billing\core\base\Lang;
use config\tables\Menu;
use config\tables\Module;
require DIR_LIBS . '/form_functions.php';



class AdminController extends AdminBaseController {



    public function indexAction() {
        View::setMeta(
            title: "Админка :: Главная страница.",
            descr: "Тут должно быть описание главной страницы Админки",
            keywords: "Ключевые слова главной страницы админки"
            );
        $this->setVariables([
            'data' => ["1", "data"]
        ]);
    }



    function updateMenuItem(array $post_data) {
        $db = new MenuModel();
        $row = [];
        $row[Menu::F_PARENT_ID]    = intval($post_data[Menu::F_PARENT_ID]);
        $row[Menu::F_ORDER]        = intval($post_data[Menu::F_ORDER]);
        $row[Menu::F_MODULE_ID]    = intval($post_data[Menu::F_MODULE_ID]);
        $row[Menu::F_VISIBLE]      = (int)($post_data[Menu::F_VISIBLE] ?? 0);
        $row[Menu::F_ANON_VISIBLE] = (int)($post_data[Menu::F_ANON_VISIBLE] ?? 0);
        $row[Menu::F_RU_TITLE]     = $post_data[Menu::F_RU_TITLE];
        $row[Menu::F_UK_TITLE]     = $post_data[Menu::F_UK_TITLE];
        $row[Menu::F_EN_TITLE]     = $post_data[Menu::F_EN_TITLE];
        $row[Menu::F_URL]          = $post_data[Menu::F_URL];
        $row[Menu::F_IS_WIDGET]    = (isset($post_data[Menu::F_IS_WIDGET]) ? 1 : 0);
        $row[Menu::F_RU_DESCR]     = $post_data[Menu::F_RU_DESCR];
        $row[Menu::F_UK_DESCR]     = $post_data[Menu::F_UK_DESCR];
        $row[Menu::F_EN_DESCR]     = $post_data[Menu::F_EN_DESCR];
//        debug($post_data, '$post_data', die: 0);
//        debug($row, '$row', die: 1);

        if (isset($post_data[Menu::F_ID])) {
            // редактироват имеющуюся строку
            $row[Menu::F_ID]   = $post_data[Menu::F_ID];
            if ($db->update_row_by_id(table: $db->db_table, row: $row)) {
                MsgQueue::msg(MsgType::SUCCESS, __('Menu item fixed'));
            } else {
                MsgQueue::msg(MsgType::ERROR, $db->errorInfo());
            }
        } else {
            // создать новую запись
            if ($db->insert_row(table: $db->db_table, row: $row)) {
                MsgQueue::msg(MsgType::SUCCESS, __('Menu item added'));
            } else {
                MsgQueue::msg(MsgType::ERROR, $db->errorInfo());
            }
        }
        redirect('/admin/admin/menuedit#MENU');
    }



    function prepare_menu(array $tree_raw): array {
        $model = new MenuModel();
        $lang = Lang::code();
        $tree = [];
        foreach ($tree_raw as $key => $item) {
            $row[Menu::F_ID] = $item[Menu::F_ID];
            $row[Menu::F_VISIBLE] = "<img src=".($item[Menu::F_VISIBLE] ? Icons::SRC_ICON_VISIBLE_ON : Icons::SRC_ICON_VISIBLE_OFF)." width=22 >";
            $row[Menu::F_ORDER] = $item[Menu::F_ORDER];
            $row[Menu::F_PARENT_ID] = $item[Menu::F_PARENT_ID];
            $row[Menu::F_MODULE_ID] = ($item[Menu::F_MODULE_ID] ? "<span title='{$model->get_module_title($item[Menu::F_MODULE_ID])}'>{$item[Menu::F_MODULE_ID]}</span>" : '-');
            $row[Menu::F_ANON_VISIBLE] = $item[Menu::F_ANON_VISIBLE];
            $row['item'] =
                    get_html_content_left_right(
                            left:   "<span title='URL:{$item[Menu::F_URL]}\nDESCR:{$item[$lang.Menu::_DESCR]}'>{$item[$lang.Menu::_TITLE]}</span> ",
                            right:  "<a href='?".Menu::F_EDIT_ID."={$key}#FORM' title='".__('edit')."'>". get_html_img(src: Icons::SRC_ICON_EDIT)."</a>"
                                    . "&nbsp;&nbsp;"
                                    . get_html_form_delete_confirm(
                                            action: '/admin/admin/menuedit',
                                            param: [
                                                Menu::F_DELETE_ID => $item[Menu::F_ID],
                                            ],
                                            attr_button: "class='btn btn-dark btn-sm'".(isset($item[Menu::F_CHILDS]) ? " disabled" : ""),
                                            text: get_html_img(src: Icons::SRC_ICON_TRASH))
                            );
            if (isset($item[Menu::F_CHILDS])) {
                $row[Menu::F_CHILDS] = $this->prepare_menu($item[Menu::F_CHILDS]);
            }
            $tree[$key] = $row;
            unset($row);
        }
        return $tree;
    }



    function delete_item($id) {
        $db = new MenuModel();
        if ($db->delete_rows_by_field(Menu::TABLE, Menu::F_ID, $id)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Элемент успешно удалён'));
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Ошибка удаления элемента'));
        }
        redirect('/admin/admin/menuedit#MENU');
    }



    public function menuEditAction() {
        $model = new MenuModel();

        /**
         * Удаления элемента, если есть комманда
         */
        if (isset($_POST[Menu::F_DELETE_ID]) && is_numeric($_POST[Menu::F_DELETE_ID]) ) {
            $this->delete_item($_POST[Menu::F_DELETE_ID]);
        }

        /**
         *  Редактирование данных, если есть данные формы
         */
        if (isset($_POST[Menu::POST_REC]) && is_array($_POST[Menu::POST_REC]) ) {
            $this->updateMenuItem($_POST[Menu::POST_REC]);
        }

        /**
         * Получение данных для редактирования элемента, если есть.
         */
        $item = null;
        if (isset($_GET[Menu::F_EDIT_ID]) && is_numeric($_GET[Menu::F_EDIT_ID])) {
            $item_id = intval($_GET[Menu::F_EDIT_ID]);
            $item = ($model->validate_id(table_name: $model->db_table, field_id: Menu::F_ID, id_value: $item_id)
                        ?   $model->get_menu_item_by_id($item_id)
                        :   null
                    );
        }

        $data_raw = $model->get_menu_raw();
        $tree_raw = $model->get_menu_tree(data_raw: $data_raw);
        $tree = $this->prepare_menu($tree_raw);

        $title = __('Редактирование пользовательского меню');
        View::setMeta(
            title: __('Редактирование пользовательского меню'),
            descr: __('Модуль редактирования пользовательского меню'),
            );

        $this->setVariables([
            'title' => $title,
            'params' => [
                'table'=> $tree,
                'pre_align' => true,
                'col_titles' =>  ["id", "v", "<span title='№ пп -- ".Menu::F_ORDER."'>№</span>", "<span title='parentd_id'>PI</span>", "<span title='Модуль'>M</span>", "AN", "item", "childs"],
                'child_col_titles' =>  true,
                'cell_attributes' =>  ["align=center", "", "align=center", "align=center", "", "", "", ""],
                'child_cell_attributes' => true,
            ],
            'item' => $item,
        ]);
    }



}