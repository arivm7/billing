<?php
/**
 *  Project : my.ri.net.ua
 *  File    : p24accView.php
 *  Path    : app/views/Bank/p24accView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:10:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of p24accView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


/**
 * Данные из контроллера
 * @var string $date1
 * @var string $date2
 * @var array $ppp
 * @var array $transactions -- Список банковских транзакций
 * @var array $unknowns     -- Нераспределённые платежи, внесённые в базу.
 */

require DIR_INC . '/p24acc_transactions.php';

?>
