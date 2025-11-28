<?php
/**
 *  Project : my.ri.net.ua
 *  File    : notice_block.php
 *  Path    : app/views/inc/notice_block.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Nov 2025 22:00:41
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of notice_block.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * @var string $notice_title
 * @var string $notice_text
 * @var int $sms_print_num
 * @var int $first_line_attr
 */

use config\Icons;
use config\tables\Notify;

$first_line_attr ??= '';

$html_text = ($first_line_attr 
                ? first_line_attr(str_replace([CR, '\n', "\n"], '<br>', h($notice_text)), $first_line_attr) 
                : str_replace([CR, '\n', "\n"], '<br>', h($notice_text)) /* nl2br() */ );
?>
<fieldset class="border mt-4 p-3">
    <legend class="text-info text-start"><?= $notice_title; ?></legend>
    <div>
        <?= $html_text; ?> 
    </div>
    <div class="text-secondary text-end">
        <button class="btn btn-outline-primary btn-sm p-1 copy-btn" data-text='<?= $notice_text; ?>'>
            <img src="<?= Icons::SRC_ICON_CLIPBOARD; ?>" title="<?= __('Скопировать в clipboard') ?>" alt="[copy]" height="30rem">
        </button><br>
        <label><span class="text-secondary">Регистрировать СМС: </span>
        <input class="form-check-input" form="f1" name="<?=Notify::POST_REC;?>[msg][<?= $sms_print_num; ?>][register]" value="1" type="checkbox" /></label>
        <input form="f1" name="<?=Notify::POST_REC;?>[msg][<?= $sms_print_num; ?>][text]" type="hidden" value="<?= h($notice_text); ?>" />
    </div>
</fieldset>
<?php $sms_info = get_sms_info_rec($notice_text); ?>
<div class='text-secondary'>
    Количество символов: <?= $sms_info['len']; ?>. Всего СМС: <?= $sms_info['count_sms']; ?> (<?= $sms_info['full_sms']; ?> полных и <?= $sms_info['char_in_last_sms']; ?><span class="fs-7">/<?= $sms_info['chars1sms']; ?></span> символов в последнем)
</div>
<br>