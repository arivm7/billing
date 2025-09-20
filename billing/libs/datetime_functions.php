<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : datetime_functions.php
 *  Path    : billing/libs/datetime_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 19 Sep 2025 22:44:34
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



define('TWENTY_FOUR_HOURS',   86400); // twenty-four hours -- сутки
define('HOURS24', TWENTY_FOUR_HOURS); // twenty-four hours -- сутки
define('ONE_DAY', TWENTY_FOUR_HOURS); // twenty-four hours -- сутки


// "d m Y H:i:s"
define("DATE_FORMAT",     "Y-m-d");
define("DATETIME_FORMAT", "Y-m-d H:i:s"); // "Y-m-d H:i:s"


$YESTERDAY = \mktime(0, 0, 0, \date("m"), \date("d")-1, \date("Y"));
$TODAY     = \mktime(0, 0, 0, \date("m"), \date("d"),   \date("Y"));
$TOMORROW  = \mktime(0, 0, 0, \date("m"), \date("d")+1, \date("Y"));



/**
 * вчера
 * @global type $TODAY
 * @param int $today
 * @return int
 */
function YESTERDAY(int $today = NA): int {
    global  $TODAY;
    if($today == NA) { $today = $TODAY; }
    return \mktime(0, 0, 0, \date("m", $today), \date("d", $today)-1, \date("Y", $today));
}



/**
 * сегодня
 * @global int $TODAY
 * @return int
 */
function TODAY():int {
    return \mktime(0, 0, 0, \date("m"), \date("d"),   \date("Y"));
}



/**
 * завтра
 * @global int $TOMORROW
 * @return int
 */
function TOMORROW():int  { global $TOMORROW;  return $TOMORROW;  }



/**
 * Возвращает только дату, без времени
 * @param int $datetime
 * @return int
 */
function get_date(int $datetime): int {
    return \mktime(0, 0, 0, \date("m", $datetime), \date("d", $datetime), \date("Y", $datetime));
}



function date_human_str($dt):string {
    //the day before yesterday
    //yesterday
    //today
    //tomorrow
    //the day after tomorrow

    //  hour = \date("H"), $minute = \date("i"), $second = \date("s"), $month = \date("n"), $day = \date("j"), $year = \date("Y"), $is_dst = -1 )
    //                        h  m  s  m  d  y
    $day_yesterday2 = \mktime (0, 0, 0, \date("n"), \date("j")-2, \date("Y"));  //echo "yesterday2: ".date('Y-m-d H:i', $day_yesterday2)."<br>\n";
    $day_yesterday  = \mktime (0, 0, 0, \date("n"), \date("j")-1, \date("Y"));  //echo " yesterday: ".date('Y-m-d H:i', $day_yesterday)."<br>\n";
    $day_today      = \mktime (0, 0, 0, \date("n"), \date("j"), \date("Y"));    //echo "     today: ".date('Y-m-d H:i', $day_today)."<br>\n";
    $day_tomorrow   = \mktime (0, 0, 0, \date("n"), \date("j")+1, \date("Y"));  //echo "  tomorrow: ".date('Y-m-d H:i', $day_tomorrow)."<br>\n";
    $day_tomorrow2  = \mktime (0, 0, 0, \date("n"), \date("j")+2, \date("Y"));  //echo " tomorrow2: ".date('Y-m-d H:i', $day_tomorrow2)."<br>\n";

//              mktime (h, m, s, $month,         $day,           $year,     $is_dst)
//  $dt_today = mktime (0, 0, 0, \date("n", $dt), \date("j", $dt), \date("Y"), $dt); было так
    $dt_today = \mktime (0, 0, 0, \date("n", $dt), \date("j", $dt), \date("Y"));
    $dt_day_str = \date('Y-m-d', $dt);
    $dt_time_str = \date('H:i', $dt);

    switch ($dt_today) {
        case ($day_yesterday2):
            $dt_day_str = "Позавчора";
            break;
        case ($day_yesterday):
            $dt_day_str = "Вчора";
            break;
        case ($day_today):
            $dt_day_str = "Сьогодні";
            break;
        case ($day_tomorrow):
            $dt_day_str = "Завтра";
            break;
        case ($day_tomorrow2):
            $dt_day_str = "Післязавтра";
            break;
    }
    $dt_str = $dt_day_str." ".$dt_time_str;
    return $dt_str;

}



/**
 * Возвраащет день месяца
 * @param int $datetime
 * @param string $format
 *        d   День месяца, 2 цифры с ведущим нулём    от 01 до 31
 *        j   День месяца без ведущего нуля           от 1 до 31
 * @return string
 */
function day($datetime = NA, $format="j"):string {
    if($datetime == NA) { $datetime = time(); }
    //  d   День месяца, 2 цифры с ведущим нулём    от 01 до 31
    //  j   День месяца без ведущего нуля           от 1 до 31
    return \date($format, $datetime);
}



