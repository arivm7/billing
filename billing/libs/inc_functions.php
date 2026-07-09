<?php
/**
 *  Project : my.ri.net.ua
 *  File    : inc_functions.php
 *  Path    : billing/libs/inc_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 23 Oct 2025 01:11:27
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of inc_functions.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use app\models\AbonModel;
use billing\core\Api;
use billing\core\base\Lang;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Icons;
use config\tables\Module;
use config\tables\PA;
use config\tables\TP;

require_once DIR_LIBS . '/billing_functions.php';
require_once DIR_LIBS . '/datetime_functions.php';
require_once DIR_LIBS . '/billing_functions.php';



const PAStatusDescription = [
    PAStatus::FUTURE->value => [
        'en' => 'Future',
        'ru' => 'Будущий',
        'uk' => 'Майбутній',
    ],
    PAStatus::CURRENT->value => [
        'en' => 'Working',
        'ru' => 'Действующий',
        'uk' => 'Діючий',
    ],
    PAStatus::PAUSE_TODAY->value => [
        'en' => 'Pause today',
        'ru' => 'Пауза сегодня',
        'uk' => 'Пауза сьогодні',
    ],
    PAStatus::PAUSE->value => [
        'en' => 'Paused',
        'ru' => 'Пауза',
        'uk' => 'Пауза',
    ],
    PAStatus::CLOSED->value => [
        'en' => 'Closed',
        'ru' => 'Закрыт',
        'uk' => 'Закритий',
    ],
];



/**
 * Предупреждающие атрибуты для отображения статусов дежурств
 */
const BADGE_PRIMARY="badge text-bg-primary";
const BADGE_SECONDARY="badge text-bg-secondary";
const BADGE_SUCCESS="badge text-bg-success";
const BADGE_DANGER="badge text-bg-danger";
const BADGE_WARNING="badge text-bg-warning";
const BADGE_INFO="badge text-bg-info";
const BADGE_LIGHT="badge text-bg-light";
const BADGE_DARK="badge text-bg-dark";



function get_html_pa_status(PAStatus $status): string {
    return match ($status) {
        PAStatus::FUTURE => "<span class='" . BADGE_SUCCESS . "'>" . PAStatusDescription[PAStatus::FUTURE->value][Lang::code()] . "</span>",
        PAStatus::CURRENT => "<span class='" . BADGE_SUCCESS . "'>" . PAStatusDescription[PAStatus::CURRENT->value][Lang::code()] . "</span>",
        PAStatus::PAUSE_TODAY => "<span class='" . BADGE_WARNING . "'>" . PAStatusDescription[PAStatus::PAUSE_TODAY->value][Lang::code()] . "</span>",
        PAStatus::PAUSE => "<span class='" . BADGE_WARNING . "'>" . PAStatusDescription[PAStatus::PAUSE->value][Lang::code()] . "</span>",
        PAStatus::CLOSED => "<span class='" . BADGE_SECONDARY . "'>" . PAStatusDescription[PAStatus::CLOSED->value][Lang::code()] . "</span>",
    };
}



function get_html_abon_ip_status(bool|null $status): string {
    if ($status === null) {
        $html = "<img src='".Icons::SRC_ICON_MIK_OFF."' alt='[-]' height='24rem' title='".__('No data from Mikrotik | Нет данных с микротика | Немає даних з мікротика')."'></img>";
    } elseif ($status === true) {
        $html = "<img src='".Icons::SRC_ICON_MIK_ABON_IP_ON."' alt='[On]' height='24rem' title='".__('IP is available and allowed | IP есть и разрешён | IP є і дозволена')."'></img>";
    } else {
        $html = "<img src='".Icons::SRC_ICON_MIK_ABON_IP_OFF_RED."' alt='[Off]' height='24rem' title='".__('There is an IP. Prohibited | IP есть. Запрещён | IP є. Заборонена')."'></img>";
    }
    return $html;
}



