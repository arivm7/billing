<?php
use config\tables\Firm;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
?>

<div class="container mt-4">
    <h2 class="mb-4"><?=__('Viewing company data');?></h2>

    <!-- Навигация по вкладкам -->
    <ul class="nav nav-tabs" id="firmTab<?=$item[Firm::F_ID];?>" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info<?=$item[Firm::F_ID];?>" type="button"><?=__('Info');?></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bank<?=$item[Firm::F_ID];?>" type="button"><?=__('Bank');?></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-office<?=$item[Firm::F_ID];?>" type="button"><?=__('Office');?></button></li>
        <?php if (can_edit(Module::MOD_FIRM_STATUS)) : ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-status<?=$item[Firm::F_ID];?>" type="button"><?=__('Status');?></button></li>
        <?php endif; ?>
    </ul>

    <!-- Контент вкладок -->
    <div class="tab-content border border-top-0 p-3" id="firmTabContent<?=$item[Firm::F_ID];?>">

        <!-- Инфо -->
        <div class="tab-pane fade show active" id="tab-info<?=$item[Firm::F_ID];?>" role="tabpanel"> <!-- show active -->
            <table class="table table-bordered table-striped table-hover table-sm w-100">
                <?php
                $infoFields = [
                    Firm::F_NAME_SHORT          => __('Short name of the enterprise'),
                    Firm::F_NAME_LONG           => __('Full name of the company'),
                    Firm::F_NAME_TITLE          => __('Public name, TM'),
                    Firm::F_MANAGER_JOB_TITLE   => __('Position of manager'),
                    Firm::F_MANAGER_NAME_SHORT  => __('Short full name of the manager'),
                    Firm::F_MANAGER_NAME_LONG   => __('Full name of the manager'),
                    Firm::F_OFFICE_PHONES       => __('Contact numbers'),
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
                    Firm::F_COD_EDRPOU           => __('EDRPOU'),
                    Firm::F_COD_IPN              => __('TIN'),
                    Firm::F_REGISTRATION         => __('Registration'),
                    Firm::F_ADDRESS_REGISTRATION => __('Registration address'),
                    Firm::F_BANK_IBAN            => 'IBAN',
                    Firm::F_BANK_NAME            => __('Bank name'),
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
                    Firm::F_ADDRESS_OFFICE_FULL     => __('Office address'),
                    Firm::F_ADDRESS_POST_PERSON     => __('Sender (post)'),
                    Firm::F_ADDRESS_POST_INDEX      => __('Postal code'),
                    Firm::F_ADDRESS_POST_UL         => __('Street'),
                    Firm::F_ADDRESS_POST_DOM        => __('Building / Block / Apt.'),
                    Firm::F_ADDRESS_POST_SITY       => __('City'),
                    Firm::F_ADDRESS_POST_REGION     => __('Region'),
                    Firm::F_ADDRESS_POST_COUNTRY    => __('Country'),
                    Firm::F_ADDRESS_OFFICE_COURIER  => __('Courier address'),

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
                    Firm::F_HAS_DELETE      => ($item[Firm::F_HAS_DELETE]
                            ? __('Marked as deleted, not participating in service')
                            : __('Active counterparty, works, serviced')),
                    Firm::F_HAS_ACTIVE      => ($item[Firm::F_HAS_ACTIVE]
                            ? __('Active enterprise, is a counterparty')
                            : __('Enterprise temporarily disabled')),
                    Firm::F_HAS_AGENT       => ($item[Firm::F_HAS_AGENT]
                            ? __('Provider')
                            : __('Not a provider')),
                    Firm::F_HAS_CLIENT      => ($item[Firm::F_HAS_CLIENT]
                            ? __('Subscriber')
                            : __('Not a subscriber')),
                    Firm::F_HAS_ALL_VISIBLE => ($item[Firm::F_HAS_ALL_VISIBLE]
                            ? __('Visible to all upon connection')
                            : __('Visible only to owner user')),
                    Firm::F_HAS_ALL_LINKING => ($item[Firm::F_HAS_ALL_LINKING]
                            ? __('Connectable by all')
                            : __('Can only be connected to owner user')),
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
