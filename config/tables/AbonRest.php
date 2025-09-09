<?php



namespace config\tables;



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

}
