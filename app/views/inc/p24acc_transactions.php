<?php
/**
 *  Project : my.ri.net.ua
 *  File    : p24acc_transactions.php
 *  Path    : app/views/inc/p24acc_transactions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:12:37
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of p24acc_transactions.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use app\models\AbonModel;
use billing\core\App;
use config\Bank;
use config\P24acc;
use config\tables\Abon;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\PppType;

require DIR_LIBS . '/inc_functions.php';

/**
 * Данные из контроллера
 * @var string $date1
 * @var string $date2
 * @var array $ppp
 * @var array $transactions -- Список банковских транзакций
 *          ['transaction'] -- Данные транзакции
 *          ['found_pays']  -- Найденные платежи в биллинге по данной транзакции
 *          ['save_pay']    -- Данные для сохранения платежа в биллинге по данной транзакции
 * @var array $unknowns     -- Нераспределённые платежи, внесённые в базу.
 */

$model = new AbonModel();

$d_start_parse = date_parse_from_format('d-m-Y', $date1);
$d_start = mktime(0, 0, 0, $d_start_parse['month'], $d_start_parse['day'], $d_start_parse['year']);
$d_end_parse = date_parse_from_format('d-m-Y', $date2);
$d_end = mktime(0, 0, 0, $d_end_parse['month'], $d_end_parse['day'], $d_end_parse['year']);
$d11 = date('d-m-Y', $d_start - App::get_config('bank_date_interval'));
$d12 = date('d-m-Y', $d_start);
$d21 = date('d-m-Y', $d_start);
$d22 = date('d-m-Y', $d_end);
$d31 = date('d-m-Y', $d_end);
$d32 = date('d-m-Y', $d_end + App::get_config('bank_date_interval'));

$index_unknown = count($transactions) + 1;

?>

<!-- Строка навигации -->

<hr>
    <div align=center>
     | 
    <a href=?<?= Bank::F_GET_PPP_ID ?>=<?= $ppp[Ppp::F_ID] ?>&<?= Bank::F_GET_DATE_START ?>=<?= $d11 ?>&<?= Bank::F_GET_DATE_END ?>=<?= $d12 ?>><?= $d11 ?>-<?= $d12 ?></a>
    &nbsp;&nbsp;|&nbsp;<<<===&nbsp;|&nbsp;&nbsp;<font size=+2 color=green><?= $d21 ?>-<?= $d22 ?></font>&nbsp;&nbsp;|&nbsp;===>>>&nbsp;|&nbsp;&nbsp;
    <a href=?<?= Bank::F_GET_PPP_ID ?>=<?= $ppp[Ppp::F_ID] ?>&<?= Bank::F_GET_DATE_START ?>=<?= $d31 ?>&<?= Bank::F_GET_DATE_END ?>=<?= $d32 ?>><?= $d31 ?>-<?= $d32 ?></a>
     | 
    </div>
<hr>

<!-- Транзакции -->

<form method=post action=''>
<!-- <input name=<?= Bank::POST_REC ?>[<?= Bank::F_GET_PPP_ID ?>] type='hidden' value='<?= $ppp[Ppp::F_ID] ?>' /> -->

