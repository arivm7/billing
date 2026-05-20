<?php
/**
 *  Project : my.ri.net.ua
 *  File    : FwInput.php
 *  Path    : config/FwInput.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:32:46
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of FwInput.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace config;

final class FwInput
{
    public const SESSION_FIELD = 'fw_wizard_mik';

    public const F_GET_PHASE = 'phase';
    public const F_GET_TP_ID = 'tp_id';
    
    public const PHASE_LOGIN = 'connect';
    public const PHASE_INTERFACE_LIST = 'interfaces';
    public const PHASE_NEIGHBORS = 'neighbor';
    public const PHASE_CERT = 'certificate';
    public const PHASE_IP_SERVICES = 'services';
    public const PHASE_FILTERS = 'firewall';

    public const PHASES_ORDER = [
        self::PHASE_LOGIN,
        self::PHASE_INTERFACE_LIST,
        self::PHASE_NEIGHBORS,
        self::PHASE_CERT,
        self::PHASE_IP_SERVICES,
        self::PHASE_FILTERS,
    ];

    public const PHASES_TITLES = [
        self::PHASE_LOGIN => [
            'en' => 'Connection', 
            'ru' => 'Подключение', 
            'uk' => 'Підключення'],
        self::PHASE_INTERFACE_LIST => [
            'en' => 'Lists WAN/LAN', 
            'ru' => 'Списки Списки WAN/LAN', 
            'uk' => 'Списки WAN/LAN'],
        self::PHASE_NEIGHBORS => [
            'en' => 'Visibility of neighbors',
            'ru' => 'Видимость соседей',
            'uk' => 'Видимість сусідів'],
        self::PHASE_CERT => [
            'en' => 'Certificate verification',
            'ru' => 'Проверка сертификата',
            'uk' => 'Перевірка сертифіката'],
        self::PHASE_IP_SERVICES => [
            'en' => 'Input Services',
            'ru' => 'Входные службы',
            'uk' => 'Вхідні служби'],
        self::PHASE_FILTERS => [
            'en' => 'Filter Rules',
            'ru' => 'Правила фильтров',
            'uk' => 'Правила фільтрів'],
    ];

    public const VIEWS = [
        self::PHASE_LOGIN => 'fw_input/login',
        self::PHASE_INTERFACE_LIST => 'fw_input/interfaces',
        self::PHASE_NEIGHBORS => 'fw_input/neighbors',
        self::PHASE_CERT => 'fw_input/certificate',
        self::PHASE_IP_SERVICES => 'fw_input/services',
        self::PHASE_FILTERS => 'fw_input/firewall',
    ];

    public static function isValid(string $phase): bool
    {
        return in_array($phase, self::PHASES_ORDER, true);
    }

    public static function next(string $phase): string
    {
        $i = array_search($phase, self::PHASES_ORDER, true);
        return self::PHASES_ORDER[$i + 1] ?? self::PHASE_LOGIN;
    }

    public static function prev(string $phase): string
    {
        $i = array_search($phase, self::PHASES_ORDER, true);
        return self::PHASES_ORDER[$i - 1] ?? self::PHASE_LOGIN;
    }
}