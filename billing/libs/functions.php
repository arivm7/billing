<?php
/*
 *  Project : my.ri.net.ua
 *  File    : functions.php
 *  Path    : billing/libs/functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 10:53:30
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */


use app\models\AbonModel;
use app\models\UserModel;
use billing\core\base\Lang;
use config\tables\Firm;
use config\tables\PA;
use config\Icons;
use config\tables\Perm;
use config\SessionFields;
use config\Sym;
use config\tables\User;
use config\tables\Abon;
use config\tables\AbonRest;
use billing\core\App;

require_once DIR_LIBS . '/compare_functions.php';
require_once DIR_LIBS . '/billing_functions.php';



$I_COLOR_STEP = 1;


const TABLE_ATTRIBUTES = "class='table table-striped table-hover table-bordered'";



// треугольник
// 🞀⏴◀◁◂◃◄
define('CH_TRIANGLE_LEFT',     "🞀");
define('CH_TRIANGLE_RIGT',     "🞂");
// 🞂🢒⊳⏵▶▷▸▹►⧐⮞⮞
define('CH_TRIANGLE',          "►");
// ▼▲▾▴
define('CH_TRIANGLE_UP',       "▲");
define('CH_TRIANGLE_DOWN',     "▼");



const ACCURACY     = 10000; // точность сравнения float значений
const LEN_DOG_NUM_MIN = 3; // Минимальное количество знаков в номере договора
const LEN_DOG_NUM_MAX = 9; // Максимальное количество знаков в номере договора



define('CHECK0', "<font size=-1 face=monospace color=gray>[&nbsp;]</font>");
define('CHECK1', "<font size=-1 face=monospace color=gray>[<font color=green>x</font>]</font>");

function get_html_CHECK(bool $has_check, string $title="", string $title_on="", string $title_off="", $check0 = CHECK0, $check1 = CHECK1): string {
    if (strlen($title_on) > 0)  { $c1 = "<font title='".$title_on."'>".$check1."</font>"; }  else { $c1 = $check1; }
    if (strlen($title_off) > 0) { $c0 = "<font title='".$title_off."'>".$check0."</font>"; } else { $c0 = $check0; }
    if ($has_check)             { $c  = $c1; }                                              else { $c  = $c0; }
    if (strlen($title) > 0)     { $s  = "<font title='".$title."'>{$c}</font>"; }           else { $s  = "{$c}"; }
    //$s = ((strlen($title) > 0) ? "<font title='".$title."'>" : "").($has_check?CHECK1:CHECK0).((strlen($title) > 0) ? "</font>" : "");
    return $s;
}



/**
 * Возвращает ассоциативный массив с полями, означающими права доступа
 * @param int $permission
 * @return array
 */
function get_permission_rec(int $permission): array {
    $rec = array(
        Perm::NONE_TITLE => ($permission  == Perm::NONE_VALUE) ? 1 : 0,
        Perm::VIEW_TITLE => ($permission  &  Perm::VIEW_VALUE) ? 1 : 0,
        Perm::EDIT_TITLE => ($permission  &  Perm::EDIT_VALUE) ? 1 : 0,
        Perm::ADD_TITLE  => ($permission  &  Perm::ADD_VALUE)  ? 1 : 0,
        Perm::DEL_TITLE  => ($permission  &  Perm::DEL_VALUE)  ? 1 : 0,
    );
    return $rec;
}



/**
 * Собирает раздельные логические права доступа в одно числовое значение
 * @param bool $view
 * @param bool $edit
 * @param bool $add
 * @param bool $del
 * @return int
 */
function get_permission_value(bool $view = false, bool $edit = false, bool $add = false, bool $del = false): int {
    return (int)((bool)$view * Perm::VIEW_VALUE) | (int)((bool)$edit * Perm::EDIT_VALUE) | (int)((bool)$add * Perm::ADD_VALUE) | (int)((bool)$del * Perm::DEL_VALUE);
}



function debug(mixed $value, string $comment = '', DebugView $debug_view = DebugView::PRINTR, int $die = 0): void
{
    echo "<b>$comment:</b>";
    echo "<pre>";
    if (is_null($value)) {
        echo "NULL";
    } else {
        switch ($debug_view) {
            case DebugView::ECHO:
                echo "$value";
                break;
            case DebugView::DUMP:
                var_dump($value);
                break;
            case DebugView::PRINTR:
            default:
                echo print_r($value, true);
                break;
        }
    }
    echo "</pre>";
    echo "<hr>\n";
    if ($die) die();
}



function debug_msg(string $text, string|null $color = null): void
{
    echo (is_null($color) ? "" : "<font color=$color>") . str_replace("\n", "<br>", $text) . (is_null($color) ? "<br>" : "</font>");
}



/**
 * Выравнивание таблицы и заполнение пропущенных ключей.
 * Делает из разномерной таблицы прямоугольную мо всеми полями
 * @param array $table
 * @return array
 */
function get_aligned_table(array|null $table): array|null {
    if (is_null($table) || count($table) <= 1) {
        return $table;
    }

    /*
     * Формируем вектор с ключами
     */
    $keys = array();
    foreach ($table as $row_key => $row) {
        $key_index = 0;
        foreach ($row as $col_key => $col) {
            if (!in_array($col_key, $keys)) {
                $keys = array_merge(array_slice($keys, 0, $key_index), array($col_key), array_slice($keys, $key_index));
            }
            $key_index++;
        }
    }

    /*
     * Перебираем строки таблицы и если нет поля соответствующего ключу ,то добавляем поле с таким ключом
     */
    $aligned = array();
    foreach ($table as $row_key => $row) {
        $full_row = array();
        foreach ($keys as $col_key) {
            $full_row[$col_key] = (array_key_exists($col_key, $row) ? $row[$col_key] : null );
        }
        $aligned[$row_key] = $full_row;
    }
    return $aligned;
}



/**
 * Возвращает строку, содержащую html-код таблицы,
 * в которой находится исндексный массив, состоящий из одинаковых ассоцитативных массивов
 * @param array   $t                    -- Таблица в виде ассоциативного массива
 * @param string  $table_attributes     -- html-тэги параметры самой таблицы
 * @param string  $caption              -- Заголовок таблицы, тэг <caption>Заголовок</caption>
 *                                         (фактически реализован в виде первой строки таблицы сведённой в одну широкую ячейку)
 * @param array   $col_titles           -- Заголовки столбцов таблицы
 * @param array   $cell_attributes      -- html-тэги параметры ячеек в соответсвующем столюце
 * @param array   $cell_format_valuues  -- Форматирование вывода значения ячейки
 * @param bool    $show_header          -- Если falce, то строка заголовков и колонки №пп и ID не выводятся, т.е. выводится только содержимое таблицы
 * @param bool    $show_key             -- Показывать колонку ключей строк (обычно это просто номера)
 * @param bool    $show_No = true       -- Показывать-ли порядковые номера строк таблицы
 * @param string  $obj_id               -- ID-имя объекта таблицы, для управления из скриптов
 * @param bool    $hidden               -- параметр hidden, т.е. таблица будет скрыта
 * @param bool    $bk_fill = true       -- Заливать ли цветом фон строк таблицы
 * @param string  $bk_color_title       -- цвет заливки строки $caption заголовка таблицы
 * @param string  $bk_color1            -- переключаемый цвет фона строки таблицы
 * @param string  $bk_color2            -- переключаемый цвет фона строки таблицы
 * @return string                       -- строка html-кода
 * @throws Exception
 */
