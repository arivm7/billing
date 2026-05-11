<?php




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

    public static array $errors = [];


    public array $TP = [];
    
    public function __construct(?int $tp_id = null, ?array $tp = null) {
        
        if (is_null($tp_id) && is_null($tp)) {
            throw new Exception('NetworkDevice::__construct -- не указаны параметры ТП.' . ' ' . 'Нужно передать ID ТП или запись TP');
        }
        $model = new Model();
        if (!empty($tp) && is_array($tp)) {
            if (!empty($tp[TP::F_ID])) {
                /*
                 * имеющаяся ТП
                 */
                if (!$model->validate_id(TP::TABLE, $tp[TP::F_ID], TP::F_ID)) {
                    throw new Exception('NetworkDevice::__construct -- не верная запись ТП');
                }
            } else {
                /*
                 * Новая ТП
                 */
                if (empty($tp[TP::F_MIK_IP]) || !validate_ip($tp[TP::F_MIK_IP])) {
                    throw new Exception('NetworkDevice::__construct -- не верный ip доступа к ТП');
                }
                if (empty($tp[TP::F_MIK_PORT_SSL])) {
                    throw new Exception('NetworkDevice::__construct -- не верный порт доступа к ТП');
                }
                if (empty($tp[TP::F_MIK_LOGIN]) || empty($tp[TP::F_MIK_PASSWD])) {
                    throw new Exception('NetworkDevice::__construct -- не верные данные авторизации');
                }
            }
            $this->TP = $tp;
        } else {
            if (!$model->validate_id(TP::TABLE, $tp_id, TP::F_ID)) {
                throw new Exception('NetworkDevice::__construct -- неверный идентификатор ТП');
            }
            $this->TP = $model->get_row_by_id(TP::TABLE, $tp_id, TP::F_ID);
        }

    }
    
    
    abstract public function get_description(): array;
    abstract public function get_hostname(): string;
    abstract public function get_uptime(): int;
    abstract public function get_memory_total(): int;
    abstract public function get_memory_free(): int;
    abstract public function get_hdd_total(): int;
    abstract public function get_hdd_free(): int;
    abstract public function get_hdd_bad_blocks(): int;
    abstract public function get_cpu_name(): string;
    abstract public function get_cpu_frequency(): int;
    abstract public function get_cpu_count(): int;
    abstract public function get_cpu_load(): int;
    abstract public function get_factory(): string;
    abstract public function is_bridge(): bool;
    abstract public function is_tp(): bool;


    
    abstract public function get_list_items(string $list, ?string $ip = null, int|bool|null $ena = null): array;
    abstract public function set_list_item(string $list, string $ip, int|bool $ena, string $descr): bool;
    abstract public function in_list_item(string $list, string $ip, int|bool $ena): bool;

    public function get_list_abon(?string $ip = null, ?int $abon_id = null, int|bool|null $ena = null): array {
        // !!! abon_id
        return $this->get_list_items(self::LIST_ABON, $ip, $ena);
    }
    
    public function set_list_abon(string $ip, ?int $abon_id = null, int|bool $ena, string $descr): bool {
        // !!! abon_id
        return $this->set_list_item(self::LIST_ABON, $ip, $ena, $descr);
    }
    
    public function in_list_abon(?string $ip, ?int $abon_id = null, int|bool|null $ena = null): bool {
        // !!! abon_id
        return $this->in_list_item(self::LIST_ABON, $ip, $ena);
    }
        
    
    
    public function get_list_dns(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_list_items(self::LIST_DNS, $ip, $ena);
    }
    
    public function set_list_dns(string $ip, int|bool $ena, string $descr): bool {
        return $this->set_list_item(self::LIST_DNS, $ip, $ena, $descr);
    }
    
    public function in_list_dns(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_list_item(self::LIST_DNS, $ip, $ena);
    }



    public function get_list_hackers(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_list_items(self::LIST_HACKERS, $ip, $ena);
    }

    public function set_list_hackers(string $ip, int|bool $ena, string $descr): bool {
        return $this->set_list_item(self::LIST_HACKERS, $ip, $ena, $descr);
    }

    public function in_list_hackers(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_list_item(self::LIST_HACKERS, $ip, $ena);
    }



    public function get_list_flood(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_list_items(self::LIST_FLOOD, $ip, $ena);
    }

    public function set_list_flood(string $ip, int|bool $ena, string $descr): bool {
        return $this->set_list_item(self::LIST_FLOOD, $ip, $ena, $descr);
    }

    public function in_list_flood(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_list_item(self::LIST_FLOOD, $ip, $ena);
    }



    abstract public function validate_filter_input(): bool;
    abstract public function validate_filter_output(): bool;
    abstract public function validate_filter_forward(): bool;
    abstract public function validate_filter_dns(): bool;
    abstract public function validate_filter_flood(): bool;
    abstract public function validate_filter_hackers(): bool;


    abstract public function get_gateways(): array;
    abstract public function is_gateway(string $ip): bool;
    
    abstract public function get_nat_list(): array;
    abstract public function get_nat_uv(): array;
    abstract public function get_nat_maskarade(): array;
    abstract public function get_nat_11(): array;
    abstract public function set_nat_11(string $ip_local, string $ip_public, string $descr): bool;
    
    abstract public function get_nat_netmap(): array;
    abstract public function set_nat_netmap(string $proto, string $port_public, string $ip_local, string $port_local, string $descr): array;
    abstract public function in_nat_netmap(?string $proto = null, ?string $port_public = null, ?string $ip_local = null, ?string $port_local = null, ?string $descr = null): bool;
    
    
    
    
    
    
    
    
    
}
