<?php
/*
 *  Project : my.ri.net.ua
 *  File    : TP.php
 *  Path    : config/tables/TP.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 20:59:17
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

use billing\core\base\Lang;

/**
 * Description of TP.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class TP {

    const URI_INDEX                = '/tp';
    const URI_EDIT                 = '/tp/edit';
    const URI_SAVE                 = '/tp/save';
    const URI_DELETE               = '/tp/delete';
    const URI_COMBINE              = '/api/combine';

    const POST_REC                 = 'tp';

    const TABLE                    = 'tp_list'; // Технические площадки, точки доступа


    /**
     * Поля
     */

    const F_ID                     = 'id';
    const F_STATUS                 = 'status';                  // '0 — Отключен/демонтирован, 1 — Работает',
    const F_DELETED                = 'deleted';                 // 'ТП демонтирована',
    const F_IS_MANAGED             = 'is_managed';              // 'Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН',
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
    const F_WEB_MANAGEMENT         = 'web_management';          // 'Страница web-доступа к устройству',
    const F_DEFAULT_PRICE_ID       = 'default_price_id';        // 'Прайс По_умолчанию для этой ТП',
    const F_DESCRIPTION            = 'description';             // 'Описание ТП',
    const F_COST_PER_M             = 'cost_per_M';              // 'Стоимость Эксплуатации/аренды/абонплаты техплощадки',
    const F_COST_PER_M_DESCRIPTION = 'cost_per_M_description';  // 'Описание стоимости эксплуатации ТП',
    const F_COST_TP_VALUE          = 'cost_tp_value';           // 'Стоимость строительства/ввода в эксплуатацию ТП',
    const F_COST_TP_DESCRIPTION    = 'cost_tp_description';     // 'Описание стоимости строительства / ввода в эксплуатацию ТП',
    const F_ABON_ID_RANGE_START    = 'abon_id_range_start';     // 'Начало диапазона выдачи ID для пользователей',
    const F_ABON_ID_RANGE_END      = 'abon_id_range_end';       // 'Конец диапазона выдачи ID для пользователей',
    const F_MIK_IP                 = 'script_mik_ip';           // 'IP устройства',
    const F_MIK_PORT               = 'script_mik_port';         // 8728 'API tcp порт доступа на устройство',
    const F_MIK_PORT_SSL           = 'script_mik_port_ssl';     // 8729 'API tcp порт для SSL доступа на устройство',
    const F_MIK_LOGIN              = 'script_mik_login';        // 'login доступа на устройство',
    const F_MIK_PASSWD             = 'script_mik_passwd';       // 'passwd доступа на устройства',
    const F_MIK_FTP_IP             = 'script_ftp_ip';           // 'IP-адрес для ftp доступа',
    const F_MIK_FTP_PORT           = 'script_ftp_port';         // 'TCP-порт для ftp доступа',
    const F_MIK_FTP_LOGIN          = 'script_ftp_login';        // 'Логин для ftp доступа',
    const F_MIK_FTP_PASSWD         = 'script_ftp_passwd';       // 'Пасс для ftp доступа',
    const F_MIK_FTP_FOLDER         = 'script_ftp_folder';       // 'Имя папаки для сохранения файлов',
    const F_MIK_FTP_GETPATH        = 'script_ftp_getpath';      // 'Путь и шаблон на сервере для скачивания файлов',
    const F_CREATION_DATE          = 'creation_date';           // 'Дата создания записи о техплощадке',
    const F_CREATION_UID           = 'creation_uid';            // 'Кто создал запись о ТП',
    const F_MODIFIED_DATE          = 'modified_date';           // 'Дата инменения записи о ТП',
    const F_MODIFIED_UID           = 'modified_uid';            // 'Кто изменил запись о ТП'



    /**
     * длины полей для валидатора
     */
    const LENGTS = [
        self::F_TITLE        => 64,
        self::F_LOGIN        => 50,
        self::F_PASS         => 50,
        self::F_COORD        => 40,
        self::F_IP           => 40,
        self::F_MIK_PORT     => 6,
        self::F_MIK_PORT_SSL => 6,
        self::F_MIK_FTP_PORT => 6,
    ];



    /**
     * Вычисляемые поля
     */
    const F_ADMIN_OWNER_NAME       = 'admin_owner_name';        // 'Имя/Название администратора-владельца',
    const F_FIRM_NAME              = 'firm_name';               // 'Имя/Название Обслуживающего предприятия',
    const F_UPLINK_NAME            = 'uplink_name';             // 'Имя/Название узла "верхнего" уровня, от которого идёт сигнал к этому узлу (не обязательно маршрутизатор)',
    const F_COUNT_PA               = 'count_pa';                // 'Количество подключённых PA',
    const F_RANG_TITLE             = 'rang_title';              // 'Ранг узла: 1 — Абонентский узел | 2 — AP | 3 — Агрегатор AP | 4 — Bridge AP | 5 — Bridge Client | 10 — Хостинговая тех. площадка | 100 — Биллинг',
    const F_UPLINK_TITLE           = 'uplink_title';            // 'Узел "верхнего" уровня, от которого идёт сигнал к этому узлу (не обязательно маршрутизатор)',



    const TYPE_NA           = 0;    // N/A 	N/A
    const TYPE_ABON         = 1;    // Абон. узел 	1 — Абонентский узел (station/client/router)
    const TYPE_NAT          = 2;    // Маршрутизатор 	2 — Точка доступа, Узел подключения абонентов, упр...
    const TYPE_AG           = 3;    // Агрегатор 	3 — Aggregation router — маршрутизатор агрегации (собирает трафик от множества абонентов).
    const TYPE_BRIDGE       = 4;    // ТД Мост          4 — Bridge AP. ТД Радиоудлинитель
    const TYPE_CLI_BRIDGE   = 5;    // Абон. Мост 	5 — Client Bridge. Оборудование в режиме Мост на с...
    const TYPE_HOSTIG       = 10;   // Хостинг          10 -- Хостинговая тех. площадка
    const TYPE_MONITOR      = 90;   // Мониторинг 	100 — Сервер мониторинга
    const TYPE_MON_PROXI    = 91;   // Прокси мониторинга  100 — Сервер прокси-мониторинга
    const TYPE_BILLIG       = 100;  // Биллинг 	100 — Сервер биллинга




    const DESCRIPTIONS = [
        self::F_STATUS => [
            0=> [
                'en'=> 'Off',
                'ru'=> 'Отключена',  
                'uk'=> 'Відключена',
            ],
            1=> [
                'en'=> 'Working',
                'ru'=> 'Работает',  
                'uk'=> 'Працює',
            ],
        ],
        self::F_IS_MANAGED => [
            0=> [
                'en'=> 'Not Managed',
                'ru'=> 'Не управляемая',  
                'uk'=> 'Не керована',
            ],
            1=> [
                'en'=> 'Managed',
                'ru'=> 'Управляемая',
                'uk'=> 'Керована',
            ],
        ],
        self::F_DELETED => [
            0=> [
                'en'=> 'The equipment is installed',
                'ru'=> 'Оборудование установлено',  
                'uk'=> 'Обладнання встановлено',
            ],
            1=> [
                'en'=> 'The equipment has been dismantled',
                'ru'=> 'Оборудование демонтировано',
                'uk'=> 'Обладнання демонтовано',
            ],
        ],
    ];


    public static function get_status(array $tp): string {
        return self::DESCRIPTIONS[self::F_STATUS]     [$tp[self::F_STATUS]]     [Lang::code()] 
                . ' | ' . self::DESCRIPTIONS[self::F_IS_MANAGED] [$tp[self::F_IS_MANAGED]] [Lang::code()] 
                // . ' | ' . self::DESCRIPTIONS[self::F_DELETED]    [$tp[self::F_DELETED]]    [Lang::code()]
                ;
    }

    
    const TYPES = [
        self::TYPE_NA => [
            Lang::C_EN => 'N/A',
            Lang::C_RU => 'N/A',
            Lang::C_UK => 'N/A',
        ],
        self::TYPE_ABON => [
            Lang::C_EN => 'Subscriber unit (CPE)',
            Lang::C_RU => 'Абонентский узел',
            Lang::C_UK => 'Абонентський вузол',
        ],
        self::TYPE_NAT => [
            Lang::C_EN => 'Network gateway (NAT router)',
            Lang::C_RU => 'Маршрутизатор (NAT-шлюз)',
            Lang::C_UK => 'Маршрутизатор (NAT-шлюз)',
        ],
        self::TYPE_AG => [
            Lang::C_EN => 'Aggregation router',
            Lang::C_RU => 'Агрегирующий маршрутизатор',
            Lang::C_UK => 'Агрегуючий маршрутизатор',
        ],
        self::TYPE_BRIDGE => [
            Lang::C_EN => 'Bridge / Access point',
            Lang::C_RU => 'Мост / Точка доступа',
            Lang::C_UK => 'Міст / Точка доступу',
        ],
        self::TYPE_CLI_BRIDGE => [
            Lang::C_EN => 'Client bridge (subscriber bridge)',
            Lang::C_RU => 'Клиентский мост (абонентский)',
            Lang::C_UK => 'Клієнтський міст (абонентський)',
        ],
        self::TYPE_HOSTIG => [
            Lang::C_EN => 'Hosting platform',
            Lang::C_RU => 'Хостинговая площадка',
            Lang::C_UK => 'Хостингова платформа',
        ],
        self::TYPE_MONITOR => [
            Lang::C_EN => 'Monitoring system',
            Lang::C_RU => 'Система мониторинга',
            Lang::C_UK => 'Система моніторингу',
        ],
        self::TYPE_MON_PROXI => [
            Lang::C_EN => 'Monitoring proxy node',
            Lang::C_RU => 'Прокси-узел мониторинга',
            Lang::C_UK => 'Проксі-вузол моніторингу',
        ],
        self::TYPE_BILLIG => [
            Lang::C_EN => 'Billing system',
            Lang::C_RU => 'Биллинговая система',
            Lang::C_UK => 'Білінгова система',
        ],
    ];


    public static function get_type_name(int $type_id): string {
        return self::TYPES[$type_id][Lang::code()];
    }



}