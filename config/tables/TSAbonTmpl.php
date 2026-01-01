<?php
/**
 *  Project : my.ri.net.ua
 *  File    : TSAbonTmpl.php
 *  Path    : config/tables/TSAbonTmpl.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:13:00
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of TSAbonTmpl.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace config\tables;

/**
 * Обёртка таблицы сопоставления текстовых шаблонов абонентам
 * ts_abons_templates
 */
class TSAbonTmpl
{
    /*
     * Имя таблицы
     */
    const TABLE = 'ts_abons_templates';

    /*
     * Основные поля
     */
    const F_ID           = 'id';            // ID записи
    const F_PPP_ID       = 'ppp_id';         // ППП, к которому относится шаблон
    const F_ABON_ID      = 'abon_id';        // Абонент, которому принадлежит шаблон
    const F_TEMPLATE     = 'template';       // Текстовый фрагмент для распознавания абонента

    /*
     * Служебные поля (кто / когда)
     */
    const F_CREATION_UID  = 'created_uid';    // Кто создал
    const F_CREATION_DATE = 'created_date';   // Когда создал
    const F_MODIFIED_UID  = 'modified_uid';   // Кто изменил
    const F_MODIFIED_DATE = 'modified_date';  // Когда изменил

    /*
     * Связанные таблицы (для JOIN / вычисляемых полей)
     */
    const REF_ABONS      = 'abons';           // abons.id
    const REF_PPP        = 'ppp_list';        // ppp_list.id
    const REF_USERS      = 'users';           // users.id

    /*
     * Вычисляемые / логические поля (если будут использоваться)
     */
    // const F_ABON_TITLE   = 'abon_title';    // Название / имя абонента (вычисляемое)
    // const F_PPP_TITLE    = 'ppp_title';     // Название ППП (вычисляемое)
    // const F_CREATED_USER = 'created_user';  // Пользователь-создатель (вычисляемое)
    // const F_MODIFIED_USER= 'modified_user'; // Пользователь-редактор (вычисляемое)
}