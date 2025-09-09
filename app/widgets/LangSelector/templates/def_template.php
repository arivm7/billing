<?php
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