<?php
/**
 *  Project : my.ri.net.ua
 *  File    : listView.php
 *  Path    : app/views/Email/listView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 07 Mar 2026 21:29:51
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use app\controllers\EmailController;
use app\controllers\InvoiceController;
use config\Email;
use config\Icons;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Invoice;
use config\tables\Notify;
use config\tables\User;

/**
 * Description of listView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * Данные из контроллера
 * @var string $title
 * @var string $today
 * @var string $to_test_send
 * @var int $autocreate_invoice
 * @var array $list_send
 * 
 */

$subform_id_prefix = 'email-form-';
$subform_template = "<form id='{$subform_id_prefix}{ABON_ID}' method='post' action='".Email::URI_FORM."' target='_blank' style='display:none;'></form>\n";
foreach ($list_send as $a) {
    echo untemplate(
            $subform_template, 
            ['{ABON_ID}' => $a[Abon::TABLE][Abon::F_ID]]
        );
} 

?>

<form method=post action=''>
    <h1 class="fs-"><?= $title ?></h1>
    <hr>

    <div class="text-center align-bottom">
        <span>
            <a href="<?= Email::URI_LIST ?>?<?= Notify::F_TODAY ?>=<?= mktime(hour: 0, minute: 0, second: 0, month: month($today)-1, day: 1, year: year($today)); ?>" 
                target="_self"
                title="<?= __('Сформировать список для предыдущего мексяца') ?>"
                ><?= __('На предыдущий месяц') ?></a>
        </span>&nbsp;&nbsp;&nbsp;&nbsp;
        <span>Список для месяца: <span class="badge text-bg-info fs-6"><?= date('m.Y', $today) ?></span> <span class="text-secondary"><?= (date('mY', $today) == date('mY') ? " Текущий" : "") ?></span></span>&nbsp;&nbsp;&nbsp;&nbsp;
        <span>
            <a href="<?= Email::URI_LIST ?>?<?= Notify::F_TODAY ?>=<?= mktime(hour: 0, minute: 0, second: 0, month: month($today)+1, day: 1, year: year($today)); ?>" 
                target="_self"
                title="<?= __('Сформировать список для следующего мексяца') ?>"
                ><?= __('На следующий месяц') ?></a>
        </span>&nbsp;&nbsp;&nbsp;&nbsp;
        <hr>
        <span><label class="hover-pointer">Автоматически создавать счета <input type=checkbox name='<?= Email::REC ?>[<?= Email::F_AUTOCREATE_INV ?>]' value='1' <?= ($autocreate_invoice ? "checked" : "") ?>></label></span>&nbsp;&nbsp;&nbsp;&nbsp;
        <button type="submit" class="btn btn-primary px-3" name='<?= Email::REC ?>[<?= Email::F_DO_SEND ?>]' value="1" ><?= __('Отправить отмеченным абонентам') ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
        <span title="<?= __('Очистити поле для відправки реальним отримувачам') ?>"><?= __('Тестовая отправка') ?>: <input type=text name='<?= Email::REC ?>[<?= Email::F_TO_TEST ?>]' value='<?= $to_test_send ?>'></span>&nbsp;&nbsp;&nbsp;&nbsp;
    </div>
    <hr>

    <table class='table table-striped table-hover table-bordered'>
        <tr>
            <th width="40%">-</th>
            <th width="5%">-</th>
            <th>-</th>
            <th>DO</th>
        </tr>
        <?php foreach ($list_send as $a): ?>
            <tr>
                <td>

                    <div class="p-3">
                        <!-- Row: User Info -->
                        <div class="row mb-3 align-items-start">
                            <div class="col-2 text-md-end fw-bold">
                                <a href="<?= Abon::URI_VIEW ?>/<?= $a[User::TABLE][User::F_ID] ?>" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                title="User ID"
                                class="text-decoration-none">
                                    <?= num_len($a[User::TABLE][User::F_ID], 6) ?>:
                                </a>
                            </div>
                            <div class="col-10">
                                <span title="USER.NAME_SHORT" class="fw-bold"><?= h($a[User::TABLE][User::F_NAME_SHORT]) ?></span>
                                <span class="text-muted mx-2">|</span>
                                <span title="USER.NAME_FULL"><?= h($a[User::TABLE][User::F_NAME_FULL]) ?></span>
                            </div>
                        </div>

                        <!-- Row: Abon Info -->
                        <div class="row mb-3 align-items-start">
                            <div class="col-2 text-md-end fw-bold">
                                <a href="<?= Abon::URI_VIEW ?>/<?= $a[Abon::TABLE][Abon::F_ID] ?>" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                title="Abon ID"
                                class="text-decoration-none">
                                    <?= num_len($a[Abon::TABLE][Abon::F_ID], 6) ?>:
                                </a>
                            </div>
                            <div class="col-10" title="ABON.ADDRESS">
                                <?= h($a[Abon::TABLE][Abon::F_ADDRESS]) ?>
                            </div>
                        </div>

                        <!-- Row: Recipients -->
                        <div class="row align-items-start">
                            <div class="col-2 text-md-end fw-bold">
                                TO:
                            </div>
                            <div class="col-10">
                                <!-- Clean addresses for email sending -->
                                <div class="mb-2">
                                    <?php if (!empty($a[Email::REC][Email::F_TO])): ?>
                                        <?php foreach ($a[Email::REC][Email::F_TO] as $rec_ro): ?>
                                            <div class="mb-1">
                                                <?= h($rec_ro['email']) ?>
                                                <?php if (!empty($rec_ro['name'])): ?>
                                                    <span class="text-muted">| <?= h($rec_ro['name']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Email from user record -->
                                <!-- 
                                <?php if (!empty($a[User::TABLE][User::F_EMAIL_MAIN])): ?>
                                    <div class="text-muted small">
                                        <i class="bi bi-person-email"></i> <?= h($a[User::TABLE][User::F_EMAIL_MAIN]) ?>
                                    </div>
                                <?php endif; ?> 
                                -->
                            </div>
                        </div>
                    </div>

                </td>



                <td>
                    <div class="p-3"> 
                        <?php 
                            $abon = $a[Abon::TABLE]; 
                            $rest = $a[AbonRest::TABLE]; 
                            include DIR_INC . '/abon_edges.php'; 
                        ?>
                    </div>
                </td>



                <td>
                    <div class="p-3">
                        <!-- Row: Абонплата -->
                        <div class="row mb-2 align-items-center" title="Фактическая абонплата по всем услугам в этом месяце. &#10;ВАЖНО: Считает на основе количества дней в указанном месяце, &#10;не учитывает возможные изменения прайсовых фрагментов в различных периодах.">
                            <div class="col-8 text-end fw-bold text-muted">
                                Абонплата:
                            </div>
                            <div class="col-4">
                                <span class="text-nowrap">
                                    <?= number_format($a[AbonRest::TABLE][AbonRest::F_SUM_PPMA_THIS], 2, ".", " ") ?>
                                </span>
                            </div>
                        </div>

                        <!-- Row: Количество счетов -->
                        <div class="row mb-2 align-items-center" title="Количество выписанных счетов в этом периоде">
                            <div class="col-8 text-end fw-bold text-muted">
                                Колич. счетов:
                            </div>
                            <div class="col-4">
                                <span class="text-nowrap"><?= count($a[Invoice::TABLE]) ?></span>
                            </div>
                        </div>

                        <!-- Row: К оплате / Автоматически -->
                        <div class="row mb-2 align-items-center" title="Сумма к оплате по всем уже выписанным счётам">
                            <div class="col-8 text-end fw-bold text-muted">
                                    Всего по счетам:
                            </div>
                            <div class="col-4">
                                <?php if (count($a[Invoice::TABLE])): ?>
                                    <?php 
                                        $invoice_payment = 0.0;
                                        foreach ($a[Invoice::TABLE] as $invoice) {
                                            $invoice_payment += $invoice[Invoice::F_COST_ALL];
                                        } 
                                    ?>
                                    <span class="badge text-bg-info">
                                        <?= number_format($invoice_payment, 2, ".", " ") ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">
                                        Auto
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Row: Количество отправленных -->
                        <div class="row mb-2 align-items-center" title="Количество уже отправленных счетов в этом периоде">
                            <div class="col-8 text-end fw-bold text-muted">
                                Отправленных:
                            </div>
                            <div class="col-4">
                                <span class="text-nowrap"><?= count($a[Notify::TABLE]) ?></span>
                            </div>
                        </div>

                        <!-- Row: Формат -->
                        <div class="row mb-3 align-items-center" title="Формат отправки писем этому пользователю">
                            <div class="col-8 text-md-end fw-bold text-muted">
                                Формат:
                            </div>
                            <div class="col-4">
                                <span class="text-nowrap">
                                    <?php if ($a[User::TABLE][User::F_EMAIL_SEND_HTML]): ?>
                                        <span class="badge text-bg-success fs-7" title="Письмо в формате HTML">
                                        <i class="bi bi-filetype-html"></i>
                                        <!-- <i class="bi bi-file-code"></i> -->
                                        </span>
                                    <?php else: ?>
                                        <span class="badge text-bg-success fs-7" title="Письмо в формате простого текста">
                                        <i class='bi bi-file-earmark-text'>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($a[User::TABLE][User::F_EMAIL_SEND_PDF]): ?>
                                        <span class="badge text-bg-success fs-7" title="Вложение в виде PDF файла">
                                        <i class='bi bi-file-earmark-pdf'></i>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Row: Чекбокс (отделен визуально) -->
                        <div class="row align-items-center">
                            <div class="col-12 text-center">
                                <div class="form-check">
                                    <label class="form-check-label fw-bold hover-pointer">
                                        <input type="checkbox" 
                                            name='<?= Email::REC ?>[<?= Abon::TABLE ?>][<?= $a[Abon::TABLE][Abon::F_ID]?>]' 
                                            value='1' 
                                            class="form-check-input"
                                            <?= (count($a[Notify::TABLE]) > 0 ? "" : "checked") ?>>
                                        Надіслати
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                </td>



                <td>
                    <div class="p-3"> 
                        <?php if (count($a[Invoice::TABLE])): ?>
                            <?php foreach ($a[Invoice::TABLE] as $invoice): ?>
                                <a class="btn btn-sm btn-outline-success m-1 ps-1 pe-2 py-1" 
                                    href="<?= Invoice::URI_EDIT ?>/<?= $invoice[Invoice::F_ID] ?>" 
                                    title="<?= __('Редактировать выписанный счёт') ?>"
                                    target="_blank"><img src="<?= Icons::SRC_ICON_INV_ACT_SHTAMP ?>" alt="INVOICE" height="28pt">&nbsp;<?= $invoice[Invoice::F_INV_NO] ?> | <?= $invoice[Invoice::F_INV_DATE_STR] ?> | <?= $invoice[Invoice::F_COST_ALL] ?> грн</a><br>
                            <?php endforeach ?>
                        <?php endif; ?>
                        <span class="text-nowrap">
                            <?php 
                                $query = http_build_query(
                                    data: InvoiceController::make_invoice(
                                        abon: $a[Abon::TABLE], 
                                        today: $today, 
                                        agent: $a['agents'][array_key_first($a['agents'])], 
                                        contragent: $a['contragents'][array_key_first($a['contragents'])],
                                        rest: $a[AbonRest::TABLE]
                                    ),
                                    numeric_prefix: "",
                                    encoding_type: PHP_QUERY_RFC1738
                                );
                            ?>
                            <a class="btn btn-sm btn-outline-warning m-1 ps-1 pe-2 py-1"
                                href="<?= Invoice::URI_EDIT ?>?<?= $query ?>"
                                title="<?= __('Создание нового счёта за услуги на текущий месяц'); ?>"
                                target="_blank"
                                ><img src="<?= Icons::SRC_ICON_INV_ACT_SHTAMP ?>" alt="INVOICE" height="28pt"> <?= __('Создать счёт') ?></a>

                            <?= url_email_subform(
                                form_id:  $subform_id_prefix . $a[Abon::TABLE][Abon::F_ID],
                                to: $a[User::TABLE][User::F_EMAIL_MAIN], 
                                subject: EmailController::make_email_subject($a['agents'], $a[Abon::TABLE]), 
                                body_html: EmailController::make_email_body($a['agents'], $a[Abon::TABLE], $a[Invoice::TABLE]), 
                                src: Icons::SRC_ICON_EMAIL2, 
                                text:  __('Написать письмо'),
                                height:"28pt", 
                                target: '_blank',
                                attributes: 'class="btn btn-sm btn-outline-warning m-1 ps-1 pe-2 py-1"',
                                title: __('Написать уведомление через встроенную форму'),
                                register: 1,
                                abon_id: $a[Abon::TABLE][Abon::F_ID],
                                ); ?>
                        </span>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</form>