function get_html_table(
        array|null  $t,
        bool        $pre_align = false,      // Предварительное выравнивание таблицы по ширине
        string      $table_attributes = TABLE_ATTRIBUTES,
        string|null $caption = null,
        array|null  $col_titles = null,
        bool        $child_col_titles = false,
        array|null  $cell_attributes = null,
        bool        $child_cell_attributes = false,
        array|null  $cell_format_valuues = null,
        bool        $show_header = true,
        bool        $show_key = false,
        bool        $show_No = false,
        string|null $obj_id = null,
        bool        $hidden = false,
        bool        $bk_fill = false,
        string|null $bk_color_title = null,
        string      $bk_color1 = COLOR1_VALUE,
        string      $bk_color2 = COLOR2_VALUE,
        string|null $anchor = null           // установка якоря для #anchor (<a name={$anchor}></a>)
        ): string {
    global $I_COLOR_STEP;
    if (is_null($t)) { $t = []; }
    if (!is_array($t)) { throw new Exception("get_html_table(array...); Первый аргумент должен быть массив"); }
    if (count($t) == 0) {
        /**
         * Массив пустой
         */
        $t = [(!is_null($caption)?"<nobr>{$caption}</nobr>":""), "Таблица пуста"];
        return get_html_table($t, show_header: 0, obj_id: $obj_id, hidden: $hidden);
    }
    if ($pre_align) { $t = get_aligned_table($t); }
    $table = "";
    $table .= "<table".($hidden?" hidden":"").(!is_null($obj_id)?" id=".$obj_id:"").(!is_null($table_attributes)?" ".$table_attributes:"").">";
    $table .= (!is_null($anchor)?"<a name={$anchor}></a>":"");
    $table .= (!is_null($caption)?"<tr ".($bk_fill ? " style='background-color:".(is_null($bk_color_title) ? (is_odd($I_COLOR_STEP++) ? $bk_color1 : $bk_color2) : $bk_color_title).";'" : "")." ><th colspan=".(count($t[array_key_first($t)])+1).">{$caption}</th></tr>" : "");

    /**
     * HEADER
     */
    if ($show_header) {
        $table .= "<thead>";
        $table .= "<tr".($bk_fill ? " bgcolor='".(is_null($bk_color_title) ? (is_odd($I_COLOR_STEP++) ? $bk_color1 : $bk_color2) : $bk_color_title)."'" : "").">";
        $table .= ($show_No  ?"<th><font color=gray>№<br>пп</font></th>" : "");
        $table .= ($show_key ?"<th>key</th>" : "");
        $col_index = 0;
        if (is_array($t[array_key_first($t)])) {
            foreach ($t[array_key_first($t)] as $cell_key => $cell_value) {
                if (is_null($cell_attributes) || !str_contains($cell_attributes[$col_index], "hidden")) {
                    $table .= "<th>";
                    $table .= (!is_null($col_titles)?(is_empty($col_titles[$col_index])?$cell_key:$col_titles[$col_index]):$cell_key);
                    $table .= "</th>";
                }
                $col_index++;
            }
        } else {
            //debug("(2)T: ", $t, "<hr>");
            $cell_key = array_key_first($t);
            if (is_null($cell_attributes) || !str_contains($cell_attributes[$col_index], "hidden")) {
                $table .= "<th>";
                $table .= (!is_null($col_titles)?(is_empty($col_titles[$col_index])?$cell_key:$col_titles[$col_index]):$cell_key);
                $table .= "</th>";
            }
            $col_index++;
        }
        $table .= "</tr>";
        $table .= "</thead>";
    }

    /**
     * BODY
     */
    $row_index = 0;
    foreach ($t as $row_key => $row_vector) {
        $row_index++;
        $table .= "<tbody>";
        $table .= "<tr".($bk_fill ? " bgcolor='".(is_odd($I_COLOR_STEP++) ? $bk_color1 : $bk_color2)."'" : "").">";
        if ($show_header) {
            $table .= ($show_No  ? "<th align=right><font color=gray>&nbsp;".($row_index).".</font></th>" : "");
            $table .= ($show_key ? "<th>".$row_key."</th>" : "");
        }
        $col_index = 0;
        if (is_array($row_vector)) {
            foreach ($row_vector as $cell_key => $cell_value) {
                $table .= "<td".($cell_attributes ? " {$cell_attributes[$col_index]}" : "").">";
                if (is_null($cell_attributes) || !str_contains($cell_attributes[$col_index], "hidden")) {
                    if (is_array($cell_value)) {
                        if (count($cell_value) > 0) {
                            $table .= get_html_table(
                                        t: $cell_value,
                                        pre_align: $pre_align,
                                        table_attributes: $table_attributes,
                                        col_titles: ($child_col_titles ?  $col_titles : null),
                                        child_col_titles: $child_col_titles,
                                        cell_attributes: ($child_cell_attributes ? $cell_attributes : null),
                                        child_cell_attributes: $child_cell_attributes,
                                        show_header: $show_header,
                                        show_key: $show_key,
                                        show_No: $show_No,
                                        bk_fill: $bk_fill,
                                        bk_color_title: $bk_color_title,
                                        bk_color1: $bk_color1,
                                        bk_color2: $bk_color2);
                        } else {
                            $table .= "Массив пуст";
                        }

                    } else {
                        $table .= ($cell_format_valuues ? sprintf($cell_format_valuues[$col_index], $cell_value) : $cell_value);
                    }
                    //echo "<pre><b>$i: </b>"; var_dump($t[$i]); echo "</pre>";
                }
                $table .= "</td>";
                $col_index++;
            }
        } else {
            $value = $row_vector;
            $table .= "<td".($cell_attributes ? " {$cell_attributes[$col_index]}" : "").">";
            if (is_null($cell_attributes) || !str_contains($cell_attributes[$col_index], "hidden")) {
                $table .= (is_null($cell_format_valuues) ? $value : sprintf($cell_format_valuues[$col_index], $value));
            }
            $table .= "</td>";
            $col_index++;

        }
        $table .= "</tr>";
    }
    $table .= "</tbody>";
    $table .= "</table>";
    return $table;
}



function is_odd($i) {
	return !((round($i/2, 0, PHP_ROUND_HALF_DOWN)*2) == $i);
}



function isAuth() {
//     /** @var \billing\core\base\Auth $AUTH */
    global $AUTH;
    return $AUTH->user_id != $AUTH::NO_AUTH;
}




/**
 * Проверяет пустая ли переменная
 * @param mixed $var
 * @return true/false
 *
 *  empty
 *  (PHP 4, PHP 5, PHP 7)
 *  empty — Проверяет, пуста ли переменная
 *  Описание ¶
 *  empty ( mixed $var ) : bool
 *
 *  Проверяет, считается ли переменная пустой. Переменная считается пустой, если она не существует или её значение равно FALSE. empty() не генерирует предупреждение, если переменная не существует.
 *  Список параметров ¶
 *
 *  var
 *      Проверяемая переменная
 *           Замечание:
 *           До PHP 5.5 empty() проверяет только переменные, и попытка проверить что-то еще вызовет ошибку синтаксиса. Другими словами, следующий код не будет работать: empty(trim($name)). Используйте вместо него trim($name) == false.
 *      Если переменная не существует, предупреждение не генерируется. Это значит, что empty() фактически является точным эквивалентом конструкции !isset($var) || $var == false
 *   Возвращаемые значения ¶
 *   Возвращает FALSE, если var существует и содержит непустое ненулевое значение. В противном случае возвращает TRUE.
 *   Следующие значения воспринимаются как пустые:
 *       "" (пустая строка)
 *      0 (целое число)
 *      0.0 (число с плавающей точкой)
 *      "0" (строка)
 *      NULL
 *      FALSE
 *      array() (пустой массив)
 *
 */
function is_empty_(mixed $var) {
    //echo "is_empty(): "; var_dump($var); echo "<br>";
    //echo "[".$var."]:".(!isset($var)?1:0)."|".(($var == false)?1:0)."|".(is_null($var)?1:0)."|".(($var == "")?1:0)."|".((strlen($var) === 0)?1:0)."|".(($var == 0)?1:0)."<br>";
    if(is_array($var)) {
        return !(count($var) > 0);
    } else {
        return ((is_null($var) || strlen($var) === 0) || ($var == "") || (!isset($var)) || ($var == false) || ($var === 0));
    }
    //return empty($var);
    //return !isset($var) || $var == false;
}



/**
 * Универсальная проверка "пустоты" переменной по заданным критериям.
 *
 * @param mixed $var            Проверяемая переменная
 * @param bool $checkArray      Считать пустым пустой массив []
 * @param bool $checkStr        Считать пустой пустую строку ""
 * @param bool $checkZero       Считать пустым число 0 и 0.0
 * @param bool $checkZeroStr    Считать пустой строку "0" (по умолчанию -- нет)
 * @param bool $checkNull       Считать пустым NULL
 * @param bool $checkFalse      Считать пустым строгое значение false
 *
 * @return bool true, если переменная соответствует хотя бы одному критерию "пустоты"
 */
function is_empty(
    mixed $var,
    bool $checkArray    = true,  // []
    bool $checkStr      = true,  // ""
    bool $checkZero     = true,  // 0 | 0.0
    bool $checkZeroStr  = false, // "0"
    bool $checkNull     = true,  // NULL
    bool $checkFalse    = true   // FALSE
): bool {
    // Проверка на пустой массив
    if ($checkArray && is_array($var) && count($var) === 0) {
        return true;
    }

    // Проверка на пустую строку
    if ($checkStr && is_string($var) && $var === '') {
        return true;
    }

    // Проверка на число 0
    if ($checkZero && is_int($var) && $var === 0) {
        return true;
    }

    // Проверка на число 0,0
    if ($checkZero && is_float($var) && $var === 0.0) {
        return true;
    }

    // Проверка на строку "0"
    if ($checkZeroStr && is_string($var) && $var === "0") {
        return true;
    }

    // Проверка на NULL
    if ($checkNull && is_null($var)) {
        return true;
    }

    // Проверка на строгое false
    if ($checkFalse && $var === false) {
        return true;
    }

    // Ничего из выбранного не подошло
    return false;

}




function a(
        string|null $href = null,
        string|null $attributes = null,
        string|null $target = "_self",
        string|null $alt = null,
        string|null $text = null,
        string|null $title = null,
        string|null $src = null,
        int|string|null $width = Icons::ICON_WIDTH_DEF,
        int|string|null $height = Icons::ICON_HEIGHT_DEF,
        string|null $color = null,
        string|null $style = null,
        string|null $id = null
        ): string {

    if (is_null($text) && is_null($src)) {
        throw new \Exception('Параметры text==null и src==null. Кто-то из них должен быть указан явно.');
    }
    return
            (is_null($href)
                ? ""
                : "<a href='{$href}'"
                  . ($attributes ? " {$attributes} " : "")
                  . (is_null($target) ? "" : " target='{$target}' ")
                  . ">"
            )
          . (is_null($color)
                ? ""
                : "<font color={$color}>"
            )
          . (is_null($src)
                ? ""
                : "<img src='{$src}' "
                    . (is_null($alt)     ? "" : "alt=\"{$alt}\"")." "
                    . (is_null($title)   ? "" : "title=\"{$title}\"") . " "
                    . (is_empty($width)  ? "" : "width={$width}") . " "
                    . (is_empty($height) ? "" : "height={$height}") . " "
                    . (is_empty($style)  ? "" : "style=\"{$style}\"") . " "
                    . (is_empty($id)     ? "" : "id=\"{$id}\"") . " "
                    . ">"
            )
          . (is_null($text)
                ? ""
                : "<span "
                    . (is_null($title)   ? "" : "title=\"{$title}\"") . " "
                    . (is_empty($style)  ? "" : "style=\"{$style}\"") . " "
                    . (is_empty($id)     ? "" : "id=\"{$id}\"") . " "
                    . ">"
                    . $text
                    . "</span>"
            )
          . (is_null($color)
                ? ""
                : "</font>"
            )
          . (is_null($href)
                ? ""
                : "</a>"
            );
}



/**
 * Возвращает html-строку с тэгом "<font face=$face color=$color title=$title size=$size>текст</font>" окрашивающим строку указанным цветом
 * @param string $s -- текст
 * @param string $color -- цвет
 * @param string $title -- содержимое тега title, если нужна всплывающая подсказка
 * @param bool $b -- жирное начертание <b>текст</b>
 * @param bool $u -- подчеркнутое начертание <u>текст</u>
 * @param string $face -- для задания гарнитуры шрифтов. serif — шрифты с засечками (антиквенные), типа Times; sans-serif — рубленные шрифты (шрифты без засечек или гротески), типичный представитель — Arial; cursive — курсивные шрифты; fantasy — декоративные шрифты; monospace — моноширинные шрифты, ширина каждого символа в таком семействе одинакова.
 * @return string
 */
