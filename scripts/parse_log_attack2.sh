#!/usr/bin/env bash

#
# Project : my.ri.net.ua
# File    : parse_log_attack2.sh
# Path    : scripts/parse_log_attack2.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 10.05.2026
# License : GPL v3
#
# Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
#
# @author Ariv <ariv@meta.ua> | https://github.com/arivm7
#
# Пост-анализа логов с IP-адресами + агрегация по странам + сведение диапазонов в CIDR-блоки.
#  
# 🔹 Берёт лог-файл
# 🔹 Извлекает IP-адреса
# 🔹 Для каждого IP:
# определяет страну через whois
# определяет диапазон (CIDR / inetnum / NetRange)


set -o pipefail

# ================= CONFIG =================

VERBOSE=1

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

FILES=("$@")

# ================= CACHE =================

declare -A CACHE_NETS
declare -A CACHE_COUNTRY

# ================= IP UTILS =================

ip2int() {
    local a b c d
    IFS=. read -r a b c d <<< "$1"
    echo $(( (a<<24) | (b<<16) | (c<<8) | d ))
}

ip_in_cidr() {
    local ip="$1"
    local cidr="$2"

    local base="${cidr%/*}"
    local mask="${cidr#*/}"

    local ipi basei size

    ipi=$(ip2int "$ip")
    basei=$(ip2int "$base")
    size=$((1 << (32 - mask)))

    (( ipi >= basei && ipi < basei + size ))
}

# ================= EXTRACT IP =================

extract_ips() {
    grep -Eo '([0-9]{1,3}\.){3}[0-9]{1,3}' "$@"
}


ip_in_cached_networks() {
    local ip="$1"
    local ipi basei mask net size

    # перевод IP в int
    IFS=. read -r a b c d <<< "$ip"
    ipi=$(( (a<<24) | (b<<16) | (c<<8) | d ))

    for net in "${!CACHE_NETS[@]}"; do

        base="${net%/*}"
        mask="${net#*/}"

        IFS=. read -r a b c d <<< "$base"
        basei=$(( (a<<24) | (b<<16) | (c<<8) | d ))

        size=$((1 << (32 - mask)))

        if (( ipi >= basei && ipi < basei + size )); then
            echo "$net"
            return 0
        fi
    done

    return 1
}


# ================= WHOIS =================

get_country() {
    local ip="$1"

    local c
    c=$(whois "$ip" 2>/dev/null | grep -i '^country:' | head -n1 | awk -F: '{gsub(/ /,"",$2); print $2}')

    [[ -z "$c" ]] && c="??"

    echo "$c"
}

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
    echo "'$net', // ${CACHE_COUNTRY[$net]}"
done | sort

