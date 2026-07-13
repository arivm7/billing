#!/bin/bash

# 
#  Project    : my.ri.net.ua
#  File       : sms_sender.sh
#  Path       : scripts/sms_sender.sh
#  Install to : ~/bin/sms_sender.sh
#  Purpose    : Скрипт отправки SMS через KDE Connect и регистрацию в базе
#  Author     : Ariv <ariv@meta.ua> | https://github.com/arivm7
#  Org        : RI-Network, Kiev, UK
#  Created    : 18.01.2026
#  License    : GPL v3
# 
#  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
# 
#  Описание:
#    Скрипт отправляет SMS-сообщение через локально подключённый Android-телефон
#    посредством KDE Connect (kdeconnect-cli --send-sms) и, опционально,
#    регистрирует факт отправки в биллинговой системе RI-Network через API
#    https://my.ri.net.ua/api (см. billing/core/Api.php, команда "sms_reg").
#
#  Общий алгоритм работы:
#    1. Чтение конфиг-файла (~/.config/ri-network/sms_sender.conf).
#       Если конфига нет — он создаётся из секции [CONFIG START]..[CONFIG END]
#       этого же скрипта (см. save_config_file()).
#    2. Разбор служебных флагов командной строки (-h, -v, --test* и т.д.),
#       если они переданы первым аргументом — выполняются и скрипт завершается.
#    3. Проверка наличия обязательных внешних зависимостей (gawk, kdeconnect, curl).
#    4. Валидация номера телефона (формат +380XXXXXXXXX и код оператора Украины).
#    5. Проверка доступности устройства KDE Connect (is_connect_device).
#    6. Отправка SMS через kdeconnect-cli.
#    7. Если передан ABON_ID — регистрация отправленного сообщения в базе через API.
#
#  Формат вызова:
#    sms_sender.sh <PHONE> <MESSAGE> [ABON_ID]
#    sms_sender.sh --help | --usage | --version | --edit-conf | --write-conf
#    sms_sender.sh --test | --test-all | --test-dev | --test-api
#    sms_sender.sh --install [PATH]
#

#notify-send "SMS: '$1': $2..."

set -o errexit
set -o nounset
set -o pipefail



APP_TITLE="Скрипт отправки SMS через KDE Connect и регистрацию в базе https://my.ri.net.ua/api"
COPYRIGHT="Copyright (C) 2006-2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK"
VERSION="1.4.0 (2026-07-13)"
LAST_CHANGES="\
v1.4.0 (2026-07-13): Добавлено тестирование доступа к API --test | --test-all | --test-dev | --test-api
v1.3.0 (2026-03-01): Добавлена функция проверки доступности устройства KDE Connect 
v1.2.0 (2026-02-26): Добавление команды установки скрипта в указанное место, а также для перезаписи конфига.
v1.1.0 (2026-01-19): Добавление конфиг-файла для хранения параметров
v1.0.0 (2026-01-18): Базовая проверка, отправка СМС и регистрация в базе
"

APP_PATH=$(cd "$(dirname "$0")" && pwd)     # Путь размещения исполняемого скрипта
APP_NAME=$(basename "$0")                   # Полное имя скрипта, включая расширение
FILE_NAME="${APP_NAME%.*}"                  # Убираем расширение (если есть)
CONFIG_DIRNAME="ri-network"
CONFIG_PATH="${XDG_CONFIG_HOME:-${HOME}/.config}/${CONFIG_DIRNAME}"
CONFIG_FILE="${CONFIG_PATH}/${FILE_NAME}.conf"



##
##  [CONFIG START] =========================================================================
##  Начало секции конфига
##
##  Конфиг для скрипта sms_sender.sh
##  Из пакета биллинговой системы RI-Network
##  VERSION 1.2.0 (2026-02-26)
##

#
#  Допустимо использование переменных окружения, типа ${HOME}
#

#
#  Токен для авторизации для регистрации СМС через API https://my.ri.net.ua/api
#  Получить токен можно из базы: hash pass2
#
TOKEN=''

#
# ID устройства KDE Connect для отправки SMS через KDE Connect
# Пример:
# device="0123456789abcdef"
#
device=""

