<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : edit_table_api.php
 *  Path    : billing/libs/edit_table_api.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



require_once DIR_LIBS . '/functions.php';


//
// =====================================================================================================================
//
// table templates
//



define('TT_CONTROL_FIELDS', 'control_fields');
define('TT_REPLACE_FIELDS', 'replace_fields');
define('TT_TITLES', 'titles');
define('TT_FIELDS', 'fields');
define('TT_CELL_ATTRIBUTES', 'cell_attributes');
define('TT_DEFAULTS', 'defaults');

$table_templates = [
    'devices_list' => [
        TT_CONTROL_FIELDS => [
            ['control_1' => 'is_ip', 'dependent' => 'mac'],
            ['control_1' => 'is_ip', 'dependent' => 'ip'],
            ['control_1' => 'is_ip', 'dependent' => 'ip_ext'],
            ['control_1' => 'is_ip', 'dependent' => 'ip_dev'],
            ['control_1' => 'is_ip', 'dependent' => 'url_http'],
            ['control_1' => 'is_ip', 'dependent' => 'url_https'],
            ['control_1' => 'is_ip', 'dependent' => 'url_winbox'],
            ['control_1' => 'is_ip', 'dependent' => 'url_ssh'],
            ['control_1' => 'is_ip', 'dependent' => 'url_zabbix'],
        ],
        TT_REPLACE_FIELDS => [
            ['field' => 'tp_id', 'func' => 'url_tp_form'],
            ['field' => 'type_id', 'func' => 'url_device_type_form'],
            ['field' => 'pass', 'func' => 'pass_hide'],
            ['field' => 'is_ip', 'func' => 'get_html_CHECK'],
            ['field' => 'is_abon_dev', 'func' => 'get_html_CHECK'],
            ['field' => 'creation_date', 'func' => 'get_gate2str'],
            ['field' => 'creation_uid', 'func' => 'get_user_name_short'],
            ['field' => 'modified_date', 'func' => 'get_gate2str'],
            ['field' => 'modified_uid', 'func' => 'get_user_name_short'],
        ],
        TT_TITLES => [
            'id', 'TP', 'type', 'DEVICE', 'IP', 'login', 'URLs', 'is_abon', 'modified', 'act'
        ],
        TT_FIELDS => [
            '{id}', '{tp_id}<br>'
            . '{placed_on}', '{type_id}', '<span title="{description}">{title}</span>'
            . '<br>{barcode}<br>{qrcode}', "<nobr>"
            . "{is_ip} IP-устройство<br>"
            . paint('IP : ', face: FACE_MONOSPACE, size: -2) . '{ip}<br>'
            . paint('EXT: ', face: FACE_MONOSPACE, size: -2) . '{ip_ext}<br>'
            . paint('DEV: ', face: FACE_MONOSPACE, size: -2) . '{ip_dev}'
            . '</nobr>', '{login}<br>{pass}', '{url_http}<br>{url_https}<br>{url_winbox}<br>{url_ssh}<br>{url_zabbix}', '{is_abon_dev}', paint(
                    "C: " . '{creation_date}' . " | " . '{creation_uid}' . "<br>"
                    . "M: " . '{modified_date}' . " | " . '{modified_uid}',
                    color: GRAY, size: -2, face: 'monospace'), '{act}'
        ],
        TT_CELL_ATTRIBUTES => [
            'align=right', '', '', '', 'align=left', '', 'align=left', 'align=center', 'align=left', ''
        ],
        TT_DEFAULTS => [
            'tp_id' => null, 'type_id' => null,
            'is_ip' => 0, 'ip' => '0.0.0.0', 'ip_ext' => '{IP}', 'ip_dev' => '{IP}',
            'url_http' => 'http://{IP}:10001', 'url_https' => 'https://{IP}:10443',
            'url_winbox' => 'winbox://{IP}:18291', 'url_ssh' => 'ssh://{IP}:21232',
            'url_zabbix' => 'https://my.ri.net.ua/zabbix/sysmaps.php',
            'is_abon' => 0
        ]
    ]
];



//
// =====================================================================================================================
//



