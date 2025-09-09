<?php
use config\tables\Firm;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
$postRec = Firm::POST_REC;
?>

<div class="container mt-4">
    <h2 class="mb-4">Редактирование предприятия</h2>
    <form method="post">
        <input type="hidden" name="<?= Firm::POST_REC ?>[<?= Firm::F_ID ?>]" value="<?= (int)($item[Firm::F_ID] ?? 0) ?>">

        <!-- Навигация по вкладкам -->
        <ul class="nav nav-tabs" id="firmTab<?=$item[Firm::F_ID];?>" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-info<?=$item[Firm::F_ID];?>" type="button" role="tab">Инфо</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-bank<?=$item[Firm::F_ID];?>" type="button" role="tab">Банк</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="office-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-office<?=$item[Firm::F_ID];?>" type="button" role="tab">Офис</button>
            </li>
            <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#tab-edit-status<?=$item[Firm::F_ID];?>" type="button" role="tab">Статус</button>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Контент вкладок -->
        <div class="tab-content border border-top-0 p-3" id="firmTabContent<?=$item[Firm::F_ID];?>">

            <!-- Вкладка Инфо -->
            <div class="tab-pane fade show active" id="tab-edit-info<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $infoFields = [
                        Firm::F_NAME_SHORT => 'Краткое название',
                        Firm::F_NAME_LONG => 'Полное название',
                        Firm::F_NAME_TITLE => 'Публичное название предприятия',
                        Firm::F_MANAGER_JOB_TITLE => 'Должность ответственного',
                        Firm::F_MANAGER_NAME_SHORT => 'ФИО ответственного',
                        Firm::F_MANAGER_NAME_LONG => 'Фамилия Имя Отчество',
                        Firm::F_OFFICE_PHONES => 'Контактные телефоны',
                    ];
                    foreach ($infoFields as $field => $label) {
                        $val = h($item[$field] ?? '');
                        echo <<<HTML
<div class="col-md-6">
    <label class="form-label">{$label}</label>
    <input type="text" class="form-control" name="{$postRec}[{$field}]" value="{$val}">
</div>
HTML;
                    }
                    ?>
                </div>
            </div>

            <!-- Вкладка Банк -->
            <div class="tab-pane fade" id="tab-edit-bank<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $bankFields = [
                        Firm::F_COD_EDRPOU => 'ЕДРПОУ',
                        Firm::F_COD_IPN => 'ИПН',
                        Firm::F_REGISTRATION => 'Регистрация',
                        Firm::F_ADDRESS_REGISTRATION => 'Адрес регистрации',
                        Firm::F_BANK_IBAN => 'IBAN',
                        Firm::F_BANK_NAME => 'Название банка',
                    ];
                    foreach ($bankFields as $field => $label) {
                        $val = h($item[$field] ?? '');
                        $type = $field === Firm::F_REGISTRATION || $field === Firm::F_ADDRESS_REGISTRATION ? 'textarea' : 'input';
                        if ($type === 'textarea') {
                            echo <<<HTML
<div class="col-md-12">
    <label class="form-label w-100">{$label}
    <textarea class="form-control w-100" name="{$postRec}[{$field}]" rows="2">{$val}</textarea></label>
</div>
HTML;
                        } else {
                            echo <<<HTML
<div class="col-md-6">
    <label class="form-label">{$label}
    <input type="text" class="form-control" name="{$postRec}[{$field}]" value="{$val}"></label>
</div>
HTML;
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Вкладка Офис -->
            <div class="tab-pane fade" id="tab-edit-office<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $officeFields = [
                        Firm::F_ADDRESS_OFFICE_FULL => 'Адрес офиса',
                        Firm::F_ADDRESS_POST_PERSON => 'От кого (почта)',
                        Firm::F_ADDRESS_POST_INDEX => 'Индекс',
                        Firm::F_ADDRESS_POST_UL => 'Улица',
                        Firm::F_ADDRESS_POST_DOM => 'Дом, корп., стр., кв.',
                        Firm::F_ADDRESS_POST_SITY => 'Город',
                        Firm::F_ADDRESS_POST_REGION => 'Область',
                        Firm::F_ADDRESS_POST_COUNTRY => 'Страна',
                        Firm::F_ADDRESS_OFFICE_COURIER => 'Адрес для курьеров',
                    ];
                    foreach ($officeFields as $field => $label) {
                        $val = htmlspecialchars($item[$field] ?? '');
                        echo <<<HTML
<div class="col-md-6">
    <label class="form-label">{$label}</label>
    <input type="text" class="form-control" name="{$postRec}[{$field}]" value="{$val}">
</div>
HTML;
                    }
                    ?>
                </div>
            </div>

            <!-- Вкладка Статус -->
            <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
            <div class="tab-pane fade" id="tab-edit-status<?=$item[Firm::F_ID];?>" role="tabpanel">
                <div class="row g-3">
                    <?php
                    $checkboxes = [
                        Firm::F_HAS_ACTIVE => 'Активное предприятие',
                        Firm::F_HAS_DELETE => 'Помечено как удалённое',
                        Firm::F_HAS_AGENT => 'Предприятие-агент (наше)',
                        Firm::F_HAS_CLIENT => 'Предприятие-клиент',
                        Firm::F_HAS_ALL_VISIBLE => 'Видимое для всех',
                        Firm::F_HAS_ALL_LINKING => 'Подключаемое всеми',
                    ];

                    foreach ($checkboxes as $field => $label) {
                        $checked = !empty($item[$field]) ? 'checked' : '';
                        echo <<<HTML
<div class="col-md-4">
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name='{$postRec}[{$field}]' value="1" {$checked}>
        <label class="form-check-label">{$label}</label>
    </div>
</div>
HTML;
                    }

//                    // Селектор ППП
//                    echo <<<HTML
//<div class="col-md-6">
//    <label class="form-label">ППП по умолчанию</label>
//    <input type="number" class="form-control" name="{$postRec}[ppp_default_id]" value="{$item[Firm::F_PPP_DEFAULT_ID]}">
//</div>
//HTML;

                    // Статическая информация
                    $readonlyFields = [
                        Firm::F_CREATION_UID => 'Кем создано (UID)',
                        Firm::F_CREATION_DATE => 'Дата создания',
                        Firm::F_MODIFIED_UID => 'Кем изменено (UID)',
                        Firm::F_MODIFIED_DATE => 'Дата изменения',
                    ];
                    echo "<table class='table border w-50'>";
                    foreach ($readonlyFields as $field => $label) {
                        $val = h($item[$field] ?? '');
                        echo "<tr><th>{$label}</th><td>{$val}</td></tr>";
                    }
                    echo "</table>";
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Кнопки -->
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
</div>
