<?php
/*
 *  Project : my.ri.net.ua
 *  File    : listView.php
 *  Path    : app/views/Abon/listView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:23:35
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of listView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

?>
<br>
<div class="container">

    <br>
    <h3>Список пользователей</h3>
    <br>

    <?php

        /**
         * Вывод таблицы фильтров
         */
        echo get_html_table(
                t: [$filter_buttons],
                table_attributes: "width=100% align=right cellpadding=5 cellspacing=3",
                cell_attributes:  [
                    "",
                    "align=center width=40pt",
                    "align=center width=40pt",
                    "align=center width=40pt",
                    "align=center width=40pt",
                    "align=center width=40pt",
                    "align=center width=40pt",
                    "align=center width=40pt"],
                show_No:  false
        );



        /**
         * Вывод оснвной таблицы абонентов
         */
        echo get_html_table(
                t: $print_arr,
                table_attributes: "width=100% border=0 align='center' cellpadding=5 cellspacing=3",
                //                 act    uid             aid             info    address  balance   prepayed    PPMA      PPDA      PP30A     PP01A     Остаток/предоплачено
                //                 edges
                //                 ТП
                col_titles: ["act", "uid", "aid", /* "stat", */ "info [" . $html_sort_name . "]", "address", "balance", "prepayed", "PPMA", "PPDA", "PP30A", "PP01A", "Остаток [" . $html_sort_balance . "]<br><font color=gray size=-2>опл.&nbsp;дней [" . $html_sort_prepayed . "]</font>",
                    "<font size=-2><nobr>PPMA&nbsp;|&nbsp;PPDA<br>" . $html_sort_pp30a . "<br>WARN&nbsp;|&nbsp;OFF<br>AutoOFF</nobr></font>",
                    (is_null($show_tp) ? "" : "<font size=2>Показана ТП</font><br><font size=+1>" . paint($TP_LIST[$show_tp]['title'], 'blue') . "</font> <font size=2>" . paint($show_tp, 'gray') . "</font><br>") . "<a href=" . $def_make_url . " title='Вернуться к полному списку всех ТП' target=_self>Показать Все ТП</a>"],
                cell_attributes: ["", "align=center", "align=center", /* "align=center", */ "", "hidden", "hidden", "hidden", "hidden", "hidden", "hidden", "hidden", "align=right",
                    "align=center",
                    ""]
        );
        //echo get_html_table($USERS_LIST);
        echo "<p align=cener>"
        . "<form name='form_contacts' method='post' action='/sms_full_list.php' target=_blank>"
        . "<input name=contacts type='hidden' value='" . implode(",", $contacts) . "'  />"
        . "<input type='submit' name='show_contacts' value='СМС' />"
        . "</form>"
        . "</p>";
        //echo "contacts: <pre>".implode(",", $contacts)."</pre><hr>";
        //echo "contacts: <pre>".print_r($contacts, true)."</pre><hr>";



        echo "<hr>";
//        echo get_html_table($wide);
        echo "<hr>";

    ?>

    <br>
    <hr>
    <br>

</div>
<br>

