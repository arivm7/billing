<?php
/**
 *  Project : my.ri.net.ua
 *  File    : config_mik.php
 *  Path    : config/config_mik.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:32:46
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of config_mik.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


return [
    
    
    
    /**
     * Блок Firewall
     * FW Input
     */
    'fw_input_def_tp_id' => 0,
    'fw_input_def_host' => '',
    'fw_input_def_port' => 8729,
    'fw_input_def_ssl' => 1,
    'fw_input_def_login' => '',
    'fw_input_def_password' => '',



    'interface_lists' => [
        'lan' => 'LAN',
        'wan' => 'WAN',
    ],



    'certificate' => [
        'name'              => 'cert1',
        'key_usage'         => 'key-cert-sign,crl-sign', 
        'key_size'          => 2048,
        'country'           => 'UA',
        'state'             => 'UA',
        'locality'          => 'Kiev',
        'organization'      => 'RI-Network',
        'unit'              => 'Tech',
        'days_valid'        => 1825,
        'sign_poll_tries'   => 10,
        'sign_poll_sleep'   => 6,
        'pre_sign_sleep'    => 1,
        'verify_sleep'      => 2,
    ],



    'services_ssl' => [
        'www-ssl', 'api-ssl', 'reverse-proxy'
    ],



    'services' => [
        'api'       => ['port' => 18728, 'ena' => false],
        'api-ssl'   => ['port' => 18729, 'ena' => true],
        'www'       => ['port' => 10001, 'ena' => false],
        'www-ssl'   => ['port' => 20001, 'ena' => true],
        'winbox'    => ['port' => 30001, 'ena' => true],
        'ssh'       => ['port' => 40001, 'ena' => true],
        'ftp'       => ['port' => 21231, 'ena' => true],
        'telnet'    => ['port' => 21233, 'ena' => false],
    ],



    'neighbor_discovery_default' => 'LAN',



    'fw_input' => [
        'ping_timeout' => '3d',
        'allowed_tcp_extra' => [],
        'allowed_udp_extra' => [],
    ],
    
    
    
];