#
# Человекочитаемое имя устройства для отображения в терминале
# Пример:
# device_name="My Android Phone"
#
device_name=""

#
#  Ожидание отправки сообщения в секундах
#
waitsending=5

#
#  Команда API для регистрации SMS
#  из billing/core/Api.php
#
CMD_SMS_REG="sms_reg"
F_AID="aid"
F_CMD="cmd"
F_TEXT="text";
F_PHONE_NUMBER="phone_num"
URI_CMD2="/api/cmd2"
URL_API="https://my.ri.net.ua${URI_CMD2}"



# -------------------------
# Массив разрешённых кодов мобильных операторов Украины
# -------------------------
VALID_OPERATORS=(
     "020" # Інтертелеком
     "039" # Голден Телеком, тепер Київстар
     "050" # Vodafone Україна[1]
     "063" # lifecell
     "066" # Vodafone Україна
     "067" # Київстар
     "068" # Київстар
     "073" # lifecell[2]
     "075" # Vodafone Україна[3]
     "077" # Київстар
     "089" # Інтертелеком для SIP-телефонії[4]
     "091" # ТриМоб, тепер Укртелеком
     "092" # PEOPLEnet
     "093" # lifecell
     "094" # Інтертелеком[5]
     "095" # Vodafone Україна
     "096" # Київстар
     "097" # Київстар
     "098" # Київстар
     "099" # Vodafone Україна
)



#
# Терминальные цвета для вывода информации
#
COLOR_USAGE="\033[1;32m"           # Терминальный цвет для вывода переменной статуса (светло-бирюзовый)
COLOR_INFO="\033[0;33m"            # Терминальный цвет для вывода информации (об ошибке или причине выхода) (оранжевый)
COLOR_INFO1="\033[2;32m"           # Терминальный цвет для вывода переменной статуса (тёмно-бирюзовый)
COLOR_TEXT="\033[1;32m"            # Терминальный цвет для вывода переменной статуса (бирюзовый)
COLOR_ERROR="\033[0;31m"           # Терминальный цвет для вывода ошибок (красный)
COLOR_FILENAME="\033[1;36m"        # Терминальный цвет для вывода имён файлов (голубой)
COLOR_STATUS="\033[0;36m"          # Терминальный цвет для вывода переменной статуса (бирюзовый)
COLOR_OK="\033[0;32m"              # Терминальный цвет для вывода Ok-сообщения (зелёный)
COLOR_OFF="\033[0m"                # Терминальный цвет для сброса цвета (по умолчанию)



# -------------------------
# Префиксы для вывода сообщений
# -------------------------
PREFIX_OK="${COLOR_OK}[ok]${COLOR_OFF}"
PREFIX_ERROR="${COLOR_ERROR}[er]${COLOR_OFF}"
PREFIX_INFO="${COLOR_INFO}[ii]${COLOR_OFF}"

# Программа-редактор для редактирования конфиг-файла и списка папаок для копирования
# (без пробелов в пути/и/названии)
EDITOR="nano"

# Путь к приложению awk
# для извлечения фрагмента конфига из самого скрипта и создания конфиг-файла
APP_AWK="/usr/bin/awk"

#
# Обязательные зависимости в виде ассоциаливного массива
# [программа]=пакет
# где "программа" -- собственно сама исполняемая програма
#     "пакет"     -- пакет внутри которого находится эта программа 
#                    для установки в систму
declare -A DEPENDENCIES_REQUIRED=(
    ["${APP_AWK}"]="gawk"
    ["kdeconnect-cli"]="kdeconnect"
    ["curl"]="curl"
)

#
# Рекомендуемый путь по умолчанию для установки скрипта
#
INSTALL_PATH="$HOME/bin"                        

##
##  Конец секции конфига
##  [CONFIG END] ---------------------------------------------------------------------------
##



#
# Вывод сообщения об успехе с префиксом [ok]
# $* -- текст сообщения
#
msg_ok()
{
    echo -e "${PREFIX_OK} $*"
}