// table info column fields
// Имена колонок из таблицы свойств полей
define('TABLE_CATALOG',  'TABLE_CATALOG');
define('TABLE_SCHEMA',   'TABLE_SCHEMA');
define('TABLE_NAME',     'TABLE_NAME');
define('COLUMN_NAME',    'COLUMN_NAME');
define('COLUMN_KEY',     'COLUMN_KEY');
define('COLUMN_DEFAULT', 'COLUMN_DEFAULT');
define('IS_NULLABLE',    'IS_NULLABLE');
define('DATA_TYPE',      'DATA_TYPE');
define('COLUMN_TYPE',    'COLUMN_TYPE');
define('COLUMN_COMMENT', 'COLUMN_COMMENT');

// поля REFERENCED записи
// имена пересекающихся таблиц и полей
define('REFERENCED_TABLE_NAME',  'REFERENCED_TABLE_NAME');
define('REFERENCED_COLUMN_NAME', 'REFERENCED_COLUMN_NAME');

// table status fields
define('FIELD_STAT_COMMENT', 'Comment');

// Элементы формы редактирования записи
define('POST_FLD_TABLE', 't');
define('POST_FLD_ROW', 'row');
define('CMD_CLONE', 'clone');
define('CMD_NEW', 'new_row');
define('SUBMIT_NAME_DEF', 'submit_form');
define('SUBMIT_TITLE_EDIT', 'Edit row');
define('SUBMIT_TITLE_ADD', 'Add row');

// имена полей
define('FIELD_ID', 'id');  // имя ID поля записи
define('FIELD_ACT', 'act'); // имя поля записи для действий (в конце таблицы)
// имена полей GET-запросов
define('GET_TABLE', 't');
define('GET_ROW_ID', 'row_id');
define('GET_SORT_ASC', 'sa');
define('GET_SORT_DESC', 'sd');
define('GET_FIELD_NAME', 'fn');
define('GET_FIELD_VALUE', 'fv');
define('GET_WHERE', 'w');

//
// =============================================================================================
//



$CASHE_TABLE_INFO = array();

/**
 * Получить информацю о полях таблицы.
 * Вернуть из БД таблицу с информацией о всех полях таблицы.
 * @global type $CASHE_TABLE_INFO -- кэш
 * @param string $table_name
 * @return array
 */
function get_table_fields_info(string $table_name): array {
    global $CASHE_TABLE_INFO;

    if (!array_key_exists($table_name, $CASHE_TABLE_INFO)) {
        $sql = "SELECT
                    " . TABLE_SCHEMA . ",
                    " . TABLE_NAME . ",
                    " . COLUMN_NAME . ",
                    " . COLUMN_KEY . ",
                    " . COLUMN_DEFAULT . ",
                    " . IS_NULLABLE . ",
                    " . DATA_TYPE . ",
                    " . COLUMN_TYPE . ",
                    " . COLUMN_COMMENT . "
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_name='{$table_name}'
                ORDER BY ORDINAL_POSITION ASC";
        $CASHE_TABLE_INFO[$table_name] = get_rows_by_sql($sql);
    }
    return $CASHE_TABLE_INFO[$table_name];
}

/**
 * Из массива с информацией о полях таблицы вернуть одну строку относящуюся к указанному полю.
 * @param $table_info
 * @param $column_name
 * @return array|null
 */
function get_column_info($table_info, $column_name): array|null {
    foreach ($table_info as $row) {
        if ($row[COLUMN_NAME] == $column_name) {
            return $row;
        }
    }
    return null;
}

$CASHE_TABLE_STATUS = array();

/**
 * Вернуть информацю относящуюся к таблице в целом.
 * Например, отсюда можно взять комментарий/описание таблицы.
 * @global array $CASHE_TABLE_STATUS
 * @param string $table_name
 * @return array
 */
function get_table_status(string $table_name): array {
    global $CASHE_TABLE_STATUS;
    if (!array_key_exists($table_name, $CASHE_TABLE_STATUS)) {
        $sql = "SHOW TABLE STATUS WHERE Name = '{$table_name}';";
        $CASHE_TABLE_STATUS[$table_name] = get_rows_by_sql($sql);
    }
    return $CASHE_TABLE_STATUS[$table_name];
}

$CASHE_TABLE_REFERENCES = array();

/**
 * Таблица связанных полей из этой таблицы к другим таблицам
 * @global type $CASHE_TABLE_REFERENCES
 * @param string $table_name
 * @return array
 */
