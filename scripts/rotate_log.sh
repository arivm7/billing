#!/usr/bin/env bash

#
# Project : my.ri.net.ua
# File    : rotate_log.sh
# Path    : scripts/rotate_log.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 14.04.2026
# License : GPL v3
#
# Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
#
# @author Ariv <ariv@meta.ua> | https://github.com/arivm7
#

#
# Управляющий скрипт для PHP-приложения
# Для ротации логов, создаваемых приложением
#



SCRIPT_NAME="$(basename "$0")"
APP_TITLE="Скрипт ротации лог-файлов"
VERSION="1.1.0 (2026-04-14)"
COPYRIGHT="Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK"
LAST_CHANGES="\
v1.1.0 (2026-04-14): Добавлена ротация по минимальному размеру файла.
v1.0.0 (2026-04-13): Добавлена поддержка списков файлов, glob-шаблонов и справки с версией.
"



DEFAULT_MAX_ROTATIONS=36
DEFAULT_MIN_SIZE_BYTES=3072



usage() {
    cat <<EOF
${APP_TITLE}
${SCRIPT_NAME} -- Версия ${VERSION}

Использование:
  $SCRIPT_NAME FILE_PATH [MAX_ROTATIONS]
  $SCRIPT_NAME FILE_PATH [FILE_PATH ...] [MAX_ROTATIONS]
  $SCRIPT_NAME FILE_GLOB [MAX_ROTATIONS]

Аргументы:
  FILE_PATH       Путь к текущему лог-файлу.
  FILE_GLOB       Шаблон файлов, например /var/log/*.log.
  FILE_PATH ...   Несколько файлов, переданных оболочкой после раскрытия шаблона.
  MAX_ROTATIONS   Количество архивных файлов для хранения, по умолчанию: $DEFAULT_MAX_ROTATIONS.

Условие ротации:
  Ротация выполняется только если размер лог-файла больше $DEFAULT_MIN_SIZE_BYTES байт.

Схема ротации:
  FILE_PATH       текущий лог-файл
  FILE_PATH.000   самый свежий архив
  FILE_PATH.001   предыдущий архив
  ...
  FILE_PATH.NNN   самый старый сохраняемый архив

Пример:
  $SCRIPT_NAME /var/log/myapp.log 36
  $SCRIPT_NAME '/var/log/*.log' 12
  $SCRIPT_NAME /var/log/*.log 12

Последние изменения:
${LAST_CHANGES}
${COPYRIGHT}
EOF
}



#
# Выполняет ротацию лог-файла: архивы переименовываются по схеме
# FILE_PATH.NNN, а на месте FILE_PATH создается новый пустой файл.
#  - если каталог файла не существует, функция завершится с ошибкой;
#  - если сам файл отсутствует, функция завершится без ошибки и выведет сообщение Файл для ротации не найден: ...;
#  - если размер файла не превышает порог, ротация не выполняется;
#  - если файл есть и размер превышает порог, ротация выполняется
#  - в обработчике параметров включена обработка шаблонов *, ?, [];
#
rotate_log() {
    local file="$1"
    local max="${2:-$DEFAULT_MAX_ROTATIONS}"
    local min_size="$DEFAULT_MIN_SIZE_BYTES"
    local dir

    if [[ -z "$file" || ! "$max" =~ ^[0-9]+$ ]]; then
        usage >&2
        return 1
    fi

    local i from to current_size
    dir="$(dirname -- "$file")"

    if [[ ! -d "$dir" ]]; then
        echo "Ошибка: каталог '$dir' не существует." >&2
        return 1
    fi

    if [[ ! -e "$file" ]]; then
        echo "Файл для ротации не найден: '$file'."
        return 0
    fi

    current_size=$(stat -c '%s' -- "$file") || return 1
    if (( current_size <= min_size )); then
        echo "Ротация не требуется: размер '$file' (${current_size} байт) не превышает порог ${min_size} байт."
        return 0
    fi

    # Удаляем самый старый архив, если он есть
    printf -v to "%s.%03d" "$file" "$max"
    [[ -e "$to" ]] && rm -f -- "$to"

    # Сдвигаем архивы: .035 -> .036, .034 -> .035, ...
    for (( i=max-1; i>=0; i-- )); do
        printf -v from "%s.%03d" "$file" "$i"
        printf -v to   "%s.%03d" "$file" "$((i+1))"
        [[ -e "$from" ]] && mv -f -- "$from" "$to"
    done

    # Текущий лог -> .000
    mv -f -- "$file" "$file.000"

    # Создаем новый пустой текущий лог
    cp --attributes-only --preserve=all "$file.000" "$file"
    : > "$file"
}



#
# проверка: скрипт запущен напрямую или подключен через source
#
if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
    shopt -s nullglob

    files=()
    max_rotations="$DEFAULT_MAX_ROTATIONS"

    case "${1:-}" in
        -h|--help)
            usage
            exit 0
            ;;
        -v|--version)
            echo "${SCRIPT_NAME} -- Версия ${VERSION}"
            exit 0
            ;;
    esac

    if [[ $# -lt 1 ]]; then
        usage >&2
        exit 1
    fi

    if [[ "${!#}" =~ ^[0-9]+$ ]]; then
        max_rotations="${!#}"
        set -- "${@:1:$(($# - 1))}"
    fi

    if [[ $# -lt 1 ]]; then
        usage >&2
        exit 1
    fi

    if [[ $# -eq 1 && "$1" == *[\*\?\[]* ]]; then
        mapfile -t files < <(compgen -G "$1")

        if [[ ${#files[@]} -eq 0 ]]; then
            echo "Файлы по шаблону не найдены: '$1'." >&2
            exit 1
        fi
    else
        files=( "$@" )
    fi

    for file in "${files[@]}"; do
        rotate_log "$file" "$max_rotations" || exit 1
    done

fi
