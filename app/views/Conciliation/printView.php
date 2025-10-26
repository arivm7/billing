<?php
/*
 *  Project : my.ri.net.ua
 *  File    : printView.php
 *  Path    : app/views/Conciliation/printView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of printView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\base\View;
use config\Sym;
use config\tables\Abon;

/*
 * вывод полной таблицы событий
 */
if(isset($_GET['debug']) && ($_GET['debug']=='1')) {
    $balance = 0;
    $I_COLOR_STEP = 1;
    echo "<table width=650 border=0 cellpadding=5 cellspacing=2>";
    echo  "<tr bgcolor='".(is_odd($I_COLOR_STEP++) ? COLOR1_VALUE : COLOR2_VALUE)."'>"
            . "<th>Дата</th>"
            . "<th>pa_id</th>"
            . "<th>name</th>"
            . "<th>text</th>"
            . "<th align=right nowrap>cost</th>"
            . "<th align=right nowrap>pay</th>"
            . "<th align=right nowrap>balance</th>"
        . "</tr>";
    foreach ($events as $e) {
        //if(isset($e['pa_id']) && ($e['pa_id'] == 1621)) { echo "<pre>".print_r($e, true)."</pre>"; }
        $balance = $balance - (isset($e['cost'])?$e['cost']:0) + (isset($e['pay'])?$e['pay']:0);
        echo ""
        . "<tr bgcolor='".(is_odd($I_COLOR_STEP++) ? COLOR1_VALUE : COLOR2_VALUE)."'>"
            . "<td>".date("Y-m-d", $e['date'])."</td>"
            . "<td>".(isset($e['pa_id'])?$e['pa_id']:"")."</td>"
            . "<td>".(isset($e['name'])?$e['name']:"")."</td>"
            . "<td>".(isset($e['text'])?$e['text']:"")."</td>"
            . "<td align=right nowrap>".(isset($e['cost'])?number_format(floatval($e['cost']),2,","," "):"")."</td>"
            . "<td align=right nowrap>".(isset($e['pay'])?number_format(floatval($e['pay']),2,","," "):"")."</td>"
                            //. "pay_fakt, "
                            //. "pay, "
            . "<td align=right nowrap>".number_format(floatval($balance),2,","," ")."</td>"
        . "</tr>";
    }
    echo "</table>";
    echo '<hr>';
}
?>
<style>
/* Кнопки фиксируем в углу, поверх контента */
.print-buttons {
  position: fixed;
  top: 10px;
  left: 10px;
  display: flex;
  gap: 10px;
  z-index: 9999; /* всегда сверху */
}

.print-buttons button {
  padding: 6px 12px;
  font-size: 14px;
  cursor: pointer;
}

/* Скрываем при печати */
@media print {
  .print-buttons {
    display: none !important;
  }
}
</style>
<style>
    body {
        margin-left: 1.5cm; /* 28.346px; 1 см */
        margin-top: 0cm;
    }
</style>
<div class="print-buttons">
    <button onclick="window.close();">Закрыть</button>
    <!--<button onclick="window.history.back();">Назад</button>-->
    <button onclick="window.print();">Печать</button>
</div>
<div style='width:17cm;'>
<h1 align=center><font size=5>Акт звіряння розрахунків<br>
    станом на <?=date("d.m.Y", $today);?> р.</font><br>
    <font size=3>за аренду цифрового порта <?= Sym::CH_NUMERO;?><u>&nbsp;<?= $abon[Abon::F_ID];?>&nbsp;</u>, що підключений за адресою:<br>
    <u>&nbsp;<?= $abon[Abon::F_ADDRESS];?>&nbsp;</u></font>
</h1>

<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>
    Акт складено між <nobr><u>&nbsp;<?=$agents[0]['name_long'] ?? '';?>&nbsp;</u></nobr> з одного боку,
    та <nobr><u>&nbsp;<?= $contragents[0]['name_long']; ?>&nbsp;</u></nobr> з іншого,
    за період з <nobr><u>&nbsp;<?= date("d.m.Y р.", $date1); ?>&nbsp;</u></nobr>
    по <nobr><u>&nbsp;<?= date("d.m.Y р.", $today); ?>&nbsp;</u></nobr> включно.</p>
