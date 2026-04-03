<?php
/**
 *  Project : my.ri.net.ua
 *  File    : get_navigation.php
 *  Path    : app/views/inc/get_navigation.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Mar 2026 14:15:45
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use billing\core\App;
use config\Bank;
use config\tables\Ppp;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * Вывод навигации для  app/controllers/BankController.php | getAction()
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



?>

<!-- 
*
*   Навигация по датам:
*
-->
<div align=center>
    <?php 
        $d1_ts = $date1_ts-App::get_config('bank_date_interval');
        $d1_str = date('Y M d', $d1_ts); 
        $d2_ts = $date1_ts;
        $d2_str = date('Y M d', $d2_ts);
    ?>
    |&nbsp;&nbsp; <a href='?<?= Bank::F_GET_DATE1_TS ?>=<?= $d1_ts ?>&<?= Bank::F_GET_DATE2_TS ?>=<?= $d2_ts ?>'><?= $d1_str ?> -- <?= $d2_str ?></a>
    <?php 
        $d1_ts = $date1_ts;
        $d1_str = date('Y M d', $d1_ts); 
        $d2_ts = $date2_ts;
        $d2_str = date('Y M d', $d2_ts);
    ?>
    &nbsp;&nbsp;|&nbsp;&nbsp;<<<===&nbsp;&nbsp;&nbsp;<font size=+2 color=green><?= $d1_str ?> -- <?= $d2_str ?></font>&nbsp;&nbsp;&nbsp;===>>>&nbsp;&nbsp;|&nbsp;&nbsp;
    <?php 
        $d1_ts = $date2_ts;
        $d1_str = date('Y M d', $d1_ts); 
        $d2_ts = $date2_ts+App::get_config('bank_date_interval');
        $d2_str = date('Y M d', $d2_ts);
    ?>
    <a href='?<?= Bank::F_GET_DATE1_TS ?>=<?= $d1_ts ?>&<?= Bank::F_GET_DATE2_TS ?>=<?= $d2_ts ?>'><?= $d1_str ?> -- <?= $d2_str ?></a> &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href='<?= Bank::URI_GET ?>/<?= $ppp[Ppp::F_ID] ?>'>Сейчас</a> &nbsp;&nbsp;|
    <hr>
    <a class="text text-end mb-2" href='https://prev.ri.net.ua/ab_templates.php?ppp_id=<?=$ppp[Ppp::F_ID]?>' target=_blank>Редактирование абонентских шаблонов</a> | 
</div>
