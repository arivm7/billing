<?php
use app\models\MenuModel;
use config\tables\Menu;
use config\tables\Module;
use billing\core\base\Lang;
/** @var string $title */
/** @var array $params */
/** @var array $item */
$model = new MenuModel();
?>
<div class="container">
    <a name="MENU"></a>
    <h1 class="display-6 text-center"><?=$title;?></h1>
    <br>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(
            t:                      $params['table'],
            pre_align:              $params['pre_align'],
            col_titles:             $params['col_titles'],
            child_col_titles:       $params['child_col_titles'],
            cell_attributes:        $params['cell_attributes'],
            child_cell_attributes:  $params['child_cell_attributes']
    ); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>

<hr>

<?php
    if (!$item) {
        $item[Menu::F_PARENT_ID]   = '';
        $item[Menu::F_ORDER]       = '';
        $item[Menu::F_MODULE_ID]   = '';
        $item[Menu::F_RU_TITLE]    = '';
        $item[Menu::F_UK_TITLE]    = '';
        $item[Menu::F_EN_TITLE]    = '';
        $item[Menu::F_URL]         = '';
        $item[Menu::F_IS_WIDGET]   = '';
        $item[Menu::F_RU_DESCR]    = '';
        $item[Menu::F_UK_DESCR]    = '';
        $item[Menu::F_EN_DESCR]    = '';
    }
    $rec = Menu::POST_REC;
?>

<a name="FORM"></a>
<form class="row g-3" action="" method="post">
    <?php if (isset($item[Menu::F_ID])) : ?>
    <div class="alert alert-primary">
        <?=__('Editing a menu item')?>
    </div>
    <input type="hidden" name="<?=$rec;?>[<?=Menu::F_ID;?>]" value="<?=$item[Menu::F_ID];?>">
    <?php else : ?>
    <div class="alert alert-warning">
        <?=__('New menu item')?>
    </div>
    <?php endif; ?>
    <div class="col-md-2">
        <label for="parent_id" class="form-label text-secondary"><?=Menu::F_PARENT_ID;?></label>
        <input type="text" class="form-control" id="parent_id" name="<?=$rec;?>[<?=Menu::F_PARENT_ID;?>]" value="<?=$item[Menu::F_PARENT_ID];?>" title="<?=__('ID of the parent menu item');?>">
    </div>
    <div class="col-md-2">
        <label for="order" class="form-label text-secondary"><?=Menu::F_ORDER;?></label>
        <input id="order" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_ORDER;?>]" value="<?=$item[Menu::F_ORDER];?>" title="<?=__('The ordinal number of the menu item when displayed');?>">
    </div>
    <div class="col-md-8">
        <label for="ru_title" class="form-label text-secondary"><?=Menu::F_RU_TITLE;?></label>
        <input id="ru_title" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_RU_TITLE;?>]" value="<?=$item[Menu::F_RU_TITLE];?>" title='ru - <?=__('Menu item name');?>'>
    </div>
    <div class="col-md-4">
        <?=make_html_select(
                data: array_column(
                        array: $model->get_rows_by_where(Module::TABLE, row_id_by: Module::F_ID),
                        column_key: Module::F_TITLE[Lang::code()],
                        index_key: Module::F_ID),
                name: "{$rec}[".Menu::F_MODULE_ID."]",
                selected_id: $item[Menu::F_MODULE_ID]);?>
    </div>
    <div class="col-md-8">
        <label for="uk_title" class="form-label text-secondary"><?=Menu::F_UK_TITLE;?></label>
        <input id="uk_title" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_UK_TITLE;?>]" value="<?=$item[Menu::F_UK_TITLE];?>" title="uk - <?=__('Menu item name');?>">
    </div>
    <div class="col-md-2">
        <!-- visible | Пункт меню отображается -->
        <div class="form-check">
            <hr class="pb-1">
            <input class="form-check-input"
                   type="checkbox" id="visible"
                   name="<?=$rec;?>[<?=Menu::F_VISIBLE;?>]"
                   value="1" <?= !empty($item[Menu::F_VISIBLE]) ? 'checked' : '' ?>>
            <label class="form-check-label" for="visible"><?=__('Отображаемый')?></label>
        </div>    </div>
    <div class="col-md-2">
        <!-- anon_visible | Показывать для неавторизованных пользователей -->
        <div class="form-check" title="<?=_('Показывать для неавторизованных пользователей')?>">
            <hr class="pb-1">
            <input class="form-check-input"
                   type="checkbox" id="anon_visible"
                   name="<?=$rec;?>[<?=Menu::F_ANON_VISIBLE;?>]"
                   value="1" <?= !empty($item[Menu::F_ANON_VISIBLE]) ? 'checked' : '' ?>>
            <label class="form-check-label" for="anon_visible"><?=__('Для анонимных')?></label>
        </div>
    </div>
    <div class="col-md-8">
        <label for="en_title" class="form-label text-secondary"><?=Menu::F_EN_TITLE;?></label>
        <input id="en_title" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_EN_TITLE;?>]" value="<?=$item[Menu::F_EN_TITLE];?>" title="en - <?=__('Menu item name');?>">
    </div>
    <div class="col-md-2"></div>

    <div class="col-md-2">
        <div class="form-check">
            <hr class="pb-1">
            <input class="form-check-input"
                   type="checkbox" id="is_widget"
                   name="<?=$rec;?>[<?=Menu::F_IS_WIDGET;?>]"
                   value="1" <?= !empty($item[Menu::F_IS_WIDGET]) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_widget"><?=__('is widget')?></label>
        </div>
    </div>

    <div class="col-md-8">
        <label for="url" class="form-label text-secondary"><?=Menu::F_URL;?> :: URL для &lt;a href=URL&gt;</label>
        <input id="url" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_URL;?>]" value="<?=$item[Menu::F_URL];?>" title="<?=__('Menu Item URL');?>">
    </div>
    <div class="col-12">
        <label for="ru_description" class="form-label text-secondary"><?=Menu::F_RU_DESCR;?></label>
        <input id="ru_description" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_RU_DESCR;?>]" value="<?=$item[Menu::F_RU_DESCR];?>" title="ru - <?=__('Description of the menu item')?>">
    </div>
    <div class="col-12">
        <label for="uk_description" class="form-label text-secondary"><?=Menu::F_UK_DESCR;?></label>
        <input id="uk_description" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_UK_DESCR;?>]" value="<?=$item[Menu::F_UK_DESCR];?>" title="uk - <?=__('Description of the menu item')?>">
    </div>
    <div class="col-12">
        <label for="en_description" class="form-label text-secondary"><?=Menu::F_EN_DESCR;?></label>
        <input id="en_description" type="text" class="form-control" name="<?=$rec;?>[<?=Menu::F_EN_DESCR;?>]" value="<?=$item[Menu::F_EN_DESCR];?>" title="en - <?=__('Description of the menu item')?>">
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary"><?=__('Send');?></button>
    </div>
</form>

