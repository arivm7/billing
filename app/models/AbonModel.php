<?php



namespace app\models;



use config\tables\Abon;
use config\tables\Notify;
use config\tables\Pay;
use config\tables\Price;
use config\SessionFields;
use config\tables\TP;
use config\tables\AbonRest;
use config\tables\PA;



/**
 * Статус прикрепленных прайсовых фрагментов
 * CLOSED, CURRENT, FUTURE
 */
enum PAStatus {
    case FUTURE;
    case CURRENT;
    case CLOSE_TODAY;
    case CLOSED;
    case FULL_CLOSED;
}



class AbonModel extends UserModel {


    /**
     * Заполнить таблицу остатков `abon_rest` на ЛС у всех абонентов
     * собираем вместе поля: abon_id, sum_pay, sum_cost, sum_PPMA, sum_PPDA
     * @param bool $force
     * @return bool
     */
    function update_abon_rest(bool $force = false): bool {

        if  (!isset($_COOKIE[SessionFields::A_REST_FIELD]) || $force) {

            $sql = "TRUNCATE TABLE ".AbonRest::TABLE.";"
                 . "INSERT INTO "
                    . AbonRest::TABLE." ("
                        . AbonRest::F_ABON_ID  . ", "
                        . AbonRest::F_SUM_PAY  . ", "
                        . AbonRest::F_SUM_COST . ", "
                        . AbonRest::F_SUM_PPMA . ", "
                        . AbonRest::F_SUM_PPDA
                    . ") "
                 . "SELECT
                        a.abon_id,
                        IFNULL(p.sum_pay, 0) AS sum_pay,
                        IFNULL(pa.sum_cost, 0) AS sum_cost,
                        IFNULL(pa.sum_PPMA, 0) AS sum_PPMA,
                        IFNULL(pa.sum_PPDA, 0) AS sum_PPDA
                    FROM
                        (
                            SELECT abon_id FROM payments
                            UNION
                            SELECT abon_id FROM prices_apply
                        ) a
                    LEFT JOIN (
                        SELECT abon_id, SUM(pay) AS sum_pay
                        FROM payments
                        GROUP BY abon_id
                    ) p ON p.abon_id = a.abon_id
                    LEFT JOIN (
                        SELECT
                        abon_id,
                        SUM(cost_value) AS sum_cost,
                        SUM(`PPMA_value`) AS sum_PPMA,
                        SUM(`PPDA_value`) AS sum_PPDA
                        FROM prices_apply
                        GROUP BY abon_id
                    ) pa ON pa.abon_id = a.abon_id;";
            if ($this->execute($sql)) {
                self::setcookie(
                        name: SessionFields::A_REST_FIELD,
                        value: SessionFields::A_REST_VALUE,
                        expires_or_options: time() + SessionFields::A_REST_TIME
                );
                return true;
            }
            return false;
        }
        return true;
    }



    function get_tp_list_with_abon(int $abon_id, int|null $closed = null): array {
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
            return $this->get_rows_by_where(table: TP::TABLE, where: TP::F_ID . ' IN (' . implode(',', $tp_id_list) . ') AND `'.TP::F_STATUS.'`=1');
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
     * Возвращает статус прайсового фрагмента, относительно указанной даты
     * Статусы: закрытый, текущий, будущий.
     * @param  int $price_apply
     * @param  int $today
     * @return int -- PRICES_APPLY_FUTURE, PRICES_APPLY_CURRENT, PRICES_APPLY_CLOSED
     */
    public static function get_price_apply_age(array $price_apply, int $today = NA): PAStatus {

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
            return PAStatus::FULL_CLOSED;
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
            return PAStatus::CLOSE_TODAY;
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
            return PAStatus::CLOSED;
        }

        echo "get_prices_apply_age:<br>этого не должно быть<br><pre>". print_r($price_apply, true)."</pre><hr>";
        echo "start: ".$price_apply['date_start']." ".date("Y-m-d H:i:s", $price_apply['date_start'])."<br>";
        echo "end: ".$price_apply['date_end']." >= ".date("Y-m-d H:i:s", $price_apply['date_end'])."<br>";
        throw new \Exception("get_prices_apply_age:<br>этого не должно быть<br><pre>". print_r($price_apply, true)."</pre>");
        // return NA;

    }



