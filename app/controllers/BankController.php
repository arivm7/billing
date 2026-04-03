<?php
/**
 *  Project : my.ri.net.ua
 *  File    : BankController.php
 *  Path    : app/controllers/BankController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:12:45
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of BankController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\models\AbonModel;
use billing\core\Api;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Module;
use config\Bank;
use config\MonoCard;
use config\P24acc;
use config\P24card;
use config\SessionFields;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\PppType;

class BankController extends AppBaseController
{

    /**
     * Статусы выполнения внесений в биллинг
     * @var int
     */
    const DO_STATUS_OK = 1;
    const DO_STATUS_ERROR = -1;
    const DO_STATUS_NA = 0;


    /**
     * Список Api поддерживаемых методом getAction()
     */
    public const SUPPORTED_API_LIST = [
        Bank::API_TYPE_P24_ACC, 
        Bank::API_TYPE_MONO_CARD,
        Bank::API_TYPE_P24_MANUAL,
    ];



    function getAction() {

        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');


        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            redirect('/');
        }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            redirect();
        }



        $model = new AbonModel();



        if  (
                empty($this->route[F_ALIAS]) ||
                !$model->validate_ppp((int)$this->route[F_ALIAS])
            ) 
        {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point ID is incorrect or not specified | ID пункта приёма платежей не верен или не указан | ID пункту прийому платежів не вірний або не вказаний'));
            redirect();
        }

        $ppp = $model->get_ppp((int)$this->route[F_ALIAS]);

        if (!$model->is_ppp_my($ppp[Ppp::F_ID])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('The payment acceptance point is not yours | Пункт приема платежей не Ваш | Пункт прийому платежів не Ваш'));
            redirect();
        }



        /**
         * -------------------------------------------------------------------------------
         * Внесение данных в базу
         */
        if (isset($_POST[Bank::POST_REC]) && is_array($_POST[Bank::POST_REC])) {
            // debug($_POST, '$_POST', die:1);
            $post_rec = $_POST[Bank::POST_REC];

            foreach ($post_rec as $index => $rec) {

                /**
                 * Сохранение шаблона, если он передан
                 */
                if (($rec[Bank::F_FOUND_TEMPLATE_SAVE] ?? 0) == 1) {
                    TemplateController::insert($rec[Pay::F_PPP_ID], $rec[Pay::F_ABON_ID], $rec[Bank::F_FOUND_TEMPLATE]);
                }
                unset($rec[Bank::F_FOUND_TEMPLATE_SAVE]);
                unset($rec[Bank::F_FOUND_TEMPLATE]);

                /**
                 * Сохранение платежа
                 */
                if (($rec[Pay::F_POST_SAVE] ?? 0) == 1) {
                    unset($rec[Pay::F_POST_SAVE]);

                    /**
                     * Нормализация полей платежа
                     */
                    PaymentsController::normalize($rec);

                    if (!empty($rec[Pay::F_SAVE_SUFFIX])) {
                        $rec[Pay::F_DESCRIPTION] = $rec[Pay::F_DESCRIPTION] . ' ' . trim($rec[Pay::F_SAVE_SUFFIX]);
                    }
                    unset($rec[Pay::F_SAVE_SUFFIX]);

                    /**
                     * Валидация полей платежа
                     */
                    if (PaymentsController::validate_deep($rec)) {
                        if (PaymentsController::payInsert($rec)) {
                            MsgQueue::msg(MsgType::SUCCESS, __('Платеж внесён') . ': ' 
                                    . $rec[Pay::F_ABON_ID] . ' | ' 
                                    . $rec[Pay::F_PAY_FAKT] . ' грн.' . ' | ' 
                                    . date('Y-m-d H:i:s', $rec[Pay::F_DATE]) . ' | ' 
                                    . h($rec[Pay::F_DESCRIPTION] ));
                            $model->recalc_abon($rec[Pay::F_ABON_ID], false);
                        }
                    }
                }
            }
            
            // redirect();
        }
        /**
         * Конец блока внесение данных в базу
         * -------------------------------------------------------------------------------
         */



        /**
         * Проверка, что ППП поддерживает те Апи, которые поддерживает этот Action
         */
        $api_suported = false;
        foreach (self::SUPPORTED_API_LIST as $api_type) {
            if (is_supported_api($ppp, $api_type)) {
                $api_suported = true;
                break;
            }
        }
        if (!$api_suported) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('API type is not supported | Тип API не поддерживается | Тип API не підтримується'));
            redirect();
        }
        unset($api_suported);
        unset($api_type);


        
        /**
         * Вычисляем даты интервала для получения транзакции
         */
        $last_pay = $model->get_last_pay_on_ppp($ppp[Ppp::F_ID]);
        $date1_ts = intval(isset($_GET[Bank::F_GET_DATE1_TS])
                        ?   $_GET[Bank::F_GET_DATE1_TS]
                        :   TODAY() - App::get_config('bank_date_interval')
                        // :   (!is_null($last_pay) ? $last_pay[Pay::F_DATE] : TODAY() - App::get_config('bank_date_interval'))
                    );
        
        $date2_ts = intval(isset($_GET[Bank::F_GET_DATE2_TS])
                        ?   $_GET[Bank::F_GET_DATE2_TS]
                        :   (($date1_ts + App::get_config('bank_date_interval')) > time() 
                                ?   time()
                                :   $date1_ts + App::get_config('bank_date_interval')
                            )
                    );

        // MsgQueue::msg(MsgType::INFO, __("Sampling dates | Даты выборки | Дати вибірки") . ": [". date('Y-m-d', $date1_ts) . "] - [" . date('Y-m-d', $date2_ts) . "]");



        $token   = $ppp[Ppp::F_API_ID];   // что-то такое: 'uBrjknkdhfasdfLXz44583xHOHfWgm';
        $api_url = $ppp[Ppp::F_API_URL];  // что-то такое: 'https://api.monobank.ua/personal/statement/';
        $accounts = [];
        $statements = [];
        $map_fields = [];
        $save_suffix = '';


        
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
                        $accounts   = MonoCard::get_card_info($token)['client'];
                        $statements = MonoCard::get_statements($token, $accounts, $date1_ts, $date2_ts, $api_url)[Bank::F_STATEMENTS];
                        // debug($accounts, '$accounts');
                        // debug($statements, '$statements');
                        $map_fields = Bank::MAP_MONOCARD;
                        if (isset($cards_info['errorDescription'])) {
                            MsgQueue::msg(MsgType::ERROR, __('Error receiving card information | Ошибка получения информации о карте | Помилка отримання інформації про карту') . ': ' . $cards_info['client']['errorDescription']);
                            // redirect(); // Bank::URI_INDEX
                        }
                        break;
                    
                    /**
                     * Карта Приватбанка
                     */
                    case is_supported_api($ppp, Bank::API_TYPE_P24_MANUAL):

                        /**
                         * Входные данные получаюся НЕ из базы, а из формы с текстовым полем, 
                         * в которое копипастится таблица с транзакциями.
                         * 
                         * Если данных нет, то выводится поля для ввода текстовой таблицы
                         */
                        
                        if  (isset($_POST[P24card::F_RAW_TEXT])) { 
                            /**
                             * Данные есть
                             */
                            $text_raw = $_POST[P24card::F_RAW_TEXT];
                            $statements = P24card::get_transactions($text_raw);
                            $map_fields = Bank::MAP_P24CARD;
                            $save_suffix = '(Manual ODF)';
                        }
                        break;
                    
                    default:
                        MsgQueue::msg(MsgType::ERROR, __('Этот тип банковских карт не поддерживается') . '. ' . __('Обратитесь к програмистам'));
                        redirect(); // Bank::URI_INDEX
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
                        $accounts   = P24acc::get_accounts($ppp);
                        $statements = P24acc::get_transactions($ppp, $date1_ts, $date2_ts, Bank::TRANSACTION_TYPE_C);
                        $map_fields = Bank::MAP_P24ACC;
                        break;
                    
                    default:
                        MsgQueue::msg(MsgType::ERROR, __('Этот тип банковского расчётного счёта не поддерживается') . '. ' . __('Обратитесь к програмистам'));
                        redirect(); // Bank::URI_INDEX
                        break;
                }
                break;
            
            default:
                MsgQueue::msg(MsgType::ERROR, __('Этот тип ППП не поддерживается') . '. ' . __('Обратитесь к програмистам'));
                redirect(); // Bank::URI_INDEX
                break;
        }



        /**
         * Чтение шаблонов распознавания абонентов
         */
        if ($statements) {
            $templates   = $model->get_abons_templates($ppp[Ppp::F_ID]);
            MsgQueue::msg(MsgType::INFO, __('Number of entries in the template table | Количество записей в таблице шаблонов | Кількість записів у таблиці шаблонів') . ": " . count($templates));
        }



        /**
         * Формирование записи для каждой транзакции включающей:
         *      [Bank::F_STATEMENT] -- саму транзакцию
         *      [Bank::F_FOUND_REC] -- запись с результатами поиска в биллинге
         *      [Bank::F_PAY_REC]   -- запись платежа для сохранения в биллинг или сравнения
         */
        $data = [];
        foreach ($statements as $index => $statement) {

            $data[$index][Bank::F_STATEMENT] = $statement;

            /**
             * Запись для внесения в базу и сравнения с уже внесёнными данными
             */
            $comission = (!empty($map_fields[Bank::F_COMISSION_FIELD]) 
                    ?   ($statement[$map_fields[Bank::F_COMISSION_FIELD]] ?? 0) 
                    :   ((is_supported_api($ppp, Bank::API_TYPE_P24_ACC))
                            ?   (str_contains_array($statement[P24acc::F_OSND], App::get_config('bank_comission_texts'))
                                    ? App::get_config('bank_comission_value')
                                    : 0
                                )
                            :   0
                        )
                    );
            $pay_rec[Pay::F_PAY_FAKT]    = floatval(get_numeric_part($statement[$map_fields[Pay::F_PAY_FAKT]]));
            $pay_rec[Pay::F_PAY_ACNT]    = floatval(get_numeric_part($statement[$map_fields[Pay::F_PAY_ACNT]]) + $comission);
            $pay_rec[Pay::F_REST]        = (isset($map_fields[Pay::F_REST]) 
                                            ?   floatval(get_numeric_part($statement[$map_fields[Pay::F_REST]]) + $comission)
                                            :   null);
            $pay_rec[Pay::F_DATE]        = (is_int($statement[$map_fields[Pay::F_DATE]]) 
                                            ?   $statement[$map_fields[Pay::F_DATE]]
                                            :   strtotime($statement[$map_fields[Pay::F_DATE]]));
            $pay_rec[Pay::F_BANK_NO]     = (is_array($map_fields[Pay::F_BANK_NO]) 
                    ?   implode(
                            '',
                            array_filter(
                                array_map(
                                    function ($key) use ($statement) {
                                        return trim((string)($statement[$key] ?? ''));
                                    },
                                    $map_fields[Pay::F_BANK_NO]
                                ),
                                function ($value) {
                                    return $value !== '';
                                }
                            )
                        )
                    :   $statement[$map_fields[Pay::F_BANK_NO]]) ;
            $pay_rec[Pay::F_TYPE_ID]     = Pay::TYPE_MONEY;
            $pay_rec[Pay::F_AGENT_ID]    = App::get_user_id();
            $pay_rec[Pay::F_PPP_ID]      = $ppp[Ppp::F_ID];
            $pay_rec[Pay::F_DESCRIPTION] = implode(
                        ' | ',
                        array_filter(
                            array_map(
                                function ($key) use ($statement) {
                                    return trim((string)($statement[$key] ?? ''));
                                },
                                $map_fields[Pay::F_DESCRIPTION]
                            ),
                            function ($value) {
                                return $value !== '';
                            }
                        )
                    );
            $pay_rec[Pay::F_SAVE_SUFFIX] = $save_suffix;

            $data[$index][Bank::F_PAY_REC] = $pay_rec;

            /**
             * платежи, внесённые в биллинг с указанным кодом транзакции
             */
            $data[$index][Bank::F_FOUND_REC] = Bank::search_payments_on_billing2($pay_rec, $templates); 


        }
        // MsgQueue::msg(MsgType::INFO, "==== ". __("All data is collected | Все данные собраны | Всі дані зібрані") ." ====");

        View::setMeta(__('Payments to a Monobank card | Платежи на карту Монобанка | Платежі на карту Монобанку'));

        $this->setVariables([
            'accounts'      => $accounts,   // [], Банковские карты или рассчётные счета
            'data'          => $data,       // [ Bank::F_STATEMENT[], Bank::F_FOUND_REC[], Bank::F_PAY_REC[] ]
            'date1_ts'      => $date1_ts,   // int, timestamp, начало периода выборки
            'date2_ts'      => $date2_ts,   // int, timestamp, конец периода выборки
            'ppp'           => $ppp,        // array ППП
        ]);

    }



    function indexAction() {

        if (!App::isAuth()) { redirect('/'); }

        if (!can_add(Module::MOD_PAYMENTS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            redirect();
        }

        $model = new AbonModel();

        $ppp_list = [];
        foreach (Bank::API_MANUAL_LIST as $api_type) {
            $ppp_list[$api_type] = $model->get_ppp_list_by_api($api_type);
        }


        $this->setVariables([
            'ppp_list' => $ppp_list,
        ]);

        View::setMeta(title: __('Список ППП для контроля и ручного распределения платежей'));

    }




}