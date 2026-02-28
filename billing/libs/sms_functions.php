<?php
/**
 *  Project : my.ri.net.ua
 *  File    : sms_functions.php
 *  Path    : billing/libs/sms_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Nov 2025 22:01:09
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of sms_functions.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\App;
use billing\core\PhoneTools;
use config\tables\AbonRest;

function get_sms_info_rec($text) {
    $chars1sms = App::get_config('sms_chars1sms');
    $len = iconv_strlen($text, 'UTF-8');
    $full_sms = (int)($len / $chars1sms);
    $char_in_last_sms = $len - ($full_sms * $chars1sms);
    $ret = [
        'len' => $len,
        'count_sms' => ((int)(($len-1) / $chars1sms) + 1),
        'full_sms' => $full_sms,
        'char_in_last_sms' => $char_in_last_sms,
        'chars1sms' => $chars1sms
    ];
    return $ret;
}



/**
 * Формирует массив данных для отправки СМС-уведомления абоненту с информацией о его задолженности и предоплате.
 * Принимает на вход ID абонента, его основной номер телефона, сумму предоплаты за 30 дней (PP30) и текущий баланс.
 * Возвращает массив с данными для отправки СМС, включая ID абонента, команду для отправки СМС, очищенный номер телефона и текст сообщения.
 *    ['abon_id'] => ID абонента, которому отсылается СМС
 *    ['cmd'] => Команда для отправки СМС, например, имя скрипта или веб-службы, которая будет использоваться для отправки СМС
 *    ['phone'] => Очищенный номер телефона, на который будет отправлено СМС. 
 *    ['text'] => Текст СМС сообщения.
 * 
 * @param mixed $abon_id
 * @param mixed $phone_main
 * @param mixed $pp30
 * @param mixed $balance
 * @throws Exception
 * @return array
 */
function get_sms_debts_rec($abon_id, $phone_main, $pp30, $balance) {
    $sms['abon_id'] = $abon_id;
    $sms['cmd'] = App::get_config('sms_sender');
    $sms['phone'] = PhoneTools::simpleCleaning($phone_main);
    if (!$sms['phone']) {
        throw new Exception("Абон: [$abon_id]. Не верный номер телефона: [$phone_main]");
    }
    $sms['text'] = "";
    switch (true) {
            case (!($pp30 > 0)):
                $sms['text'] = "RILAN:Дог.".$abon_id."."." Для відновлення послуги телефонуйте.";
                break;
            case ($balance > $pp30/5):
                $sms['text'] = "RILAN:Дог.".$abon_id." Залиш.".round($balance)."грн".($pp30>0?", абонпл.".round($pp30)."гр/м":"")."";
                break;
            case ($balance >= 0):
                $sms['text'] = "RILAN:Дог.".$abon_id." Залиш.".round($balance)."гр".($pp30>0?", абонпл.".round($pp30)."гр/м":"")."".($pp30>0?". До сплати ".round($pp30)."гр":"")."";
                break;
            case ($balance < 0):
                $sms['text'] = "RILAN:Дог.".$abon_id." Залиш.".round($balance)."гр".($pp30>0?", абонпл.".round($pp30)."гр/м":"")."".($pp30>0?". До сплати ".round($pp30-$balance,-1)."гр":". До сплати ".round(-$balance,0)."грн")."";
                break;
            default :
                throw new Exception("get_sms_debts_rec: этого не должно быть.<br><pre>get_sms_debts_rec($abon_id, $phone_main, $pp30, $balance)</pre>");
    }
    return $sms;
}



function compare_abons(array $a, array $b): int {
    $ar = $a[AbonRest::TABLE];
    $br = $b[AbonRest::TABLE];

    if ($ar[AbonRest::F_SUM_PP30A] == 0 && $br[AbonRest::F_SUM_PP30A] != 0) { return -1; }
    if ($ar[AbonRest::F_SUM_PP30A] != 0 && $br[AbonRest::F_SUM_PP30A] == 0) { return 1; }
    if ($ar[AbonRest::F_SUM_PP30A] == 0 && $br[AbonRest::F_SUM_PP30A] == 0) { 
        if ($ar[AbonRest::F_DATE_PAUSED] > $br[AbonRest::F_DATE_PAUSED]) { return 1; }
        if ($ar[AbonRest::F_DATE_PAUSED] < $br[AbonRest::F_DATE_PAUSED]) { return -1; }
        return 0; 
    }

    if ($ar[AbonRest::F_PREPAYED] > $br[AbonRest::F_PREPAYED]) { return 1; }
    if ($ar[AbonRest::F_PREPAYED] < $br[AbonRest::F_PREPAYED]) { return -1; }
    return 0; 
}