    public static function get_price_apply_cost($price_apply, $today=NA) { //2022
        if($today == NA) { $today = TODAY(); } else { $today = get_date($today); }
        $cost = 0.0;
        // echo "<pre>prices_apply:<br>".print_r($price_apply, true)."</pre>" ;
        switch (self::get_price_apply_age($price_apply, $today)) {

          case PAStatus::CURRENT:
          case PAStatus::CLOSE_TODAY:
            $price_apply[PA::F_DATE_END] = $today;                                //echo "Активный прайсовый фрагмент<br>";

          case PAStatus::CLOSED:
          case PAStatus::FULL_CLOSED:
            if(abs($price_apply[PA::FF_P_PPD]) > 0) {

                /*
                $d1 = new DateTime("@".$prices_apply['date_start']);
                $d2 = new DateTime("@".$prices_apply['date_end']);
                $interval = date_diff($d1, $d2);
                $cost += (($interval->days + 1) * (double)$prices_apply['pay_per_day']);
                 */
                $d1 = $price_apply[PA::F_DATE_START];
                $d2 = $price_apply[PA::F_DATE_END];
                $interval = get_between_days($d1, $d2);
                $cost += (($interval + 1) * (double)$price_apply[PA::FF_P_PPD]);

            }
            if(abs($price_apply[PA::FF_P_PPM]) > 0) {
                // дни начисления в одном месяце
                if(date("Ym", $price_apply[PA::F_DATE_START]) == date("Ym", $price_apply[PA::F_DATE_END])) {
                    $days_of_month = days_of_month($price_apply[PA::F_DATE_START]);
                    $days_costed = day($price_apply[PA::F_DATE_END]) - day($price_apply[PA::F_DATE_START]) + 1;
                    $cost += ((double)$price_apply[PA::FF_P_PPM] / $days_of_month * $days_costed);
                } else {
                    // дни начисления в разных месяцах
                    //дней в первом месяце
                    $day_start = day($price_apply[PA::F_DATE_START]);
                    $days_of_month = days_of_month($price_apply[PA::F_DATE_START]);
                    $days_costed = $days_of_month - $day_start + 1;                  //echo "Оплачиваемых дней в певом месяце = ".$days_costed."<br>";
                    $cost += ((double)$price_apply[PA::FF_P_PPM] / $days_of_month * $days_costed);

                    //дней в последнем месяце
                    $day_end = day($price_apply[PA::F_DATE_END]);
                    $days_of_month = days_of_month($price_apply[PA::F_DATE_END]);
                    $days_costed = $day_end;                                    //echo "Оплачиваемых дней в поледнем месяце = ".$days_costed."<br>";
                    $cost += ((double)$price_apply[PA::FF_P_PPM] / $days_of_month * $days_costed);

                    //полных месяцев
                    $d1_str = year($price_apply[PA::F_DATE_START])."-".month($price_apply[PA::F_DATE_START])."-".(days_of_month($price_apply[PA::F_DATE_START])-1); //echo "Рассчёт месяцев 1 = ".$d1_str."<br>";
                    $d2_str = year($price_apply[PA::F_DATE_END])."-".month($price_apply[PA::F_DATE_END])."-02";                                              //echo "Рассчёт месяцев 2 = ".$d2_str."<br>";
                    $d1 = date_create($d1_str);
                    $d2 = date_create($d2_str);
                    $interval = date_diff($d1, $d2);                                //echo "Оплачиваемых месяцев = ".($interval->m + $interval->y * 12)."<br>";
                    $cost += ((double)$price_apply[PA::FF_P_PPM] * ($interval->m + $interval->y * 12));
                    //echo "<pre>prices_apply:<br>".print_r($interval, true)."</pre>";
                }
            }
            return $cost;
            //break;

          case PAStatus::FUTURE:
            return 0;
            //break;

          default:
            exit("<hr>Этого не должно быть:<br>get_price_apply_cost():<br><pre>".print_r($price_apply, true)."</pre><hr>");

        }
    }



