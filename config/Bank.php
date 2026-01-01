<?php
/**
 *  Project : my.ri.net.ua
 *  File    : Bank.php
 *  Path    : config/Bank.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Oct 2025 19:53:55
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of Bank.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

namespace config;

use app\models\AbonModel;
use billing\core\App;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\TSAbonTmpl;
use PDO;

class Bank
{

    const URI_INDEX = '/bank';

    const POST_REC = 'pay_update';
    
    const URI_API_LIST = [
        self::API_TYPE_P24_ACC      => '/bank/p24acc',
        self::API_TYPE_P24_MANUAL   => '/bank/p24manual',
        self::API_TYPE_MONO_CARD    => '/bank/monocard',
    ];

    /**
     * Поддерживаемые АПИ
     */
    const API_TYPE_BANK_CARD  = 'bank_card';
    const API_TYPE_P24_ACC    = 'p24_acc';
    const API_TYPE_P24_LIQPAY = 'p24_liqpay';
    const API_TYPE_P24PAY     = 'p24pay';
    const API_TYPE_P24_MANUAL = 'p24_manual';
    const API_TYPE_MONO_CARD  = 'mono_pay';

    /**
     * Имена $_GET переменных для праметров выборки
     */
    const F_GET_ACC        = 'acc';
    const F_GET_PPP_ID        = 'ppp_id';
    const F_GET_DATE_START    = 'startDate';
    const F_GET_DATE_END      = 'endDate';
    const F_GET_FOLLOW_ID     = 'followId';
    const F_GET_LIMIT         = 'limit';



    /**
     * Полный список АПИ
     */
    const API_TYPE_LIST = [
        self::API_TYPE_BANK_CARD,
        self::API_TYPE_P24_ACC,
        self::API_TYPE_P24_LIQPAY,
        self::API_TYPE_P24PAY,
        self::API_TYPE_P24_MANUAL,
        self::API_TYPE_MONO_CARD,
    ];



    /**
     * Список АПИ для ручного внесения поатежей
     */
    const API_MANUAL_LIST = [
        self::API_TYPE_P24_ACC,
        self::API_TYPE_P24_MANUAL,
        self::API_TYPE_MONO_CARD,
    ];



    /**
     * //Тип вибираемых транзакций дебет/кредит (D, C)
     */
    const TRANSACTION_TYPE_D   = -1; // D "-" (Дебет)
    const TRANSACTION_TYPE_C   =  1; // C "+" (Кредит)
    const TRANSACTION_TYPE_ALL =  0; // Все



    /**
     * Возвращает список транзакций
     * @param array $ppp
     * @param string $date1
     * @param string $date2
     * @param mixed $trantype
     * @throws \Exception
     * @return array
     */
    public static function p24acc_get_transactions(array $ppp, string $date1, string $date2, $trantype = self::TRANSACTION_TYPE_ALL): array {

        $autoclient_id      = $ppp[Ppp::F_API_ID];
        $autoclient_token   = $ppp[Ppp::F_API_PASS];
        $autoclient_acc     = str_replace(" ", "", $ppp[Ppp::F_NUMBER]);
        $autoclient_url     = $ppp[Ppp::F_API_URL];

        $followId           = null;

        $headers = self::p24acc_make_header($autoclient_id, $autoclient_token);

        $transactions = [];
        $iteration     = 0;
        do {
            $iteration++;
            $request = self::p24acc_make_request($autoclient_acc, $date1, $date2, $followId);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPGET,           1); // Использовать метод GET
            curl_setopt($ch, CURLOPT_HTTPHEADER,        $headers);
            curl_setopt($ch, CURLOPT_URL,               $autoclient_url.$request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    1); // TRUE to return the transfer as a string of the return value of {@see curl_exec()} instead of outputting it directly.
            curl_setopt($ch, CURLOPT_HEADER,            0); // TRUE to include the header in the output.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,    1); // Проверять SSL сертификат / 1 - проверять, 0 - не проверять

            $res = curl_exec($ch);
            $s = "Статус получения данных из банка: ".(curl_errno($ch)==0?"Ok":"ERROR")." : ".curl_error($ch)."<br>\n";
            MsgQueue::msg(MsgType::INFO_AUTO, $s);
            if(curl_errno($ch)!=0) {
                curl_close($ch);
                throw new \Exception('Ошибка выполнения запроса к банку. Сообщите программисту');
            }
            curl_close($ch);

            $arr = json_decode($res, true);
            //echo "ARRAY:<pre>"; var_dump($arr); echo "</pre><hr>";
            $s  = "Статус ответа банка: ".($arr['status']).(isset($arr['message']) ? " [".paint($arr['message'], color: RED)."]":"")."<br>\n";
            MsgQueue::msg(MsgType::INFO_AUTO, $s);
            foreach ($arr['transactions'] as $T) {
                switch ($trantype) {
                    case self::TRANSACTION_TYPE_ALL:
                        $transactions[] = $T;
                        break;
                    case self::TRANSACTION_TYPE_C: // Кредит +
                        if ($T['TRANTYPE'] == 'C') {
                            $transactions[] = $T;
                        }
                        break;
                    case self::TRANSACTION_TYPE_D: // Дебет -
                        if ($T['TRANTYPE'] == 'D') {
                            $transactions[] = $T;
                        }
                        break;
                    default:
                        throw new \Exception('Не верноый тип транзакции TRANSACTION_TYPE_*: {$trantype}');
                }
            }
            $followId = ($arr['exist_next_page']
                            ? $arr['next_page_id']
                            : null
                        );
            $s = "Следующая транзакция: ".($followId?$followId:"нет")."<br>\n";
            MsgQueue::msg(MsgType::INFO_AUTO, $s);
        } while ($arr['exist_next_page'] && ($iteration < App::get_config('bank_get_iteration_max')));
        return $transactions;
    }



    public static function p24acc_make_header(string $autoclient_id, string $autoclient_token): array {
        return array
        (
            'User-Agent: '. App::get_config('bank_http_user_agent'),
            'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
            'Accept-Encoding: deflate',
            'Accept-Charset: utf-8;q=0.7,*;q=0.7',
            'id: '.$autoclient_id,
            'token: '.$autoclient_token,
            'Content-Type: application/json;charset=utf8'
        );
    }


    public static function p24acc_make_request(string $autoclient_acc, string $date1, string $date2, ?string $followId = null): string {
        return '?'.self::F_GET_ACC.'='.$autoclient_acc
                .'&'.self::F_GET_DATE_START.'='.rawurlencode($date1)
                .'&'.self::F_GET_DATE_END.'='.rawurlencode($date2)
                .($followId ? '&'.self::F_GET_FOLLOW_ID.'='.$followId : "")
                .'&'.self::F_GET_LIMIT.'='. App::get_config('bank_limit_per_page');
    }



    /**
     * Возвращает abon_id из таблицы шаблонов по входной строке
     * @param array $templates -- ссылка на массив шаблонов (из БД/TSAbonTmpl::TABLE)
     * @param string $text -- входная строка
     * @return int -- abon_id или 0 если не найдено
     */
    public static function get_abon_id_from_templates(array &$templates, string $text): int {
        foreach ($templates as $template) {
            if(is_empty(trim($template[TSAbonTmpl::F_TEMPLATE]))) {
                continue;
            }
            if(strpos($text, $template[TSAbonTmpl::F_TEMPLATE]) !== false) {
                return $template[TSAbonTmpl::F_ABON_ID];
            }
        }
        return 0;
    }




    /**
     * Поиск платежа в биллинге или поиск абонента, для которого этот платеж.
     * Поиск в базе проводится только по номеру документа (ID транзакции в банке).
     * Возвращает ассоциативный массив:
     * [payments]    -- массив найденных платежей, считается найденным, если найден один платёж
     * [abon]        -- если платёж не найден, то абонент, для которого этот платёж
     * [on_billing]  -- найден в биллинге, платёж уже внесён
     * [searched_on] -- коментарий к поиску, где найден платёж или абонент
     * @param array $transaction
     * @param int $ppp_id
     * @param array $templates
     * @return array
     */
    public static function p24acc_search_payments_on_billing($transaction, $ppp_id, &$templates): array {
        $model = new AbonModel();
        $found_pay['payments'] = $model->get_billing_payments_by_no($transaction[P24acc::F_ID], $ppp_id);
        // if (count($found_pay['payments']) == 0) {
        //     $found_pay['payments'] = $model->get_billing_payments_id_date_pay($transaction[P24acc::F_NUM_DOC], strtotime($transaction[P24acc::F_DATE_TIME_DAT_OD_TIM_P]), get_numeric_part($transaction[P24acc::F_SUM]), $ppp_id, true);
        // }

        if (count($found_pay['payments']) > 1) {
            // Если найдено несколько платежей, то считаем, что НЕ найдено
            $id_list = array_column($found_pay['payments'], Pay::F_ID);
            foreach ($id_list as &$item) { $item = url_abon_form($item); }
            $found_pay['on_billing']  = false;
            $found_pay['searched_on'] = 'billing ('.__('Many found | Найдено много | Знайдено багато').': '.implode(", ", $id_list).')';
        } elseif (count($found_pay['payments']) == 1) {
            $found_pay['abon']        = $model->get_abon($found_pay['payments'][0][Pay::F_ABON_ID]);
            $found_pay['on_billing']  = true;
            $found_pay['searched_on'] = 'billing';
        } else {
            $aid = Bank::get_abon_id_from_templates($templates, $transaction[P24acc::F_OSND]);
            if ($aid > 0) {
                $found_pay['abon']        = $model->get_abon($aid);
                $found_pay['on_billing']  = false;
                $found_pay['searched_on'] = 'Templates (by [' . P24acc::F_OSND .'])';
            } else {
                $aid = Bank::get_abon_id_from_templates($templates, $transaction[P24acc::F_AUT_CNTR_NAM]);
                if ($aid > 0) {
                    $found_pay['abon']        = $model->get_abon($aid);
                    $found_pay['on_billing']  = false;
                    $found_pay['searched_on'] = 'Templates (by [' . P24acc::F_AUT_CNTR_NAM . '])';
                } else {
                    $aid = $model->get_abon_id_from_text($transaction[P24acc::F_OSND]);
                    if ($aid > 0) {
                        $found_pay['abon']        = $model->get_abon($aid);
                        $found_pay['on_billing']  = false;
                        $found_pay['searched_on'] = 'from [' . P24acc::F_OSND .']';
                    } else {
                        $payer_txt = Bank::get_payer_from_text($transaction[P24acc::F_OSND]);
                        if (strlen($payer_txt)>0) {
                            $aid = $model->get_abon_id_one_by_payer_name($payer_txt);
                            if ($aid > 0) {
                                $found_pay['abon']        = $model->get_abon($aid);
                                $found_pay['on_billing']  = false;
                                $found_pay['searched_on'] = 'Payments &laquo;'.$payer_txt.'&raquo;';
                                $found_pay['template']    = $payer_txt;
                            } else {
                                $found_pay['on_billing']  = false;
                                $found_pay['searched_on'] = __('Nowhere | Нигде | Ніде');
                            }
                        } else {
                            $found_pay['on_billing']  = false;
                            $found_pay['searched_on'] = __('Nowhere | Нигде | Ніде');
                        }
                    }
                }
            }
        }
        return $found_pay;
    }



    /**
     * Возвращает имя плательщика найденное во входной строк
     * @param string $text
     * @return string
     */
    public static function get_payer_from_text(string $text): string {

        $template_words     = "дог\."
                            . "|Отправитель"
                            . "|ПЛ\-ЩИК"
                            . "|Пл\-щик"
                            . "|Плательщик"
                            . "|Перевод от"
                            . "|От"
                            . "|Від"
                            . "|за послуги інтернет від";
        $template_excludes  = ['Тел.', 'тел.', 'карта'];
        $template_name      = "[^0-9,(\+\.\s]+\.?";
        $subject            = $text;
        // номера групп:           1                     2
        $pattern            = "/^.*($template_words):?\s+((($template_name)\s*){2,4}).*$/";
        $matches            = array();
        $p                  = preg_match_all($pattern, $subject, $matches);
        if($p === false) {
            die("Этого не должно быть: get_abon_id_from_text(string $text)");
        } else {
            if($p > 0) {
                //echo "<pre>"; var_dump($matches); echo "</pre>";
                return trim(str_replace($template_excludes, "", trim($matches[2][0])));
            } else {
                return "";
            }
        }
    }



    /**
     * Возвращает сумму комиссии, снятую банком
     * вычисляется из суммы фактически пришедшей и комиссии банка
     * @param float $pay_fact -- фактичеси пришедшая сумма
     * @param float $koefficient -- коэффициент комиссии банка
     * @return float -- сумма денег, снятая банком в качестве комиссии
     */
    public static function calc_comission(float $pay_fact, float | null $koefficient): float {
        return 0.0;
        //return ($koefficient*$pay_fact)/(1-$koefficient);
    }



    public static function get_html_transaction_real(string $param): string {
        switch ($param) {
        case 'r':
            return paint(s: "Р.", color: "GREEN", title: "Ознака реальності проводки(r,i)");
        case 'i':
        default:
            return paint(s: $param, color: "GRAY", title: "Ознака реальності проводки(r,i)");
        }
    }



    public static function get_html_transaction_status(string $param): string {
        $title='Стан p-проводиться, t-сторнирована, r-проведена, n-забракована';
        switch ($param) {
            case 'p':
                return paint(s: "проводиться", color: "BLUE", title: $title);
            case 't':
                return paint(s: "сторнирована", color: "RED", title: $title);
            case 'r':
                return paint(s: "проведена", color: "GREEN", title: $title);
            case 'n':
                return paint(s: "забракована", color: "RED", title: $title);
            default:
                return $param;
        }
    }



    public static function template_has_exist(int $ppp_id, string $template): bool { 
        // !!! нужно уменьшить количество запросов, свести к одному. или оставить как есть.
        $model = new AbonModel();
        $sql = "SELECT `id` FROM `ts_abons_templates` WHERE `ppp_id`=".$ppp_id." AND `template`='".$template."'";
        $count = $model->get_count_by_sql($sql);
        return $count > 0;
    }    



    public static function template_add(int $ppp_id, int $abon_id, string $template): int|false {

        $model = new AbonModel();

        // Проверка чтобы такой записи небыло
        $template = $model->quote($template);
        if(!self::template_has_exist($ppp_id, $template)) {
            // добавление новой записи
            $row = [
                'ppp_id' => $ppp_id,
                'abon_id' => $abon_id,
                'template' => $template,
                'modified_uid' => App::get_user_id(),
                'modified_date' => time(),
                'created_uid' => App::get_user_id(),
                'created_date' => time(),
            ];
            return $model->insert_row('ts_abons_templates', $row);
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Такая запись шаблона в базе есть'));
            return false;
        }
    }



    /**
     * Проверяет, есть ли такой платёж
     * @param int    $abon_id      Абонент, на которого зачисляется поалёж
     * @param float  $pay_fakt     Фактическая сумма пришедшая на р/с
     * @param float  $pay          Сумма платежа вносимая на ЛС
     * @param string $pay_date_str Дата платежа
     * @param int    $pay_type_id  ID Типа платежа
     * @param string $description  Описание платежа
     * @return bool TRUE если такой платёж есть
     */
    public static function pay_has_exist(
            int     $abon_id,        //int     Абонент, на которого зачисляется поалёж
            float   $pay_fakt,       //float   Фактическая сумма пришедшая на p/c
            float   $pay,            //float   Сумма платежа вносимая на ЛС
            string  $pay_date_str,   //str     Дата платежа
            int     $pay_type_id,    //int     ID Типа платежа
            string  $description     //text    Описание платежа
            ) 
    {
        $model = new AbonModel();
        $sql = "SELECT `id` "
                . "FROM `payments` "
                . "WHERE "
                . "`abon_id`='".$abon_id."' AND "
                . "`pay_fakt`='".$pay_fakt."' AND "
                . "`pay`='".$pay."' AND "
                . "`pay_date`=UNIX_TIMESTAMP('".$pay_date_str."') AND "
                . "`pay_type_id`='".$pay_type_id."' AND "
            //. "`pay_ppp_id`='".$pay_ppp_id."' AND "
                . "`description` like '%". $model->quote(preg_replace('/\s+/', '%', trim($description)))."%' ";
        //echo $SQL."<hr>";
        return $model->get_count_by_sql($sql)>0;
    }



    /**
     * Добавление платежа на ЛС
     * @param int    $abon_id       Абонент, на которого зачисляется поалёж
     * @param float  $pay_fakt      Фактическая сумма пришедшая на р/с
     * @param float  $pay_acnt      Сумма платежа вносимая на ЛС
     * @param string $pay_date_str  Дата платежа
     * @param string $pay_bank_no   Банковский номер операции
     * @param int    $pay_type_id   ID Типа платежа
     * @param int    $ppp_id        ID ППП
     * @param string $description   Описание платежа
     */
    public static function pay_add(
            int    $abon_id,        //int     Абонент, на которого зачисляется поалёж
            float  $pay_fakt,       //float   Фактическая сумма пришедшая на р/с
            float  $pay_acnt,       //float   Сумма платежа вносимая на ЛС
            string $pay_date_str,   //str     Дата платежа
            string $pay_bank_no,    //tiny    Банковский номер операции
            int    $pay_type_id,    //int     ИД Типа платежа
            int    $ppp_id,         //int     ID ППП
            string $description,    //text    Краткое описание платежа
            ): int|false 
    {

        $model = new AbonModel();
        // Проверка чтобы такой записи небыло
        if(!self::pay_has_exist($abon_id, $pay_fakt, $pay_acnt, $pay_date_str, $pay_type_id, $description)) {
            // добавление новой записи
            $row = [
                Pay::F_AGENT_ID      => App::get_user_id(), 
                Pay::F_ABON_ID       => $abon_id, 
                Pay::F_PAY_FAKT      => $pay_fakt, 
                Pay::F_PAY_ACNT      => $pay_acnt, 
                Pay::F_DATE          => strtotime($pay_date_str), 
                Pay::F_BANK_NO       => $pay_bank_no, 
                Pay::F_TYPE_ID       => $pay_type_id, 
                Pay::F_PPP_ID        => $ppp_id, 
                Pay::F_DESCRIPTION   => $model->quote($description), 
                Pay::F_CREATION_DATE => time(), 
                Pay::F_CREATION_UID  => App::get_user_id(), 
                Pay::F_MODIFIED_DATE => time(), 
                Pay::F_MODIFIED_UID  => App::get_user_id(),
            ];

            $id = $model->insert_row(Pay::TABLE, $row);

            if ($id !== false ) {
                $model->price_apply_auto_ON($abon_id);
                $model->recalc_abon($abon_id);
            }
            return true;
        } else {
            MsgQueue::msg(MsgType::INFO_AUTO, "Такая запись в базе есть");
        }
        return false;
    }



    /**
     * Редактирование имеющейся записи
     * @param int $id
     * @param int $abon_id
     * @param float $pay_fakt
     * @param float $pay_acnt
     * @param string $pay_date_str
     * @param string $pay_bank_no
     * @param int $pay_type_id
     * @param int $pay_ppp_id
     * @param string $description
     * @return void
     */
    public static function pay_update(
            int    $id,             //int     ID записи в базе
            int    $abon_id,        //int     Абонент, на которого зачисляется поалёж
            float  $pay_fakt,       //float   Фактическая сумма пришедшая на счёт
            float  $pay_acnt,       //float   Сумма платежа вносимая на ЛС
            string $pay_date_str,   //str     Дата платежа
            string $pay_bank_no,    //tiny    Банковский номер операции
            int    $pay_type_id,    //int     ИД Типа платежа
            int    $pay_ppp_id,     //int     Изменить Изменить
            string $description     //text    Краткое описание платежа
            ) 
    {
        $model = new AbonModel();    
        $row[Pay::F_ID]          = $id;
        $row[Pay::F_AGENT_ID]    = App::get_user_id();
        $row[Pay::F_ABON_ID]     = $abon_id;
        $row[Pay::F_PAY_FAKT]    = $pay_fakt;
        $row[Pay::F_PAY_ACNT]    = $pay_acnt;
        $row[Pay::F_DATE]        = strtotime($pay_date_str);
        $row[Pay::F_BANK_NO]     = $pay_bank_no;
        $row[Pay::F_DESCRIPTION] = $model->quote($description);
        if ($model->validate_id( "payments_types", $pay_type_id, 'id')) {
            $row[Pay::F_TYPE_ID] = $pay_type_id;
        }
        if ($model->validate_ppp($pay_ppp_id)) {
            $row[Pay::F_PPP_ID]  = $pay_ppp_id;
        }
        $model->update_row_by_id(Pay::TABLE, $row, Pay::F_ID);
        $model->recalc_abon($abon_id);
    }



}