<table class='table table-striped table-hover table-bordered' >
    <tr>
        <th>Контрагент</th>
        <th>Платёж</th>
        <th>Документ</th>
        <th>Описание платежа</th>
        <th>Терминал</th>
    </tr>    
    <?php foreach ($transactions as $index => $t_row): ?>
        <?php $transaction = $t_row['transaction']; ?>
        <?php $found_pays  = $t_row['found_pays']; ?>
        <?php $save_pay    = $t_row['save_pay']; ?>
        <tr>
            <td align=left valign=top>

                <!-- Банковские поля -->
                 
                <!-- 'AUT_CNTR'  =>   -->
                <b><?= $transaction[P24acc::F_AUT_CNTR_NAM] ?></b><br>
                ЄДРПОУ: <?= $transaction[P24acc::F_AUT_CNTR_CRF] ?><br>
                <nobr>Р/Р: <?= $transaction[P24acc::F_AUT_CNTR_ACC] ?></nobr><br>
                МФО: <?= $transaction[P24acc::F_AUT_CNTR_MFO] ?><br>
                Банк: <?= $transaction[P24acc::F_AUT_CNTR_MFO_NAME] ?>, <?= $transaction[P24acc::F_AUT_CNTR_MFO_CITY] ?><br>
                <!-- //'DOC'       => -->
                Тип пл. документа: <?= $transaction[P24acc::F_DOC_TYP] ?><br>
                Клієнтська: <?= $transaction[P24acc::F_DAT_KL] ?><br>
                Валютування: <?= $transaction[P24acc::F_DAT_OD] ?> <?= $transaction[P24acc::F_TIM_P] ?>
            </td>
            <td align=left valign=top>
                <!-- 'TTI'       =>   -->
                № док. агента: <?= $transaction[P24acc::F_NUM_DOC] ?><br>
                Референс проводки: <?= $transaction[P24acc::F_REF] ?>&nbsp;<font color=gray title='№ п/п внутри проводки'>(<?= $transaction[P24acc::F_REFN] ?>)</font><br>
                <font title='Референс платежу сервісу через який створювали платіж \n(payment_pack_ref - при створенні платежу через АПИ Автоклієнт)'>
                    Референс сервісу: <?= $transaction[P24acc::F_DLR] ?></font><br>
                <font title='TECHNICAL_TRANSACTION_ID'>
                    TTID: <?= $transaction[P24acc::F_TECHNICAL_TRANSACTION_ID] ?></font><br>
            </td>
            <td align=left valign=top width=30%>
                <!-- 'BANK_REC'  => -->
                ID транзакції: <font color=blue title='Номер документа, сверяемый с биллингом'><?= $transaction[P24acc::F_ID] ?></font><br>
                DATE: <font color=blue title='Дата, сверяемая с биллингом'><?= $transaction[P24acc::F_DATE_TIME_DAT_OD_TIM_P] ?></font><br>
                Сума: <font color=blue title='Сумма, сверяемая с биллингом'><?= $transaction[P24acc::F_SUM] ?></font> | <?= $transaction[P24acc::F_SUM_E] ?><br>
                <!-- //"Стан: " -->
                <a title='Валюта'><?= $transaction[P24acc::F_CCY] ?></a>&nbsp;| 
                <?= Bank::get_html_transaction_real($transaction[P24acc::F_FL_REAL]) ?>&nbsp;| 
                <?= Bank::get_html_transaction_status($transaction[P24acc::F_PR_PR]) ?> | 
                <font title='Тип транзакції дебет/кредит (D, C)'><?= ($transaction[P24acc::F_TRANTYPE]=='C' ? paint("[+]", "green") : paint("[-]", "red")) ?></font>
                <hr>
                <!-- //'OSND'      => -->
                <?= $transaction[P24acc::F_OSND] ?>
            </td>
            <td align=left valign=top width=30%>

                <!-- Биллинговые поля -->

                <!-- 'BILLING_REC'   =>   -->
                <nobr>
                <?php if ($found_pays['on_billing']): ?>
                    <?php $found_pay = $found_pays['payments'][0]; ?>
                    <!-- // 'pay_bank_no'   => -->
                    Bank No: <font color="<?= ($found_pay[Pay::F_BANK_NO]==$transaction[P24acc::F_ID] ? "teal" : "red") ?>"><?= $found_pay[Pay::F_BANK_NO] ?></font><br>
                    <!-- // 'pay_date'      => -->
                    DATE:    <font color="<?= ($found_pay[Pay::F_DATE]==strtotime($transaction[P24acc::F_DATE_TIME_DAT_OD_TIM_P]) ? "teal" : "red") ?>" title='Дата платежа'><?= date('Y-m-d H:i:s', $found_pay[Pay::F_DATE]) ?></font><br>
                    <!-- // 'pay_fakt'      => -->
                    Сума:    <font color="<?= (cmp_float(floatval($found_pay[Pay::F_PAY_FAKT]), floatval($transaction[P24acc::F_SUM])) == 0 ? "teal" : "red") ?>" title='pay_fakt'><?= sprintf("%.2f", floatval($found_pay[Pay::F_PAY_FAKT])) ?></font> | 
                    <!-- // 'pay'      => -->
                             <font color="<?= (cmp_float(floatval($found_pay[Pay::F_PAY_ACNT]), floatval($save_pay[Pay::F_PAY_ACNT])) == 0 ? "teal" : "red") ?>" title='pay'><?= sprintf("%.2f", floatval($found_pay[Pay::F_PAY_ACNT])) ?></font><br>

                    ABON:    <?= url_abon_form($found_pays['abon'][Abon::F_ID]) ?> | 
                    <font color=teal title='PAY: <?= print_r($found_pay, true) ?>'>(<?= $found_pays['searched_on'] ?>)</font> <?= url_pay_form($found_pay[Pay::F_ID]) ?> | 
                    <!-- // 'pay_type'      => -->
                    <font title='pay_type: <?= CR . Pay::title($found_pay[Pay::F_TYPE_ID]) . CR . Pay::description($found_pay[Pay::F_TYPE_ID]) ?>'><?= $found_pay[Pay::F_TYPE_ID] ?></font> | 
                    <?= $model->url_ppp_form_22($found_pay[Pay::F_PPP_ID]) ?>
                    <hr>
                    <?= $found_pay[Pay::F_DESCRIPTION] ?>

                <?php else: ?>
                    <span class="text-danger">Платіж не знайдено в Біллінгу</span>
                    <!-- // 'pay_bank_no'   => -->
                    Bank No: <font color="blue" ?>"><?= $save_pay[Pay::F_BANK_NO] ?></font><br>
                    <!-- // 'pay_date'      => -->
                    DATE:    <font color="blue" title='Дата платежа'><?= date('Y-m-d H:i:s', $save_pay[Pay::F_DATE]) ?></font><br>
                    <!-- // 'pay_fakt'      => -->
                    Сума:    <font color="blue" title='pay_fakt'><?= sprintf("%.2f", floatval($save_pay[Pay::F_PAY_FAKT])) ?></font> | 
                    <!-- // 'pay'      => -->
                             <font color="blue" title='pay'><?= sprintf("%.2f", floatval($save_pay[Pay::F_PAY_ACNT])) ?></font>
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_PAY_FAKT ?>]  type='hidden' value='<?= $save_pay[Pay::F_PAY_FAKT ] ?>' />
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_PAY_ACNT ?>]  type='hidden' value='<?= $save_pay[Pay::F_PAY_ACNT ] ?>' />
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_DATE     ?>]  type='hidden' value='<?= $save_pay[Pay::F_DATE     ] ?>' />
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_BANK_NO  ?>]  type='hidden' value='<?= $save_pay[Pay::F_BANK_NO  ] ?>' />
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_TYPE_ID  ?>]  type='hidden' value='<?= $save_pay[Pay::F_TYPE_ID  ] ?>' />
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_PPP_ID   ?>]  type='hidden' value='<?= $save_pay[Pay::F_PPP_ID   ] ?>' />
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_ABON_ID  ?>]  type='text'   value='<?= $save_pay[Pay::F_ABON_ID  ] ?>' size=10 style='text-align:center;'/>
                    <br>
                    <font color=gray>(<?= $save_pay['searshed_on'] ?>)</font>
                    <hr>
                    <textarea name=<?= Bank::POST_REC ?>[<?= $index ?>][<?= Pay::F_DESCRIPTION ?>] cols=40 rows=3 style='width:100%;'><?= $save_pay[Pay::F_DESCRIPTION] ?></textarea>

                <?php endif; ?>
                </nobr><br>

                <?php if (isset($found_pays['template']) or (!$found_pays['on_billing'])): ?>
                    <hr><nobr>Доб. шаблона: 
                        <input name=<?= Bank::POST_REC ?>[<?= $index ?>][template_aid]  type=text value='<?= $found_pays['abon'][Abon::F_ID] ?? 0 ?>' size=7 style='text-align:center;'/>&nbsp;
                        <input name=<?= Bank::POST_REC ?>[<?= $index ?>][template_text] type=text value='<?= $found_pays['template'] ?? '' ?>' size=40 style='text-align:left; width:50%;'/>
                        <input name=<?= Bank::POST_REC ?>[<?= $index ?>][template_add]  type='checkbox' value="1" /></nobr>
                <?php endif; ?>

            </td>
            <td align=left valign=top>
                <!-- 'act'           =>   -->
                <?php if ($found_pays['on_billing']): ?>
                    <pre>&nbsp;[&nbsp;]&nbsp;</pre>
                <?php else: ?>
                    <input name=<?= Bank::POST_REC ?>[<?= $index ?>][to_billing] type='checkbox' value="1" />
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach ?>
</table>
<hr>