function get_table_references(string $table_name): array {
    global $CASHE_TABLE_REFERENCES;
    if (!array_key_exists($table_name, $CASHE_TABLE_REFERENCES)) {
        $sql = "SELECT TABLE_NAME,
                                               COLUMN_NAME,
                                               CONSTRAINT_NAME,
                                               REFERENCED_TABLE_NAME,
                                               REFERENCED_COLUMN_NAME
                                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                        WHERE TABLE_SCHEMA = 'billing'
                                              AND TABLE_NAME = '{$table_name}'
                                              AND REFERENCED_COLUMN_NAME IS NOT NULL;";
        $CASHE_TABLE_REFERENCES[$table_name] = get_rows_by_sql($sql);
    }
    return $CASHE_TABLE_REFERENCES[$table_name];
}

/**
 * Вернуть строку перекрёстных связей, относящуюся к одному полю
 * @param string $table_name
 * @param string $field_name
 * @return array|null
 */
function get_references_row_by_field(string $table_name, string $field_name): array|null {
    $t_ref = get_table_references($table_name);
    foreach ($t_ref as $row) {
        if ($row['COLUMN_NAME'] == $field_name) {
            return $row;
        }
    }
    return null;
}

/**
 * Сформировать html-строку с формой редактирования поля таблицы
 * @param string $table_name
 * @param array $row
 * @param string $method
 * @param string $action
 * @param string $submit_name
 * @param string $submit_title_edit
 * @param string $submit_title_add
 * @param string $form_name
 * @param string $form_attr
 * @return string
 */
function make_form_edit(
        string $table_name,
        array $row,
        string $where = "1",
        string $method = 'post',
        string $action = "",
        string $submit_name = SUBMIT_NAME_DEF,
        string $submit_title_edit = SUBMIT_TITLE_EDIT,
        string $submit_title_add = SUBMIT_TITLE_ADD,
        string $form_name = "",
        string $form_attr = "align=center border=1" . TABLE_ATTRIBUTES  // width=50%
): string {
    global $table_templates;

    $field_name = (isset($_GET[GET_FIELD_NAME]) ? $_GET[GET_FIELD_NAME] : null);
    $field_value = (isset($_GET[GET_FIELD_VALUE]) ? $_GET[GET_FIELD_VALUE] : null);

    $ref = get_http_script() . "?" . GET_TABLE . "=" . $table_name
            . (!is_empty($where) ? "&" . GET_WHERE . "={$where}" : "")
            . (!is_empty($field_name) & !is_empty($field_value) ? "&" . GET_FIELD_NAME . "=" . $field_name . "&" . GET_FIELD_VALUE . "=" . $field_value : "")
            . "#id" . $row[FIELD_ID] . "";
    $a_ref = "<a href='{$ref}' target=_self title='Вернуться к просмотру таблицы'> <<< Вернуться к таблице <<< </a>";
    $t_fields = get_table_fields_info($table_name);
    $t_status = get_table_status(table_name: $table_name);
    $t_caption = $table_name . " | " . $t_status[array_key_first($t_status)][FIELD_STAT_COMMENT];
    $s = "<form name='{$form_name}' method='{$method}' action='{$action}'>";
    $s .= "<input name='" . POST_FLD_TABLE . "' type='hidden' value='{$table_name}' />";
    $s .= "<table {$form_attr}>";
    $s .= "<tr>"
            . "<td colspan=2 align=center>"
            . "<div align=left>" . $a_ref . "</div>"
            . "<a name=EDIT>Редактирование записи {$row[FIELD_ID]} таблицы<br>" . paint($t_caption, b: 1, color: GREEN, size: +3) . "</a>"
            . "</td>"
            . "</tr>";
    foreach ($row as $field => $value) {
        if ($field == $field_name) {
            $value = $field_value;
        }

//                                if (array_key_exists(key: TT_CONTROL_FIELDS, array: $table_templates[$table_name])) {
//                                    foreach ($table_templates[$table_name][TT_CONTROL_FIELDS] as $control_row) {
//                                        $control_row[]
//                                        // ['control_1' => 'is_ip',     'dependent' => 'mac'],
//
//                                    }
//                                }

        $field_info = get_column_info(table_info: $t_fields, column_name: $field);
        $s .= "<tr>"
                . "<td align=right width=35% >"
                . "<b>{$field}</b><br>"
                . (isset($field_info[COLUMN_COMMENT]) ? "{$field_info[COLUMN_COMMENT]}" : "") . "<br>"
                . paint("[{$field_info[COLUMN_TYPE]}]", color: GRAY)
                . "</td>"
                . "<td align=left width=65% >";

        if ($field == "creation_date" || $field == "modified_date") {
            $s .= $value . " | " . date(DATE_FORMAT, $value);
        } elseif ($field == "creation_uid" || $field == "modified_uid") {
            $s .= $value . " | " . get_user_name_short($value);
        } elseif ($field == "creation_uid" || $field == "modified_uid") {
            $s .= $value . " | " . get_user_name_short($value);
        } else {
            $ref_row = get_references_row_by_field(table_name: $table_name, field_name: $field);
            if (!is_null($ref_row)) {
                $s .= get_references_rolldown(
                        sub_table: $ref_row['REFERENCED_TABLE_NAME'],
                        return_name: "" . POST_FLD_ROW . "[{$field}]",
                        field_id: $ref_row['REFERENCED_COLUMN_NAME'],
                        selected_id: $value);
            } else {
                //if ($field_info[COLUMN_TYPE] == 'tinyint(1)') {
                //    $s .= "<input "
                //            . "name='".POST_FLD_ROW."[{$field}]' "
                //            . "type='checkbox' "
                //            . "placeholder='{$field}' "
                //            . ($value ? " checked ":"")
                //            . ($field_info[COLUMN_KEY] == 'PRI' ? "readonly " : "")
                //        . "/>";
                //}
                if ($field_info[COLUMN_TYPE] == 'text') {
                    $s .= "<textarea rows=3 cols=45 "
                            . "name='" . POST_FLD_ROW . "[{$field}]' "
                            . "style=\"width: 98%; font-size: medium;\" "
                            . "placeholder='{$field}' "
                            . ($field_info[COLUMN_KEY] == 'PRI' ? "readonly " : "")
                            . ">"
                            . $value
                            . "</textarea>";
                } else {
                    $s .= "<input "
                            . "name='" . POST_FLD_ROW . "[{$field}]' "
                            . "type='text' "
                            . "placeholder='{$field}' "
                            . "value=\"{$value}\" "
                            . "style=\"width: 98%; font-size: medium;\" "
                            . ($field_info[COLUMN_KEY] == 'PRI' ? "readonly " : "")
                            . "/>";
                }
            }
        }
        $s .= "</td>"
                . "</tr>";
    }
    $s .= "<tr>"
            . "<td colspan=2 align=center>"
            . "<input type=\"submit\" name=\"{$submit_name}\" value=\"" . (validate_id($table_name, $row[FIELD_ID]) ? $submit_title_edit : $submit_title_add) . "\" />"
            . "<div align=left>" . $a_ref . "</div>"
            . "</td>"
            . "</tr>";
    $s .= "</table>";
    $s .= "</form>";

    return $s;
}

