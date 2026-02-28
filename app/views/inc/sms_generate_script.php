<?php
/**
 *  Project : my.ri.net.ua
 *  File    : sms_generate_script.php
 *  Path    : app/views/inc/sms_generate_script.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Feb 2026 03:09:27
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * формирование скрипта для отправки СМС-рассылки
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\base\Lang;
use config\Icons;
use config\tables\AbonRest;

Lang::load_inc(__FILE__);



/**
 * Данные переданные из контроллера
 * 
 * @var string $title
 * 
 * Данные для фильтра
 * @var bool $filter_set    -- флаг, означающий, что фильтры установлены, можно отображать список.
 * @var array $tp_list      -- массив технических площадок для отображения в форме фильтра.
 * @var int $selected_tp_id -- выбранная техническая площадка (ТП). Для установки в форме фильтра.
 * @var int $abon_id        -- ID абонента для которого отображается список. Для установки в форме фильтра.
 * @var int $show_paused    -- флаг для отображения приостановленных абонентов. Для установки в форме фильтра.
 * @var int $not_send_days  -- количество дней пошедших после отправки предыдущего уведомления, при пересечении которых отправлять повторное СМС.
 * @var int $not_pay_days   -- количество дней после последнего платежа, при пересечении которых отправлять СМС.
 * @var int $max_count_sms  -- максимальное количество СМС в автоматически формируемом списке. 0 - автоматически, без ограничений.
 * 
 * Данные для списка
 * @var array $lines        -- массив абонентских строк для отрисовки списка
 * @var bool $do_script_show -- флаг для генерации скрипта или отображения формы списка
 */

$count_selected = count(array_filter($lines, function($line) { return $line['do_send']; }));

$script = "#!/bin/sh\n\n\n"
."#".date("Y-m-d")." (". $count_selected . "/" . count($lines).")"."\n\n\n";

$n = 0; 
for ($index = 0; $index < count($lines); $index++) {
    if(!$lines[$index]['do_send']) { continue; } 
    $sms = get_sms_debts_rec(
                    $lines[$index]['abon_id'],                             // $abon_id
                    $lines[$index]['phone_main'],                       // $phone_main
                    $lines[$index][AbonRest::TABLE][AbonRest::F_SUM_PP30A],   // $pp30a,
                    $lines[$index][AbonRest::TABLE][AbonRest::F_BALANCE]   // $balance
                    );

    $script .= "<span class='text-secondary'>echo \"" . (++$n) . "/" . $count_selected . ". " . $lines[$index]['address'] . "\"</span>\n";
    $script .= $sms['cmd'] . " " . $sms['phone'] . " \"".$sms['text'] . "\"" . ' ' . $sms['abon_id'] . "\n\n";
}

$script .= "";

?>


<div class="my-0 p-0">

    <div class="card shadow-sm d-inline-block w-auto">

        <div class="card-header">
            <strong>Скрипт для отправки СМС</strong>
        </div>

        <div class="card-body">

            <pre>

----cut----

<?= $script; ?>

----end-cut----

            </pre>

        </div>

        <div class="card-footer text-end">
            
            <button class="btn btn-outline-primary btn-sm p-1 copy-btn" data-text='<?= strip_tags($script); ?>'>
                <img src="<?= Icons::SRC_ICON_CLIPBOARD; ?>" title="<?= __('Скопировать в clipboard') ?>" alt="[copy]" height="30rem">
            </button><br>

        </div>

    </div>

</div>