<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>
    &laquo;Нарахування&raquo; <?=Sym::CH_DASH;?> сума вартості наданих полуг згідно
    &laquo;Публічного договору надання послуги оренди порту для доступу до мережі Інтернет&raquo;
    за порт <b><?= Sym::CH_NUMERO;?>&nbsp;<?=$abon[Abon::F_ID];?></b>,
    підключений за адресою: <b><u>&nbsp;<?=$abon[Abon::F_ADDRESS];?>&nbsp;</u></b></p>
<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>
    &laquo;Сплати&raquo; <?=Sym::CH_DASH;?> суми платежів від <u>&nbsp;<?=$contragents[0]['name_short'] ?? '';?>&nbsp;</u>
    на р/р <u>&nbsp;<?=$agents[0]['name_short'] ?? 'Провайдера';?>&nbsp;</u>
    для сплати полуг доступу до мережі інтренет</p>
<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>
    &laquo;Сальдо&raquo; <?= Sym::CH_DASH;?> залишок коштів <u>&nbsp;<?=$contragents[0]['name_short'] ?? 'Абонента';?>&nbsp;</u>
    на р/р <u>&nbsp;<?=$agents[0]['name_short'] ?? 'Провайдера';?>&nbsp;</u>
    для сплати полуг. Коли залишок від'ємний <?= Sym::CH_DASH;?> це сума заборгованності при нестачі сплачених коштів за вже надані послуги.</p>
<?php
    /**
     * Если начало вывода после даты начала событий
     * то пропускаем все ранние события
     * при этом считаем баланс
     */
    $balance = 0;
    if(first_day_month($date1) > $months[0]['date']) {
        //echo "скипаем...<br>";
        for ($index = 0; $index < count($months); $index++) {
            $e = $months[$index];
            if($e['date'] < $date1) {
                $balance = $balance-(isset($e['cost'])?$e['cost']:0)+(isset($e['pay'])?$e['pay']:0);
            } else {
                break;
            }
        }
    }
?>
<!-- вывод таблицы по месяцам -->
<hr>
<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>
    За данними білінгової системи <nobr><u>&nbsp;<?=$agents[0]['name_short'] ?? 'Провайдера';?>&nbsp;</u></nobr>
    сальдо на початок звіряємого періода складає <b><u>&nbsp;<?=number_format(floatval($balance),2,","," ");?>&nbsp;грн&nbsp;</u></b>
</p>
<table width=100% align=center border=1 cellpadding=7 cellspacing=0>
<?php
    $balance = 0;
    $year_curr = 0;
    $num_line = 1;
    for ($index = 0; $index < count($months); $index++) {
        $e = $months[$index];
        $balance=$balance-(isset($e['cost'])?$e['cost']:0)+(isset($e['pay'])?$e['pay']:0);
        if($e['date'] >= first_day_month($date1)) {
            if($year_curr != year($e['date'])) {
                $year_curr = year($e['date']);
                echo ""
                . "<tr bgcolor='".COLOR3_VALUE."'>"
                    . "<td align=center colspan=6>&nbsp;".$year_curr." рік.&nbsp;</td>"
                . "</tr>"
                . "<tr bgcolor='".COLOR1_VALUE."'>"
                    . "<td rowspan=2 align=center>". Sym::CH_NUMERO."</td>"
                    . "<td rowspan=2 align=center>Розрахунковий місяць</td>"
                    . "<td colspan=3 align=center>За данними білінгової системи<br><nobr><u>&nbsp;".($agents[0]['name_short'] ?? 'Провайдера')."&nbsp;</u></nobr></td>"
                    . "<td rowspan=2 align=center>Розбіжності за данними<br><u>&nbsp;".($contragents[0]['name_short'] ?? 'Абонента')."&nbsp;</u></td>"
                . "</tr>"
                . "<tr bgcolor='".COLOR1_VALUE."'>"
                    . "<td style='width:21mm;' align=center><font size=-1>Нарахування</font></td>"
                    . "<td style='width:21mm;' align=center><font size=-1>Сплати</font></td>"
                    . "<td style='width:21mm;' align=center>Сальдо</td>"
                . "</tr>";
            }
            echo ""
            . "<tr bgcolor='".COLOR_WHITE."'>"
                . "<td align=right><font color=gray>&nbsp;".($num_line++).".&nbsp;</font></td>"
                . "<td>".ukr_in_date("[m] M", $e['date'])."</td>"
                . "<td align=right nowrap>".((isset($e['cost']) && (abs($e['cost']) > 0))?number_format(floatval($e['cost']),2,","," "):"")."</td>"
                . "<td align=right nowrap>".((isset($e['pay']) && (abs($e['pay']) > 0))?number_format(floatval($e['pay']),2,","," "):"")."</td>"
                . "<td align=right nowrap>".number_format(floatval($balance),2,","," ")."</td>"
                . "<td align=right></td>"
            . "</tr>";
        }
    }
