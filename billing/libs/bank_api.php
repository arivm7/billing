<?php
/**
 *  Project : my.ri.net.ua
 *  File    : bank_api.php
 *  Path    : billing/libs/bank_api.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Oct 2025 19:53:55
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of bank_api.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



const PAYMENT_MIN        = 50;   // сумма минимального платежа


/**
 * Поддерживаемые АПИ
 */
const API_TYPE_BANK_CARD  = 'bank_card';
const API_TYPE_P24_ACC    = 'p24_acc';
const API_TYPE_P24_LIQPAY = 'p24_liqpay';
const API_TYPE_P24PAY     = 'p24pay';
const API_TYPE_P24_MANUAL = 'p24_manual';
const API_TYPE_MONO_CARD  = 'mono_pay';

const API_TYPE_LIST = [
    API_TYPE_BANK_CARD,
    API_TYPE_P24_ACC,
    API_TYPE_P24_LIQPAY,
    API_TYPE_P24PAY,
    API_TYPE_P24_MANUAL,
    API_TYPE_MONO_CARD,
];


