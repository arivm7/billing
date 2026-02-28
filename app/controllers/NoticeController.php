<?php
/**
 *  Project : my.ri.net.ua
 *  File    : NoticeController.php
 *  Path    : app/controllers/NoticeController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 28 Nov 2025 22:00:59
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of NoticeController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\Controllers;

use app\controllers\AppBaseController;
use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Notice;
use config\tables\Abon;
use config\tables\AbonRest;
use config\tables\Module;
use config\tables\Notify;
use config\tables\PA;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\TP;
use config\tables\User;



require_once DIR_LIBS . '/sms_functions.php';



class NoticeController extends AppBaseController
{



    public function indexAction()
    {
        redirect('/');
    }


    
    public function infoAction()
    {

        /**
         * Проверка наличия авторизации
         */
        if (!App::isAuth()) {   
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        /**
         * Проверка прав
         */
        if (!can_view(Module::MOD_NOTICE)) {   
            MsgQueue::msg(MsgType::ERROR, __('Нет прав'));
            redirect();
        }

        $model = new AbonModel();

        $abon_id = $this->route[F_ALIAS] ?? 0;
        if (!$model->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('ID абонента не верен'));
            redirect();
        }

        $abon = $model->get_abon($abon_id);
        $user = $model->get_user($abon[Abon::F_USER_ID]);

        /**
         * Регистрация сообщений
         */
        if (isset($_POST[Notify::POST_REC]) && is_array($_POST[Notify::POST_REC])) {
            $post_rec = $_POST[Notify::POST_REC];
            if ($abon_id !=  $_POST[Notify::POST_REC][Notify::F_ABON_ID]) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Не верные данные')); // !!! нужно логировать. Возможно вмешательство
                redirect();
            }
            foreach ($post_rec['msg'] as $notice_rec) {
                if (empty($notice_rec['register'])) { continue; }
                $notice = [
                    Notify::F_ABON_ID => $abon_id,
                    Notify::F_TYPE_ID => Notify::TYPE_SMS,
                    Notify::F_PHONENUMBER => $user[User::F_PHONE_MAIN],
                    Notify::F_METHOD => Notify::METHOD_KDE_CONNECT,
                    Notify::F_DATE => time(),
                    Notify::F_TEXT => $notice_rec['text'],
                ];
                if (Notify::save($notice)) {
                    MsgQueue::msg(MsgType::SUCCESS, __('Сообщение зарегистрировано'));
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Ошибка регистрации сообщения') . ': ' . $model->errorInfo());
                }
                redirect();
            }
        }


        $rest = $model->get_abon_rest($abon_id);
        $pa_list = $model->get_pa_active_or_last($abon_id); // нужна проверка: может быть пустым
        if (!empty($pa_list)) {
            $ppp_list = $model->get_ppp_for_pay(from_pa_list: $pa_list);
        } else {
            $ppp_list = [];
        }
        // debug($rest, '$rest');
        // debug($ppp_list, '$ppp_list');

        $title = __('Информация для отправки абоненту') . ' ' . $abon[Abon::F_ID];
        View::setMeta($title);
        $this->setVariables([
            'title' => $title,
            'abon' => $abon,
            'user' => $user,
            'rest' => $rest,
            'pa_list' => $pa_list,
            'ppp_list' => $ppp_list,
        ]);
    }



    public function smsAction() {

        // debug($_POST, '_post');

        $model = new AbonModel();

        $tp_list        = $model->get_tp_list(status: 1);

        /**
         * Флаг установки фильтра. 
         * Если его нет, то нечего отрисовывать
         */
        $filter_set     = isset($_POST[Notify::FLTR_PREFIX]) && is_array($_POST[Notify::FLTR_PREFIX]);

        /**
         * Флаг для генерации скрипта на основе выбранных строк
         */
        $do_script_show = (intval($_POST['do_script_show'] ?? 0) == 1);

        /**
         * Список ID абонентов для которых нужно отправлять СМС. Передаётся из формы списка.
         */
        $abon_id_list   = (isset($_POST[Notify::FLTR_ABON_ID_LIST]) && is_array($_POST[Notify::FLTR_ABON_ID_LIST]) 
                            ?   array_keys($_POST[Notify::FLTR_ABON_ID_LIST])
                            :   []
                          );

        // debug($abon_id_list, '$abon_id_list');

        $selected_tp_id = ((isset($_POST[Notify::FLTR_PREFIX][Notify::FLTR_TP_ID]) && (intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_TP_ID]) > 0)) 
                            ? intval(h($_POST[Notify::FLTR_PREFIX][Notify::FLTR_TP_ID]))
                            : 0);

        $abon_id        = (isset($_POST[Notify::FLTR_PREFIX][Notify::FLTR_ABON_ID]) && ($model->validate_id(Abon::TABLE, intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_ABON_ID]), Abon::F_ID))
                            ?   intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_ABON_ID])
                            :   0
                          );

        /**
         * Показывать абонентов на паузе -- 1 | 0 
         */
        $show_paused    = (isset($_POST[Notify::FLTR_PREFIX]) && is_array($_POST[Notify::FLTR_PREFIX])
                            ? intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_SHOW_PAUSED] ?? 0)
                            : App::get_config('sms_filer_show_paused')
                          ); 

        /**
         * Не отправлять тем, кому уже отправляли до указанного количества дней тому
         */
        $not_send_days  = (isset($_POST[Notify::FLTR_PREFIX][Notify::FLTR_NOT_SEND_DAYS]) 
                            ?   intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_NOT_SEND_DAYS])
                            :   App::get_config('sms_filer_not_send_days')
                          );

        $not_send_date  = TODAY() - $not_send_days * 86400; // 86400 seconds in a day

        /**
        * Не отправлять тем, кто уже оплачивал до N дней тому
        */
        $not_pay_days   = (isset($_POST[Notify::FLTR_PREFIX][Notify::FLTR_NOT_PAY_DAYS]) 
                            ?   intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_NOT_PAY_DAYS])
                            :   App::get_config('sms_filer_not_pay_days')
                          );

        $not_pay_date   = TODAY() - $not_pay_days * 86400; // 86400 seconds in a day

        /**
         * Максимальное количество СМС для рассылки
         * 0 -- автоматически: 
         *      выбираются все абоненты которым нужно отправлять уведомления, 
         *      с учётом фильтров $not_send_date и $not_pay_date, 
         *      без ограничения количества
         */
        $max_count_sms  = (isset($_POST[Notify::FLTR_PREFIX][Notify::FLTR_MAX_COUNT]) 
                            ?   intval($_POST[Notify::FLTR_PREFIX][Notify::FLTR_MAX_COUNT])
                            :   App::get_config('sms_filer_max_count_sms')
                          );

        $lines = array();

        /**
         * Если фильтер установлен, то делаем выборку
         */
        if ($filter_set) {

            $sql = "SELECT 
                    `abons`.`id` AS abon_id,
                    `users`.`id` AS user_id,
                    `abons`.`address`,
                    `abons`.`duty_max_warn`,
                    `abons`.`duty_max_off`,
                    `abons`.`duty_auto_off`,
                    `users`.`name`,
                    `users`.`name_short`,
                    `users`.`phone_main`

                    FROM `abons` 
                    LEFT JOIN `users` ON `users`.`id` = `abons`.`user_id`
                    WHERE 
                        `users`.`do_send_sms`=1 AND
                        `abons`.`is_payer`=1 AND
                        `abons`.`id` IN (
                            SELECT `abon_id` FROM `prices_apply` 
                            WHERE 
                                ".($selected_tp_id ? " (prices_apply.net_router_id=".$selected_tp_id.") AND " : "")."
                                ".($abon_id ? " (prices_apply.abon_id=".$abon_id.") AND " : "")."
                                `price_closed`=0 AND 
                                `net_router_id` IN (
                                    SELECT `tp_list`.`id` FROM `tp_list` WHERE `status`=1 AND `id` IN (
                                        SELECT `tp_id` FROM `ts_user_tp` WHERE `user_id`=". App::get_user_id()."
                                    )
                                )
                            GROUP BY `abon_id`
                        )";
            // echo "SQL: ".$sql."<br />";
            $rows = $model->get_rows_by_sql($sql);
            MsgQueue::msg(MsgType::INFO, __("Всего получено строк") . ": ".count($rows).".");



            if ($rows) {

                /**
                 * Формируем список записей для уведомлений
                 */
                foreach ($rows as $row) {
                    $row[AbonRest::TABLE] = $model->get_abon_rest($row['abon_id']);
                    if  (
                            (
                                ($show_paused) || 
                                ($row[AbonRest::TABLE][AbonRest::F_SUM_PP30A] > 0)
                            ) 
                            &&
                            (
                                ($row[AbonRest::TABLE][AbonRest::F_PREPAYED] <= ($row[Abon::F_DUTY_MAX_WARN] + 1))
                            )
                        )
                    {
                        $row['last_sms'] = $model->get_notify_last($row['abon_id']);
                        $row['last_pay'] = $model->get_payment_last($row['abon_id']);
                        $row['do_send'] = 0;
                        $lines[] = $row;
                        // if ($row['abon_id'] == 19595) {
                        //     debug($row, '$row');
                        // }
                    }
                    // else
                    // {
                    //     if ($row[AbonRest::TABLE][AbonRest::F_SUM_PP30A] == 0) {
                    //         debug($row, '$row');
                    //     }
                    //     else {
                    //         echo $row['abon_id'] . '<br>';
                    //     }
                        
                    // }
                }
                unset($rows);



                $count = count($lines);
                for ($x = 0; $x < $count-1; $x++) {
                    for ($y=$x+1; $y < $count; $y++) {
                        if (compare_abons($lines[$x], $lines[$y]) > 0)
                        {
                            $item = $lines[$x];
                            $lines[$x] = $lines[$y];
                            $lines[$y] = $item;
                        }
                    }
                }
                MsgQueue::msg(MsgType::INFO, __("Отсортировано строк") . ": $count. Ок");


                $COUNT_SELECTED = 0;

                /**
                 * Отмечаем строки для отправки СМС
                 */
                foreach ($lines as &$line) {

                    if ($do_script_show) {

                        /**
                         * Если команда генерировать скрипт, то отмечаем только те строки, 
                         * которые были переданы в POST-запросе из формы списка,
                         * для которых нужно генерировать скрипт. Остальные строки не отмечаем.
                         */
                        $line['do_send'] = in_array($line['abon_id'], $abon_id_list) ? 1 : 0;

                    } else {

                        /**
                         * Автоматически отмечаем строки
                         */
                        if  (
                                ($max_count_sms == 0) || ($COUNT_SELECTED < $max_count_sms)
                            ) 
                        {
                            $line['do_send'] =
                                (
                                    (
                                        /**
                                         * Условие для отправки уведомления:
                                         * 1. Не отправлять тем, кому уже отправляли до указанного количества дней тому -- $not_send_date
                                         */
                                        (empty($line['last_sms']) || ($line['last_sms'][Notify::F_DATE] < $not_send_date)) &&
                                        /**
                                         * 2. Не отправлять тем, кто уже оплачивал до N дней тому -- $not_pay_date
                                         */
                                        (empty($line['last_pay']) || ($line['last_pay'][Pay::F_DATE] < $not_pay_date)) &&
                                        /**
                                         * 3. Отправлять тем, у кого предоплаченные дни меньше или равны границе предупреждения -- $line[AbonRest::TABLE][AbonRest::F_PREPAYED] <= $line[Abon::F_DUTY_MAX_WARN]
                                         */
                                        ($line[AbonRest::TABLE][AbonRest::F_PREPAYED] <= $line[Abon::F_DUTY_MAX_WARN]) &&
                                        /**
                                         * 4. Отправлять тем, у кого есть абонплата -- $line[AbonRest::TABLE][AbonRest::F_SUM_PP30A] > 0
                                         */
                                        ($line[AbonRest::TABLE][AbonRest::F_SUM_PP30A] > 0)
                                    )
                                    ? 1
                                    : 0
                                );
                        }
                    }
                    if ($line['do_send']) { $COUNT_SELECTED++; }

                }
                MsgQueue::msg(MsgType::INFO, __("Отмечено для отправки уведомлений") . ": ".$COUNT_SELECTED);

            }

        }


        $title = __('Генерация скрипта СМС-рассылки');
        View::setMeta($title);
        $this->setVariables([
            'title' => $title,
            'filter_set' => $filter_set,
            'tp_list' => $tp_list,
            'selected_tp_id' => $selected_tp_id,
            'abon_id' => $abon_id,
            'show_paused' => $show_paused,
            'not_send_days' => $not_send_days,
            'not_pay_days' => $not_pay_days,
            'max_count_sms' => $max_count_sms,
            'do_script_show' => $do_script_show,
            'lines' => $lines,
        ]);

    }




}