    /**
     * Считает правильно
     * @param array $price_apply
     * @param int $today
     * @return array
     */
    public static function get_price_apply_cost_per_montch(array $price_apply, int $today): array {
        $struct = array();

        $pa_age = self::get_price_apply_age($price_apply, $today);
//        debug($pa_age);
        switch ($pa_age) {
            case PAStatus::FUTURE:
                return $struct;
                // break;

            case PAStatus::CURRENT:
            case PAStatus::CLOSE_TODAY:
                $price_apply[PA::F_DATE_END] = $today;
                $price_apply[PA::F_DATE_END_STR] = date("Y-m-d", $price_apply[PA::F_DATE_END]);

            case PAStatus::CLOSED:
            case PAStatus::FULL_CLOSED:
                // считаем
                break;

            default:
                throw new \Exception("<hr>Этого не должно быть:<br>get_price_apply_cost_per_montch():<br><pre>". print_r($price_apply, 1)."</pre><hr>");
                //break;
        }

        $index = 0;
        $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
        $struct[$index]['date']     = get_date($price_apply[PA::F_DATE_START]);
        $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
        $struct[$index]['text']     = (isset($price_apply[PA::FF_P_TITLE])?$price_apply[PA::FF_P_TITLE]:"");
        $struct[$index]['name']     = (isset($price_apply[PA::F_NET_NAME])?$price_apply[PA::F_NET_NAME]:"");
        $struct[$index]['cost']      = 0;

        if(     // если прайсовый фрагмент что-то начисляет, то
                (abs($price_apply[PA::FF_P_PPD])   > 0) ||
                (abs($price_apply[PA::FF_P_PPM]) > 0)
          ) {
            // дни начисления в одном месяце
            if(date("Ym", $price_apply[PA::F_DATE_START]) == date("Ym", $price_apply[PA::F_DATE_END])) {
                $days_of_month = date("t", $price_apply[PA::F_DATE_START]);
                $days_costed = date("j", $price_apply[PA::F_DATE_END]) - date("j", $price_apply[PA::F_DATE_START]) + 1;
                $cost = self::get_price_apply_cost($price_apply, $today);
                $index++;
                $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                $struct[$index]['date']     = get_date($price_apply[PA::F_DATE_END]);
                $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                $struct[$index]['text']     = $price_apply[PA::FF_P_TITLE] ?? "";
                $struct[$index]['name']     = $price_apply[PA::F_NET_NAME] ?? "";
                $struct[$index]['cost']     = $cost;
            } else {
                // дни начисления в разных месяцах
                //дней в первом месяце
                $day_start = day($price_apply[PA::F_DATE_START]);
                $days_of_month = \days_of_month($price_apply[PA::F_DATE_START]);
                $days_costed = $days_of_month - $day_start + 1;                  //echo "Оплачиваемых дней в певом месяце = ".$days_costed."<br>";
                $cost = ((double)$price_apply[PA::FF_P_PPM] / $days_of_month * $days_costed)
                      + ((double)$price_apply[PA::FF_P_PPD] * $days_costed);
                $index++;
                $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                $struct[$index]['date']     = mktime(0, 0, 0, month($price_apply[PA::F_DATE_START]), days_of_month($price_apply[PA::F_DATE_START]), year($price_apply[PA::F_DATE_START]));
                $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                $struct[$index]['text']     = $price_apply[PA::FF_P_TITLE] ?? "";
                $struct[$index]['name']     = $price_apply[PA::F_NET_NAME] ?? "";
                $struct[$index]['cost']     = $cost;

                //дней в последнем месяце
                $day_end = day($price_apply[PA::F_DATE_END]);
                $days_of_month = days_of_month($price_apply[PA::F_DATE_END]);
                $days_costed = $day_end;                                         //echo "Оплачиваемых дней в поледнем месяце = ".$days_costed."<br>";
                $cost = ((double)$price_apply[PA::FF_P_PPM] / $days_of_month * $days_costed)
                      + ((double)$price_apply[PA::FF_P_PPD] * $days_costed);
                $index++;
                $struct[$index]['pa_id']    = $price_apply[PA::F_ID];
                $struct[$index]['date']     = get_date($price_apply[PA::F_DATE_END]);
                $struct[$index]['date_str'] = date("Y-m-d", $struct[$index]['date']);
                $struct[$index]['text']     = $price_apply[PA::FF_P_TITLE] ?? "";
                $struct[$index]['name']     = $price_apply[PA::F_NET_NAME] ?? "";
                $struct[$index]['cost']     = $cost;

                //полных месяцев
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
                        $struct[$index]['text']     = (isset($price_apply[PA::FF_P_TITLE])?$price_apply[PA::FF_P_TITLE]:"");
                        $struct[$index]['name']     = (isset($price_apply[PA::F_NET_NAME])?$price_apply[PA::F_NET_NAME]:"");
                        $days_of_month = date("t", mktime(0, 0, 0, $m, 1, $y));
                        $cost = ((double)$price_apply[PA::FF_P_PPM])
                              + ((double)$price_apply[PA::FF_P_PPD] * $days_of_month);
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
         * * тестирование конкретного прайсового фрагмента
        if($prices_apply['id'] == 1741) {
            echo "<hr>1741:<pre>".print_r($struct, true)."</pre><hr>";
        }
         */
        return $struct;
    }



    /**
     * Список уведомлений (СМС)
     * @param int $abon_id
     * @param int|string|null $limit
     * @return array
     */
    function get_notify_by_abon_id(int $abon_id, int|string|null $limit = null): array
    {
        return $this->get_rows_by_field(
                table: Notify::TABLE,
                field_name: Notify::F_ABON_ID,
                field_value: $abon_id,
                order_by: Notify::F_ID . ' DESC',
                limit: $limit);
    }



    /**
     * Список прикрепленных прайсвых фрагментов, включая название прикрепленного прайса
     * @param int $abon_id
     * @return array
     */
    function get_pa_by_abon_id(int $abon_id): array
    {
        $sql = "SELECT "
                . "`".Price::TABLE."`.`".Price::F_TITLE."` AS ".PA::F_PRICE_TITLE.", "
                . "`".PA::TABLE."`.* "
                . "FROM `".PA::TABLE."` "
                . "LEFT JOIN ".Price::TABLE." ON ".Price::TABLE.".".Price::F_ID." = ".PA::TABLE.".".PA::F_PRICE_ID." "
                . "WHERE `".PA::TABLE."`.`".PA::F_ABON_ID."`={$abon_id} "
                . "ORDER BY "
                    . "(`".PA::TABLE."`.`".PA::F_DATE_END."` IS NOT NULL)," //  -- сначала идут строки с NULL
                    . "`".PA::TABLE."`.`".PA::F_DATE_END."` DESC,"          //  -- затем сортировка по убыванию date_end
                    . "`".PA::TABLE."`.`".PA::F_DATE_START."` DESC";        //  -- потом по убыванию date_start
        return $this->get_rows_by_sql($sql);
    }


    function get_abons_by_uid(int $user_id): array {
        return $this->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user_id, order_by: Abon::F_DATE_JOIN . ' DESC');
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
                prices.title,
                prices.pay_per_day,
                prices.pay_per_month,
                prices.description,
                tp_list.title                                                      AS tp_title,
                tp_list.status                                                     AS tp_status,
                tp_list.deleted                                                    AS tp_deleted,
                tp_list.is_managed                                                 AS tp_is_managed
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



    function get_payments(
            int      $abon_id,
            int|null $pay_type = null,
            int|null $ppp_id = null
        ): array
    {
        $sql = "SELECT "
                . "*  "
                . "FROM "
                . "`".Pay::TABLE."` "
                . "WHERE "
                . "`".Pay::F_ABON_ID."`={$abon_id} "
                . ($pay_type ? "AND `".Pay::F_PAY_TYPE_ID."`=1 " : "")
                . ($ppp_id ? "AND `".Pay::F_PAY_PPP_ID."` = {$ppp_id} " : "")
                . "ORDER BY "
                . "`".Pay::TABLE."`.`".Pay::F_PAY_DATE."` DESC";
        return $this->get_rows_by_sql($sql);
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


}
