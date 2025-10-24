<?php
/*
 *  Project : my.ri.net.ua
 *  File    : common.php
 *  Path    : billing/libs/common.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 22:26:09
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

use \billing\core\base\Lang;

define('NA',      -1); // N/A -- Значение не определено
define('CR', '&#10;');

define('FIELD_ID', 'id');

define('FIELD_CREATION_DATE', 'creation_date');
define('FIELD_CREATION_UID',  'creation_uid');
define('FIELD_MODIFIED_DATE', 'modified_date');
define('FIELD_MODIFIED_UID',  'modified_uid');

define('TARGET_BLANK',  '_blank');
define('TARGET_PARENT', '_parent');
define('TARGET_SELF',   '_self');
define('TARGET_TOP',    '_top');

const FORM_DATE_TIME = 'Y-m-d\TH:i';


const ICON_SIZE       = 18; // размер иконок
const ICON_WIDTH_DEF  = ICON_SIZE;      $ICON_WIDTH_DEF  = ICON_WIDTH_DEF;
const ICON_HEIGHT_DEF = ICON_SIZE;      $ICON_HEIGHT_DEF = ICON_HEIGHT_DEF;


enum DebugView: string
{
    case ECHO = '1';
    case PRINTR = '2';
    case DUMP = '3';
}





enum DataTypes {
    case INT;
    case INT_NULABLE;
    case LONG;
    case FLOAT;
    case STR;
}


class AbonStatus
{
    const ABON_0        = -1000; // Нулевой абон -- служебный ИД
    const NA            =  -100; // Вообще не абон. Не прошел валидность ИД
    const SW            =   -10; // Свитч
    const OFF           =    -2; // Не плательщик
    const LONG_PAUSED   =    -1; // долго на паузе, т.е. потенциальный OFF
    const PAUSED        =     0; // Отключены прайсы, но, плательщик
    const WARN          =     1; // Есть активные прайсы, должник в статусе "Требуется уведомление"
    const WARN2         =     2; // Есть активные прайсы, должник в статусе "Нужно отключать"
    const OK            =     3; // Есть активные прайсы, "+" на ЛС
}


class AbonStatusTitle
{
    const ABON_0      = 'AID служебный или отстутствует';
    const NA     = 'AID не прошел проверку на валидность';
    const SW          = 'Коммутатор или другое служебное сетевое устройство';
    const OFF    = 'Абонентт НЕ плательщик';
    const LONG   = 'Долго на паузе, потенциальный НЕ плательщик.';
    const PAUSED = 'На паузе: Отключены прайсы, но, потенциально, плательщик.';
    const WARN   = 'Есть активные прайсы, должник в статусе &laquo;Требуется уведомление&raquo;"';
    const WARN2  = 'Есть активные прайсы, должник в статусе &laquo;Нужно отключать&raquo;"';
    const OK     = 'Есть активные прайсы, &laquo;+&raquo; на ЛС, или в зоне &laquo;Уведомление не требуется&raquo;';
}


class MikRuleTypes {
    const NAT = -2;
    const IP  = -3;
    const UV  = -4;
}

class MikAbonStatus {

    const ABON_0  = 0;
    const XZ = -100;
    const SW = -1;

}



/**
 * Cтатус для предупреждения абонента
 * в завиимости от оставшихся предоплаченных дней и статуса услуги
 */
enum DutyWarn {
    case NA;        // "Статус не понятен, этого не должно быть."
    case ON_PAUSE;  // "Услуга на паузе."
    case NORMAL;    // "Оплата есть. Услуга подключена."
    case WARN;      // "Требуется оплата. Услуга подключена"
    case NEED_OFF;  // "Оплаты давно нет, нужно отключать. Услуга подключена"
    case INFO;     // "INFO. Услуга подключена"
}



/**
 * Типы сервисов, предоствляемых абонентам
 */
enum ServiceType {
    case INTERNET;
    case TV;
    case CCTV; // Видеонаблюдение
    case OTHER;
}


/**
 * Массив названий сервисов по языкам
 */
class ServiceTitles
{
    public static array $MAP = [
        ServiceType::INTERNET->name => [
            Lang::C_RU => 'Интернет',
            Lang::C_UK => 'Інтернет',
            Lang::C_EN => 'Internet',
        ],
        ServiceType::TV->name => [
            Lang::C_RU => 'Телевидение',
            Lang::C_UK => 'Телебачення',
            Lang::C_EN => 'TV',
        ],
        ServiceType::CCTV->name => [
            Lang::C_RU => 'Видеонаблюдение',
            Lang::C_UK => 'Відеоспостереження',
            Lang::C_EN => 'CCTV',
        ],
        ServiceType::OTHER->name => [
            Lang::C_RU => 'Подключенная услуга',
            Lang::C_UK => 'Підключена послуга',
            Lang::C_EN => 'Connected service',
        ],
    ];

    public static function get(ServiceType|string $type, ?string $lang = null): string
    {
        $lang = $lang ?? Lang::code();
        $key  = $type instanceof ServiceType ? $type->name : (string)$type;
        return self::$MAP[$key][$lang] ?? self::$MAP[$key][Lang::C_EN] ?? '';
    }
}


// define('MAX_COMMENT_LENGTH', 20);