/**
 * Собирает данные строки POST
 * и вносит в базу с помощью update_row_by_id().
 * Если ID нет, то вносит в базу с помощью insert_row_by_id().
 * @param  string|null $submit_name -- имя кнопки формы отправки данных, для подтверждения что данные отправлены именно из формы
 * @return int  ID если всё норм  // форма обработана
 *         null    если ошибка    // форма с ошибкой
 */
function form_parse_submited(string|null $submit_name = null): int|null {
    if (!is_null($submit_name)) {
        if (!isset($_POST[$submit_name])) {
            echo "Нет команды отправки формы<br>";
            return null;
        }
    }
    if (isset($_POST[POST_FLD_TABLE]) && isset($_POST[POST_FLD_ROW])) {
        $table = $_POST[POST_FLD_TABLE];
        $row = $_POST[POST_FLD_ROW];
    } else {
        echo "Нет данных отправленной формы<br>";
        return null;
    }

    /*
     * Проверяем все поля на предмет пересечения с другими таблицами.
     * Если есть пересечения, то
     * поле проверяется на валидность значения в другой таблице,
     * если значение поля не валидно, то поле удаляется из записи.
     */
    foreach ($row as $key => $value) {
        $ref_row = get_references_row_by_field(table_name: $table, field_name: $key);
        if (!is_null($ref_row)) {
            // есть пересечене с другой таблицей.
            // если значение НЕ валидное, то поле удаляется и внесение данных не происходит.
            if (!validate_id(table_name: $ref_row[REFERENCED_TABLE_NAME], id_value: $value)) {
                unset($row[$key]);
            }
        }
    }



    /*
     * Проверяем наличие ключа ID
     * если есть, то обновляем запись,
     * если нет, то добавляем запись.
     */
    foreach ($row as $key => $value) {
        if ($key == FIELD_ID) {
            if (validate_id($table, $value)) {
                /*
                 * Обновление существующей записи
                 */
                if (update_row_by_id(table: $table, row: $row)) {
                    echo "Запись отредактирована.<br>";
                    return $row[FIELD_ID];
                } else {
                    echo "Ошибка обновления записи.<br>";
                    return $row[FIELD_ID];
                }
            } else {
                break;
            }
        }
    }

    /*
     * Вставка новой записи
     */
    $row_id = insert_row_by_id(table: $table, row: $row);
    if ($row_id > 0) {
        echo "Запись добавлена ({$row_id}).<br>";
        return $row_id;
    } else {
        echo "Ошибка добавления записи ({$row[FIELD_ID]}/{$row_id}) .<br>";
        return $row_id;
    }
}