function paint(
        string|null $s,
        string|null $color=null,
        string      $title="",
        string|null $size=null,
        bool        $b=false,
        bool        $u=false,
        string|null $face=null,
        bool        $span=false
        ): string {
    if (is_null($s)) { return ""; }
    return  ($span ? "<span>" : "")
            . "<font ".(is_null($face) ? "" : "face='{$face}'")." ".(!is_null($size) ? "size={$size}" : "")." ".(is_null($color) ? "" : "color='{$color}'")." ".(is_empty($title) ? "" : " title=\"".$title."\" ").">"
            . ($u ? "<u>" : "")
            . ($b ? "<b>" : "")
            . $s
            . ($b ? "</b>" : "")
            . ($u ? "</u>" : "")
            . "</font>"
            . ($span ? "</span>" : "");
}



/**
 * Возвращает индексированную копию массива:
 * все индексы массива равны записи ID соответсвующей строки
 * @param array $arr
 * @return array
 */
function indexing_arr(array $arr): array {
    $indexed = array();
    foreach ($arr as $row) {
        $indexed[$row['id']] = $row;
    }
    return $indexed;
}



/**
 * Пользовательская функция для сравнения при сортировке массива
 * (используется внутри get_tps_by_uid() )
 * нужно переделать get_tps_by_uid, сделать выборку запросом
 * @param $a
 * @param $b
 * @return int
 */
function compare_title($a, $b) {
    if ($a['title'] == $b['title']) {
        return 0;
    }
    return ($a['title'] < $b['title']) ? -1 : 1;
}



/**
 * Возвращает html-строку с кодом ссылки на телефонный вызов
 * @param string $phone_number
 * @return string
 */
function url_tel(string $phone_number): string {
    return "<a href='tel:$phone_number' rel=nofollow title='Позвонить по номеру $phone_number' target=_blank><img src=". Icons::SRC_ICON_PHONE." alt=CALL width=16 height=16></a>";
}

/**
 * Заменяет все номера телефонов в тексте на HTML-ссылки tel:
 *
 * @param string $text Текст, содержащий телефоны
 * @return string Текст с HTML-ссылками
 */
function url_tel_all(string $text): string {
    // Регекс телефонов в формате +XXXXXXXXXXXX
    $pattern = '/\+\d{12,14}/';

    // Заменяем каждый номер на ссылку
    return preg_replace_callback($pattern, function($matches) {
        $number = $matches[0];
        return '<a href="tel:' . h($number) . '">' . h($number) . '</a>';
    }, $text);
}


/**
 * Возвращает html-строку с кодом ссылки на программу СМС-отправки
 * @param string $phone_numbers
 * @return string
 */
function url_sms(string $phone_numbers): string {
    return "<a href='sms:$phone_numbers' rel=nofollow title='Отправить СМС номерам, указанным в списке.' target=_blank><img src=".Icons::SRC_ICON_SMS." alt=SMS width=16 height=16></a>";
}



function url_email(string $email, ?string $text = null, ?string $src = null, ?string $attributes = null): string {
    $subj  = rawurlencode(__('Rilan'));
    $body  = rawurlencode(
                  __('Здравствуйте')
                . '\n\n\n\n----\n'
                . __('С уважением,')
                . __('Rilan')
             );
    $cc    = '';
    $bcc   = '';
    $title = __('Написать письмо');
    return "<a href=\"mailto:{$email}?subject={$subj}&body={$body}&cc={$cc}&bcc={$bcc}\" title='{$title}' {$attributes}>"
            . ($src ? get_html_img(src: Icons::SRC_ICON_EMAIL) : "")
            . ($text ? h($text) : "")
            . "</a>";
}



/**
 * Возвращает URL текущего скрипта, включая протокол и домен.
 * Если $full_url == true, то вида 'https://my.site.com:443/page.php'
 * Если $full_url == false, то вида '/page.php'
 * @param bool $full_url
 * @return string
 */
function get_http_script(bool $full_url = true): string {

    return
    ($full_url
        ?   (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : REQUEST_SCHEME_DEFAULT)
            . "://"
            . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : SERVER_NAME_DEFAULT)
            . ":"
            . (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : SERVER_PORT_DEFAULT)
        :   ""
    )   . $_SERVER['SCRIPT_NAME'];
}





function get_html_img(
        string $src,
        string|null $href=null,
        string|null $alt=null,
        string|null $target="_self",
        string|null $title=null,
        int   |null $width=ICON_WIDTH_DEF,
        int   |null $height=ICON_HEIGHT_DEF,
        string|null $color=null,
        string|null $style=null,
        string|null $id=null
        ) {
    return
            (
                is_null($href)
                    ? ""
                    : "<a href='{$href}'"
                      . (is_null($target) ? "" : " target='{$target}' ")
                      . ">"
            )
          . (is_null($color) ? "":"<font color={$color}>")
          . "<img src='{$src}' "
                . (is_null($alt) ? "" : "alt='{$alt}'")." "
                . (is_null($title) ? "" : "title='{$title}'") . " "
                . (is_empty($width) ? "" : "width={$width}") . " "
                . (is_empty($height) ? "" : "height={$height}") . " "
                . (is_empty($style) ? "" : "style=\"{$style}\"") . " "
                . (is_empty($id) ? "" : "id=\"{$id}\"") . " "
                . ">"
          . (is_null($color) ? "":"</font>")
          . (is_null($href) ? "":"</a>");
}



function get_html_green_red(bool $flag, string $title_on = "ON", string $title_off = "OFF"): string {
    global $SRC_OK, $SRC_RED, $ICON_WIDTH_DEF, $ICON_HEIGHT_DEF;
    $on  = "<img src=$SRC_OK alt=ON width=$ICON_WIDTH_DEF height=$ICON_HEIGHT_DEF title='$title_on'>";
    $off = "<img src=$SRC_RED alt=OFF width=16 height=16 title='$title_off'>";
    return ($flag?$on:$off);
}



function get_html_check_img(
        bool $status,
        string $title       = "",
        string $title_true  = "",
        string $title_false = "",
        string $img_true    = Icons::SRC_OK,
        string $img_false   = Icons::SRC_WARN,
        string $alt         = "",
        string $alt_true    = "[Ok]",
        string $alt_false   = "[WARN]",
        string|null $style  = null,
        int|null $icon_width     = Icons::ICON_WIDTH_DEF,
        int|null $icon_height    = Icons::ICON_HEIGHT_DEF
        ): string {
    if ($title) {
        $title_true  = $title . $title_true;
        $title_false = $title . $title_false;
    }
    if ($alt) {
        $alt_true  = $alt . $alt_true;
        $alt_false = $alt . $alt_false;
    }
    if ($status) {
        return  get_html_img(
                        src:    $img_true,
                        alt:    $alt_true,
                        title:  $title_true,
                        width:  $icon_width, height:  $icon_height,
                        style:  $style,
                        color:  GREEN
                );
    } else {
        return  get_html_img(
                        src:    $img_false,
                        alt:    $alt_false,
                        title:  $title_false,
                        width:  $icon_width, height:  $icon_height,
                        style:  $style,
                        color:  RED
                );
    }
}



function last_octet_str(string $ip): string {
    return  num_len(value: ltrim(string: strrchr(haystack: $ip, needle: "."), characters: "."), length: 3);
}



function num_len($value, $length = 2) {
    $str = "".$value;
    while (strlen($str) < $length) {
        $str = "0".$str;
    }
    return $str;
}



/**
 * Удаляет из строки все символы не относящиеся к целому числу
 * @param string $str
 * @return string
 */
function clear_int(string $str): string {
    return preg_replace("/[^\d\-]/", "", $str);
}



/**
 * Удаляет из строки все символы не относящиеся к float числу
 * @param string $str
 * @return string
 */
function clear_float(string $str): string {
    return preg_replace("/[^\d\-\.\,]/", "", $str);
}


function var_dump_ret($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}



function redirect_to(string $host = URL_HOST, string $path = "/") {
//    echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=". $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."".$path."\">";
//    header('Location: ' . self::URL_REDIRECT . ''); //перенаправляем на главную страницу сайта }
    $redirect = "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0;URL=". $host . $path."\">";
//    echo htmlentities($redirect);
    echo $redirect;
    exit;
}



function redirect_(string|false $url = false) {
    if ($url) {
        $redirect = $url;
    } else {
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "/";
    }
    header("Location: {$redirect}");
    exit;
}



function redirect(string|false $url = false) {
    if ($url) {
        $redirect = $url;
    } else {
        // Получаем текущий URL и реферер
        $current_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        
        // Если реферер совпадает с текущим URL или реферер отсутствует,
        // перенаправляем на главную
        if (parse_url($referer, PHP_URL_PATH) === parse_url($current_url, PHP_URL_PATH)) {
            // debug("Redirect loop detected.");
            // debug( $current_url, "Current URL: ");
            // debug($referer, "Referer: ", die: 1);
            // $redirect = '/';
            $redirect = $referer;
        } else {
            $redirect = $referer;
        }
    }
    header("Location: {$redirect}");
    exit;
}



/**
 * Сохраняет HTML-сущности (&#10;, &nbsp;, &#x0A; и т.д.).
 * Сохраняет переводы строк.
 * Экранирует все спецсимволы, включая ' и ".
 * @param string|null $str
 * @return string
 */
function h(string|null $str): string {
    // return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    if ($str === null) {
        return '';
    }

    // Регулярка для всех HTML-сущностей
    $entityPattern = '/&(?:[a-zA-Z0-9]+|#[0-9]+|#x[0-9a-fA-F]+);/';

    // Массив для хранения сущностей
    $entities = [];

    // Заменяем сущности на маркеры __ENTITY0__, __ENTITY1__ и т.д.
    $str = preg_replace_callback($entityPattern, function($matches) use (&$entities) {
        $key = '__ENTITY' . count($entities) . '__';
        $entities[$key] = $matches[0];
        return $key;
    }, $str);

    // Экранируем все спецсимволы
    $str = htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Восстанавливаем сущности из массива
    $str = strtr($str, $entities);

    return $str;

}



function sort_array_by_field(array &$array, string $field, bool $asc = true): void {
    usort($array, function ($a, $b) use ($field, $asc) {
        return $asc
            ? ($a[$field] <=> $b[$field])
            : ($b[$field] <=> $a[$field]);
    });
}



