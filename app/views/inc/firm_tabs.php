<?php
use config\tables\Firm;
use config\tables\Module;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
?>
<div class="container-fluid">
    <ul class="nav nav-tabs justify-content-end" role="tablist"> <!-- id="my_tab_firms" -->
        <li class="nav-item" role="presentation">
            <a class="nav-link py-1 px-2 active" data-bs-toggle="tab" href="#tab_view_firms_<?=$item[Firm::F_ID]?>" role="tab"><small><?=__('View');?></small></a>
        </li>
        <?php if (can_edit(Module::MOD_MY_FIRM) || can_edit(Module::MOD_FIRM)) : ?>
        <li class="nav-item" role="presentation">
          <a class="nav-link py-1 px-2" data-bs-toggle="tab" href="#tab_edit_firms_<?=$item[Firm::F_ID]?>" role="tab"><small><?=__('Edit');?></small></a>
        </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content"> <!-- id="myTabContent" -->
        <div class="tab-pane fade show active" id="tab_view_firms_<?=$item[Firm::F_ID]?>" role="tabpanel">
            <?php require DIR_INC . '/firm_view.php'; ?>
        </div>
        <?php if (can_edit(Module::MOD_MY_FIRM) || can_edit(Module::MOD_FIRM)) : ?>
        <div class="tab-pane fade" id="tab_edit_firms_<?=$item[Firm::F_ID]?>" role="tabpanel">
            <?php require DIR_INC . '/firm_edit.php'; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
