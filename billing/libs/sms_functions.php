<?php
/**
 *  Project : my.ri.net.ua
 *  File    : sms_functions.php
 *  Path    : billing/libs/sms_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Nov 2025 22:01:09
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of sms_functions.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use billing\core\App;

function get_sms_info_rec($text) {
    $chars1sms = App::get_config('sms_chars1sms');
    $len = iconv_strlen($text, 'UTF-8');
    $full_sms = (int)($len / $chars1sms);
    $char_in_last_sms = $len - ($full_sms * $chars1sms);
    $ret = [
        'len' => $len,
        'count_sms' => ((int)(($len-1) / $chars1sms) + 1),
        'full_sms' => $full_sms,
        'char_in_last_sms' => $char_in_last_sms,
        'chars1sms' => $chars1sms
    ];
    return $ret;
}