function get_html_btn_abon_ip_turn(int $tp_id, string $ip, bool|int $enable, string $title = '', string $options = 'class="btn"', string $target = '_self'): string {

    // ?cmd=set_tp_abon_ip_disabled
    // &tp_id=68
    // &ip=10.1.4.161
    // &disabled=0


    $query =  http_build_query(
        [
            Api::F_CMD      => Api::CMD_IP_ENABLE,
            Api::F_TP_ID    => $tp_id,
            Api::F_IP       => $ip,
            Api::F_ENABLED  => $enable ? 1 : 0,
        ],
        "", null, PHP_QUERY_RFC1738 // PHP_QUERY_RFC3986
    );

    $title = ($title ?: ($enable ? __('Including an IP address in the ABON table on Mikrotik | Включение IP-адреса в таблице ABON на микротике | Увімкнення IP-адреси в таблиці ABON на мікротиці') : __('Disabling an IP address in the ABON table on Mikrotik | Отключение IP-адреса в таблице ABON на микротике | Відключення IP-адреси у таблиці ABON на мікротиці')));
    $src = ($enable ? Icons::SRC_ICON_MIK_ABON_IP_TURN_ON : Icons::SRC_ICON_MIK_ABON_IP_TURN_OFF);
    $alt = ($enable ? '[On]' : '[Off]');

    $html = "<a ".($options ?:'')." href='".Api::URI_CMD."?{$query}' title='{$title}' target='{$target}'>"
                ."<img src='{$src}' alt='{$alt}' height='24rem'></img>"
            ."</a>";
    return $html;
}



/**
 * Генерация кнопок для работы с услугой (ПФ + Мик)
 * пауза, снаятие с паузы, форсированное включение
 * @param int|null $pa_id
 * @param array|null $pa
 * @param bool|int $ena
 * @param bool|int $force
 * @param string $title
 * @param string $options
 * @param string $target
 * @return string
 */
