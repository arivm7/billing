<?php
/*
 *  Project : my.ri.net.ua
 *  File    : menu_template_select.php
 *  Path    : app/widgets/menu/templates/menu_template_select.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

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