/**
 * Возвращает запись для вставки строки в таблицу с предварительно заполненными полями
 * @param string $table_name
 * @return array
 */
function get_empty_row(string $table_name): array {
    global $table_templates;
    $t_fields = get_table_fields_info($table_name);
    $row = array();
    foreach ($t_fields as $column) {
        if (isset($table_templates[$table_name][TT_DEFAULTS][$column[COLUMN_NAME]])) {
            $row[$column[COLUMN_NAME]] = $table_templates[$table_name][TT_DEFAULTS][$column[COLUMN_NAME]];
        } elseif ($column[COLUMN_KEY] == 'PRI') {
            $row[$column[COLUMN_NAME]] = get_next_id($table_name);
            // unset($row[$column[COLUMN_NAME]]);
            // ID поле не создаём, чтобы ID формировался автоматически при вставке таблицы.
            // continue;
        } elseif ($column[IS_NULLABLE] == 'YES') {
            $row[$column[COLUMN_NAME]] = null;
        } elseif (str_contains($column[DATA_TYPE], 'text')) {
            $row[$column[COLUMN_NAME]] = "";
        } elseif (str_contains_array($column[DATA_TYPE], ['int', 'float'])) {
            $row[$column[COLUMN_NAME]] = 0;
        } else {
            $row[$column[COLUMN_NAME]] = null;
        }
    }
    return $row;
}

/**
 * Возвращает html-строку, представляющую собой выпадающий список значений для подстановки в поле связанной таблицы
 *
 * @param string $sub_table -- таблица, из которой выбираются щначения для подстановки
 * @param string $return_name -- имя поля html-формы, в которое вставляется выбранное значение
 * @param string $field_id -- имя поля в списке, в котором хранится ID
 * @param string|null $selected_id -- ID предварительно выбранного поля
 * @return string -- html-строка с выпадающим списком
 */
function get_references_rolldown(string $sub_table, string $return_name, string $field_id = FIELD_ID, string|null $selected_id = ""): string {
    $t_fields = get_table_fields_info($sub_table);
    $fields_filter1 = ['status', 'active'];
    $fields_filter0 = ['deleted'];
    $where = "1";
    foreach ($fields_filter1 as $field) {
        if (!is_null(get_column_info(table_info: $t_fields, column_name: $field))) {
            $where .= " AND `{$field}`=1";
        }
    }
    foreach ($fields_filter0 as $field) {
        if (!is_null(get_column_info(table_info: $t_fields, column_name: $field))) {
            $where .= " AND `{$field}`=0";
        }
    }

    $table = get_rows_by_where($sub_table, where: $where);

    $fields_title = ['title', 'name', 'name_short', 'address'];
    $fields_descr = ['description', 'name', 'address'];

    $field_text = "";
    foreach ($fields_title as $title) {
        if (array_key_exists($title, $table[array_key_first($table)])) {
            $field_text = $title;
            break;
        }
    }

    $field_title = "";
    foreach ($fields_descr as $descr) {
        if (array_key_exists($descr, $table[array_key_first($table)])) {
            $field_title = $descr;
            break;
        }
    }

    return get_id_from_simple_rolldown(
            list: $table, // массив элементов из которого выбираем ID
            return_name: $return_name, // имя возвращаемого поля формы с выбранным значением ID
            selected_id: $selected_id, // ID поля "по умолчанию", предварительно установленного
            field_id: $field_id, // имя поля в списке, в котором хранится ID
            field_text: $field_text, // имя поля в списке где хранится название элемента
            field_title: $field_title   // имя поля в списке, в котором хранится значение подсказки для тэга title
    );
}

