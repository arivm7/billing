#!/usr/bin/env bash
set -euo pipefail
trap 'logger -p error -t "S1_DEPLOY" "[$(date)] Ошибка в строке $LINENO: команда \"$BASH_COMMAND\""' ERR



# 
# Отправляет проект на сервер, публикует его
# Выполняем post-deploy скрипт
# 

# ВАЖНО:
# На сервере папка назначения заранее должна иметь права записи для пользователя от которого происходит деплой.
# Что-то типа такого на сервере:
#   sudo mkdir -p /var/www/${DOMAIN}
#   sudo chown -R ${USER}:www-data /var/www/${DOMAIN}
#   sudo chmod -R 770 /var/www/${DOMAIN}


# ==============================
# Настройки деплоя
# ==============================
DOMAIN="s1.ri.net.ua"
SSH_PORT="21235"
SSH_KEY="${HOME}/.ssh/id_ed25519"   # или "" если ключ не нужен. ВАЖНО: путь должен быть БЕЗ пробелов
CLOUD_HOST="${USER}@${DOMAIN}"
CLOUD_PATH="/var/www/${DOMAIN}"
CLOUD_URL="${CLOUD_HOST}:${CLOUD_PATH}"
CLOUD_RUN="organize_folders.sh"



# --- что деплоим ---
DEPLOY=(
    "app"
    "billing"
    "config"
    "public"
    "vendor"
    "composer.json"
    "composer.lock"
    ".htaccess"
    "organize_folders.sh"
)

# --- что исключаем ---
EXCLUDES=(
    ".git"
    ".gitignore"
    "storage/*"
    "public/uploads/*"
    "public/phpinfo.php"
)



# ==============================
# Формируем параметры rsync
# ==============================

# Формируем список исключений для rsync
EXCLUDE_PARAMS=()
for excl in "${EXCLUDES[@]}"; do
    EXCLUDE_PARAMS+=(--exclude="$excl")
done

# Формируем список путей для копирования
DEPLOY_PATHS=()
for item in "${DEPLOY[@]}"; do
    DEPLOY_PATHS+=("./${item}")
done



# ==============================
# Формируем rsh для rsync и ssh 
# ==============================
SSH_CMD=(ssh)
[[ -n "${SSH_KEY}" ]] && SSH_CMD+=(-i "${SSH_KEY}")
SSH_CMD+=(-p "${SSH_PORT}")



# ==============================
# Запускаем rsync
# ==============================
echo "→ Deploying..."
rsync \
    --rsh="${SSH_CMD[*]}" \
    --archive \
    --human-readable \
    --verbose \
    --progress \
    --delete \
    "${EXCLUDE_PARAMS[@]}" \
    "${DEPLOY_PATHS[@]}" \
    "${CLOUD_URL}/"


# ==============================
# Post-deploy на сервере
# ==============================
echo "Выполняем post-deploy скрипт..."
"${SSH_CMD[@]}" "${CLOUD_HOST}" "bash -l -c 'bash ${CLOUD_PATH}/${CLOUD_RUN}'"
echo "...end post-deploy."
echo "→ Deploy completed successfully!"




##
## --human-readable        Выводит размеры файлов в читаемом виде (1K, 2.3M, 1G) вместо байт.
## --verbose               Подробный вывод: показывает, что копируется, создаётся, удаляется.
## --progress              Показывает прогресс копирования каждого файла (скорость, % выполнения).
## 
## Архивирование и рекурсия
## 
## --archive (-a)          Режим архивации. Это сочетание нескольких опций:
##                         -r → рекурсивно копирует каталоги
##                         -l → копирует символические ссылки как ссылки
##                         -p → сохраняет права доступа
##                         -t → сохраняет времена модификации
##                         -g → сохраняет группу
##                         -o → сохраняет владельца
##                         -D → сохраняет устройства и файлы специального типа
## 
## --recursive (-r)        Рекурсивное копирование директорий.
##                         Примечание: при --archive рекурсия уже включена, так что --recursive тут дублирует.
## 
## Времена, права, владельцы
## 
## --times (-t)            → сохраняет время модификации файлов
## --perms (-p)            → сохраняет права файлов
## --owner (-o)            → сохраняет владельца файла (нужны права root на сервере)
## --xattrs                → сохраняет расширенные атрибуты файлов (SELinux, ACL и пр.)
## --acls                  → сохраняет ACL (списки контроля доступа)
## --atimes                → сохраняет время последнего доступа
## --executability         → сохраняет флаг выполнения (+x)
## 
## Ссылки
## 
## --links (-l)            → сохраняет символические ссылки как ссылки
## --hard-links            → сохраняет жёсткие ссылки
## 
## Удаление
## 
## --delete                Удаляет на сервере файлы и папки, которых больше нет в исходной директории.
##
