<?php
/**
 *  Project : my.ri.net.ua
 *  File    : printView.php
 *  Path    : app/views/Invoice/printView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Dec 2025 20:11:34
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of printView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

/**
 * Данные переданные из контроллера
 * @var string $title
 * @var array $invoice
 * @var int $show_sht       = 1|0
 * @var int $show_inv       = 1|0
 * @var int $show_act       = 1|0
 * @var array $abon
 * @var array $user
 * @var array $agent
 * @var array $contragent
 * 
 */

use config\Icons;

if ($show_inv) {
    require DIR_INC . '/print_invoice.php';
} else {
    require DIR_INC . '/print_akt.php';
}

?>
