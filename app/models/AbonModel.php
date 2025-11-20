<?php 
/*
 *  Project : my.ri.net.ua
 *  File    : AbonModel.php
 *  Path    : app/models/AbonModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use AbonStatus;
use AbonStatusTitle;
use billing\core\App;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Icons;
use config\Mik;
use config\tables\Abon;
use config\tables\User;
use config\tables\Notify;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\Price;
use config\tables\TSUserTp;
use MikAbonStatus;
use PAStatus;
use config\SessionFields;
use config\tables\TP;
use config\tables\AbonRest;
use config\tables\PA;
use ServiceType;
use billing\core\base\Lang;

require_once DIR_LIBS . '/billing_functions.php';


/**
 * Description of AbonModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AbonModel extends UserModel {


    /**
     * Заполнить таблицу остатков `abon_rest` на ЛС у всех абонентов
     * собираем вместе поля: abon_id, sum_pay, sum_cost, sum_PPMA, sum_PPDA
     * @param bool $force
     * @return bool
     */
    function update_abon_rest_all(int|null $abon_id = null): bool {
        $sql = "
            INSERT INTO ".AbonRest::TABLE." (
                ".AbonRest::F_ABON_ID.",
                ".AbonRest::F_SUM_PAY.",
                ".AbonRest::F_SUM_COST.",
                ".AbonRest::F_SUM_PPMA.",
                ".AbonRest::F_SUM_PPDA."
            )
            SELECT
                a.".Pay::F_ABON_ID.",
                IFNULL( p.".AbonRest::F_SUM_PAY .", 0) AS ".AbonRest::F_SUM_PAY .",
                IFNULL(pa.".AbonRest::F_SUM_COST.", 0) AS ".AbonRest::F_SUM_COST.",
                IFNULL(pa.".AbonRest::F_SUM_PPMA.", 0) AS ".AbonRest::F_SUM_PPMA.",
                IFNULL(pa.".AbonRest::F_SUM_PPDA.", 0) AS ".AbonRest::F_SUM_PPDA."
            FROM
            ( "
                . (is_null($abon_id)

                    ?   "SELECT ".Pay::F_ABON_ID." AS ".AbonRest::F_ABON_ID." FROM ".Pay::TABLE." "
                        . "UNION "
                        . "SELECT ".PA::F_ABON_ID."  AS ".AbonRest::F_ABON_ID." FROM ".PA::TABLE.""

                    :   "SELECT {$abon_id} AS ".AbonRest::F_ABON_ID.""

                  )
            ." ) AS a
            LEFT JOIN (
                SELECT ".Pay::F_ABON_ID.", SUM(".Pay::F_PAY_ACNT.") AS ".AbonRest::F_SUM_PAY."
                FROM ".Pay::TABLE." 
                ".(is_null($abon_id) ? "" : "WHERE ".Pay::F_ABON_ID." = {$abon_id}")."
                GROUP BY ".Pay::F_ABON_ID."
            ) AS p ON p.".Pay::F_ABON_ID." = a.".AbonRest::F_ABON_ID."
            LEFT JOIN (
                SELECT
                    ".PA::F_ABON_ID.",
                    SUM(".PA::F_COST_VALUE.") AS ".AbonRest::F_SUM_COST.",
                    SUM(".PA::F_PPMA_VALUE.") AS ".AbonRest::F_SUM_PPMA.",
                    SUM(".PA::F_PPDA_VALUE.") AS ".AbonRest::F_SUM_PPDA."
                FROM ".PA::TABLE." 
                ".(is_null($abon_id) ? "" : "WHERE ".PA::F_ABON_ID." = {$abon_id}")."
                GROUP BY ".PA::F_ABON_ID."
            ) AS pa ON pa.".PA::F_ABON_ID." = a.".AbonRest::F_ABON_ID."

            ON DUPLICATE KEY UPDATE
                ".AbonRest::TABLE.".".AbonRest::F_SUM_PAY."  = VALUES(".AbonRest::F_SUM_PAY."),
                ".AbonRest::TABLE.".".AbonRest::F_SUM_COST." = VALUES(".AbonRest::F_SUM_COST."),
                ".AbonRest::TABLE.".".AbonRest::F_SUM_PPMA." = VALUES(".AbonRest::F_SUM_PPMA."),
                ".AbonRest::TABLE.".".AbonRest::F_SUM_PPDA." = VALUES(".AbonRest::F_SUM_PPDA.");
        ";
        // echo 'SQL: '.  $sql . "\n";
        return $this->execute($sql);
    }



    /**
     * версия 2021 года
     * @param int $recalc_abon_id -- ID абонента, если нужно обновить только его прайсы (по умолчанию — 0, значит все)
     * @return bool
     */
    function update_prices_cost_all(int $recalc_abon_id = 0): bool // версия 2021 года
    {
        $cost_value = 0.0; //* echo 'инициализируем поле "начислено"<br>';
        $cost_date = time(); //* echo 'инициализируем поле "дата начисления"<br>';
        $ret = true;

        $sql = "SELECT
            `prices_apply`.`id`,
            `prices_apply`.`abon_id`,
            `prices_apply`.`date_start`,
            `prices_apply`.`date_end`,
            `prices_apply`.`".PA::F_CLOSED."`,
            `prices_apply`.`cost_value`,
            `prices_apply`.`cost_date`,
            `prices`.`pay_per_day` AS ".PA::F_PRICE_PPD.",
            `prices`.`pay_per_month` AS ".PA::F_PRICE_PPM."
            FROM prices_apply
            LEFT JOIN `billing`.`prices` ON `prices_apply`.`prices_id` = `prices`.`id`
            LEFT JOIN `billing`.`abons` ON `prices_apply`.`abon_id` = `abons`.`id`
            ".(($recalc_abon_id>0) ? "WHERE `prices_apply`.`abon_id` = ".$recalc_abon_id : "WHERE `abons`.`is_payer`=1")." ";

        $model = new AbonModel();
        // запрашиваем список прайсовых фрагментов
        $rows =  $model->get_rows_by_sql($sql); 
        if ($rows) {
            foreach ($rows as $row) {
                // дата обновления стоимости ПФ
                $cost_date = time();
                // считаем стоимость ПФ
                $cost_value = self::get_price_apply_cost($row);
                if ($recalc_abon_id > 0) {
                    MsgQueue::msg(MsgType::INFO_AUTO, "price_apply_id: {$row[PA::F_ID]}: {$cost_value}");
                }
                // обновить данные в таблицах;
                $sql = "UPDATE `".PA::TABLE."` "
                        . "SET `".PA::F_COST_VALUE."`='{$cost_value}',`".PA::F_COST_DATE."`={$cost_date} "
                        . "WHERE `".PA::F_ID."`={$row[PA::F_ID]}";
                if (!$model->execute($sql)) {
                    $this->errors[] = __('Ошибка обновления стоимости прайсового начисления') . " [{$sql}]";
                    $ret = false;
                }
            }
        } else {
            $this->errors[] = __('Ошибка выборки прайсовых фрагментов, или прайсовые фрагменты не прикреплены для abon_id') . " [{$recalc_abon_id}]";
            $ret = false;
        }
        return $ret;
    }



    /**
     * Версия 2021 года
     * 1️⃣ — обновляет поля активных прайсов,
     * 2️⃣ — обнуляет поля у неактивных (закрытых) прайсов.
     * Функция ставит или сбрасывает текущие значения активной абонплаты (PPDA_value, PPMA_value) 
     * только для тех строк, у которых прайсовый фрагмнт активен на текущий момент.
     * @param int $recalc_abon_id -- ID абонента, если нужно обновить только его прайсы (по умолчанию — 0, значит "все")
     * @return bool
     */
    function update_prices_active_all(int $recalc_abon_id = 0): bool // Версия 2021 года
    {
        $ret = true;

        /**
         * Устанавливаем поле активного прайса в открытых прайсах
         */
        $sql = "UPDATE  prices_apply
                LEFT JOIN billing.prices ON prices_apply.prices_id = prices.id
                SET
                prices_apply.PPDA_value=prices.pay_per_day,
                prices_apply.PPMA_value=prices.pay_per_month
                WHERE
                ".(($recalc_abon_id > 0) ? "prices_apply.abon_id=".$recalc_abon_id." AND" : "")."
                (
                    (
                        !isnull(`prices_apply`.`date_end`)
                        AND (`prices_apply`.`date_end` > UNIX_TIMESTAMP())
                    ) OR (
                        isnull(`prices_apply`.`date_end`)
                        AND (`prices_apply`.`date_start` < UNIX_TIMESTAMP())
                    )
                ) = 1
                ";

        if (!$this->execute($sql)) {
            $this->errors[] = __('Ошибка установки полей `PPDA_value` и `PPMA_value` для активных прайсовых фрагментов');
            $ret = false;
        }

        /**
         * Обнуляем поле активного прайса в закрытых прайсах
         */
        $sql = "UPDATE `prices_apply`
                LEFT JOIN `billing`.`prices` ON `prices_apply`.`prices_id` = `prices`.`id`
                SET
                `prices_apply`.`PPDA_value`=0,
                `prices_apply`.`PPMA_value`=0
                WHERE
                    ".(($recalc_abon_id > 0) ? "(`prices_apply`.`abon_id`=" . $recalc_abon_id . ") AND" : "")."
                    (
                        (
                            !isnull(`prices_apply`.`date_end`)
                            AND (`prices_apply`.`date_end` > UNIX_TIMESTAMP())
                        ) OR (
                            isnull(`prices_apply`.`date_end`)
                            AND (`prices_apply`.`date_start` < UNIX_TIMESTAMP())
                        )
                    ) = 0
                ";

        if (!$this->execute($sql)) {
            $this->errors[] = __('Ошибка обнуления полей `PPDA_value` и `PPMA_value` для закрытых прайсовых фрагментов');
            $ret = false;
        }
        return $ret;
    }



    function get_tp_list_with_abon(int $abon_id, int|null $closed = null): array|null {
        if (!$this->validate_id(table_name: Abon::TABLE, field_id: Abon::F_ID, id_value: $abon_id)) { return null; }
        $sql = "SELECT "
                . "`".PA::F_TP_ID."` "
                . "FROM `".PA::TABLE."` "
                . "WHERE (`".PA::F_ABON_ID."`={$abon_id}) "
                . (!is_null($closed) ? "AND (`".PA::F_CLOSED."`={$closed}) " : "")
                . "GROUP BY `".PA::F_TP_ID."`";
        $tp_id_list = array_column(
                array:  $this->get_rows_by_sql($sql),
                column_key: PA::F_TP_ID,
        );
        if (empty($tp_id_list)) {
            return [];
        } else {
            $list = $this->get_rows_by_where(table: TP::TABLE, where: TP::F_ID . ' IN (' . implode(',', $tp_id_list) . ') AND `'.TP::F_STATUS.'`=1');
            foreach ($list as &$tp) {
                $this->normalize_tp($tp);
            }
            return $list;
        }
    }



    /**
     * Возвращает одномерный массив id абонентов, подключенных к указанным ТП
     * @param array $tp_list -- Ассоциативный массив. Из него берётся поле, указанное в $field_id
     * @param string $field_id -- имя поля в котором находится id ТП
     * @param bool $skip_closed_pa -- игнорировать закрытые прайсовые фрагменты
     * @return array
     */
    function get_abons_id_by_tp(array &$tp_list, string $field_id = TP::F_ID,  bool $skip_closed_pa = true) {
        if (empty($tp_list)) { return []; }
        $tp_list_str = implode(',', array_column($tp_list, $field_id));
        return array_column(
                $this->get_rows_by_sql("SELECT `".PA::F_ABON_ID."` FROM `".PA::TABLE."` WHERE (`".PA::F_TP_ID."` IN ({$tp_list_str})) ". ($skip_closed_pa ? "AND (`price_closed`=0)" : "") . " GROUP BY `abon_id`"),
                PA::F_ABON_ID);
    }



    /**
     * Считает стоимость прайсового фрагмента с помощью механизма пормесячной разбивки и точного рассчёта
     * @param array $price_apply
     * @param int $today
     * @return float
     */
    public static function get_price_apply_cost_2023(array $price_apply, int $today=NA): float { //2023
        if($today == NA) { $today = TODAY(); } else { $today = get_date($today); }
        $pa_monthly_parts = self::get_price_apply_cost_per_montch($price_apply, $today);
        $cost = 0;
        foreach ($pa_monthly_parts as $part) {
            $cost += $part['cost'];
        }
        unset($part);
        unset($pa_monthly_parts);
        return $cost;
    }



    /**
     * Возвращает ПЕРЕСЧИТАННУЮ суммарную стоимость прайсовых фрагментов.
     * Процедура ресурсоёмкая.
     * @param array $prices_apply_all -- список всех прайсовых фрагментов абонента.
     * @return float -- сумма стоимости прайсовых фрагментов.
     */
    public static function get_prices_apply_cost_sum(array $prices_apply_all, int $today=NA): float {
        if($today == NA) { $today = TODAY(); } else { $today = get_date($today); }
        $cost = 0;
        foreach ($prices_apply_all as $pa_one) {
            $cost += self::get_price_apply_cost_2023($pa_one, $today);
        }
        return $cost;
    }



    /**
     * вычисляет стоимость услуги по записи из таблицы prices_apply 
     * — сумму, которую должен заплатить абонент за период действия прайса.
     * Версия 2022 года
     * Она учитывает:
     * ежедневную оплату (pay_per_day, PPD);
     * ежемесячную оплату (pay_per_month, PPM);
     * даты начала и конца действия (date_start, date_end);
     * текущее состояние прайса (CURRENT, PAUSE, CLOSED, FUTURE, и др.);
     * и дату расчёта ($today).
     * 
     * @param mixed $price_apply -- запись прайсового фрагмента
     * @param mixed $today -- дата, до которой делается рассчёт
     * @return float|int
     */
    public static function get_price_apply_cost(array $price_apply, int $today=NA): float { //2022
        if ($today == NA) { $today = TODAY(); }
        $today = get_date($today);
        $cost = 0.0;
        // echo "<pre>prices_apply:<br>".print_r($price_apply, true)."</pre>" ;
        switch (get_price_apply_age($price_apply, $today)) {

            /**
             * Если прайс активный (CURRENT или PAUSE_TODAY)
             * конец периода временно принимается за сегодня, чтобы рассчитать накопленную стоимость «по текущее число».
             */
            case PAStatus::CURRENT:
            case PAStatus::PAUSE_TODAY:
                $price_apply[PA::F_DATE_END] = $today;                             

            /**
             * Если прайс приостановлен или закрыт (PAUSE, CLOSED)
             * Происходит реальный расчёт стоимости по датам начала и конца.
             */
            case PAStatus::PAUSE:
            case PAStatus::CLOSED:

                /**
                 * Расчёт подневной оплаты
                 */
                if(abs($price_apply[PA::F_PRICE_PPD]) > 0) {
                    /**
                     * — вычисляется количество оплачиваемых дней (+1, чтобы включить последний) 
                     */
                    $d1 = $price_apply[PA::F_DATE_START];
                    $d2 = $price_apply[PA::F_DATE_END];
                    $interval = get_between_days($d1, $d2);
                    $cost += (($interval + 1) * (double)$price_apply[PA::F_PRICE_PPD]);
                }

                /**
                 * Расчёт ежемесячной оплаты
                 */
                if(abs($price_apply[PA::F_PRICE_PPM]) > 0) {
                    // дни начисления в одном месяце
                    if(date("Ym", $price_apply[PA::F_DATE_START]) == date("Ym", $price_apply[PA::F_DATE_END])) {
                        $days_of_month = days_of_month($price_apply[PA::F_DATE_START]);
                        $days_costed = day($price_apply[PA::F_DATE_END]) - day($price_apply[PA::F_DATE_START]) + 1;
                        $cost += ((double)$price_apply[PA::F_PRICE_PPM] / $days_of_month * $days_costed);
                    } else {
                        // дни начисления в разных месяцах
                        // дней в первом месяце
                        $day_start = day($price_apply[PA::F_DATE_START]);
                        $days_of_month = days_of_month($price_apply[PA::F_DATE_START]);
                        $days_costed = $days_of_month - $day_start + 1;                  //echo "Оплачиваемых дней в певом месяце = ".$days_costed."<br>";
                        $cost += ((double)$price_apply[PA::F_PRICE_PPM] / $days_of_month * $days_costed);

                        // дней в последнем месяце
                        $day_end = day($price_apply[PA::F_DATE_END]);
                        $days_of_month = days_of_month($price_apply[PA::F_DATE_END]);
                        $days_costed = $day_end;                                    //echo "Оплачиваемых дней в поледнем месяце = ".$days_costed."<br>";
                        $cost += ((double)$price_apply[PA::F_PRICE_PPM] / $days_of_month * $days_costed);

                        // полных месяцев
                        $d1_str = year($price_apply[PA::F_DATE_START])."-".month($price_apply[PA::F_DATE_START])."-".(days_of_month($price_apply[PA::F_DATE_START])-1); //echo "Рассчёт месяцев 1 = ".$d1_str."<br>";
                        $d2_str = year($price_apply[PA::F_DATE_END])."-".month($price_apply[PA::F_DATE_END])."-02";                                              //echo "Рассчёт месяцев 2 = ".$d2_str."<br>";
                        $d1 = date_create($d1_str);
                        $d2 = date_create($d2_str);
                        $interval = date_diff($d1, $d2);                                //echo "Оплачиваемых месяцев = ".($interval->m + $interval->y * 12)."<br>";
                        $cost += ((double)$price_apply[PA::F_PRICE_PPM] * ($interval->m + $interval->y * 12));
                        // echo "<pre>prices_apply:<br>".print_r($interval, true)."</pre>";
                    }
                }
                return $cost;
                // break;

            case PAStatus::FUTURE:
                return 0.0;
                // break;

            default:
                throw new \Exception("<hr>Этого не должно быть:<br>get_price_apply_cost:<br><pre>".print_r($price_apply, true)."</pre><hr>", 1);
        }
    }



    /**
     * Считает правильно!
     * Расщепляет прайсовый фрагмент на месяцы и рассчитывает помесячную стоимость (частичные + полные месяцы), 
     * возвращая структуру начислений.
     * 
     * Рассчитывает детализированную помесячную стоимость одного фрагмента прайса (prices_apply), 
     * начиная с даты старта до даты окончания (или до $today, если активный).
     * Возвращает массив $struct, где каждая запись описывает один расчётный месяц (или его часть) и сумму начисления за этот период.
     * 
     * @param array $price_apply -- один прайсовый фрагмент
     * @param int $today -- дата, считающаяся в расчётах "текущей"
     * @return array -- помесячная структура стоимости
     */
    public static function get_price_apply_cost_per_montch(array $price_apply, int $today): array {
        $struct = array();

        $pa_age = get_price_apply_age($price_apply, $today);
        switch ($pa_age) {
            case PAStatus::FUTURE:
                return $struct;
                // break;

            case PAStatus::CURRENT:
            case PAStatus::PAUSE_TODAY:
                $price_apply[PA::F_DATE_END] = $today;
                $price_apply[PA::F_DATE_END_STR] = date("Y-m-d", $price_apply[PA::F_DATE_END]);
                // никуда не выходим, продожаем обработку

            case PAStatus::PAUSE:
            case PAStatus::CLOSED:
                // считаем
                break;

            default:
                throw new \Exception("<hr>Этого не должно быть:<br>get_price_apply_cost_per_montch():<br><pre>". print_r($price_apply, 1)."</pre><hr>");
        }

        $index = 0;
        $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
        // начальная дата расчётов
        $struct[$index]['date']     = get_date($price_apply[PA::F_DATE_START]);
        $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
        // Название прикреплённого прайса
        $struct[$index]['text']     = (isset($price_apply[PA::F_PRICE_TITLE])?$price_apply[PA::F_PRICE_TITLE]:"");
        // название прайсвого фрагмнта
        $struct[$index]['name']     = (isset($price_apply[PA::F_NET_NAME])?$price_apply[PA::F_NET_NAME]:"");
        // Начальная стоимость ПФ
        $struct[$index]['cost']     = 0.0;

        if(     // если прайсовый фрагмент что-то начисляет, то
                (abs($price_apply[PA::F_PRICE_PPD])   > 0) ||
                (abs($price_apply[PA::F_PRICE_PPM]) > 0)
          ) {

            if(date("Ym", $price_apply[PA::F_DATE_START]) == date("Ym", $price_apply[PA::F_DATE_END])) {
                /**
                 * дни начисления в одном месяце
                 */
                $days_of_month = date("t", $price_apply[PA::F_DATE_START]);
                $days_costed = date("j", $price_apply[PA::F_DATE_END]) - date("j", $price_apply[PA::F_DATE_START]) + 1;
                $cost = self::get_price_apply_cost($price_apply, $today);
                $index++;
                $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                $struct[$index]['date']     = get_date($price_apply[PA::F_DATE_END]);
                $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                $struct[$index]['text']     = $price_apply[PA::F_PRICE_TITLE] ?? "";
                $struct[$index]['name']     = $price_apply[PA::F_NET_NAME] ?? "";
                $struct[$index]['cost']     = $cost;
            } else {
                /**
                 * дни начисления в разных месяцах
                 */

                /**
                 * дней в первом месяце
                 */
                $day_start = day($price_apply[PA::F_DATE_START]);
                $days_of_month = \days_of_month($price_apply[PA::F_DATE_START]);
                $days_costed = $days_of_month - $day_start + 1;                  //echo "Оплачиваемых дней в певом месяце = ".$days_costed."<br>";
                $cost = ((double)$price_apply[PA::F_PRICE_PPM] / $days_of_month * $days_costed)
                      + ((double)$price_apply[PA::F_PRICE_PPD] * $days_costed);
                $index++;
                $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                $struct[$index]['date']     = mktime(0, 0, 0, month($price_apply[PA::F_DATE_START]), days_of_month($price_apply[PA::F_DATE_START]), year($price_apply[PA::F_DATE_START]));
                $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                $struct[$index]['text']     = $price_apply[PA::F_PRICE_TITLE] ?? "";
                $struct[$index]['name']     = $price_apply[PA::F_NET_NAME] ?? "";
                $struct[$index]['cost']     = $cost;

                /**
                 * дней в последнем месяце
                 */
                $day_end = day($price_apply[PA::F_DATE_END]);
                $days_of_month = days_of_month($price_apply[PA::F_DATE_END]);
                $days_costed = $day_end;                                         //echo "Оплачиваемых дней в поледнем месяце = ".$days_costed."<br>";
                $cost = ((double)$price_apply[PA::F_PRICE_PPM] / $days_of_month * $days_costed)
                      + ((double)$price_apply[PA::F_PRICE_PPD] * $days_costed);
                $index++;
                $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                $struct[$index]['date']     = get_date($price_apply[PA::F_DATE_END]);
                $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                $struct[$index]['text']     = $price_apply[PA::F_PRICE_TITLE] ?? "";
                $struct[$index]['name']     = $price_apply[PA::F_NET_NAME] ?? "";
                $struct[$index]['cost']     = $cost;

                /**
                 * полных месяцев
                 */
                $y1 = year (mktime(0, 0, 0, month($price_apply[PA::F_DATE_START]) +1, 1, year($price_apply[PA::F_DATE_START])));
                $m1 = month(mktime(0, 0, 0, month($price_apply[PA::F_DATE_START]) +1, 1, year($price_apply[PA::F_DATE_START])));
                $y2 = year (mktime(0, 0, 0, month($price_apply[PA::F_DATE_END]  ) -1, 1, year($price_apply[PA::F_DATE_END]  )));
                $m2 = month(mktime(0, 0, 0, month($price_apply[PA::F_DATE_END]  ) -1, 1, year($price_apply[PA::F_DATE_END]  )));

                /*
                 * тестирование конкретного прайсового фрагмента
                 *
                    if($prices_apply['id'] == 1337) {
                        echo "<hr>1337:<br>$y1-$m1<br>$y2-$m2<br>";
                        echo (date("m", $prices_apply['date_end'])-1)."<br>";
                        echo (date("Y-m-d", mktime(0, 0, 0, date("m", $prices_apply['date_end'])-1, 1, date("Y", $prices_apply['date_end']))))."<br>";
                        echo mktime(0, 0, 0, $m2, 1, $y2)."<br>";
                        echo mktime(0, 0, 0, $m1, 1, $y1)."<hr>";
                    }
                 */

                $m = $m1;
                $y = $y1;
                if(mktime(0, 0, 0, $m2, 1, $y2) >= mktime(0, 0, 0, $m1, 1, $y1)) {
                    do {
                        $index++;
                        $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                        $struct[$index]['date']     = mktime(0, 0, 0, $m, 1, $y);
                        $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                        $struct[$index]['text']     = (isset($price_apply[PA::F_PRICE_TITLE])?$price_apply[PA::F_PRICE_TITLE]:"");
                        $struct[$index]['name']     = (isset($price_apply[PA::F_NET_NAME])?$price_apply[PA::F_NET_NAME]:"");
                        $days_of_month = date("t", mktime(0, 0, 0, $m, 1, $y));
                        $cost = ((double)$price_apply[PA::F_PRICE_PPM])
                              + ((double)$price_apply[PA::F_PRICE_PPD] * $days_of_month);
                        $struct[$index]['cost'] = $cost;
                        $m++;
                        if($m > 12) {
                            $m = 1;
                            $y++;
                        }
                        if(($y == $y2) && $m > $m2) {
                            break;
                        }
                    } while ($y <= $y2);
                }
            }
        }
        /*
         * тестирование конкретного прайсового фрагмента
            if($prices_apply['id'] == 1741) {
                echo "<hr>1741:<pre>".print_r($struct, true)."</pre><hr>";
            }
         */
        return $struct;
    }



    /**
    * Возвращает сумарную месячную абонплату из переданных прайсовых фрагментов.
    * Считает ppma, ppda и количество дней в указанном или текущем месяце.
    * @param array $pricess_apply_list -- все прикрепленные прайсы абонента
    * @param int $today -- день, который считается "сегодняшним" для определения активности прайса
    * @return float -- сумма прайсов за месяц
    */
    public static function get_ppma(array $pricess_apply_list, int $today = NA): float {

       if ($today == NA) { $today = TODAY(); } else { $today = get_date($today); }

       $PPDA = 0.0;
       $PPMA = 0.0;
       foreach ($pricess_apply_list as $PA) {
           if (get_price_apply_age($PA, $today)->value & (PAStatus::CURRENT->value | PAStatus::PAUSE_TODAY->value)) {
               $PPDA += $PA['pay_per_day'];
               $PPMA += $PA['pay_per_month'];
           }
       }
       return $PPDA * days_of_month($today) + $PPMA;
    }



    function get_sql_notify_by_abon_id(int $abon_id, int|string|null $limit = null): string
    {
        return "SELECT "
                . "* "
                . "FROM ".Notify::TABLE." "
                . "WHERE ".Notify::F_ABON_ID."=".$this->quote($abon_id)." "
                . "ORDER BY ".Notify::F_ID." DESC"
                . (empty($limit) ? "" : " LIMIT {$limit}");
    }



    /**
     * Список уведомлений (СМС)
     * @param int $abon_id
     * @param int|string|null $limit
     * @return array
     */
    function get_notify_by_abon_id(int $abon_id, int|string|null $limit = null): array
    {
        $sql = $this->get_sql_notify_by_abon_id($abon_id, $limit);
        return $this->get_rows_by_sql($sql);
    }



    /**
     * Возвращает активные или последние закрытые прайсовые фрагменты абонента.
     *
     * @param int $abon_id
     * @return array
     */
    function get_pa_active_or_last(int $abon_id): array
    {
        // 1. Попытка найти активные прайсовые фрагменты
        $sql_active = "
            SELECT *
            FROM `" . PA::TABLE . "`
            WHERE `" . PA::F_ABON_ID . "` = {$abon_id}
            AND (`" . PA::F_DATE_END . "` IS NULL OR `" . PA::F_DATE_END . "` = 0)
            ORDER BY `" . PA::F_ID . "` ASC
        ";

        // debug($sql_active, '$sql_active');

        $rows = $this->get_rows_by_sql($sql_active);

        // 2. Если активных нет — ищем последние закрытые (по максимальному дню закрытия)
        if (empty($rows)) {
            $sql_closed = "
                SELECT *
                FROM `" . PA::TABLE . "`
                WHERE `" . PA::F_ABON_ID . "` = {$abon_id}
                AND DATE(FROM_UNIXTIME(`" . PA::F_DATE_END . "`)) = (
                    SELECT DATE(FROM_UNIXTIME(MAX(`" . PA::F_DATE_END . "`)))
                    FROM `" . PA::TABLE . "`
                    WHERE `" . PA::F_ABON_ID . "` = {$abon_id}
                        AND `" . PA::F_DATE_END . "` IS NOT NULL
                        AND `" . PA::F_DATE_END . "` > 0
                )
                ORDER BY `" . PA::F_DATE_END . "` DESC
            ";

            // debug($sql_closed, '$sql_closed');

            $rows = $this->get_rows_by_sql($sql_closed);
        }
        

        // foreach ($rows as &$row) {
        //     $row[PA::FF_DATE_START_STR] = date('Y-m-d H:i:s', (int)$row[PA::F_DATE_START]);
        //     $row[PA::FF_DATE_END_STR]   = ($row[PA::F_DATE_START] ? date('Y-m-d H:i:s', (int)$row[PA::F_DATE_END]) : "-");
        // }

        return $rows ?: [];
    }



    /**
     * Список прикрепленных прайсвых фрагментов, включая название прикрепленного прайса
     * @param int $abon_id
     * @param bool|null $active -- делает выборку активных или закрытых прайсов. Если NULL, то выбирает всех.
     * @return array
     */
    function get_pa_by_abon_id(int $abon_id, bool|null $active = null): array
    {
        $sql = "SELECT "
                . "`".Price::TABLE."`.`".Price::F_TITLE."` AS ".PA::F_PRICE_TITLE.", "
                . "`".PA::TABLE."`.* "
                . "FROM `".PA::TABLE."` "
                . "LEFT JOIN ".Price::TABLE." ON ".Price::TABLE.".".Price::F_ID." = ".PA::TABLE.".".PA::F_PRICE_ID." "
                . "WHERE "
                .    "`".PA::TABLE."`.`".PA::F_ABON_ID."`={$abon_id} "
                . (is_null($active) 
                    ?   ""
                    :   ($active 
                            ?   "AND "
                                . "("
                                    . "`".PA::TABLE."`.`".PA::F_DATE_END."` IS NULL "
                                    . "OR "
                                    . "`".PA::TABLE."`.`".PA::F_DATE_END."` >= UNIX_TIMESTAMP(CURDATE()) "
                                . ") "
                            :   "AND "
                                . "("
                                    . "`".PA::TABLE."`.`".PA::F_DATE_END."` IS NOT NULL "
                                    . "AND "
                                    . "`".PA::TABLE."`.`".PA::F_DATE_END."` < UNIX_TIMESTAMP(CURDATE()) "
                                . ") "
                        )
                  )
                . "ORDER BY "
                    . "(`".PA::TABLE."`.`".PA::F_DATE_END."` IS NOT NULL)," //  -- сначала идут строки с NULL
                    . "`".PA::TABLE."`.`".PA::F_DATE_END."` DESC,"          //  -- затем сортировка по убыванию date_end
                    . "`".PA::TABLE."`.`".PA::F_DATE_START."` DESC";        //  -- потом по убыванию date_start

        // debug($sql, '$sql', die:1);
        return $this->get_rows_by_sql($sql);

        // вариант:
        // (
        //   SELECT *
        //   FROM prices_apply
        //   WHERE abon_id = 458 AND date_end IS NULL
        //   ORDER BY date_start DESC
        // )
        // UNION ALL
        // (
        //   SELECT *
        //   FROM prices_apply
        //   WHERE abon_id = 458 AND date_end IS NOT NULL
        //   ORDER BY date_end DESC
        // );

    }



    function get_pa(int $pa_id):array {
        self::$errors = [];
        if ($this->validate_id(PA::TABLE, $pa_id, PA::F_ID)) {
            return $this->get_row_by_id(PA::TABLE, $pa_id, PA::F_ID);
        } else {
            self::$errors[] = "No Valid ID ".$pa_id."";
            return [];
        }
    }



    function get_srvice_type_by_pa(array $pa): ServiceType
    {
        if ($pa[PA::F_NET_IP_SERVICE]) {
            return ServiceType::INTERNET;
        } else {
            return ServiceType::OTHER;
        }
    }


    function get_abons_by_uid(int $user_id): array {
        return $this->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user_id, order_by: Abon::F_DATE_JOIN . ' DESC');
    }



    /**
     * Возвращает список активных прайсовых фрагментов на указанной ТП
     * @param int $tp_id -- ID ТП
     * @param int|null $PA_AGE
     * @return array массив прайсовых фрагментов
     */
    function get_prices_apply_by_tp(int $tp_id, int|null $PA_AGE = (PAStatus::CURRENT->value | PAStatus::PAUSE_TODAY->value)): array {
        $pa_list_raw = $this-> get_rows_by_field(
                            table: PA::TABLE,
                            field_name: PA::F_TP_ID,
                            field_value: $tp_id,
                            order_by: PA::F_ABON_ID . " ASC");
        if (is_null($PA_AGE)) {
            return $pa_list_raw;
        } else {
            $pa_list = array();
            foreach ($pa_list_raw as $pa_one) {
                if ($PA_AGE & get_price_apply_age($pa_one)->value) {
                    $pa_list[] = $pa_one;
                }
            }
            return $pa_list;
        }
    }



    /**
     * Таблица кэширования прайсовых фрагментов для абонентов
     */
    protected static $CACHE_PA_BY_ABON = array();


    /**
     * Возвращает из кэша self::CACHE_PA_BY_ABON[$abon_id] все прикрепленные прайсовые фрагменты
     * Если их там нет, то вносит их туда из базы и возвращает.
     * @global array self::CACHE_PA_BY_ABON -- Кэш-таблица
     * @param int $abon_id -- ID абоненета
     * @return array -- список прикрепленных прайсовых фрагментов
     */
    function get_prices_apply_by_abon($abon_id): array {

        if (!array_key_exists($abon_id, self::$CACHE_PA_BY_ABON)) {
            //echo "CACHE_PA_BY_ABON - reading...<br>";
            $SQL = "SELECT
                prices_apply.*,
                prices_apply.id                                                    AS prices_apply_id,
                DATE_FORMAT(from_unixtime(prices_apply.date_start),'%Y-%m-%d')     AS date_start_str,
                DATE_FORMAT(from_unixtime(prices_apply.date_end),  '%Y-%m-%d')     AS date_end_str,
                DATE_FORMAT(from_unixtime(prices_apply.cost_date), '%Y-%m-%d')     AS cost_date_str,
                DATE_FORMAT(from_unixtime(prices_apply.modified_date), '%Y-%m-%d') AS modified_date_str,
                prices.title                                                       AS ".PA::F_PRICE_TITLE.",
                prices.pay_per_day                                                 AS ".PA::F_PRICE_PPD.",
                prices.pay_per_month                                               AS ".PA::F_PRICE_PPM.",
                prices.description                                                 AS ".PA::F_PRICE_DESCR.",
                tp_list.title                                                      AS ".PA::FF_TP_TITLE.",
                tp_list.status                                                     AS ".PA::FF_TP_STATUS.",
                tp_list.deleted                                                    AS ".PA::FF_TP_DELETED.",
                tp_list.is_managed                                                 AS ".PA::FF_TP_IS_MANAGED."
                FROM prices_apply
                    LEFT JOIN billing.prices  ON prices_apply.prices_id     = prices.id
                    LEFT JOIN billing.tp_list ON prices_apply.net_router_id = tp_list.id
                WHERE abon_id =".$abon_id."
                ORDER BY prices_apply.date_start ASC";
            $prices = $this->get_rows_by_sql($SQL);
            self::$CACHE_PA_BY_ABON[$abon_id] = $prices;
        }
        return self::$CACHE_PA_BY_ABON[$abon_id];
    }



    function get_sql_payments(
            int      $abon_id,
            int|null $pay_type = null,
            int|null $ppp_id = null
        ): string
    {
        return "SELECT "
                . "*  "
                . "FROM "
                . "`".Pay::TABLE."` "
                . "WHERE "
                . "`".Pay::F_ABON_ID."`={$abon_id} "
                . ($pay_type ? "AND `".Pay::F_TYPE_ID."`=1 " : "")
                . ($ppp_id ? "AND `".Pay::F_PPP_ID."` = {$ppp_id} " : "")
                . "ORDER BY "
                . "`".Pay::TABLE."`.`".Pay::F_DATE."` DESC";
    }



    function get_payments(
            int      $abon_id,
            int|null $pay_type = null,
            int|null $ppp_id = null
        ): array
    {
        return $this->get_rows_by_sql($this->get_sql_payments(abon_id: $abon_id, pay_type: $pay_type, ppp_id: $ppp_id));
    }



    /**
     * Возвращает список предприятий-клиентов привязанных к указанному пользователю
     * @param int $uid -- ID пользователя
     * @return array -- список предприятий
     */
    function get_firms_by_uid_cli(int $uid): array
    {
        $sql = "SELECT
                ts_firms_users.firm_id,
                ts_firms_users.user_id,
                firm_list.*

                FROM ts_firms_users
                    LEFT JOIN firm_list ON firm_list.id = ts_firms_users.firm_id

                WHERE
                    ts_firms_users.user_id=$uid
                    AND firm_list.has_active
                    AND firm_list.has_client";

        return $this->get_rows_by_sql($sql);
    }



    /**
     * Возвращает все записи из таблицы firm_list, которые связаны с абонентом (abon_id) через таблицы prices_apply и tp_list.
     * Связь осуществляется через net_router_id и firm_id, и учитываются только те записи, которые актуальны на текущий момент
     * (то есть, период действия еще не закончился или не ограничен).
     *
     * @param int $abon_id
     * @return array
     */
    function get_agents_by_abon_id(int $abon_id): array {
        $sql = "SELECT
                *,
                id AS firm_id
                FROM
                firm_list
                WHERE
                id IN
                (
                    SELECT
                    firm_id
                    FROM
                    tp_list
                    WHERE
                    id IN
                    (
                        SELECT
                        net_router_id
                        FROM
                        prices_apply
                        WHERE
                        (abon_id = $abon_id)
                        AND
                        (
                            (
                                (date_start < UNIX_TIMESTAMP()) AND
                                (isnull(date_end))
                            )
                            OR
                            (
                                date_end > UNIX_TIMESTAMP()
                            )
                        )
                        GROUP BY net_router_id
                    )
                    GROUP BY firm_id
                )";

        return $this->get_rows_by_sql($sql);
    }



    /**
     * Возвращает все записи из таблицы firm_list, которые связаны с абонентом (abon_id) через таблицы prices_apply и tp_list.
     * Запрос не учитывает временные ограничения (date_start и date_end). Он просто ищет все записи,
     * связанные с абонентом, без учета актуальности периода действия.
     * @param int $abon_id
     * @return array
     */
    function get_agents_by_abon_id_all(int $abon_id): array {
        $sql = "SELECT
                *,
                id AS firm_id
                FROM
                firm_list
                WHERE
                id IN
                (
                    SELECT
                    firm_id
                    FROM
                    tp_list
                    WHERE
                    id IN
                    (
                        SELECT
                        net_router_id
                        FROM
                        prices_apply
                        WHERE (abon_id = $abon_id)
                        GROUP BY net_router_id
                    )
                    GROUP BY firm_id
                )";

        return $this->get_rows_by_sql($sql);
    }



    function is_payer(int $abon_id): bool {
        return $this->get_abon($abon_id)[Abon::F_IS_PAYER];
    }



    /**
     * Кэширование статусов абонентов
     */
    private static $CASHE_ABON_STATE_LIST = array();



    function get_abon_state(int $abon_id): int {
        if (!array_key_exists($abon_id, self::$CASHE_ABON_STATE_LIST)) {

            /**
             * Проверить валидность AID
             */
            if ($abon_id == MikAbonStatus::ABON_0) {

                self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::ABON_0;

            } elseif ($abon_id == MikAbonStatus::ABON_XZ) {

                self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::NA;

            } elseif ($abon_id == MikAbonStatus::ABON_SW) {

                self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::SW;

            } elseif (!$this->validate_id(Abon::TABLE, $abon_id)) {

                self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::NA;

            } else {
                /**
                 * Проверка: НЕ плательщик
                 */
                if (!$this->is_payer($abon_id)) {

                    self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::OFF;

                } else {
                    /**
                     * Если ППМА == 0, то посчитать количество дней и определить
                     * просто пауза или длинаая пауза
                     */
                    $prices_apply_all = $this->get_prices_apply_by_abon($abon_id);
                    $ppma = self::get_ppma($prices_apply_all);
                    if ($ppma == 0) {
                        $last = self::get_last_PA($abon_id, $prices_apply_all);
                        $pause_days = (TODAY() - $last['off_time']) / (60 * 60 * 24);
                        if ($pause_days > App::get_config('LONG_PAUSED_DAYS')) {

                            self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::LONG_PAUSED;

                        } else {

                            self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::PAUSED;
                        }
                    } else {
                        /**
                         * если есть прайс, то считаем сколько оплачено дней
                         * если оплаченных дней меньше точки уведомлений, то статус уведомления
                         * Если оплачсенных дней больше точки уведомления, то статус ОК
                         */
                        $ppda =  $ppma / days_of_month();
                        $cost = self::get_prices_apply_cost_sum($prices_apply_all); // всего начислено за услуги
                        $pays = self::get_payments_pay_sum($abon_id); // всего внесено (оплачено) на ЛС
                        $ball = $pays - $cost; // остаток на ЛС
                        $prepayed_days = $ball / $ppda; // предоплачено дней

                        $a = $this->get_abon($abon_id);

                        // duty_max_warn Индекс int Количество оплаченных дней, при пересечении которых отправлять предупреждение абоненту об оплате
                        if ($prepayed_days < $a['duty_max_off']) {

                            self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::WARN2;

                        } elseif ($prepayed_days < $a['duty_max_warn']) {

                            self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::WARN;

                        } else {

                            self::$CASHE_ABON_STATE_LIST[$abon_id] = AbonStatus::OK;

                        }
                    }
                }
            }
        }
        return self::$CASHE_ABON_STATE_LIST[$abon_id];
    }



    function get_abon_state_img(int $abon_id, string $title_prefix='', int $icon_width = Icons::ICON_WIDTH_DEF, int $icon_height = Icons::ICON_HEIGHT_DEF, $style = ""): string {
        switch ($this->get_abon_state($abon_id)) {
            case AbonStatus::NA:
                return paint(s: "<img src='". Icons::SRC_ABON_NA."' alt=[NA] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GRAY, title: $title_prefix."[{$abon_id}] ". AbonStatusTitle::NA."") ;
                //break;
            case AbonStatus::ABON_0:
                return paint(s: "<img src='".Icons::SRC_ABON_0."' alt=[NA] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GRAY, title: $title_prefix."".AbonStatusTitle::ABON_0."");
                //break;
            case AbonStatus::SW:
                return paint(s: "<img src='".Icons::SRC_SW."' alt=[SW] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GRAY, title: $title_prefix."".AbonStatusTitle::SW."") ;
                //break;
            case AbonStatus::OFF:
                return paint(s: "<img src='".Icons::SRC_ABON_OFF."' alt=[OFF] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GRAY, title: $title_prefix."".AbonStatusTitle::OFF."") ;
                //break;
            case AbonStatus::LONG_PAUSED:
                return paint(s: "<img src='".Icons::SRC_ABON_LONG."' alt=[LONG] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GRAY, title: $title_prefix."".AbonStatusTitle::LONG."") ;
                //break;
            case AbonStatus::PAUSED:
                return paint(s: "<img src='".Icons::SRC_ABON_PAUSED."' alt=[PAUSE] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: ORANGE, title: $title_prefix."".AbonStatusTitle::PAUSED."") ;
                //break;
            case AbonStatus::WARN2:
                return paint(s: "<img src='".Icons::SRC_ABON_WARN2."' alt=[WARN2] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: ORANGE, title: $title_prefix."".AbonStatusTitle::WARN2."") ;
                //break;
            case AbonStatus::WARN:
                return paint(s: "<img src='".Icons::SRC_ABON_WARN."' alt=[WARN] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GREEN, title: $title_prefix."".AbonStatusTitle::WARN."") ;
                //break;
            case AbonStatus::OK:
                return paint(s: "<img src='".Icons::SRC_ABON_OK."' alt=[OFF] width={$icon_width} height={$icon_height} style=\"{$style}\" >", color: GREEN, title: $title_prefix."".AbonStatusTitle::OK."") ;
                //break;

            default:
                throw new \Exception('Этого не должно быть');
                //break;
        }
    }




    /**
     * Для указанного абонента возвращает последние закрытый, все текущие и все будущие прайсы.
     * @param int $abon_id -- ИД абонента для которого ищутся правйсовые фрагменты
     * @param array $pa_list -- массив прайсовых фрагментов из которого делается выборка
     * @return array -- возвращает массив:
     *                  $last['off'] = array(prices_apply) -- последние закрытые
     *                  $last['cur'] = array(prices_apply) -- все текущие
     *                  $last['fut'] = array(prices_apply) -- все будущие
     */
    public static function get_last_PA(int $abon_id, array &$pa_list): array {
        $last['off'] = array();
        $last['cur'] = array();
        $last['fut'] = array();
        $last['off_time'] = -1;
        $last['cur_time'] = -1;
        $last['fut_time'] = -1;
        /**
         * Ищем даты последних включенных и отключенных прайсов
         */
        foreach ($pa_list as $pa_id => $pa_item) {
            if ($pa_item['abon_id'] == $abon_id) {
                switch (get_price_apply_age($pa_item)) {
                    case PAStatus::CLOSED:
                    case PAStatus::PAUSE:
                        if ($pa_item['date_end'] > $last['off_time']) {
                            $last['off_time'] = $pa_item['date_end'];
                        }
                        break;
                    case PAStatus::CURRENT:
                    case PAStatus::PAUSE_TODAY:
                        if ($pa_item['date_start'] > $last['cur_time']) {
                            $last['cur_time'] = $pa_item['date_start'];
                        }
                        break;
                    case PAStatus::FUTURE:
                        if ($pa_item['date_start'] > $last['fut_time']) {
                            $last['fut_time'] = $pa_item['date_start'];
                        }
                        break;
                }
            }
        }
        /**
         * Считываем все прайсовые фрагенты по найденным датам
         */
        foreach ($pa_list as $pa_id => $pa_item) {
            if ($pa_item['abon_id'] == $abon_id) {
                switch (get_price_apply_age($pa_item)) {
                    case PAStatus::CLOSED:
                    case PAStatus::PAUSE:
                        if ($pa_item['date_end'] == $last['off_time']) { $last['off'][] = $pa_item; }
                        break;
                    case PAStatus::CURRENT:
                    case PAStatus::PAUSE_TODAY:
                        $last['cur'][] = $pa_item;
                        break;
                    case PAStatus::FUTURE:
                        $last['fut'][] = $pa_item;
                        break;
                }
            }
        }
        return $last;
    }



//    function get_abons_by_user_id($user_id) {
//        $SQL = "SELECT "
//                . "* "
//                . "FROM abons "
//                . "WHERE user_id=".$user_id." "
//                . "ORDER BY date_join ASC" ;
//        $abons = get_rows_by_sql($SQL);
//        foreach ($abons as &$A) {
//            $A['abon_id']           = $A['id'];
//            $A['date_join_str']     = date("Y-m-d", $A['date_join']);
//            $A['created_date_str']  = date("Y-m-d", $A['created_date']);
//            $A['modified_date_str'] = date("Y-m-d", $A['modified_date']);
//        }
//        return $abons;
//    }


//    /**
//     * Возвращает ABON_ID первого встретившегося активоного (is_payer=1) абонента,
//     * подключенного к данному пользователю
//     * @param int $user_id
//     * @return int -- $abon_id или -1 если список пуст
//     */
//    function get_abon_id_first_by_user_id(int $user_id): int {
//        $SQL = "SELECT `id` FROM `abons` WHERE (`user_id`=$user_id) AND (`is_payer`=1) LIMIT 1";
//        $rez = $this->get_rows_by_sql($SQL);
//        if (mysqli_num_rows($rez) > 0) {
//            return mysqli_fetch_assoc($rez)['id'];
//        } else {
//            return -1;
//        }
//
//    }


    function get_prices_apply_sum(int $abon_id): float {
        $sql = "SELECT "
                . "SUM(cost_value) AS cost_sum "
                . "FROM prices_apply "
                . "WHERE abon_id=".$abon_id;
        return floatval($this->get_rows_by_sql($sql)[0]['cost_sum']);
    }


    /**
     * Возвращает сумму зачисленных платежей
     * SELECT SUM(pay) AS pay_sum FROM payments WHERE abon_id=$abon_id;
     * @param int $abon_id
     * @return float -- сумма зачисленных платежей
     */
    function get_payments_pay_sum(int $abon_id): float {
        $sql = "SELECT "
                . "SUM(pay) AS pay_sum "
                . "FROM payments "
                . "WHERE abon_id=".$abon_id;
        return floatval($this->get_rows_by_sql($sql)[0]['pay_sum']);
    }


    function get_abon_rest(int $abon_id): array|null {
        $rest = $this->get_row_by_id(AbonRest::TABLE, $abon_id, AbonRest::F_ABON_ID);
        return $rest ?: null;
    }



    function get_ppp_my(int|null $active = null, int|null $type_id = null, int|null $abon_payments = null): array {
        $user_id = $_SESSION[User::SESSION_USER_REC][User::F_ID];
        $sql = "SELECT 
                * 
                FROM 
                `".Ppp::TABLE."` 
                WHERE 
                `".Ppp::F_FIRM_ID."` in 
                (
                    SELECT 
                    `".TP::F_FIRM_ID."` 
                    FROM `".TP::TABLE."` 
                    WHERE 
                    `".TP::F_ID."` in (SELECT `".TSUserTp::F_TP_ID."` FROM `".TSUserTp::TABLE."` WHERE `".TSUserTp::F_USER_ID."`={$user_id})
                    AND (`".TP::F_STATUS."`=1)
                    GROUP BY `".TP::F_FIRM_ID."`
                ) "
                .(!is_null($active) ? "AND (`active`=$active) " : "")
                .(!is_null($type_id) ? "AND (`type_id`=$type_id) " : "")
                .(!is_null($abon_payments) ? "AND (`abon_payments`=$abon_payments) " : "")
                ."ORDER BY `".Ppp::TABLE."`.`".Ppp::F_TITLE."` ASC";
        // debug($sql, '$sql');
        return $this->get_rows_by_sql($sql);
    }



    /**
     * Возвращает запись-массив параметров Абонента.
     * @param int $id
     * @return array
     */
    public function get_abon(int $id): array {
        if ($id === 0) {
            return $this->get_abon_0();
        }
        if ($this->validate_id(Abon::TABLE, $id, Abon::F_ID)) {
            return $this->get_row_by_id(Abon::TABLE, $id, Abon::F_ID);
        } else {
            throw new \Exception("get_abon(int $id) -- нет такого абонента");
        }
    }



    public function get_abon_by_hash(string $hash): array {
        return $this->get_row_by_id(table_name: Abon::TABLE, field_id: Abon::F_ID_HASH, id_value: $hash);
    }



    function get_abon_address(int $aid): string|null
    {
        if (!$this->validate_id(Abon::TABLE, $aid, Abon::F_ID)) { return null; }
        $a = $this->get_abon($aid);
        return (isset($a[Abon::F_ADDRESS]) ? $a[Abon::F_ADDRESS] : '');
    }



    /**
     * Возвращает ассоциативный массив с полями пользователя.
     * На вход получает ID абонента.
     * @param int $abon_id -- ID абонента
     * @return array
     */
    function get_user_by_abon_id(int $abon_id): array {
        return $this->get_user($this->get_abon($abon_id)[Abon::F_USER_ID]);
    }



    function get_user_id_by_abon_id(int $abon_id): int {
        return $this->get_user($this->get_abon($abon_id)[Abon::F_USER_ID])[User::F_ID];
    }


    /**
     * Возвращает html строку '[$]' флажка, показывающую является ли абонент или пользователь плательщиком
     * @param int|null $aid
     * @param int|null $uid
     */
    function get_html_chek_payer(int|null $aid = null, int|null $uid = null) {
        $payer = false;
        if (!is_null($aid)) {
            $abon = $this->get_abon($aid);
            $payer = $abon[Abon::F_IS_PAYER];
        } elseif (!is_null($uid)) {
            $A = $this->get_rows_by_field(Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $uid);
            foreach ($A as $abon) {
                if ($abon[Abon::F_IS_PAYER]) {
                    $payer = true;
                    break;
                }
            }
        }
        $check0 = "<font size=-1 face=monospace color=gray>[<font color=". GRAY.">$</font>]</font>";
        $check1 = "<font size=-1 face=monospace color=gray>[<font color=".GREEN.">$</font>]</font>";

        return get_html_CHECK(has_check: $payer, title_on: 'Есть подключения в статусе "Плательщик"', title_off: 'Не "Плательщик" ', check0: $check0, check1: $check1);
    }



    /**
     * Возвращает текстовую строку-ссылку на страницу пользователя
     * @param int $user_id
     * @return string -- Строка с html-кодом
     */
    function url_user_form(int $user_id): string {
        $c = $this->get_html_chek_payer(uid: $user_id);
        return "<nobr><a href='".Abon::URI_VIEW."/{$user_id}' target=_blank title='". $this->get_user_name($user_id)."' >$user_id</a>&nbsp;{$c}</nobr>";
    }



    /**
     * Возвращает текстовую строку-ссылку на страницу абонента (пользователя
     * @param int $abon_id
     * @return string -- Строка с html-кодом
     */
    function url_abon_form(int $abon_id): string {
        if (is_null($abon_id) || $abon_id == 0 || !$this->validate_id("abons", $abon_id)) { return $abon_id; }
        $c = $this->get_html_chek_payer(aid: $abon_id);
        return "<nobr>" . a(href: Abon::URI_VIEW . "/{$abon_id}", text: "{$abon_id}", title: $this->get_abon_address($abon_id), target: "_blank") . "&nbsp;{$c}</nobr>";
    }



    /**
     * Обновляет стоимости начислений ПФ 
     * (PA::F_COST_VALUE),
     * активные абонплаты
     * (PA::F_PPMA_VALUE, PA::F_PPDA_VALUE)
     * для всех ПФ указанного абонента.
     * Остатки для указанного абонента
     * (AbonRest::F_SUM_COST, AbonRest::F_SUM_PPMA, AbonRest::F_SUM_PPDA)
     * @param int $abon_id
     * @return bool
     */
    function recalc_abon(int $abon_id): bool {
        $result = true;
        if ($this->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {

            /**
             * Обновление стоимосьти ПФ
             */
            MsgQueue::msg(MsgType::INFO_AUTO, __("COST") . ': ' . __("Обновляем стоимость начисления в прайсовых фрагментах") . " [".$abon_id."]...");
            if ($this->update_prices_cost_all($abon_id)) {
                MsgQueue::msg(MsgType::INFO_AUTO, __("COST") . ': ' . __("Успешно."));
            } else {
                MsgQueue::msg(MsgType::INFO_AUTO, __("COST") . ': ' . __("Ошибка"));
                MsgQueue::msg(MsgType::ERROR, $this->errorInfo());
                $result = false;
            }

            /**
             * Обновление активных абонплат
             */
            MsgQueue::msg(MsgType::INFO_AUTO, __("PA") . ': ' . __('Обновляем активные абонплаты прайсовых фрагментов') . " [".$abon_id."]...");
            if ($this->update_prices_active_all($abon_id)) {
                MsgQueue::msg(MsgType::INFO_AUTO, __("PA") . ': ' . __("Успешно."));
            } else {
                MsgQueue::msg(MsgType::INFO_AUTO, __("PA") . ': ' . __("Ошибка"));
                MsgQueue::msg(MsgType::ERROR, $this->errorInfo());
                $result = false;
            }

            /**
             * Обновление остатков
             */
            MsgQueue::msg(MsgType::INFO_AUTO, __("REST") . ': ' . __('Обновляем активные остатки') . " [".$abon_id."]...");
            if ($this->update_abon_rest_all($abon_id)) {
                MsgQueue::msg(MsgType::INFO_AUTO, __("REST") . ': ' . __("Успешно."));
            } else {
                MsgQueue::msg(MsgType::INFO_AUTO, __("REST") . ': ' . __("Ошибка"));
                MsgQueue::msg(MsgType::ERROR, $this->errorInfo());
                $result = false;
            }
        }
        return $result;
    }






}