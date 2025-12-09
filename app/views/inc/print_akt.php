<?php
/**
 *  Project : my.ri.net.ua
 *  File    : print_akt.php
 *  Path    : app/views/inc/print_akt.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 09 Dec 2025 17:19:38
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of print_akt.php
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
 */

use config\Icons;
use billing\core\base\Lang;
use config\tables\Firm;
use config\tables\Invoice;

Lang::load_inc(__FILE__);

global $month_ua_r;

$D=(validate_date_str($invoice[Invoice::F_AKT_DATE_STR]) 

        ?   "<u>&nbsp;&laquo;&nbsp;"
            .(mb_substr($invoice[Invoice::F_AKT_DATE_STR], 0, 2, "UTF-8") ?? "__")
            ."&nbsp;&raquo;&nbsp;"
            .@$month_ua_r[intval(mb_substr($invoice[Invoice::F_AKT_DATE_STR], 3, 2, "UTF-8"))-1]
            ."&nbsp; "
            .mb_substr($invoice[Invoice::F_AKT_DATE_STR], 6, 4, "UTF-8")
            ." р.&nbsp;</u>"

        :   $invoice[Invoice::F_AKT_DATE_STR]) ;

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
        margin-top: 1.0cm;
    }
</style>
<?php if ($show_sht): ?>
    <div style="position: absolute; left: 45mm; top: 0mm; width:44mm; height: 44.185mm; z-index: 1; overflow: visible;"><img src="<?= Icons::SRC_FAXIMILE ?>" width=100% height=100% /></div>
    <div style="position: absolute; left: 40mm; top: 120mm; width:44mm; height: 44mm; z-index: 1; overflow: visible;"><img src="<?= Icons::SRC_FAXIMILE ?>" width=100% height=100% /></div>