function get_html_btn_serv_ena(int|null $pa_id = null, array|null $pa = null, bool|int $ena = 1, bool|int $force = 0, string $title = '', string $options = 'class="btn"', string $target = '_self'): string 
{
    global $TODAY;

    if (empty($pa)) {
        $model = new AbonModel();
        $pa = $model->get_pa($pa_id);
        if (empty($pa)) {
            // throw new Exception("PA ID No Valid");
            MsgQueue::msg(MsgType::ERROR, __('Price fragment is not correct | Прайсовый фрагмент не верен | Прайсовий фрагмент не вірний'));
            if (can_use(Module::MOD_WEB_DEBUG)) {
                MsgQueue::msg(MsgType::ERROR, "pa_id: [{$pa_id}]");
                if (is_array($pa)) {
                    MsgQueue::msg(MsgType::ERROR, "PA:");
                    MsgQueue::msg(MsgType::ERROR, $pa);
                }
            }
            return '';
        }
    }

    if  (
            empty($pa[PA::F_ID]) 
            || empty($pa[PA::F_TP_ID])
         // || empty($pa[PA::F_NET_IP]) || !validate_ip($pa[PA::F_NET_IP])
        )
    {
        // throw new Exception("PA Struct No Valid");
        MsgQueue::msg(MsgType::ERROR, __('Price fragment structure error | Ошибка структуры прайсового фрагмента | Помилка структури прайсового фрагменту'));
        if (can_use(Module::MOD_WEB_DEBUG)) {
            MsgQueue::msg(MsgType::ERROR, __('Validated fields | Проверяемые поля | Перевірені поля') . ':');
            MsgQueue::msg(MsgType::ERROR, PA::F_ID . ': ' . $pa[PA::F_ID]);
            MsgQueue::msg(MsgType::ERROR, PA::F_TP_ID . ': ' . $pa[PA::F_TP_ID]);
            // MsgQueue::msg(MsgType::ERROR, PA::F_NET_IP . ': ' . $pa[PA::F_NET_IP] . ' (с валидацией)');
        }
        return '';
    }

    // ?cmd=set_abon_pause
    // &tp_id=68
    // &prices_apply_id=2760
    // &date_end=1762390800
    // &ip=10.1.1.125
    // &disabled=1

    $query =  http_build_query(
        [
            Api::F_CMD      => Api::CMD_SERV_ENA,
            Api::F_PA_ID    => $pa[PA::F_ID],
            Api::F_FORCE    => $force ? 1 : 0,
            Api::F_ENABLED  => $ena ? 1 : 0,
        ],
        "", null, PHP_QUERY_RFC1738 // PHP_QUERY_RFC3986
    );

    $title = ($title ?: 
        ($ena 
            ?   ($force 
                    ? __('Force enable service | Форсированно включить услугу | Примусово увімкнути послугу') . ":" . CR
                        . "1. " . __('Reset date_end field | Обнулить поле date_end | Обнулити поле date_end') . CR
                        . "2. " . __('Activate IP [%s] on MikroTik | Активировать IP [%s] на микротике | Активувати IP [%s] на MikroTik', ($pa[PA::F_NET_IP] ?: "")) . " " . CR
                        . __('Important | Важно | Важливо') . ": ". __('Ignores pause days count | Игнорирует количество дней паузы | Ігнорує кількість днів паузи') ."." . CR
                        . __('Use carefully to avoid billing issues | Использовать с осторожностью, чтобы не нарушать начисление | Використовувати обережно, щоб не порушити нарахування') . "."
                    : __("Cancel pause — reactivate this tariff: | Отменить паузу -- снова активировать этот прайс: | Скасувати паузу -- знову активувати цей тариф:".CR
                        . "1. " . __('Reset date_end field | Обнулить поле date_end | Обнулити поле date_end') .CR
                        . "2. " . __('Activate IP [%s] on MikroTik | Активировать IP [%s] на микротике | Активувати IP [%s] на MikroTik', ($pa[PA::F_NET_IP] ?: "")) . " " . CR
                        . __('Use for recently closed tariff (not more than %s tariff days) | Использовать для недавно закрытого прайса (не более %s прайсовых дней) | Використовувати для нещодавно закритого тарифу (не більше %s тарифних днів)', UNPAUSED_DAYS_ENABLE) . ", " . CR
                        . __('to avoid billing issues | чтобы не нарушать начисление | щоб не порушити нарахування') . ".")
                )
            : __('Pause now | Поставить на паузу сейчас | Поставити на паузу зараз') . ': ' . CR
                . '1. ' . __('Closes current tariff fragment | Закрывает текущий прайсовый фрагмент | Закриває поточний тарифний фрагмент') . CR
                . '2. ' . __('Disables IP address on technical platform if possible | отключает IP адрес на ТП, по возможности | відключає IP адресу на ТП, по можливості') . '.'
        ));

    $src = ($ena ? ($force ? Icons::SRC_UNPAUSE_FORCE : Icons::SRC_UNPAUSE) : Icons::SRC_PAUSE);
    $alt = ($ena ? ($force ? "⏩" : "⏯") : "⏸"); // [On] [Off]

    $html = "<a ".($options ?:'')." href='".PA::URI_ENABLE."?{$query}' title='{$title}' target='{$target}'>"
                ."<img src='{$src}' alt='{$alt}' height='24rem'></img>"
            ."</a>";
    return $html;
}



function get_html_btn_clone(int|null $pa_id = null, string $title = '', string $options = 'class="btn"', string $target = '_self'): string {

    // ?cmd=price_apply_open_clone
    // &cloned_price_apply_id=2760

    $query =  http_build_query(
        [
            Api::F_CMD      => Api::CMD_PA_CLONE,
            Api::F_PA_ID    => $pa_id,
        ],
        "", null, PHP_QUERY_RFC1738 // PHP_QUERY_RFC3986
    );

    $title = ($title ?: 
        __('Clone this tariff | Клонировать этот прайс | Клонувати цей тариф') . ". " . CR
        . __('Create an open tariff fragment activated by current date | Создать открытый прайсовый фрагмент, активированный текущей датой | Створити відкритий тарифний фрагмент, активований поточною датою') . CR
        . __('with network parameters of this fragment | с сетевыми параметрами этого фрагмента | з мережевими параметрами цього фрагмента') . CR
        . __('without changing parameters on the technical platform | без изменения параметров на технической площадке | без зміни параметрів на технічному майданчику') . "."    
    );
    
    $src = Icons::SRC_CLONE;
    $alt = '[Clone]';

    $html = "<a ".($options ?:'')." href='".Api::URI_CMD."?{$query}' title='{$title}' target='{$target}'>"
                ."<img src='{$src}' alt='{$alt}' height='24rem'></img>"
            ."</a>";
    return $html;
}


