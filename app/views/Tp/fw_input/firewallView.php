<?php
/**
 *  Project : my.ri.net.ua
 *  File    : firewallView.php
 *  Path    : app/views/Tp/fw_input/firewallView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:13:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of firewallView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\FwInput;
use config\tables\TP;

$currentRules = $data['current_rules'] ?? [];
$proposedRules = $data['proposed_rules'] ?? [];
$allowedTcpExtra = $data['allowed_tcp_extra'] ?? '';
$allowedUdpExtra = $data['allowed_udp_extra'] ?? '';
$hasRequiredLists = !empty($data['has_required_lists']);
$meta = $data['meta'] ?? [];



/**
 * Подключение файла-заголовка 
 */
$page_title = __('Final: Setting up Firewall Input chain protection | Финал: Настройка защиты цепочки Firewall Input | Фінал: Налаштування захисту ланцюжка Firewall Input');
$device_title = $data['title'] ?? '';
$device_description = $data['description'] ?? '';
include __DIR__ . '/header.php';

//debug($currentRules, '$currentRules');

?>
<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_FILTERS ?>">
    <input type="hidden" name="fwf_action" value="delete">
    <?php if (!$hasRequiredLists): ?>
        <div class="alert alert-danger">
            <?= __('LAN/WAN lists were not found. Return to the interface list step. | Списки LAN/WAN не найдены. Вернитесь к шагу списков интерфейсов. | Списки LAN/WAN не знайдені. Поверніться до кроку списків інтерфейсів.') ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="alert alert-info">
            <?= __('If you consider the setup sufficient, you can close this page or return to the connection form to configure another device. | Если вы считаете настройку достаточной, можете закрыть страницу или вернуться к форме подключения для настройки другого устройства. | Якщо ви вважаєте налаштування достатнім, можете закрити сторінку або повернутися до форми підключення для налаштування іншого пристрою.') ?>
        </div>
    </div>

    <div class="mb-3">
        <h5><?= __('Current input rules | Текущие input-правила | Поточні input-правила') ?></h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th class="text-center"><?= __('Delete | Удалить | Видалити') ?></th>
                        <th class="text-center">.id</th>
                        <th class="text-center"><?= __('Ena/Dis | Вкл/Выкл | Вкл/Вим') ?></th>
                        <th class="text-center"><?= __('Action | Действие | Дія') ?></th>
                        <th class="text-center"><?= __('Protocol | Протокол | Протокол') ?></th>
                        <th class="text-center"><?= __('Ports | Порты | Порти') ?></th>
                        <th class="text-center"><?= __('Comment | Комментарий | Коментар') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentRules as $idx => $rule): ?>
                        <?php
                            $warn =  str_starts_with(($rule['comment'] ?? ''), 'FW 06') || str_starts_with(($rule['comment'] ?? ''), 'FW 07');
                            $ena = !mikBool($rule['disabled'] ?? '');
                            $td_attr = ($warn ? 'bg-warning-subtle' : '') . ' ' . ($ena ? '' : 'text-secondary');
                        ?>
                        <tr>
                            <td class="text-center <?= $td_attr ?>">
                                <input type="hidden" name="fwf[delete][<?= $idx ?>][.id]" value="<?= h((string) ($rule['.id'] ?? '')) ?>">
                                <div class="text-center">
                                    <span class="btn btn-outline-secondary d-inline-flex align-items-center fs-6 py-1 px-3">
                                        <label class="hover-pointer mb-0"
                                            for="delete<?= $idx ?>"><?= __('Del | Удалить | Видалити') ?>
                                        </label>
                                        <input class="form-check-input hover-pointer ms-2 m-0" 
                                            type="checkbox"
                                            id="delete<?= $idx ?>" 
                                            name="fwf[delete][<?= $idx ?>][checked]" 
                                            value="1">
                                    </span>
                                </div>
                            </td>
                            <td class="<?= $td_attr ?>"><?= h((string) ($rule['.id'] ?? '')) ?></td>
                            <td class="text-center <?= $td_attr ?>"><?= $ena ? 'Вкл.' : 'Выкл.' ?></td>
                            <td class="<?= $td_attr ?>"><?= h((string) ($rule['action'] ?? '')) ?></td>
                            <td class="<?= $td_attr ?>"><?= h((string) ($rule['protocol'] ?? '')) ?></td>
                            <td class="<?= $td_attr ?>"><?= h((string) ($rule['dst-port'] ?? '')) ?></td>
                            <td class="<?= $td_attr ?>"><?= h((string) ($rule['comment'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><?= __('Delete marked | Удалить отмеченные | Видалити позначені') ?></button>
    </div>
    
</form>

<form method="post" action="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_FILTERS ?>" class="mt-4">
    <input type="hidden" name="fwf_action" value="add">

    <div class="mb-3">
        <h5><?= __('Additional allowed ports | Дополнительные разрешённые порты | Додаткові дозволені порти') ?></h5>
        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Allowed TCP extra | Дополнительные TCP | Додаткові TCP') ?></label>
            <div class="col-9">
                <input type="text" class="form-control" name="fwf[allowed_tcp_extra]" value="<?= h($allowedTcpExtra) ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-3 col-form-label"><?= __('Allowed UDP extra | Дополнительные UDP | Додаткові UDP') ?></label>
            <div class="col-9">
                <input type="text" class="form-control" name="fwf[allowed_udp_extra]" value="<?= h($allowedUdpExtra) ?>">
            </div>
        </div>
        <div class="small text-secondary">
            TCP: <?= h(implode(',', $meta['tcp_ports'] ?? [])) ?><br>
            UDP: <?= h(implode(',', $meta['udp_ports'] ?? [])) ?>
        </div>
    </div>

    <div class="mb-3">
        <h5><?= __('Proposed rules | Предлагаемые правила | Запропоновані правила') ?></h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th><?= __('Add | Добавить | Додати') ?></th>
                        <th><?= __('Code | Код | Код') ?></th>
                        <th><?= __('Rule | Правило | Правило') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proposedRules as $code => $rule): ?>
                        <?php
                        $exists = false;
                        foreach ($currentRules as $currentRule) {
                            $same =
                                (($currentRule['chain'] ?? '') === ($rule['chain'] ?? '')) &&
                                (($currentRule['action'] ?? '') === ($rule['action'] ?? '')) &&
                                (($currentRule['protocol'] ?? '') === ($rule['protocol'] ?? '')) &&
                                (($currentRule['dst-port'] ?? '') === ($rule['dst-port'] ?? '')) &&
                                (($currentRule['connection-state'] ?? '') === ($rule['connection-state'] ?? '')) &&
                                (($currentRule['in-interface-list'] ?? '') === ($rule['in-interface-list'] ?? '')) &&
                                (($currentRule['src-address-list'] ?? '') === ($rule['src-address-list'] ?? '')) &&
                                (($currentRule['address-list'] ?? '') === ($rule['address-list'] ?? '')) &&
                                (($currentRule['address-list-timeout'] ?? '') === ($rule['address-list-timeout'] ?? '')) &&
                                (($currentRule['disabled'] ?? 'false') === 'false');
                            if ($same) {
                                $exists = true;
                                break;
                            }
                        }
                        ?>
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input" type="checkbox" name="fwf[add][<?= h($code) ?>]" value="1" <?= $exists ? '' : 'checked' ?>>
                            </td>
                            <td><?= h($code) ?></td>
                            <td class="font-monospace small"><?= h(json_encode($rule, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><?= __('Add selected rules | Добавить выбранные правила | Додати вибрані правила') ?></button>
    </div>
    
    <hr>
    
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::prev(FwInput::PHASE_FILTERS) ?>">
                <?= __('Back | Назад | Назад') ?>
            </a>
            <a class="btn btn-outline-secondary" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_FILTERS ?>">
                <?= __('Reread | Перечитать | Перечитати') ?>
            </a>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-success" href="<?= TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_LOGIN ?>">
                <?= __('To connect form | На форму подключения | До форми підключення') ?>
            </a>
        </div>
    </div>
    
</form>