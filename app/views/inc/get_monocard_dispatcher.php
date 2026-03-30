<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_monocard_dispatcher.php
 *  Path    : app/views/inc/get_monocard_dispatcher.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Mar 2026 22:16:16
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



/**
 * Вид отображения данных и транзакций из MonoCard
 * 
 * app/controllers/BankController.php
 *          public function getAction()
 *                  app/views/Bank/getView.php
 *                          app/views/inc/get_monocard_dispatcher.php
 *                                  app/views/inc/get_monocard_accounts.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_monocard_statement.php
 *                                  app/views/inc/get_pay_rec_form.php
 * 
 *                          app/views/inc/get_p24acc_dispatcher.php
 *                                  app/views/inc/get_p24acc_account.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_p24acc_transaction_card.php
 *                                  app/views/inc/get_pay_rec_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Данные приходящие от контроллера
 * 
 * @var array $accounts     [], Банковские карты или рассчётные счета
 * @var array $data         [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
 * @var int   $date1_ts     int, timestamp, начало периода выборки
 * @var int   $date2_ts     int, timestamp, конец периода выборки
 * @var array $ppp          [], ППП
 * 
 */



use billing\core\App;
use config\Bank;
use config\MonoCard;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\User;



?>
<!-- 
*
*   Список банковских карт 
*
-->
<?php include DIR_INC . '/get_monocard_accounts.php'; ?>



<!-- 
*
*   Навигация по датам
*
-->
<?php include DIR_INC . '/get_navigation.php'; ?>


<!-- =======================FORM======================== -->

<form method="post" action="">

        <?php foreach ($data as $index => $rec) : ?>
            <?php 
                $statement = $rec[Bank::F_STATEMENT];
                $pay_rec = $rec[Bank::F_PAY_REC];
                $found_rec = $rec[Bank::F_FOUND_REC];
                if (sign(get_numeric_part($statement[MonoCard::F_AMOUNT])) === -1) { continue; } 
                if (!empty($statement[MonoCard::F_COMMENT])) {
                    $statement[MonoCard::F_DESCRIPTION] = $statement[MonoCard::F_DESCRIPTION]." | ".$statement[MonoCard::F_COMMENT];
                    unset($statement[MonoCard::F_COMMENT]);
                }

            ?>

            <div class="row">
                <div class="col-4">
                    <?php include DIR_INC . '/get_monocard_statement.php'; ?>
                </div>

                <div class="col-8">
                    <?php include DIR_INC . '/get_pay_rec_form.php'; ?>
                </div>

            </div>

        <?php endforeach; ?>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                Save Selected
            </button>
        </div>

</form>

<!-- 
*
*   Навигация по датам
*
-->
<?php include DIR_INC . '/get_navigation.php'; ?>



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