/**
 * Возвращает номер месяца
 * @param int $datetime
 * @param string $format = "n"
 *  n Порядковый номер месяца без ведущего нуля     от 1 до 12
 *  m Порядковый номер месяца с ведущим нулём       от 01 до 12
 *  F Полное наименование месяца                    от January до December
 *  M Сокращённое наименование месяца, 3 символа    от Jan до Dec
 *  t Количество дней в указанном месяце            от 28 до 31
 * @return string
 */
function month($datetime = NA, $format="n"):string {
    if($datetime == NA) { $datetime = time(); }
    return \date($format, $datetime);
}



function year($datetime = NA, $format="Y") {
    if($datetime == NA) { $datetime = time(); }
    //    Y 	Порядковый номер года, 4 цифры 	Примеры: 1999, 2003
    return \date($format, $datetime);
}



function date_Ymd(int|null $timestamp, $value_if_null = ""): string {
    return (!is_null($timestamp) && ($timestamp > 0) ? date("Y-m-d", $timestamp): $value_if_null);
}


/**
 * Возвращает строку вида "08.2023" для указанной даты
 * @param int $datetime -- для та для которой нужно вернуть "мм.гггг"
 * @param string $format -- Формат вывода строки
 * @return string -- строка даты вида "08.2023"
 */
function month_year(int $datetime = NA, string $format="n.Y"): string {
    if($datetime == NA) { $datetime = time(); }
    return \date($format, $datetime);
}



/**
 * Возвращает количество дней в месяце
 * @param int $datetime
 * @return int $datetime
 */
function days_of_month($datetime = NA):int {
    if($datetime == NA) { $datetime = time(); }
    //  t   Количество дней в указанном месяце      от 28 до 31
    return \date("t", $datetime);
}



/**
 * Возвращает дату ПЕРВОГО дня предыдущего месяца
 * @param int $datetime
 * @return int $datetime
 */
function first_day_prev_month($datetime = NA) {
    if($datetime == NA) { $datetime = time(); }
    return \mktime(0, 0, 0, month($datetime)-1, 1, year($datetime));
}



/**
 * Возвращает дату ПЕРВОГО дня текущего месяца
 * @param int $datetime
 * @return int $datetime
 */
function first_day_month($datetime = NA) {
    if($datetime == NA) { $datetime = time(); }
    return \mktime(0, 0, 0, month($datetime), 1, year($datetime));
}



/**
 * Возвращает дату последнего дня текущего месяца
 * @param int $datetime
 * @return int $datetime
 */
function last_day_month($datetime = NA) {
    if($datetime == NA) { $datetime = time(); }
    return \mktime(0, 0, 0, month($datetime), days_of_month($datetime), year($datetime));
}



/**
 * Возвращает дату первого дня следующего месяца
 * @param int $datetime или NA -- без значения, т.е. текущая дата
 * @return int $datetime
 */
function next_month_first_day($datetime = NA) {
    if($datetime == NA) { $datetime = time(); }
    return \mktime(0, 0, 0, month($datetime)+1, 1, year($datetime));
}



/**
 * Возвращает дату последнего дня следующего месяца
 * @param int $datetime или NA -- без значения, т.е. текущая дата
 * @return int $datetime
 */
function next_month_last_day($datetime = NA) {
    if($datetime == NA) { $datetime = time(); }
    $next_month_first_day = next_month_first_day($datetime);
    return \mktime(0, 0, 0, month($next_month_first_day), days_of_month($next_month_first_day), year($next_month_first_day));
}



/**
 * Возвращает дату без времени в формате TIMESTAMP
 * @param $datetime
 * @return int
 */
function date_only(int $datetime = NA): int {
    if($datetime === NA) { $datetime = time(); }
    return \mktime(0, 0, 0, month($datetime), day($datetime), year($datetime));
}



define('IGNORE_TIME_ON',  true);
define('IGNORE_TIME_OFF', false);
define('NULL_HAS_TODAY',  false);
define('NULL_HAS_NULL',   true);



/**
 * Считает количество дней между датами
 * Если отсчётное действие совершено в date1, то до date2 нужно считать 3 дня
 *
 *    0         24        0         24
 * ---|---------|---------|---------|---------|---
 *        |     0         24        0     |   24
 *        date1                       date2
 *
 * @param int $date1
 * @param int $date2
 * @param bool $null_has_today
 * @param bool $ignore_time
 * @return mixed
 */
function get_between_days($date1, $date2, $null_has_today = NULL_HAS_TODAY, $ignore_time = IGNORE_TIME_ON) {
    global $TODAY;
    if((is_null($date1) || ($date1 == NA)) && $null_has_today) {
        $date1 = $TODAY;
    }
    if((is_null($date2) || ($date2 == NA)) && $null_has_today) {
        $date2 = $TODAY;
    }
    if(is_null($date1) || is_null($date2)) {
        return null;
    }
    if($ignore_time) {
        $date1 = \mktime(0, 0, 0, \date("m", $date1), \date("d", $date1), \date("Y", $date1));
        $date2 = \mktime(0, 0, 0, \date("m", $date2), \date("d", $date2), \date("Y", $date2));
    }
    $d1 = new DateTime("@".$date1);
    $d2 = new DateTime("@".$date2);
    $diff = date_diff($d1, $d2);
    return $diff->days;
}



