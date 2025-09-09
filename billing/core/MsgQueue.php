<?php

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

class MsgQueue {

    public static function msg(MsgType $type, string|array $message): void
    {
        $_SESSION[$type->value][] = $message;
    }

}
