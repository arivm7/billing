#!/usr/bin/env bash

#
# Project : my.ri.net.ua
# File    : updates.sh
# Path    : scripts/updates.sh
# Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
# Org     : RI-Network, Kiev, UK
# Created : 04 Nov 2025 07:01:44
# License : GPL v3
#
# Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
#
# @author Ariv <ariv@meta.ua> | https://github.com/arivm7
#

#
# Управляющий скрипт для PHP-приложения
# Использование:
#   ./updates.sh update   — запустить update_rest.php
#   ./updates.sh LOG      — показать строки логов по $APP_NAME
#



VERSION="1.0.0 (2025-11-04)"
COPYRIGHT="Copyright (C) 2004-2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK"
LAST_CHANGES="\
v1.0.0 (2025-11-04): базовый функционал
"

APP_TITLE="Скрипт обновления сумм начислений и оплат для всх ЛС"
APP_NAME=$(basename "$0")                                   # Полное имя скрипта, включая расширение
APP_PATH=$(cd "$(dirname "$0")" && pwd)                     # Путь размещения исполняемого скрипта
FILE_NAME="${APP_NAME%.*}"                                  # Убираем расширение (если есть), например ".sh"

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_BIN="$(command -v php || echo '/usr/bin/php')"

LOG_TITLE="RI-BILLING"
COUNT_LAST_LOG=40

help() 
{
echo -e "\
${APP_TITLE}\n\
Использование: ${APP_NAME} {update|LOG|log}\n\
    ${APP_NAME} update  -- обновление данных в базе\n\
    ${APP_NAME} LOG|log -- Показать последние ${COUNT_LAST_LOG} строк лога работы скрипта\n\
\n\
ВАЖНО:\n\
    Обновлять начисления нужно только после обновления стоимости прайсовых фрагментов\n\
\n\
Самостоятельно пссмотреть логи можно примерно так:\n\
    journalctl -t ${LOG_TITLE} -n ${COUNT_LAST_LOG} --no-pager\n\
\n\
Использовать в cron примерно так (crontab -e):\n\
  1  1  *  *  *             /путь/к/скрипту/updates.sh update\n\
\n\
Имя файла скрипта    : ${APP_NAME}\n\
Расположение скрипта : ${APP_PATH}\n\
Версия               : ${VERSION}\n\
Последние изменения\n\
${LAST_CHANGES}\n\
${COPYRIGHT}\n\
"
}

# ---------- Проверка аргументов ----------
ACTION="$1"
if [[ -z "$ACTION" ]]; then
    help
    exit 1
fi

# ---------- Действия ----------
case "$ACTION" in
    update)
        echo "[INFO] Запуск update_rest.php из $SCRIPT_DIR ..."
        "$PHP_BIN" "$SCRIPT_DIR/update_rest.php"
        EXIT_CODE=$?
        echo "[INFO] Завершено с кодом $EXIT_CODE"
        exit $EXIT_CODE
        ;;

    LOG|log)
        echo "[INFO] Последние $COUNT_LAST_LOG строк лога '$LOG_TITLE'"
        journalctl -t "$LOG_TITLE" -n "$COUNT_LAST_LOG" --no-pager
        ;;

    *)
        echo "Неизвестная команда: $ACTION"
        help
        exit 1
        ;;
esac

