<?php
/**
 *  Project : my.ri.net.ua
 *  File    : printView.php
 *  Path    : app/views/Invoice/printView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Dec 2025 20:11:34
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of printView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * Данные переданные из контроллера
 * @var string $title
 * @var array $invoice
 * @var int $show_sht       = 1|0
 * @var int $show_inv       = 1|0
 * @var int $show_act       = 1|0
 * @var array $abon
 * @var array $user
 * @var array $agent
 * @var array $contragent
 * 
 */

use config\Icons;

?>
<style type="text/css">
    body {
        width: 17cm;
        /* height:26cm; */
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        /* line-height: 14px; */
        left: 1cm;
        /* <body leftmargin="число"> */
        margin-left: 1.5cm; /* 28.346px; 1 см */
        margin-top: 0cm;
    }


    h1 {
        text-align:center;
        font-size:larger;
    }


    table.think {
        border: 1px solid;
        border-width:1px;
        border-color:#000000;
        border-collapse:collapse;
    }


    tr.fon {
        background:#CCCCCC;
    }
    td.b {
        border: 1px solid #000000;
        border-top-width:0px;
        border-left-width:0px;
        border-right-width:0px;
    }

    p {
        font-size:small;
        font-weight:normal;
        padding:0;
    }

    p.w {
        font-size:small;
        color:#FFC;
    }



    p.s8 {
        font-size:8pt;
    }

    table.in {
        /* border="0" cellpadding="1" cellspacing="0" */
        border:0;
        padding:1;
        border-spacing:0;
        line-height:normal;
    }
</style>
<!-- СЧЁТ -->
<h1 align="center"><font size="3">РАХУНОК-ФАКТУРА № <?= $invoice['sf_no']; ?><br />від <?= $invoice['sf_date']; ?>&nbsp;р.</font></h1>
<?php if ($show_sht): ?>
    <?php if ($show_inv): ?>
        <div style='position: absolute; left: 106px; top: 350px; width:44mm; height: 44.185mm; z-index: 3; overflow: visible;'>
            <img src='<?= Icons::SRC_FAXIMILE ?>' width='100%' height='100%' /></div>
    <?php endif; ?>
    <?php if ($show_act): ?>
        <div style='position: absolute; left: 41px; top: 807px; width:44mm; height: 44mm; z-index: 3; overflow: visible;'>
            <img src='<?= Icons::SRC_FAXIMILE ?>' width='100%' height='100%' /></div>
    <?php endif; ?>
<?php endif; ?>
<div style="position: relative; z-index:10;">
<table width="100%">
  <tr>
    <td align="right" valign="top">Постачальник:&nbsp;</td>
    <td align="left" valign="top" class="b">&nbsp;<?= $agent['name_long'] ?></td>
  </tr>
  <tr>
      <td align="right" valign="top">Р/рахунок&nbsp;<b>IBAN:</b>&nbsp;</td>
      <td align="left" valign="top" class="b">&nbsp;<b><?= str_replace(" ", "&nbsp;", $agent['bank_IBAN']) ?></b></td>
  </tr>
  <tr>
    <td align="right" valign="top">код ЄДРПОУ:&nbsp;</td>
    <td align="left" valign="top" class="b">&nbsp;<?= $agent['cod_EDRPOU'] ?></td>
  </tr>
  <tr>
    <td align="right" valign="top">Банк:&nbsp;</td>
    <td align="left" valign="top" class="b">&nbsp;МФО:&nbsp;<?= get_mfo_from_iban($agent['bank_IBAN']) ?>. <?PHP echo $agent['bank_name'] ?></td>
  </tr>
  <tr>
    <td align="right" valign="top">№ свідоцтва:&nbsp;</td>
    <td align="left" valign="top" class="b">&nbsp;<?= str_replace("\n", "<br />&nbsp;", $agent['registration']) ?></td>
  </tr>
  <tr>
    <td align="right" valign="top">телефон:&nbsp;</td>
    <td align="left" valign="top" class="b">&nbsp;<?= str_replace("\n", "<br />&nbsp;", $agent['office_phones']) ?></td>
  </tr>
  <tr>
    <td align="right" valign="top">&nbsp;</td>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td align="right" valign="top">Платник:&nbsp;</td>
    <td align="left" valign="top" class="b"><b>&nbsp;<?= $invoice['sf_firm']; ?></b></td>
  </tr>
