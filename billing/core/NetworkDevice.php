<?php
/**
 *  Project : my.ri.net.ua
 *  File    : NetworkDevice.php
 *  Path    : billing/core/NetworkDevice.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:32:46
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of NetworkDevice.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace billing\core;

use billing\core\base\Model;
use config\tables\TP;
use Exception;

require_once DIR_LIBS . '/functions.php';

/**
 * Абстракция для сетевых устройств
 */
abstract class NetworkDevice {

    const LIST_DNS='DNS';
    const LIST_ABON='ABON';
    const LIST_HACKERS='HACKERS';
    const LIST_FLOOD='_FLOOD';

    public static array $messages = [];


    public array $TP = [];
    
    public function __construct(?int $tp_id = null, ?array $tp = null) {
        
        if (is_null($tp_id) && is_null($tp)) {
            throw new Exception('NetworkDevice::__construct -- ' 
                    . __('Не указаны параметры техплощадки') . '. ' 
                    . __('Нужно передать ID техплощадки или запись техплощадки'));
        }
        $model = new Model();
        if (!empty($tp) && is_array($tp)) {
            if (!empty($tp[TP::F_ID])) {
                /*
                 * имеющаяся ТП
                 */
                if (!$model->validate_id(TP::TABLE, $tp[TP::F_ID], TP::F_ID)) {
                    throw new Exception('NetworkDevice::__construct -- ' . __('Не верная запись техплощадки'));
                }
            } else {
                /*
                 * Новая ТП
                 */
                if (empty($tp[TP::F_MIK_IP]) || !validate_ip($tp[TP::F_MIK_IP])) {
                    throw new Exception('NetworkDevice::__construct -- ' . __('Не верный ip доступа к техплощадке'));
                }
                if (empty($tp[TP::F_MIK_PORT] && empty($tp[TP::F_MIK_PORT_SSL]))) {
                    throw new Exception('NetworkDevice::__construct -- ' . __('Не верный порт доступа к техплощадке'));
                }
                if (empty($tp[TP::F_MIK_LOGIN]) || empty($tp[TP::F_MIK_PASSWD])) {
                    throw new Exception('NetworkDevice::__construct -- ' . __('Не верные данные авторизации'));
                }
            }
            $this->TP = $tp;
        } else {
            if (!$model->validate_id(TP::TABLE, $tp_id, TP::F_ID)) {
                throw new Exception('NetworkDevice::__construct -- ' . __('Неверный id техплощадки'));
            }
            $this->TP = $model->get_tp($tp_id);
        }

    }
    
    
//    abstract public function get_description(): array;
//    abstract public function get_hostname(): string;
//    abstract public function get_uptime(): int;
//    abstract public function get_memory_total(): int;
//    abstract public function get_memory_free(): int;
//    abstract public function get_hdd_total(): int;
//    abstract public function get_hdd_free(): int;
//    abstract public function get_hdd_bad_blocks(): int;
//    abstract public function get_cpu_name(): string;
//    abstract public function get_cpu_frequency(): int;
//    abstract public function get_cpu_count(): int;
//    abstract public function get_cpu_load(): int;
//    abstract public function get_factory(): string;
//    abstract public function is_bridge(): bool;
//    abstract public function is_tp(): bool;


    
//    abstract public function get_list_items(string $list, ?string $ip = null, int|bool|null $ena = null): array;
//    abstract public function set_list_item(string $list, string $ip, int|bool $ena, string $descr): bool;
//    abstract public function in_list_item(string $list, string $ip, int|bool $ena): bool;



//    abstract public function validate_filter_input(): bool;
//    abstract public function validate_filter_output(): bool;
//    abstract public function validate_filter_forward(): bool;
//    abstract public function validate_filter_dns(): bool;
//    abstract public function validate_filter_flood(): bool;
//    abstract public function validate_filter_hackers(): bool;


//    abstract public function get_gateways(): array;
//    abstract public function is_gateway(string $ip): bool;
    
//    abstract public function get_nat_list(): array;
//    abstract public function get_nat_uv(): array;
//    abstract public function get_nat_maskarade(): array;
//    abstract public function get_nat_11(): array;
//    abstract public function set_nat_11(string $ip_local, string $ip_public, string $descr): bool;
    
//    abstract public function get_nat_netmap(): array;
//    abstract public function set_nat_netmap(string $proto, string $port_public, string $ip_local, string $port_local, string $descr): array;
//    abstract public function in_nat_netmap(?string $proto = null, ?string $port_public = null, ?string $ip_local = null, ?string $port_local = null, ?string $descr = null): bool;
    
    
    
    
    
    
    
    
    
}