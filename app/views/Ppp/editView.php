<?php
/**
 *  Project : my.ri.net.ua
 *  File    : editView.php
 *  Path    : app/views/Ppp/editView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Oct 2025 22:12:51
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of editView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\tables\Ppp;
use config\tables\PppType;
use config\tables\User;
use config\tables\Firm;
use config\SessionFields;
use billing\core\base\Lang;

require_once DIR_LIBS ."/bank_api.php";

/** @var array $ppp_item        */
/** @var callable $form_data_fn */
/** @var array $ppp_types       */
/** @var array $firms           */
/** @var array $owner           */

/**
 * Гарантируем, что $ppp_item определён и является массивом
 */
$ppp_item = (isset($ppp_item) && is_array($ppp_item)) ? $ppp_item : [];

/**
 * Если переданы данные формы в POST, используем их
 */
if (isset($_POST[SessionFields::FORM_DATA]) && is_array($_POST[SessionFields::FORM_DATA])) {
    $post_data = $_POST[SessionFields::FORM_DATA];
    unset($_POST[SessionFields::FORM_DATA]);
} else {
    $post_data = [];
}

/**
 * Функция возвращает значение поля формы
 * - сначала проверяет данные из POST
 * - если нет, берёт из $ppp_item
 * - если и там нет, возвращает пустую строку
 */
$form_data_fn = function(string $field) use ($ppp_item, $post_data) {
    return $post_data[$field]
        ?? $ppp_item[$field]
        ?? '';
};

// безопасные значения по умолчанию
$owner_id_val = ($form_data_fn(Ppp::F_OWNER_ID) ?: ($owner[User::F_ID] ?? 0));
$owner_name   = ($owner[User::F_NAME_FULL] ?? '-- ' . __('(not set) | (не указан) | (не вказано)') . ' --');
$ppp_id       = $form_data_fn(Ppp::F_ID);
$templates = implode(
    ', ', 
    array_map(
        fn($t) => '{' . $t . '}', 
        Ppp::TEMPLATES
    )
);

