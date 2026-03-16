<?php
/**
 *  Project : my.ri.net.ua
 *  File    : sms_list_form.php
 *  Path    : app/views/inc/sms_list_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Feb 2026 03:09:36
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Вывод списка абонентов для СМС-рассылки и чекбоксов для выбора тех, кому отправлять СМС
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use billing\core\base\Lang;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Notify;

Lang::load_inc(__FILE__);

require_once DIR_LIBS . '/sms_functions.php';



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


<form action="" method="post" target="_self">

    <!-- Скрытые параметры фильтра -->
    <input type="hidden" name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_TP_ID ?>]" value="<?= $selected_tp_id ?>">
    <input type="hidden" name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_ABON_ID ?>]" value="<?= $abon_id ?>">
    <input type="hidden" name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_SHOW_PAUSED ?>]" value="<?= ($show_paused ? "1" : "0") ?>">
    <input type="hidden" name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_NOT_SEND_DAYS ?>]" value="<?= $not_send_days ?>">
    <input type="hidden" name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_NOT_PAY_DAYS ?>]" value="<?= $not_pay_days ?>">
    <input type="hidden" name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_MAX_COUNT ?>]" value="<?= $max_count_sms ?>">

    <div class="card shadow-sm my-3 min-w-400">

        <div class="card-header">
            <div class="row fw-bold">
                <div class="col-1">№</div>
                <div class="col-6">ИНФО</div>
                <div class="col-3">Даты оплаты и СМС</div>
                <div class="col-2 text-end">Отправлять</div>
            </div>
        </div>

        <div class="list-group list-group-flush">

            <?php for ($index = 0; $index < count($lines); $index++) : ?>

                <?php
                    $sms_debts_rec = get_sms_debts_rec(
                        $lines[$index]['abon_id'],
                        $lines[$index]['phone_main'],
                        $lines[$index][AbonRest::TABLE][AbonRest::F_SUM_PP30A],
                        $lines[$index][AbonRest::TABLE][AbonRest::F_BALANCE]
                    );
                ?>

                <div class="list-group-item">

                    <div class="row">

                        <!-- No -->
                        <div class="col-1 fs-3 text-nowrap text-end">
                            <?= $index+1 ?>.
                        </div>

                        <!-- ИНФО -->
                        <div class="col-6 small">

                            <div class="text fs-6 bg-primary-subtle p-2 mb-2 rounded">
                                <?= h($lines[$index]['address']) ?>,<br>
                                <?= h($lines[$index]['name_short']) ?>

                                <a href="<?= Abon::URI_VIEW ?>/<?= $lines[$index]['abon_id'] ?>"
                                   target="_blank"
                                   class="badge rounded-pill text-bg-success m-1">
                                    A
                                </a>
                            </div>

                            <div>
                                <span class="text-secondary">
                                    Границы обслуживания:
                                    <span title="<?= Abon::description(Abon::F_DUTY_MAX_WARN) ?>"><?= $lines[$index][Abon::F_DUTY_MAX_WARN] ?></span> |
                                    <span title="<?= Abon::description(Abon::F_DUTY_MAX_OFF) ?>"><?= $lines[$index][Abon::F_DUTY_MAX_OFF] ?></span> |
                                    <span title="<?= Abon::description(Abon::F_DUTY_AUTO_OFF) ?>"><?= get_html_CHECK($lines[$index][Abon::F_DUTY_AUTO_OFF]) ?></span> |
                                    Оплаченных дней:&nbsp;&nbsp;
                                    <?=
                                        (($lines[$index][AbonRest::TABLE][AbonRest::F_SUM_PP30A] > 0)
                                            ? round($lines[$index][AbonRest::TABLE][AbonRest::F_PREPAYED])
                                            : '<span class="text-danger">НУЛЕВОЙ ПРАЙС</span>')
                                    ?>
                                </span>
                            </div>

                            <div class="text-muted">
                                <?= Notify::get_warn_message(
                                    $warn_status = Notify::get_warn_status(
                                        $lines[$index][AbonRest::TABLE],
                                        $lines[$index][Abon::F_DUTY_MAX_WARN],
                                        $lines[$index][Abon::F_DUTY_MAX_OFF]
                                        )
                                    ) 
                                . ($warn_status == Notify::WARN_IS_OFF 
                                    ? '<span class="text ms-2">'
                                        .date('Y-m-d', $lines[$index][AbonRest::TABLE][ AbonRest::F_DATE_PAUSED] ?? 0) . ' | '
                                        .get_between_days($lines[$index][AbonRest::TABLE][ AbonRest::F_DATE_PAUSED] ?? 0, TODAY(), NULL_HAS_TODAY, IGNORE_TIME_ON).' дней тому</span>' 
                                    : ''
                                  )
                                ?>
                            </div>

                            <div class="mt-2 fs-6 text-bg-secondary p-2 rounded">
                                <span class="text font-monospace small"><?= $sms_debts_rec['phone'] ?></span> | 
                                <?= $sms_debts_rec['text'] ?>
                            </div>

                        </div>

                        <!-- ДАТЫ -->
                        <div class="col-3 small font-monospace">

                            <div>
                                SMS:
                                <?= (!empty($lines[$index]['last_sms'])
                                    ? date("Y-m-d", $lines[$index]['last_sms']['date'])
                                    : "-") ?>

                                <span class="text-primary">
                                    <?= (!empty($lines[$index]['last_sms'])
                                        ? get_between_days($lines[$index]['last_sms']['date'], TODAY(), NULL_HAS_TODAY, IGNORE_TIME_ON)
                                        : "-") ?>
                                </span> дн.
                            </div>

                            <div>
                                PAY:
                                <?= (!empty($lines[$index]['last_pay'])
                                    ? date("Y-m-d", $lines[$index]['last_pay']['pay_date'])
                                    : "-") ?>

                                <span class="text-primary">
                                    <?= (!empty($lines[$index]['last_pay'])
                                        ? get_between_days($lines[$index]['last_pay']['pay_date'], TODAY(), NULL_HAS_TODAY, IGNORE_TIME_ON)
                                        : "-") ?>
                                </span> дн.
                            </div>

                        </div>

                        <!-- ЧЕКБОКС -->
                        <div class="col-2 text-end align-self-center">

                            <div class="form-check d-inline-flex align-items-center">
                                <label class="form-check-label text-muted small hover-pointer mb-0" 
                                    for="checkbox<?= $lines[$index]['abon_id'] ?>">
                                    <?= $lines[$index]['abon_id'] ?>&nbsp;
                                </label>
                                <input class="form-check-input ms-2"
                                    type="checkbox"
                                    id="checkbox<?= $lines[$index]['abon_id'] ?>"
                                    name="<?= Notify::FLTR_ABON_ID_LIST ?>[<?= $lines[$index]['abon_id'] ?>]"
                                    value="1"
                                    <?= ($lines[$index]['do_send'] ? "checked" : "") ?>>
                            </div>
                        </div>

                    </div>

                </div>

            <?php endfor; ?>

        </div>

        <div class="card-footer d-flex justify-content-end">

            <button name="do_script_show"
                    value="1"
                    type="submit"
                    class="btn btn-primary">
                Получить скрипт
            </button>

        </div>

    </div>

</form>

