<?php



namespace config\tables;



use billing\core\base\Lang;

class Notify {

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID       = 'sms_id';

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    public const POST_REC       = 'notifyRec';

    /**
     * Имя таблицы в базе
     */
    public const TABLE          = 'sms_list';       // Имя таблицы в базе

    public const F_ID           = 'id';             // ID СМС
    public const F_ABON_ID      = 'abon_id';        // ID абонента, которому отсылается СМС
    public const F_TYPE_ID      = 'type_id';        // Тип уведомления: 1 - SMS | 2 - Email | 3 - ...
    public const F_DATE         = 'date';           // Дата-время отправки СМС
    public const F_TEXT         = 'text';           // Текст СМС сообщения
    public const F_PHONENUMBER  = 'phonenumber';    // Номер телефона, на который отправили СМС
    public const F_METHOD       = 'method';         // Метод отправки СМС: скрипт, вэб-служба или что-то ещё


    /**
     * вычисляемые поля
     */

    const F_USER_ID             = 'user_id';        // Пользоватеь, к которому относится Абонент, которому относится уведомление
    const F_COUNT               = 'notify_count';   // Общее количество уведомлений

    /**
     * Раздел типов уведомлений
     */

    public const TYPE_NA        = 0;
    public const TYPE_SMS       = 1;
    public const TYPE_EMAIL     = 2;
    public const TYPE_OTHER     = 255;

    public const TYPES = [
        self::TYPE_NA    => [
            Lang::C_RU => 'Тип уведомления не указан',
            Lang::C_UK => 'Тип повідомлення не вказано',
            Lang::C_EN => 'Notification type not specified',
        ],
        self::TYPE_SMS   => [
            Lang::C_RU => 'SMS-уведомление',
            Lang::C_UK => 'SMS-повідомлення',
            Lang::C_EN => 'SMS-notifycation',
        ],
        self::TYPE_EMAIL => [
            Lang::C_RU => 'Эл. почта',
            Lang::C_UK => 'Ел. пошта',
            Lang::C_EN => 'Email',
        ],
        self::TYPE_OTHER => [
            Lang::C_RU => 'Другое',
            Lang::C_UK => 'Інше',
            Lang::C_EN => 'Other',
        ],

    ];


    public static function get_type_title(?int $type_id): string {
        return self::TYPES[$type_id ?? self::TYPE_NA][Lang::code()];
    }

}
