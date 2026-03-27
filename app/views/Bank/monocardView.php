<?php
/**
 *  Project : my.ri.net.ua
 *  File    : monocardView.php
 *  Path    : app/views/Bank/monocardView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Jan 2026 11:01:20
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вид отображения данных и транзакций из MonoCard
 * 
 * BankController.php 
 * BankController::monocardAction() -> 
 *      monocardView.php -> (этот)
 *              monocard_card_list.php
 *              monocard_statement.php
 *              monocard_pay_rec_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Данные переданные из контроллера
 * 
 * @var array{connect:array,client:array} $cards_info
 * @var array{connect:array,statements:array} $data
 * @var int $date1 -- int, timestamp, начало периода выборки
 * @var int $date2 -- int, timestamp, конец периода выборки
 * @var int $date_last_pay -- int, timestamp, Дата последнего зарегистрированного платежа на ППП
 * @var array $ppp
 */



/**
 * @var array{id:string,time:int,description:string,comment:string,mcc:int,originalMcc:int,amount:int,operationAmount:int,currencyCode:int,commissionRate:int,cashbackAmount:int,balance:int,hold:int} $statement
 */


use billing\core\App;
use config\Bank;
use config\MonoCard;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\User;

$statements = &$data[Bank::F_STATEMENTS];
$d_start = $date1; 
$d_end = $date2;

?>
<h2 class="fs-6">Информация по MonoCard</h2>
<h3 class="fs-6"><span class="text-primary" title="[client][name]"><?=$cards_info['client']['name']?></span><span class="text-secondary fs-8" title="[client][clientId]"> | <?=$cards_info['client']['clientId']?></span></h3>



<!-- 
*
*   Список банковских карт 
*
-->
<?php include DIR_INC . '/monocard_card_list.php'; ?>



<!-- 
*
*   Навигация по датам:
*
-->
<div align=center>
    <?php 
        $d1 = date('Y-m-d', $d_start-App::get_config('bank_date_interval')); 
        $d2 = date('Y-m-d', $d_start);
    ?>
    | <a href=?startDate=<?= $d1 ?>&endDate=<?= $d2 ?>><?= $d1 ?> -- <?= $d2 ?></a>
    <?php 
        $d1 = date('Y-m-d', timestamp: $d_start); 
        $d2 = date('Y-m-d', timestamp: $d_end);
    ?>
    &nbsp;&nbsp;|&nbsp;<<<===&nbsp;|&nbsp;&nbsp;<font size=+2 color=green><?= $d1 ?> -- <?= $d2 ?></font>&nbsp;&nbsp;|&nbsp;===>>>&nbsp;|&nbsp;&nbsp;
    <?php 
        $d1 = date('Y-m-d', $d_end); 
        $d2 = date('Y-m-d', $d_end+App::get_config('bank_date_interval'));
    ?>
    <a href=?startDate=<?= $d1 ?>&endDate=<?= $d2 ?>><?= $d1 ?> -- <?= $d2 ?></a> | 
    <hr>
    <a class="text text-end mb-2" href='https://prev.ri.net.ua/ab_templates.php?ppp_id=<?=$ppp[Pay::F_ID]?>' target=_blank>Редактирование абонентских шаблонов</a> | 
</div>


<!-- =======================FORM======================== -->

<form method="post" action="">

        <?php foreach ($statements as $index => &$statement) : ?>
            <?php 
                if (sign(get_numeric_part($statement[MonoCard::F_AMOUNT])) === -1) { continue; } 
                if (!empty($statement[MonoCard::F_COMMENT])) {
                    $statement[MonoCard::F_DESCRIPTION] = $statement[MonoCard::F_DESCRIPTION]." | ".$statement[MonoCard::F_COMMENT];
                    unset($statement[MonoCard::F_COMMENT]);
                }

            ?>

            <div class="row">
                <div class="col-4">
                    <?php include DIR_INC . '/monocard_statement.php'; ?>
                </div>

                <div class="col-8">
                    <?php include DIR_INC . '/monocard_pay_rec_form.php'; ?>
                </div>

            </div>

        <?php endforeach; ?>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                Save Selected
            </button>
        </div>

