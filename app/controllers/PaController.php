<?php
/**
 *  Project : my.ri.net.ua
 *  File    : PaController.php
 *  Path    : app/controllers/PaController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 23 Oct 2025 01:11:27
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of PaController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\models\AbonModel;
use billing\core\Api;
use billing\core\App;
use billing\core\base\Model;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Module;
use config\tables\PA;
use config\tables\Price;
use config\tables\TP;
use DebugView;
use Valitron\Validator;
use config\SessionFields;

class PaController extends AppBaseController {

    /**
     * Нормализует входные данные формы перед сохранением
     */
    public static function normalize(array $data): array {

        // debug($data, '$data', die: 0);

        // 5️⃣ Проставляем флаги по умолчанию, если отсутствуют
        foreach (PA::FLAGS as $flag) {
            if (!isset($data[$flag])) {
                $data[$flag] = 0;
            }
        }

        /**
         * Если передан пустой F_TP_ID, то убираем его из обновления
         */
        if (isset($data[PA::F_TP_ID]) && ($data[PA::F_TP_ID] < 1)) {
            unset($data[PA::F_TP_ID]);
        }

        /**
         * Если передан флаг "ПФ Закрыт", то проверить поле date_end,
         * если поле пустое, то заполнить его сегодняшней датой.
         */
        if  ( $data[PA::F_CLOSED] == 1 ) 
        {
            if (empty($data[PA::F_DATE_END_STR])) {
                $data[PA::F_DATE_END_STR] = date('Y-m-d');
                MsgQueue::msg(MsgType::WARN, __('Дата закрытия ПФ была пустой. Установлена в сегодняшнюю. Проверьте правильность.'));
            }
            if ($data[PA::F_NET_IP_SERVICE] == 1) {
                $data[PA::F_NET_IP_SERVICE] = 0;
                MsgQueue::msg(MsgType::WARN, __('Флаг [IP_SERVICE] принудительно отключён в связи с закрытием прайсового фрагмента. Проверьте правильность.'));
            }
        }

        // debug($data, '$data', die: 1);

        // Инициализация результата
        $norm = [];

        foreach ($data as $key => $value) {
            // 1️⃣ Если флаг — установить 0 или 1
            if (in_array($key, PA::FLAGS, true)) {
                $norm[$key] = !empty($value) ? 1 : 0;
                continue;
            }

            // 2️⃣ Если числовое поле — привести к int (все числовые поля — целые)
            if (in_array($key, PA::NUM_TYPES, true)) {
                $norm[$key] = is_numeric($value) ? (int)$value : 0;
                continue;
            }

            // 3️⃣ Если строковое поле — обрезать пробелы
            if (in_array($key, PA::STR_TYPES, true)) {
                $norm[$key] = trim((string)$value);
                continue;
            }

            // 4️⃣ Остальное — копируем как есть
            $norm[$key] = $value;
        }

        if (isset($data[PA::F_DATE_START_STR])) {
            $norm[PA::F_DATE_START] = strtotime($data[PA::F_DATE_START_STR]);
            unset($norm[PA::F_DATE_START_STR]);
        }

        if (isset($data[PA::F_DATE_END_STR]) && ($data[PA::F_DATE_END_STR])) {
            $norm[PA::F_DATE_END] = strtotime($data[PA::F_DATE_END_STR]);
            unset($norm[PA::F_DATE_END_STR]);
        } else {
            $norm[PA::F_DATE_END] = null;
            unset($norm[PA::F_DATE_END_STR]);
        }

        return $norm;
    }

    /**
     * Проверка входных данных от формы
     */
    public static function validate(array $data): bool
    {
        $v = new Validator($data);

        // Правила проверки
        $v->rule('required', [
            PA::F_ABON_ID,
            PA::F_PRICE_ID,
            PA::F_NET_NAME,
            PA::F_DATE_START,
        ])->message('{field} — обязательное поле.');

        $v->rule('integer', [
            PA::F_ABON_ID,
            PA::F_PRICE_ID,
            PA::F_DATE_START,
        ]);

        $v->rule('lengthMax', PA::F_NET_NAME, 120);

        if ($data[PA::F_NET_IP_SERVICE]) {
            $v->rule('ip', [PA::F_NET_IP, PA::F_NET_ON_ABON_IP, PA::F_NET_GATEWAY])
                ->message('Поле {field} должно содержать корректный IP-адрес.');
        }

        // $v->rule('boolean', [PA::F_CLOSED, PA::F_NET_IP_SERVICE, PA::F_NET_IP_TRUSTED]);

        $v->rule('numeric', [PA::F_PPMA_VALUE, PA::F_PPDA_VALUE, PA::F_COST_VALUE]);

        // Проверка результата
        if (!$v->validate()) {
            MsgQueue::msg(MsgType::ERROR, $v->errors());
            return false;
        }
        return true;
    }



    public function editAction() {

        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        /**
         * Проверка наличия авторизации
         */
        if (!App::isAuth()) {   
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect('/');
        }

        /**
         * Проверка прав на редактирование
         */
        if (!can_edit(Module::MOD_PA)) {   
            MsgQueue::msg(MsgType::ERROR, __('Нет прав'));
            redirect();
        }

        $model = new AbonModel();

        /**
         * Редактирование данных
         */
        if (isset($_POST[PA::POST_REC]) && is_array($_POST[PA::POST_REC])) {
            // нормализация данных
            $data = self::normalize($_POST[PA::POST_REC]);
            // Предыдущая запись в базе для сравнения и возврата
            $pa = $model->get_pa($data[PA::F_ID]);
            // debug($data, '$data', debug_view: DebugView::DUMP, die: 0);
            // debug($data, '$data', die: 1);
            // Валидация
            if (!self::validate($data)) {
                $_SESSION[SessionFields::FORM_DATA] = $data;
            } else {
                $data = Model::get_modified($data, $pa, PA::F_ID);
                if ($data) {
                    /**
                     * Данные есть. Вносить в базу
                     */
                    if ($model->update_row_by_id(PA::TABLE, $data, PA::F_ID)) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, __("Данные внесены"));
                        // MsgQueue::msg(MsgType::SUCCESS, $data);
                    } else {
                        MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                    }
                } else {
                    /**
                     * Данных нет. Просто сообщить
                     */
                    MsgQueue::msg(MsgType::INFO_AUTO, __("нет изменений в данных"));
                }

            }
            redirect(PA::URI_EDIT . '/' . $pa[PA::F_ID]);
        }



        $pa_id = intval($this->route[F_ALIAS]);

        if (!$model->validate_id(PA::TABLE, $pa_id, PA::F_ID)) {   
            MsgQueue::msg(MsgType::ERROR, __('Не верный ID'));
            redirect();
        }

        $pa = $model->get_row_by_id(PA::TABLE, $pa_id, PA::F_ID);

        $tp = $model->get_tp($pa[PA::F_TP_ID]);

        $arp = null;
        if  (
                $tp[TP::F_STATUS] &&                    // ТП активна
                $tp[TP::F_IS_MANAGED] &&                // ТП управляемая
                ($pa[PA::F_NET_IP_SERVICE] == 1) &&     // это IP услуга
                !empty($pa[PA::F_NET_IP]) &&            // IP-адрес указан
                validate_ip($pa[PA::F_NET_IP])      // IP-адрес валидный
            ) 
        {
            /**
             * Получение данных с микротика
             * Запись из таблицы ARP микротика со статусом IP-адреса
             */
            $mik = Api::tp_connector(tp: $tp);
            if ($mik !== false) {
                /**
                 * Соединение с миротиком установлено
                 */
                $arp = Api::get_mac_from_arp_by_ip(
                    $mik,
                    $pa[PA::F_NET_IP], 
                    true);
            } else {
                MsgQueue::msg(MsgType::ERROR, Api::$errors);
            }
        }


        $prices_list = array_column(
                array: $model->get_rows_by_sql("SELECT `".Price::F_ID."`, `".Price::F_TITLE."` FROM `".Price::TABLE."` WHERE (`".Price::F_ACTIVE."`=1) ORDER BY `".Price::TABLE."`.`".Price::F_TITLE."` ASC"),
                column_key: Price::F_TITLE,
                index_key: Price::F_ID);

        $tp_list = array_column(
                array: $model->get_my_tp_list(status: 1),
                column_key: TP::F_TITLE,
                index_key: TP::F_ID);

        View::setMeta(title: __('Редактирование прайсового фрагмента'));
        $this->setVariables([
            'pa'=> $pa,
            'tp'=> $tp,
            'arp'=> $arp,
            'prices_list'=> $prices_list,
            'tp_list'=> $tp_list,
        ]);
    }





}