<!-- * НЕ РАСПРЕДЕЛЁННЫЕ ПЛАТЕЖИ -->

<h2 align=center>НЕ РАСПРЕДЕЛЁННЫЕ ПЛАТЕЖИ</h2>
<table class='table table-striped table-hover table-bordered'> <!-- border=0 align=center cellpadding=3 cellspacing=2 -->
    <tr>
        <td align=center>No</td>
        <td align=center>pay_id</td>
        <td align=center>abon_id</td>
        <td align=center>pay_fakt<br>pay</td>
        <td align=center>pay_date</td>
        <td align=center>pay_bank_no</td>
        <td align=center>pay_type_id<br>pay_ppp_id</td>
        <td align=center>description</td>
        <td align=center></td>
    </tr>
<?php foreach ($unknowns as $upay): ?>
    <?php $index_unknown++; ?>
    <tr>
        <td valign=top align=right><font color=gray><?= $index_unknown ?>.&nbsp;</font></td>
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_ID ?>]        type='hidden' value='<?= $upay[Pay::F_ID] ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_AGENT_ID ?>]  type='hidden' value='<?= $upay[Pay::F_AGENT_ID] ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_PAY_FAKT ?>]  type='hidden' value='<?= floatval($upay[Pay::F_PAY_FAKT]) ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_PAY_ACNT ?>]  type='hidden' value='<?= floatval($upay[Pay::F_PAY_ACNT]) ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_DATE ?>]      type='hidden' value='<?= $upay[Pay::F_DATE] ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_BANK_NO ?>]   type='hidden' value='<?= $upay[Pay::F_BANK_NO] ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_TYPE_ID ?>]   type='hidden' value='<?= $upay[Pay::F_TYPE_ID] ?>' />
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_PPP_ID ?>]    type='hidden' value='<?= $upay[Pay::F_PPP_ID] ?>' />
        <td align=center>
            <?= url_pay_form($upay[Pay::F_ID]) ?>
        </td>
        <td align=center>
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_ABON_ID ?>] type=text value='<?= $upay[Pay::F_ABON_ID] ?>' size=10 style='text-align:center;'/>
        </td>
        <td align=right>
            <?= sprintf("%.2f", floatval($upay[Pay::F_PAY_FAKT])) ?><br>
            <?= sprintf("%.2f", floatval($upay[Pay::F_PAY_ACNT])) ?></td>
        <td align=center><?= date('Y-m-d H:i:s', $upay[Pay::F_DATE]) ?></td>
        <td align=center><?= $upay[Pay::F_BANK_NO] ?></td>
        <td align=left>
            <?= $upay[Pay::F_TYPE_ID] ?> <font color=gray size=-1>(<?= Pay::title($upay[Pay::F_TYPE_ID]) ?>)</font><br>
            <?= $upay[Pay::F_PPP_ID] ?> <font color=gray size=-1>(<?= $ppp[Ppp::F_TITLE] ?>)</font></td>
        <td align=center>
            <textarea name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][<?= Pay::F_DESCRIPTION ?>] cols=40 rows=3 style='width:100%;'><?= $upay[Pay::F_DESCRIPTION] ?></textarea>
        </td>
        <td align=center>
            <input name=<?= Bank::POST_REC ?>[<?= $index_unknown ?>][to_edit] value="1" type='checkbox' />
        </td>
    </tr>
<?php endforeach ?>
</table>
<div align=right><input type=submit value='отправить' /></div>
</form>