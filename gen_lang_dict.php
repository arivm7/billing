#!/usr/bin/env php
<?php
/**
 *  Project : my.ri.net.ua
 *  File    : gen_lang_dict.php
 *  Path    : gen_lang_dict.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 18:30:48
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



/**
 * Генератор словарей.
 *
 * Сперва вам нужно полностью сформировать файл, для которого нужно будет сформировать переводы.
 * В этом файле все фрагменты, нуждающиеся в переводе нужно обернуть в функцию-обёртку __('').
 * Внутри вызова этой функции нужно вписать сразху все три языковых хначения:
 * __('english | русккий | украинский')
 *
 * Данный скрипт разбирает эти конструкции и формирует языковые файлы и формирует файл с ключами вместо мультиязычной строки.
 * В качестве ключа берётся английский вариант.
 *
 * генерирует 4 файла:
 * Один исправленный php-файл с ключами
 * и три языковых файла вида:
 *
 *       <?php
 *       // en              <- язык языкового файла
 *       // for Layouts     <- Вид или другой файл для котрого этот перевод
 *       return [
 *           'ключ' => 'Языковой перевод',
 *           ...
 *       ];
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


require_once __DIR__ . '/gen_lang_lib.php';


echo "generate language dictionaries\n";

if ($argc < 2) {
    fwrite(STDERR, "Usage: gen_lang.php input.php\n");
    exit(1);
}

$inputFileName = $argv[1];
if (!is_file($inputFileName)) {
    fwrite(STDERR, "File not found: $inputFileName\n");
    exit(1);
}



$lines = file($inputFileName, FILE_IGNORE_NEW_LINES);
$basename = basename($inputFileName, '.php');

// Определяем имя вида: ищем в первых строках
$helperStr = $inputFileName;



// Выходные файлы
$baseNameDict = preg_replace('/View$/', '', $basename);

$outClean = GEN_LANGS . "/__{$basename}_clean.php";
$outEn    = GEN_LANGS . "/__{$baseNameDict}_en.php";
$outRu    = GEN_LANGS . "/__{$baseNameDict}_ru.php";
$outUk    = GEN_LANGS . "/__{$baseNameDict}_uk.php";

echo "outClean : $outClean\n";
echo "outEn    : $outEn\n";
echo "outRu    : $outRu\n";
echo "outUk    : $outUk\n";


$code = file_get_contents($inputFileName);

$translations = [];
$clean = $code;

// Регулярка для поиска __() с первым аргументом в кавычках
$regex = '/__\(\s*(["\'])(.*?)\1(\s*,[^)]*)?\)/s';
if (preg_match_all($regex, $code, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $m) {
//        debug($matches, '$matches');
        $full   = $m[0];       // вся конструкция
        $inside = $m[2];       // содержимое первых кавычек
        $tail   = $m[3] ?? ''; // запятая + остальные аргументы

        // Делим по НЕэкранированному |
        $parts = preg_split('/(?<!\\\\)\|/', $inside);
        if (count($parts) !== 3) {
            fwrite(STDERR, "Error: invalid translation string: $full\n");
            exit(1);
        }

        $en = trim(stripslashes($parts[0]));
        $ru = trim(stripslashes($parts[1]));
        $uk = trim(stripslashes($parts[2]));

        $translations[$en] = ['en' => $en, 'ru' => $ru, 'uk' => $uk];

        // В чистом файле оставляем только ключ + хвост
        $repl = "__('".addslashes($en)."'$tail)";
        $clean = str_replace($full, $repl, $clean);
    }
}


// Пишем clean-файл
file_put_contents($outClean, $clean);


// Пишем языковые файлы
writeLang('en', $translations, $outEn, $helperStr);
writeLang('ru', $translations, $outRu, $helperStr);
writeLang('uk', $translations, $outUk, $helperStr);

echo "Cleaned source written to: $outClean\n";
