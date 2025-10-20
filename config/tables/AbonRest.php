<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : AbonRest.php
 *  Path    : config/tables/AbonRest.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of AbonRest.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AbonRest {

    const TABLE      = 'abon_rest';

    /*
     * Поля из базы
     */
    const F_ABON_ID  = 'abon_id';   // ID абонента
    const F_SUM_PAY  = 'sum_pay';   // Сумма платежей и внесений на ЛС
    const F_SUM_COST = 'sum_cost';  // Сумма начислений за услуги price_apply
    const F_SUM_PPMA = 'sum_PPMA';  //
    const F_SUM_PPDA = 'sum_PPDA';  //

    /*
     * Рассчетные поля
     */
    const F_SUM_PP30A = 'sum_PP30A';  // Активная абонплата за 30 дней
    const F_SUM_PP01A = 'sum_PP01A';  // Активная абонплата за 1 день
    const F_REST      = 'rest';       // Остаток на лицевом счету
    const F_PREPAYED  = 'prepayed';   // Количество предоплаченных дней
    const F_AMOUNT    = 'amount';     // Рекомендуемая к оплате сумма


}