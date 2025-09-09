<!--contacts_edit.php-->
<?php
use config\tables\Contacts;
use config\tables\Module;
use config\tables\User;
use config\Icons;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/** @var array $user */

$contacts = $user[Contacts::TABLE] ?? [];
$can_view = can_view([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS]);
$can_edit = can_edit([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS]);
$can_add  = can_add([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS]);
$can_del  = can_del([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS]);
?>
<div class="container-fluid p-0">
    <!-- <h2 class="text">Дополнительные контакты</h2>-->
    <br>
    <table class="table table-bordered table-striped align-middle table-hover">
        <thead>
            <tr>
                <th><?=__('Title');?></th>
                <th><?=__('Contact');?></th>
                <?php if ($can_del) : ?>
                <th align="center" width="50px" nowrap title="<?=__('Mark for deletion');?>">[X]</th>
                <?php endif; ?>
                <th width="10%"><?=__('Contact Type');?></th>
                <th width="100px"><?=__('Actions');?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($can_add) : ?>
                <form method="post" action="<?=Contacts::URI_ADD;?>">
                    <tr>
                        <input type="hidden" name="<?=Contacts::POST_REC;?>[<?=Contacts::F_USER_ID;?>]" value="<?=h($user[User::F_ID]);?>">
                        <td>
                            <input type="text" name="<?=Contacts::POST_REC;?>[<?=Contacts::F_TITLE;?>]" class="form-control form-control-sm text-end"
                                   value="">
                        </td>
                        <td>
                            <input type="text" name="<?=Contacts::POST_REC;?>[<?=Contacts::F_VALUE;?>]" class="form-control form-control-sm"
                                   value="" required>
                        </td>
                        <?php if ($can_del) : ?>
                        <td></td>
                        <?php endif; ?>
                        <td>
                            <?=make_html_select(
                                    data: Contacts::TYPES,
                                    name: Contacts::POST_REC . '[' . Contacts::F_TYPE_ID . ']',
                                    selected_id: Contacts::T_AUTO,
                                    select_opt:  "class='form-select form-select-sm'"
                            );?>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-primary btn-sm">[+]</button>
                        </td>
                    </tr>
                </form>
            <?php endif; ?>

            <?php if ($can_edit || $can_del) : ?>
                <?php foreach ($contacts as $record): ?>
                    <form method="post" action="<?=Contacts::URI_EDIT . '/' . h($record[Contacts::F_ID]);?>" onsubmit="return confirm(__('Confirm editing of contact')));">
                        <tr>
                            <td>
                                <?php if ($can_edit || $can_del) : ?>
                                <input type="text" name="<?=Contacts::POST_REC;?>[<?=Contacts::F_TITLE;?>]" class="form-control form-control-sm text-end"
                                       value="<?= h($record[Contacts::F_TITLE]) ?>">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($can_edit || $can_del) : ?>
                                <input type="text" name="<?=Contacts::POST_REC;?>[<?=Contacts::F_VALUE;?>]" class="form-control form-control-sm"
                                       value="<?= h($record[Contacts::F_VALUE]) ?>">
                                <?php endif; ?>
                            </td>
                            <?php if ($can_del) : ?>
                            <td align="center" nowrap><?= ($record[Contacts::F_IS_HIDDEN] ? "[x]" : "") ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($can_edit || $can_del) : ?>
                                <?=make_html_select(
                                        data: Contacts::TYPES,
                                        name: Contacts::POST_REC . '[' . Contacts::F_TYPE_ID . ']',
                                        selected_id: ($record[Contacts::F_TYPE_ID] > Contacts::T_AUTO
                                                        ? $record[Contacts::F_TYPE_ID]
                                                        : Contacts::autoType($record[Contacts::F_VALUE])),
                                        select_opt:  "class='form-select form-select-sm'"
                                );?>
                                <?php endif; ?>
                            </td>
                            <td nowrap>
                                <?php if ($can_edit) : ?>
                                <button type="submit" class="btn btn-primary btn-sm"><img src="<?= Icons::SRC_ICON_EDIT;?>" width="<?=Icons::ICON_WIDTH_DEF;?>" height="<?=Icons::ICON_HEIGHT_DEF;?>" alt="[E]"/></button>
                                <a  href="<?= Contacts::URI_VISIBLE."/".$record[Contacts::F_ID].'?'.Contacts::F_GET_VISIBLE.'='.($record[Contacts::F_IS_HIDDEN] ? 1 : 0);?>"
                                    class='btn btn-sm btn-secondary'
                                    onclick="return confirm(<?=__('Hide contact from contact list').'?';?>);"
                                    title="<?=__('Hide contact from contact list');?>">
                                    <img src="<?=($record[Contacts::F_IS_HIDDEN] ? Icons::SRC_ICON_VISIBLE_ON : Icons::SRC_ICON_VISIBLE_OFF);?>" width="<?=Icons::ICON_WIDTH_DEF;?>" height="<?=Icons::ICON_HEIGHT_DEF;?>" alt="[hide]"/>
                                </a>
                                <?php endif; ?>
                                <?php if ($can_del) : ?>
                                <a  href="<?= Contacts::URI_DEL . "/" . h($record[Contacts::F_ID]);?>"
                                    class='btn btn-sm btn-secondary'
                                    onclick="return confirm(<?=__('Delete this contact').'?';?>);"
                                    title="<?=__('Delete this contact');?>">
                                    <img src="<?=Icons::SRC_ICON_TRASH;?>" width="<?=Icons::ICON_WIDTH_DEF;?>" height="<?=Icons::ICON_HEIGHT_DEF;?>" alt="[del]"/>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
