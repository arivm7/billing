<?php
/**
 *  Project : my.ri.net.ua
 *  File    : auto_off.php
 *  Path    : scripts/auto_off.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *
 *  Автоотключение абонентов по задолженности.
 *
 *  Логика разбита на функции, каждая с одной обязанностью:
 *    decide_action()   — чистый расчёт решения, без БД и побочных эффектов
 *    execute_action()  — выполняет действие (БД/пауза) и сама пишет в LOG_ACTION
 *    print_abon_row()  — только форматирует и печатает строку таблицы в stdout
 *    notify_admin() — уведомления администратора
 *
 *  License : GPL v3
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

const APP_NAME = "RI-BILLING";

use app\models\AbonModel;
use app\models\AuthModel;
use app\controllers\EmailController;
use billing\core\App;
use billing\core\base\Controller;
use config\tables\PA;
use config\tables\Abon;
use config\tables\User;
use config\tables\AbonRest;

require __DIR__    . '/../config/dirs.php';
require DIR_CONFIG . '/ini.php';
require DIR_LIBS   . '/common.php';
require DIR_LIBS   . '/functions.php';

const LOG_ACTION = 'auto_off_actions.log';

/**
 * Возможные решения decide_action(). Простые строковые константы —
 * без отдельного enum-класса, чтобы не плодить лишние файлы.
 */
const ACTION_SKIPPED_ZERO_TARIFF = 'SKIPPED_ZERO_TARIFF'; // sum_pp01a <= 0 — служебный/на паузе/контрагент
const ACTION_NONE                = 'NONE';                // prepayed >= duty_max_off — всё в порядке
const ACTION_WAIT_DECREMENTED    = 'WAIT_DECREMENTED';    // decrement duty_wait_days
const ACTION_PAUSED              = 'PAUSED';              // постановка на паузу

/**
 * Ширина колонки "адрес"
 */
const MAX_WIDTH_STR = 50;


/**
 * Расчёт решения о нужном действии.
 * Возвращааемые требуемые действия: 
 *     ACTION_NONE                -- действия нет
 *     ACTION_SKIPPED_ZERO_TARIFF -- действия нет
 *     ACTION_WAIT_DECREMENTED    -- уменьшить счётчик ожидания
 *     ACTION_PAUSED              -- поставить на паузу
 *
 * Ожидаемые ключи в $abon_data:
 *   duty_max_off, duty_wait_days, sum_pp01a, prepayed
 */
function decide_action(array $abon_data): string
{
    if ($abon_data['sum_pp01a'] <= 0) {
        return ACTION_SKIPPED_ZERO_TARIFF;
    }

    if ($abon_data['prepayed'] >= $abon_data['duty_max_off']) {
        return ACTION_NONE;
    }

    if ($abon_data['duty_wait_days'] > 0) {
        return ACTION_WAIT_DECREMENTED;
    }

    return ACTION_PAUSED;
}



/**
 * Административное уведомление администратора об ошибках или изменениях параметров абонента или услуги.
 * Получает данные абонента, если таковые есть, и текстовое сообщение, которое нужно отправить администратору.
 * Обычно это лог ошибки работы базы или лог действия изменения статуса абонента.
 */
function notify_admin(?array $abon_data, string $log_text, string &$log_send = ''): bool
{
    $to = EmailController::parse_mail_recipients(App::get_user()[User::F_EMAIL_MAIN]);
    
    $subject = 
            'RI-BILLING. '
            . ($abon_data
                ?   $abon_data[Abon::F_ABON_ID] . ' | ' . $abon_data[Abon::F_ADDRESS] . ' | ' . __('Operations with the subscriber’s personal account and connected services | Операции с лицевым счётом абонента и подключёнными услугами | Операції з особовим рахунком абонента та підключеними послугами')
                :   'ERROR. ' . __('Error in auto shutdown script | Ошибка в работе скрипта автоотключения | Помилка роботи скрипта автовідключення')
              );
    
    $body_text = 
            'RI-BILLING. ' . "\n"
            . ($abon_data
                ?   $abon_data[Abon::F_ABON_ID] . ' | ' . $abon_data[Abon::F_ADDRESS] . "\n" 
                    . __('Operations with the subscriber’s personal account and connected services | Операции с лицевым счётом абонента и подключёнными услугами | Операції з особовим рахунком абонента та підключеними послугами')
                :   __('Error in auto shutdown script | Ошибка в работе скрипта автоотключения | Помилка роботи скрипта автовідключення')
              ) . "\n"
            . "\n"
            . $log_text;
    
    $as_html = false;
    
    return EmailController::send(
            to: $to,
            subject: $subject,
            body_text: $body_text,
            as_html: $as_html,
            log: $log_send
        );
}



/**
 * Выполняет реальные изменения (БД / пауза) и сама же пишет
 * результат в лог-файл действий LOG_ACTION.
 *
 * Возвращает $abon_data, обогащённый:
 *   - duty_wait_days  — обновлено (после декремента), если действие его меняло
 *   - action_result   — '[N]' (осталось дней ожидания), '[X] SUCCESS'/'[X] ERROR'
 *                        (факт отключения), или '' если действий не было
 */
