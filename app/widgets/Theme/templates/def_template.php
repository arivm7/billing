<?php
/*
 *  Project : my.ri.net.ua
 *  File    : def_template.php
 *  Path    : app/widgets/Theme/templates/def_template.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use app\widgets\Theme\ThemeSelector;
use billing\core\base\Theme;

/** @var ThemeSelector $this */
?>
<select name="theme" id="theme" title="<?=__('Color Theme') . ': ' . $this->curr[Theme::F_TITLE];?>" class="form-select form-select-sm">
    <option value="<?=$this->curr['id'];?>" title="<?=$this->curr[Theme::F_TITLE];?>"><?=$this->curr['id'];?></option>
    <?php foreach ($this->list as $key => $value) : ?>
        <?php if ($this->curr['id'] != $key) : ?>
        <option value="<?=$key;?>" title="<?=$value[Theme::F_TITLE];?>"><?=$key;?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>