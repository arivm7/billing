#!/usr/bin/env php
<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : gen_lang_ctrl.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  License : GPL v3
 *
 *  Генератор языковых словарей для контроллеров
 */

require_once __DIR__ . '/gen_lang_lib.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: gen_lang_ctrl.php Controller.php\n");
    exit(1);
}

$input = $argv[1];
if (!is_file($input)) {
    fwrite(STDERR, "File not found: $input\n");
    exit(1);
}


$basename = basename($input, '.php');
$sourceCode = file_get_contents($input);

echo "Обработка файла: $input...\n";
echo "basename: $basename\n";


// --- Найдём все функции ---
$functions = extractFunctionsBalanced($sourceCode);


if (!$functions) {
    fwrite(STDERR, "Не найдено функций в $input\n");
    exit(1);
}

echo "найдено " . count($functions) . " функций в $input\n";
// echo print_r($functions, true) . "\n";
foreach ($functions as $f) {
    echo '    ' . $f['name'] . "\n";
}

$allClean = $sourceCode;

foreach ($functions as $f) {
    echo "Обработка функции: {$f['name']}...\n";

    // ['name'] => $name,
    // ['body'] => $body,
    // ['start'] => $posStart,
    // ['end'] => $i

    $funcName = $f['name'];
    $funcBody  = $f['body'];
    $context = "{$basename}::{$funcName}";

    // Извлекаем переводы
    $translations = extractTranslations($funcBody);
    if (!$translations) continue;

    // Генерируем файлы
    $outBase = GEN_LANGS . "/__{$basename}_{$funcName}";

    echo "funcName: $funcName\n";
    echo "context: $context\n";
    echo "outBase: $outBase\n";
    echo "translations: " . count($translations) . "\n";

    writeLang('en', $translations, "{$outBase}_en.php", $context);
    writeLang('ru', $translations, "{$outBase}_ru.php", $context);
    writeLang('uk', $translations, "{$outBase}_uk.php", $context);

    // Вносим изменения в общий clean
    $cleanBody = cleanSource($funcBody);
    $allClean = str_replace($funcBody, $cleanBody, $allClean);
    echo "ok.\n\n";

}

// Пишем общий clean-файл
writeClean($basename, $allClean);

exit(0);
// End of gen_lang_ctrl.php
