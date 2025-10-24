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



use billing\core\base\Lang;

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