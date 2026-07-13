#!/usr/bin/env bash

#
# Project : my.ri.net.ua
# File    : parse_cidr_logs.sh
# Path    : scripts/parse_cidr_logs.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 10.05.2026
# License : GPL v3
#
# Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
#
# @author Ariv <ariv@meta.ua> | https://github.com/arivm7
#
# ОПИСАНИЕ
# --------
# Пост-анализ логов с IP-адресами: агрегация по странам и сведение
# диапазонов адресов в CIDR-блоки на основе данных whois.
#
# Что делает:
#   🔹 Принимает один или несколько лог-файлов (в т.ч. по маске/glob)
#   🔹 Извлекает из них все IPv4-адреса (grep -Eo)
#   🔹 Для каждого уникального IP:
#       - проверяет, не попадает ли он уже в ранее найденную сеть (кэш)
#       - если нет — запрашивает whois и определяет:
#           * сеть (CIDR / route / fallback "/32")
#           * страну (country:)
#       - кэширует пару "сеть -> страна", чтобы не дублировать whois-запросы
#   🔹 В конце выводит отсортированный список уникальных сетей вида:
#         '<CIDR>', // <country>
#
# Вход:
#   $1..$N  — путь(и) к лог-файлу(ам) или shell-маска (в кавычках), напр.:
#             ./parse_log_attack2.sh log.txt
#             ./parse_log_attack2.sh "logs/*.log"
#
# Выход (stdout):
#   Отсортированный список строк "'<CIDR>', // <country>" — по одной
#   на каждую уникальную сеть, найденную в логах.
#
# Выход (stderr, при VERBOSE=1):
#   Пошаговый лог обработки каждого IP (для отладки).
#
# Зависимости:
#   bash 4+ (ассоциативные массивы), grep, awk, whois, sort.
#
# Ограничения / известные проблемы (см. также раздел РЕВЬЮ в конце файла):
#   - whois вызывается по одному разу на каждый НЕ закэшированный IP —
#     на больших логах может быть медленно и/или упереться в rate-limit
#     whois-серверов (RIPE/ARIN и т.д.);
#   - формат вывода whois не строго стандартизирован между регистраторами,
#     разбор полей CIDR/route/country может давать неточный результат;
#   - извлечение IP регуляркой не проверяет валидность октетов (0-255).


set -o pipefail



# ================= CONFIG =================



VERBOSE=1

# log <msg...>
#   Печатает отладочное сообщение в stderr, если VERBOSE=1.
#   Ничего не делает, если VERBOSE=0.
log() {
    [[ "$VERBOSE" -eq 1 ]] && echo "$*" >&2
}



# ================= HELP =================



usage() {
cat <<'EOF'
Usage:
  parse_cidr_logs.sh <file|files|glob>

Examples:
  ./parse_cidr_logs.sh log.txt
  ./parse_cidr_logs.sh "logs/*.log"

Description:
  - Extract IPs from logs
  - Resolve CIDR networks via whois
  - Skip IPs already belonging to known CIDR networks
  - Cache networks and countries
  - Output:
        '<CIDR>' // <country>
EOF
}