function execute_action(string $action, array $abon_data, AbonModel $model, string $log_filename): array
{
    $abon_id = $abon_data['abon_id'];
    $abon_data['action_result'] = '';

    switch ($action) {

        case ACTION_SKIPPED_ZERO_TARIFF:
        case ACTION_NONE:
            // Ничего не делать, в LOG_ACTION ничего не пишется, action_result остаётся пустым.
            break;

        case ACTION_WAIT_DECREMENTED:
            $new_wait_days = $abon_data['duty_wait_days'] - 1;

            $ok = $model->set_field_value(
                Abon::TABLE,
                Abon::F_ID,
                $abon_id,
                Abon::F_DUTY_WAIT_DAYS,
                $new_wait_days,
                false
            );

            log_action(sprintf('%8d', $abon_id) . " | Ожидание платежа: "
                    .   ($ok
                            ? "Осталось дней ожидания: {$new_wait_days}"
                            : "ОШИБКА: не удалось изменить количество дней ожидания платежа в базе"
                        ),
                $log_filename
            );

            $abon_data['duty_wait_days'] = $ok ? $new_wait_days : $abon_data['duty_wait_days'];
            $abon_data['action_result']  = '[' . sprintf('%02d', $abon_data['duty_wait_days']) . '] WAITING';
            break;

        case ACTION_PAUSED:
            $header = sprintf('%8d', $abon_id);

            /**
             * Фактическая установка услуги в паузу (Mikrotik + БД).
             * Явный булев результат — set_abon_pause() больше не требует
             * парсинга текста лога для определения успеха/неудачи.
             */
            $pause_log = '';
            $paused_ok = $model->set_abon_pause($abon_id, $pause_log);

            log_action($header . " | " . $pause_log, $log_filename);

            /**
             * Уведомление администратора — пока заглушка.
             * Её результат НЕ влияет на факт постановки на паузу —
             * пауза уже необратимо произошла к этому моменту.
             */
            $notify_log = '';
            $notified = notify_admin($abon_data, $header . "\n" . $pause_log, $notify_log);

            log_action($header . " | Отправка уведомления администратору: " 
                    . ($notified ? "SUCCESS" : "ERROR")
                    . ($notify_log ? "\n" . $notify_log : ""),
                $log_filename
            );

            $abon_data['action_result'] = '[X] ' . ($paused_ok ? 'SUCCESS' : 'ERROR');
            break;

    }

    return $abon_data;
}



/**
 * Обёртка над Controller::log() — единственное место, знающее,
 * как физически пишется строка в LOG_ACTION.
 */
function log_action(string $msg, string $log_filename): void
{
    Controller::log(
        msg: $msg,
        eol_cr: 1,
        log_filename: $log_filename,
        log_url: 0,
        log_ip: 0
    );
}


/**
 * Очистка и подготовка адреса для колонки фиксированной ширины
 * (перенесено без изменений из исходного скрипта).
 */
function clean_address(string $address): string
{
    $address = html_entity_decode($address);
    $address = str_replace(search: '"', replace: "`", subject: $address);
    $address = str_replace(search: "'", replace: "`", subject: $address);
    $address = str_replace(search: "<", replace: "`", subject: $address);
    $address = str_replace(search: ">", replace: "`", subject: $address);

    if (iconv_strlen($address, "UTF-8") > MAX_WIDTH_STR) {
        $address = "<" . mb_substr($address, -(MAX_WIDTH_STR - 1), MAX_WIDTH_STR - 1);
    } else {
        while (iconv_strlen($address, "UTF-8") < MAX_WIDTH_STR) {
            $address .= " ";
        }
    }
    return $address;
}


function print_table_header(): void
{
    echo ""
        . ".------------" . str_repeat("-", MAX_WIDTH_STR)              . "-.-------.-----------.---------.--------------.\n"
        . "|  abon_id | " . sprintf("%-".MAX_WIDTH_STR."s", "Адрес") . "      | PP01A | Опл. дней | граница |   действие   |\n"
        . "|----------|-" . str_repeat("-", MAX_WIDTH_STR)              . "-|-------|-----------|---------|--------------|\n";
}



/**
 * Печатает строку основной таблицы для одного абонента.
 * Только форматирование — не принимает решений, не смотрит на $action,
 * просто выводит то, что уже лежит в $abon_data (включая action_result).
 */
function print_abon_row(array $abon_data): void
{
    echo "| " . sprintf('%8d', $abon_data['abon_id']) . " | "
       . sprintf("%-".MAX_WIDTH_STR."s", $abon_data['address']) . " | "
       . sprintf('%5.2f', $abon_data['sum_pp01a']) . " | "
       . sprintf('%9.2f', $abon_data['prepayed']) . " | "
       . sprintf('%7d', $abon_data['duty_max_off']) . " | "
       . sprintf('%-12s', $abon_data['action_result']) . " |\n";
}



function print_table_footer(): void
{
    echo ""
        . " ---------- -" . str_repeat("-", MAX_WIDTH_STR)              . "- ------- ----------- --------- -------------- \n\n";
}



