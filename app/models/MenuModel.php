<?php
/*
 *  Project : my.ri.net.ua
 *  File    : MenuModel.php
 *  Path    : app/models/MenuModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use config\tables\Menu;

/**
 * Description of MenuModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class MenuModel extends AppBaseModel {


    public string $db_table = Menu::TABLE;



    function __construct(string $db_table = Menu::TABLE) {
        parent::__construct();
        $this->db_table = $db_table;
    }



    /**
     * Возвращает массив со всеми записями таблицы меню
     * @param string $table_name
     * @return array
     */
    public function get_menu_raw(string $table_name = null): array {
        if (!$table_name) { $table_name = $this->db_table; }

        $sql = "SELECT * FROM `{$table_name}` WHERE 1 ORDER BY `{$table_name}`.`" . Menu::F_ORDER . "` ASC";
        return $this->get_rows_by_sql(sql: $sql, row_id_by: Menu::F_ID);
    }



    /**
     * Возвращает ассоциативный массив,
     * подготовренный в виде вложенной структуры меню
     * @param array $data_raw
     * @param string $field_parent
     * @param string $field_child
     * @return array
     */
    public function get_menu_tree(
            array $data_raw = null,
            string $field_parent = Menu::F_PARENT_ID,
            string $field_child = Menu::F_CHILDS): array {
        if (!$data_raw) {
            $data_raw = $this->get_menu_raw($this->db_table);
        }
        $tree = [];
        $data = $data_raw;
        foreach ($data as $key => &$node) {
            if (!$node[$field_parent]) {
                $tree[$key] = &$node;
            } else {
                $data[$node[$field_parent]][$field_child][$key] = &$node;
            }
        }
        return $tree;
    }



    public function get_menu_item_by_id(int $id_value, string $table_name = null): array {
        if (!$table_name) { $table_name = $this->db_table; }
        return $this->get_row_by_id(table_name: $table_name, field_id: Menu::F_ID, id_value: $id_value);
    }
}