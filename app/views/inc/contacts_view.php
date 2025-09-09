<!--contacts_view.php-->
<?php
use config\tables\Module;
use config\tables\Contacts;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $user */
/** @var array $contacts Список контактов */
$contacts = $user[Contacts::TABLE] ?? null;
?>
<div class="container-fluid p-0">
    <!-- <h2 class="text">Дополнительные контакты</h2>-->
    <br>
    <?php if (empty($contacts)): ?>
        <div class="alert alert-info"><?=__('There is no contact list to view');?>.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped table-hover ">
            <thead>
                <tr>
                    <th><?=__('Title');?></th>
                    <th><?=__('Contact');?></th>
                    <th width="10%"><?=__('Type');?></th>
                    <th width="100px" nowrap><span class="text-secondary fs-7"><?=__('Date of change');?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                    <?php if (!$c[Contacts::F_IS_HIDDEN]): ?>
                    <tr>
                        <td><?= h($c[Contacts::F_TITLE]) ?></td>
                        <td><?= h($c[Contacts::F_VALUE]) ?></td>
                        <td><?= Contacts::TYPES[$c[Contacts::F_TYPE_ID]] ?></td>
                        <td width="100px" nowrap>
                            <span class="text-secondary fs-7">
                                <?= $c[Contacts::F_CREATION_DATE]
                                    ? date('d.m.Y H:i', $c[Contacts::F_CREATION_DATE])
                                    : '-' ?><br>
                                <?= $c[Contacts::F_MODIFIED_DATE]
                                    ? date('d.m.Y H:i', $c[Contacts::F_MODIFIED_DATE])
                                    : '-' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

