<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : def_template.php
 *  Path    : app/widgets/LangSelector/templates/def_template.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use app\widgets\LangSelector\LangSelector;
/** @var LangSelector $this */
?>
<select name="lang" id="lang" title="<?=__('Мова сайту') . ': ' . $this->curr['title'];?>" class="form-select form-select-sm">
    <option value="<?=$this->curr['code'];?>" title="<?=$this->curr['title'];?>"><?=$this->curr['code'];?></option>
    <?php foreach ($this->list as $key => $value) : ?>
        <?php if ($this->curr['code'] != $key) : ?>
        <option value="<?=$key;?>" title="<?=$value['title'];?>"><?=$key;?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>