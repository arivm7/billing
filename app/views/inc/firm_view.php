<?php
use config\tables\Firm;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
?>

<div class="container mt-4">
    <h2 class="mb-4">Просмотр предприятия</h2>

    <!-- Навигация по вкладкам -->
    <ul class="nav nav-tabs" id="firmTab<?=$item[Firm::F_ID];?>" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info<?=$item[Firm::F_ID];?>" type="button">Инфо</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bank<?=$item[Firm::F_ID];?>" type="button">Банк</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-office<?=$item[Firm::F_ID];?>" type="button">Офис</button></li>
        <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-status<?=$item[Firm::F_ID];?>" type="button">Статус</button></li>
        <?php endif; ?>
    </ul>

    <!-- Контент вкладок -->
    <div class="tab-content border border-top-0 p-3" id="firmTabContent<?=$item[Firm::F_ID];?>">

        <!-- Инфо -->
        <div class="tab-pane fade" id="tab-info<?=$item[Firm::F_ID];?>" role="tabpanel"> <!-- show active -->
            <table class="table table-bordered table-striped table-hover table-sm w-100">
                <?php
                $infoFields = [
                    Firm::F_NAME_SHORT => 'Краткое название предприятия',
                    Firm::F_NAME_LONG => 'Полное название предприятия',
                    Firm::F_NAME_TITLE => 'Публичное название | ТМ',
                    Firm::F_MANAGER_JOB_TITLE => 'Должность руководителя',
                    Firm::F_MANAGER_NAME_SHORT => 'Краткое ФИО руководителя',
                    Firm::F_MANAGER_NAME_LONG => 'Полное ФИО руководителя',
                    Firm::F_OFFICE_PHONES => 'Контактные телефоны',
                ];
                foreach ($infoFields as $field => $label) {
                    $val = cleaner_html($item[$field] ?? '');
                    echo "<tr><th class='text-nowrap text-right w-25 ps-3 pe-3'>{$label}</th><td class='text-left ps-3 pe-3'>{$val}</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Банк -->
        <div class="tab-pane fade" id="tab-bank<?=$item[Firm::F_ID];?>" role="tabpanel">
            <table class="table table-bordered table-striped table-hover table-sm">
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
                    $val = nl2br(cleaner_html($item[$field] ?? ''));
                    echo "<tr><th class='text-nowrap w-25 ps-3 pe-3'>{$label}</th><td class='text-left ps-3 pe-3'>{$val}</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Офис -->
        <div class="tab-pane fade" id="tab-office<?=$item[Firm::F_ID];?>" role="tabpanel">
            <table class="table table-bordered table-striped table-hover table-sm">
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
                    $val = cleaner_html($item[$field] ?? '');
                    echo "<tr><th class='text-nowrap w-25 ps-3 pe-3'>{$label}</th><td class='text-left ps-3 pe-3'>{$val}</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Статус -->
        <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
        <div class="tab-pane fade" id="tab-status<?=$item[Firm::F_ID];?>" role="tabpanel">
            <table class="table table-bordered table-striped table-hover table-sm">
                <?php
                $statuses = get_firm_status(firm: $item);
                $checkboxes = [
                    Firm::F_HAS_DELETE      => ($item[Firm::F_HAS_DELETE] ? 'Помечено как удалённое, не участвует в обслуживании' : "Является контрагентом, работает, обслуживается" ),
                    Firm::F_HAS_ACTIVE      => ($item[Firm::F_HAS_ACTIVE] ? 'Активное предприятие, явлается контрагеном' : "Предприятие временно отключено") ,
                    Firm::F_HAS_AGENT       => ($item[Firm::F_HAS_AGENT] ? 'Провайдер' : "Не провайдер" ),
                    Firm::F_HAS_CLIENT      => ($item[Firm::F_HAS_CLIENT] ? 'Абоннет' : "Не абонент" ),
                    Firm::F_HAS_ALL_VISIBLE => ($item[Firm::F_HAS_ALL_VISIBLE] ? 'Видимое для всех при подключении' : "Видимое только пользователю-владелцу" ),
                    Firm::F_HAS_ALL_LINKING => ($item[Firm::F_HAS_ALL_LINKING] ? 'Подключаемое всеми' : "Может подключаться только к пользователю владельцу" ),
                ];
                foreach ($checkboxes as $field => $label) {
//                    $val = !empty($item[$field]) ? '✅ Да' : '❌ Нет';
                    echo "<tr><th width=10% class='text-nowrap text-center ps-3 pe-3'>{$statuses[$field]}</th><td class='text-left ps-3 pe-3'>{$label}</td></tr>";
                }

//                // ППП
//                echo "<tr><th>ППП по умолчанию</th><td>".(int)($item[Firm::F_PPP_DEFAULT_ID] ?? 0)."</td></tr>";

//                // Статическая информация
//                $readonlyFields = [
//                    Firm::F_CREATION_UID => 'Кем создано (UID)',
//                    Firm::F_CREATION_DATE => 'Дата создания',
//                    Firm::F_MODIFIED_UID => 'Кем изменено (UID)',
//                    Firm::F_MODIFIED_DATE => 'Дата изменения',
//                ];
//                foreach ($readonlyFields as $field => $label) {
//                    $val = htmlspecialchars($item[$field] ?? '');
//                    echo "<tr><th>{$label}</th><td>{$val}</td></tr>";
//                }
                ?>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Кнопка возврата -->
    <div class="mt-4">
        <a href="?" class="btn btn-secondary">Назад</a>
    </div>
</div>