</table>
<br />
<table width="100%" border="1" cellpadding="3" class="think">
  <tr>
    <td align="center" valign="middle">&nbsp;№&nbsp; </td>
    <td align="center" valign="middle">Найменування</td>
    <td align="center" valign="middle">Одиниця виміру</td>
    <td align="center" valign="middle">Кількість</td>
    <td align="center" valign="middle">Ціна без ПДВ</td>
    <td align="center" valign="middle">Сумма без ПДВ</td>
  </tr>
  <tr class="fon">
    <td align="center" valign="middle"><font size="1">1</font></td>
    <td align="center" valign="middle"><font size="1">2</font></td>
    <td align="center" valign="middle"><font size="1">3</font></td>
    <td align="center" valign="middle"><font size="1">4</font></td>
    <td align="center" valign="middle"><font size="1">5</font></td>
    <td align="center" valign="middle"><font size="1">6</font></td>
  </tr>
  <tr>
    <td align="center">1</td>
    <td><?= html_entity_decode($invoice['sf_text']); ?></td>
    <td align="center">послуга</td>
    <td align="center">&nbsp;<?= $invoice['sf_count']; ?>&nbsp;</td>
    <td align="center">&nbsp;<?= $invoice['sf_cost_1']; ?>&nbsp;</td>
    <td align="center">&nbsp;<?= $invoice['sf_cost_all']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5" align="right">Всього без ПДВ: </td>
    <td align="center"><b>&nbsp;<?= $invoice['sf_cost_all']; ?>&nbsp;</b></td>
  </tr>
</table>
<br>
<table width="100%">
  <tr>
    <td width="25%">Загальна сумма до сплати:</td>
    <td width="75%" align="center" class="b"><font size="3"><b><?= num2str($invoice['sf_cost_all']); ?></b></font></td>
  </tr>
</table>
<br />
<br />
<br />
<table width="60%">
  <tr>
    <td align="left">Керівник</td>
    <td width="50%" class="b">&nbsp;</td>
    <td align="right"><?= $agent['name_short']; ?></td>
  </tr>
</table>
<p>&nbsp;</p>
<!-- АКТ -->
<?php if($show_act == 1): ?>
<hr noshade="noshade" size="1px">
<p>&nbsp;</p>
  <h1 align="center"><font size="3">АКТ</font><font size="3"> № <?= $invoice['sf_no']; ?><br />
від <?= $invoice['akt_date']; ?>&nbsp;р.</font></h1>
  <div style="position: relative; z-index:10;">
      <p align="left"><span class="b"><b><?= $invoice['sf_firm']; ?></b></span> та <?= str_replace(" ", "&nbsp;", $agent['name_short']); ?> склали цей Акт про те, що за рахунком-фактурою № <?= $invoice['sf_no']; ?> виконані наступні послуги/роботи:</p>
<table width="100%" border="1" cellpadding="3" class="think">
  <tr>
    <td align="center" valign="middle">&nbsp;№&nbsp; </td>
    <td align="center" valign="middle">Найменування</td>
    <td align="center" valign="middle">Одиниця виміру</td>
    <td align="center" valign="middle">Кількість</td>
    <td align="center" valign="middle">Ціна без ПДВ</td>
    <td align="center" valign="middle">Сумма без ПДВ</td>
  </tr>
  <tr class="fon">
    <td align="center" valign="middle"><font size="1">1</font></td>
    <td align="center" valign="middle"><font size="1">2</font></td>
    <td align="center" valign="middle"><font size="1">3</font></td>
    <td align="center" valign="middle"><font size="1">4</font></td>
    <td align="center" valign="middle"><font size="1">5</font></td>
    <td align="center" valign="middle"><font size="1">6</font></td>
  </tr>
  <tr>
    <td align="center">1</td>
    <td><?= html_entity_decode($invoice['sf_text']); ?></td>
    <td align="center">послуга</td>
    <td align="center">&nbsp;<?= $invoice['sf_count']; ?>&nbsp;</td>
    <td align="center">&nbsp;<?= $invoice['sf_cost_1']; ?>&nbsp;</td>
    <td align="center">&nbsp;<?= $invoice['sf_cost_all']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5" align="right">Всього без ПДВ: </td>
    <td align="center"><b>&nbsp;<?= $invoice['sf_cost_all']; ?>&nbsp;</b></td>
  </tr>
</table>
<br>
<table width="100%">
  <tr>
    <td colspan="2">Вказані послуги надані належним чином, в зазначений строк та у відповідності до договору.</td>
  </tr>
  <tr>
    <td align="left">Загальна вартість<br>наданих послуг складає:</td>
    <td align="center" class="b"><font size="3"><nobr><b><?= num2str($invoice['sf_cost_all']); ?></b></nobr></font></td>
  </tr>
</table>
<p>&nbsp;</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="55%" align="left" valign="top"><?= str_replace(" ", "&nbsp;", $agent['name_short']); ?><br />
    <br />
    <br />
    ______________________________</td>
    <td width="45%" align="left" valign="top"><b><?= $invoice['sf_firm']; ?></b><br />
    <br />
    <br />
    _________________ / _________________ / </td>
  </tr>
</table>
<?php endif; ?>
