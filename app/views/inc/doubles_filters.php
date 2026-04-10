<?php
/**
 *  Project : my.ri.net.ua
 *  File    : doubles_filters.php
 *  Path    : app/views/inc/doubles_filters.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Apr 2026 21:21:49
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of doubles_filters.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



use config\Doubles;
use config\tables\Ppp;
use billing\core\base\Lang;
use billing\core\Pagination;
use config\tables\Pay;
Lang::load_inc(__FILE__);



/**
 * Данные полученные из контроллера
 * 
 * @var string $title
 * @var array $filter
 * @var array $doubles
 * @var Pagination $pager
 * 
 */


$flags = [
    [
        "(pay1.abon_id = pay2.abon_id)",
        Doubles::F_BY_ABON_ID,
        $filter[Doubles::F_BY_ABON_ID],
        "Сравнивать только с одинаковыми AID"
    ],

    [
        "(pay1.pay_type_id = pay2.pay_type_id)",
        Doubles::F_BY_PAY_TYPE_ID,
        $filter[Doubles::F_BY_PAY_TYPE_ID],
        "Сравнивать тип платежа (пополнение, корректировка, начисление)"
    ],

    [
        "(pay1.pay_ppp_id = pay2.pay_ppp_id)",
        Doubles::F_BY_PAY_PPP_ID,
        $filter[Doubles::F_BY_PAY_PPP_ID],
        "Сравнивать ППП платежа"
    ],

    [
        "(pay1.pay_bank_no = pay2.pay_bank_no)",
        Doubles::F_BY_PAY_BANK_NO,
        $filter[Doubles::F_BY_PAY_BANK_NO],
        "Сравнивать банковский № платежа. <span class='text-danger text-nowrap'>Не уникален!</span>"
    ],

    [
        "(pay1.pay_fakt = pay2.pay_fakt)",
        Doubles::F_BY_PAY_FAKT,
        $filter[Doubles::F_BY_PAY_FAKT],
        "Сравнивать фактический платёж."
    ],

    [
        "(pay1.pay = pay2.pay)",
        Doubles::F_BY_PAY_ACNT,
        $filter[Doubles::F_BY_PAY_ACNT],
        "Сравнивать платёж вносимый на ЛС."
    ],

    [
        "(pay1.pay > 0) AND (pay2.pay > 0)",
        Doubles::F_BY_PAY_ACNT_CREDIT,
        $filter[Doubles::F_BY_PAY_ACNT_CREDIT],
        "Платёж вносимый на ЛС больше '0'."
    ],

    [
        "(pay1.description = pay2.description) <span class='text-danger fw-bold'>Может НЕ распознать дубликаты!</span>",
        Doubles::F_BY_DESCR,
        $filter[Doubles::F_BY_DESCR],
        "Сравнивать по комментарию. <span class='text-danger fw-bold'>Медленно!</span>"
    ],

];


?>

<div class="card">
    <div class="card-header">
        <div class="row mb-2">
            <div class="col-6 text-end">
                <label class="form control hover-pointer" for="<?= Doubles::F_DATE1_STR ?>">Проверять после даты:</label>
            </div>
            <div class="col-3">
                <input class="form-control form-control-sm"
                    id="<?= Doubles::F_DATE1_STR ?>"
                    name="<?= Doubles::F_DATE1_STR ?>"
                    type="text"
                    value="<?= date("Y-m-d H:i:s", $filter[Doubles::F_DATE1_TS]) ?>">
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row mt-3">
            <div class="col-6 text-end">
                Искать двойников в платежах с указанных пунктов приёма платежей
            </div>
            <div class="col-6">
                <?php foreach ($filter[Doubles::F_PPP_LIST] as $ppp): ?>
                    <div class="mt-1">
                        <label class="hover-pointer">
                            <span class="text-secondary"><?= num_len($ppp[Ppp::F_ID], 3) ?></span>
                            <input class="m-2" name='<?= Doubles::F_PPP_INCLUDE ?>[<?= $ppp[Ppp::F_ID] ?>]' type='checkbox' <?= ($ppp[Doubles::F_PPP_INCLUDE] ? "checked" : "") ?> value="1">
                            <span class="text-primary fw-bold"><?= $ppp[Ppp::F_TITLE] ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-3">
            <?php foreach ($flags as [$expr, $name, $value, $label]): ?>
                <div class="row mx-3 py-2 align-items-start border-bottom">
                    <div class="col-6 text-center">
                        <span class="font-monospace fw-bold text-primary"><?= $expr ?></span>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="<?= $name ?>"
                                name="<?= $name ?>"
                                value="1"
                                <?= ($value ? 'checked' : '') ?>>
                            <label class="form-check-label hover-pointer" for="<?= $name ?>">
                                <?= $label ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-3">
            <div class="col-6 text-end">
                <span>
                    Точность сравнения времени транзакции по шаблону <span class="text-primary font-monospace">%Y-%m-%d %H:%i:%s</span>
                </span>
            </div>
            <div class="col-6">
                <label class="hover-pointer">
                    (<span class="font-monospace text-primary">%Y-%m-%d %H:%i:%s</span><span class="font-monospace text-secondary"></span>)
                    <input class="hover-pointer ms-1" type=radio name='<?= Doubles::F_BY_PAY_TIME_LVL ?>' value=1 <?= ($filter[Doubles::F_BY_PAY_TIME_LVL] == 1 ? "checked" : "") ?> >
                </label><br>
                <label class="hover-pointer">
                    (<span class="font-monospace text-primary">%Y-%m-%d %H:%i</span><span class="font-monospace text-secondary">:%s</span>)
                    <input class="hover-pointer ms-1" type=radio name='<?= Doubles::F_BY_PAY_TIME_LVL ?>' value=2 <?= ($filter[Doubles::F_BY_PAY_TIME_LVL] == 2 ? "checked" : "") ?> >
                </label><br>
                <label class="hover-pointer">
                    (<span class="font-monospace text-primary">%Y-%m-%d %H</span><span class="font-monospace text-secondary">:%i:%s</span>)
                    <input class="hover-pointer ms-1" type=radio name='<?= Doubles::F_BY_PAY_TIME_LVL ?>' value=3 <?= ($filter[Doubles::F_BY_PAY_TIME_LVL] == 3 ? "checked" : "") ?> >
                </label><br>
                <label class="hover-pointer">
                    (<span class="font-monospace text-primary">%Y-%m-%d</span><span class="font-monospace text-secondary"> %H:%i:%s</span>)
                    <input class="hover-pointer ms-1" type=radio name='<?= Doubles::F_BY_PAY_TIME_LVL ?>' value=4 <?= ($filter[Doubles::F_BY_PAY_TIME_LVL] == 4 ? "checked" : "") ?> >
                </label><br>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <div class="row mt-3">
            <div class="col-6">
                <a class="btn btn-secondary" href="<?= Pay::URI_DOUBLES ?>">Сбросить</a>
            </div>
            <div class="col-6">
                <button class="btn btn-primary" type='submit' name='<?= Doubles::F_CMD_DO ?>' value="1">Искать</button>
            </div>
        </div>
    </div>
</div>


