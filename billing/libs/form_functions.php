<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : form_functions.php
 *  Path    : billing/libs/form_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */


enum LabelLayout {

    case H;
    case V;
}



enum InputType: string {

    case BUTTON         = 'button';
    case CHECKBOX       = 'checkbox';
    case COLOR          = 'color';
    case DATE           = 'date';
    case DATETIME_LOCAL = 'datetime-local';
    case EMAIL          = 'email';
    case FILE           = 'file';
    case HIDDEN         = 'hidden';
    case IMAGE          = 'image';
    case MONTH          = 'month';
    case NUMBER         = 'number';
    case PASSWORD       = 'password';
    case RADIO          = 'radio';
    case RANGE          = 'range';
    case RESET          = 'reset';
    case SEARCH         = 'search';
    case SUBMIT         = 'submit';
    case TEL            = 'tel';
    case TEXT           = 'text';
    case TIME           = 'time';
    case URL            = 'url';
    case WEEK           = 'week';

}



$ACCORDION_ID = 1;

function __accordion_id() {
    global $ACCORDION_ID;
    return ++$ACCORDION_ID;
}



function get_html_accordion_(
        array $table,
        string $file_view,
        string|null $field_title = null,
        ?callable $func_get_title = null): string {

    $acc_id = __accordion_id();
    $html = "<div class='accordion' id='accordion_{$acc_id}'>";
    foreach ($table as $index => $item) {
        $collapse_id = __accordion_id();
        $html .= "<div class='accordion-item'>"
                    . "<h3 class='accordion-header'>"
                        . "<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse_{$collapse_id}'"
                            . "aria-expanded='false' aria-controls='collapse_{$collapse_id}'>"
                         // . "<div>"
                            . (!is_null($func_get_title) ? $func_get_title($item) : $item[$field_title])
                         // . "</div>"
                        . "</button>"
                    . "</h3>"
                    . "<div id='collapse_{$collapse_id}' class='accordion-collapse collapse' data-bs-parent='#accordion_{$acc_id}'>"
                        . "<div class='accordion-body'>";
                            ob_start();
                            require $file_view;
                            $html .= ob_get_clean();
                            $html .= ""
                        . "</div>"
                    . "</div>"
                . "</div>";
    }
    $html .= "</div>";
    return $html;
}




/**
 * В.2
 * @param array $table
 * @param string $file_view
 * @param string|null $field_title
 * @param callable|null $func_get_title
 * @param int|string|null $open_index
 * @return string
 */
function get_html_accordion(
    array $table,
    string $file_view,
    string|null $field_title = null,
    ?callable $func_get_title = null,
    int|string|null $open_index = null // индекс элемента, который открыть по умолчанию
): string {
    $acc_id = __accordion_id();
    $html = "<div class='accordion' id='accordion_{$acc_id}'>";

    foreach ($table as $index => $item) {
        $collapse_id = __accordion_id();

        // Определяем, должен ли элемент быть открыт
        $is_open = $open_index !== null && $index == $open_index;
        $button_class = $is_open ? 'accordion-button' : 'accordion-button collapsed';
        $collapse_class = $is_open ? 'accordion-collapse collapse show' : 'accordion-collapse collapse';
        $aria_expanded = $is_open ? 'true' : 'false';

        $html .= "<div class='accordion-item'>"
                    . "<h3 class='accordion-header'>"
                        . "<button class='{$button_class}' type='button' data-bs-toggle='collapse' data-bs-target='#collapse_{$collapse_id}'"
                            . " aria-expanded='{$aria_expanded}' aria-controls='collapse_{$collapse_id}'>"
                            . (!is_null($func_get_title) ? $func_get_title($item) : $item[$field_title])
                        . "</button>"
                    . "</h3>"
                    . "<div id='collapse_{$collapse_id}' class='{$collapse_class}' data-bs-parent='#accordion_{$acc_id}'>"
                        . "<div class='accordion-body'>";
                            ob_start();
                            require $file_view;
                            $html .= ob_get_clean();
        $html .=     "</div>"
                . "</div>"
            . "</div>";
    }

    $html .= "</div>";
    return $html;
}



/**
 * Ширина поля метки в колонках табличного форматирования bootstrap-5
 */
const DEFAULT_LABEL_COL = 3;

/**
 * Ширина поля ввода в колонках табличного форматирования bootstrap-5
 */
const DEFAULT_INPUT_COL = 6;



function inputRow(
        string|null $label = null,
        string|null $title = null,
        string|null $name = null,
        string|null $id = null,
        mixed $value = '',
        InputType $type = InputType::TEXT,
        int $label_col = DEFAULT_LABEL_COL,
        int $input_col = DEFAULT_INPUT_COL,
        LabelLayout $l_layout = LabelLayout::V,
        string $post_rec = 'item',
        string $options = "class='mb-3 row'")
{
    $id = ($id ?: 'input_' . $name);
    echo "<div {$options}>"
            . ($l_layout == LabelLayout::H
                ? ($label ? "<label for='$id' class='col-sm-{$label_col} col-form-label'>$label</label>" : "")
                : "")
            . "<div class='col-sm-{$input_col}'>"
                . ($l_layout == LabelLayout::V
                    ? ($label ? "<label for='$id' class='col-form-label'>$label</label>" : "")
                    : "")
                . "<input ".($title ?:"")." type='{$type->value}' class='form-control' id='{$id}' name='{$post_rec}[{$name}]' value='" . h($value) . "'>"
            . "</div>"
        . "</div>";
}