function sort_objects_by_field(array &$array, string $field, bool $asc = true): void {
    usort($array, function ($a, $b) use ($field, $asc) {
        return $asc
            ? ($a->$field <=> $b->$field)
            : ($b->$field <=> $a->$field);
    });
}



/**
 * Сортировка с сохранинием ключей
 * @param array $array
 * @param string $field
 * @param bool $asc
 * @return void
 */
function sort_assoc_by_field(array &$array, string $field, bool $asc = true): void {
    uasort($array, function ($a, $b) use ($field, $asc) {
        $valA = $a[$field] ?? null;
        $valB = $b[$field] ?? null;

        return $asc
            ? ($valA <=> $valB)
            : ($valB <=> $valA);
    });
}



/**
 * Формирует URL текущей страницы без параметров, указанных в массиве исключений,
 * чтобы можно было использовать его, например, для генерации ссылок
 * пагинации (?page=2, ?page=3 и т.д.), не дублируя параметр page.
 * @return string
 */
function get_uri(array $excludes = []): string {
    if (!$excludes) {
        return $_SERVER['REQUEST_URI'];
    }
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
        foreach ($params as $param) {
            foreach ($excludes as $ex) {
                if (!preg_match("#{$ex}=#", $param)) {
                    $uri .= "{$param}&amp;";
                }
            }
        }
    }
    return $uri;
}



/**
 * Обертка возврата перевода для языкового модуля
 * @param string $key -- ключ для поиска и возврата значения из словаря
 * @param mixed $param -- вставка внутренних значений в строку
 * @param string|null $default -- значение "по умолчанию" если в словаре нет записи
 * @return string
 */
function  __(string $key, $param = null, string|null $default = null): string {
    return \billing\core\base\Lang::get(key: $key, param: $param, default: $default);
}



/**
 * Обёртка для получения текущего языка
 * @return string
 */
function __L(): string {
    return billing\core\base\Lang::code();
}



/**
 * Обёртка для get_price_apply_age();
 * @param array $pa
 * @return PAStatus
 */
function __pa_age(array $pa): PAStatus {
    return get_price_apply_age(price_apply: $pa);
}



/**
 * Возвращает поле $field
 * Из таблицы $table,
 * из строки с $id_field = $id_value,
 * @param string $table
 * @param string $id_field
 * @param int $id_value
 * @param string $field
 * @return string
 */
function __field(string $table, string $id_field, int $id_value, string $field): string {
    $model = new AbonModel();
    $row = $model->get_row_by_id(table_name: $table, field_id: $id_field, id_value: $id_value);
    return $row[$field];
}



/**
 * Возвращает указанное поле. По умолчанию -- короткое имя пользователя
 * Обёртка для использования в видах.
 * @param int|null $user_id
 * @param int|null $abon_id
 * @param string $field -- имя возвращаемого поля
 * @return string
 */
function __user(?int $user_id = null, ?int $abon_id = null, string $field = User::F_NAME_SHORT): string {
    $model = new AbonModel();
    if ($user_id) {
        return $model->get_user($user_id)[$field];
    }
    if ($abon_id) {
        return $model->get_user_by_abon_id($abon_id)[$field];
    }
    return '';
}



/**
 * Возвращает указанное поле из записи абонента. По умолчанию -- адрес.
 * Обертка для использования в видах.
 * @param int $abon_id
 * @param string $field
 * @return string
 */
function __abon(int|null $abon_id = null, string $field = Abon::F_ADDRESS): string {
    $model = new AbonModel();
    return $model->get_abon($abon_id)[$field];
}



const MSG_HAS_ERROR   = 1;
const MSG_HAS_SUCCESS = 2;

function msg_to_session(string|array|null $msg = null, int $status = MSG_HAS_ERROR): void {
    if (is_array($msg)) {
        $s  = "<ul>";
        foreach ($msg as $value) {
            $s .= "<li>{$value}</li>";
        }
        $s .= "</ul>";
    } elseif (is_string($msg)) {
        $s = $msg;
    } else {
        $s = "<pre>" . print_r($msg, true) ."</pre>";
    }
    switch ($status) {
        case MSG_HAS_ERROR:
            $_SESSION[SessionFields::ERROR] = $msg;
            break;
        case MSG_HAS_SUCCESS:
            $_SESSION[SessionFields::SUCCESS] = $msg;
            break;
        default:
            throw new Exception(__('Не известный статус сообщения') . ': [' . $status . ']');
         // break;
    }

}


// function get_html_pa_status_attr(PAStatus $status): string {

//     // bootstrap 5
//     // "<span class='badge rounded-pill text-bg-primary'>Primary</span>"
//     // "<span class='badge rounded-pill text-bg-secondary'>Secondary</span>"
//     // "<span class='badge rounded-pill text-bg-success'>Success</span>"
//     // "<span class='badge rounded-pill text-bg-danger'>Danger</span>"
//     // "<span class='badge rounded-pill text-bg-warning'>Warning</span>"
//     // "<span class='badge rounded-pill text-bg-info'>Info</span>"
//     // "<span class='badge rounded-pill text-bg-light'>Light</span>"
//     // "<span class='badge rounded-pill text-bg-dark'>Dark</span>"

//     switch ($status) {
//         case \PAStatus::FUTURE:
//             return "class='badge rounded-pill text-bg-secondary'";
//             //break;
//         case \PAStatus::CURRENT:
//             return "class='badge rounded-pill text-bg-success'";
//             //break;
//         case \PAStatus::PAUSE_TODAY:
//             return "class='badge rounded-pill text-bg-warning'";
//             //break;
//         case \PAStatus::PAUSE:
//             return "class='badge rounded-pill text-bg-secondary'";
//             //break;
//         case \PAStatus::CLOSED:
//             return "class='badge rounded-pill text-bg-secondary'";
//             //break;
//         default:
//             return "class='badge rounded-pill text-bg-danger'";
//             //break;
//     }
// }



// function get_html_pa_status_badge(PAStatus $status, ?array $messages = null): string
// {
//     if (!$messages) {
//         $messages = [
//             \PAStatus::FUTURE->name      => __('Будущий'),
//             \PAStatus::CURRENT->name     => __('Текущий'),
//             \PAStatus::PAUSE_TODAY->name => __('На паузе сегодня'),
//             \PAStatus::PAUSE->name      => __('Пауза'),
//             \PAStatus::CLOSED->name => __('Закрыт'),
//         ];
//     }
//     switch ($status) {
//         case \PAStatus::FUTURE:
//             return "<span ".get_html_pa_status_attr($status).">{$messages[PAStatus::FUTURE->name]}</span>";
//             //break;
//         case \PAStatus::CURRENT:
//             return "<span ".get_html_pa_status_attr($status).">{$messages[PAStatus::CURRENT->name]}</span>";
//             //break;
//         case \PAStatus::PAUSE_TODAY:
//             return "<span ".get_html_pa_status_attr($status).">{$messages[PAStatus::PAUSE_TODAY->name]}</span>";
//             //break;
//         case \PAStatus::PAUSE:
//             return "<span ".get_html_pa_status_attr($status).">{$messages[PAStatus::PAUSE->name]}</span>";
//             //break;
//         case \PAStatus::CLOSED:
//             return "<span ".get_html_pa_status_attr($status).">{$messages[PAStatus::CLOSED->name]}</span>";
//             //break;
//         default:
//             return "<span ".get_html_pa_status_attr($status).">ERROR</span>";
//             //break;
//     }
// }




/**
 * Возвращает статус для предупреждения абонента
 * в зависимости от оставшихся предоплаченных дней
 * @param array $rest -- Ассоциативный масив записи остатков и границ обслуживания абонента
 * @param array $abon -- Ассоциативный масив записи абонента
 * @return DutyWarn -- статус предупреждения абонента
 */
function get_abon_warn_status(array|null $rest, array $abon): DutyWarn {

    if (is_null($rest)) { return DutyWarn::NA; }

    if  (
            !isset($rest[AbonRest::F_PREPAYED]) || !isset($rest[AbonRest::F_SUM_PP30A]) || 
            !isset($rest[AbonRest::F_SUM_PP01A]) || !isset($rest[AbonRest::F_REST])
        ) 
    {
        update_rest_fields($rest);
    }

    switch (true) {
        case (is_null($rest[AbonRest::F_PREPAYED])):
            return DutyWarn::ON_PAUSE;
            // break;
        case ($rest[AbonRest::F_PREPAYED] > $abon[Abon::F_DUTY_MAX_WARN]):
            return DutyWarn::NORMAL;
            // break;
        case (($rest[AbonRest::F_PREPAYED] <= $abon[Abon::F_DUTY_MAX_WARN]) && ($rest[AbonRest::F_PREPAYED] > $abon[Abon::F_DUTY_MAX_OFF])):
            return DutyWarn::WARN;
            // break;
        case ($rest[AbonRest::F_PREPAYED] <= $abon[Abon::F_DUTY_MAX_OFF]):
            return DutyWarn::NEED_OFF;
            // break;
        default:
            return DutyWarn::NA;
            // break;
    }
}



function get_description_by_warn(DutyWarn $status): string {
    switch ($status) {
        case DutyWarn::NA:
            return __("Статус не понятен, этого не должно быть.");
            // break;
        case DutyWarn::ON_PAUSE:
            return __("Услуга на паузе.");
            // break;
        case DutyWarn::NORMAL:
            return __("Оплата есть. Услуга подключена.");
            // break;
        case DutyWarn::WARN:
            return __("Требуется оплата. %s Услуга подключена", CR);
            // break;
        case DutyWarn::NEED_OFF:
            return __("Оплаты давно нет, нужно отключать. %s Услуга подключена", CR);
            // break;
        case DutyWarn::INFO:
            return __("INFO. %s Услуга подключена", CR);
            // break;
        default:
            return "";
            // break;
    }
}



/**
 * Размещение строк слева и справа в контейнере в одной строке
 * @param string|null $left
 * @param string|null $right
 * @param string $attributes
 * @return string
 */
function get_html_content_left_right_(string|null $left, string|null $right, string $attributes = ''): string {
    return  "<div ".($attributes ?: "")." style='display: flex; justify-content: space-between; align-items: center;'>"
                . "<div>{$left}</div>"
                . "<div>{$right}</div>"
            . "</div>";
}



