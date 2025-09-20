#!/usr/bin/env bash
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