/**
 * Клонирует строку базы в нвую,
 * возвращает ID новой строки,
 * или NULL если клонирование не удалось.
 * @param string $table -- таблица в которой происходят действия
 * @param int $row_id -- клонируемая строка
 * @return int|null -- ID нововой строки
 */
function table_row_clone(string $table, int $row_id): int|null {
    if (validate_id($table, $row_id)) {
        $row = get_row_by_id($table, $row_id);
        $row[FIELD_ID] = get_next_id($table);
        if ($row[FIELD_ID] == insert_row_by_id($table, $row)) {
            echo "скопирован.";
            return $row[FIELD_ID];
        } else {
            echo "Копирование {$table}[{$row_id}] по какой-то причине не удалось, или скопировалось иначе чем ожидалось.<br>";
            return null;
        }
    } else {
        echo "Нет исходной строки для клонирования {$table}[{$row_id}].<br>";
        return null;
    }
}

function validate_where(string $where): bool {
    $bad = ['CREATE', 'DATABASES', 'SELECT', 'INSERT', 'DELETE', 'UPDATE', 'FUNCTION'];
    if (str_contains_array(haystack: $where, needle: $bad, case_sensitive: 0)) {
        return false;
    } else {
        return true;
    }
}

function get_gate2str(int|string|null $date): string {
    if (is_int($date) || is_numeric($date)) {
        return date(DATETIME_FORMAT, $date); // . " | " . $date;
    } else {
        return date(DATETIME_FORMAT, 0);     // . " | " . 0;
    }
}

function get_table_by_get_param(string $table_name): array {
    $where = (isset($_GET[GET_WHERE]) ? $_GET[GET_WHERE] : "1");
    if (isset($_GET[GET_FIELD_NAME]) && isset($_GET[GET_FIELD_VALUE])) {
        $where .= " and `{$_GET[GET_FIELD_NAME]}`='" . (is_empty($_GET[GET_FIELD_VALUE]) ? "" : rawurldecode($_GET[GET_FIELD_VALUE])) . "'";
    }

    $sort = (isset($_GET[GET_SORT_ASC]) ? $_GET[GET_SORT_ASC] . " ASC" : (isset($_GET[GET_SORT_DESC]) ? $_GET[GET_SORT_DESC] . " DESC" : null
            )
            );

    echo "where: $where<br>";
    //echo "sort: $sort<br>";
    $t = get_rows_by_where(
            table: $table_name,
            where: $where,
            order_by: $sort
    );
    return $t;
}

