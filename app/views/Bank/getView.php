<?php
/**
 *  Project : my.ri.net.ua
 *  File    : getView.php
 *  Path    : app/views/Bank/getView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Mar 2026 01:52:52
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



/**
 * Вид-диспетчер по внесению абонентских платежей в биллинг
 * 
 * app/controllers/BankController.php
 *          public function getAction()
 *                  app/views/Bank/getView.php
 *                          app/views/inc/get_monocard_dispatcher.php
 *                                  app/views/inc/get_monocard_accounts.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_monocard_statement.php
 *                                  app/views/inc/get_pay_rec_form.php
 * 
 *                          app/views/inc/get_p24acc_dispatcher.php
 *                                  app/views/inc/get_p24acc_accounts.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_p24acc_transaction_card.php
 *                                  app/views/inc/get_pay_rec_form.php
 * 
 *                          app/views/inc/get_p24card_dispatcher.php
 *                                  app/views/inc/get_navigation.php
 *                                  app/views/inc/get_p24card_statement.php
 *                                  app/views/inc/get_pay_rec_form.php
 * 
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Bank;
use config\MonoCard;
use config\P24acc;
use config\tables\Ppp;
use config\tables\PppType;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * Данные приходящие от контроллера
 * 
 * @var array $accounts     [], Банковские карты или рассчётные счета
 * @var array $data         [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
 * @var int   $date1_ts     int, timestamp, начало периода выборки
 * @var int   $date2_ts     int, timestamp, конец периода выборки
 * @var array $ppp          [], ППП
 * 
 */

?>

<h1 class="fs-2"><?= $ppp[Ppp::F_TITLE] ?></h1>

<?php
/**
 * Диспетчер получения данных из банка 
 * в зависимост от типа ППП и поддерживаемого API
 */
switch ($ppp[Ppp::F_TYPE_ID]) {

    case PppType::TYPE_CARD :
        /** 
         * Банковская карта
         */
        switch (true) {

            /**
             * Карта Монобанк
             */
            case is_supported_api($ppp, Bank::API_TYPE_MONO_CARD):
                include DIR_INC . '/get_monocard_dispatcher.php';
                break;
            
            /**
             * Карта Приватбанка
             */
            case is_supported_api($ppp, Bank::API_TYPE_P24_MANUAL):
                include DIR_INC . '/get_p24card_dispatcher.php';
                break;
            
            default:
                echo    '<div class="alert alert-info" role="alert">'
                            . __('Bank card type is not supported | Тип банковской карты не поддерживается | Тип банківської карти не підтримується')
                        . '</div>';
                break;
        }
        break;
    
    case PppType::TYPE_BANK :
        /** 
         * Рассчётный счёт в банке
         */
        switch (true) {
            /**
             * Приватбанк, Автоклиент
             */
            case is_supported_api($ppp, Bank::API_TYPE_P24_ACC):
                include DIR_INC . '/get_p24acc_dispatcher.php';
                break;
            
            default:
                echo    '<div class="alert alert-info" role="alert">'
                            . __('The banking API type is not supported | Тип банковского API не поддерживается | Тип банківського API не підтримується')
                        . '</div>';
                break;
        }
        break;
    
    default:
        echo    '<div class="alert alert-info" role="alert">'
                    . __('The payment acceptance point type is not supported | Тип пункта приёма платежей не поддерживается | Тип пункту прийому платежів не підтримується')
                . '</div>';
        break;
}



// debug($accounts, '$accounts');
// debug($data, '$data');
// debug($ppp, '$ppp');

?>
