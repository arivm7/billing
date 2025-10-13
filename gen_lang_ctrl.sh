#!/usr/bin/env bash
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

