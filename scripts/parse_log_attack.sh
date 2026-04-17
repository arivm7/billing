#!/usr/bin/env bash

#
# Project : my.ri.net.ua
# File    : parse_log_attack.sh
# Path    : scripts/parse_log_attack.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 15.04.2026
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
# преобразует диапазоны в CIDR
# группирует по стране
# сливает пересекающиеся диапазоны
# выводит финальную минимизированную сетевую карту
# 
# Итоговая логика (коротко)
# LOG FILE
#    ↓
# extract IPs
#    ↓
# WHOIS lookup
#    ↓
# IP → CIDR / ranges
#    ↓
# group by country
#    ↓
# convert CIDR → ranges
#    ↓
# merge overlaps
#    ↓
# ranges → CIDR
#    ↓
# output minimal IP blocks per country
# 

LOG_FILE="$1"

if [[ -z "$LOG_FILE" || ! -f "$LOG_FILE" ]]; then
echo "Usage: $0 <log_file>"
exit 1
fi

# ---------- utils ----------

# ip2int() {
# local IFS=.
# read -r a b c d <<< "$1"
# echo $(( (a<<24) + (b<<16) + (c<<8) + d ))
# }

ip2int() {
    local ip="$1"
    local a b c d

    [[ "$ip" =~ ^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$ ]] || {
        echo "invalid ip: $ip" >&2
        return 1
    }

    IFS='.' read -r a b c d <<< "$ip"

    # валидация диапазона
    for o in $a $b $c $d; do
        (( o >= 0 && o <= 255 )) || {
            echo "invalid octet in $ip" >&2
            return 1
        }
    done

    echo $(( (a<<24) | (b<<16) | (c<<8) | d ))
}


# int2ip() {
# local ip=$1
# echo "$(( (ip>>24)&255 )).$(( (ip>>16)&255 )).$(( (ip>>8)&255 )).$(( ip&255 ))"
# }

int2ip() {
    local ip="$1"

    [[ "$ip" =~ ^[0-9]+$ ]] || {
        echo "invalid ip int: $ip" >&2
        return 1
    }

    (( ip >= 0 && ip <= 4294967295 )) || {
        echo "out of range: $ip" >&2
        return 1
    }

    echo "$(( (ip >> 24) & 255 )).$(( (ip >> 16) & 255 )).$(( (ip >> 8) & 255 )).$(( ip & 255 ))"
}


is_private_ip() {
local ip=$1
local IFS=.
read -r a b c d <<< "$ip"

[[ $a -eq 10 ]] && return 0
[[ $a -eq 192 && $b -eq 168 ]] && return 0
[[ $a -eq 172 && $b -ge 16 && $b -le 31 ]] && return 0

return 1
}

range2cidr() {
local start_ip end_ip start end

start_ip=$1
end_ip=$2

start=$(ip2int "$start_ip")
end=$(ip2int "$end_ip")

while [[ $start -le $end ]]; do
    max_size=32

    while true; do
    mask=$((32 - (max_size - 1)))
    block_size=$((1 << (32 - mask)))

    if (( start % block_size != 0 )); then
        ((max_size--))
        continue
    fi

    last=$((start + block_size - 1))
    if (( last > end )); then
        ((max_size--))
        continue
    fi

    break
    done

    mask=$((32 - (max_size - 1)))
    echo "$(int2ip $start)/$mask"

    start=$((start + (1 << (32 - mask))))
done
}

cidr2range() {

local cidr ip mask ip_int size

cidr=$1
ip=${cidr%/*}
mask=${cidr#*/}

ip_int=$(ip2int "$ip")
size=$((1 << (32 - mask)))

echo "$ip_int $((ip_int + size - 1))"
}

merge_ranges() {
sort -n | awk '
{
    if (NR == 1) {
    start = $1
    end = $2
    next
    }

    if ($1 <= end + 1) {
    if ($2 > end) end = $2
    } else {
    print start, end
    start = $1
    end = $2
    }
}
END {
    print start, end
}'
}

# ---------- main ----------

TMP_IPS=$(mktemp)
TMP_DATA=$(mktemp)

awk -F'|' '{gsub(/ /, "", $2); print $2}' "$LOG_FILE" | sort -u > "$TMP_IPS"

while read -r IP; do
echo "Processing $IP..." >&2

if is_private_ip "$IP"; then
    echo "$IP/32|PRIVATE" >> "$TMP_DATA"
    continue
fi

WHOIS=$(whois "$IP")

COUNTRY=$(echo "$WHOIS" | grep -iE '^country:' | head -n1 | cut -d: -f2 | xargs)
[[ -z "$COUNTRY" ]] && COUNTRY="??"

CIDR_LINE=$(echo "$WHOIS" | grep -iE '^CIDR:' | head -n1 | cut -d: -f2 | xargs)

if [[ -n "$CIDR_LINE" ]]; then
    echo "$CIDR_LINE" | tr ',' '\n' | while read -r C; do
    echo "$C|$COUNTRY" >> "$TMP_DATA"
    done
else
    RANGE=$(echo "$WHOIS" | grep -iE 'inetnum:|NetRange:' | head -n1 | cut -d: -f2 | xargs)

    if [[ -n "$RANGE" ]]; then
    START=$(echo "$RANGE" | awk '{print $1}')
    END=$(echo "$RANGE" | awk '{print $3}')

    range2cidr "$START" "$END" | while read -r C; do
        echo "$C|$COUNTRY" >> "$TMP_DATA"
    done
    else
    echo "$IP/32|$COUNTRY" >> "$TMP_DATA"
    fi
fi

sleep 1

done < "$TMP_IPS"

# ---------- агрегация по странам ----------

cut -d'|' -f2 "$TMP_DATA" | sort -u | while read -r COUNTRY; do

grep "|$COUNTRY$" "$TMP_DATA" | cut -d'|' -f1 | sort -u > tmp.cidrs

# CIDR → ranges
while read -r CIDR; do
    cidr2range "$CIDR"
done < tmp.cidrs > tmp.ranges

# merge
MERGED=$(merge_ranges < tmp.ranges)

# обратно в CIDR
echo "$MERGED" | while read -r START END; do
    range2cidr "$(int2ip $START)" "$(int2ip $END)"
done | sort -u | while read -r FINAL; do

    if [[ "$COUNTRY" == "PRIVATE" ]]; then
    echo "$FINAL // внутренняя подсеть"
    else
    echo "$FINAL // $COUNTRY"
    fi

done

done

# cleanup
rm -f "$TMP_IPS" "$TMP_DATA" tmp.cidrs tmp.ranges