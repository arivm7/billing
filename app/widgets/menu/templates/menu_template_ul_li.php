<?php
/**
 *   Шаблон для построения html-строки меню
 */
use app\models\MenuModel;
use app\widgets\menu\Menu;
/**
 * @var Menu    $this
 * @var int     $id       вызов require из Menu::item_to_template(array $item, string $tab, int $id);
 * @var string  $tab      вызов require из Menu::item_to_template(array $item, string $tab, int $id);
 * @var array   $item     вызов require из Menu::item_to_template(array $item, string $tab, int $id);
 */
?>
<li class="my-accordion-menu-item container-fluid">
    <?php if (isset($item[MenuModel::F_CHILDS])):?>
    <a title="<?=$item[MenuModel::_DESCR];?>">
        <?=$item[MenuModel::_TITLE];?><span class="arrow align-items-end">▶</span>
        <!--
        <div style="display: flex; justify-content: space-between;">
            <div>
                <?=$item[MenuModel::_TITLE];?>
            </div>
            <div>
                &nbsp;<span class="arrow">▶</span>
            </div>
        </div>
        -->
    </a>
    <ul class="my-accordion-submenu" ><?= $this->get_html(subTree:$item[MenuModel::F_CHILDS]);?></ul>
    <?php else : ?>
    <a title="<?=$item[MenuModel::_DESCR];?>" href="<?=$item[MenuModel::F_URL];?>"><?=$item[MenuModel::_TITLE];?></a>
    <?php endif; ?>
</li>
