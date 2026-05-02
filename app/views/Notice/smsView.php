<?php
/**
 *  Project : my.ri.net.ua
 *  File    : smsView.php
 *  Path    : app/views/Notice/smsView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Feb 2026 03:09:45
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Диспетчер формирования скрипта 
 * для отправки СМС-сообщений абонентам.
 * Тут просто подклюжается фильтр и выводится форма списка или форма генерации скрипта
 * 
 * NoticeController.php -> smsView.php (этот файл) -> sms_filter_form.php, sms_generate_script.php | sms_list_form.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




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

?>

<?php include DIR_INC . '/sms_filter_form.php'; ?>


<!-- 
    распределение отрисовки между выбором и генерацией скрипта 
-->

<?php if ($do_script_show) : ?>

    <?php include DIR_INC . '/sms_generate_script.php'; ?>

<?php elseif ($filter_set) : ?>

    <?php include DIR_INC . '/sms_list_form.php'; ?>

<?php else : ?>

    <div class="alert alert-info m-0" role="alert">
        <?= __('Выберите параметры фильтра') ?>
    </div>    

<?php endif; ?>