function checkboxRow(
        string|null $label = null,
        string|null $title = null,
        string|null $name = null,
        string|null $id = null,
        bool $checked = false,
        int $label_col = DEFAULT_LABEL_COL,
        int $input_col = DEFAULT_INPUT_COL,
        LabelLayout $l_layout = LabelLayout::V,
        string $post_rec = 'item',
        string $options = "class='mb-3 row'")
{
    $id = ($id ?: 'input_' . $name);
    echo "<div {$options}>"
            . ($l_layout == LabelLayout::H
                ? ($label ? "<label for='$id' class='col-sm-{$label_col} col-form-label'>{$label}</label>" : "")
                : "")
            . "<div class='col-sm-{$input_col}'>"
                . ($l_layout == LabelLayout::V
                    ? ($label ? "<label for='{$id}' class='col-form-label'>{$label}</label><br>" : "")
                    : "")
                . "<input ".($title ?:"")." type='checkbox' class='form-check-input' id='{$id}' name='{$post_rec}[{$name}]' value='1'" . ($checked ? ' checked' : '') . ">"
            . "</div>"
        . "</div>";
}



function dateRow(
        string $label,
        string $name,
        int|null $timestamp,
        int $label_col = DEFAULT_LABEL_COL,
        int $input_col = DEFAULT_INPUT_COL,
        LabelLayout $l_layout = LabelLayout::V,
        string|null $options = "class='mb-3 row'")
{
    inputRow(label: $label, name: $name, value: $timestamp ? date(DATE_FORMAT, $timestamp) : '',
            type: InputType::DATE,
            label_col: $label_col,
            input_col: $input_col,
            l_layout: $l_layout,
            options:  $options
    );
}



/**
 * Создание html-строки выпадающего списка.
 * Ключи массива импользуются для возвращаемых значений, значения полей -- для отображения
 * @param array $data -- Одномерный ассоциативный или обычный массив, из которого монтируется select
 * @param string $name -- возвращаемое имя
 * @param mixed $selected_id -- id предварительно выбранного элемента
 * @param array $excludes_keys -- список игнорируемых ключей
 * @param array $excludes_values -- список игнорируемых значений
 * @param string $select_opt -- параметры тэга select, классы и/или опции.
 * @param string $option_opt -- параметры тэга option, классы и/или опции.
 * @param bool $show_keys -- отображать id/key в выпадающем списке.
 * @param string $show_keys_opt -- параметры отображения ключа в выпадающем списке, классы и/или опции.
 * @return string
 */
function make_html_select(
        array $data,
        string $name,
        mixed $selected_id = null,
        array $excludes_keys = [],
        array $excludes_values = [],
        string $select_opt = "class='form-select'",
        string $option_opt = '',
        bool $show_keys = false,
        string $show_keys_opt = "class='text-bg-secondary font-monospace small'"
        ): string
{
    $str = "<select name='{$name}' {$select_opt}>";
    $str .= "<option value='0'>-</option>";
    foreach ($data as $key => $value) {
        if ($excludes_values && in_array($value, $excludes_values)) { continue; }
        if ($excludes_keys   && in_array($key,   $excludes_keys))   { continue; }
        $str .= "<option {$option_opt} value=\"{$key}\" ".($key == $selected_id ? "selected" : "")." >"
                . ($show_keys ? "<span {$show_keys_opt}>[".sprintf("%02d", $key)."]</span>&nbsp;&nbsp;" : "")
                . "{$value}"
                . "</option>";
    }
    $str .= "</select>";
    return $str;
}



function selectRow(
        string|null $label = null,
        string|null $title = null,
        string|null $name = null,
        array $data = [],
        mixed $selected_id = null,
        bool $show_keys = false,
        int $label_col = DEFAULT_LABEL_COL,
        int $input_col = DEFAULT_INPUT_COL,
        LabelLayout $l_layout = LabelLayout::H,
        string $post_rec = 'item',
        string $options = "class='mb-3 row'")
{
    $id = 'input_' . $name;
    echo "<div {$options}>"
            . ($l_layout == LabelLayout::H
                ? ($label ? "<label for='$id' class='col-sm-{$label_col} col-form-label'>$label</label>" : "")
                : "")
            . "<div class='col-sm-{$input_col}' ".($title ? "title='{$title}'" : "").">"
                . ($l_layout == LabelLayout::V
                    ? ($label ? "<label for='$id' class='col-form-label'>$label</label><br>" : "")
                    : "")
                . make_html_select(data: $data, name: ($post_rec ? "{$post_rec}[{$name}]" : $name), selected_id: $selected_id,
                        show_keys: $show_keys) // , select_opt: "style='min-width: 99%; white-space: nowrap;'"
            . "</div>"
        . "</div>";
}




/**
 * Подтверждение удаления и перенаправление на действие удаления
 * $action.'?'.$field_id.'='.$value_id
 * /contr/del?id=2
 * @param string $action
 * @param string $method
 * @param string $attr_form
 * @param string $attr_button
 * @param string $confirm
 * @param string $text
 * @param array $param -- массив полей и значений формы для передачи в запросе
 * @return string
 */
function get_html_form_delete_confirm(
        string $action='',
        string $method = "post",
        string $attr_form = "class='d-inline'",
        string $attr_button = "class='btn btn-outline-warning btn-sm'",
        string $confirm = "Delete record?",
        string $text = "Delete",
        array  $param = []): string
{
    $str = "";
    foreach ($param as $name => $value) {
        $str .= "<input type='hidden' name='{$name}' value='{$value}'>";
    }
    return  "<form method='{$method}' action='{$action}' {$attr_form} onsubmit=\"return confirm('{$confirm}');\">"
                . $str
                . "<button type='submit' {$attr_button}>"
                    . "{$text}"
                . "</button>"
            . "</form>";
}