<?php


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



/**
 * Статус прикрепленных прайсовых фрагментов
 * CLOSED, CURRENT, FUTURE
 */
enum PAStatus: int  {
    case FUTURE         = 0b00000001;
    case CURRENT        = 0b00000010;
    case CLOSE_TODAY    = 0b00000100;
    case CLOSED         = 0b00001000;
    case FULL_CLOSED    = 0b10000000;
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


define('MAX_COMMENT_LENGTH', 20);
