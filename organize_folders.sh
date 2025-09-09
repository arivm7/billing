#!/usr/bin/env bash
set -euo pipefail



# 
# Приводит в порядок папки и файлы на стороне сервера
# Создаёт нужные папки и назначает права доступа папкам и файлам.
# 
# Должен быть исправлен или переписан в соответствии с вашими нуждами сервера
# 



echo "ORGANIZE FOLDERS..."
cd "$(dirname "$0")" || { echo "Не удалось перейтив папаку скрипта"; exit 1; }
pwd



# Настройки
CLOUD_USER="${USER}"
CLOUD_GROUP="www-data"
CLOUD_MOD_FOLDERS="770"
CLOUD_MOD_FILES="660"

# Временные папки
TMP="tmp"
CACHE="cache"
LOG="log"
PURIFIER="htmlpurifier"



# -----------------------------
# Создаём нужные вложенные папки
# -----------------------------
mkdir -p \
    "${TMP}/${CACHE}" \
    "${TMP}/${LOG}" \
    "${TMP}/${PURIFIER}"

# -----------------------------
# 1️⃣ Установить владельца и группу для всех файлов и папок
# -----------------------------
sudo chown -R "${CLOUD_USER}:${CLOUD_GROUP}" .

# -----------------------------
# 2️⃣ Права для файлов (включая скрытые)
# -----------------------------
find . -type f -exec chmod ${CLOUD_MOD_FILES} {} +

# -----------------------------
# 3️⃣ Права для папок
# -----------------------------
find . -type d -exec chmod ${CLOUD_MOD_FOLDERS} {} +

# -----------------------------
# 4️⃣ Сделать исполняемыми ключевые скрипты
# -----------------------------
EXEC_SCRIPTS=(
    "$(realpath "$0")"
    "composer.reload.sh"
    "deploy.sh"
    "watch_and_deploy.sh"
)

for f in "${EXEC_SCRIPTS[@]}"; do
    if [ -f "$f" ]; then
        sudo chmod +x "$f" || echo "Не удалось изменить права: $f"
    else
        echo "Не найден: $f"
    fi
done
echo "→ Folder organization completed successfully!"
echo "Права успешно выставлены."
echo "...Ok."
