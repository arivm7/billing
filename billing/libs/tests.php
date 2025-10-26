<?php
/**
 *  Project : my.ri.net.ua
 *  File    : tests.php
 *  Path    : billing/libs/tests.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Oct 2025 20:30:57
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of tests.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\base\Lang;


require_once DIR_LIBS . '/lang_functions.php';

define('INPUT', 'str');
define('EXPECTED', 'translit');
define('RULE', 'comment');


function translit_uk_test(bool $verbose = false) {


    $TESTS = [
        // Исходная строка                  Ожидаемая транслитерация        Проверяемое правило
        [ INPUT => 'Київ',                  EXPECTED => 'Kyiv',              RULE => 'Й → Y в начале слова, і' ],
        [ INPUT => 'Згорани',               EXPECTED => 'Zghorany',          RULE => '“зг” → zgh' ],
        [ INPUT => 'Розгон',                EXPECTED => 'Rozghon',           RULE => '“зг” внутри слова' ],
        [ INPUT => 'Єнакієве',              EXPECTED => 'Yenakiieve',        RULE => 'Є в начале слова → Ye; Є в середине → ie' ],
        [ INPUT => 'Гаєвич',                EXPECTED => 'Haievych',          RULE => 'Є после согласной → ie' ],
        [ INPUT => 'Їжак',                  EXPECTED => 'Yizhak',            RULE => 'Ї в начале слова → Yi' ],
        [ INPUT => 'Марії',                 EXPECTED => 'Marii',             RULE => 'Ї в середине слова → i' ],
        [ INPUT => 'Йосип',                 EXPECTED => 'Yosyp',             RULE => 'Й в начале слова → Y' ],
        [ INPUT => 'Майя',                  EXPECTED => 'Maiia',             RULE => 'Й в середине слова → i' ],
        [ INPUT => 'Юлія',                  EXPECTED => 'Yuliia',            RULE => 'Ю в начале слова → Yu; я → ia' ],
        [ INPUT => 'Тетяна',                EXPECTED => 'Tetiana',           RULE => 'Я в середине слова → ia' ],
        [ INPUT => 'Ярема',                 EXPECTED => 'Yarema',            RULE => 'Я в начале слова → Ya' ],
        [ INPUT => 'Галина',                EXPECTED => 'Halyna',            RULE => 'Г → H' ],
        [ INPUT => 'Ирина',                 EXPECTED => 'Yryna',             RULE => 'И → Y' ],
        [ INPUT => 'Михайло',               EXPECTED => 'Mykhailo',          RULE => 'Х → Kh' ],
        [ INPUT => 'Чернігів',              EXPECTED => 'Chernihiv',         RULE => 'Ч → Ch' ],
        [ INPUT => 'Борщів',                EXPECTED => 'Borshchiv',         RULE => 'Щ → Shch' ],
        [ INPUT => 'Цимбалюк',              EXPECTED => 'Tsymbaliuk',        RULE => 'Ц → Ts, Ю → iu' ],
        [ INPUT => 'Апостроф’юк',           EXPECTED => 'Apostrofiuk',       RULE => 'апостроф (’) не передаётся, ю → iu' ],
        [ INPUT => 'Львів',                 EXPECTED => 'Lviv',              RULE => 'ь не передаётся' ],
        [ INPUT => 'П’ятниця',              EXPECTED => 'Piatnytsia',        RULE => 'апостроф не транслитерируется, я → ia' ],
        [ INPUT => 'Олександр',             EXPECTED => 'Oleksandr',         RULE => 'обычные буквы' ],
        [ INPUT => 'Село “Єдність”',        EXPECTED => 'Selo “Yednist”',    RULE => 'кавычки, спецсимволы не мешают начальной Є' ],
        [ INPUT => '(Юлія)',                EXPECTED => '(Yuliia)',          RULE => 'начальная буква после знаков' ],
        [ INPUT => '[Яків]',                EXPECTED => '[Yakiv]',           RULE => 'буква после спецсимвола' ],
        [ INPUT => '«Їжа»',                 EXPECTED => '«Yizha»',           RULE => 'буква после кавычек' ],
        [ INPUT => 'Роз’яснення',           EXPECTED => 'Roziasnennia',      RULE => 'апостроф, я → ia' ],
        [ INPUT => 'Кузьма-Скрябін',        EXPECTED => 'Kuzma-Skriabin',    RULE => 'дефис, мягкий знак, ь не транслитерируется' ],
        [ INPUT => 'щастя',                 EXPECTED => 'shchastia',         RULE => 'щ → shch, я → ia' ],
        [ INPUT => 'Любов',                 EXPECTED => 'Liubov',            RULE => 'ю → iu в середине слова' ],
        [ INPUT => 'Ольга',                 EXPECTED => 'Olha',              RULE => 'ль → l, г → h' ],
        [ INPUT => 'Софія',                 EXPECTED => 'Sofiia',            RULE => 'ї → i' ],
        [ INPUT => 'Ґалаґан',               EXPECTED => 'Galagan',           RULE => 'Ґ → G, г → h (оба регистра)' ],
        [ INPUT => 'Іван',                  EXPECTED => 'Ivan',              RULE => 'І → I, обычное слово' ],
        [ INPUT => 'Євген',                 EXPECTED => 'Yevhen',            RULE => 'Є в начале слова → Ye' ],
        [ INPUT => 'мова’',                 EXPECTED => 'mova',              RULE => 'апострофы везде удаляются' ],
        [ INPUT => "'слово'",               EXPECTED => "slovo",             RULE => 'ASCII апострофы удаояются' ],
        [ INPUT => 'ʼУкраїна',              EXPECTED => 'Ukraina',           RULE => 'U+02BC апостроф удаляется' ],
        [ INPUT => '’Україна',              EXPECTED => 'Ukraina',           RULE => 'U+2019 апостроф удаляется' ],
        [ INPUT => '— Єнот і Їжак.',        EXPECTED => '— Yenot i Yizhak.', RULE => 'пунктуация сохраняется, начальные Є/Ї → Ye/Yi' ],
        [ INPUT => '12345',                 EXPECTED => '12345',             RULE => 'Только цифры' ],
    ];

    foreach ($TESTS as $row) {
        $input = $row[INPUT];
        $output = translit_uk($input);
        $expected = $row[EXPECTED];
        $rule = $row[RULE];
        $format = "[%-25s] => [%-25s] | %s<br>\n";
        if ($output === $expected) {
            if ($verbose) { printf($format, $input, $output, '<font color=green>OK</font>'); };
        } else {
            printf($format, $input, $output, "<font color=red>FAIL</font> (expected [{$expected}] | rule: {$rule})");
        }
    }

}



function translit_ru_test(bool $verbose = false) {

    $TESTS = [
        // Исходная строка                  Ожидаемая транслитерация            Проверяемое правило
        [ INPUT => "'слово'",               EXPECTED => "slovo",                RULE => 'ASCII апострофы сохраняются' ],
        [ INPUT => "мёд",                   EXPECTED => 'med',                  RULE => 'ё внутри слова → e' ],
        [ INPUT => 'Ёжик',                  EXPECTED => 'Ezhik',                RULE => 'Ё в начале → E' ],
        [ INPUT => 'Ёлка',                  EXPECTED => 'Elka',                 RULE => 'Ё → E' ],
        [ INPUT => 'Александр',             EXPECTED => 'Aleksandr',            RULE => 'обычные буквы' ],
        [ INPUT => 'Борщ',                  EXPECTED => 'Borshch',              RULE => 'Щ → Shch' ],
        [ INPUT => 'Москва',                EXPECTED => 'Moskva',               RULE => 'обычные буквы' ],
        [ INPUT => 'Мёд',                   EXPECTED => 'Med',                  RULE => 'ё → e' ],
        [ INPUT => 'Никита',                EXPECTED => 'Nikita',               RULE => 'обычные буквы' ],
        [ INPUT => 'Павел',                 EXPECTED => 'Pavel',                RULE => 'обычные буквы' ],
        [ INPUT => 'Пётр',                  EXPECTED => 'Petr',                 RULE => 'ё → e, р → r, т → t' ],
        [ INPUT => 'Санкт-Петербург',       EXPECTED => 'Sankt-Peterburg',      RULE => 'дефис сохраняется, обычные буквы' ],
        [ INPUT => 'Сергей',                EXPECTED => 'Sergei',               RULE => 'й в конце слова → i' ],
        [ INPUT => 'Челябинск',             EXPECTED => 'Chelyabinsk',          RULE => 'Ч → Ch, Я → ya внутри слова' ],
        [ INPUT => 'Щастье',                EXPECTED => 'Shchastie',            RULE => 'Щ → Shch, е внутри → ie' ],
        [ INPUT => 'Щука',                  EXPECTED => 'Shchuka',              RULE => 'Щ → Shch' ],
        [ INPUT => 'Юлия',                  EXPECTED => 'Yuliya',               RULE => 'Ю в начале слова → Yu, я → ia внутри слова' ],
        [ INPUT => 'Юрий Иванов',           EXPECTED => 'Yurii Ivanov',         RULE => 'несколько слов, начало каждого слова' ],
        [ INPUT => 'Юрий',                  EXPECTED => 'Yurii',                RULE => 'Ю в начале слова → Yu, й в конце → i' ],
        [ INPUT => 'Ярослав',               EXPECTED => 'Yaroslav',             RULE => 'Я в начале слова → Ya' ],
        [ INPUT => 'москва',                EXPECTED => 'moskva',               RULE => 'малые буквы' ],
        [ INPUT => 'ёжик',                  EXPECTED => 'ezhik',                RULE => 'ё → e' ],
        [ INPUT => '— Юлия и Ярослав.',     EXPECTED => '— Yuliya i Yaroslav.', RULE => 'пунктуация сохраняется, начальные Ю/Я → Yu/Ya' ],
        [ INPUT => '12345',                 EXPECTED => '12345',                RULE => 'Только цифры' ],

    ];

    foreach ($TESTS as $row) {
        $input = $row[INPUT];
        $output = translit_ru($input);
        $expected = $row[EXPECTED];
        $rule = $row[RULE];
        $format = "[%-25s] => [%-25s] | %s<br>\n";
        if ($output === $expected) {
            if ($verbose) { printf($format, $input, $output, '<font color=green>OK</font>'); };
        } else {
            printf($format, $input, $output, "<font color=red>FAIL</font> (expected [{$expected}] | rule: {$rule})");
        }
    }

}


function detect_language_test(bool $verbose = false) {

    $TESTS = [
        [INPUT => 'Привет',                EXPECTED => Lang::C_UK,              RULE => 'Общие буквы' ],
        [INPUT => 'Щастя',                 EXPECTED => Lang::C_UK,              RULE => 'Слово с украинской буквой Щ' ],
        [INPUT => 'Марія',                 EXPECTED => Lang::C_UK,              RULE => 'Слово с українською літерою і' ],
        [INPUT => 'Пётр',                  EXPECTED => Lang::C_RU,              RULE => 'Буква ё → русский язык' ],
        [INPUT => 'Ґалаґан',               EXPECTED => Lang::C_UK,              RULE => 'Буквы Ґ → украинский' ],
        [INPUT => 'Hello',                 EXPECTED => Lang::C_UK,              RULE => 'Не определяем → по умолчанию украинский' ],
        [INPUT => '12345',                 EXPECTED => Lang::C_UK,              RULE => 'Только цифры → по умолчанию украинский' ],
        [INPUT => 'їжак',                  EXPECTED => Lang::C_UK,              RULE => 'Буква ї → украинский' ],
        [INPUT => 'Электрон',              EXPECTED => Lang::C_RU,              RULE => 'Буква Э → русский' ],
        [INPUT => 'Щастя — это хорошо',    EXPECTED => Lang::C_UK,              RULE => 'Щ в начале -- украинский' ],
        [INPUT => 'дуже щаслива людина',   EXPECTED => Lang::C_UK,              RULE => 'щ в начале -- украинский' ],

    ];

    foreach ($TESTS as $row) {
        $input = $row[INPUT];
        $expected = $row[EXPECTED];
        $output = detect_language($input);
        $rule = $row[RULE];
        $format = "[%-15s] => [%-5s] | %s<br>\n";
        if ($output === $expected) {
            if ($verbose) printf($format, $input, $output, '<font color=green>OK</font>');
        } else {
            printf($format, $input, $output, "<font color=red>FAIL</font> (expected [{$expected}] | rule: {$rule})");
        }
    }

}



function translit_test(bool $verbose = false) {

    $TESTS = [
        // Украинский
        [INPUT => 'Київ',            EXPECTED => 'Kyiv',         RULE => 'Украинский текст' ],
        [INPUT => 'Марія',           EXPECTED => 'Mariia',       RULE => 'Украинский текст с і' ],
        [INPUT => 'Ґалаґан',         EXPECTED => 'Galagan',      RULE => 'Украинский текст с Ґ' ],

        // Русский
        [INPUT => 'Ёжик',            EXPECTED => 'Ezhik',        RULE => 'Русский текст с Ё' ],

        // Смешанный / пунктуация
        [INPUT => 'Щастя — это хорошо', EXPECTED => 'Shchastia — eto khorosho', RULE => 'Смешанный текст' ],
        [INPUT => 'Привіт, Юля!',   EXPECTED => 'Pryvit, Yulia!', RULE => 'Смешанный текст с украинским и русским' ],
        [INPUT => '12345',           EXPECTED => '12345',       RULE => 'Только цифры' ],
    ];

    foreach ($TESTS as $row) {
        $input = $row[INPUT];
        $expected = $row[EXPECTED];
        $output = translit($input);
        $rule = $row[RULE];
        $format = "[%-30s] => [%-30s] | %s<br>\n";
        if ($output === $expected) {
            if ($verbose) printf($format, $input, $output, '<font color=green>OK</font>');
        } else {
            printf($format, $input, $output, "<font color=red>FAIL</font> (expected [{$expected}] | rule: {$rule})");
        }
    }
}