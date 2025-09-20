#!/usr/bin/env php
<?php

echo "generate language dictionaries\n";

if ($argc < 2) {
    fwrite(STDERR, "Usage: gen_lang.php input.php\n");
    exit(1);
}

$input = $argv[1];
if (!is_file($input)) {
    fwrite(STDERR, "File not found: $input\n");
    exit(1);
}

enum _DebugView: string
{
    case ECHO = '1';
    case PRINTR = '2';
    case DUMP = '3';
}

function _debug(mixed $value, string $comment = '', _DebugView $debug_view = _DebugView::DUMP, int $die = 0): void
{
    echo "<b>$comment:</b><pre>";
    if (is_null($value)) {
        echo "NULL";
    } else {
        switch ($debug_view) {
            case _DebugView::ECHO:
                echo "$value";
                break;
            case _DebugView::DUMP:
                var_dump($value);
                break;
            case _DebugView::PRINTR:
            default:
                echo print_r($value, true);
                break;
        }
    }
    echo "</pre>";
    echo "<hr>";
    if ($die) die();
}



$lines = file($input, FILE_IGNORE_NEW_LINES);
$basename = basename($input, '.php');

// Определяем имя вида: ищем в первых строках
$view_name = null;
foreach (array_slice($lines, 0, 3) as $line) {
    if (preg_match('/(?:<!--\s*|\/\*\s*|\*+\s*|\/\/\s*)([^>\s]+\.php)/', $line, $m)) {
        $view_name = $m[1];
        // $view_name = basename($m[1], '.php');
        break;
    }
}

if (!$view_name) {
    $view_name = $basename;
}



// Выходные файлы
$outClean = "__{$basename}_clean.php";
$outEn    = "__{$basename}_en.php";
$outRu    = "__{$basename}_ru.php";
$outUk    = "__{$basename}_uk.php";

$code = file_get_contents($input);

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

// Пишем словари
function writeLang(string $lang, array $trs, string $file, string $view_name): void {
    $out = "<?php\n"
            . "/**\n"
            . " * {$lang}\n"
            . " * for {$view_name}\n"
            . " */\n"
            . "\n"
            . "return [\n";
    foreach ($trs as $key => $row) {
        $k = addslashes($key);
        $v = addslashes($row[$lang]);
        $out .= "    '$k' => '$v',\n";
    }
    $out .= "];\n";
    file_put_contents($file, $out);
    echo "Generated: {$file}\n";
}

writeLang('en', $translations, $outEn, $view_name);
writeLang('ru', $translations, $outRu, $view_name);
writeLang('uk', $translations, $outUk, $view_name);

echo "Cleaned source written to: $outClean\n";

