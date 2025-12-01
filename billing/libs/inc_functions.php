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

require_once DIR_LIBS . '/billing_functions.php';
require_once DIR_LIBS . '/datetime_functions.php';
require_once DIR_LIBS . '/billing_functions.php';



const PAStatusDescription = [
    PAStatus::FUTURE->value => [
        'en' => 'Future',
        'ru' => 'Будет',
        'uk' => 'Буде',
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
        $html = "<img src='".Icons::SRC_ICON_MIK_OFF."' alt='[-]' height='24rem' title='Нет данных с микротика'></img>";
    } elseif ($status === true) {
        $html = "<img src='".Icons::SRC_ICON_MIK_ABON_IP_ON."' alt='[On]' height='24rem' title='IP есть и разрешён'></img>";
    } else {
        $html = "<img src='".Icons::SRC_ICON_MIK_ABON_IP_OFF_RED."' alt='[Off]' height='24rem' title='IP есть. Запрещён'></img>";
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

    $title = ($title ?: ($enable ? __('Включение IP-адреса в таблице ABON на микротике') : __('Отключение IP-адреса в таблице ABON на микротике')));
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
function get_html_btn_serv_ena(int|null $pa_id = null, array|null $pa = null, bool|int $ena = 1, bool|int $force = 0, string $title = '', string $options = 'class="btn"', string $target = '_self'): string {
    global $TODAY;

    if (empty($pa)) {
        $model = new AbonModel();
        $pa = $model->get_pa($pa_id);
        if (empty($pa)) {
            // throw new Exception("PA ID No Valid");
            MsgQueue::msg(MsgType::ERROR, __('Прайсовый фрагмент не верен'));
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
        MsgQueue::msg(MsgType::ERROR, __('Ошибка структуры прайсового фрагмента'));
        if (can_use(Module::MOD_WEB_DEBUG)) {
            MsgQueue::msg(MsgType::ERROR, __('Проверяемые поля:'));
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
                    ? __("Форсированно включить услугу:".CR
                        . "1. Обнулить поле date_end".CR
                        . "2. Активировать IP " . ($pa[PA::F_NET_IP] ?: "")." на микротике".CR
                        . "Игнорирует количество дней паузы.".CR
                        . "Использовать с осторожностью, чтобы не нарушать начисление.")
                    : __("Отменить паузу -- снова активировать этот прайс:".CR
                        . "1. Обнулить поле date_end".CR
                        . "2. Активировать IP " . ($pa[PA::F_NET_IP] ?: "")." на микротике".CR
                        . "Использовать для недавно закрытого прайса (не более ".UNPAUSED_DAYS_ENABLE." прайсовых дней), ".CR
                        . "чтобы не нарушать начисление.")
                )
            : __('Поставить на паузу сейчас: '.CR
                . '1. Закрывает текущий прайсовый фрагмент '.CR
                . '2. отключает IP адрес на ТП, по возможности.') 
        ));

    $src = ($ena ? ($force ? Icons::SRC_UNPAUSE_FORCE : Icons::SRC_UNPAUSE) : Icons::SRC_PAUSE);
    $alt = ($ena ? ($force ? "⏩" : "⏯") : "⏸"); // [On] [Off]

    $html = "<a ".($options ?:'')." href='".Api::URI_CMD."?{$query}' title='{$title}' target='{$target}'>"
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
        "Клонировать этот прайс. " . CR
        . "Создать открытый прайсовый фрагмент, активированный текущей датой " . CR
        . "с сетевыми параметрами этого фрагмента " . CR
        . "без изменения параметров на ТП."
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
        "Удалить этот прайсовый фрагмент. " . CR
        . "Влияет на начисление услуги, лучше это не нажимать."
    );
    
    $src = Icons::SRC_DELETE;
    $alt = '[x]';

    $html = "<a ".($options ?:'')." href='".Api::URI_CMD."?{$query}' title='{$title}' target='{$target}' "
                . "onclick=\"return confirm('Подтвердите удаление прайсового фрагмента. Влияет на начисление за услуги.');\""
                . ">"
                ."<img src='{$src}' alt='{$alt}' height='24rem'></img>"
                . "</a>";
            
    return $html;
}


