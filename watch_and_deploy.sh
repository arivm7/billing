#!/usr/bin/env bash



# 
# Скрипт на стороне редактирования проекта.
# Следит за указанными папками и 
# при обнаружении изменений выполняет deploy -- отправку изменений на сервер
# 



COLOR_USAGE="\e[1;32m"              # Терминальный цвет для вывода переменной статуса
COLOR_ERROR="\e[0;31m"              # Терминальный цвет для вывода ошибок
COLOR_INFO="\e[0;34m"               # Терминальный цвет для вывода информации (об ошибке или причине выхода)
COLOR_FILENAME="\e[1;36m"           # Терминальный цвет для вывода имён файлов
COLOR_OK="\e[0;32m"                 # Терминальный цвет для вывода статуса "Ok"
COLOR_OFF="\e[0m"                   # Терминальный цвет для сброса цвета


# Путь к проекту
PROJECT_DIR="${HOME}/Programing/NetBeansProjects/s1.ri.net.ua"

## Путь к скрипту нормализации папок
## echo "==== Нормализация папаок (выполяется на сервере)"
## ORGANIZE_FOLDERS="${PROJECT_DIR}/organize_folders.sh"
## "${ORGANIZE_FOLDERS}" -- на стороне разработки не нужен. Выполняется на стороне сервера из DEPLOY_SCRIPT.

# Путь к скрипту деплоя
DEPLOY_SCRIPT="${PROJECT_DIR}/deploy.sh"

# Папки, за которыми следим (например, src/ и public/)
WATCH_DIRS=(
  "${PROJECT_DIR}/"
)
  # "${PROJECT_DIR}/app"
  # "${PROJECT_DIR}/billing"
  # "${PROJECT_DIR}/config"
  # "${PROJECT_DIR}/nbproject"
  # "${PROJECT_DIR}/public"
  # "${PROJECT_DIR}/tmp"
  # "${PROJECT_DIR}/vendor"


cd "${PROJECT_DIR}" || { echo -e "${COLOR_ERROR}Ошибка${COLOR_OFF}: Не удалось перейти в папку исходников проекта [${COLOR_FILENAME}${PROJECT_DIR}$COLOR_OFF]"; exit 1; }

echo -e ""
echo -e "Текущая папака: ${COLOR_FILENAME}$(pwd)${COLOR_OFF}"
echo -e ""

echo "===> Watching for changes in:"
printf "     %s\n" "${WATCH_DIRS[@]}"
echo "===> Ctrl+C to stop."

# Главный цикл
inotifywait -r -m -e modify,create,delete,move --format '%w|%e|%f' "${WATCH_DIRS[@]}" | while IFS='|' read -r path action file; do
    echo -e "🟡 Change detected"
    echo -e "${COLOR_FILENAME}${path}${COLOR_OFF}"
    echo -e "${COLOR_USAGE}$(date +%F\ %T)${COLOR_OFF} | ${COLOR_INFO}${action}${COLOR_OFF} on ${COLOR_FILENAME}${file}${COLOR_OFF}"
    echo -e "==== Start deploy..."
    "${DEPLOY_SCRIPT}"
    echo -e "==== ${COLOR_OK}End deploy${COLOR_OFF}  [ ${COLOR_USAGE}Ctrl+C${COLOR_OFF} to stop ]"
done
