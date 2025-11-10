<?php
/*
 *  Project : my.ri.net.ua
 *  File    : ConciliationController.php
 *  Path    : app/controllers/ConciliationController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;


use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Conciliation;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\User;

/**
 * Description of ConciliationController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class ConciliationController extends AbonController {


    
    function indexAction() {

        if (!App::$auth->isAuth) { redirect(); }

        $model = new AbonModel();

        if (isset($this->route[F_ALIAS])) {
            if ($model->validate_id(Abon::TABLE, intval($this->route[F_ALIAS]), Abon::F_ID)) {
                /**
                 * Это верный abon_id
                 */
                $user_id = $model->get_user_id_by_abon_id(intval($this->route[F_ALIAS]));
            } elseif ($model->validate_id(User::TABLE, intval($this->route[F_ALIAS]), User::F_ID)) {
                /**
                 * Это верный user_id
                 */
                $user_id = intval($this->route[F_ALIAS]);
            }
        } else {
            $user_id = App::get_user_id();
        }

        /**
         * Мой ли это user_id ?
         */
        if ($user_id == App::get_user_id()) {
            /**
             * Это мой user_id
             */
            if (!can_view(Module::MOD_MY_CONCILIATION)) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
        } else {
            /**
             * Это чужой user_id
             */
            if (!can_view(Module::MOD_CONCILIATION)) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
        }

        $user = $model->get_user($user_id);
        $user[Abon::TABLE] = $model->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user[User::F_ID]);

        $this->setVariables([
            'user' => $user,
        ]);

        View::setMeta(title: __('Запрос на Акт сверки платежей'));

    }



    function intervalAction(){

        if (!App::$auth->isAuth) { redirect(); }

        $model = new AbonModel();

        if (isset($this->route[F_ALIAS])) {
            if ($model->validate_id(Abon::TABLE, intval($this->route[F_ALIAS]), Abon::F_ID)) {
                /**
                 * Верный abon_id
                 */
                $abon_id = intval($this->route[F_ALIAS]);
            } else {
                /**
                 * Ошибочный abon_id
                 */
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Не верный ID'));
                redirect();
            }
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Не укащан ID'));
            redirect();
        }

        /**
         * Мой ли это user_id ?
         */
        $user_id = $model->get_user_id_by_abon_id($abon_id);
        if ($user_id == App::get_user_id()) {
            /**
             * Это мой user_id
             */
            if (!can_view(Module::MOD_MY_CONCILIATION)) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
        } else {
            /**
             * Это чужой user_id
             */
            if (!can_view(Module::MOD_CONCILIATION)) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Нет прав'));
                redirect();
            }
        }

        $user = $model->get_user($user_id);
        $abon = $model->get_abon($abon_id);

        $this->setVariables([
            'user' => $user,
            'abon' => $abon,
        ]);

        View::setMeta(title: __('Выбор интервала Акта сверки платежей'));

    }



    function printAction() {

        $model = new AbonModel();

        $user = array();
        if (is_numeric($this->route[F_ALIAS])) {
            if (!App::$auth->isAuth) {
                MsgQueue::msg(MsgType::ERROR, __('Для данного действия требуется авторизация'));
                redirect('/');
            }
            $user = $model->get_user_by_abon_id((int)$this->route[F_ALIAS]);
            if ($_SESSION[User::SESSION_USER_REC][User::F_ID] != $user[User::F_ID]) {
                MsgQueue::msg(MsgType::ERROR, __('Вы запрашиваете чужой документ'));
                redirect('/');
            }
            $abon = $model->get_abon((int)$this->route[F_ALIAS]);

        } else {
            $abon_hash = $this->route[F_ALIAS];
            $abon = $model->get_abon_by_hash($abon_hash);
            $user = $model->get_user($abon[Abon::F_USER_ID]);
        }



//        debug($_GET, '$_GET', die: 0);
//        debug($this->route, '$this->route', die: 0);



        $A = $abon;
        $U = $user;


        if(isset($_GET[Conciliation::F_DATE1_STR])) {
            $date1 = strtotime($_GET[Conciliation::F_DATE1_STR]);
        } else {
            $date1 = 0;
        }

        if(isset($_GET[Conciliation::F_DATE2_STR])) {
            $today = strtotime($_GET[Conciliation::F_DATE2_STR]);
        } else {
            $today = last_day_month();
        }



        $events = array();
        // $index = 0;
        $pa_list = $model->get_prices_apply_by_abon($A[Abon::F_ID]);
//        debug($pa_list, '$prices', die: 1);
        foreach ($pa_list as $pa) {
            $struct = AbonModel::get_price_apply_cost_per_montch($pa, $today);
//            debug($struct, '$struct', die: 1);
            foreach ($struct as $rec) {
                $events[] = $rec;
            }
            unset($rec);
        }

//        debug($events, '$events', die: 1);

        $pays = $model->get_payments($A[Abon::F_ID]);

        foreach ($pays as $p) {
            if(get_date($p[Pay::F_DATE]) <= $today) {
                $rec['date']     = get_date($p[Pay::F_DATE]);
                $rec['pay_fakt'] = $p[Pay::F_PAY_FAKT];
                if($p[Pay::F_TYPE_ID] == Pay::TYPE_MONEY) {
                    $rec['pay']      = $p[Pay::F_PAY_ACNT];
                    $rec['cost']     = 0;
                } else {
                    $rec['pay']      = 0;
                    $rec['cost']     = -$p[Pay::F_PAY_ACNT];
                }
                $events[]        = $rec;
            } else {
                break;
            }
        }


        //
        // сортировка событий по дате
        //
        for ($x = 0; $x < count($events); $x++) {
            for ($y = 0; $y < count($events); $y++) {
                if($events[$x]['date'] < $events[$y]['date']) {
                    $e          = $events[$x];
                    $events[$x] = $events[$y];
                    $events[$y] = $e;
                }
            }
        }

        //
        // добавление строкового поля даты
        // для отладки
        //
        for ($x = 0; $x < count($events); $x++) {
            $events[$x]['date_str'] =  date("Y-m-d", $events[$x]['date']);
        }



        //
        // Упаковка таблицы по месяцам
        //
        $months         = array();
        $current_month  = month($events[0]['date']);
        //echo "Первый месяц: ".$current_month."<br>";
        $record['date'] = mktime(0, 0, 0, month($events[0]['date']), 1, year($events[0]['date']));
        $record['date_str'] = date("Y-m-d", $record['date']);
        //echo "дата: ".$record['date']." : ". date("Y-m-d H:i:s", $record['date'])."<br>";
        $record['cost'] = 0;
        $record['pay']  = 0;

        //echo "<pre>events[0]:".print_r($events[0], true)."</pre>";

        for ($index = 0; $index < count($events); $index++) {
            //echo "index $index : ".$current_month." == ".month($events[$index]['date'])."<br>";
            if($current_month == month($events[$index]['date'])) {
                $record['cost'] = $record['cost'] + (isset($events[$index]['cost'])?$events[$index]['cost']:0);
                $record['pay']  = $record['pay']  + (isset($events[$index]['pay'])?$events[$index]['pay']:0);
            } else {
                $months[] = $record;
                unset($record);
                $current_month = month($events[$index]['date']);
                $record['date'] = mktime(0, 0, 0, month($events[$index]['date']), 1, year($events[$index]['date']));
                $record['date_str'] = date("Y-m-d", $record['date']);
                $record['cost'] = (isset($events[$index]['cost'])?$events[$index]['cost']:0);
                $record['pay']  = (isset($events[$index]['pay'])?$events[$index]['pay']:0);
            }
        }
        $months[] = $record;
        unset($record);

//        debug($months, '$months', die: 0);




        //
        //  Предприятия клиента
        //
        $contragents = $model->get_firms_by_uid_cli($U[User::F_ID]);
        if(empty($contragents)) {
            $contragents[0]['name_long'] = $U[User::F_NAME_FULL];
            $contragents[0]['name_short'] = $U[User::F_NAME_SHORT];
        }

        //
        // Препдирятия агента
        //
        $agents = $model->get_agents_by_abon_id($abon[Abon::F_ID]);
        //echo "<pre>AGENTS:".print_r($agents, true)."</pre><hr>";
        if(count($agents) == 0) {
            $agents = $model->get_agents_by_abon_id_all($abon[Abon::F_ID]);
        }

        if($date1 == 0) {
            $date1 = $months[0]['date'];
        }


        $this->setVariables([
            'date1' => $date1,
            'today' => $today,
            'events' => $events,
            'months' => $months,
            'user' => $user,
            'abon' => $abon,
            'contragents' => $contragents,
            'agents' => $agents,
        ]);




        View::setMeta(title: __('Акт звіряння розрахунків по договору %s', $A[Abon::F_ID]) . ' ' . __('за період') . ' ' . date("d.m.Y", $date1) . ' - ' . date("d.m.Y", $today));


//        debug($this->layout, '$this->layout');
//        debug($this->view, '$this->view', die: 1);

        $this->layout  = 'print';
    }

}