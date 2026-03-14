<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Abon.php
 *  Path    : config/tables/Abon.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

use billing\core\App;
use billing\core\base\Lang;

/**
 * Description of Abon.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Abon {
    
    const URI_INDEX = "/abon";
    const URI_LAST = "/abon/last";
    const URI_VIEW = "/abon/view";
    const URI_EDIT = "/abon/edit";
    const URI_UPDATE = "/abon/update";
    const URI_REST_UPDATE = "/abon/restupdate"; // <id>
    

    /**
     * Не определённый абонент
     */
    const NA = -1;
    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    const F_GET_ID                  = 'abon_id';
    const F_ABON_ID                 = 'abon_id';

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    const POST_REC                  = 'abon';

    public const TABLE              = 'abons';          // Имя таблицы в базе
    public const REC                = 'abon';           // 'Поле одного абонента';


    /**
     * Поля формы
     */

    public const F_ID               = 'id';
    public const F_ID_HASH          = 'id_hash';        // 'хэш эквивалент для ID',
    public const F_USER_ID          = 'user_id';        // 'Данные пользователя',
    public const F_ADDRESS          = 'address';        // 'Адрес подключения',
    public const F_COORD_GMAP       = 'coord_gmap';     // 'Координаты на Гугл-карте',
    public const F_IS_PAYER         = 'is_payer';       // 'Абонент "плательщик", т.е. пользуется услугой и оплачивает её (должен оплачивать)',
    public const F_DATE_JOIN        = 'date_join';      // 'Дата подключения',
    public const F_DATE_JOIN_STR    = 'date_join_str';  // 'Дата подключения текстовая для поля в форме',
    public const F_COMMENTS         = 'comments';       // 'Примечания по абоненту',
    public const F_DUTY_MAX_WARN    = 'duty_max_warn';  // 'Количество оплаченных дней, при пересечении которых отправлять предупреждение абоненту об оплате',
    public const F_DUTY_MAX_OFF     = 'duty_max_off';   // 'Количество оплаченных дней, при пересечении которых отключать услуги',
    public const F_DUTY_AUTO_OFF    = 'duty_auto_off';  // 'Автоматически отключать/ставить на паузу абонента при пересечении значения duty_max_off',
    public const F_DUTY_WAIT_DAYS   = 'duty_wait_days'; // 'Количество дней ожидания перед выключением (для ожидания оплаты при ручном включении после автоотключения)',
    public const F_CREATION_UID     = 'creation_uid';   // 'Кто создал заппись',
    public const F_CREATION_DATE    = 'creation_date';  // 'Дата создания записи',
    public const F_MODIFIED_UID     = 'modified_uid';   // 'Кто изменил запись',
    public const F_MODIFIED_DATE    = 'modified_date';  // 'Дата изменения записи'

    const FORM_FIELDS = [
        self::F_ID               => null,
        self::F_ID_HASH          => null,
        self::F_USER_ID          => null,
        self::F_ADDRESS          => '',
        self::F_COORD_GMAP       => '',
        self::F_IS_PAYER         => 1,
        self::F_DATE_JOIN_STR    => null,
        self::F_COMMENTS         => '',
        self::F_DUTY_MAX_WARN    => 0,
        self::F_DUTY_MAX_OFF     => 0,
        self::F_DUTY_AUTO_OFF    => 1,
        self::F_DUTY_WAIT_DAYS   => 0,
    ];



    const TEXT_FIELDS = [
        self::F_ADDRESS          => '',
        self::F_COMMENTS         => '',
    ];



    const T_FLAGS = [
        self::F_IS_PAYER            => 1,
        self::F_DUTY_AUTO_OFF       => 1,
        ];


        
    public const DESCRIPTIONS = [
        self::F_IS_PAYER => [
            Lang::C_EN => 'Subscriber "payer", i.e. uses the service and pays for it (should pay)',
            Lang::C_RU => 'Абонент "плательщик", т.е. пользуется услугой и оплачивает её (должен оплачивать)',
            Lang::C_UK => 'Абонент "платник", т.е. користується послугою і оплачує її (повинен оплачивать)',
        ],
        self::F_DUTY_MAX_WARN => [
            Lang::C_EN => 'The number of prepaid days, at the intersection of which, to send a warning to the subscriber about the need for payment',
            Lang::C_RU => 'Количество предоплаченных дней, при пересечении которых, отправлять абоненту предупреждение о необходимости оплаты',
            Lang::C_UK => 'Кількість передплачених днів, при перетині яких, відправляти абоненту попередження про необхідність оплати',
        ],
        self::F_DUTY_MAX_OFF => [
            Lang::C_EN => 'The number of prepaid days when crossing which to disable services',
            Lang::C_RU => 'Количество предоплаченных дней, при пересечении которых, отключать услуги',
            Lang::C_UK => 'Кількість передплачених днів, при перетині яких, відключати послуги',
        ],
        self::F_DUTY_AUTO_OFF => [
            Lang::C_EN => 'Automatically disable services, pause the subscriber, when crossing the value of F_DUTY_MAX_OFF',
            Lang::C_RU => 'Автоматически отключать услуги, ставить на паузу абонента, при пересечении значения F_DUTY_MAX_OFF',
            Lang::C_UK => 'Автоматично відключати послуги, ставити на паузу абонента, при перетині значення F_DUTY_MAX_OFF',
        ],
        self::F_DUTY_WAIT_DAYS => [
            Lang::C_EN => 'The number of waiting days before the service is turned off (to wait for payment when manually turned on after auto-off)',
            Lang::C_RU => 'Количество дней ожидания перед выключением услуги (для ожидания оплаты при ручном включении после автоотключения)',
            Lang::C_UK => 'Кількість днів очікування перед вимиканням послуги (для очікування оплати при ручному включенні після автовідключення)',
        ],
    ]; 


    public static function description(string $field_name, string|null $lang = null) {
        if (is_null($lang)) { $lang = App::lang(); }
        return self::DESCRIPTIONS[$field_name][$lang] ?? '';
    }

}