function show_table(string $table_name) {
    global $table_templates;

    $t_status = get_table_status(table_name: $table_name);
    $t_fields = get_table_fields_info(table_name: $table_name);
    //echo "_t_fields:<pre>". print_r($t_fields, 1)."</pre><hr>";
    //echo get_html_table($t_fields);

    /*
     * REF-ссылка на эту таблицу с параметрами выборки
     */
    $ref_src = get_http_script() . "?" . GET_TABLE . "=" . $table_name
            . (isset($_GET[GET_WHERE]) ? "&" . GET_WHERE . "=" . $_GET[GET_WHERE] : "")
            . (isset($_GET[GET_FIELD_NAME]) && isset($_GET[GET_FIELD_VALUE]) ? "&" . GET_FIELD_NAME . "={$_GET[GET_FIELD_NAME]}&" . GET_FIELD_VALUE . "={$_GET[GET_FIELD_VALUE]}" : "");

    /*
     * REF-ссылка на эту таблицу БЕЗ фильтра по значению полей
     */
    $ref_no_filter = get_http_script() . "?" . GET_TABLE . "=" . $table_name
            . (isset($_GET[GET_WHERE]) ? "&" . GET_WHERE . "=" . $_GET[GET_WHERE] : "");

    /*
     * Формирование строки сортировки ао полям таблицы
     */
    $t_sort_row = array();
    foreach ($t_fields as $row_properties) {
        $sort_asc = "&" . GET_SORT_ASC . "=" . $row_properties[COLUMN_NAME];  // "ORDER BY `tp_id` ASC";
        $sort_desc = "&" . GET_SORT_DESC . "=" . $row_properties[COLUMN_NAME];  // "ORDER BY `tp_id` DESC";
        $t_sort_row[$row_properties[COLUMN_NAME]] = "<a href='{$ref_src}{$sort_asc}'>" . CH_TRIANGLE_UP . "</a> <a href='{$ref_src}{$sort_desc}'>" . CH_TRIANGLE_DOWN . "</a>";
    }
    //echo get_html_table([$t_sort_row]);




    /*
     * Считать таблицу согласно параметрам GET-запроса
     */
    $t = get_table_by_get_param($table_name);
    //echo get_html_table($t);



    /*
     * Формирование системы фильтров по полям таблицы
     */
    $t_field_filters = array();
    foreach ($t as $row_index => $row_values) {
        foreach ($row_values as $f => $v) {
            $t_field_filters[$row_index][$f] = get_html_img(
                    src: SRC_ICON_FILER,
                    alt: '[F]',
                    href: get_http_script()
                    . "?" . GET_TABLE . "=" . $table_name
                    . "&" . GET_FIELD_NAME . "=" . $f
                    . "&" . GET_FIELD_VALUE . "=" . (is_empty($v) ? "" : rawurlencode($v)) . "'",
                    title: "Показать только \n[{$f}] == {$v}",
                    width: 10, height: 10
            );
        }
    }
    //echo get_html_table($t_field_filters);



    /*
     * Предварительная обработка таблицы.
     * Добавление поля "действие" FIELD_ACT
     */
    foreach ($t as $index => &$row) {
        $row[FIELD_ACT] = (isset($row[FIELD_ID]) && is_numeric($row[FIELD_ID]) && validate_id($table_name, $row[FIELD_ID]) ? get_html_img(href: get_http_script() . "?" . GET_TABLE . "=" . $table_name . "&" . GET_ROW_ID . "=" . $row[FIELD_ID] . "#EDIT", src: SRC_EDIT) . " "
                . get_html_img(href: get_http_script() . "?" . GET_TABLE . "=" . $table_name . "&" . GET_ROW_ID . "=" . $row[FIELD_ID] . "&" . CMD_CLONE, src: SRC_CLONE) : "-");
        //$t_view[$index] = $row;
    }



    /*
     * Если есть шаблон таблицы, то произвести замены в полях согласно шаблона
     */
    if (array_key_exists($table_name, $table_templates)) {

        /*
         * Замена полей с помощью процедур указанных в
         * $templates = ['<имя_таблицы>' => ['replace_fields' => [['field' => '<поле>', 'func' => '<функция>']];
         */
        foreach ($table_templates[$table_name][TT_REPLACE_FIELDS] as $value) {
            replace_field_on_table(table: $t, field: $value['field'], func: $value['func']);
        }



        /*
         * Добавляем к значениям полей ссылки на фильтры
         */
        foreach ($t as $i => &$row) {
            foreach ($row as $f => &$v) {
                if (array_key_exists(key: $f, array: $t_field_filters[$i])) {
                    $v = $t_field_filters[$i][$f] . " " . $v;
                }
            }
        }



        /*
         * Замена полей на те, что указаны в шаблоне
         */
        foreach ($t as $index => $t_row) {
            /*
             * Строка, собранная из шаблона
             * $templates = ['имя таблицы' => ['titles'  => [], 'fields'  => []]]
             */
            $new_row = array_combine(keys: $table_templates[$table_name][TT_TITLES], values: $table_templates[$table_name][TT_FIELDS]);
            foreach ($t_row as $src_key => $src_value) {
                if (is_null($src_value)) {
                    $src_value = "";
                }
                foreach ($new_row as $i => $field_value) {
                    $new_row[$i] = str_replace(search: '{' . $src_key . '}', replace: $src_value, subject: $field_value);
                }
            }
            $t[$index] = $new_row;
        }



        /*
         * Переворматирование строки сортировки в соответствии с шаблоном
         */
        $new_row = array_combine(keys: $table_templates[$table_name][TT_TITLES], values: $table_templates[$table_name][TT_FIELDS]);
        foreach ($t_sort_row as $src_key => $src_value) {
            if (is_null($src_value)) {
                $src_value = "";
            }
            foreach ($new_row as $i => $field_value) {
                $new_row[$i] = str_replace(search: '{' . $src_key . '}', replace: $src_value, subject: $field_value);
            }
        }
        unset($new_row[FIELD_ACT]);
        $t_sort_row = $new_row;
        //echo get_html_table([$t_sort_row]);
    } else {

        /*
         * Добавляем к значениям полей ссылки на фильтры
         */
        foreach ($t as $i => &$row) {
            foreach ($row as $f => &$v) {
                if (array_key_exists(key: $f, array: $t_field_filters[$i])) {
                    $v = $t_field_filters[$i][$f] . " " . $v;
                }
            }
        }
    }



    array_unshift($t, $t_sort_row);
    $t[][FIELD_ACT] = get_html_img(href: $ref_src . "&new_row", src: SRC_PLUS);
    $t = get_aligned_table($t);

    echo get_html_table(
            $t,
            caption: paint("<br>" . $table_name . " | " . $t_status[array_key_first($t_status)][FIELD_STAT_COMMENT]
                    . (isset($_GET[GET_FIELD_NAME]) && isset($_GET[GET_FIELD_VALUE]) ? paint("<br><br><a href=$ref_no_filter>убрать фильтр</a>", size: -1) : "")
                    . "<br>&nbsp;",
                    color: GREEN, size: +3),
            table_attributes: "align=center " . TABLE_ATTRIBUTES,
            cell_attributes: (isset($table_templates[$table_name][TT_CELL_ATTRIBUTES]) ? $table_templates[$table_name][TT_CELL_ATTRIBUTES] : null));
}