</form>





<?php return; ?>





<form id=form1 name=form1 method=post action=''>
<table width=98% border=0 align=center cellpadding=3 cellspacing=2>
    <tr>
        <td align=center>#</td>
        <td align=center>Банк</td>
        <td align=center>Биллинг</td>
    </tr>

        <?php 
        ?>


        <tr>
            <td valign=top align=right width=4%><font color=gray><?= ($index+1) ?>&nbsp;&nbsp;</font></td>
            <td valign=top align=left width=48%>
                <table width=100%>
                    <tr><td valign=top align=right width=5%>ID: </td><td valign=top align=left><?= $statement['id'] ?></td></tr>
                    <tr><td valign=top align=right>дата: </td><td valign=top align=left><?= date("Y-m-d H:i:s", $statement['time']) ?></td></tr>
                    <tr><td valign=top align=right>amount: </td><td valign=top align=left>
                        <font title='amount: Сумма транзакции'><?= get_numeric_part($statement['amount']) ?></font> :
                        <font title='cardamount: Сумма транзакции в валюте карты'><?= get_numeric_part($statement['operationAmount']) ?></font>
                        <font title='balance: Баланс рахунку' color=gray>[<?= get_numeric_part($statement['balance']) ?>]</font>
                        </td></tr>
                    <tr><td valign=top align=right>terminal: </td><td valign=top align=left>-<br>&nbsp;</td></tr>
                    <tr><td valign=top align=left colspan=2><hr>
                        <?= htmlentities($statement['description']) ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign=top align=left width=48%>"
                <table width=100%>
                    <tr><td valign=top align=right width=5%>ID: </td>
                        <td><font color=<?= ($found_rec['on_billing'] ? ($found_rec['pay']['pay_bank_no']==$pay_rec['pay_bank_no'] ? "teal" : "red") : "blue") ?>><?= $pay_rec['pay_bank_no'] ?></font></td>
                    </tr>
                    <tr><td valign=top align=right>дата: </td>
                        <td nowrap><font color=<?= ($found_rec['on_billing'] ? ($found_rec['pay']['pay_date']==$pay_rec['pay_date'] ? "teal" : "red") : "blue") ?>><?= date('Y-m-d H:i:s', $pay_rec['pay_date']) ?></font></td>
                    </tr>
                    <tr><td valign=top align=right>pay: </td>
                        <td nowrap>
                            <font color=<?= ($found_rec['on_billing'] ? (floatval($found_rec['pay']['pay_fakt'])==floatval($pay_rec['pay_fakt']) ? "teal" : "red") : "blue") ?> title='pay_fakt'><?= sprintf("%.2f", floatval($pay_rec['pay_fakt'])) ?></font> : 
                            <font color=<?= ($found_rec['on_billing'] ? (floatval($found_rec['pay']['pay'])     ==floatval($pay_rec['pay'])      ? "teal" : "red") : "blue") ?> title='pay to LK'><?= sprintf("%.2f", floatval($pay_rec['pay'])) ?></font>
                        </td>
                    </tr>
                    <tr><td valign=top align=right>SRC: </td>
                        <td nowrap>
                            <?= $pay_rec['pay_type_id'] ?><font color=gray size=-1>(<?= Pay::type_title($pay_rec['pay_type_id']) ?>)</font><br>
                            <?= $pay_rec['pay_ppp_id'] ?> <font color=gray size=-1>(<?= $ppp['title'] ?>)</font>
                        </td>
                    </tr>
                </table>
                <?php if ($found_rec['on_billing']) : ?>
                    <?php foreach ($pay_rec['payments'] as $statement) : ?>
                        <hr>
                        <?= (str_contains($statement['description'], $statement['description'])?"<font color=teal>".h($statement['description'])."</font>":h($statement['description']))?><br>
                        <nobr>
                        <font color=gray title='Место, где найден уже внесённый платёж.'>(<?= $pay_rec['searshed_on'] ?>)</font>: 
                        <font <?= ($statement['abon_id']==0?" color=red":"") ?>>AID: <?= url_abon_form($statement['abon_id']) ?></font> | "
                        PAY: <<?= url_pay_form($statement['id']) ?> | "
                        <font title='pay_fakt'><?= sprintf("%.2f", floatval($statement['pay_fakt'])) ?></font> : <font title='pay'><?= sprintf("%.2f", floatval($statement['pay'])) ?></font>"
                        </nobr><br>
                    <?php endforeach; ?>
                    <hr>
                <?php else : ?>
                    <?php 
                        $last_aid = NA;
                        if (count($pay_rec['aid_list']) == 0) {
                            $pay_rec['aid_list'][0]['aid'] = "";
                        }
                    ?>
                    <?php for ($pay_index = 0; $pay_index < count($pay_rec['aid_list']); $pay_index++): ?>
                        <?php if  (
                                !isset($pay_rec['aid_list'][$pay_index]['aid']) ||
                                (
                                    $last_aid != $pay_rec['aid_list'][$pay_index]['aid']
                                    //&& $pay['aid_list'][$pay_index]['aid'] != 0
                                )
                            ) : ?>
                            <?php $last_aid = $pay_rec['aid_list'][$pay_index]['aid']; ?>
                        
                            <fieldset style='width:90%; text-align:left;'><legend>Внесение:</legend>
                                <textarea name=pay[$index][to_billing][$pay_index][description] cols=40 rows=3 style='width:100%;'><?=$pay_rec['description']?></textarea>
                                <nobr>
                                <input name=pay[$index][to_billing][$pay_index][pay_date]    type='hidden' value='<?=$pay_rec['pay_date']?>' >
                                <input name=pay[$index][to_billing][$pay_index][pay_bank_no] type='hidden' value='<?=$pay_rec['pay_bank_no']?>' >
                                <input name=pay[$index][to_billing][$pay_index][pay_type_id] type='hidden' value='<?=$pay_rec['pay_type_id']?>' >
                                <input name=pay[$index][to_billing][$pay_index][pay_ppp_id]  type='hidden' value='<?=$pay_rec['pay_ppp_id']?>' >
                                <input name=pay[$index][to_billing][$pay_index][abon_id] type=text value='<?= (isset($pay_rec['aid_list'][$pay_index]['aid'])?$pay_rec['aid_list'][$pay_index]['aid']:"") ?>' size=5 style='text-align:center;' title='abon_id'> | 
                                <input name=pay[$index][to_billing][$pay_index][pay_fakt] type=text value='<?=$pay_rec['pay_fakt']?>' size=5 style='text-align:center;' title='pay_fakt'> | 
                                <input name=pay[$index][to_billing][$pay_index][pay] type=text value='<?=$pay_rec['pay']?>' size=5 style='text-align:center;' title='pay'>
                                <input name=pay[$index][to_billing][$pay_index][to_billing] type='checkbox' >
                                </nobr>
                            </fieldset>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <font color=gray  title='Место, где найден abon_id плательщика \n(возможно, только предположительно).'>(Найден: <?=$pay_rec['searshed_on']?>)</font>

                    <?php if (isset($pay_rec['template'])) : ?>
                        <fieldset style='width:90%; text-align:left;'><legend>Шаблон:</legend>
                            <nobr>
                            <input name=pay[$index][template_ppp] type='hidden' value='<?=$pay_rec['pay_ppp_id']?>' >
                            <input name=pay[$index][template_aid] type=text value='<?= (count($pay_rec['aid_list'])>0?$pay_rec['aid_list'][0]['aid']:"") ?>' size=5 style='text-align:center;' title='abon_id' >&nbsp;
                            <input name=pay[$index][template_text] type=text value='<?= (isset($pay_rec['template'])?$pay_rec['template']:"") ?>' size=40 style='text-align:left; width:55%;' title='Текстовый фрагмент, являющийся шаблоном' >
                            <input name=pay[$index][template_add] type='checkbox' >
                            </nobr>
                        </fieldset>
                    <?php endif; ?>
                <?php endif; ?>
                </td>
        </tr>
    <?php // endfor; ?>

    <tr><td colspan=9 align=right><input type=submit name=do value='отправить' ></td></tr>
    </table>
    </form>

