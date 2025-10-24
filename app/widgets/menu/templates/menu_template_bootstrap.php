<?php
/*
 *  Project : my.ri.net.ua
 *  File    : menu_template_bootstrap.php
 *  Path    : app/widgets/menu/templates/menu_template_bootstrap.php
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
use config\tables\Menu;
/**
 * @var Menu   $this
 * @var int    $id      вызов require из Menu::itemToTemplate(array $item, string $tab, int $id);
 * @var string $tab     вызов require из Menu::itemToTemplate(array $item, string $tab, int $id);
 * @var array  $item    вызов require из Menu::itemToTemplate(array $item, string $tab, int $id);
 */
?>
<div class="accordion-item">
    <h2 class="accordion-header">
        <?php if (isset($item[Menu::F_CHILDS])) : ?>
            <button title="<?=$item[Menu::_DESCR];?>" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$id;?>" aria-expanded="false" aria-controls="collapse<?=$id;?>">
                <?= $tab . $item[Menu::_TITLE]; ?>
            </button>
        <?php else : ?>
            <?php if ($item[Menu::F_IS_WIDGET]) : ?>
                <!--<hr>-->
                <?php new $item[Menu::F_URL](); ?>
                <!--<hr>-->
            <?php else : ?>
                <!-- <a class="accordion-button no-arrow collapsed" -->
                <a class="accordion-button no-arrow collapsed"
                   title="<?=$item[Menu::_DESCR];?>"
                   href="<?=$item[Menu::F_URL];?>"><?=$item[Menu::_TITLE];?></a>
            <?php endif; ?>
        <?php endif; ?>
    </h2>
    <?php if (isset($item[Menu::F_CHILDS])): ?>
        <div id="collapse<?=$id;?>" class="accordion-collapse collapse" data-bs-parent="#accordionEx">
            <div class="accordion-body pt-0 mt-0 mb-0 pe-0 me-0">   <!-- отступы pt-0 pb-0 mt-0 mb-0 pe-0 me-0 -->
                <?= $this->get_html(subTree:$item[Menu::F_CHILDS], tab: $tab); ?>
            </div>
        </div>
    <?php endif; ?>
</div>