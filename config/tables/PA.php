<?php
/*
 *  Project : my.ri.net.ua
 *  File    : PA.php
 *  Path    : config/tables/PA.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

/**
 * Description of PA.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class PA {

    const URI_CREATE = "/pa/new";
    const URI_EDIT = "/pa/edit";
    const URI_CLONE = "/pa/clone";
    const URI_DELETE = "/pa/delete";
    const URI_CLOSE = "/pa/close";

    /**
     * имя массива в котором в пост-запрове хранятся данные формы
     */
    const POST_REC              = 'post_PA';

    const TABLE                 = 'prices_apply';

    const F_ID                  = 'id';                 //
    const F_ABON_ID             = 'abon_id';            // Абонент, которому назначен прайс
    const F_PRICE_ID            = 'prices_id';          // ID прикреплённого прайса
    const F_PRICE_TITLE         = 'price_title';        // Название прикрепленного прайса из таблицы Прайсов
    const F_DATE_START          = 'date_start';         // Дата активации прайса
    const F_DATE_START_STR      = 'date_start_str';     // Дата активации прайса
    const F_DATE_END            = 'date_end';           // Дата отключения прайса
    const F_DATE_END_STR        = 'date_end_str';       // Дата отключения прайса
    const F_CLOSED              = 'price_closed';       // Прайс закрыт. Не только указана конечная дата, но она уже прошла и установлена стоимость прайсового фрагмента. Если конечная дата не прошла, то прайс открыт.
    const F_NET_NAME            = 'net_name';           // Сетевое имя абонентского устройсва
    const F_NET_IP_SERVICE      = 'net_ip_service';     // IP услуга, т.е. в параметрах имеет IP-адрес
    const F_NET_ON_ABON_IP      = 'net_on_abon_ip';     // IP на оборудовании абонента, который проходит мимо микротика
    const F_NET_ON_ABON_MASK    = 'net_on_abon_mask';   // Сетевая маска для настройки IP-адреса на оборудовании абонента мимо микротика
    const F_NET_ON_ABON_GATE    = 'net_on_abon_gate';   // Сетевой шлюз для настройки IP-адреса на оборудовании абонента мимо микротика
    const F_NET_NAT11           = 'net_nat11';          // IP-адрес для проброса NAT 1:1
    const F_NET_IP              = 'net_ip';             // IP-адрес
    const F_NET_IP_TRUSTED      = 'net_ip_trusted';     // IP адрес записывается в таблицу "trusted"
    const F_NET_MASK            = 'net_mask';           // Маска подсети
    const F_NET_GATEWAY         = 'net_gateway';        // Шлюз по умолчанию
    const F_NET_DNS1            = 'net_dns1';           // Первичный ДНС
    const F_NET_DNS2            = 'net_dns2';           // Вторичный ДНС
    const F_NET_MAC             = 'net_mac';            // MAC абонентского устройства
    const F_TP_ID               = 'net_router_id';      // ID маршрутизатора к которому подключён абонент
    const F_COORD_GMAP          = 'coord_gmap';         // Координаты точки предоставления услуги на Гугл-карте
    const F_COST_VALUE          = 'cost_value';         // Стоимость прайсового фрагмента
    const F_COST_DATE           = 'cost_date';          // дата пересчёта начисления по этайсовуму фрагментуому пр
    const F_COST_DATE_STR       = 'cost_date_str';      // prices_apply.cost_date), '%Y-%m-%d')
    const F_PPMA_VALUE          = 'PPMA_value';         // Price Per Montch - Значение активной абонплаты
    const F_PPDA_VALUE          = 'PPDA_value';         // Price Per Day - Текущая абонплата в день
    const F_CREATION_UID        = 'creation_uid';       // ID пользователя, создавшего запись
    const F_CREATION_DATE       = 'creation_date';      // Дата создания записи
    const F_MODIFIED_UID        = 'modified_uid';       // Кто изменил запись
    const F_MODIFIED_DATE       = 'modified_date';      // Дата изменения записи в базе



    const FLAGS = [
        self::F_CLOSED,
        self::F_NET_IP_SERVICE,
        self::F_NET_IP_TRUSTED,
    ];

    const STR_TYPES = [
        self::F_NET_NAME,
        self::F_NET_ON_ABON_IP,
        self::F_NET_ON_ABON_MASK,
        self::F_NET_ON_ABON_GATE,
        self::F_NET_NAT11,
        self::F_NET_IP,
        self::F_NET_MASK,
        self::F_NET_GATEWAY,
        self::F_NET_DNS1,
        self::F_NET_DNS2,
        self::F_NET_MAC,
        self::F_COORD_GMAP,
    ];


    const NUM_TYPES = [
        self::F_ID,
        self::F_ABON_ID,
        self::F_PRICE_ID,
        self::F_TP_ID,
        self::F_DATE_START,
        self::F_DATE_END,
    ];

    

    /* ----------------
     * вычисляемые поля
     * ---------------- */

    const FF_P_TITLE            = 'title';              // prices.title,
    const FF_P_PPD              = 'pay_per_day';        // prices.pay_per_day,
    const FF_P_PPM              = 'pay_per_month';      // prices.pay_per_month,
    const FF_P_DESCR            = 'description';        // prices.description,
    const FF_TP_TITLE           = 'tp_title';           // tp_list.title
    const FF_TP_STATUS          = 'tp_status';          // tp_list.status
    const FF_TP_DELETED         = 'tp_deleted';         // tp_list.deleted
    const FF_TP_IS_MANAGED      = 'tp_is_managed';      // tp_list.is_managed




}