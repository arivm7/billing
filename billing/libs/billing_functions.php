<?php
/**
 *  Project : my.ri.net.ua
 *  File    : billing_functions.php
 *  Path    : billing/libs/billing_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 23 Oct 2025 01:11:27
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of billing_functions.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\PA;
use config\tables\AbonRest;

require_once DIR_LIBS . '/common.php';


/**
 * Статус прикрепленных прайсовых фрагментов
 * PAUSE, CURRENT, FUTURE
 */
enum PAStatus: int  {
    case FUTURE         = 0b10000000;
    case CURRENT        = 0b01000000;
    case PAUSE_TODAY    = 0b00100000;
    case PAUSE          = 0b00010000;
    case CLOSED         = 0b00001000;
    case ACTIVE         = self::FUTURE->value | self::CURRENT->value | self::PAUSE_TODAY->value;
}



/**
 * Возвращает статус прайсового фрагмента, относительно указанной даты
 * Статусы: закрытый, текущий, будущий.
 * @param  array $price_apply
 * @param  int $today
 * @return PAStatus -- PRICES_APPLY_FUTURE, PRICES_APPLY_CURRENT, PRICES_APPLY_CLOSED
 */
 function get_price_apply_age(array $price_apply, int $today = NA): PAStatus {

    if($today === NA) { $today = TODAY(); } else { $today = get_date($today); }
//        $yesterday  = mktime(0, 0, 0, month($today), day($today)-1, year($today));
//        $tomorrow   = mktime(0, 0, 0, month($today), day($today)+1, year($today));

    // убрать время, оставить только дату
    $price_apply[PA::F_DATE_START] = date_only($price_apply[PA::F_DATE_START]);
    $price_apply[PA::F_DATE_END]   = (($price_apply[PA::F_DATE_END] > 0) ? date_only($price_apply[PA::F_DATE_END]) : 0);

//        debug([
//            'today' => $today,
//            'START' => $price_apply[PA::F_DATE_START],
//            '__END' => $price_apply[PA::F_DATE_END]
//        ], '$today', debug_view: DebugView::PRINTR);

    if  (   // залочка "закрыт"
            $price_apply[PA::F_CLOSED]
        )
    {
        return PAStatus::CLOSED;
    }

    if  (   // завтра
            $price_apply[PA::F_DATE_START] > $today
        )
    {
        return PAStatus::FUTURE;
    }

    if  (   // сегодня поставлен на паузу
            ($price_apply[PA::F_DATE_END] == $today) &&
            ($price_apply[PA::F_DATE_START] <= $today)
        )
    {
        return PAStatus::PAUSE_TODAY;
    }

    if  (   // сегодня
            ($price_apply[PA::F_DATE_END] >= $today) ||
            (($price_apply[PA::F_DATE_START] <= $today) && is_empty($price_apply[PA::F_DATE_END]))
        )
    {
        return PAStatus::CURRENT;
    }

    if  (   // вчера
            ($price_apply[PA::F_DATE_START] < $today) &&
            ($price_apply[PA::F_DATE_END]   >  0) && ($price_apply[PA::F_DATE_END] < $today)
        )
    {
        return PAStatus::PAUSE;
    }

    echo "get_prices_apply_age:<br>этого не должно быть<br><pre>". print_r($price_apply, true)."</pre><hr>";
    echo "start: ".$price_apply['date_start']." ".date("Y-m-d H:i:s", $price_apply['date_start'])."<br>";
    echo "end: ".$price_apply['date_end']." >= ".date("Y-m-d H:i:s", $price_apply['date_end'])."<br>";
    throw new \Exception("get_prices_apply_age:<br>этого не должно быть<br><pre>". print_r($price_apply, true)."</pre>");
    // return NA;
}



function get_pa_list_age(array $pa_list): PAStatus {
    $status = PAStatus::CLOSED;
    foreach ($pa_list as $pa) {
        $pa_status = get_price_apply_age($pa);
        switch ($pa_status) {
            case PAStatus::FUTURE:
            case PAStatus::CURRENT:
                $status = $pa_status; 
                break 2;
            default:
                if ($pa_status > $status) {
                    $status = $pa_status; 
                }
                break;
        }
    }
    return $status;
}



/**
 * Добавляет в ассоциативный массив записи поля:
 *   F_SUM_PP30A    -- Активная абонплата за 30 дней
 *   F_SUM_PP01A    -- Активная абонплата за 1 день
 *   F_REST         -- Остаток на лицевом счету
 *   F_PREPAYED     -- Количество предоплаченных дней
 * @param array $rest -- Ассоциативный массив записи абонента с добавленными базовыми границами (abon_rest)
 * @return void
 */
function update_rest_fields(array &$rest): void {

    /**
     * Активная абонплата за 30 дней
     */
    $rest[AbonRest::F_SUM_PP30A] = floatval($rest[AbonRest::F_SUM_PPDA] * 30.0 + $rest[AbonRest::F_SUM_PPMA]);

    /**
     * Активная абонплата за 1 день
     */
    $rest[AbonRest::F_SUM_PP01A] = floatval($rest[AbonRest::F_SUM_PPMA] / 30.0 + $rest[AbonRest::F_SUM_PPDA]);

    /**
     * Остаток на лицевом счету
     */
    $rest[AbonRest::F_REST] = floatval($rest[AbonRest::F_SUM_PAY] - $rest[AbonRest::F_SUM_COST]);

    /**
     * Количество предоплаченных дней
     */
    $rest[AbonRest::F_PREPAYED] = (cmp_float($rest[AbonRest::F_SUM_PP01A], 0) == 0 ? 0 : intval($rest[AbonRest::F_REST] / $rest[AbonRest::F_SUM_PP01A]));

    /**
     * Рекомендуемая к оплате сумма
     */
    $rest[AbonRest::F_AMOUNT] = 
        (
            $rest[AbonRest::F_REST] >= 0
                ? ceil(floatval($rest[AbonRest::F_SUM_PP01A] * days_of_month()) / 10) * 10 // округление до 10-ти вверх
                : ceil((floatval($rest[AbonRest::F_SUM_PP01A] * days_of_month()) - $rest[AbonRest::F_REST]) / 10 ) * 10 // округление до 10-ти вверх
        );
}