/**
 * Размещение строк слева и справа в контейнере в одной строке (Bootstrap-версия)
 * @param string|null $left   Содержимое слева
 * @param string|null $right  Содержимое справа
 * @param string      $add_class  Дополнительные CSS-классы
 * @param string      $attributes  Дополнительные HTML-атрибуты (id, data-...)
 * @return string
 */
function get_html_content_left_right(
    string|null $left,
    string|null $right,
    string $add_class = '',
    string $attributes = ''
): string {
    $classes = trim("d-flex justify-content-between align-items-center $add_class");
    return  "<div class='$classes' $attributes>"
                . "<div>{$left}</div>"
                . "<div>{$right}</div>"
            . "</div>";
}



function get_firm_status_str(array $firm, ?string $ref = null): string {
    if (!$ref) {
        $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
    }

    $ret = "<span face=monospace>";
    $ret .= implode('&nbsp;|&nbsp;', get_firm_status($firm, $ref));
    $ret .= "</span>";
    return $ret;
}



function get_firm_status(array $firm, ?string $ref = null): array {

    if (!$ref) {
        $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
    }

    $statuses = [
        Firm::F_HAS_DELETE => [
            0 => [
                'char'  => Sym::CH_OFF,
                'color' => 'green',
                'title' => "Сейчас статус &laquo;Работает&raquo;\nНажать для отключения.",
            ],
            1 => [
                'char'  => Sym::CH_OFF,
                'color' => 'gray',
                'title' => 'Сейчас статус &laquo;Удалено&raquo;. &#10Предприятие не используется, &#10кроме ранее выписанных документов&#10Нажать для &laquo;Включения&raquo;.',
            ],
        ],

        Firm::F_HAS_ACTIVE => [
            0 => [
                'char'  => Sym::CH_PAUSE,
                'color' => 'orange',
                'title' => "Сейчас статус &laquo;Остановлено&raquo;. &#10В новых платежах и документах не используется. &#10Нажать для &laquo;Активации&raquo;.",
            ],
            1 => [
                'char'  => Sym::CH_ACTIVE,
                'color' => 'green',
                'title' => 'Сейчас статус &laquo;Активно&raquo;. &#10Используется в платежах и документообороте. &#10Нажать для &laquo;Паузы&raquo;.',
            ],
        ],

        Firm::F_HAS_AGENT => [
            0 => [
                'char'  => Sym::CH_AGENT,
                'color' => 'gray',
                'title' => 'Сейчас статус &laquo;не представитель провайдера&raquo;, &#10другое, стороннее предприятие. &#10Нажать для включения статуса &laquo;Агент&raquo;.',
            ],
            1 => [
                'char'  => Sym::CH_AGENT,
                'color' => 'green',
                'title' => 'Провайдер / Агент &#10Сейчас статус &laquo;Агент&raquo;. &#10Предприятие-Агент, провайдер или представитель провайдера. &#10Нажать для отключения статуса.',
            ],
        ],

        Firm::F_HAS_CLIENT => [
            0 => [
                'char'  => Sym::CH_UAH,
                'color' => 'gray',
                'title' => "Сейчас статус &laquo;Не Клиент&raquo;. \nНажать для включения статуса &laquo;Клиент&raquo;, того, кто пользуется услугами.",
            ],
            1 => [
                'char'  => Sym::CH_UAH,
                'color' => 'green',
                'title' => "Клиент / Контрагент \nСейчас статус &laquo;Клиент&raquo; (абонент). \nНажать для отключения статуса.",
            ],
        ],

        Firm::F_HAS_ALL_VISIBLE => [
            0 => [
                'char'  => Sym::CH_INVISIBLE,
                'color' => 'gray',
                'title' => "Отображается в &laquo;Списке предприятий&raquo; только у подключенных пользователей. \nНажать для отображения в списках для всех.",
            ],
            1 => [
                'char'  => Sym::CH_VISIBLE,
                'color' => 'green',
                'title' => "Видимый ВСЕМ \nОтображается в &laquo;Списке предприятий&raquo; у всех. \nНажать для отображения в списках только для подключенных пользователей.",
            ],
        ],

        Firm::F_HAS_ALL_LINKING => [
            0 => [
                'char'  => Sym::CH_LINK,
                'color' => 'gray',
                'title' => "Разрешено подклюячение только \nот уже подключенного пользователя \nпо ручному указанию номера UID подключаемого пользователя.",
            ],
            1 => [
                'char'  => Sym::CH_LINK,
                'color' => 'green',
                'title' => "Подключаемый ВСЕМ \nРазрешено подклюячение ко всем пользователям.",
            ],
        ],
    ];



    $arr = [];
    foreach ($statuses as $flag => $value) {
        $arr[$flag] =   "<a href=" . Firm::URI_STATUS . '?'
                        . http_build_query([
                            'id'     => $firm[Firm::F_ID],
                            'field'  => $flag,
                            'status' => (int)$firm[$flag],
                            'ref'    => rawurlencode($ref)]) . ' '
                        . "title='" . $value[(int)$firm[$flag]]['title'] . "'"
                        . "><span color={$value[(int)$firm[$flag]]['color']}>{$value[(int)$firm[$flag]]['char']}</span></a>";
    }
    return $arr;
}



function cleaner_html($dirty_html): string {
    require_once DIR_VENDOR . '/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('Cache.SerializerPath', DIR_TEMP . '/htmlpurifier');
    $purifier = new \HTMLPurifier($config);
    return $purifier->purify($dirty_html);
}



/**
 *
 * @param string|array $msg
 * @param bool|null $show_key -- null -- автоматический выбор (сейчас если ключ не число, то выводить)
 * @return string
 */
function parce_msg(string|array $msg, bool|null $show_key = null): string {
    if (is_array($msg)) {
        $str = '<ul>';
        foreach ($msg as $key => $row) {
            $str .= "<li>";
            $str .= (is_array($row)
                        ? parce_msg($row)
                        : (is_null($show_key)
                                ? (is_int($key) ? "" : $key.' : ') . $row
                                : ($show_key ? $key.' : ' : '') . $row
                          )
                    );
            $str .= "</li>";
        }
        $str .= '</ul>';
    } else {
        $str = $msg;
    }
    return $str;
}



/**
 * Возвращает размер в байтах из размеров k, kb, m, mb, g, gb
 * @param string $val
 * @return int
 */
function return_bytes(string $val): int
{
    if(empty($val))return 0;

    $val = trim($val);

    preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);

    $last = '';
    if(isset($matches[2])){
        $last = $matches[2];
    }

    if(isset($matches[1])){
        $val = (int) $matches[1];
    }

    switch (strtolower($last))
    {
        case 'g':
        case 'gb':
            $val *= 1024;
        case 'm':
        case 'mb':
            $val *= 1024;
        case 'k':
        case 'kb':
            $val *= 1024;
    }

    return (int) $val;
}



/**
 * Проверяет разрешение для одного или нескольких модулей
 * @param int|array|null $module -- список ID модулей
 * @param int $perm -- проверяемое разрешение
 * @return bool
 */
function can_perm(int|array|null $module, int $perm): bool {
    if (is_array($module)) {
        $can = false;
        foreach ($module as $module_one) { $can = $can | can_perm($module_one, $perm); }
        return $can;
    } else {
        return (bool) ((App::$app->permissions[$module] ?? 0) & $perm);
    }
}



/**
 * Можно ли видеть данный модуль
 * @param int $module
 * @return bool
 */
function can_view(int|array|null $module): bool {
    return can_perm($module, Perm::VIEW_VALUE);
}



/**
 * Можно ли редактировать данные в указанном модуле
 * @param int $module
 * @return bool
 */
function can_edit(int|array|null $module): bool {
    return can_perm($module, Perm::EDIT_VALUE);
}



/**
 * Можно ли добавлять данные в указанном модуле
 * @param int $module
 * @return bool
 */
function can_add(int|array|null $module): bool {
    return can_perm($module, Perm::ADD_VALUE);
}



/**
 * Можно ли удалять данные в указанном модуле
 * @param int $module
 * @return bool
 */
function can_del(int|array|null $module): bool {
    return can_perm($module, Perm::DEL_VALUE);
}



/**
 * Возвращает true если есть хоть какое-то разрешение на использование этого модуля
 * @param int $module
 * @return bool
 */
function can_use(int|array|null $module): bool {
    return can_perm($module, Perm::ALL_VALUE);
}



/**
 * Проверяет, входит ли IPv4/IPv6 в диапазон (CIDR или одиночный IP)
 *
 * @param string $ip   Проверяемый IP
 * @param string $cidr Диапазон (например, "192.168.0.0/24" или "8.8.8.8")
 * @return bool
 */
function ip_in_range(string $ip, string $cidr): bool {
    // если маски нет → сравниваем напрямую
    if (strpos($cidr, '/') === false) {
        return $ip === $cidr;
    }

    list($subnet, $mask) = explode('/', $cidr, 2);

    /*
     * переводит IP в бинарное представление (packed in_addr):
     * IPv4 → 4 байта, IPv6 → 16 байт.
     * Если IP некорректный → вернёт false.
     */
    $ip_bin     = inet_pton($ip);
    $subnet_bin = inet_pton($subnet);
    /*
     * Проверяем, что оба адреса корректные
     */
    if ($ip_bin === false || $subnet_bin === false) {
        return false; // некорректный IP
    }

    $mask = (int)$mask;
    $len  = strlen($ip_bin); // 4 байта IPv4 или 16 байт IPv6

    /*
     * $bytes – количество полных байт, которые входят в маску,
     * $bits – сколько оставшихся бит нужно проверить в следующем байте.
     * Например:
     *     /24 → 3 полных байта (24/8=3), остаток 0.
     *     /21 → 2 полных байта (21/8=2), остаток 5 бит.
     */
    $bytes = intdiv($mask, 8);
    $bits  = $mask % 8;

    /*
     * 👉 Сравниваем первые $bytes байт IP и подсети.
     *  Если они не совпадают → IP не входит в диапазон.
     *  Пример:
     *  IP 192.168.1.10 (C0 A8 01 0A)
     *  Подсеть 192.168.2.0/16 (C0 A8 02 00)
     *  Первые 2 байта (C0 A8) совпадают, значит проверку /16 проходят.
     */
    if ($bytes > 0 && substr($ip_bin, 0, $bytes) !== substr($subnet_bin, 0, $bytes)) {
        return false;
    }

    /*
     * 👉Если маска не кратна 8 (например, /21, /13, /25):
     * Создаём маску для одного байта ($maskByte).
     * Побитово сравниваем остаток бит в этом байте.
     * Если не совпадает → IP не входит в диапазон.
     */
    if ($bits > 0) {
        $maskByte = chr(((0xFF00 >> $bits) & 0xFF));
        if (($ip_bin[$bytes] & $maskByte) !== ($subnet_bin[$bytes] & $maskByte)) {
            return false;
        }
    }

    return true;
}



