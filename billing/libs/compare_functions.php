<?php



/**
 * Сравнение float чисел с точностью ACCURACY
 * @param float $a
 * @param float $b
 * @return int -- возвращает -1 | 0 | 1
 */
function cmp_float(float $a, float $b): int {
    if (round($a * ACCURACY) == round($b * ACCURACY)) { return 0; }
    return (($a < $b) ? -1 : 1);
}



/**
 * Сравнение ipv4 путем преобразования в long
 * @param string $a
 * @param string $b
 * @return int
 */
function cmp_ipv4(string|null $a, string|null $b): int {
    if (($a == $b)) { return 0; }
    if (is_null($a))  { return -1; }
    if (is_null($b))  { return  1; }
    if (trim($a) == trim($b)) { return 0; }
    return ((ip2long($a) < ip2long($b)) ? -1 : 1);
}



/**
 * Сравнение записей по полю [prepayed].
 * Если поля [prepayed] равны, то стравниваются поля [balance]
 * @param $a
 * @param $b
 * @return int
 */
function compare_prepayed_asc($a, $b): int {
    if ((is_null($a['prepayed']) && is_null($b['prepayed'])) || ($a['prepayed'] == $b['prepayed'])) {
        if ($a['balance'] == $b['balance']) {
            return 0;
        } else {
            return (($a['balance'] < $b['balance']) ? -1 : 1);
        }
    }
    if (is_null($a['prepayed'])) {
        return 1;
    }
    if (is_null($b['prepayed'])) {
        return -1;
    }
    return (($a['prepayed'] < $b['prepayed']) ? -1 : 1);
}



/**
 * Сравнение записей по полю [prepayed].
 * Если поля [prepayed] равны, то стравниваются поля [balance]
 * @param $a
 * @param $b
 * @return int
 */
function compare_prepayed_desc($a, $b): int {
    return compare_balance_asc($b, $a);
}



/**
 * Сравнение записей по полю [balance]
 * @param $a
 * @param $b
 * @return int
 */
function compare_balance_asc($a, $b): int {
    if ($a['balance'] == $b['balance']) {
        return 0;
    }
    return (($a['balance'] < $b['balance']) ? -1 : 1);
}



/**
 * Сравнение записей по полю [balance]
 * @param $a
 * @param $b
 * @return int
 */
function compare_balance_desc($a, $b): int {
    return compare_balance_asc($b, $a);
}



/**
 * Сравнение записей по полю [PP30A]
 * @param $a
 * @param $b
 * @return int
 */
function compare_pp30a_asc($a, $b): int {
    if ($a['PP30A'] == $b['PP30A']) {
        return 0;
    }
    return (($a['PP30A'] < $b['PP30A']) ? -1 : 1);
}



/**
 * Сравнение записей по полю [PP30A]
 * @param $a
 * @param $b
 * @return int
 */
function compare_pp30a_desc($a, $b): int {
    return compare_pp30a_asc($b, $a);
}



/**
 * Сравнение записей по полю ['address']
 * @param $a
 * @param $b
 * @return int
 */
function compare_address_asc($a, $b): int {
    $eq = substr_compare($a['address'], $b['address'], 0, null, true);
    if ($eq == 0) {
        return ((mb_strlen($a['address']) < mb_strlen($b['address'])) ? -1 : 1);
    } else {
        return $eq;
    }
}



/**
 * Сравнение записей по полю ['address']
 * @param $a
 * @param $b
 * @return int
 */
function compare_address_desc($a, $b): int {
    return compare_address_asc($b, $a);
}



