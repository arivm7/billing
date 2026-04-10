<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Doubles.php
 *  Path    : config/Doubles.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 08 Apr 2026 21:22:28
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of Doubles.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace config;

use billing\core\App;
use config\tables\Ppp;

/**
 * Класс для поиска дубликатов
 *
 */
class Doubles {

    /**
     * Поля фильтров
     */

    const F_CMD_DO                  = 'DO';


    /**
     * Поля поискового фильтра
     */

    /**
     * Дата начала выборки. Используется в контроллере
     * int unix timestamp
     */
    const F_DATE1_TS                = 'date1_ts';       // int,     Дата начала поиска

    /**
     * Дата начала выборки возвращаемая из формы
     * string
     */
    const F_DATE1_STR               = 'date1_str';      // string,  Дата начала поиска

    // (p1.abon_id = p2.abon_id)
    const F_BY_ABON_ID              = 'by_aid';         // 1|0
    const BY_ABON_ID_DEFAULT        = 1;                // 1|0

    // (p1.pay_type_id = p2.pay_type_id)
    const F_BY_PAY_TYPE_ID          = 'by_pay_type_id'; // 1|0
    const BY_PAY_TYPE_ID_DEFAULT    = 0;                // 1|0

    // (p1.pay_ppp_id = p2.pay_ppp_id)
    const F_BY_PAY_PPP_ID           = 'by_pay_ppp_id';  // 1|0
    const BY_PAY_PPP_ID_DEFAULT     = 1;                // 1|0

    // (p1.pay_bank_no = p2.pay_bank_no)
    const F_BY_PAY_BANK_NO          = 'by_pay_bank_no'; // 1|0
    const BY_PAY_BANK_NO_DEFAULT    = 0;                // 1|0

    // (p1.pay_fakt = p2.pay_fakt)
    const F_BY_PAY_FAKT             = 'by_pay_fakt';    // 1|0
    const BY_PAY_FAKT_DEFAULT       = 1;                // 1|0

    // (p1.pay = p2.pay)
    const F_BY_PAY_ACNT                = 'by_pay_acnt';    // 1|0
    const BY_PAY_ACNT_DEFAULT       = 0;                // 1|0

    // (p1.pay > 0)
    const F_BY_PAY_ACNT_CREDIT         = 'cmp_pay_plus';   // 1|0
    const BY_PAY_ACNT_CREDIT_DEFAULT = 1;               // 1|0

    // (%Y-%m-%d %H:%i:%s)
    const PAY_TIME_LVLS_STR = [
            1 => "%Y-%m-%d %H:%i:%s",
            2 => "%Y-%m-%d %H:%i",
            3 => "%Y-%m-%d %H",
            4 => "%Y-%m-%d",
        ];
    const PAY_TIME_LVLS_TS = [
            1 => 1,             // "%Y-%m-%d %H:%i:%s"  ничего не убирать
            2 => 60,            // "%Y-%m-%d %H:%i"     убрать секунды
            3 => 60*60,         // "%Y-%m-%d %H"        убрать минуты
            4 => 60*60*24,      // "%Y-%m-%d"           убрать часы
        ];

    const F_BY_PAY_TIME_LVL         = 'pay_time_level';     // 1-4 
    const BY_PAY_TIME_LVL_DEFAULT   = 2;                    // 1|2|3|4

    /**
     * Список ППП обслуживаемых сотрудником
     */
    const F_PPP_LIST                = 'ppp_list';           // [...]
    const BY_PPP_INCLUDE_AUTOSELECT = 1;                    // 1|0 Автоматически включить в поиск ППП. Для флага формы

    /**
     * Вектор ID ППП для выборки из базы
     * AND (`pay_ppp_id` IN (...))
     */
    const F_PPP_INCLUDE             = 'in_these_ppp';       // [...]

    // (p1.description = p2.description)
    const F_BY_DESCR                = 'cmp_descr';          // 1|0
    const BY_DESCR_DEFAULT          = 0;                    // 1|0


    /**
     * Дата в запросе выборки плоатежей округлённая до нужного уровня часы/минуты/секунды
     */
    const F_DATE_BUCKET         =   'pay_date_bucket';

    /**
     * Поля результатов поиска
     */

    const F_P1_ID               =   'p1_id';
    // const F_P1_DATE             =   'p1_date';
    // const F_P1_DATE_BUCKET      =   'p1_date_bucket';
    // const F_P1_ABON_ID          =   'p1_abon_id';
    // const F_P1_PAY_FAKT         =   'p1_pay_fakt';
    // const F_P1_PAY_ACNT         =   'p1_pay_acnt';
    // const F_P1_PAY_BANK_NO      =   'p1_pay_bank_no';
    // const F_P1_AGENT_ID         =   'p2_agent_id';
    // const F_P1_TYPE_ID          =   'p2_type_id';
    // const F_P1_PPP_ID           =   'p2_ppp_id';
    // const F_P1_DESCRIPTION      =   'p1_description';

    const F_P2_ID               =   'p2_id';
    // const F_P2_DATE             =   'p2_date';
    // const F_P2_DATE_BUCKET      =   'p2_date_bucket';
    // const F_P2_ABON_ID          =   'p2_abon_id';
    // const F_P2_PAY_FAKT         =   'p2_pay_fakt';
    // const F_P2_PAY_ACNT         =   'p2_pay_acnt';
    // const F_P2_PAY_BANK_NO      =   'p2_pay_bank_no';
    // const F_P2_AGENT_ID         =   'p2_agent_id';
    // const F_P2_TYPE_ID          =   'p2_type_id';
    // const F_P2_PPP_ID           =   'p2_ppp_id';
    // const F_P2_DESCRIPTION      =   'p2_description';


    public static function get_ppp_id_list_sql(): string
    {
        return  "SELECT `id`, `title` "
                . "FROM `ppp_list` "
                . "WHERE `active`=1 "
                    . "AND `firm_id` IN "
                    . "("
                        . "SELECT `id` "
                        . "FROM `firm_list` "
                        . "WHERE `id` IN "
                        . "("
                            . "SELECT `firm_id` "
                            . "FROM `ts_firms_users` "
                            . "WHERE `user_id`=". App::get_user_id().""
                        . ") "
                        . "AND `has_active` "
                        . "AND `has_agent`"
                    . ") "
                . "ORDER BY `".Ppp::F_ORDER_NUM."`, `".Ppp::F_TITLE."`";    
    }

}