function get_html_btn_pa_delete(int|null $pa_id = null, string $title = '', string $options = 'class="btn"', string $target = '_self'): string {

    // ?cmd=price_apply_open_clone
    // &cloned_price_apply_id=2760

    $query =  http_build_query(
        [
            Api::F_CMD      => Api::CMD_PA_DELETE,
            Api::F_PA_ID    => $pa_id,
        ],
        "", null, PHP_QUERY_RFC1738 // PHP_QUERY_RFC3986
    );

    $title = ($title ?: 
        __('Delete this tariff fragment | Удалить этот прайсовый фрагмент | Видалити цей тарифний фрагмент') . ". " . CR
        . __('Affects service billing. Better not press this | Влияет на начисление услуги. Лучше это не нажимать | Впливає на нарахування послуги. Краще це не натискати') . "."
    );
    
    $src = Icons::SRC_DELETE;
    $alt = '[x]';

    $html = "<a ".($options ?:'')." href='".Api::URI_CMD."?{$query}' title='{$title}' target='{$target}' "
                . "onclick=\"return confirm(".__('Confirm deletion of tariff fragment. Affects service billing | Подтвердите удаление прайсового фрагмента. Влияет на начисление за услуги | Підтвердіть видалення тарифного фрагмента. Впливає на нарахування за послуги').".');\""
                . ">"
                ."<img src='{$src}' alt='{$alt}' height='24rem'></img>"
                . "</a>";
            
    return $html;
}



function url_pay_form(int $id): string {
    $model = new AbonModel();
    return $model->url_pay_form($id);
}



const BADGE_NA          = BADGE_SECONDARY;

const STATUSES = [
    1 => BADGE_DANGER,
    2 => BADGE_WARNING,
    3 => BADGE_SUCCESS,
    4 => BADGE_INFO,
    5 => BADGE_PRIMARY,
    6 => BADGE_SECONDARY,
    7 => BADGE_LIGHT,
    8 => BADGE_DARK,
];

/**
 * Функция генерации HTML-бейджа (значка) с заданным текстом и цветовым статусом
 * 
 * @param string $text Текст, который будет отображаться в бейдже
 * @param int|string $sign Ключ для определения цветового статуса из массива $statuses
 * @param array $statuses Массив соответствия ключей к классам статусов (по умолчанию используется константа STATUSES)
 * @return string Возвращает HTML-строку с тегом span и соответствующим CSS-классом
 */
function html_badge(string $text, int|string $sign, array $statuses = STATUSES, string $bage_na = BADGE_NA, string $title = ''): string {

    // Получаем значение статуса из переданного массива по ключу $sign или используем значение по умолчанию BADGE_NA
    $warn = $statuses[$sign] ?? $bage_na;
    // Формируем HTML-тег span с присвоением CSS-класса статуса и отображаемым текстом
    $s = '<span class="'.$warn.'" '.(!empty($title) ? 'title="'.$title.'"' : "").' >'.$text.'</span>';
    // Возвращаем сформированную строку
    return $s;

}



function status_ip_abon_img(null|bool $state): string {
    $width = 22;
    $s = '';
    switch (true) {
        
        case $state === null:
            $s .= '<img src="'.Icons::SRC_ICON_MIK_ABON_IP_OFF.'" alt="NO" width='.$width.' title="'.__('The IP address is not in the ABON table | IP адреса нет в таблице ABON | IP адреси немає в таблиці ABON').'">';
            break;

        case $state === true:
            $s .= '<img src="'.Icons::SRC_ICON_MIK_ABON_IP_ON.'" alt="ON" width='.$width.' title="'.__('The IP address is in the ABON table and is active | IP адрес есть в таблице ABON и активен | IP адреса є в таблиці ABON і активна').'">';
            break;

        case $state === false:
            $s .= '<img src="'.Icons::SRC_ICON_MIK_ABON_IP_OFF_RED.'" alt="OFF" width='.$width.' title="'.__('The IP address is in the ABON table and is disabled | IP адрес есть в таблице ABON и отключён | IP адреса є в таблиці ABON і відключена').'">';
            break;

        default:
            break;
    }
    return $s;
}