?>
<div class="container-fluid d-flex justify-content-center">
    <div class="card col-12 col-sm-12 col-md-10 col-lg-8 col-xl-6">
        <div class="card-header">
            <h3 class="mb-3"><?=__('Edit payment point | Редактирование ППП | Редагування ППП');?></h3>
        </div>

        <form method="post" class="needs-validation" action="<?=Ppp::URI_EDIT;?>/<?=$ppp_id;?>" novalidate>
            <input type="hidden" name="<?=Ppp::POST_REC;?>[<?=Ppp::F_ID;?>]" value="<?=$ppp_id;?>">
        
            <div class="card-body">

                <!-- Название -->
                <div class="row mb-3">
                    <label for="title" class="col-sm-4 col-form-label"><?=__('Title | Название | Назва');?></label>
                    <div class="col-sm-8">
                        <input type="text"
                            class="form-control fs-4"
                            id="title"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_TITLE;?>]"
                            value="<?=$form_data_fn(Ppp::F_TITLE);?>"
                            required>
                        <div class="invalid-feedback"><?=__('Please enter title | Укажите название ППП | Вкажіть назву ППП');?></div>
                    </div>
                </div>

                <!-- Активность и показ -->
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="active" class="form-check-label"><?=__('Active | Активен | Активний');?></label>
                    </div>
                    <div class="col-sm-1 form-check form-switch">
                        <input type="checkbox"
                            class="form-check-input ms-1"
                            id="active"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_ACTIVE;?>]"
                            value="1"
                            <?=($form_data_fn(Ppp::F_ACTIVE)?'checked':'');?>>
                    </div>
                    <div class="col-sm-7 form-check form-switch">
                        <?=__('Used for making payments | Используется для внесения платежей | Використовується для внесення платежів');?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="abon_payments" class="form-check-label"><?=__('In abon list | В списке абонентов | У списку абонентів');?></label>
                    </div>
                    <div class="col-sm-1 form-check form-switch">
                        <input type="checkbox"
                            class="form-check-input ms-1"
                            id="abon_payments"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_ABON_PAYMENTS;?>]"
                            value="1"
                            <?=($form_data_fn(Ppp::F_ABON_PAYMENTS)?'checked':'');?>>
                    </div>
                    <div class="col-sm-7 form-check form-switch">
                        <?=__('It is displayed for subscribers in the list of payment methods | Отображается у абонентов в списке способов оплаты | Відображається у абонентів в списку способів оплати');?>
                    </div>
                </div>

                <!-- Предприятие -->
                <div class="row mb-3">
                    <label for="firm_id" class="col-sm-4 col-form-label"><?=__('Firm | Предприятие | Підприємство');?></label>
                    <div class="col-sm-8">
                        <select class="form-select" id="firm_id" name="<?=Ppp::POST_REC;?>[<?=Ppp::F_FIRM_ID;?>]" required>
                            <option value="">-- <?=__('Select... | Выберите... | Оберіть...');?> --</option>
                            <?php foreach ($firms as $firm): ?>
                                <option value="<?=$firm[Firm::F_ID];?>"
                                    <?=($form_data_fn(Ppp::F_FIRM_ID)==$firm[Firm::F_ID]?'selected':'');?>>
                                    <?=$firm[Firm::F_NAME_LONG];?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?=__('Select firm | Выберите предприятие | Оберіть підприємство');?></div>
                    </div>
                </div>

                <!-- Тип ППП -->
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label" for="type_id"><?=__('Type | Тип ППП | Тип ППП');?></label>
                    <div class="col-sm-8">
                        <select class="form-select" id="type_id" name="<?=Ppp::POST_REC;?>[<?=Ppp::F_TYPE_ID;?>]">
                            <?php foreach ($ppp_types as $type): ?>
                                <option value="<?=$type[PppType::F_ID];?>"
                                    <?=($form_data_fn(Ppp::F_TYPE_ID)==$type[PppType::F_ID]?'selected':'');?>>
                                    <?=$type[PppType::F_RU_TITLE];?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Владелец -->
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label" for="owner_id"><?=__('Owner | Владелец | Власник');?></label>
                    <div class="col-sm-2 d-flex align-items-center gap-2">
                        <input
                            type="number"
                            id="owner_id"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_OWNER_ID;?>]"
                            class="form-control"
                            value="<?=h(strval($owner_id_val));?>"
                            min="1"
                            inputmode="numeric"
                            pattern="\d*"
                            title="<?=__('Введите цифровой ID владельца');?>"
                        />
                    </div>
                    <div class="col-sm-6 d-flex align-items-center gap-2">
                        <div class="form-text mb-0">[<?=h($owner_name);?>]</div>
                    </div>
                </div>

                <!-- Реквизиты -->
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label" for="number_prefix"><?=__('Account description prefix | Префикс описания счёта | Префікс опису рахунку');?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="number_prefix"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_NUMBER_PREFIX;?>]"
                            value="<?=$form_data_fn(Ppp::F_NUMBER_PREFIX);?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label" for="number"><?=__('Account | Счёт | Рахунок');?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="number"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_NUMBER;?>]"
                            value="<?=$form_data_fn(Ppp::F_NUMBER);?>">
                    </div>
                </div>

                <!-- Назначение платежа -->
                <div class="row mb-3">
                    <label for="number_purpose" class="col-sm-4 col-form-label" title="Field: <?=Ppp::F_NUMBER_PURPOSE;?>">
                        <?=__('Purpose of payment | Назначение платежа | Призначення платежу');?></label>
                    <div class="col-sm-8">
                        <textarea
                            class="form-control"
                            id="number_purpose"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_NUMBER_PURPOSE;?>]"
                            rows="2"
                        ><?=h($form_data_fn(Ppp::F_NUMBER_PURPOSE));?></textarea>
                    </div>
                </div>

                <!-- Комиссии -->
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label"><?=__('Commissions | Комиссии | Комісії');?></label>
                    <div class="col-sm-8">
                        <?php $lang = Lang::code(); ?>
                        <?php foreach (Ppp::F_COMMISSIONS as $field => $cfg): ?>
                            <?php
                                $title  = $cfg['title'][$lang] ?? $cfg['title']['ru'] ?? reset($cfg['title']);
                                $suffix = $cfg['suffix'] ?? '';
                                $value  = $form_data_fn($field);
                            ?>
                            <div class="row mb-2 align-items-center">
                                <div class="col-sm-6">
                                    <?=h($title);?>
                                </div>
                                <div class="col-sm-3">
                                    <input type="number" step="0.01" lang="en"
                                        class="form-control text-end"
                                        name="<?=Ppp::POST_REC;?>[<?=$field;?>]"
                                        value="<?=h($value);?>">
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-text mb-0"><?=h($suffix);?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>




                <!-- Дополнительная информация -->
                <div class="row mb-3">
                    <label for="number_info" class="col-sm-4 col-form-label" title="Field: <?=Ppp::F_NUMBER_INFO;?>">
                        <?=__('Account info | Доп. данные счета | Дод. дані рахунку');?>
                    </label>
                    <div class="col-sm-8">
                        <textarea
                            class="form-control"
                            id="number_info"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_NUMBER_INFO;?>]"
                            rows="2"
                        ><?=h($form_data_fn(Ppp::F_NUMBER_INFO));?></textarea>
                    </div>
                </div>

                <!-- Комментарий -->
                <div class="row mb-3">
                    <label for="number_comment" class="col-sm-4 col-form-label" title="Field: <?=Ppp::F_NUMBER_COMMENT;?>">
                        <?=__('Comment | Комментарий | Коментар');?>
                    </label>
                    <div class="col-sm-8">
                        <textarea
                            class="form-control"
                            id="number_comment"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_NUMBER_COMMENT;?>]"
                            rows="2"
                        ><?=h($form_data_fn(Ppp::F_NUMBER_COMMENT));?></textarea>
                    </div>
                </div>

                <!-- СМС для абонента -->
                <div class="row mb-3">
                    <label for="sms_pay_info" class="col-sm-4 col-form-label" title="Field: <?=Ppp::F_SMS_PAY_INFO;?>">
                        <?=__('SMS text | Текст для СМС | Текст для СМС');?>
                    </label>
                    <div class="col-sm-8">
                        <textarea
                            class="form-control"
                            id="sms_pay_info"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_SMS_PAY_INFO;?>]"
                            rows="2"
                            placeholder="{PORT} {LOGIN} {SUM}"
                        ><?=h($form_data_fn(Ppp::F_SMS_PAY_INFO));?></textarea>
                        <div class="form-text"><?=__('May contain placeholders | Может содержать подстановки | Може містити підстановки | %s', $templates);?></div>
                    </div>
                </div>

                <!-- Телефоны поддержки -->
                <div class="row mb-3">
                    <label for="support_phones" class="col-sm-4 col-form-label" title="Field: <?=Ppp::F_SUPPORT_PHONES;?>">
                        <?=__('Support phones | Телефоны поддержки | Телефони підтримки');?>
                    </label>
                    <div class="col-sm-8">
                        <textarea
                            class="form-control"
                            id="support_phones"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_SUPPORT_PHONES;?>]"
                            rows="2"
                        ><?=h($form_data_fn(Ppp::F_SUPPORT_PHONES));?></textarea>
                    </div>
                </div>

                <!-- API параметры -->
                <hr>
                <h5 class="text-center mb-3"><?=__('API Settings | Настройки API | Налаштування API');?></h5>

                <div class="row mb-3">
                    <label for="api_type" class="col-sm-4 col-form-label"><?=__('API Type | Тип API | Тип API');?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                            id="api_type"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_TYPE;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_TYPE);?>">
                        <div class="form-text"><?=__('Supported APIs | Поддерживаемые API | Підтримувані API');?>: <?=implode(', ', API_TYPE_LIST);?></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="api_id" class="col-sm-4 col-form-label"><?=__('API ID | API ID | API ID');?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                            id="api_id"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_ID;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_ID);?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="api_pass" class="col-sm-4 col-form-label"><?=__('API Password | Пароль / токен | Пароль / токен');?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                            id="api_pass"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_PASS;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_PASS);?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="api_url" class="col-sm-4 col-form-label"><?=__('API URL | API URL | API URL');?></label>
                    <div class="col-sm-8">
                        <input type="url" class="form-control"
                            id="api_url"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_URL;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_URL);?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="api_auto_pay_registration" class="form-check-label"><?=__('Auto register payments | Авто-регистрация платежей | Авто-реєстрація платежів');?></label>
                    </div>
                    <div class="col-sm-2 form-check form-switch">
                        <input type="checkbox"
                            class="form-check-input ms-1"
                            id="api_auto_pay_registration"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_AUTO_PAY_REG;?>]"
                            value="1"
                            <?=($form_data_fn(Ppp::F_API_AUTO_PAY_REG)?'checked':'');?>>
                    </div>
                    <div class="col-sm-6 form-text">
                        <?=__('Automatically register identified payments | Автоматически регистрировать распознанные платежи | Автоматично реєструвати розпізнані платежі');?>
                    </div>
                </div>

                <!-- LiqPay -->
                <div class="row mb-3">
                    <label for="api_liqpay_public" class="col-sm-4 col-form-label">LiqPay Public Key</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                            id="api_liqpay_public"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_LIQPAY_PUBLIC;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_LIQPAY_PUBLIC);?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="api_liqpay_private" class="col-sm-4 col-form-label">LiqPay Private Key</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                            id="api_liqpay_private"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_LIQPAY_PRIVATE;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_LIQPAY_PRIVATE);?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="api_liqpay_url" class="col-sm-4 col-form-label">LiqPay URL</label>
                    <div class="col-sm-8">
                        <input type="url" class="form-control"
                            id="api_liqpay_url"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_LIQPAY_URL;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_LIQPAY_URL);?>">
                    </div>
                </div>

                <!-- Приват24 -->
                <div class="row mb-3">
                    <label for="api_24pay_ident" class="col-sm-4 col-form-label">p24pay ID</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control"
                            id="api_24pay_ident"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_24PAY_IDENT;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_24PAY_IDENT);?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="api_24pay_url" class="col-sm-4 col-form-label">p24pay URL</label>
                    <div class="col-sm-8">
                        <input type="url" class="form-control"
                            id="api_24pay_url"
                            name="<?=Ppp::POST_REC;?>[<?=Ppp::F_API_24PAY_URL;?>]"
                            value="<?=$form_data_fn(Ppp::F_API_24PAY_URL);?>">
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <!-- Кнопки -->
                <div class="d-flex justify-content-center">
                        <a href="<?=Ppp::URI_INDEX;?>" class="btn btn-secondary w-25 me-2"><i class="bi bi-cancel"></i> <?=__('Cancel | Отмена | Скасувати');?></a>
                        <button type="submit" class="btn btn-primary w-50"><i class="bi bi-save"></i> <?=__('Save | Сохранить | Зберегти');?></button>
                </div>
            </div>
        </form>        
    </div>
</div>

<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>