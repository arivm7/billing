<?php
use app\widgets\Theme\ThemeSelector;
use billing\core\base\Theme;

/** @var ThemeSelector $this */
?>
<select name="theme" id="theme" title="<?=__('Кольорова Тема') . ': ' . $this->curr[Theme::F_TITLE];?>" class="form-select form-select-sm">
    <option value="<?=$this->curr['id'];?>" title="<?=$this->curr[Theme::F_TITLE];?>"><?=$this->curr['id'];?></option>
    <?php foreach ($this->list as $key => $value) : ?>
        <?php if ($this->curr['id'] != $key) : ?>
        <option value="<?=$key;?>" title="<?=$value[Theme::F_TITLE];?>"><?=$key;?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>