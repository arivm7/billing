<?php



namespace config\tables;



class Notify {

    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID       = 'sms_id';

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    public const POST_REC       = 'smsRec';

    public const TABLE          = 'sms_list';       // Имя таблицы в базе

    public const F_ID           = 'id';             // ID СМС
    public const F_ABON_ID      = 'abon_id';        // ID абонента, которому отсылается СМС
    public const F_TYPE_ID      = 'type_id';        // Тип уведомления: 1 - SMS | 2 - Email | 3 - ...
    public const F_DATE         = 'date';           // Дата-время отправки СМС
    public const F_TEXT         = 'text';           // Текст СМС сообщения
    public const F_PHONENUMBER  = 'phonenumber';    // Номер телефона, на который отправили СМС
    public const F_METHOD       = 'method';         // Метод отправки СМС: скрипт, вэб-служба или что-то ещё


}
