<?php
/** app/views/Payments/listView.php */
use billing\core\Pagination;
use config\tables\Abon;
use config\tables\Module;
use config\tables\User;
use config\tables\Pay;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/** @var array $user */
/** @var Pagination $pager */
/** @var array $payments */
/** @var array $pay_one — запись из таблицы payments */

$view_all = can_view([Module::MOD_PAYMENTS]);
$view_my = can_view(Module::MOD_MY_PAYMENTS) && $user[User::F_ID] == $user[Abon::REC][Abon::F_USER_ID];

?>
<?php if ($view_all || $view_my) : ?>
<div class="mx-auto w-auto">

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="fs-6 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> ::</span> <?=$user[User::F_NAME_FULL];?>:</h3>
        </div>
        <div class="card-body">
            <span class="text text-secondary"><?=$user[Abon::REC][Abon::F_ID];?> ::</span> <?=$user[Abon::REC][Abon::F_ADDRESS];?>
        </div>
    </div>

    <?=$pager;?>
    <table class="table table-bordered table-striped table-hover align-middle min-w-75 w-auto mx-auto">
        <thead>
            <tr>
                <th class="text-center align-middle"><?=__('Pay Date');?></th>
                <th class="text-center align-middle" title="<?=__('Actual received amount');?>"><?=__('Pay Fakt');?></th>
                <?php if ($view_all) : ?>
                <th class="text-center align-middle" title="<?=__('Amount credited to account');?>"><?=__('on ACC');?></th>
                <th class="text-center align-middle"><?=__('Description');?></th>
                <th class="text-center align-middle"><?=__('Bank No');?></th>
                <th class="text-center align-middle"><?=__('Service info about payment');?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $pay_one) : ?>
            <tr>
                <td class="text-center align-middle"><?=date('Y-m-d H:i:s', $pay_one[Pay::F_DATE])?></td>
                <td class="text-end align-middle"><?=number_format($pay_one[Pay::F_PAY_FAKT], 2, '.', ' ')?></td>
                <?php if ($view_all) : ?>
                <td class="text-end align-middle"><?=number_format($pay_one[Pay::F_PAY_ACNT], 2, '.', ' ')?></td>
                <td class="text-start align-middle"><?=nl2br(cleaner_html($pay_one[Pay::F_DESCRIPTION]))?></td>
                <td class="text-start align-middle"><?=h($pay_one[Pay::F_BANK_NO])?></td>
                <td class="text-start align-middle text-secondary small">
                    <span title="<?=__('Payment ID in DB');?>">ID: <?=h($pay_one[Pay::F_ID])?></span><br>
                    <span title="<?=__('Who entered payment into DB');?>"><?=h($pay_one[Pay::F_AGENT_ID])?> : <?=h($pay_one[Pay::F_AGENT_TITLE])?></span><br>
                    <span title="<?=__('Payment type');?>"><?=h($pay_one[Pay::F_TYPE_ID])?> : <?=h($pay_one[Pay::F_TYPE_TITLE])?></span><br>
                    <span title="<?=__('PAP through which payment came');?>"><?=h($pay_one[Pay::F_PPP_ID])?> : <?=h($pay_one[Pay::F_PPP_TITLE])?></span><br>
                    <span title="<?=__('Who and when modified record');?>"><?=date('Y-m-d H:i:s', $pay_one[Pay::F_MODIFIED_DATE])?> : <?=h($pay_one[Pay::F_MODIFIED_UID])?></span>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?=$pager;?>
</div>
<?php endif; ?>
