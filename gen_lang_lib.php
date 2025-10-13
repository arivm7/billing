<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : gen_lang_lib.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  License : GPL v3
 *
 *  Общие функции для генераторов языковых файлов
 */



define('GEN_LANGS', getenv('GEN_LANGS') ?: __DIR__ . '/tmp/gen_langs');
@mkdir(GEN_LANGS, 0777, true);


/**
 * Распарсить конструкцию __('en | ru | uk')
 */
function parseTranslationString(string $fullMatch, string $inside): ?array {
    // Разделяем по неэкранированному |
    $parts = preg_split('/(?<!\\\\)\|/', $inside);
    if (count($parts) !== 3) {
        fwrite(STDERR, "❌ Invalid translation string: $fullMatch\n");
        return null;
    }
    return [
        'en' => trim(stripslashes($parts[0])),
        'ru' => trim(stripslashes($parts[1])),
        'uk' => trim(stripslashes($parts[2])),
    ];
}



/**
 * Записать языковой файл
 */
function writeLang(string $lang, array $trs, string $fileName, string $helperStr): void {
    $out = "<?php\n"
         . "/**\n"
         . " * dict {$lang}\n"
         . " * for {$helperStr}\n"
         . " */\n\n"
         . "return [\n";
    foreach ($trs as $key => $row) {
        $k = addslashes($key);
        $v = addslashes($row[$lang]);
        $out .= "    '{$k}' => '{$v}',\n";
    }
    $out .= "];\n";

    file_put_contents($fileName, $out);
    echo "✅ Generated: {$fileName}\n";
}



/**
 * Записать чистый PHP-файл (clean)
 */
function writeClean(string $basename, string $clean): void {
    $outClean = GEN_LANGS . "/__{$basename}_clean.php";
    file_put_contents($outClean, $clean);
    echo "✅ Cleaned source written to: $outClean\n";
}



/**
 * Извлечь все переводы из строки кода
 */
function extractTranslations(string $code): array {
    $translations = [];
    $regex = '/__\(\s*(["\'])(.*?)\1(\s*,[^)]*)?\)/s';
    if (preg_match_all($regex, $code, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $full   = $m[0];
            $inside = $m[2];
            $parsed = parseTranslationString($full, $inside);
            if (!$parsed) continue;
            $translations[$parsed['en']] = $parsed;
        }
    }
    return $translations;
}



/**
 * Очистить исходный код — оставить только английские ключи
 */
function cleanSource(string $code): string {
    $regex = '/__\(\s*(["\'])(.*?)\1(\s*,[^)]*)?\)/s';
    return preg_replace_callback($regex, function ($m) {
        $inside = $m[2];
        $tail   = $m[3] ?? '';
        $parts = preg_split('/(?<!\\\\)\|/', $inside);
        if (count($parts) !== 3) return $m[0];
        $en = trim(stripslashes($parts[0]));
        return "__('" . addslashes($en) . "'$tail)";
    }, $code);
}



/**
 * Извлечение всех функций и их тел из PHP-кода
 * с корректной обработкой вложенных { ... }.
 */
function extractFunctionsBalanced(string $code): array {
    $functions = [];
    $offset = 0;
    $len = strlen($code);

    while (preg_match('/function\s+([a-zA-Z0-9_]+)\s*\([^)]*\)\s*\{/', $code, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $name = $m[1][0];
        $posStart = $m[0][1];
        $braceStart = strpos($code, '{', $posStart);
        if ($braceStart === false) break;

        $level = 1;
        $i = $braceStart + 1;
        $inString = false;
        $stringChar = null;
        $escaped = false;

        while ($i < $len && $level > 0) {
            $ch = $code[$i];

            // учёт строк и кавычек, чтобы не считать скобки внутри них
            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === '\\') {
                    $escaped = true;
                } elseif ($ch === $stringChar) {
                    $inString = false;
                    $stringChar = null;
                }
            } else {
                if ($ch === '"' || $ch === "'") {
                    $inString = true;
                    $stringChar = $ch;
                } elseif ($ch === '{') {
                    $level++;
                } elseif ($ch === '}') {
                    $level--;
                }
            }
            $i++;
        }

        $body = substr($code, $braceStart + 1, $i - $braceStart - 2);
        $functions[] = [
            'name' => $name,
            'body' => $body,
            'start' => $posStart,
            'end' => $i
        ];
        $offset = $i;
    }

    return $functions;
}



// End of gen_lang_lib.php