[[ $# -eq 0 ]] && { usage; exit 1; }
[[ "$1" == "-h" || "$1" == "--help" ]] && { usage; exit 0; }



# ================= INPUT =================
# Аргументы командной строки используются напрямую как "$@" в extract_ips
# ниже (see MAIN). Отдельная переменная FILES не нужна и была удалена
# как мёртвый код (см. РЕВЬЮ в конце файла).



# ================= CACHE =================



declare -A CACHE_NETS
declare -A CACHE_COUNTRY



# ================= IP UTILS =================



# ip2int <ipv4>
#   Переводит IPv4-адрес вида "a.b.c.d" в 32-битное целое число.
#   Не валидирует диапазон октетов (0-255) — на некорректном вводе
#   даст некорректный, но "молчаливый" результат.
# ip2int() {
#     local a b c d
#     IFS=. read -r a b c d <<< "$1"
#     echo $(( (a<<24) | (b<<16) | (c<<8) | d ))
# }
ip2int() {
    local a b c d
    IFS=. read -r a b c d <<< "$1"
    echo $(( a * 16777216 + b * 65536 + c * 256 + d ))
}



# ip_in_cidr <ip> <cidr>
#   Проверяет, попадает ли <ip> в диапазон <cidr> (напр. "1.2.3.0/24").
#   Возвращает 0 (true), если попадает, иначе — код ошибки от (( )).
ip_in_cidr() {
    local ip="$1"
    local cidr="$2"

    local base="${cidr%/*}"
    local mask="${cidr#*/}"

    local ipi basei size

    ipi=$(ip2int "$ip")
    basei=$(ip2int "$base")
    # size=$((1 << (32 - mask)))
    size=$(( 2 ** (32 - mask) ))

    (( ipi >= basei && ipi < basei + size ))
}



# ================= EXTRACT IP =================



# extract_ips <file...>
#   Извлекает из файла(ов) все подстроки, похожие на IPv4-адрес.
#   ВНИМАНИЕ: регулярка не проверяет, что каждый октет 0-255,
#   поэтому в вывод могут попасть "адреса" вроде 999.999.999.999.
extract_ips() {
    grep -Eo '([0-9]{1,3}\.){3}[0-9]{1,3}' "$@"
}



# ip_in_cached_networks <ip>
#   Линейно проходит по уже найденным сетям (CACHE_NETS) и проверяет,
#   не попадает ли <ip> в одну из них. При совпадении печатает сеть
#   и возвращает 0; иначе — возвращает 1.
#   По сути дублирует логику ip_in_cidr()/find_existing_network(),
#   но inline, без вызова ip2int() — см. пункт в РЕВЬЮ ниже.
ip_in_cached_networks() {
    local ip="$1"
    local ipi basei mask net size
    local a b c d base

    # перевод IP в int
    IFS=. read -r a b c d <<< "$ip"
    # ipi=$(( (a<<24) | (b<<16) | (c<<8) | d ))
    ipi=$(( a * 16777216 + b * 65536 + c * 256 + d ))

    for net in "${!CACHE_NETS[@]}"; do

        base="${net%/*}"
        mask="${net#*/}"

        IFS=. read -r a b c d <<< "$base"
        # basei=$(( (a<<24) | (b<<16) | (c<<8) | d ))
        basei=$(( a * 16777216 + b * 65536 + c * 256 + d ))

        # size=$((1 << (32 - mask)))
        size=$(( 2 ** (32 - mask) ))

        if (( ipi >= basei && ipi < basei + size )); then
            echo "$net"
            return 0
        fi
    done

    return 1
}



# ================= WHOIS =================



# get_country <ip>
#   Запрашивает whois для <ip> и возвращает код страны из поля
#   "country:" первой найденной строки. Если поле не найдено —
#   возвращает "??".
get_country() {
    local ip="$1"
    local c

    c=$(whois "$ip" 2>/dev/null | grep -i '^country:' | head -n1 | awk -F: '{gsub(/ /,"",$2); print $2}')

    [[ -z "$c" ]] && c="??"

    echo "$c"
}



# get_network <ip>
#   Определяет сеть для <ip> через whois в порядке приоритета:
#     1) поле "CIDR:"
#     2) поле "route:" (важно для RIPE/BGP-данных)
#     3) fallback — явный /32 (сеть из одного адреса), если whois
#        не вернул ни CIDR, ни route.
#   Всегда печатает ровно одну сеть и возвращает 0.
get_network() {
    local ip="$1"
    local net

    # 1. CIDR
    net=$(whois "$ip" 2>/dev/null | grep -i '^CIDR:' | head -n1 | awk '{print $2}')

    if [[ -n "$net" ]]; then
        echo "${net%%,*}"
        return 0
    fi

    # 2. route (очень важно для RIPE/BGP данных)
    net=$(whois "$ip" 2>/dev/null | grep -i '^route:' | head -n1 | awk '{print $2}')

    if [[ -n "$net" ]]; then
        echo "${net%%,*}"
        return 0
    fi

    # 3. fallback — ВСЕГДА ЯВНЫЙ /32 (и логируем)
    echo "$ip/32"
    return 0
}



# ================= NETWORK CHECK =================



# find_existing_network <ip>
#   То же самое, что ip_in_cached_networks(), но реализовано через
#   ip_in_cidr() вместо inline-арифметики.
#   ВНИМАНИЕ: сейчас эта функция нигде не вызывается в MAIN
#   (используется только ip_in_cached_networks) — мёртвый код,
#   см. РЕВЬЮ ниже.
find_existing_network() {
    local ip="$1"

    for net in "${!CACHE_NETS[@]}"; do
        if ip_in_cidr "$ip" "$net"; then
            echo "$net"
            return 0
        fi
    done

    return 1
}



# ================= MAIN =================



while read -r ip; do

    [[ -z "$ip" ]] && continue

    log "IP: $ip"

    if existing=$(ip_in_cached_networks "$ip"); then
        log "SKIP: $ip already in $existing"
        continue
    fi

    net=$(get_network "$ip")

    log "  → resolved network: $net"

    # кеш сети (как есть)
    CACHE_NETS["$net"]=1

    country=$(get_country "$ip")
    CACHE_COUNTRY["$net"]="$country"

    log "  → country: $country"
    log "  → stored: $net // $country"

done < <(extract_ips "$@" | sort -u)



# ================= OUTPUT =================



log  "========================================"
log  "Output:"

for net in "${!CACHE_NETS[@]}"; do
    echo "'$net', // ${CACHE_COUNTRY["$net"]}"
done | sort



# ============================================================
# РЕВЬЮ (Ariv, см. также описание в шапке файла)
# ============================================================
#
# Кэшировать проверяемые адреса, чтоыб по уже проверенным не выполнять повторную порверку через whois.
#
# 1. Кавычки / область видимости (то, что просили проверить особо):
#    - В ip_in_cached_networks() переменные a,b,c,d,base,mask
#      объявлялись БЕЗ `local`, в отличие от соседней ip_in_cidr(),
#      где `local` есть. Это утечка в глобальную область видимости:
#      если позже добавить ещё функцию с переменными a/b/c/d, будет
#      трудноуловимый баг из-за пересечения имён. Исправлено —
#      добавлен `local a b c d base`.
#    - В финальном выводе было ${CACHE_COUNTRY[$net]} без кавычек
#      вокруг ключа, тогда как запись делалась как
#      CACHE_NETS["$net"]=1 — то есть кавычки использовались
#      непоследовательно. Функционально для CIDR-строк это не ломалось
#      (нет пробелов/спецсимволов), но стилистически исправлено на
#      ${CACHE_COUNTRY["$net"]} для единообразия.
#    - Собственно опасных "пропущенных кавычек" (когда $var реально
#      мог бы разъехаться на слова или схлопнуться с glob) в коде не
#      нашлось: все вызовы whois "$ip", echo "$ip/32", "${net%%,*}"
#      и т.п. уже корректно в кавычках.
#
# 2. Дублирование логики:
#    - ip_in_cached_networks() и find_existing_network()+ip_in_cidr()
#      решают одну и ту же задачу двумя разными способами. Реально в
#      MAIN используется только ip_in_cached_networks(); find_existing_network()
#      и ip_in_cidr() — мёртвый код. Стоит либо удалить дубликат, либо
#      унифицировать вызов через одну функцию.
#
# 3. Валидация IP:
#    - extract_ips() регуляркой ловит любые "\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}",
#      включая невалидные октеты (999.999.999.999, версии вида 10.20.30.40
#      внутри других чисел и т.п.). Стоит добавить фильтр по диапазону
#      0-255 (например, через доп. awk/regex или проверку в ip2int()).
#
# 4. Устойчивость к whois:
#    - Нет ни таймаута, ни retry, ни паузы между запросами whois.
#      На больших логах это может быть медленно и/или привести к
#      rate-limit / бану со стороны whois-серверов (RIPE, ARIN и т.д.).
#      Стоит добавить `timeout N whois ...` и небольшую задержку.
#
# 5. Разное:
#    - В usage() указано имя parse_cidr_logs.sh, а фактический файл
#      называется parse_log_attack2.sh — несоответствие, вводит в
#      заблуждение при чтении --help.
#    - VERBOSE зашит константой (=1), нет флага --quiet/-q, чтобы
#      выключить лог в stderr без правки кода.
#    - Порядок обработки IP (`for net in "${!CACHE_NETS[@]}"`) не
#      детерминирован до финальной сортировки — это нормально, т.к.
#      вывод в конце сортируется, но при желании логировать в
#      воспроизводимом порядке это стоит иметь в виду.
