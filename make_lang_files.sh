#!/usr/bin/env bash
set -euo pipefail



#
# Принимает на вход один файл (например, contacts_view.php).
# Проверяет, есть ли в нём маркер вида
# <!--contacts_edit.php-->
#
# Если есть — берём базовое имя contacts_edit.
# Если нет — берём имя самого файла без расширения.
#
# Ищет все конструкции вида:
# __('Title | Название | Назва')
# (ключ/en + 2 перевода).
#
# Генерирует три отдельных файла:
# en_<basename>.php
# ru_<basename>.php
# uk_<basename>.php
#
# Каждый файл оформляется в нужной php-структуре.
#
# Создаёт
# <basename>_clean.php — копия исходного файла, 
# но вызовы __('ключ | ru | uk') заменены на __('ключ').
#


# ================================
# Usage: ./extract_lang.sh input.php
# ================================



INPUT="$1"

# Определяем базовое имя словаря
BASENAME=$(grep -oP '<!--\K[^>]+(?=-->)' "$INPUT" || true)
if [[ -z "$BASENAME" ]]; then
  BASENAME=$(basename "$INPUT" .php)
fi

# Временные файлы для языков
TMP_EN=$(mktemp)
TMP_RU=$(mktemp)
TMP_UK=$(mktemp)

# Извлекаем все __('... | ... | ...')
grep -oP "__\('[^']+'\)" "$INPUT" | \
sed -E "s/^__\('(.*)'\)/\1/" | \
while IFS="|" read -r en ru uk; do
    en=$(echo "$en" | xargs)
    ru=$(echo "$ru" | xargs)
    uk=$(echo "$uk" | xargs)

    [[ -n "$en" && -n "$ru" && -n "$uk" ]] || continue

    printf "    %-32s => %s,\n" "'$en'" "'$en'" >>"$TMP_EN"
    printf "    %-32s => %s,\n" "'$en'" "'$ru'" >>"$TMP_RU"
    printf "    %-32s => %s,\n" "'$en'" "'$uk'" >>"$TMP_UK"
done

# Функция для генерации php файла
make_lang_file() {
    local lang="$1"
    local tmpfile="$2"
    local outfile="${lang}_${BASENAME}.php"

    {
        echo "<?php"
        echo "/**"
        echo " * $lang"
        echo " * for $BASENAME"
        echo " */"
        echo
        echo "return ["
        cat "$tmpfile"
        echo "];"
    } >"$outfile"

    echo "Generated: $outfile"
}

make_lang_file en "$TMP_EN"
make_lang_file ru "$TMP_RU"
make_lang_file uk "$TMP_UK"

# Чистим исходный файл от переводов
CLEAN_OUT="${BASENAME}_clean.php"
sed -E "s/__\('([^|']+)\s*\|[^']*'\)/__('\1')/g" "$INPUT" >"$CLEAN_OUT"

echo "Generated: $CLEAN_OUT"

# Убираем временные файлы
rm -f "$TMP_EN" "$TMP_RU" "$TMP_UK"
