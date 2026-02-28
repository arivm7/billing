<?php
/**
 *  Project : my.ri.net.ua
 *  File    : sms_filter_form.php
 *  Path    : app/views/inc/sms_filter_form.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Feb 2026 03:09:05
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Форма фильтра для генерации списка абонентов для СМС-рассылки 
 * и автоотметок для отправки СМС
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




use billing\core\base\Lang;
use config\tables\Notify;
use config\tables\TP;

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

?>

<div class="my-0 p-0">

    <div class="card shadow-sm d-inline-block w-auto">

        <div class="card-header">
            <strong>Параметры генерации списка СМС-рассылки</strong>
        </div>

        <div class="card-body">

            <form action="" method="post">

                <!-- Фильтр ТП -->
                <div class="row mb-3 align-items-center">
                    <label class="col-4 col-form-label">
                        Фильтр для ТП:
                    </label>

                    <div class="col-8">
                        <select name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_TP_ID ?>]" class="form-select">
                            <option value="0">-</option>

                            <?php foreach ($tp_list as $tp_one): ?>
                                <option value="<?= $tp_one[TP::F_ID]; ?>"
                                    <?= ($tp_one[TP::F_ID] == $selected_tp_id ? "selected" : ""); ?>
                                    title="<?= $tp_one[TP::F_DESCRIPTION]; ?>">

                                    [<?= sprintf("%02d", $tp_one[TP::F_ID]); ?>]
                                    <?= $tp_one[TP::F_TITLE]; ?>

                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Фильтр абонента -->
                <div class="row mb-3 align-items-center">
                    <label class="col-4 col-form-label">
                        Фильтр для абонента:
                    </label>

                    <div class="col-4">
                        <input name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_ABON_ID ?>]"
                               type="text"
                               value="<?= $abon_id; ?>"
                               class="form-control text-center">
                    </div>
                </div>

                <!-- Показывать абонентов на паузе -->
                <div class="row mb-3 align-items-center">
                    <label class="col-4 col-form-label">
                        Показывать на паузе:
                    </label>

                    <div class="col-8">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_SHOW_PAUSED ?>]"
                                   value="1"
                                   <?= ($show_paused ? "checked" : ""); ?>
                                   title="Показывать абонентов на паузе, с нулевым прайсом.">

                        </div>
                    </div>
                </div>

                <!-- Не отправлять, если уже отправляли -->
                <div class="row mb-3 align-items-center">
                    <label class="col-4 col-form-label">
                        Не отправлять тем, кому уже отправляли до
                    </label>

                    <div class="col-3">
                        <input name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_NOT_SEND_DAYS ?>]"
                               type="text"
                               value="<?= $not_send_days; ?>"
                               class="form-control text-center">
                    </div>

                    <div class="col-5">
                        дней тому [
                        <span class="text-primary">
                            <?= date("Y-m-d", strtotime("-{$not_send_days} days")); ?>
                        </span> ]
                    </div>
                </div>

                <!-- Не отправлять, если оплачивал -->
                <div class="row mb-3 align-items-center">
                    <label class="col-4 col-form-label">
                        Не отправлять тем, кто уже оплачивал
                    </label>

                    <div class="col-3">
                        <input name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_NOT_PAY_DAYS ?>]"
                               type="text"
                               value="<?= $not_pay_days; ?>"
                               class="form-control text-center">
                    </div>

                    <div class="col-5">
                        дней тому
                        <span class="text-primary">
                            (<?= date("Y-m-d", strtotime("-{$not_pay_days} days")); ?>)
                        </span>
                    </div>
                </div>

                <!-- Максимум СМС -->
                <div class="row mb-4 align-items-center">
                    <label class="col-4 col-form-label">
                        Рекомендуемое количество СМС:
                    </label>

                    <div class="col-3">
                        <input name="<?= Notify::FLTR_PREFIX ?>[<?= Notify::FLTR_MAX_COUNT ?>]"
                               type="text"
                               value="<?= $max_count_sms; ?>"
                               class="form-control text-center">
                    </div>

                    <div class="col-5">
                        смс (0 -- автоматически, без ограничения)
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="d-flex justify-content-between">

                    <a href="<?= Notify::URI_SMS_LIST ?>"
                       class="btn btn-secondary">
                        Сбросить
                    </a>

                    <button type="submit"
                            class="btn btn-primary">
                        Получить новый список
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<hr>
