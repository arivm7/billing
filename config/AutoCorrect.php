<?php
/**
 *  Project : my.ri.net.ua
 *  File    : AutoCorrect.php
 *  Path    : config/AutoCorrect.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 03 Dec 2025 21:19:22
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of AutoCorrect.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace config;

class AutoCorrect
{

    const REPLACEMENTS = [
        '<<' => '«',
        '>>' => '»',
    ];

    public static function correct(string $text): string
    {
        return str_replace(
            array_keys(self::REPLACEMENTS),
            array_values(self::REPLACEMENTS),
            $text
        );    
    }

}