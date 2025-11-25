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
    

    /**
     * Не определённый абонент
     */
    const NA = -1;
    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    const F_GET_ID                  = 'abon_id';

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



    const T_FLAGS = [
        self::F_IS_PAYER            => 1,
        self::F_DUTY_AUTO_OFF       => 1,
        ];

    public const DESCRIPTIONS = [
        self::F_DUTY_MAX_WARN => [
            'en' => 'The number of prepaid days, at the intersection of which, to send a warning to the subscriber about the need for payment',
            'ru' => 'Количество предоплаченных дней, при пересечении которых, отправлять абоненту предупреждение о необходимости оплаты',
            'uk' => 'Кількість передплачених днів, при перетині яких, відправляти абоненту попередження про необхідність оплати',
        ],
        self::F_DUTY_MAX_OFF => [
            'en' => 'The number of prepaid days when crossing which to disable services',
            'ru' => 'Количество предоплаченных дней, при пересечении которых, отключать услуги',
            'uk' => 'Кількість передплачених днів, при перетині яких, відключати послуги',
        ],
        self::F_DUTY_AUTO_OFF => [
            'en' => 'Automatically disable services, pause the subscriber, when crossing the value of F_DUTY_MAX_OFF',
            'ru' => 'Автоматически отключать услуги, ставить на паузу абонента, при пересечении значения F_DUTY_MAX_OFF',
            'uk' => 'Автоматично відключати послуги, ставити на паузу абонента, при перетині значення F_DUTY_MAX_OFF',
        ],
        self::F_DUTY_WAIT_DAYS => [
            'en' => 'The number of waiting days before the service is turned off (to wait for payment when manually turned on after auto-off)',
            'ru' => 'Количество дней ожидания перед выключением услуги (для ожидания оплаты при ручном включении после автоотключения)',
            'uk' => 'Кількість днів очікування перед вимиканням послуги (для очікування оплати при ручному включенні після автовідключення)',
        ],
    ]; 
}