define("SIGN_MINUS", -1);
define("SIGN_NUL",    0);
define("SIGN_PLUS",  +1);

/**
 * Возвращает знак числа
 * @param int|float $value
 * @return int -1 или 0 или 1 (SIGN_MINUS, SIGN_NUL, SIGN_PLUS)
 */
function sign(int|float $value): int {
    return (($value < 0)
            ? SIGN_MINUS
            : (($value > 0)
                ? SIGN_PLUS
                : SIGN_NUL
              )
           );
}



function get_this_by_sign($value, $minus="red", $nul="gray", $plus="green") {

    switch (sign($value)) {
        case SIGN_MINUS:
            return $minus;
            //break;
        case SIGN_PLUS:
            return $plus;
            //break;
        case SIGN_NUL:
        default:
            return $nul;
            //break;
    }
}



function validate_ip(string|null $ip): bool {
    if (empty($ip)) {
        return false;
    } else {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
}



function validate_mac(string|null $mac) {
    if (is_empty($mac)) {
        return false;
    } else {
        return filter_var($mac, FILTER_VALIDATE_MAC);
    }
}



function is_ip_net(string|null $ip_net) {
    if (is_empty($ip_net)) {
        return false;
    } else {
        $pos_slash = strpos($ip_net, "/");
        $pos_minus = strpos($ip_net, "-");
        if ($pos_slash === false && $pos_minus === false) {
            return false;
        } else {
            if ($pos_slash > 0) {
                $rec = explode("/", $ip_net);
                if (count($rec) == 2) {
                    if (!is_numeric($rec[1])) { return false; }
                    if ((intval($rec[1]) < 0) || (intval($rec[1]) > 255)) { return false; }
                    return filter_var($rec[0], FILTER_VALIDATE_IP);
                } else {
                    return false;
                }
            } else {
                $rec = explode("-", $ip_net);
                if (count($rec) > 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}



/*
 * Возвращае true если запись
 * НЕ запрещена (['disabled'] != "true") и
 * НЕ заблокирована (['blocked'] != "true")
 */
function has_enabled_rec($rec) {
    return  !isset($rec['disabled']) ||
            (
                ($rec['disabled'] != "true") && (isset($rec['blocked'])
                    ? ($rec['blocked'] != "true")
                    : true)
            );
}



function get_str_cut(string|null $text, int $max_length=20, string $encoding="UTF-8") {
    if (is_empty($text)) {
        return "";
    }

    if (mb_strlen($text) == strlen($text)) {
        $text = mb_convert_encoding($text, $encoding, "cp1251, KOI8-R, UTF-8");
    }

    $text_dec = html_entity_decode($text);
    if(iconv_strlen($text_dec, $encoding)>$max_length) {
        $text_cut  = htmlentities(mb_substr($text_dec, 0, ($max_length-1), $encoding));
        return paint($text_cut . paint(CH_TRIANGLE, BLUE), face: 'monospace', title: $text);
    } else {
        return paint($text, face: 'monospace');
    }
}



/**
 * Обёртка для функции AppBaseModel->url_abon_form()
 * Возвращает текстовую строку-ссылку на страницу абонента (пользователя
 * @param int $abon_id
 * @return string -- Строка с html-кодом
 */
function url_abon_form(int $abon_id): string {
    // !!! Убрать обращение к базе
    $model = new AbonModel();
    return $model->url_abon_form($abon_id);
}


/**
 * Обёртка для функции AppBaseModel->url_user_form()
 * Возвращает текстовую строку-ссылку на страницу пользователя
 * @param int $user_id
 * @return string -- Строка с html-кодом
 */
function url_user_form(int $user_id): string {
    // !!! Убрать обращение к базе
    $model = new AbonModel();
    return $model->url_user_form($user_id);
}



/**
 * Обёртка для функции AppBaseModel->url_tp_form()
 * Возвращает текстовую строку-ссылку на форму редактирования ТП
 * @param int|null $tp_id
 * @param array|null $tp
 * @param bool $has_img
 * @param int $icon_width
 * @param int $icon_height
 * @return string
 */
function url_tp_form(int|null $tp_id = null, array|null $tp = null, bool $has_img = false, int $icon_width = ICON_WIDTH_DEF, int $icon_height = ICON_HEIGHT_DEF): string {
    // !!! Убрать обращение к базе
    $model = new UserModel();
    return $model->url_tp_form($tp_id, $tp, $has_img, $icon_width, $icon_height);
}



function url_pa_form($pa_id, int $icon_width = Icons::ICON_WIDTH_DEF, int $icon_height = Icons::ICON_HEIGHT_DEF): string {
    // !!! Вызов кэшируется. Но всё равно нужно как-то обойтись без вызова базы.
    $model = new AbonModel();
    $pa = $model->get_row_by_id(PA::TABLE, $pa_id);
    return "<a href=/pa_form.php?pa_id=".$pa_id." target=_blank title='Редактировать прикрепленный прайсовый фрагмент \n[{$pa_id}] {$pa['net_name']}'><img src='".Icons::SRC_PA_EDIT."' width=$icon_width height=$icon_height /></a>";
}


function url_pa_form_22($pa_id): string {
    return url_pa_form(pa_id: $pa_id, icon_width: 22, icon_height: 22);
}



function price_frm(int $price_id, bool $has_img = true, int $icon_width = 22, int $icon_height = 22, string $target = "_self"): string {
    // !!! Убрать обращение к базе
    $model = new AbonModel();
    $price = $model->get_price($price_id);
    return "<a href='/price_form.php?id={$price_id}' title='Редактировать прайс \n[".$price_id."] ".$price['title']."\n{$price['description']}' target={$target}>".($has_img?"<img src=/img/price_edit.png alt='[edit]' width=$icon_width height=$icon_height>":$price['title'])."</a>";
}



function isPhone(string $value): bool {
    // оставляем только цифры и плюс
    return preg_match('/^\+?\d{5,15}$/', preg_replace('/[^\d\+]/', '', $value));
}



function isUsername(string $value): bool {
    // начинается с буквы, длина 5-32, допустимы буквы, цифры и _
    return preg_match('/^[a-zA-Z][\w_]{4,31}$/', $value);
}



function isTelegram(string $value): bool {
    /*
     * Поддерживает:
     *  - http://t.me/username или https://t.me/username
     *  - @username
     *  - email: user@example.com
     *  - телефон: +380501234567, 380501234567, 0501234567
     * Username: 5–32 символа, буквы/цифры/подчеркивание, регистронезависимо
     */
    $value = trim($value);

    // Telegram web-ссылка
    if (preg_match('#^https?://(www\.)?t\.me/[\w_]{5,32}$#i', $value)) {
        return true;
    }

    // Telegram username с @
    if (preg_match('#^@[\w_]{5,32}$#i', $value)) {
        return true;
    }

    // Email
    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return true;
    }

    // Номер телефона (допускаем +, пробелы, дефисы и скобки)
    if (preg_match('#^\+?\d[\d\s\-\(\)]{7,20}$#', $value)) {
        return true;
    }

    return false;
}



function isJabberFull(string $value): bool {
    /**
     * Jabber (XMPP) обычно логин имеет вид: user@domain
     * Иногда с портом или ресурсом: user@domain/resource
     * проверяем: user@domain/resource
     *
     * [a-z]{2,} — доменная зона минимум 2 символа
     * i — регистр не важен
     * Допускаются буквы, цифры, _, -, . в имени и домене
     * Ресурс (/resource) опционален
     *  — ресурс может содержать буквы, цифры, -, ., _
     */
    return preg_match('/^[\w\.\-]+@[\w\.\-]+\.[a-z]{2,}(\/[\w\-\.]+)?$/i', $value);
}

/*
 * <a href="tel:+74955555555">+7(495) 555-55-55</a>
 * <a href="mailto:mail@example.com">Пример ссылки на емайл</a>
 * 
 * Как сделать ссылку на Telegram
 * <a href="https://t.me/agvento1">Написать автору</a>
 * <a href="tg://resolve?domain=agvento-test">Написать автору</a>
 * 
 * Как сделать ссылку на Viber на сайте
 * 
 * 1. Открыть чат с номером
 * на номер пользователя Viber. Вместо плюса используется «%2B»:
 * <a href="viber://chat?number=%2B4957777777">Ссылка на чат Viber</a>
 * 
 * 2. Добавить контакт (работает только c телефонов):
 * <a href="viber://add?number=4957777777">Добавить контакт в Viber</a>
 * 
 * 3. Поделиться текстом (до 200 символов), открывается список контактов:
 * <a href="viber://forward?text=Привет!">Поделиться текстом в Viber</a>
 * 
 * 4. Открыть вкладку «Чаты»:
 * <a href="viber://chats">Открыть Чаты в Viber</a>
 * 
 * 5. В мобильном приложении открыть вкладку вызовов
 * <a href="viber://calls">Открыть Вызовы в Viber</a>
 * 
 * Как правильно сделать ссылку на WhatsApp
 *
 * 1. Открыть чат с номером
 * <a href="https://wa.me/4957777777">Чат с пользователем WhatsApp</a>
 * 
 * 2. Открыть чат с номером и отправить сообщение:
 * <a href="https://wa.me/4957777777?text=Привет!">Чат+сообщение WhatsApp</a>
 * 
 * 3. Поделиться текстом
 * <a href="whatsapp://send?text=Привет!">Поделиться текстом WhatsApp</a>
 * 
 * Ссылка на VK
 * 
 * Прямая ссылка на диалог с пользователем вконтакте:
 * <a href="vk.me/agvento">Написать в VK</a>
 * 
 * Ссылка на мессенджер Facebook
 * 
 * Прямая ссылка на диалог с пользователем Facebook:
 * <a href="https://www.messenger.com/t/jack.malbon.3">Facebook Messenger</a>
 *
 */



/**
 * В каждом ППП есть строка в которой через ',' перечисляются поддерживаемые АПИ.
 * Эта функция проверяет наличие указанно АПИ в этой строке
 * и возвращает true / false
 * @param array $ppp   -- запись ППП
 * @param string $api  -- искомый АПИ
 * @return boolean
 */
function is_supported_api(array $ppp, string $api): bool {
    $supported_api_list = explode(",", $ppp['api_type']);
    $supported = false;
    foreach ($supported_api_list as $supported_api) {
        if (trim($supported_api) == $api) {
            $supported = true;
            break;
        }
    }
    return $supported;
}



/**
 * В одномерном ассоциативном массиве в указанном поле меняет значение указанного поля с помощью функции $func($value)
 * @param array $row
 * @param string|int|bool $field
 * @param callable $func
 */
function replace_field_on_row(array &$row, string|int|bool $field, callable $func) {
    foreach ($row as $key => &$value) {
        if ($key == $field && !is_null($value)) {
            $value = $func($value);
        }
    }
}



/**
 * В двумерном массиве в каждой строке в указанном поле меняет значение указанного поля с помощью функции $func($value)
 * @param array $table -- изменяемый двумерный массив
 * @param string|int|bool $field -- изменяемое поле
 * @param callable $func -- высываемая функция возвращающая новый результат для поля
 */
function replace_field_on_table(array &$table, string|int|bool $field, callable $func) {
    foreach ($table as &$row) {
        replace_field_on_row($row, $field, $func);
    }
}



function highlight_like_groups(string $text, string $likePattern): string {
    $parts = array_filter(explode('%', $likePattern));
    if (!$parts) return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // сортируем по длине по возрастанию
    usort($parts, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

    $textHtml = $text; // = h($text);

    foreach ($parts as $part) {
        if ($part === '') continue;
        $partEsc = preg_quote($part, '/');

        // регистронезависимая замена всех вхождений
        $textHtml = preg_replace_callback(
            "/($partEsc)/iu",
            fn($m) => '<mark>' . $m[1] . '</mark>',
            $textHtml
        );
    }

    return $textHtml;
}



function detect_invoker(): string {
    // 1) очевидный веб
    if (php_sapi_name() !== 'cli' && PHP_SAPI !== 'cli') {
        return 'web';
    }

    // теперь мы в CLI (включая cron)
    // 2) если есть типичный веб-серверный context — веб:
    if (!empty($_SERVER['REMOTE_ADDR']) || !empty($_SERVER['REQUEST_METHOD']) || !empty($_SERVER['HTTP_USER_AGENT'])) {
        return 'web';
    }

    // 3) попробуем понять — интерактивный CLI (ssh/tty) или фоновый (cron)
    $isTty = false;
    // posix_isatty доступен не всегда
    if (function_exists('posix_isatty')) {
        $isTty = posix_isatty(STDIN);
    } else {
        // fallback: есть переменные окружения типа TERM или SSH_ — обычно значит интерактив
        $isTty = (getenv('TERM') !== false) || (getenv('SSH_TTY') !== false) || (getenv('SSH_CONNECTION') !== false);
    }

    if ($isTty) {
        return 'cli-interactive';
    }

    // 4) ещё попытка: посмотреть родительский процесс (Linux /proc)
    if (function_exists('posix_getppid')) {
        $ppid = posix_getppid();
        $procCmd = @file_get_contents("/proc/{$ppid}/cmdline");
        if ($procCmd !== false) {
            $procCmd = str_replace("\0", ' ', $procCmd);
            $procCmd = strtolower($procCmd);
            // типичные имена демонов/планировщиков
            if (strpos($procCmd, 'cron') !== false || strpos($procCmd, 'crond') !== false) {
                return 'cron';
            }
            if (strpos($procCmd, 'systemd') !== false && strpos($procCmd, 'cron') !== false) {
                return 'cron';
            }
            // parent could be bash called by cron; if parent cmdline contains -c and no tty vars - likely cron
            if (strpos($procCmd, '-c') !== false && !getenv('SSH_CONNECTION')) {
                return 'cron-like';
            }
        }
    }

    // 5) эвристики по окружению: у cron часто нет интерактивных переменных (SHELL, TERM), но и это не строго
    $hasInteractiveEnv = (getenv('SHELL') !== false) || (getenv('TERM') !== false) || (getenv('USER') !== false && getenv('HOME') !== false && getenv('LOGNAME') !== false);
    if (!$hasInteractiveEnv) {
        return 'cron';
    }

    // 6) последнее — CLI, но не очевидно — пометим как cli-background
    return 'cli-background';
}



/**
 * Возвращает высоту, в количествах строк, для редактора коментариев <textarea rows>
 * @param string $text
 * @param int $count_rows_min
 * @param int $count_rows_max
 * @return int
 */
function get_count_rows_for_textarea(string $text, int $count_rows_min=0, int $count_rows_max=0, ): int {
    if ($count_rows_min < 1) { $count_rows_min = App::get_config('textarea_rows_min'); }
    if ($count_rows_max < 1) { $count_rows_max = App::get_config('textarea_rows_max'); }
    $count_lines_cr = (empty($text) ? 0 : substr_count($text, "\n") + 1);
    $count_lines_approximate = round_up(mb_strlen($text) / App::get_config('textarea_approximate_chars_per_line'));
    $count_lines = ($count_lines_cr > $count_lines_approximate ? $count_lines_cr : $count_lines_approximate);
    return
        (($count_lines <= $count_rows_min)
            ?   $count_rows_min
            :   (($count_lines > $count_rows_max)
                    ? $count_rows_max
                    : $count_lines
                )
        );    
}



/**
 * Генерирует URL-кодированную строку запроса из предоставленного ассоциативного (или индексированного) массива. 
 * Возвращает только фрагмент с указанием переменных запроса, т.е. строку между '?' и '#'
 * @param array $params
 * @param string|null $url
 * @return string
 */
function make_get_params(array $params, string|null $url = null): string {
    if ($url === null) { $url = $_SERVER['REQUEST_URI']; }

    $query = parse_url($url, PHP_URL_QUERY);
    if ($query) {
        parse_str($query, $query_params);
    } else {
        $query_params = [];
    }

    foreach ($params as $key => $value) {
        $query_params[$key] = $value;
    }
    $uri = http_build_query($query_params);

    return $uri;
}



/**
 * Определяет, что передано: префикс (/24) или маска (255.255.255.0)
 * @param string $ip
 * @return IpType
 */
function detect_ip_mask_type(string $ip): IpType {
    $ip = trim($ip);

    // Удалим ведущий слэш, если он есть
    $value = ltrim($ip, '/');

    // Проверяем — это число от 0 до 32 (префикс)
    if (ctype_digit($value) && (int)$value >= 0 && (int)$value <= 32) {
        return IpType::PREFIX;
    }

    // Проверяем — это IPv4-маска
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return IpType::MASK;
    }

    // Ничего не подошло
    return IpType::NA;
}



/**
 * Преобразовывает ip маску в ip префикс
 * 255.255.255.0 -> 24
 * @param string $mask
 * @return string
 */
function ip_mask_to_prefix(string $mask): string|false {
    if (detect_ip_mask_type($mask) == IpType::PREFIX) { return $mask; }
    // Преобразуем в бинарную строку и считаем количество единиц
    $long = ip2long($mask);
    if ($long === false) {
        // throw new InvalidArgumentException("Некорректная маска: $mask");
        return false;
    }
    $binary = decbin($long);
    return strval(substr_count($binary, '1'));
}



/**
 * Из префикса ([/]24) → в маску (255.255.255.0)
 * @param string $prefix
 * @return string
 */
function ip_prefix_to_mask(string $prefix): string|false {
    if (detect_ip_mask_type($prefix) == IpType::MASK) { return $prefix; }
    $int_prefix = intval(ltrim($prefix, '/'));
    if ($int_prefix < 0 || $int_prefix > 32) {
        // throw new InvalidArgumentException("Некорректный префикс: /$prefix");
        return false;
    }
    $mask = (0xFFFFFFFF << (32 - $int_prefix)) & 0xFFFFFFFF;
    return long2ip($mask);
}



/**
 * Возвращает последний октет IPv4-адреса
 *
 * @param string $ip IPv4-адрес (например "192.168.1.45")
 * @return string|false Последний октет или false, если IP некорректный
 */
function ip_get_last_octet(string $ip): string|false {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }

    $parts = explode('.', $ip);
    return end($parts);
}



/**
 * Возвращает только изменённые данные из новой записи
 * Если различия есть, то обязательно добавляет ID поле
 * @param array $new    -- новый массив
 * @param array $prev   -- исходный массив
 * @param string $field_id -- ID поле, если указано, то обязательно для передачив результирующий массив
 * @return array
 */
function get_diff_fields(array $new, array $prev, string|null $field_id = 'id'): array {

    $differences = [];

    /**
     * Добавляем только изменившиеся поля
     */
    foreach ($new as $key => $value) {
        
        if  (
                array_key_exists($key, $prev) &&
                $prev[$key] != $value
            ) 
        {
            $differences[$key] = $value;
        }
    }

    /**
     * Если есть изменённые данные и ID поле указано,
     * то добавить ID поле
     */
    if ($differences) {
        /**
         * Ключ если указан, то обязателен
         */
        if ($field_id) {
            $differences[$field_id] = $new[$field_id];
        }
        /**
         * Возвращаем изменённые данные
         */
        return $differences;
    } else {
        /**
         * Изменений нет
         */
        return [];
    }
}



function untemplate(string $text, array $templates = []): string {
    foreach ($templates as $key => $value) {
        $text = str_replace($key, $value, $text);
    }
    return $text;
}


function validate_date_str(string $date): bool {
    $ts = strtotime($date);
    $isValid = $ts !== false;
    return $isValid;
}


function validate_timestamp(int|string $ts): bool {
    return ctype_digit((string)$ts) && $ts >= 0;
}



function round_up(float $value, int $rounder = 1): int {
    return ceil($value / $rounder) * $rounder; // округление до $rounder вверх;
}

function first_line_attr(string $text, string $attribute, string $line_separator = '<br>'): string {
    // Разбиваем текст на строки
    $lines = explode($line_separator, $text);
    // Оборачиваем первую строку в <span>
    $lines[0] = "<span {$attribute}>" . h($lines[0]) . "</span>";
    // Склеиваем обратно, заменяя переходы строки на <br>
    $result = implode($line_separator, $lines);
    return $result;
}


function get_mfo_from_iban($iban_str) {
    return substr(str_replace(" ", "", $iban_str), 4, 6);
}

function get_rec_firm_from_user(array $user): array {
    $firm[Firm::F_NAME_SHORT]          = $user[User::F_NAME_SHORT];     // Краткое название
    $firm[Firm::F_NAME_LONG]           = $user[User::F_NAME_FULL];      // Полное название
    $firm[Firm::F_NAME_TITLE]          = $user[User::F_NAME_SHORT];     // Название сети для рассылки
    $firm[Firm::F_MANAGER_JOB_TITLE]   = '';                            // Должность ответственного
    $firm[Firm::F_MANAGER_NAME_SHORT]  = $user[User::F_NAME_SHORT];     // ФИО
    $firm[Firm::F_MANAGER_NAME_LONG]   = $user[User::F_NAME_FULL];      // Фамилия Имя Отчество
    return $firm;
}



$month_ua_n = array("Січень", "Лютий",  "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад",  "Грудень");
$month_ua_r = array("Січня",  "Лютого", "Березня",  "Квітня",  "Травня",  "Червня",  "Липня",  "Серпня",  "Вересня",  "Жовтня",  "Листопада", "Грудня");



/**
 * Возвращает сумму прописью
 * @author runcore
 * @uses morph(...)
 */
function num2str($num) {
    $nul='ноль';
    $ten=array(
        array('', 'одна', 'два', 'три', 'чотири', 'п\'ять', 'шість', 'сім', 'вісім', 'дев\'ять'),
        array('', 'одна', 'дві', 'три', 'чотири', 'п\'ять', 'шість', 'сім', 'вісім', 'дев\'ять'),
    );
    $a20=array('десять','одинадцять','дванадцять','тринадцять','чотирнадцять' ,'п\'ятнадцять','шістнадцять','сімнадцять','вісімнадцять','дев\'ятнадцять');
    $tens=array(2=>'двадцять','тридцять','сорок','п\'ятьдесят','шістьдесят','сімдесят' ,'вісімдесят','дев\'яносто');
    $hundred=array('','сто','двісті','триста','чотириста','п\'ятьсот','шістьсот', 'сімсот','вісімсот','дев\'ятьсот');
    $unit=array( // Units
        array('копійка', 'копійки',  'копійок',	  1),
        //array('рубль', 'рубля',    'рублей',    0),
        array('гривня',  'гривні',   'гривень',   0),
        array('тисяча',  'тисячі',   'тисяч',     1),
        array('мільйон', 'мільйона', 'мільйонів', 0),
        array('мільярд', 'міліарда', 'мільярдів', 0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) {$out[]= $tens[$i2].' '.$ten[$gender][$i3];} # 20-99
            else {$out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3];} # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) {$out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);}
        } //foreach
    }
    else {$out[] = $nul;}
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

function num2str_rus($num) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('копейка' ,'копейки' ,'копеек',	 1),
        //array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('гривня'   ,'гривни'   ,'гривень'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) { continue; }
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) { $out[]= $tens[$i2].' '.$ten[$gender][$i3]; } # 20-99
            else { $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; } # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) { $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]); }
        } //foreach
    }
    else { $out[] = $nul; }
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}


