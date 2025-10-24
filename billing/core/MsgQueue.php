<?php
/*
 *  Project : my.ri.net.ua
 *  File    : MsgQueue.php
 *  Path    : billing/core/MsgQueue.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 15:32:22
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

use config\SessionFields;

enum MsgType: string {
    case ERROR          = SessionFields::ERROR;
    case ERROR_AUTO     = SessionFields::ERROR_AUTO;
    case SUCCESS        = SessionFields::SUCCESS;
    case SUCCESS_AUTO   = SessionFields::SUCCESS_AUTO;
    case INFO           = SessionFields::INFO;
    case INFO_AUTO      = SessionFields::INFO_AUTO;
}

/**
 * Description of MsgQueue.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class MsgQueue {

    public static function msg(MsgType $type, string|array $message): void
    {
        if (empty($message)) {
            return;
        }

        if (!isset($_SESSION[$type->value]) || !is_array($_SESSION[$type->value])) {
            $_SESSION[$type->value] = [];
        }

        if (is_array($message)) {
            // объединяем массивы
            $_SESSION[$type->value] = array_merge($_SESSION[$type->value], $message);
        } else {
            // добавляем строку в конец очереди
            $_SESSION[$type->value][] = $message;
        }
    }


}