<?php
/**
 *  Project : s1.ri.net.ua
 *  File    : PayController.php
 *  Path    : app/controllers/PayController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 18 Oct 2025 14:11:51
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of PayController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Abon;
use config\tables\Ppp;
use config\tables\TP;
use config\tables\User;
use config\tables\Pay;
use config\tables\Price;
use config\tables\PA;
use config\tables\Firm;
use config\tables\AbonRest;

require_once DIR_LIBS . '/bank_api.php';



/**
 * Фазы проведения оплаты:
 * 
 * 1    Выбор номера договора
 *      или из списка договоров авторизованного абонента 
 *      или из поля ввода номера
 * 
 * 2.   На входе есть номер договора
 * 
 *      -- Проверка правильности введённого или выбранного номера договора.
 *         Если abon_id не верен то перейти на п. 1
 * 
 *      -- Для выбранного договора найти акивные прайсовые фрагменты.
 *         Если активных прайсовых фрагментов нет, то искать посление закрытые прайсовые фрагменты.
 * 
 *      -- По найденным прайсовым франгментам
 *         -- найти абонплату, задолженность и вычислить рекомендуемую сумму для оплаты;
 *         -- подтвердить оплачиваемую услугу.
 *         (поскольку, возможно, прайсовые фрагменты (ПФ) найдены из закрытых, то нужно убедиться, что абонент хочет опачивать именно за них)
 * 
 * 3.   На входе есть abon_id, amount -- сумма оплаты.
 * 
 *      -- ещё раз по abon_id найти 
 *          PA -> TP -> Firms -> PPP.
 *
 *      -- Для каждого ППП
 *          -- показать способ оплаты
 *          -- показать кнопку перехода на систему оплаты
 * 
 */
class PayController extends AppBaseController
{

