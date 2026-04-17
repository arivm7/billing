#!/usr/bin/env php
<?php
/**
 *  Project : my.ri.net.ua
 *  File    : security_cleanup.php
 *  Path    : scripts/security_cleanup.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026 19:10:05
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of security_cleanup.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

declare(strict_types=1);

use billing\core\SecurityAttackGuard;

require __DIR__ . '/../config/dirs.php';
require DIR_CONFIG . '/ini.php';
require DIR_CONFIG . '/colors.php';
require DIR_LIBS . '/common.php';
require DIR_LIBS . '/functions.php';
require __DIR__ . '/../vendor/autoload.php';

new billing\core\App();

$deletedCount = SecurityAttackGuard::cleanupExpiredAttackEvents();

echo date('Y-m-d H:i:s') . ' | deleted attack events: ' . $deletedCount . PHP_EOL;