// $month_ua_n = array("Січень", "Лютий",  "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад",  "Грудень");
// $month_ua_r = array("Січня",  "Лютого", "Березня",  "Квітня",  "Травня",  "Червня",  "Липня",  "Серпня",  "Вересня",  "Жовтня",  "Листопада", "Грудня");



function rus_in_date($param, $time=0) {
	if(intval($time)==0)$time=time();
	$MonthNames=array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
	if(strpos($param,'M')===false) return \date($param, $time);
		else return \date(str_replace('M',$MonthNames[date('n',$time)-1],$param), $time);
}



function ukr_in_date($param, $time=0) {
	if(intval($time)==0)$time=time();
	$MonthNames=array("Січень", "Лютий", "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень");
	if(strpos($param,'M')===false) return \date($param, $time);
		else return \date(str_replace('M',$MonthNames[date('n',$time)-1],$param), $time);
}



function rdate($param, $time=0) {
    if(intval($time)==0)$time=time();
    $MN=array(«Января», «Февраля», «Марта», «Апреля», «Мая», «Июня», «Июля», «Августа», «Сентября», «Октября», «Ноября», «Декабря»);
    $MonthNames[]=$MN[date('n',$time)-1];
    $MN=array(«Воскресенье»,«Понедельник», «Вторник», «Среда», «Четверг», «Пятница», «Суббота»);
    $MonthNames[]=$MN[date('w',$time)];
    $arr[]='M';
    $arr[]='N';
    if(strpos($param,'M')===false) {
        return \date($param, $time);
    } else {
        return \date(str_replace($arr,$MonthNames,$param), $time);
    }
}



/*
 * PHP функция для нахождения разности между двумя датами
 * Источник: http://savvateev.org/blog/37/
   В качестве параметров, функция принимает две даты, между которыми будет находится разница. Если второй параметр опущен, то в качестве него будет принята текущая дата. Порядок задания дат значения не имеет. То есть, не важно, какая дата будет на первом месте большая или меньшая.
   Формат дат следующий:
   [год]-[номер месяца]-[число] [часы]:[минуты]:[секунды]
   Время указывать тоже не обязательно. Запись 2010-03-31 будет эквивалентна 2010-03-31 00:00:00
 */
function real_date_diff($date1, $date2 = NULL)
{
    $diff = array();

    //Если вторая дата не задана принимаем ее как текущую
    if(!$date2) {
        $cd = getdate();
        $date2 = $cd['year'].'-'.$cd['mon'].'-'.$cd['mday'].' '.$cd['hours'].':'.$cd['minutes'].':'.$cd['seconds'];
    }

    //Преобразуем даты в массив
    $pattern = '/(\d+)-(\d+)-(\d+)(\s+(\d+):(\d+):(\d+))?/';
    \preg_match($pattern, $date1, $matches);
    for($i=5;$i<=7;$i++)if(!isset($matches[$i]))$matches[$i]=0;
    $d1 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);
    \preg_match($pattern, $date2, $matches);
    for($i=5;$i<=7;$i++)if(!isset($matches[$i]))$matches[$i]=0;
    $d2 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);

    //Если вторая дата меньше чем первая, меняем их местами
    for($i=0; $i<count($d2); $i++) {
        if($d2[$i]>$d1[$i]) break;
        if($d2[$i]<$d1[$i]) {
            $t = $d1;
            $d1 = $d2;
            $d2 = $t;
            break;
        }
    }

    //Вычисляем разность между датами (как в столбик)
    $md1 = array(31, $d1[0]%4||(!($d1[0]%100)&&$d1[0]%400)?28:29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $md2 = array(31, $d2[0]%4||(!($d2[0]%100)&&$d2[0]%400)?28:29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $min_v = array(NULL, 1, 1, 0, 0, 0);
    $max_v = array(NULL, 12, $d2[1]==1?$md2[11]:$md2[$d2[1]-2], 23, 59, 59);
    for($i=5; $i>=0; $i--) {
        if($d2[$i]<$min_v[$i]) {
            $d2[$i-1]--;
            $d2[$i]=$max_v[$i];
        }
        $diff[$i] = $d2[$i]-$d1[$i];
        if($diff[$i]<0) {
            $d2[$i-1]--;
            $i==2 ? $diff[$i] += $md1[$d1[1]-1] : $diff[$i] += $max_v[$i]-$min_v[$i]+1;
        }
    }

    //Возвращаем результат
    return $diff;
}



/**
 * From MySQL datetime to Unix Timestamp 2003-12-30 23:30:59 -> 1072834230
 * @param $mysql_datetime
 * @return int unixtimestamp
 */
function convertMysqlDateTimeToUnixTimeStamp($mysql_datetime) {
    $yr=strval(substr($mysql_datetime,0,4));
    $mo=strval(substr($mysql_datetime,5,2));
    $da=strval(substr($mysql_datetime,8,2));
    $hr=strval(substr($mysql_datetime,11,2));
    $mi=strval(substr($mysql_datetime,14,2));
    $se=strval(substr($mysql_datetime,17,2));
    return \mktime($hr,$mi,$se,$mo,$da,$yr);
}