    public function indexAction(){
        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        $model = new AbonModel();
        $phase = $_POST[Pay::POST_REC]['phase'] ?? 0;


        switch ($phase) {

            case 0:

                /**
                 * 1    Выбор номера договора
                 *      или из списка договоров авторизованного абонента 
                 *      или из поля ввода номера
                 */

                $abon_list = App::isAuth() ? $model->get_abons_by_uid($_SESSION[User::SESSION_USER_REC][User::F_ID]): [];
                $title = __('Specify the personal account number that needs to be replenished');
                View::setMeta(__('Payment for services') . ' :: ' . $title);
                $this->setVariables([
                    'title'=> $title,
                    'phase' => ++$phase,
                    'abon_list'=> $abon_list,
                ]);
                break;

            case 1:

                /**
                 * 2.   На входе есть номер договора
                 * 
                 *      -- Проверка правильности введённого или выбранного номера договора.
                 *         Если abon_id не верен то перейти на п. 1
                 * 
                 *      -- Для выбранного договора найти акивные прайсовые фрагменты.
                 *         Если активных прайсовых фрагментов нет, то искать посление закрытые прайсовые фрагменты.
                 * 
                 *      -- По найденным прайсовым франгментам
                 *         -- найти абонплату, задолженность и вычислить рекомендуемую сумму для оплаты;
                 *         -- подтвердить оплачиваемую услугу.
                 *         (поскольку, возможно, прайсовые фрагменты (ПФ) найдены из закрытых, то нужно убедиться, что абонент хочет опачивать именно за них)
                 */

                $abon_id = 
                        (isset($_POST[Pay::POST_REC]['option_abon_id']) && (intval($_POST[Pay::POST_REC]['option_abon_id'] > 0)) 
                            ?   intval($_POST[Pay::POST_REC]['option_abon_id']) 
                            :   (isset($_POST[Pay::POST_REC]['custom_abon_id']) && (intval($_POST[Pay::POST_REC]['custom_abon_id'] > 0)) 
                                    ? intval($_POST[Pay::POST_REC]['custom_abon_id']) 
                                    : Abon::NA
                                )
                        );
                
                /**
                 * Проверка правильности введённого или выбранного номера договора
                 */
                if (!$model->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {
                    MsgQueue::msg(MsgType::ERROR, __('The contract number is incorrect'));
                    redirect(Pay::URI_PAY);
                }

                /**
                 * найти акивные прайсовые фрагменты
                 */
                $pa_list = $model->get_pa_active_or_last($abon_id);

                if (!$pa_list) {
                    MsgQueue::msg(MsgType::INFO, 
                    __('There are no connected services under your agreement'). '.' . CR 
                    . __('Contact the foreman who handles your contract') . '.');
                    redirect(Pay::URI_PAY);
                }

                /**
                 * Прикрепляем Прайсы к прайсовым фрагментам для уточнения цен
                 */
                foreach ($pa_list as &$pa) {
                    $pa[Price::TABLE] = $model->get_price($pa[PA::F_PRICE_ID]);
                }

                $rest = $model->get_abon_rest($abon_id);
                update_rest_fields($rest);


                // debug($pa_list, '$pa_list', die: 0);

                $title = __('Please confirm the services you are paying for');
                View::setMeta(__('Payment for services') . ' :: ' . $title);
                $this->setVariables([
                    'title'=> $title,
                    'abon_id'=>$abon_id,
                    'pa_list'=>$pa_list,
                    'rest'=>$rest,
                    'phase' => ++$phase,
                ]);
                break;

            case 2:

                /**
                 * 3.   На входе есть abon_id, amount -- сумма оплаты.
                 * 
                 *      -- ещё раз по abon_id найти 
                 *          PA -> TP -> Firms -> PPP.
                 *
                 *      -- Для каждого ППП
                 *          -- показать способ оплаты
                 *          -- показать кнопку перехода на систему оплаты
                 */

                $abon_id = 
                        (isset($_POST[Pay::POST_REC]['abon_id']) && (intval($_POST[Pay::POST_REC]['abon_id'] > 0)) 
                            ? intval($_POST[Pay::POST_REC]['abon_id']) 
                            : Abon::NA
                        );

                /**
                 * Проверка правильности номера договора
                 */
                if (!$model->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {
                    MsgQueue::msg(MsgType::ERROR, __('Phase 3: The contract number is incorrect'));
                    redirect();
                }

                /**
                 * Получить сумму для оплаты
                 */
                $amount = 
                        (isset($_POST[Pay::POST_REC][AbonRest::F_AMOUNT]) && (floatval($_POST[Pay::POST_REC][AbonRest::F_AMOUNT]) >= PAYMENT_MIN)) 
                            ? floatval($_POST[Pay::POST_REC][AbonRest::F_AMOUNT]) 
                            : 0.0;  

                /**
                 * Проверка правильности суммы для оплаты
                 */
                if ($amount < PAYMENT_MIN) {
                    MsgQueue::msg(MsgType::ERROR, __('Phase 3: The payment amount is incorrect'));
                    redirect();
                }

                /**
                 * найти акивные прайсовые фрагменты
                 */
                $pa_list = $model->get_pa_active_or_last($abon_id);

                if (!$pa_list) {
                    MsgQueue::msg(MsgType::INFO, 
                    __('There are no connected services under your agreement'). '.' . CR 
                    . __('Contact the foreman who handles your contract') . '.');
                    redirect(Pay::URI_PAY);
                }

                /**
                 * Выбрать вектор ID технических площадок из прайсовых фрагментов
                 */
                $tp_list = 
                        array_values(
                            array_unique(
                                array_map(
                                    'intval',
                                    array_filter(
                                        array_column(
                                            $pa_list,
                                            PA::F_TP_ID), 
                                        fn($v) => $v !== null && $v !== ''
                                    )
                                )
                            )
                        );

                // debug(implode(',', $tp_list), '$tp_list');

                /**
                 *      -- Из ТП выбрать обслуживаемые предприятия.
                 */
                $firm_list_id = $model->get_rows_by_sql( 
                    "SELECT `".TP::F_FIRM_ID."` FROM `".TP::TABLE."` WHERE (`".TP::F_ID."` in (" . implode(',', $tp_list) . ")) AND (`".TP::F_STATUS."` = 1)" 
                );

                if (!$firm_list_id) {
                    MsgQueue::msg(MsgType::INFO, 
                    __('There are no serviced enterprises under your agreement'). '.' . CR 
                    . __('Contact the foreman who handles your contract') . '.');
                    redirect(Pay::URI_PAY);
                }

                /**
                 * Выбрать вектор ID предприятий из ТП
                 */
                $firm_list_id = array_values(
                    array_unique(
                        array_map(
                            'intval',
                            array_filter(
                                array_column(
                                    $firm_list_id,
                                    TP::F_FIRM_ID), 
                                fn($v) => $v !== null && $v !== ''
                            )
                        )
                    )
                );

                /**
                 * Список обслуживающих предприятий
                 */
                $firm_list = $model->get_rows_by_sql( 
                    sql: "SELECT * FROM `".Firm::TABLE."` WHERE (`".Firm::F_ID."` in (" . implode(',', $firm_list_id) . "))",
                    row_id_by: Firm::F_ID
                );

                /**
                 *      -- Из предприятий выбрать пункты приёма платежей (ППП)
                 */
                $ppp_list = $model->get_rows_by_sql( 
                    sql: "SELECT * FROM `".Ppp::TABLE."` WHERE (`".Ppp::F_FIRM_ID."` in (" . implode(',', $firm_list_id) . ")) AND (`".Ppp::F_ACTIVE."` = 1) AND (`".Ppp::F_ABON_PAYMENTS."` = 1) ORDER BY `".Ppp::F_ORDER_NUM."`,`".Ppp::F_TITLE."` ASC",
                    row_id_by: Ppp::F_ID 
                );

                if (!$ppp_list) {
                    MsgQueue::msg(MsgType::INFO, 
                    __('There are no payment methods specified under your agreement'). '.' . CR 
                    . __('Contact the foreman who handles your contract') . '.');
                    redirect(Pay::URI_PAY);
                }


                // debug($ppp_list, '$ppp_list');

                $phase++;
                $title = __('Choose a payment method');
                View::setMeta(__('Payment for services') . ' :: ' . $title);
                $this->setVariables([
                    'phase' => $phase,
                    'title'=> $title,
                    'abon_id'=> $abon_id,
                    'amount'=> $amount,
                    'firm_list'=> $firm_list,
                    'ppp_list'=> $ppp_list,
                ]);
                break;
            
            default:
                throw new \Exception("pay/index: This shouldn't be happening | Этого не должно быть", 1);
                // break;
        }

        
    }
}