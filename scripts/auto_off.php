<?php
//echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";

const APP_NAME = "RI-BILLING";

use app\models\AbonModel;
use billing\core\base\Model;
use config\tables\Abon;
use config\tables\AbonRest;

require __DIR__    . '/../config/dirs.php';
require DIR_CONFIG . '/ini.php';
require DIR_LIBS   . '/common.php';
require DIR_LIBS   . '/functions.php';

/**
 * Автозагручик Composer'а
 */
require __DIR__    . '/../vendor/autoload.php';

$model = new AbonModel();

echo "AUTO_OFF | ".time()." | ".date("Y-m-d G:i:s")."\n";

// Имитируем авторизацию от пользователя billling
// $UID = 11;  // billng
// $_SESSION['id'] = $UID;

$SQL = "SELECT
            abons.id AS abon_id,
            abons.address,
            abons.duty_max_off,
            abons.duty_wait_days
        FROM
            prices_apply
            LEFT JOIN abons ON abons.id = prices_apply.abon_id
        WHERE
        ( abons.is_payer = 1 ) 
        AND
        ( abons.duty_auto_off = 1 )
        AND
        (
            (
                (prices_apply.date_start < UNIX_TIMESTAMP()) AND
                (isnull(prices_apply.date_end))
            )
            OR
            (
                prices_apply.date_end > UNIX_TIMESTAMP()
            )
        )
        GROUP BY abons.id
        ";

$alist = $model->get_rows_by_sql($SQL);
$max_width_str = 40; // Ширина колонки "адрес"
if ($alist) {
    $count_AID = count($alist);
    echo "Строк: ".$count_AID."\n";
    echo ""
    . ".------------" .  str_repeat("-", $max_width_str)             . "-.------------.-----------.----------.-------.-----------.---------.----------.\n"
    . "|  abon_id | ".sprintf("%-".($max_width_str)."s", "Адрес")."      | Начислено: | Оплачено: | Залишок: | PP01: | Опл. дней | граница | вкл/выкл |\n"
    . "|----------|-" .  str_repeat("-", $max_width_str)             . "-|------------|-----------|----------|-------|-----------|---------|----------|\n";
    for ($i = 0; $i < $count_AID; $i++) {
        $abon = &$alist[$i];
        $rest = $model->get_abon_rest($abon[Abon::F_ABON_ID]);
        //
        // очистка
        $abon[Abon::F_ADDRESS] = html_entity_decode($abon[Abon::F_ADDRESS]);
        $abon[Abon::F_ADDRESS] = str_replace(search: '"', replace: "`", subject: $abon[Abon::F_ADDRESS]);
        $abon[Abon::F_ADDRESS] = str_replace(search: "'", replace: "`", subject: $abon[Abon::F_ADDRESS]);
        $abon[Abon::F_ADDRESS] = str_replace(search: "<", replace: "`", subject: $abon[Abon::F_ADDRESS]);
        $abon[Abon::F_ADDRESS] = str_replace(search: ">", replace: "`", subject: $abon[Abon::F_ADDRESS]);
        //
        //

        if(iconv_strlen($abon[Abon::F_ADDRESS], "UTF-8") > $max_width_str) {
            $abon[Abon::F_ADDRESS] = "<".mb_substr($abon[Abon::F_ADDRESS], -($max_width_str-1), $max_width_str-1);
        } else {
            while (iconv_strlen($abon[Abon::F_ADDRESS], "UTF-8") < $max_width_str) {
                $abon[Abon::F_ADDRESS] .= " ";
            }
        }

        if (round($rest[AbonRest::F_SUM_PP01A] * 100) > 0) {
            $s  = "| ".sprintf("%8d", $abon[Abon::F_ABON_ID])." | "
                    . sprintf("%-".($max_width_str)."s", $abon[Abon::F_ADDRESS])." | "
                    . "".sprintf("%10.2f", $cost=$rest[AbonRest::F_SUM_COST])." | "
                    . "".sprintf("%9.2f", $pays=$rest[AbonRest::F_SUM_PAY])." | "
                    . "".sprintf("%8.2f", $rest[AbonRest::F_BALANCE])." | "
                    . "".sprintf("%5.2f", $rest[AbonRest::F_SUM_PP01A])." | "
                    . "".   ( $rest[AbonRest::F_SUM_PP01A] > 0 
                                ?   sprintf("%9.2f", $rest[AbonRest::F_PREPAYED])
                                :   sprintf("%9s", '-')
                            ) . " | "
                    . "".sprintf("%7d", $abon[Abon::F_DUTY_MAX_OFF])." | "
                    . "".   (($rest[AbonRest::F_PREPAYED] < $abon[Abon::F_DUTY_MAX_OFF])
                                ?   "  [".($abon[Abon::F_DUTY_WAIT_DAYS] > 0 
                                        ?   $abon[Abon::F_DUTY_WAIT_DAYS] 
                                        :   "x") . "]    |\n|          |\n"
                                            .   ($abon[Abon::F_DUTY_WAIT_DAYS] > 0
                                                    ?   ($model->set_field_value(Abon::TABLE, Abon::F_ID, $abon[Abon::F_ABON_ID], Abon::F_DUTY_WAIT_DAYS, --$abon[Abon::F_DUTY_WAIT_DAYS], false)
                                                            ?   "|          | Режим ожидания платежа ".$abon[Abon::F_DUTY_WAIT_DAYS]." дней"
                                                            :   "|          | ОШИБКА: изменение количества дней ожидания платежа не удалось"
                                                        )
                                                    :   $model->set_abon_pause($abon[Abon::F_ABON_ID])
                                                )
                                    . "\n|          |"
                                :   "         |"
                            ) 
                            . "";
            //echo str_replace(" ", "&nbsp;", $s)."\n";
            echo $s."\n";
            flush();
        } else { 
            $s  = "| ".sprintf("%8d", $abon['abon_id'])." | "
                    . sprintf("%-".($max_width_str)."s", $abon['address'])." | "
                    . "".sprintf("%10.2f", $rest[AbonRest::F_SUM_COST])." | "
                    . "".sprintf("%9.2f", $rest[AbonRest::F_SUM_PAY])." | "
                    . "".sprintf("%8.2f", $rest[AbonRest::F_BALANCE])." | "
                    . "".sprintf("%5.2f", $rest[AbonRest::F_SUM_PP01A])." | "
                    . "".   ( $rest[AbonRest::F_SUM_PP01A] > 0 
                                ?   sprintf("%9.2f", $rest[AbonRest::F_PREPAYED])
                                :   sprintf("%9s", '-')
                            ) . " | "
                    . "СЛУЖЕБНЫЙ (НУЛЕВОЙ)";
            
            echo $s."\n";
        }
    }
    echo ""
    . " ----------------------------------------------------- ------------ ----------- ---------- ------- ----------- --------- ---------- \n\n";

} else {
    echo "Ошибка запроса списка абонентов с автоотключением ".my_error()."<br />";
    exit;
}


?>
