#!/usr/bin/env bash
#
# Project : s1.ri.net.ua
# File    : gen_lang_dict.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 20 Sep 2025 18:30:48
# License : GPL v3
#
# Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
#
# Обёртка для  Генератора словарей gen_lang_dict.php
# 
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 input.php"
  echo "Справка в php-файле."
  exit 1
fi

# Определяем директорию, где лежит сам bash-скрипт
SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)

# Вызываем PHP-скрипт с тем же именем, но с расширением .php
php "$SCRIPT_DIR/$(basename "$0" .sh).php" "$1"
