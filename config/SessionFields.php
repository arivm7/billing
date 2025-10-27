<?php
/*
 *  Project : my.ri.net.ua
 *  File    : SessionFields.php
 *  Path    : config/SessionFields.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Имена полей переменных, сохраняемых и передаваемых через сессию
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace config;


class SessionFields {

    public const ERROR        = 'error';
    public const ERROR_AUTO   = 'error_auto';
    public const SUCCESS      = 'success';
    public const SUCCESS_AUTO = 'success_auto';
    public const INFO         = 'info';
    public const INFO_AUTO    = 'info_auto';
    public const WARNING      = 'warning';
    public const WARNING_AUTO = 'warning_auto';

    public const INFO_TIMEOUT    =  5000; // ms
    public const ERROR_TIMEOUT   = 10000; // ms
    public const WARNING_TIMEOUT = 10000; // ms
    public const SUCCESS_TIMEOUT =  3000; // ms

    public const FORM_DATA    = 'form_data';
    public const A_REST_FIELD = 'abon_rest';
    public const A_REST_VALUE = 1;
    public const A_REST_TIME  = 60*60*1; // 1 часов

}