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
 * 
 * @var string $title           // Заголовок страницы, из которого формируется имя файла для сохранения
 * @var array  $invoice         // Запись Счёта/Акта полученная из бызы
 * @var int    $show_sht        // Флаг: 1|0 -- Показывать штамп и подпись
 * @var int    $show_inv        // Флаг: 1|0 -- Показывать Счёт
 * @var int    $show_act        // Флаг: 1|0 -- Показывать Акт
 * @var array  $abon,           // Абонент, для которого віписан Счёт/Акт
 * @var array  $user,           // Пользователь, для которого віписан Счёт/Акт
 * @var array  $agent,          // Предприятие-провайдер.
 * @var array  $contragent,     // Предприятие-абонент.
 * 
 */


if ($show_inv) {
    require DIR_INC . '/print_invoice.php';
} else {
    require DIR_INC . '/print_akt.php';
}

?>