#
# Вывод сообщения об ошибке с префиксом [!!] (без выхода из скрипта)
# $* -- текст сообщения
#
msg_error()
{
    echo -e "${PREFIX_ERROR} $*"
}

#
# Вывод информационного сообщения с префиксом [ii]
# $* -- текст сообщения
#
msg_info()
{
    echo -e "${PREFIX_INFO} $*"
}


#
# Вывод строки и выход из скрипта
# $1 -- сообщение
# $2 -- код ошибки. По умолчанию "1"
#
exit_with_msg() {
    local msg="${1:?Строка не передана или пуста. Смотреть вызывающую функцию.}"
    local num="${2:-1}"
    case "${num}" in
    1)
        # log_error "ERR: ${msg}"
        msg="${PREFIX_ERROR} ${msg}"
        ;;
    2)
        # log_error "ERR: ${msg}"
        msg="${PREFIX_ERROR} ${msg}"
        msg="${msg}\nПодсказка по использованию: ${COLOR_USAGE}${APP_NAME} --usage|-u${COLOR_OFF}"
        ;;
    0)
        # log_info "OK: ${msg}"
        msg="${PREFIX_OK} ${msg}"
        ;;
    *)
        # log_info "${msg}"
        msg="${PREFIX_INFO} ${msg}"
        ;;
    esac
    echo -e "${msg}"
    exit "$num"
}



#
#  Записывает в конфиг файл фрагмент этого же скрипта между строками, содержащими [КОНФИГ СТАРТ] и [КОНФИГ ЕНД] 
#  Используемые глобальные переменные 0 и CONFIG_FILE
#
save_config_file()
{
    #  Сохранение имеющегося конфиг-файла, если есть, с добавлением суффикса .old
    if [ -f "${CONFIG_FILE}" ]; then
        msg_info "Обнаружен существующий конфиг-файл: ${COLOR_FILENAME}${CONFIG_FILE}${COLOR_OFF}."
        mv --force "${CONFIG_FILE}" "${CONFIG_FILE}.old"    
        msg_info "Старый конфиг переименован в ${COLOR_FILENAME}${CONFIG_FILE}.old${COLOR_OFF}"
    fi

    mkdir -p "${CONFIG_PATH}"
    msg_info "Инициализация конфиг-файла '${COLOR_FILENAME}${CONFIG_FILE}${COLOR_OFF}'"
    if ! command -v "${APP_AWK}" >/dev/null 2>&1; then
        exit_with_msg "Нет приложения ${COLOR_FILENAME}${APP_AWK}${COLOR_OFF}." 1
    fi
    # Извлечь фрагмент между [КОНФИГ СТАРТ] и [КОНФИГ ЕНД] из самого скрипта
    "${APP_AWK}" '/\[\s*CONFIG START\s*\]/,/\[\s*CONFIG END\s*\]/' "$0" > "${CONFIG_FILE}"
}



#
#  Чтение конфигурационного файла.
#  Если его нет, то создание.
#
read_config_file()
{
    #
    # Перепределение переменных из конфиг-файла
    # Если конфиг-файла нет, то создаём его
    # load_config
    #
    if [ -f "${CONFIG_FILE}" ]; then
        # shellcheck source="${XDG_CONFIG_HOME:-${HOME}/.config}/${CONFIG_DIRNAME}}/${FILE_NAME}.conf"
        # shellcheck disable=SC1091
        source "${CONFIG_FILE}"
    else
        save_config_file
    fi
}



#
# Проверка наличия команды/исполняемого файла в системе (через command -v)
# $1      -- имя команды или путь к исполняемому файлу
# Возврат -- 0, если команда найдена в PATH; 1, если не найдена
#
is_installed() {
    command -v "$1" &>/dev/null
}


