<?php


namespace config;


class SessionFields {

    public const ERROR        = 'error';
    public const ERROR_AUTO   = 'error_auto';
    public const SUCCESS      = 'success';
    public const SUCCESS_AUTO = 'success_auto';
    public const INFO         = 'info';
    public const INFO_AUTO    = 'info_auto';

    public const INFO_TIMEOUT    =  5000; // ms
    public const ERROR_TIMEOUT   = 10000; // ms
    public const SUCCESS_TIMEOUT =  3000; // ms

    public const FORM_DATA    = 'form_data';
    public const A_REST_FIELD = 'abon_rest';
    public const A_REST_VALUE = 1;
    public const A_REST_TIME  = 60*60*1; // 1 часов

}
