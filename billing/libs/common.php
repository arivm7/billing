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


enum DataTypes {
    case INT;
    case INT_NULABLE;
    case LONG;
    case FLOAT;
    case STR;
}
