<?php
/**
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Ppp/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Oct 2025 22:13:03
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use config\tables\Module;
use config\tables\Ppp;

/** @var array $ppp_list -- массив всех ППП */
?>
<div class="container-fluid mt-4">
    <div class="card mx-auto w-auto w-75">
        <div class="card-header">
            <h4><?=__('Payment Points | Пункты приёма платежей | Пункти прийому платежів');?></h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="<?=Ppp::URI_EDIT;?>" class="btn btn-info btn-sm"><?=__('Добавить');?></a>
                <?php if (isset($_GET['active']) || isset($_GET['type_id']) || isset($_GET['abon_payments'])): ?>
                    <a href="<?=Ppp::URI_INDEX;?>" class="btn btn-info btn-sm"><?=__('Показать все');?></a>
                <?php else: ?>
                    <a href="<?=Ppp::URI_INDEX;?>?active=1" class="btn btn-info btn-sm"><?=__('Показать активные');?></a>
                <?php endif; ?>
            </div>
            <table class="table table-hover table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th><?=__('ID');?></th>
                        <th><?=__('Firm');?></th>
                        <th><?=__('Title');?></th>
                        <th><?=__('Type');?></th>
                        <th><?=__('Active');?></th>
                        <th><?=__('Show to abonents');?></th>
                        <th><?=__('Actions');?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($ppp_list as $item): ?>
                    <tr>
                        <td><?= h($item[Ppp::F_ID]); ?></td>
                        <td><?= h($item[Ppp::F_FIRM_ID]); ?></td>
                        <td><?= h($item[Ppp::F_TITLE]); ?></td>
                        <td><?= h($item[Ppp::F_TYPE_ID]); ?></td>
                        <td class="text-center"><?= $item[Ppp::F_ACTIVE] ? '✔' : '✖'; ?></td>
                        <td class="text-center"><?= $item[Ppp::F_ABON_PAYMENTS] ? '✔' : '✖'; ?></td>
                        <td class="text-nowrap">
                            <?php if (can_edit(Module::MOD_PPP)): ?>
                                <a href="<?=Ppp::URI_EDIT;?>/<?= $item[Ppp::F_ID]; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil-square"></i> <?=__('Edit');?></a>
                            <?php endif; ?>
                            <?php if (can_del(Module::MOD_PPP)): ?>
                                <?php if ($item[Ppp::F_OWNER_ID] == App::get_user_id()): ?>
                                    <a href="<?=Ppp::URI_DELETE;?>/<?= $item[Ppp::F_ID]; ?>" class="btn btn-sm btn-outline-danger me-1" onclick="return confirm('<?=__('Are you sure?');?>');" title="<?= __('Delete') . CR . __('Удаление записи из базы'); ?>"><i class="bi bi-x-circle"></i></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>  
    </div>
</div>