/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) { return $f5; }
    $n = $n % 10;
    if ($n>1 && $n<5) { return $f2; }
    if ($n==1) { return $f1; }
    return $f5;
}



const FIELD_TYPE_DEF    = -1;
const FIELD_TYPE_INT    =  1;
const FIELD_TYPE_STRING =  2;
const FIELD_TYPE_BOOL   =  3;
const FIELD_TYPE_DATE   =  4;

/**
 * Сортирует индексный массив, элементы которого ассоциативные массивы.
 * Сортировка проводится по указанному полю
 * Поддерживаются сортировки дат, булевых значений
 * и обычное сравнение оператором сравнения для чисел, строк и прочего содержимого.
 * @param array $array -- ссылка на сортируемый массив
 * @param string $compare_field -- имя поля по которому проводится сортировка
 * @param int $field_type -- тип содержимого сортируемого поля FIELD_TYPE_DEF, FIELD_TYPE_INT, FIELD_TYPE_STRING, FIELD_TYPE_BOOL, FIELD_TYPE_DATE.
 * @param bool $sort_asc -- сортировка по возрастанию/убыванию
 * @return true
 */
function sort_array_by_field_(array &$array, string $compare_field, int $field_type = FIELD_TYPE_DEF, bool $sort_asc = true):bool {
    for ($i = 0; $i < count($array)-1; $i++) {
        for ($x = $i+1; $x < count($array); $x++) {
            switch ($field_type) {
                case FIELD_TYPE_DATE:
                    if((strtotime($array[$i][$compare_field]) > strtotime($array[$x][$compare_field])) === $sort_asc) {
                        $tmp = $array[$i];
                        $array[$i] = $array[$x];
                        $array[$x] = $tmp;
                    }
                    break;

                case FIELD_TYPE_BOOL:
                    if((($array[$i][$compare_field]?1:0) > ($array[$x][$compare_field]?1:0)) === $sort_asc) {
                        $tmp = $array[$i];
                        $array[$i] = $array[$x];
                        $array[$x] = $tmp;
                    }
                    break;

                case FIELD_TYPE_INT:
                case FIELD_TYPE_STRING:
                case FIELD_TYPE_DEF:
                default:
                    if(($array[$i][$compare_field] > $array[$x][$compare_field]) === $sort_asc) {
                        $tmp = $array[$i];
                        $array[$i] = $array[$x];
                        $array[$x] = $tmp;
                    }
                    break;
            }
        }
    }
    return true;
}