#
#  Проверка обязательных зависимостей (см. ассоциативный массив DEPENDENCIES_REQUIRED)
#  Аргументов не принимает.
#  Возврат -- 0, если все зависимости установлены;
#             1, если хотя бы одна отсутствует (список выводится в msg_error)
#
check_dependencies_required() {
  local missing=()

  for cmd in "${!DEPENDENCIES_REQUIRED[@]}"; do
    if ! is_installed "$cmd"; then
      missing+=("$cmd")
    fi
  done

  if [ "${#missing[@]}" -eq 0 ]; then
    msg_ok "Все обязательные зависимости установлены."
    return 0
  else
    msg_error "Обязательные зависимости не найдены:"
    for cmd in "${missing[@]}"; do
      local pkg="${DEPENDENCIES_REQUIRED[$cmd]}"
      echo -e "${COLOR_STATUS}      - $cmd (пакет: ${pkg:-неизвестен})${COLOR_OFF}"
    done
    return 1
  fi
}



#
# Установка скрипта в указанное место 
# с проверкой существования файла и возможностью перезаписи
# А также с проверкой наличия конфига 
# Использование: APP --install "~/bin"
# $1      -- путь назначения (необязателен: если пуст, запрашивается
#            подтверждение на использование INSTALL_PATH по умолчанию)
# Возврат -- 0 при успешной установке или при явном отказе от перезаписи;
#            завершает скрипт через exit_with_msg при фатальных ошибках
#            (нет каталога назначения, ошибка копирования и т.п.)
#
install() {
    local dest_dir="$1"

    if [[ -z "$dest_dir" ]]; then
        if [[ -z "$INSTALL_PATH" ]]; then
            msg_error "Переменная INSTALL_PATH не задана"
            return 1
        fi

        msg_info "Путь назначения не указан."
        read -rp "Использовать путь по умолчанию (${INSTALL_PATH})? [y/N]: " ans
        if [[ "$ans" =~ ^[Yy]$ ]]; then
            dest_dir="$INSTALL_PATH"
        else
            exit_with_msg "Установка отменена." 1
        fi
    fi

    if [[ "$dest_dir" == "~"* ]]; then
        dest_dir="${dest_dir/#\~/$HOME}"
    fi

    # Определяем путь к текущему скрипту
    local src
    src="$(realpath "${BASH_SOURCE[0]}")" || {
        exit_with_msg "Не удалось определить путь к исходному файлу" 1
    }

    # Проверка наличия каталога назначения
    if [[ ! -d "$dest_dir" ]]; then
        exit_with_msg "Каталог назначения не существует: $dest_dir" 1
    fi

    # --- Проверка существования основного файла ---
    local dest="$dest_dir/$APP_NAME"
    if [[ -e "$dest" ]]; then
        read -rp "Файл $dest уже существует. Перезаписать? [y/N]: " ans
        [[ "$ans" =~ ^[Yy]$ ]] || {
            msg_info "Установка отменена."
            return 0
        }
    fi

    # Копирование
    if cp "$src" "$dest"; then
        chmod +x "$dest" || { echo "Ошибка изменения chmod файла"; return 1; }
        msg_ok "Установлено: $dest"
    else
        exit_with_msg "Ошибка копирования" 1
    fi

    # --- Работа с конфигом ---
    if [[ -e "$CONFIG_FILE" ]]; then
        msg_info "Обнаружен конфиг-файл предыдущей версии: ${COLOR_FILENAME}${CONFIG_FILE}${COLOR_OFF}."
        msg_info "Его можно оставить или перезаписать командой ${COLOR_USAGE}${APP_NAME} -wc|--write-conf${COLOR_OFF}."
        msg_info "Его можно можно удалить и он автоматически будет создан при первом запуске скрипта."
        read -rp "Удалить старый конфиг $CONFIG_FILE ? [y/N]: " ans
        if [[ "$ans" =~ ^[Yy]$ ]]; then
            mv -i "$CONFIG_FILE" "${CONFIG_FILE}.old"
            msg_info "Старый Конфиг перемещен в ${CONFIG_FILE}.old"
            return 0
        else 
            msg_info "Оставлен старый конфиг ${COLOR_FILENAME}${CONFIG_FILE}${COLOR_OFF}"
            return 0
        fi
    fi
}



