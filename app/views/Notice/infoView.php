<?php
/**
 *  Project : my.ri.net.ua
 *  File    : infoView.php
 *  Path    : app/views/Notice/infoView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Nov 2025 22:00:51
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of infoView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Notice Info View
 * Отображение информации для отправки абоненту
 * 
 * @var string $title
 * @var array $abon
 * @var array $user
 * @var array $rest
 * @var array $pa_list
 * @var array $ppp_list
 */

use billing\core\App;
use config\Icons;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Notify;
use config\tables\PA;
use config\tables\Ppp;
use config\tables\PppType;
use config\tables\User;

require_once DIR_LIBS . '/functions.php';
require_once DIR_LIBS . '/sms_functions.php';

/**
 * Просто порядковый номер информационного блока
 */
$tp_print_num = 0;

/**
 * Просто порядковый номер СМС для регистрации в базе
 */
$sms_print_num = 0;

?>
<form action='' method='post' name='f1' id='f1' target='_self' >
    <input name='<?=Notify::POST_REC;?>[<?=Notify::F_ABON_ID;?>]' type='hidden' value='<?=$abon[Abon::F_ID];?>'>
</form>
<div class="container w-auto">

    <!-- Статус абонента -->
    <h3 class="fs-4 bukvitca">Статус абонента</h3>
    <table class='table table-striped table-hover table-bordered w-auto'>
        <tr><td class="text-end">Начислено:</td>            <td class="text-end font-monospace"><?= number_format($rest[AbonRest::F_SUM_COST], 2, '.', ' '); ?></td>   <td>грн</td></tr>
        <tr><td class="text-end">Оплачено:</td>             <td class="text-end font-monospace"><?= number_format($rest[AbonRest::F_SUM_PAY], 2, '.', ' '); ?></td>    <td>грн</td></tr>
        <tr><td class="text-end">Остаток на ЛС:</td>        <td class="text-end font-monospace"><?= number_format($rest[AbonRest::F_REST], 2, '.', ' '); ?></td>       <td>грн</td></tr>
        <!-- 
        <tr><td class="text-end">PPMA:</td>                 <td class="text-end font-monospace"><?= number_format($rest[AbonRest::F_SUM_PPMA], 2, '.', ' '); ?></td>   <td>грн/мес</td></tr>
        <tr><td class="text-end">PPDA:</td>                 <td class="text-end font-monospace"><?= number_format($rest[AbonRest::F_SUM_PPDA], 2, '.', ' '); ?></td>   <td>грн/сут</td></tr> 
        -->
        <tr><td class="text-end">Абонплата за 30 дней:</td> <td class="text-end font-monospace<?= ($rest[AbonRest::F_SUM_PP30A] < 10 ? " text-danger" : " text-success") ?>"><?= number_format($rest[AbonRest::F_SUM_PP30A], 2, '.', ' '); ?></td>  <td>грн/30дней</td></tr>
        <tr><td class="text-end">Предоплачено дней:</td>    <td class="text-end font-monospace"><?= $rest[AbonRest::F_PREPAYED]; ?></td>                                                            <td>дней</td></tr>
    </table>

    <h2 class="fs-3 mt-5 mb-4"><?= h($title); ?></h2>

    <!-- Вход в личный кабинет абонента -->
    <h3 class="fs-4 bukvitca"><?= (++$tp_print_num); ?>&nbsp;.&nbsp;Особистий кабінет</h3>
    <?php
    $sms_print_num++;
    $notice_title = 'СМС :: Вход в личный кабинет';
    $notice_text = "Dog. ". $abon[Abon::F_ID]."\n"
            ."https://my.ri.net.ua\n"
            ."Login: ". $user[User::F_LOGIN]."\n"
            ."Pass: ".substr($user[User::F_PHONE_MAIN], 3) . "";
    require DIR_INC . '/notice_block.php';
    ?>

    <!-- Инструкции по оплате по списку ППП -->
    <?php foreach ($ppp_list as $index => $ppp): ?>
        <h3 class="fs-4 bukvitca"><?= (++$tp_print_num); ?>&nbsp;.&nbsp;<?= $ppp[Ppp::F_TITLE] ?></h3>
        <?php 
        switch ($ppp[Ppp::F_TYPE_ID]) {
            case PppType::TYPE_BANK:
                // Банковские реквизиты
                $sms_print_num++;
                $notice_title = 'СМС :: Регулярный платёж';
                $notice_text = untemplate(str_replace('\n', CR,   $ppp[Ppp::F_SMS_PAY_INFO]), [
                    "{NUMBER_INFO}" => $ppp[Ppp::F_NUMBER_INFO],
                    "{NUMBER}" => $ppp[Ppp::F_NUMBER],
                    "{PORT}" => $abon[Abon::F_ID],
                    "{SUM}" => $rest[AbonRest::F_SUM_PP30A],
                ]);
                require DIR_INC . '/notice_block.php';
                break;

            case PppType::TYPE_CARD:
                // Месячная Абонплата
                $sms_print_num++;
                $notice_title = 'СМС :: Регулярный платёж';
                $notice_text = untemplate(str_replace('\n', CR,   $ppp[Ppp::F_SMS_PAY_INFO]), [
                    "{NUMBER_INFO}" => $ppp[Ppp::F_NUMBER_INFO],
                    "{NUMBER}" => $ppp[Ppp::F_NUMBER],
                    "{PORT}" => $abon[Abon::F_ID],
                    "{SUM}" => $rest[AbonRest::F_SUM_PP30A],
                ]);
                require DIR_INC . '/notice_block.php';

                // Расчёт до конца месяца
                $sms_print_num++;
                $notice_title = 'СМС :: До конца месяца';
                $notice_text = untemplate(str_replace('\n', CR,   $ppp[Ppp::F_SMS_PAY_INFO]), [
                    "{NUMBER_INFO}" => $ppp[Ppp::F_NUMBER_INFO],
                    "{NUMBER}" => $ppp[Ppp::F_NUMBER],
                    "{PORT}" => $abon[Abon::F_ID],
                    "{SUM}" => $rest[AbonRest::F_PAY_CUR_MONTH],
                ]);
                require DIR_INC . '/notice_block.php';

                // Долг + Абонплата
                $sms_print_num++;
                $notice_title = 'СМС :: Долг + Абонплата';
                $notice_text = untemplate(str_replace('\n', CR,   $ppp[Ppp::F_SMS_PAY_INFO]), [
                    "{NUMBER_INFO}" => $ppp[Ppp::F_NUMBER_INFO],
                    "{NUMBER}" => $ppp[Ppp::F_NUMBER],
                    "{PORT}" => $abon[Abon::F_ID],
                    "{SUM}" => $rest[AbonRest::F_AMOUNT],
                ]);
                require DIR_INC . '/notice_block.php';
                break;

            default:
                ?>
                <p class="text text-secondary">Нет инструкции по оплате на вашей техплощадке. Свяжитесь с мастером участка.</p>
                <?php
                break;
        }
        ?>
    <?php endforeach; ?>

    <!-- Информационные СМС об остатке на ЛС и оплате -->
    <h3 class="fs-4 bukvitca"><?= (++$tp_print_num); ?>&nbsp;.&nbsp;Информационные СМС об остатке на ЛС и оплате</h3>
    <?php
    $sms_print_num++;
    $notice_title = 'СМС :: Стандартная информация';
    $notice_text = "RILAN:Дог.{$abon[Abon::F_ID]} Залиш.".round($rest[Abonrest::F_REST], 0)."гр, абонпл.{$rest[Abonrest::F_SUM_PP30A]}гр/м. До сплати {$rest[Abonrest::F_AMOUNT]}гр";
    require DIR_INC . '/notice_block.php';

    $sms_print_num++;
    $notice_title = 'Script command';
    $first_line_attr = "class='text-secondary'";
    $notice_text  = 'echo "'.$abon[Abon::F_ADDRESS].', '.$user[User::F_NAME_SHORT].'"' . CR
                    . App::get_config('sms_sender') . ' ' . $user[User::F_PHONE_MAIN] . ' "' . $notice_text . '"';
    require DIR_INC . '/notice_block.php';
    ?>

    <!-- Настройки IP -->
    <h3 class="fs-4 bukvitca"><?= (++$tp_print_num); ?>&nbsp;.&nbsp;Настройки IP</h3>
    <?php
    foreach ($pa_list as $id => $pa) {
        $sms_print_num++;
        $notice_title = 'СМС :: Настройки IP';
        $first_line_attr = "class='fw-bolder'";
        $notice_text  = "{$pa[PA::F_NET_NAME]}" . CR
                        . "IP: " . $pa[PA::F_NET_IP] . CR
                        . "MASK: " . $pa[PA::F_NET_MASK] . CR
                        . "GATE: " . $pa[PA::F_NET_GATEWAY] . CR
                        . "DNS1: " . $pa[PA::F_NET_DNS1] . CR
                        . "DNS2: " . $pa[PA::F_NET_DNS2];
        require DIR_INC . '/notice_block.php';
    }
    ?>
    
    <!-- Ручной ввод СМС -->
    <h3 class="fs-4 bukvitca"><?= (++$tp_print_num); ?>&nbsp;.&nbsp;Ручной набор СМС</h3>
    <?php
    $sms_print_num++;
    $notice_title = 'Ввод текста СМС';
    $notice_text  = "З ".date("d.m.Y", next_month_first_day())."р. "
            . "абонплата за інтернет на ваших підключеннях "
            . "складатиме 300грн/30діб. "
            . "Подробиці у вашого майстра";
    ?>
    <fieldset class="border mt-4 p-3">
        <legend class="text-info text-start"><?= $notice_title; ?></legend>
        <div>
            <textarea class="w-100 mb-2" form=f1 name='<?=Notify::POST_REC;?>[msg][<?= $sms_print_num; ?>][text]' rows=<?= get_count_rows_for_textarea($notice_text, 3); ?>><?= $notice_text; ?></textarea>
        </div>
        <div class="text-secondary text-end">
            <label><span class="text-secondary">Регистрировать СМС: </span>
            <input class="form-check-input" form="f1" name="<?=Notify::POST_REC;?>[msg][<?= $sms_print_num; ?>][register]" value="1" type="checkbox"></label>
        </div>
    </fieldset>
    <?php $sms_info = get_sms_info_rec($notice_text); ?>
    <div class='text-secondary'>
        Количество символов: <?= $sms_info['len']; ?>. Всего СМС: <?= $sms_info['count_sms']; ?> (<?= $sms_info['full_sms']; ?> полных и <?= $sms_info['char_in_last_sms']; ?><span class="fs-7">/<?= $sms_info['chars1sms']; ?></span> символов в последнем)
    </div>

    <br>
    <input class="btn btn-outline-primary btn-sm" form=f1 name='register_sms' type='submit' value='Зарегистрировать выбранные уведомления'>


</div>
