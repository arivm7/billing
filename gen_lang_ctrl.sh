#!/usr/bin/env bash
#
# Project : my.ri.net.ua
# File    : gen_lang_ctrl.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 10 Oct 2025 18:30:48
# License : GPL v3
#
# Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
#
# Обёртка для Генератора языковых словарей для контроллеров gen_lang_ctrl.php
#
set -euo pipefail

# Папка для вывода (если не задана)
: "${GEN_LANGS:=tmp/gen_langs}"
export GEN_LANGS

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 Controller.php"
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
php "${SCRIPT_DIR}/gen_lang_ctrl.php" "$1"