// ============================================================================
// 
//                                Точка входа
//  
// ============================================================================



/**
 * Автозагрузчик Composer'а
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 *  Инициализация Реестра App::$app
 */
new App;

/**
 * Авторизуемся от пользователя billling
 */
$conf  = require DIR_CONFIG . '/config_secret.php';
$token = $conf['token'];

$model = new AuthModel();
$model->login_by_token($token);

echo "\n";
echo "AUTO_OFF | " . date("Y-m-d G:i:s") . " | " . time() . " | " . App::get_user_id() . ' | ' . App::get_user()[User::F_NAME_SHORT] . " \n";

unset($model);
$model = new AbonModel();

$SQL = "SELECT
            ".Abon::TABLE.".".Abon::F_ID." AS ".Abon::F_ABON_ID.",
            ".Abon::TABLE.".".Abon::F_ADDRESS.",
            ".Abon::TABLE.".".Abon::F_DUTY_MAX_OFF.",
            ".Abon::TABLE.".".Abon::F_DUTY_WAIT_DAYS."
        FROM
            ".PA::TABLE."
            LEFT JOIN ".Abon::TABLE." ON ".Abon::TABLE.".".Abon::F_ID." = ".PA::TABLE.".".PA::F_ABON_ID."
        WHERE
        ( ".Abon::TABLE.".".Abon::F_IS_PAYER." = 1 )
        AND
        ( ".Abon::TABLE.".".Abon::F_DUTY_AUTO_OFF." = 1 )
        AND
        (
            (
                (".PA::TABLE.".".PA::F_DATE_START." < UNIX_TIMESTAMP()) AND
                (isnull(".PA::TABLE.".".PA::F_DATE_END."))
            )
            OR
            (
                ".PA::TABLE.".".PA::F_DATE_END." > UNIX_TIMESTAMP()
            )
        )
        GROUP BY ".Abon::TABLE.".".Abon::F_ID."
        ";

try {
    $alist = $model->get_rows_by_sql($SQL);
} catch (Exception $exc) {
    echo "Ошибка запроса списка абонентов " . $exc->getMessage() . "\n";
    $notify_log = '';
    notify_admin(
            null,
            "Ошибка запроса списка абонентов \n" 
            . "ОШИБКА:\n" . $exc->getMessage() . "\n"
            . "ТРАССА:\n" . print_r($exc->getTraceAsString(), true) . "\n",
            $notify_log
        );
    if ($notify_log) {
        echo $notify_log . "\n";
    }
    exit;
}

$count_AID = count($alist);
echo "Строк: " . $count_AID . "\n";

print_table_header();

foreach ($alist as $abon_row) {
    $abon_id = (int) $abon_row[Abon::F_ABON_ID];
    $address = clean_address((string) ($abon_row[Abon::F_ADDRESS] ?? ''));

    /**
     * Обновление остатков — только для текущего абонента.
     * Ошибка здесь касается только его одного, следующий абонент
     * обрабатывается со своим собственным обновлением независимо.
     */
    try {
        
        /**
         * Обновление остатков для абонента
         */
        $rest_update_ok = $model->update_abon_rest_all($abon_id);
        if (!$rest_update_ok) {
            $msg = "| " . sprintf('%8d', $abon_id) . " | " . sprintf("%-".MAX_WIDTH_STR."s", $address) . " | "
                . 'REST: Ошибка обновления остатков для абонента ' . "\n";
            echo $msg;
            $notify_log = '';
            notify_admin(null, $msg, $notify_log);
            if ($notify_log) {
                echo $notify_log . "\n";
            }
            continue;
        }
        $rest = $model->get_abon_rest($abon_id);
        
    } catch (Exception $exc) {
        $msg = "| " . sprintf('%8d', $abon_id) . " | " . sprintf("%-".MAX_WIDTH_STR."s", $address) . " | "
            . 'Ошибка обращения к базе ' . "\n"
            . $exc->getMessage() . "\n"
            . print_r($exc->getTraceAsString(), true);
        echo $msg;
        $notify_log = '';
        notify_admin(null, $msg, $notify_log);
        if ($notify_log) {
            echo $notify_log . "\n";
        }
        continue;
    }

    /**
     * Единая структура данных абонента — для decide_action() и для print_abon_row(). 
     */
    $abon_data = [
        'abon_id'        => $abon_id,
        'address'        => $address,
        'duty_max_off'   => (int)   $abon_row[Abon::F_DUTY_MAX_OFF],
        'duty_wait_days' => (int)   $abon_row[Abon::F_DUTY_WAIT_DAYS],
        'sum_pp01a'      => (float) ($rest[AbonRest::F_SUM_PP01A] ?? 0),
        'prepayed'       => (float) ($rest[AbonRest::F_PREPAYED]  ?? 0),
    ];

    $action = decide_action($abon_data);
    $abon_data = execute_action($action, $abon_data, $model, LOG_ACTION);
    print_abon_row($abon_data);

    flush();
}

print_table_footer();