/**
 * Извлекает числовую часть строки, включая дробную часть.
 * @param string $str -- строка для извлечения числа
 * @param int $max_float_part -- максимальное количество знаков после запятой
 * @return float|null -- извлечённое число или null, если не удалось извлечь
 */
function get_numeric_part(string $str, int $max_float_part = LEN_DOG_NUM_MAX): float|null {
    $subject = $str;
    $pattern = "/^\s*\-?\d+(\s\d{".LEN_DOG_NUM_MIN."})*([.,]\d{1,$max_float_part})?/";
    $matches = array();
    if(preg_match($pattern, $subject, $matches) === false) {
        return null;
    } else {
        if(count($matches)>0) {
            return floatval(str_replace(",", ".", str_replace(" ", "", $matches[0])));
        } else {
            return null;
        }
    }
}




/**
 * Функция проверяет, содержится ли в строке $haystack хотя бы одна подстрока из:
 * массива строк (array $needle), или
 * одной строки (string $needle)
 * и возвращает true или false.
 * Проще: «Есть ли в строке $haystack хотя бы одно совпадение?»
 * @param string $haystack
 * @param array|string $needle
 * @param bool $case_sensitive
 * @return bool
 */
function str_contains_array(string $haystack, array|string $needle, bool $case_sensitive=false): bool {
    if (is_array($needle)) {
        /**
         * Если $needle — массив:
         * перебирается каждый элемент;
         * если хотя бы один элемент найден в $haystack, функция возвращает true;
         * если ни один не найден — false.
         */
        if ($case_sensitive) { $haystack = mb_strtoupper($haystack); }
        foreach ($needle as $need) {
            if ($case_sensitive) { $need = mb_strtoupper($need); }
            if( !(stripos($haystack, $need) === false) ) {
                return true;
            }
        }
        return false;
    } else {
        /**
         * Если $needle — строка:
         * выполняется проверка наличия подстроки;
         * возвращается true или false.
         */
        if ($case_sensitive) { $needle = mb_strtoupper($needle); }
        if(stripos($haystack, $needle) === false) {
            return false;
        } else {
            return true;
        }
    }
}



/**
 * Поиск записи в массиве по значению указанного поля
 * @param array $rows -- массив строк (каждая строка — ассоциативный массив)
 * @param string $field -- имя поля для поиска
 * @param mixed $value -- искомое значение
 * @param callable|null $cmp -- функция сравнения (по умолчанию ===)
 * @return array|null -- найденная строка или null, если не найдена
 */
function find_row_by_field_value(
    array $rows,
    string $field,
    mixed $value,
    callable|null $cmp = null
): array|null {
    $cmp ??= fn($a, $b) => $a === $b;

    foreach ($rows as $row) {
        if (isset($row[$field]) && $cmp($row[$field], $value)) {
            return $row;
        }
    }
    return null;
}



/**
 * Заменяет любые запрещённые для имён файлов символы. 
 * Сохраняет расширение, если оно есть.
 * @param string $filename
 * @param string $replace -- строка для замены запрещённых символов (по умолчанию '_')
 * @param bool $cyrillic -- разрешать ли кириллицу (по умолчанию нет, т.е. разрешены только латинские буквы, цифры, дефис, точка и подчёркивание)
 * @return array|string|null
 */
function sanitize_filename(string $filename, string $replace = '_', bool $cyrillic = false): string {
    // Убираем всё, что не буквы, цифры, точка, дефис или подчёркивание
    if ($cyrillic) {
        return preg_replace('/[^a-zA-Z0-9\-\._а-яА-ЯёЁ]/u', $replace, $filename);
    } else {
        return preg_replace('/[^a-zA-Z0-9\-\._]/', $replace, $filename);
    }
}

/**
 * Формирует URL с GET-параметрами
 *
 * @param string $baseUrl Базовый URL (например, https://my.ri.net.ua/api/cmd)
 * @param array $params Ассоциативный массив параметров GET
 * @return string Полный URL с корректной кодировкой параметров
 */
function build_url(string $baseUrl, array $params = []): string {
    if (empty($params)) { return $baseUrl; }
    // Разделитель ? или & если уже есть параметры в $baseUrl
    $separator = strpos($baseUrl, '?') === false ? '?' : '&';
    return $baseUrl . $separator . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