?>
</table>
<br>

<?php

        echo "<div style='position:relative; z-index:20; overflow:visible;'>";
        if($balance >= 0) {
            echo "<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>"
                    . "Станом на ".date("d.m.Y", $today)." "
                    . "заборгованність <u>&nbsp;".($contragents[0]['name_long'] ?? '')."&nbsp;</u> "
                    . "перед <u>&nbsp;".($agents[0]['name_short'] ?? '')."&nbsp;</u> відсутня. "
                    . "</p>";
        } else {
            echo "<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>"
                    . "Станом на ".date("d.m.Y", $today)." "
                    . "заборгованність <u>&nbsp;".($contragents[0]['name_long'] ?? '')."&nbsp;</u> "
                    . "перед <u>&nbsp;".($agents[0]['name_short'] ?? '')."&nbsp;</u> "
                    . "за надані послуги складає <nobr><u>&nbsp;".number_format(floatval(abs($balance)),2,","," ")."&nbsp;</u></nobr> грн. "
                    . "</p>";
        }
        echo '<br>';

        echo "<p style='text-indent:1cm; text-align:justify; font-size:12pt;'>"
                . "Дата: <u>&nbsp;".ukr_in_date("d M Y р.", $today)."&nbsp;</u>"
                . "</p>";
        echo ""
        . "<table width=100% align=center border=1 cellpadding=7 cellspacing=0>"
        . "<tr>"
                . "<td width=50% align=left valign=top>"
                . "Представник <u>&nbsp;".($agents[0]['name_short'] ?? '')."&nbsp;</u>"
                . "<br><br><br><br>__________________ &nbsp;&nbsp;/ ".(isset($_GET['shtamp'])?"<u>&nbsp;".($agents[0]['manager_name_short'] ?? '')."&nbsp;</u>":"___________")." /"
                . "<font size=-2><br>мп".str_repeat("&nbsp;", 20)."підпис".str_repeat("&nbsp;", 45)."ФІО</font>"
                . "</td>"
                . "<td width=50% align=left valign=top>"
                . "Представник <u>&nbsp;".($contragents[0]['name_short'] ?? '')."&nbsp;</u>"
                . "<br><br><br><br>__________________ &nbsp;&nbsp;/ ___________ /"
                . "<font size=-2><br>мп".str_repeat("&nbsp;", 20)."підпис".str_repeat("&nbsp;", 45)."ФІО</font>"
                . "</td>"
        . "</tr>"
        . "</table>"
        . "</div>";
        if (isset($_GET['shtamp'])) {
            echo "
                <div style='position:relative; left:10px; top:-150px; width:44mm; height:44.185mm; z-index:10; overflow:visible;'>
                    <img src='/img/ar_shtamp_podpis.jpg' width='100%' height='100%' /></div>
            ";
        }
        echo "</div>";


?>