# -------------------------
# Показать краткое использование скрипта
# -------------------------
print_usage() 
{
echo -e "$(cat << EOF     
${COLOR_INFO}${APP_TITLE}${COLOR_OFF}

${COLOR_INFO}Использование:${COLOR_OFF} ${COLOR_USAGE}$APP_NAME${COLOR_OFF} <${COLOR_USAGE}PHONE${COLOR_OFF}> <${COLOR_USAGE}MESSAGE${COLOR_OFF}> [${COLOR_USAGE}ABON_ID${COLOR_OFF}]
     
${COLOR_INFO}Обязательные параметры:${COLOR_OFF}
    ${COLOR_USAGE}PHONE${COLOR_OFF}     Номер телефона +380XXXXXXXXX, на который отправляется SMS
    ${COLOR_USAGE}MESSAGE${COLOR_OFF}   Текст сообщения для отправки

${COLOR_INFO}Необязательный параметр:${COLOR_OFF}
    ${COLOR_USAGE}ABON_ID${COLOR_OFF}   ID абонента для регистрации сообщения через API https://my.ri.net.ua/api

${COLOR_INFO}Флаги:${COLOR_OFF}
    ${COLOR_USAGE}-h,  --help${COLOR_OFF}         Показать полную справку
    ${COLOR_USAGE}-u,  --usage${COLOR_OFF}        Показать краткую подсказку
    ${COLOR_USAGE}-v,  --version${COLOR_OFF}      Показать версию скрипта
    ${COLOR_USAGE}-ec, --edit-conf${COLOR_OFF}    Открыть конфиг в редакторе
    ${COLOR_USAGE}-wc, --write-conf${COLOR_OFF}   Принудительно перезаписать конфиг по умолчанию
                        Использовать с осторожностью, поскольку в конфиге есть важные параметры, 
                        такие как токен для API и ID устройства. 
                        Рекомендуется сначала сделать резервную копию текущего конфига.
    ${COLOR_USAGE}--test-dev${COLOR_OFF}          Проверить только доступность устройства KDE Connect
    ${COLOR_USAGE}--test-api${COLOR_OFF}          Проверить только доступность API https://my.ri.net.ua/api
    ${COLOR_USAGE}--test, --test-all${COLOR_OFF}  Проверить и устройство, и API одновременно

    ${COLOR_USAGE}--install [<path>]${COLOR_OFF}  Установить скрипт в указанное место (например, ${COLOR_FILENAME}${APP_NAME} --install ~/bin${COLOR_OFF})
                        Путь установки по умолчанию ${COLOR_USAGE}${INSTALL_PATH}${COLOR_OFF}.

EOF
)"
}



# -------------------------
# Показать полное описание / помощь
# -------------------------
print_help() 
{
echo -e "$(cat << EOF     
${COLOR_INFO}Описание:${COLOR_OFF}
Скрипт отправляет SMS через KDE Connect 
и регистрирует сообщение для указанного абонента в базе https://my.ri.net.ua/ через API.

$(print_usage)

Примеры использования:
    Без регистрации в базе:
        ${COLOR_USAGE}${APP_NAME} +380931234567 'Текстовое сообщение'${COLOR_OFF}
    С регистрацией в базе для абонента с ID 123:
        ${COLOR_USAGE}${APP_NAME} +380931234567 'Текстовое сообщение' 123${COLOR_OFF}

    Проверить только устройство KDE Connect (без отправки SMS):
        ${COLOR_USAGE}${APP_NAME} --test-dev${COLOR_OFF}
    Проверить только доступность API:
        ${COLOR_USAGE}${APP_NAME} --test-api${COLOR_OFF}
    Проверить и устройство, и API одновременно:
        ${COLOR_USAGE}${APP_NAME} --test${COLOR_OFF}

Если не переданы обязательные параметры PHONE и MESSAGE, скрипт завершится с ошибкой.
EOF
)"
}



# -------------------------
#  Вывод версии скрипта
# -------------------------
print_version()
{
echo -e "$(cat << EOF     
${APP_TITLE}
Скрипт       : ${APP_NAME}
Версия       : ${VERSION}
Путь скрипта : "${APP_PATH}"
Конфиг       : "${CONFIG_FILE}"
Последние изменения
${LAST_CHANGES}
${COPYRIGHT}
EOF
)"
}



