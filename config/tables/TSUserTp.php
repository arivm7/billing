<?php


namespace config\tables;


class TSUserTp {

    const POST_REC          = 'tsUserTp';

    const TABLE             = 'ts_user_tp';     // 'Таблица связи Пользователя и ТП';

    const F_USER_ID         = 'user_id';        // 'ID Пользователя',
    const F_TP_ID           = 'tp_id';          // 'ID Техплощадки',
    const F_PERCENT_OWNER   = 'percent_owner';  // 'Процент долевого участия (владения и получения дивидендов)'

}
