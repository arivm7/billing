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
use billing\core\MikrotikDevice;
use config\AutoCorrect;
use config\tables\Module;
use config\tables\PA;
use config\tables\Price;
use config\tables\TP;
use config\Mik;
use DebugView;
use Valitron\Validator;
use config\SessionFields;
use config\tables\Abon;
use PAStatus;

class PaController extends AppBaseController {

    /**
     * Нормализует входные данные формы перед сохранением
     */
    public static function normalize(array $data): array {

        // 5️⃣ Проставляем флаги по умолчанию, если отсутствуют
        foreach (PA::FLAGS as $flag) {
            if (!isset($data[$flag])) {
                $data[$flag] = 0;
            }
        }

        /**
         * Если передан пустой F_PRICE_ID, то убираем его из обновления
         */
        if (isset($data[PA::F_PRICE_ID]) && ($data[PA::F_PRICE_ID] < 1)) {
            unset($data[PA::F_PRICE_ID]);
        }

        /**
         * Если передан пустой F_TP_ID, то убираем его из обновления
         */
        if (isset($data[PA::F_TP_ID]) && ($data[PA::F_TP_ID] < 1)) {
            unset($data[PA::F_TP_ID]);
        }

        /**
         * Если передан пустой F_NET_NAME, то генерируем текст
         */
        if (empty($data[PA::F_NET_NAME])) {
            $model = new AbonModel();
            $abon_address = trim($model->get_abon_address($data[PA::F_ABON_ID]));
            $data[PA::F_NET_NAME] =
                $data[PA::F_ABON_ID]
                . ($abon_address ? ' ' . $abon_address : '')
                . (($data[PA::F_NET_IP_SERVICE] == 1) && !empty($data[PA::F_NET_IP])
                    ? ' | ' . $data[PA::F_NET_IP].'/'.\ip_mask_to_prefix($data[PA::F_NET_MASK]).'/.'.ip_get_last_octet($data[PA::F_NET_GATEWAY])
                    : '');
            MsgQueue::msg(MsgType::WARN, __('F_NET_NAME was empty. Set to F_ABON_ID and connection parameters. Check for correctness | F_NET_NAME был пуст. Установлен в F_ABON_ID и параметры подключения. Проверьте правильность | F_NET_NAME був порожній. Встановлено в F_ABON_ID та параметри підключення. Перевірте правильність') .'.');
        }

        /**
         * Если передан флаг "ПФ Закрыт", то проверить поле date_end,
         * если поле пустое, то заполнить его сегодняшней датой.
         */
        if  ( $data[PA::F_CLOSED] == 1 ) 
        {
            if (empty($data[PA::F_DATE_END_STR])) {
                $data[PA::F_DATE_END_STR] = date('Y-m-d');
                MsgQueue::msg(MsgType::WARN, __('The PF closing date was empty. Installed today. Check for correctness | Дата закрытия ПФ была пустой. Установлена в сегодняшнюю. Проверьте правильность | Дата закриття ПФ була порожньою. Встановлено у сьогоднішню. Перевірте правильність') . '.');
            }
            if ($data[PA::F_NET_IP_SERVICE] == 1) {
                $data[PA::F_NET_IP_SERVICE] = 0;
                MsgQueue::msg(MsgType::WARN, __('The [IP_SERVICE] flag is forcibly disabled due to the closing of the price fragment. Check for correctness | Флаг [IP_SERVICE] принудительно отключён в связи с закрытием прайсового фрагмента. Проверьте правильность | Флаг [IP_SERVICE] примусово вимкнено у зв\'язку із закриттям прайсового фрагмента. Перевірте правильність') . '.');
            }
        }

        /**
         * Автозамены
         */
        foreach (PA::AUTOREPLACES as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = AutoCorrect::correct($data[$field]);
            }
        }

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
            PA::F_NET_NAME,
            PA::F_DATE_START,
        ])->message('{field} — '.__('required field | обязательное поле | обов\'язкове поле').'.');

        $v->rule('integer', [
            PA::F_ABON_ID,
            PA::F_PRICE_ID,
            PA::F_DATE_START,
        ]);

        $v->rule('lengthMax', PA::F_NET_NAME, 120);

        if ($data[PA::F_NET_IP_SERVICE]) {
            $v->rule('ip', [PA::F_NET_IP, PA::F_NET_ON_ABON_IP, PA::F_NET_GATEWAY])
                ->message('{field} -- '.__('The field must contain a valid IP address | Поле должно содержать корректный IP-адрес | Поле має містити коректну IP-адресу').'.');
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



    /**
     * Если присутствуют поля, указанные в списке, то требуется пересчет стоимости фрагментов
     * @param array $data -- обновляемые поля записи ПФ
     * @return bool
     */
    public static function need_recalc_cost(array $data): bool {
        foreach (PA::NEED_RECALC_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }
        return false;
    }



    public static function clone(int|null $pa_id = null, array|null $pa = null): int|false {
        $model = new AbonModel();
        if (empty($pa)) { 
            $pa = $model->get_pa($pa_id); 
        }

        unset($pa[PA::F_ID]);
        $pa[PA::F_DATE_START] = TODAY();
        $pa[PA::F_DATE_END] = null;
        $pa[PA::F_CREATION_UID] = App::get_user_id();
        $pa[PA::F_CREATION_DATE] = time();
        $pa[PA::F_MODIFIED_UID] = App::get_user_id();
        $pa[PA::F_MODIFIED_DATE] = time();

        $pa_new_id = $model->insert_row(PA::TABLE, $pa);
        if ($pa_new_id) {
            return $pa_new_id;
        } else {
            return false;
        }
    }



    /**
     * 1. Закрытие текущего прайсового фрагмента (установка даты закрытия)
     * 2. Создание нового прайсового фрагмента с параметрами текущего
     * 3. Новый фрагмент начинается с указанной даты
     * 4. В новом фрагменте указывается новый прайс и новая техплощадка.
     * 5. Фактическая услуга на устройстве не меняется.
     * @param int $pa_id            -- ID текущего ПФ
     * @param string $on_date_str   -- Дата вступления в силу нового ПФ (формат 'YYYY-MM-DD')
     * @param int $to_price_id      -- ID нового прайса
     * @param int $to_tp_id         -- ID новой техплощадки
     * @return int|false            -- ID нового ПФ или false при ошибке
     */
    public static function change_price(
            int $pa_id, 
            string $on_date_str, 
            int $to_price_id, 
            int $to_tp_id): int|false 
    {

        // debug(
        //     [
        //         'pa_id' => $pa_id,
        //         'on_date_str' => $on_date_str,
        //         'to_price_id' => $to_price_id,
        //         'to_tp_id' => $to_tp_id,
        //     ],
        //     'PaController::change_price input',
        //     debug_view: DebugView::DUMP,
        //     die: 0
        // );

        // debug(
        //     $_GET,
        //     '$_GET',
        //     debug_view: DebugView::DUMP,
        //     die: 0
        // );

        $model = new AbonModel();

        /**
         * Проверка исходного ПФ
         */
        $pa = $model->get_pa($pa_id); 
        if (empty($pa)) {
            MsgQueue::msg(MsgType::ERROR, __('Price fragment with ID not found | Не найден прайсовый фрагмент с ID | Не знайдено прайсового фрагмента з ID') . ": $pa_id");
            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
            return false;
        }

        /**
         * Текущий ПФ должен быть открыт (без даты закрытия)
         */
        if (!empty($pa[PA::F_DATE_END])) {
            MsgQueue::msg(MsgType::ERROR, "ПФ с ID: $pa_id уже закрыт. Смена прайса невозможна.");
            return false;
        }

        /**
         * Проверка даты начала нового ПФ
         */
        $on_date_start = strtotime($on_date_str);
        if ($on_date_start === false) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid date format | Не верный формат даты | Неправильний формат дати') . ": $on_date_str");
            return false;
        }
        if ($on_date_start <= $pa[PA::F_DATE_START]) {
            MsgQueue::msg(MsgType::ERROR, __('The switch date must be greater than the start date of the current price fragment | Дата переключения должна быть больше даты начала текущего прайсового фрагмента | Дата перемикання повинна бути більше дати початку поточного прайсового фрагмента'));
            return false;
        }

        /**
         * Проверка даты закрытия старого ПФ
         */
        $on_date_end = $on_date_start - 60*60*24; // минус один день
        if ($on_date_end <= $pa[PA::F_DATE_START]) {
            MsgQueue::msg(MsgType::ERROR, __('The closing date of the current price fragment must be greater than the opening date of the current price fragment | Дата закрытия текущего прайсового фрагмента должна быть больше даты открытия текущего прайсового фрагмента | Дата закриття поточного прайсового фрагмента має бути більшою за дати відкриття поточного прайсового фрагменту'));
            return false;
        }

        /**
         * Проверка нового прайса
         */
        $price = $model->get_price($to_price_id); 
        if (empty($price)) {
            MsgQueue::msg(MsgType::ERROR, "Не найден прайс с ID: $to_price_id");
            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
            return false;
        }

        /**
         * Проверка ТП
         */
        $tp = $model->get_tp($to_tp_id); 
        if (empty($tp)) {
            MsgQueue::msg(MsgType::ERROR, "Не найден ТП с ID: $to_tp_id");
            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
            return false;
        }

        /**
         * Создание нового ПФ с новыми параметрами
         */
        unset($pa[PA::F_ID]);
        $pa[PA::F_DATE_START] = $on_date_start;
        $pa[PA::F_DATE_END] = null;
        $pa[PA::F_PRICE_ID] = $to_price_id;
        $pa[PA::F_TP_ID] = $to_tp_id;
        $pa[PA::F_CREATION_UID] = App::get_user_id();
        $pa[PA::F_CREATION_DATE] = time();
        $pa[PA::F_MODIFIED_UID] = App::get_user_id();
        $pa[PA::F_MODIFIED_DATE] = time();
        $pa_new_id = $model->insert_row(PA::TABLE, $pa);
        if ($pa_new_id === false) {
            MsgQueue::msg(MsgType::ERROR, __('Error creating a new price fragment | Ошибка создания нового прайсового фрагмента | Помилка створення нового прайсового фрагменту'));
            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
            return false;
        }
        MsgQueue::msg(MsgType::SUCCESS, "Создан новый ПФ с ID: $pa_new_id");
        
        /**
         * Закрытие текущего ПФ установкой даты закрытия
         */
        if ($model->update_row_by_id(
            PA::TABLE, 
            [
                PA::F_ID => $pa_id, 
                PA::F_DATE_END => $on_date_end,
                PA::F_MODIFIED_UID => App::get_user_id(),
                PA::F_MODIFIED_DATE => time(),
            ], 
            PA::F_ID)) 
        {
            MsgQueue::msg(MsgType::SUCCESS, "Текущий ПФ с ID: $pa_id закрыт датой: ".date('Y-m-d', $on_date_end));
            return $pa_new_id;
        } else {
            MsgQueue::msg(MsgType::ERROR, "Ошибка закрытия текущего ПФ с ID: $pa_id");
            MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
            return false;
        };
    }



    /**
     * Включает или выключает услугу (прайсовый фрагмент) абонента:
     * на управляемом Mikrotik (address-list) и в биллинге (закрытие/открытие
     * прайсового фрагмента PA).
     *
     * Порядок операций: сначала пытаемся применить изменение на устройстве
     * (если ТП управляемый), и только при успехе — фиксируем изменение в БД.
     * Это защищает от ситуации "в БД отключено, а на роутере всё ещё работает".
     * ВНИМАНИЕ: обратная защита отсутствует — см. пометку у update_row_by_id() ниже:
     * если устройство изменить удалось, а запись в БД не удалась, состояние
     * останется рассинхронизированным (устройство уже переключено, БД — нет).
     *
     * @param int|null    $pa_id  ID прайсового фрагмента (используется, если $pa не передан)
     * @param array|null  $pa     Уже загруженная запись прайсового фрагмента (приоритетнее $pa_id)
     * @param bool|int    $ena    true/1 — включить услугу, false/0 — выключить
     * @param int|bool    $force  true/1 — при включении игнорировать давность паузы
     *                            и не клонировать прайсовый фрагмент (см. ниже)
     * @param string      &$log   Параметр по ссылке: сюда дописывается пошаговый
     *                            текстовый лог (SUCCESS:/ERROR: строки) — вызывающий
     *                            код не должен по нему определять успех/неудачу,
     *                            для этого используется явный возврат функции.
     *
     * @return int|false  ID актуального прайсового фрагмента (может отличаться от
     *                     входного $pa_id/$pa[PA::F_ID], если был создан клон —
     *                     см. ветку клонирования) при успехе; false — при любой
     *                     ошибке на любом из этапов (устройство или БД).
     */
    public static function enable(int|null $pa_id = null, array|null $pa = null, bool|int $ena = 1, int|bool $force = 0, string &$log = ''): int|false {

        $ena = ($ena ? true : false);
        $force = ($force ? true : false);
        $model = new AbonModel();

        if (empty($pa)) {
            // ВНИМАНИЕ: если оба параметра ($pa_id и $pa) не переданы — сюда придёт
            // get_pa(null). Поведение AbonModel::get_pa(null) явно не проверено —
            // стоит убедиться, что оно бросает исключение или возвращает пусто,
            // а не какую-то первую попавшуюся запись.
            $pa = $model->get_pa($pa_id);
        }

        $tp = $model->get_tp($pa[PA::F_TP_ID]);

        /**
         * Установка (включение/выключение) услуги на Mikrotik.
         * Выполняется ТОЛЬКО для активных управляемых ТП — для неуправляемых
         * это шаг просто пропускается, и мы сразу переходим к изменению в БД.
         */
        if ($tp[TP::F_ACTIVE] && $tp[TP::F_IS_MANAGED]) {
            if (($mik = Api::tp_connector(tp: $tp)) === false) {
                // Примечание: переменная $mik далее не используется — только
                // для проверки успешности подключения. $dev создаётся отдельно ниже.
                $s = "Не удалось подключиться к ТП";
                MsgQueue::msg(MsgType::ERROR, $s);
                $log .= 'ERROR: ' . $s;
                return false;
            }
            $dev = new MikrotikDevice(tp: $tp);
            $result = $dev->set_address_list_item(
                    search: [
                        Mik::F_SEARCH_LIST => Mik::L_ABON,
                        Mik::F_SEARCH_IP => $pa[PA::F_NET_IP],
                    ],
                    update: [
                        Mik::F_UPDATE_ENA => $ena,
                    ]);
            if (!$result) {
                $s = '<pre>' . print_r(MikrotikDevice::get_messages(), true) . '</pre>';
                MsgQueue::msg(MsgType::ERROR, $s);
                $log .= 'ERROR: ' . $s;
                $s = "Не удалось установить услугу на микротике. Изменение в базе отменено.";
                MsgQueue::msg(MsgType::ERROR, $s);
                $log .= 'ERROR: ' . $s;
                // Корректно: раз устройство не поменялось, в БД тоже ничего не трогаем.
                return false;
            }

            $s = "Услуга на микротике " . ($ena ? "включена" : "выключена");
            MsgQueue::msg(MsgType::SUCCESS_AUTO, $s);
            $log .= 'SUCCESS: ' . $s . "\n";
        }

        if ($ena) {
            /**
             * Включение услуги. Два варианта:
             *  - фрагмент был закрыт давно (дольше pa_unpaused_days) и !$force
             *    → не переоткрываем тот же фрагмент, а клонируем его и открываем клон
             *      (вероятно, чтобы не искажать историю начислений старого периода);
             *  - иначе (закрыт недавно, либо $force=true) → просто переоткрываем
             *    тот же фрагмент, установив F_DATE_END = null.
             *
             * ВНИМАНИЕ: если фрагмент вообще не был на паузе (F_DATE_END уже null),
             * get_between_days($pa[PA::F_DATE_END], TODAY()) получит null первым
             * аргументом — поведение get_between_days(null, ...) здесь не проверялось.
             */
            if  (
                    !$force &&
                    get_between_days($pa[PA::F_DATE_END], TODAY()) > App::get_config('pa_unpaused_days')
                )
            {
                /**
                 * Закрыт давно — создаём копию и открываем клонированный ПФ,
                 * оригинал остаётся закрытым как есть (сохраняется история).
                 */
                if (($pa_new_id = self::clone(pa: $pa)) === false) {
                    $s = "Не удалось клонировать ПФ для открытия";
                    MsgQueue::msg(MsgType::ERROR, $s);
                    $log .= 'ERROR: ' . $s;
                    return false;
                }
                $s = "ПФ на паузе давно. Клонирован";
                MsgQueue::msg(MsgType::SUCCESS_AUTO, $s);
                $log .= 'SUCCESS: ' . $s . "\n";

                $pa_rec = [
                    PA::F_ID => $pa_new_id,
                    PA::F_DATE_END => null,
                ];

            } else {
                /**
                 * Закрыт недавно (или force=true) — просто открываем тот же ПФ.
                 */
                $s = "ПФ на паузе недавно (или force). Открываем";
                MsgQueue::msg(MsgType::SUCCESS_AUTO, $s);
                $log .= 'SUCCESS: ' . $s . "\n";

                $pa_rec = [
                    PA::F_ID => $pa[PA::F_ID],
                    PA::F_DATE_END => null,
                ];
            }

        } else {
            /**
             * Выключение услуги — просто закрываем текущий ПФ сегодняшней датой.
             * (Именно эта ветка используется из AbonModel::set_abon_pause().)
             */
            $pa_rec = [
                PA::F_ID => $pa[PA::F_ID],
                PA::F_DATE_END => TODAY(),
            ];
        }

        /**
         * Внесение параметра услуги в базу — финальный шаг.
         * ВНИМАНИЕ: если мы до этого успешно поменяли состояние на Mikrotik
         * (see выше), а здесь запись в БД не удастся — устройство и БД разойдутся:
         * функция вернёт false (что корректно сигнализирует вызывающему коду
         * об ошибке), но откатить уже применённое изменение на устройстве
         * автоматически не пытается. Это стоит иметь в виду при интерпретации
         * false как "ничего не изменилось" — на устройстве изменение уже могло
         * произойти.
         */
        if ($model->update_row_by_id(PA::TABLE, $pa_rec, PA::F_ID)) {
            $s = "Услуга в биллинге " . ($ena ? "включена" : "выключена");
            MsgQueue::msg(MsgType::SUCCESS_AUTO, $s);
            $log .= 'SUCCESS: ' . $s;
            return $pa_rec[PA::F_ID];
        } else {
            $s = "Ошибка установки параметров услуги в базе. Требуется проверка.";
            MsgQueue::msg(MsgType::ERROR, $s);
            $log .= 'ERROR: ' . $s;
            return false;
        }
    }


    
    public static function delete(int $pa_id): bool {
        $model = new AbonModel();
        $ret = false;
        if ($model->validate_id(PA::TABLE, $pa_id, PA::F_ID)) {
            if ($model->delete_rows_by_field(PA::TABLE, PA::F_ID, $pa_id)) {
                $ret = true;
            } else {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Error deleting price fragment | Ошибка удаления прайсового фрагмента | Помилка видалення прайсового фрагменту'));
                MsgQueue::msg(MsgType::ERROR_AUTO, $model->errorInfo());
                $ret = false;
            }
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Price fragment ID is not correct | ID прайсового фрагмента не верен | ID прайсового фрагмента не вірний'));
            $ret = false;
        }
        return $ret;
    }

    
    public function enableAction() {
        /**
         * Проверка наличия авторизации
         */
        if (!App::isAuth()) {   
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect('/');
        }

        /**
         * Проверка прав на редактирование
         */
        if (!can_edit(Module::MOD_PA)) {   
            MsgQueue::msg(MsgType::ERROR, __('No rights | Нет прав | Немає прав'));
            self::log_no_rights();
            redirect();
        }
        
        $ena   = (($_GET[PA::F_ENABLED] ?? 0) ? 1 : 0);
        $force = (($_GET[PA::F_FORCE] ?? 0) ? 1 : 0);
        $pa_id = intval($_GET[PA::F_PA_ID] ?? 0);
        $model = new AbonModel();
        $pa = $model->get_pa($pa_id);
        $pa_new_id = PaController::enable(pa: $pa, ena: $ena, force: $force);
        if ($ena) {
            /**
             * если включаем ПФ, то установить время ожидания оплаты (из конфига).
             */
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Set the waiting time for payment | Устанавливаем время ожидания оплаты | Встановлюємо час очікування оплати') . ' (' . App::get_config('pa_days_to_wait_payment') . ' ' . __('days | дней | днів') . ')');
            $model->set_field_value(
                table_name: Abon::TABLE, 
                field_id: Abon::F_ID, 
                value_id: $pa[PA::F_ABON_ID], 
                field: Abon::F_DUTY_WAIT_DAYS, 
                value: App::get_config('pa_days_to_wait_payment'),
                update_access_time: false);
        }
        $model->recalc_abon($pa[PA::F_ABON_ID]);
        if ($pa_new_id && ($pa_new_id != $pa_id)) {
            redirect(PA::URI_EDIT . '/' . $pa_new_id);
        }
        redirect();
        
    }

    
    
    public function editAction() {

        // debug($_GET, '$_GET');
        // debug($_POST, '$_POST');
        // debug($this->route, '$this->route');

        /**
         * Проверка наличия авторизации
         */
        if (!App::isAuth()) {   
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect('/');
        }

        /**
         * Проверка прав на редактирование
         */
        if (!can_edit(Module::MOD_PA)) {   
            MsgQueue::msg(MsgType::ERROR, __('No rights | Нет прав | Немає прав'));
            self::log_no_rights();
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
            // debug($pa, '$pa', debug_view: DebugView::DUMP, die: 0);
            // debug($data, '$data', die: 1);
            // Валидация
            if (!self::validate($data)) {
                $_SESSION[SessionFields::FORM_DATA] = $data;
            } else {
                /**
                 * Возвращаем только изменённые поля (и поле ID для идентификации)
                 */
                $data = get_diff_fields($data, $pa, PA::F_ID);
                if ($data) {
                    /**
                     * Данные есть. Вносить в базу
                     */
                    if ($model->update_row_by_id(PA::TABLE, $data, PA::F_ID)) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, __("Data entered | Данные внесены | Дані внесені"));
                        
                        if (self::need_recalc_cost($data)) {
                            /**
                             * Пересчет стоимости прайсовых фрагментов и начислений
                             */
                            $model->recalc_abon($pa[PA::F_ABON_ID]);
                        }
                        // MsgQueue::msg(MsgType::SUCCESS, $data);
                    } else {
                        MsgQueue::msg(MsgType::ERROR, $model->errorInfo());
                    }
                } else {
                    /**
                     * Данных нет. Просто сообщить
                     */
                    MsgQueue::msg(MsgType::INFO_AUTO, __("no data changes | нет изменений в данных | немає змін у даних"));
                }

            }
            redirect(PA::URI_EDIT . '/' . $pa[PA::F_ID]);
            // redirect();
        }



        $pa_id = intval($this->route[F_ALIAS]);

        if (!$model->validate_id(PA::TABLE, $pa_id, PA::F_ID)) {   
            MsgQueue::msg(MsgType::ERROR, __('Invalid ID | Не верный ID | Не вірний ID'));
            redirect();
        }

        $pa = $model->get_pa($pa_id);

        $price = $model->get_price($pa[PA::F_PRICE_ID]);
        $tp = $model->get_tp($pa[PA::F_TP_ID]);
        $tp_default_price = $model->get_price($tp[TP::F_DEFAULT_PRICE_ID]);

        $arp_resolve = null;
        $abon_ip_on = null;
        if  (                                           // если
                $tp[TP::F_ACTIVE] &&                    // ТП активна
                $tp[TP::F_IS_MANAGED] &&                // ТП управляемая
                ($pa[PA::F_NET_IP_SERVICE] == 1) &&     // это IP услуга
                !empty($pa[PA::F_NET_IP]) &&            // IP-адрес указан
                validate_ip($pa[PA::F_NET_IP])          // IP-адрес валидный
            ) 
        {
            /**
             * Получение данных с микротика
             * Запись из таблицы ARP микротика со статусом IP-адреса
             */
            try {
                $dev = new MikrotikDevice(tp: $tp);
//                $t = microtime(true);
                $abon_ip_on = $dev->in_address_list_abon(ip: $pa[PA::F_NET_IP], ena: true);
//                MsgQueue::msg(MsgType::INFO, 'TIMER: in_list_abon: ' . round(microtime(true) - $t, 3) . ' sec');
//                $t = microtime(true);
                $arp_resolve = $dev->resolve_arp_items(search: [ Mik::F_ARP_IP => $pa[PA::F_NET_IP], ]);
//                MsgQueue::msg(MsgType::INFO, 'TIMER: resolve_arp_items: ' . round(microtime(true) - $t, 3) . ' sec');
                
            } catch (\Throwable $exc) {
                MsgQueue::msg(MsgType::ERROR, 'PaController::editAction: ' . __('Error receiving data | Ошибка получения данных | Помилка отримання даних'));
                MsgQueue::msg(MsgType::ERROR, '<pre>' . $exc->getTraceAsString()) . '</pre>';
                if (MikrotikDevice::$messages) {
                    MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages);
                }
                $arp_resolve = null;
                $abon_ip_on = null;
            }

            