#
# Проверка доступности API https://my.ri.net.ua/api (команда test_auth)
# Аргументов не принимает, использует глобальные TOKEN и URL_API.
# Возврат -- 0, если API вернул HTTP 200;
#            1, если получен любой другой код (401/403/404/др.), сообщение
#            об ошибке при этом выводится через msg_error
#
api_test()
{
    local code

    code=$(curl -s -o /dev/null -w "%{http_code}" \
        -X POST "${URL_API}" \
        -H "Authorization: Bearer ${TOKEN}" \
        -d "cmd=test_auth")

    case "$code" in
        200)
            return 0
            ;;
        401)
            msg_error "Неверный Bearer Token"
            ;;
        403)
            msg_error "Доступ запрещён"
            ;;
        404)
            msg_error "API недоступно"
            ;;
        *)
            msg_error "Ошибка API (HTTP ${code})"
            ;;
    esac

    return 1
}



# -------------------------
# Проверка подключения устройства к KDE Connect (двумя попытками ping,
# со сбросом/обновлением списка устройств между попытками)
# Аргументов не использует — работает с глобальными $device и $device_name.
# Возврат -- 0, если устройство ответило на --ping;
#            1, если устройство недоступно после двух попыток
# -------------------------
is_connect_device() {

    # Первая попытка пинга
    if kdeconnect-cli -d "$device" --ping >/dev/null 2>&1; then
        return 0  # устройство доступно
    fi

    # Не удалось — пробуем обновить список устройств
    msg_info "Попытка обновления подключения к устройству..."
    kdeconnect-cli --refresh >/dev/null 2>&1
    sleep 1  # небольшой таймаут для обновления сети

    # Вторая попытка пинга
    if kdeconnect-cli -d "$device" --ping >/dev/null 2>&1; then
        return 0
    fi

    # Всё ещё недоступно
    msg_error "Устройство $device ($device_name) недоступно."
    msg_error "Проверьте подключение телефона к сети и запустите KDE Connect на телефоне."
    return 1
}



# -------------------------
# Функция проверки номера телефона: формат +380XXXXXXXXX и принадлежность
# номера одному из разрешённых кодов операторов (см. VALID_OPERATORS)
# $1      -- номер телефона для проверки
# Возврат -- 0, если номер валиден; 1, если формат неверный или оператор не разрешён
# -------------------------
is_valid_phone() {
    local phone="$1"

    # Проверяем общий формат: +380XXXXXXXXX
    if [[ ! "$phone" =~ ^\+380[0-9]{9}$ ]]; then
        return 1  # неверный формат
    fi

    # Извлекаем код оператора (три цифры после +380)
    local operator="${phone:3:3}"

    # Проверяем, есть ли код в массиве разрешённых операторов
    for valid in "${VALID_OPERATORS[@]}"; do
        if [[ "$operator" == "$valid" ]]; then
            return 0  # номер валиден
        fi
    done

    return 1  # оператор не разрешён
}



#
# ----------------------------------- MAIN ------------------------------------
#



#
# Чтение конфигурационного файла
#
read_config_file



# -------------------------
# Обработка специальных команд
# -------------------------
# Примечание: используем "${1:-}", а не "$1", т.к. при set -o nounset и
# запуске скрипта совсем без аргументов обращение к незаданному "$1"
# приводит к аварийному завершению с ошибкой "unbound variable".
case "${1:-}" in
    -h|--help)
        print_help
        exit 0
        ;;
        
    -u|--usage)
        print_usage
        exit 0
        ;;

    -v|--version)
        print_version
        exit 0
        ;;

    -ec|--edit-conf)
        echo "Редактирование конфига: ${CONFIG_FILE}"
        exec "${EDITOR}" "${CONFIG_FILE}"
        exit 0
        ;;

    -wc|--write-conf)
        echo "перезапись конфига по умолчанию: ${CONFIG_FILE}"
        save_config_file
        exit 0
        ;;

     --test-dev)
          echo -e "${PREFIX_INFO} Проверка подключения к устройству ${device} (${device_name})..."
          if is_connect_device; then
              exit_with_msg "Устройство доступно" 0
          else
              exit_with_msg "Устройство недоступно" 1
          fi
          ;;

     --test-api)
          msg_info "Проверка подключения к API..."
          if api_test; then
              exit_with_msg "API доступно" 0
          else
              exit_with_msg "API недоступно" 1
          fi
          ;;

     --test|--test-all)
          TEST=0
          msg_info "Проверка подключения к устройству ${device} (${device_name})..."
          if is_connect_device; then
              msg_ok "Устройство доступно"
          else
              msg_error "Устройство недоступно"
              TEST=1
          fi

          msg_info "Проверка подключения к API..."
          if api_test; then
              msg_ok "API доступно"
          else
              msg_error "API недоступно"
              TEST=1
          fi
          exit ${TEST};
          ;;


    --install)
        echo "Установка скрипта в указанное место: $2"
        install "$2"
        exit 0
        ;;
