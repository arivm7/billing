<?php
/**
 *  Project : my.ri.net.ua
 *  File    : lang_functions.php
 *  Path    : billing/libs/lang_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Oct 2025 20:30:48
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of lang_functions.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\base\Lang;


function translit_uk(string $text): string
{
    $first_pos_map = [
        'Є' => 'Ye', 'Ї' => 'Yi', 'Й' => 'Y', 'Ю' => 'Yu', 'Я' => 'Ya',
        'є' => 'ye', 'ї' => 'yi', 'й' => 'y', 'ю' => 'yu', 'я' => 'ya',
    ];

    $main_map = [
        'Зг' => 'Zgh', 'зг' => 'zgh',

        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'H', 'Ґ' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Є' => 'ie', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'Y',
        'І' => 'I', 'Ї' => 'i', 'Й' => 'i', 'К' => 'K', 'Л' => 'L',
        'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh',
        'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
        'Ю' => 'iu', 'Я' => 'ia', 'Ь' => '',

        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd',
        'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z', 'и' => 'y',
        'і' => 'i', 'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh',
        'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ю' => 'iu', 'я' => 'ia', 'ь' => '',

        // пассивная поддержка русских букв
        'Ё'=>'E','ё'=>'e','Э'=>'E','э'=>'e','Ы'=>'Y','ы'=>'y','Ъ'=>'','ъ'=>'',
    ];

    // апострофы удаляем
    $apostrophes = ["’", "ʼ", "'"];
    $text = str_replace($apostrophes, '', $text);

    $result = '';
    $is_word_start = true;
    $chars = mb_str_split($text);
    $i = 0;
    $len = count($chars);

    while ($i < $len) {
        // проверяем двухсимвольные сочетания Зг / зг
        if ($i + 1 < $len && isset($main_map[$chars[$i] . $chars[$i + 1]])) {
            $result .= $main_map[$chars[$i] . $chars[$i + 1]];
            $i += 2;
            $is_word_start = false;
            continue;
        }

        $char = $chars[$i];

        if (preg_match('/\p{L}/u', $char)) {
            if ($is_word_start && isset($first_pos_map[$char])) {
                $result .= $first_pos_map[$char];
            } else {
                $result .= $main_map[$char] ?? $char;
            }
            $is_word_start = false;
        } else {
            $result .= $char;
            $is_word_start = true;
        }

        $i++;
    }

    return $result;
}



/**
 * Русский транслит
 * 
 * @param string $text
 * @return string
 */
function translit_ru(string $text): string {

    $first_pos_map = [
        'Ю'=>'Yu','Я'=>'Ya','Ё'=>'E','Й'=>'I',
        'ю'=>'yu','я'=>'ia','ё'=>'e','й'=>'i',
    ];

    $main_map = [
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E','Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'I',
        'К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F',
        'Х'=>'Kh','Ц'=>'Ts','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch','Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'i',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        // пассивная поддержка украинских букв
        'Ґ'=>'G','ґ'=>'g','Є'=>'E','є'=>'e','І'=>'I','і'=>'i','Ї'=>'I','ї'=>'i',
    ];

    // апострофы удаляем всегда
    $apostrophes = ["’","ʼ","'"];
    $text = str_replace($apostrophes, '', $text);

    $result = '';
    $is_word_start = true;
    $chars = mb_str_split($text);
    $prev_char = '';

    foreach ($chars as $i => $char) {
        if (preg_match('/\p{L}/u', $char)) {

            // правило й в конце слова → i
            $is_last_in_word = true;
            for ($j=$i+1; $j<count($chars); $j++) {
                if (preg_match('/\p{L}/u', $chars[$j])) { $is_last_in_word = false; break; }
            }
            if (($char === 'Й' || $char === 'й') && $is_last_in_word) {
                $result .= 'i';
                $is_word_start = false;
                $prev_char = $char;
                continue;
            }

            // е после мягкого знака или ё внутри слова → ie / io
            if (!$is_word_start && $prev_char === 'Ь' || $prev_char === 'ь') {
                if ($char === 'Е') { $result .= 'Ie'; $prev_char=$char; $is_word_start=false; continue; }
                if ($char === 'е') { $result .= 'ie'; $prev_char=$char; $is_word_start=false; continue; }
            }

            // буква в начале слова
            if ($is_word_start && isset($first_pos_map[$char])) {
                $result .= $first_pos_map[$char];
            } else {
                $result .= $main_map[$char] ?? $char;
            }

            $is_word_start = false;
        } else {
            $result .= $char;
            $is_word_start = true;
        }
        $prev_char = $char;
    }

    return $result;
}



function detect_language(string $text): string {
    // Заменяем все символы кроме букв и цифр на пробел
    $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text); // оставляем по одному пробелу
    $text = trim($text);

    // Проверка на украинские буквы
    if (preg_match('/[ґҐєЄіІїЇ]/u', $text)) {
        return Lang::C_UK;
    }

    // Разбиваем на слова и проверяем первую букву
    $words = explode(' ', $text);
    foreach ($words as $word) {
        if (mb_substr($word, 0, 1) === 'Щ' || mb_substr($word, 0, 1) === 'щ') {
            return Lang::C_UK;
        }
    }

    // Проверка на русские буквы
    if (preg_match('/[ёыэЁЫЭ]/u', $text)) {
        return Lang::C_RU;
    }

    // По умолчанию — украинский
    return Lang::C_UK;
}




function translit(string $text): string {
    $lang = detect_language($text);

    switch ($lang) {
        case Lang::C_RU:
            return translit_ru($text);
        case Lang::C_UK:
        default:
            return translit_uk($text);
    }
}



