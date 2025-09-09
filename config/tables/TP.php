<?php


namespace config\tables;


class TP {

    const POST_REC                 = 'tp';

    const TABLE                    = 'tp_list'; // Технические площадки, точки доступа

    const F_ID                     = 'id';
    const F_TERRITORIAL_GROUP_ID   = 'territorial_group_id';    // 'ID территориальной группы технических площадок',
    const F_INVEST_GROUP_ID        = 'invest_group_id';         // 'ID инвестиционной группы распределения дивидендов',
    const F_ADMIN_OWNER_ID         = 'admin_owner_id';          // 'ID администратора-владельца',
    const F_FIRM_ID                = 'firm_id';                 // 'ID Обслуживающего предприятия',
    const F_TITLE                  = 'title';                   // 'Название тех.площадки',
    const F_IP                     = 'ip';                      // 'IP-адрес точки доступа или тех.площадки',
    const F_LOGIN                  = 'login';                   // 'логин для управляющего доступа',
    const F_PASS                   = 'pass';                    // 'пароль дл управляющего доступа',
    const F_URL                    = 'url';                     // 'URL-строка для управления устройством (обычно через вэб)',
    const F_URL_ZABBIX             = 'url_zabbix';              // 'URL страницы в системе мониторинга zabbix относящейся к этой ТП',
    const F_ADDRESS                = 'address';                 // 'Адрес размещения ТП',
    const F_COORD                  = 'coord';                   // 'Географические координаты ТП для отображения на картах',
    const F_RANG_ID                = 'rang_id';                 // 'Ранг узла: 1 — Абонентский узел | 2 — AP | 3 — Агрегатор AP | 4 — Bridge AP | 5 — Bridge Client | 10 — Хостинговая тех. площадка | 100 — Биллинг',
    const F_UPLINK_ID              = 'uplink_id';               // 'Узел "верхнего" уровня, от которого идёт сигнал к этому узлу (не обязательно маршрутизатор)',
    const F_STATUS                 = 'status';                  // '0 — Отключен/демонтирован, 1 — Работает',
    const F_DELETED                = 'deleted';                 // 'ТП демонтирована',
    const F_IS_MANAGED             = 'is_managed';              // 'Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН',
    const F_WEB_MANAGEMENT         = 'web_management';          // 'Страница web-доступа к устройству',
    const F_DEFAULT_PRICE_ID       = 'default_price_id';        // 'Прайс По_умолчанию для этой ТП',
    const F_DESCRIPTION            = 'description';             // 'Описание ТП',
    const F_COST_PER_M             = 'cost_per_M';              // 'Стоимость Эксплуатации/аренды/абонплаты техплощадки',
    const F_COST_PER_M_DESCRIPTION = 'cost_per_M_description';  // 'Описание стоимости эксплуатации ТП',
    const F_COST_TP_VALUE          = 'cost_tp_value';           // 'Стоимость строительства/ввода в эксплуатацию ТП',
    const F_COST_TP_DESCRIPTION    = 'cost_tp_description';     // 'Описание стоимости строительства / ввода в эксплуатацию ТП',
    const F_ABON_ID_RANGE_START    = 'abon_id_range_start';     // 'Начало диапазона выдачи ID для пользователей',
    const F_ABON_ID_RANGE_END      = 'abon_id_range_end';       // 'Конец диапазона выдачи ID для пользователей',
    const F_SCRIPT_MIK_IP          = 'script_mik_ip';           // 'IP устройства',
    const F_SCRIPT_MIK_PORT        = 'script_mik_port';         // 'tcp порт доступа на устройство',
    const F_SCRIPT_MIK_LOGIN       = 'script_mik_login';        // 'login доступа на устройство',
    const F_SCRIPT_MIK_PASSWD      = 'script_mik_passwd';       // 'passwd доступа на устройства',
    const F_SCRIPT_FTP_IP          = 'script_ftp_ip';           // 'IP-адрес для ftp доступа',
    const F_SCRIPT_FTP_PORT        = 'script_ftp_port';         // 'TCP-порт для ftp доступа',
    const F_SCRIPT_FTP_LOGIN       = 'script_ftp_login';        // 'Логин для ftp доступа',
    const F_SCRIPT_FTP_PASSWD      = 'script_ftp_passwd';       // 'Пасс для ftp доступа',
    const F_SCRIPT_FTP_FOLDER      = 'script_ftp_folder';       // 'Имя папаки для сохранения файлов',
    const F_SCRIPT_FTP_GETPATH     = 'script_ftp_getpath';      // 'Путь и шаблон на сервере для скачивания файлов',
    const F_CREATION_DATE          = 'creation_date';           // 'Дата создания записи о техплощадке',
    const F_CREATION_UID           = 'creation_uid';            // 'Кто создал запись о ТП',
    const F_MODIFIED_DATE          = 'modified_date';           // 'Дата инменения записи о ТП',
    const F_MODIFIED_UID           = 'modified_uid';            // 'Кто изменил запись о ТП'

}