esac



#
# Проверка обязательных зависимостей
#
check_dependencies_required



#
# Номер телефона на который отправляется сообщение
# ("${1:-}" вместо "$1" — чтобы не падать с "unbound variable" при
#  set -o nounset, если скрипт запущен вовсе без аргументов)
#
phone="${1:-}"

#
#  Отправляемое соощение
#
msg="${2:-}"

#
#  Номер абонента для регистрации отправки сообщения в базе
#
abon_id="${3:-}"

#
#  Проверка наличия обязательных параметров командной строки
#
if [ -z "$phone" ] || [ -z "$msg" ]; then
    echo -e "${PREFIX_ERROR} Ошибка: не указаны обязательные параметры (PHONE: [$phone], MSG: [$msg])."
    echo -e "${PREFIX_INFO} Справка по использованию: ${COLOR_USAGE}${APP_NAME} -u|--usage|-h|--help${COLOR_OFF}"
    exit 1
fi

# -------------------------
# Проверка перед отправкой
# -------------------------

# Проверка формата номера телефона и принадлежности разрешённому оператору
if ! is_valid_phone "$phone"; then
    echo -e "${PREFIX_ERROR} Номер телефона '$phone' невалидный или не принадлежит разрешённому оператору."
    exit 1
fi


# Проверка подключённости устройства
if ! is_connect_device "$device"; then
    exit 1
fi

echo -e "${PREFIX_INFO} Адресат   : ${COLOR_INFO1}${phone} (через ${device_name} : ${device})${COLOR_OFF}"
echo -e "${PREFIX_INFO} Сообщение : ${COLOR_TEXT}${msg}${COLOR_OFF}"

#
# Отправка SMS через KDE Connect
#
kdeconnect-cli -d "${device}"  --destination "${phone}" --send-sms "${msg}"
rc=$?

if (( rc != 0 )); then
    echo -e "${PREFIX_ERROR} Ошибка отправки SMS через KDE Connect"
else
    echo -e "${PREFIX_OK} SMS отправлено"
fi

#
#  Регистрация отправки сообщения в базе
#
if [ -n "${abon_id}" ]; then

    echo -e "${PREFIX_INFO} Регистрация в базе"

    response=$(curl -s -w "%{http_code}" -X POST "${URL_API}" \
        -H "Authorization: Bearer ${TOKEN}" \
        -d "${F_CMD}=${CMD_SMS_REG}" \
        -d "${F_AID}=${abon_id}" \
        -d "${F_PHONE_NUMBER}=${phone}" \
        -d "${F_TEXT}=${msg}")

    rc=$?

    if (( rc != 0 )); then
        echo "Нет соединения с API"
        exit 1
    fi

    http_code="${response: -3}"       # последние 3 символа — код HTTP
    body="${response:0:-3}"           # остальное — тело ответа

    echo -en "$body"  # выводим сообщение от PHP

    if [[ $http_code -ge 200 && $http_code -lt 300 ]]; then
        echo -e "${PREFIX_OK} Сообщение зарегистрировано в базе"
    else
        echo -e "${PREFIX_ERROR} Ошибка записи в базу: код выхода ${http_code}"
    fi
else
    echo -e "${PREFIX_INFO} Без регистрации в базе"
fi

echo "(Ожидание отправки ${waitsending} сек.)"
sleep "${waitsending}"
echo "-------------------------------------------------------------------------"