<?php endif; ?>
<div style="position: relative; z-index:10;">
    <table width=100% border=0 cellpadding=2 cellspacing=0 style="width:170mm;">
        <!--
        <tr>
            <td style="width:3mm;">&nbsp;</td>
            <td style="width:77mm;">&nbsp;</td>
            <td style="width:10mm;">&nbsp;</td>
            <td style="width:77mm;">&nbsp;</td>
            <td style="width:3mm;">&nbsp;</td>
        </tr>
        -->
        <tr>
            <td style="width:3mm;">&nbsp;</td>
            <td style="width:77mm;" valign =top>
                ЗАТВЕРДЖУЮ<br />
                <?= $agent[Firm::F_MANAGER_JOB_TITLE] ?> <?= $agent[Firm::F_MANAGER_NAME_SHORT] ?>
            </td>
            <td style="width:10mm;">&nbsp;</td>
            <td style="width:77mm;" valign =top>
                ЗАТВЕРДЖУЮ<br />
                <?= $contragent[Firm::F_MANAGER_JOB_TITLE] ?> <?= $contragent[Firm::F_MANAGER_NAME_SHORT] ?>
            </td>
            <td style="width:3mm;">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><br /><div align=right><font size=-2>мп&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></div><hr noshade /></td>
            <td>&nbsp;</td>
            <td><br /><div align=right><font size=-2>мп&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></div><hr noshade /></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><?= $agent[Firm::F_MANAGER_NAME_SHORT];?></td>
            <td>&nbsp;</td>
            <td><?= $contragent[Firm::F_MANAGER_NAME_SHORT];?></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan=3>
                <h3 align=center>АКТ надання послуг<br />
                    №<u>&nbsp;<?= $invoice[Invoice::F_INV_NO];?>&nbsp;</u> від <?= $D; ?></h3>
                <hr />
                <p align=justify>
                    Ми, що нижче підписалися, представник Замовника
                    <u>&nbsp;<?= $contragent[Firm::F_NAME_LONG];?>&nbsp;</u>,
                    <u>&nbsp;<?= $contragent[Firm::F_MANAGER_JOB_TITLE];?> <?= $contragent[Firm::F_MANAGER_NAME_SHORT];?>&nbsp;</u>, з одного боку, і представник Виконавця
                    <u>&nbsp;_<?= $agent[Firm::F_MANAGER_JOB_TITLE];?> <?= $agent[Firm::F_MANAGER_NAME_SHORT];?>_&nbsp;</u>, з іншого боку,
                    склали цей акт про те, що на підставі наведених документів:<br />
                    Рахунок <nobr>№<u>&nbsp;<?= $invoice[Invoice::F_INV_NO];?>&nbsp;</u></nobr> <nobr>від <u><?= $invoice[Invoice::F_INV_DATE_STR];?>р.</u></nobr>
                    <br />
                    Виконавцем були виконані наступні роботи (надані такі послуги):
                </p>
                <div style="position: relative; z-index:10;">
                <table border=1 cellpadding=5 cellspacing=0 width=100%>
                    <tr align=left bgcolor=silver>
                        <th>№</th>
                        <th>Найменування робіт, послуг</th>
                        <th>Кіл-сть</th>
                        <th>Од.</th>
                        <th>Ціна за одиницю, грн</th>
                        <th>Сума до сплати, грн</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td><?= str_replace("&lt;/nobr&gt;", "</nobr>",
                                        str_replace("&lt;nobr&gt;", "<nobr>",
                                        $invoice[Invoice::F_TEXT]
                                        ));?>&nbsp;</td>
                        <td align=center><?= $invoice[Invoice::F_COUNT];?></td>
                        <td align=center>посл</td>
                        <td align=center><?= $invoice[Invoice::F_COST_1];?></td>
                        <td align=center><?= $invoice[Invoice::F_COST_ALL];?></td>
                    </tr>
                    <tr>
                        <td colspan=5 align=right>Всього:</td>
                        <td align=center><?= $invoice[Invoice::F_COST_ALL];?></td>
                    </tr>
                    <tr>
                        <td colspan=5 align=right>У тому числі ПДВ:</td>
                        <td align=center nowrap>без ПДВ</td>
                    </tr>
                </table>
                </div>
                <p align=justify>
                    Загальна вартість робіт (послуг) склала без ПДВ <nobr><u>&nbsp;<?= round($invoice[Invoice::F_COST_ALL]);?>&nbsp;</u> гривень</nobr> <nobr><u>&nbsp;<?= substr((round($invoice[Invoice::F_COST_ALL]*100)), strlen(round($invoice[Invoice::F_COST_ALL]*100))-2, 2);?>&nbsp;</u> копійок</nobr>, <u><b><?= num2str($invoice[Invoice::F_COST_ALL]); ?></b></u>.
                    Виконавець не є платником ПДВ.
                    <!--
                    <br />
                    ПДВ 0 гривень 0 копійок,<br />
                    загальна вартість робіт (послуг) із ПДВ <u>&nbsp;&nbsp;</u> гривень <u>&nbsp;&nbsp;</u> копійок.<br />
                    -->
                    Замовник претензій по об'єму, якості та строкам виконання робіт (надання послуг) не має.
                    <!--<br />Місце складання: м. Київ-->
                </p>
                <hr />
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td valign =top>
                Від Виконавця<font size=-1><sup>*</sup></font>
                <br /><br /><div align=right><font size=-2>мп&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></div><hr noshade />
                <?= $agent[Firm::F_MANAGER_JOB_TITLE];?><br />
                <?= $agent[Firm::F_MANAGER_NAME_SHORT];?>
            </td>
            <td></td>
            <td valign =top>
                Від Замовника<font size=-1><sup>*</sup></font>
                <br /><br /><div align=right><font size=-2>мп&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font></div><hr noshade />
                <?= $contragent[Firm::F_MANAGER_JOB_TITLE];?><br />
                <?= $contragent[Firm::F_MANAGER_NAME_SHORT];?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <font size=-2><sup>*</sup>
                    Відповідальний за здійснення господарської операції і правильність її оформлення
                    <!-- від Виконавця: СПД-ФО Малий Аркадій Володимирович, ЄДРПОУ 2655616894-->
                </font>
            </td>
            <td>&nbsp;</td>
            <td>
                <font size=-2><sup>*</sup>
                    Відповідальний за здійснення господарської операції і правильність її оформлення
                    <!-- від Замовика: Товариство з обмеженою відповідальністю «Сезам Фуд», ЄДРПОУ&nbsp;42014602-->
                </font>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <?= $D; ?>
            </td>
            <td>&nbsp;</td>
            <td>
                <?= $D; ?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td valign=top>
                <font size=-1><br /><hr /><br />
                <?= $agent[Firm::F_NAME_LONG];?><br />
                ЄДРПОУ <?= $agent[Firm::F_COD_EDRPOU];?><br />
                ІПН <?= $agent[Firm::F_COD_IPN];?>, <br />
                Адреса реєстрації:<br />
                <?= str_replace("&lt;/nobr&gt;", "</nobr>",
                                        str_replace("&lt;nobr&gt;", "<nobr>",
                                        $agent[Firm::F_ADDRESS_REGISTRATION]
                                        ));?><br />
                IBAN <?= $agent[Firm::F_BANK_IBAN];?><br />
                <?= $agent[Firm::F_BANK_NAME];?>
                </font>
            </td>
            <td>&nbsp;</td>
            <td valign=top>
                <font size=-1><br /><hr /><br />
                <?= $contragent[Firm::F_NAME_SHORT];?>, <br />
                ЄДРПОУ <?= $contragent[Firm::F_COD_EDRPOU] ?? '';?><br />
                ІПН <?= $contragent[Firm::F_COD_IPN] ?? '';?>, <br />
                Адреса реєстрації:<br />
                <?= str_replace("&lt;/nobr&gt;", "</nobr>",
                                        str_replace("&lt;nobr&gt;", "<nobr>", 
                                        $contragent[Firm::F_ADDRESS_REGISTRATION] ?? ''
                                        ));?><br />
                IBAN <?= $contragent[Firm::F_BANK_IBAN] ?? '';?><br />
                <?= $contragent[Firm::F_BANK_NAME] ?? '';?>
                </font>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</div>