//
// =====================================================================================================================
//



//echo "<h1>Редактирование записей таблицы:</h1><br/>";



//echo "GET:<pre>". print_r($_GET, 1)."</pre><hr>";
//echo "POST:<pre>". print_r($_POST, 1)."</pre><hr>";


if (!isset($_GET[GET_TABLE])) {
    $table_name = $_GET[GET_TABLE];
} else {
    throw new Exception("Не указана таблица");
}

if (isset($_GET[GET_ROW_ID]) && validate_id($table_name, intval($_GET[GET_ROW_ID]))) {
    $row_id = intval($_GET[GET_ROW_ID]);
} else {
    $row_id = null;
}

if (isset($_GET[GET_WHERE]) && validate_where($_GET[GET_WHERE])) {
    $where = $_GET[GET_WHERE];
} else {
    $where = null;
}



/**
 * Обработка комманды клонирования
 *
 */
function cmd_clone() {
    global $table_name, $row_id;
    if (isset($_GET[CMD_CLONE])) {
        echo "Клонирование записи {$table_name}[{$row_id}].<br>";
        $row_id = table_row_clone($table_name, $row_id);
        if (validate_id($table_name, $row_id)) {
            echo "Запись скопирована {$table_name}[{$row_id}].<br>";
            $ref = get_http_script() . "?" . GET_TABLE . "=" . $table_name . "&" . GET_ROW_ID . "=" . $row_id; // ."#EDIT"
            echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1;URL=" . rawurldecode($ref) . "\">";
            exit;
        } else {
            echo "Копирование по какой-то причине не удалось или скопировалось иначе чем ожидалось ({$table_name}[{$row_id}]).<br>";
            $row_id = null;
        }
    }
}



/**
 * Обработка комманды редактирования
 */
function cmd_update_submited_row() {
    global $table_name;
    if (isset($_POST[SUBMIT_NAME_DEF])) {
        $row_id = form_parse_submited(submit_name: SUBMIT_NAME_DEF);
        if (validate_id($table_name, $row_id)) {
            echo "Запись отредактирована {$table_name}[{$row_id}].<br>";
            $ref = get_http_script() . "?" . GET_TABLE . "=" . $table_name . "&" . GET_ROW_ID . "=" . $row_id; // ."#EDIT"
            echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"1;URL=" . rawurldecode($ref) . "\">";
            exit;
        } else {
            echo "Ошибка редактирования ({$table_name}[{$row_id}]).<br>";
            $row_id = null;
        }
    }
}



/**
 * ВЫБОР ФОРМЫ ДЛЯ ОТОБРАЖЕНИЯ
 */
function cmd_route_form() {
    if (isset($_GET[CMD_NEW])) {

        /*
         * Обработка комманды клонирования
         */
        $new_row = get_empty_row($table_name);
        $form = make_form_edit(table_name: $table_name, row: $new_row);
        echo $form;
    } elseif (is_null($row_id)) {

        /*
         * Если ИД не указан, то отображается таблица
         */
        show_table($table_name);
    } elseif (validate_id($table_name, $row_id)) {

        /*
         * Если указан ИД строки то форма редактирования
         */
        $row = get_row_by_id(table: $table_name, id: $row_id);
        $form = make_form_edit(table_name: $table_name, row: $row);
        echo $form;
    } else {
        throw new \Exception('Данные для формы не распознаны');
    }
}









