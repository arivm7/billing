<?php
/**
 *   Шаблон для построения html-строки меню
 */
use app\models\MenuModel;
use app\widgets\menu\Menu;
/**
 * @var Menu  $this
 * @var int   $id       вызов require из Menu::itemToTemplate(array $item, string $tab, int $id);
 * @var array $item     вызов require из Menu::itemToTemplate(array $item, string $tab, int $id);
 */
?>
<option value="<?=$id;?>"><?= $tab . $item[MenuModel::_TITLE]; ?></option>>
<?php if (isset($item[MenuModel::F_CHILDS])): ?>
    <?= $this->get_html(subTree:$item[MenuModel::F_CHILDS], tab: "&nbsp;&nbsp;" . $tab . "-"); ?>
<?php endif; ?>