//            $mik = Api::tp_connector(tp: $tp);
//            if ($mik !== false) {
//                /**
//                 * Enable/Disable статус IP-адреса в таблице ABON
//                 */
//                $dev = new \billing\core\MikrotikDevice1(tp: $tp);
//                //                $abon_ip_on = Api::get_ip_enabled_on_mik_abon($mik, $pa[PA::F_NET_IP]);
//                //                if ($abon_ip_on === null) {
//                //                    MsgQueue::msg(MsgType::ERROR, Api::get_errors());
//                //                }
//                $abon_ip_on = $dev->in_list_abon(ip: $pa[PA::F_NET_IP], ena: true);
//                /**
//                 * Соединение с миротиком установлено
//                 */
//                $arp = Api::get_mac_from_arp_by_ip($mik, $pa[PA::F_NET_IP], true);
//            } else {
//                MsgQueue::msg(MsgType::ERROR, Api::get_errors());
//            }
        }


        $prices_list = array_column(
                array: $model->get_rows_by_sql("SELECT `".Price::F_ID."`, `".Price::F_TITLE."` FROM `".Price::TABLE."` WHERE (`".Price::F_ACTIVE."`=1) ORDER BY `".Price::TABLE."`.`".Price::F_TITLE."` ASC"),
                column_key: Price::F_TITLE,
                index_key: Price::F_ID);

        $tp_list = array_column(
                array: $model->get_my_tp_list(active: 1),
                column_key: TP::F_TITLE,
                index_key: TP::F_ID);

        $abon = $model->get_abon($pa[PA::F_ABON_ID]);
        $user = $model->get_user($abon[Abon::F_USER_ID]);

        View::setMeta(title: __('Editing a price fragment | Редактирование прайсового фрагмента | Редагування прайсового фрагменту'));
        $this->setVariables([
            'user'=> $user,
            'abon'=> $abon,
            'pa'=> $pa,
            'price'=> $price,
            'tp'=> $tp,
            'tp_default_price'=> $tp_default_price,
            'abon_ip_on'=> $abon_ip_on,
            'arp_resolve'=> $arp_resolve,
            'prices_list'=> $prices_list,
            'tp_list'=> $tp_list,
        ]);
    }


    public static function pa_close(int $pa_id, bool $service_off = false): bool {
        $model = new AbonModel();

        if (!$model->validate_id(PA::TABLE, $pa_id, PA::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid price fragment ID | Не верный ID прайсового фрагмента | Не вірний ID прайсового фрагмента'));
            return false;
        }

        $pa = $model->get_pa($pa_id);
        $tp = $model->get_tp($pa[PA::F_TP_ID]);
        $rez = true;

        /**
         * Отключить IP на микротике
         */
        if ($service_off) {
            if ((__pa_age($pa) == PAStatus::ACTIVE_TODAY) && $tp[TP::F_ACTIVE] && $tp[TP::F_IS_MANAGED]) {
                $ip = $pa[PA::F_NET_IP];
                if (Api::set_mik_abon_ip(Api::tp_connector(tp: $tp), $ip, false, true)) {
                    MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Disabling the service completed successfully | Отключение услуги выполнени успешно | Відключення послуги виконано успішно'));
                    if (Api::$errors) {
                        MsgQueue::msg(MsgType::SUCCESS_AUTO, Api::$errors);
                    }
                
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Error disabling the IP address on the technical site | Ошибка отключения IP адреса на технической площадке | Помилка відключення IP адреси на технічному майданчику'));
                    if (Api::$errors) {
                        MsgQueue::msg(MsgType::ERROR, Api::$errors);
                        $rez = false;
                    }
                }
            }
        }

        /** 
         * закрытие ПФ
         */
        $pa[PA::F_CLOSED] = 1;
        if (empty($pa[PA::F_DATE_END])) {
            $pa[PA::F_DATE_END] = today();
            MsgQueue::msg(MsgType::WARN, __('The PF closing date was empty. Installed today. Check for correctness | Дата закрытия ПФ была пустой. Установлена в сегодняшнюю. Проверьте правильность | Дата закриття ПФ була порожньою. Встановлено у сьогоднішню. Перевірте правильність') . '.');
        }

        /**
         * отключение флага IP_SERVICE при закрытии ПФ
         */
        if ($pa[PA::F_NET_IP_SERVICE] == 1) {
            $pa[PA::F_NET_IP_SERVICE] = 0;
            MsgQueue::msg(MsgType::WARN, __('The [IP_SERVICE] flag is forcibly disabled due to the closing of the price fragment. Check for correctness | Флаг [IP_SERVICE] принудительно отключён в связи с закрытием прайсового фрагмента. Проверьте правильность | Прапор [IP_SERVICE] примусово вимкнено у зв\'язку із закриттям прайсового фрагмента. Перевірте правильність') . '.');
        }

        if ($model->update_row_by_id(PA::TABLE, $pa, PA::F_ID)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Data in the database was updated successfully | Данные в базе обновлены успешно | Дані в базі оновлено успішно'));
            /**
             * Пересчёт остатков и начислений по абоненту
             */
            $model->recalc_abon($pa[PA::F_ABON_ID]);
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Error updating data in the database | Ошибка обновления данных в базе | Помилка оновлення даних у базі'));
            MsgQueue::msg(MsgType::ERROR_AUTO, $model->errorInfo());
            $rez = false;
        }        
        return $rez;
    }



}
