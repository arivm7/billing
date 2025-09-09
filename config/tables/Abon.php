<?php



namespace config\tables;



class Abon {

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    const F_GET_ID                  = 'abon_id';

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    const POST_REC                  = 'abon';

    public const TABLE              = 'abons';          // 'Список абонентов';

    public const F_ID               = 'id';
    public const F_ID_HASH          = 'id_hash';        // 'хэш эквивалент для ID',
    public const F_USER_ID          = 'user_id';        // 'Данные пользователя',
    public const F_ADDRESS          = 'address';        // 'Адрес подключения',
    public const F_COORD_GMAP       = 'coord_gmap';     // 'Координаты на Гугл-карте',
    public const F_IS_PAYER         = 'is_payer';       // 'Абонент "плательщик", т.е. пользуется услугой и оплачивает её (должен оплачивать)',
    public const F_DATE_JOIN        = 'date_join';      // 'Дата подключения',
    public const F_COMMENTS         = 'comments';       // 'Примечания по абоненту',
    public const F_DUTY_MAX_WARN    = 'duty_max_warn';  // 'Количество оплаченных дней, при пересечении которых отправлять предупреждение абоненту об оплате',
    public const F_DUTY_MAX_OFF     = 'duty_max_off';   // 'Количество оплаченных дней, при пересечении которых отключать услуги',
    public const F_DUTY_AUTO_OFF    = 'duty_auto_off';  // 'Автоматически отключать/ставить на паузу абонента при пересечении значения duty_max_off',
    public const F_DUTY_WAIT_DAYS   = 'duty_wait_days'; // 'Количество дней ожидания перед выключением (для ожидания оплаты при ручном включении после автоотключения)',
//    public const F_CREATED_UID      = 'created_uid';    // 'Юзер, создавший запись',
//    public const F_CREATED_DATE     = 'created_date';   // 'Дата создания записис в базе',
    public const F_CREATION_UID     = 'creation_uid';   // 'Кто создал заппись',
    public const F_CREATION_DATE    = 'creation_date';  // 'Дата создания записи',
    public const F_MODIFIED_UID     = 'modified_uid';   // 'Кто изменил запись',
    public const F_MODIFIED_DATE    = 'modified_date';  // 'Дата изменения записи'

}
