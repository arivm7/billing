<?php
/**
 *  Project : my.ri.net.ua
 *  File    : MikrotikDevice1.php
 *  Path    : billing/core/MikrotikDevice1.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:32:46
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of MikrotikDevice1.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace billing\core;

use MikrotikApi\MikroLink;
use config\Mik;
use config\tables\TP;



/**
 * Класс для работы с устройствами Mikrotik
 */
class MikrotikDevice1 extends NetworkDevice {
    
    const CONNECT_TIMEOUT  = 1; // Ожидание ответа при подключении
    const CONNECT_ATTEMPTS = 2; // количество попыток подключения
    const CONNECT_DELAY = 0;    // Задержка после попытки подключения
    const LOG_FILE_NAME = 'mikrolink.log'; // имя log файла
    
    const NAT_UV_POSITION = 0;  // Позиция правил переадресации
    const NAT_11_POSITION = 1;  // 1:1 NAT всегда сразу после них
    
    /**
     * [uptime] => 7w4d3h26m36s
     * [version] => 6.44.1 (stable)
     * [build-time] => Mar/13/2019 08:38:51
     * [factory-software] => 6.28
     * [free-memory] => 6750208
     * [total-memory] => 33554432
     * [cpu] => MIPS 24Kc V7.4
     * [cpu-count] => 1
     * [cpu-frequency] => 650
     * [cpu-load] => 3 %
     * [free-hdd-space] => 7532544
     * [total-hdd-space] => 16777216
     * [write-sect-since-reboot] => 14778
     * [write-sect-total] => 66960
     * [bad-blocks] => 0
     * [architecture-name] => smips
     * [board-name] => hAP lite
     * [platform] => MikroTik
     */
    public array $resource = [];
    
    public string $hostname = '';
    
    public array $gateways = [];
    
    public array $nat_rules = [];
    public array $filter_rules = [];
    public array $ip_services = [];
    
    /**
     * Массив, содержащий массывы адресов по запрошенным адресным листам
     *      [
     *          'ABON' => [...],
     *          'DNS' => [...],
     *      ]
     */
    public array $address_list = [];
    
    public MikroLink $connector;

    
    
    /**
     * Создаёт объект подключения к микротику
     * Для подключения нужно передать или ID ехплощадки или массив параметров техплощадки.
     * @param int|null $tp_id -- ID техплощадки
     * @param array|null $tp -- Массив с параметрами техплощадки
     * @throws \Exception
     * @return false|MikroLink -- Возвращаемій объект.
     */
    public function __construct(?int $tp_id = null, ?array $tp = null, ?bool $ssl = null) {
        parent::__construct($tp_id, $tp);
        
        if (is_null($ssl)) {
            if (!empty($this->TP[TP::F_MIK_PORT_SSL])) { 
                $ssl = true; 
            } else { 
                $ssl = false; 
            }
        }
        
        $connector = self::mik_connector(
                ip:     $this->TP[TP::F_MIK_IP],
                login:  $this->TP[TP::F_MIK_LOGIN],
                pass:   $this->TP[TP::F_MIK_PASSWD],
                port:   ($ssl ? $this->TP[TP::F_MIK_PORT_SSL] : $this->TP[TP::F_MIK_PORT]),
                ssl:    $ssl);
        
        if (!$connector) {
            throw new \Exception('Ошибка подключения к Mikrotik' . ' ['.$tp[TP::F_MIK_IP].']' . CR . '<pre>'. print_r(self::$messages, true) . '</pre>');
        }
        
        $this->connector = $connector;
    }



   public static function mik_connector(string $ip, string $login, string $pass, int $port, bool $ssl): MikroLink|false {
        $connector = new MikroLink(
            timeout:  self::CONNECT_TIMEOUT,    // Ожидание ответа при подключении
            attempts: self::CONNECT_ATTEMPTS,   // количество попыток подключения
            delay:    self::CONNECT_DELAY,      // Задержка после попытки подключения
            logFile:  DIR_LOG . '/' . self::LOG_FILE_NAME,
            printLog: false
        );

        set_error_handler(function($errno, $errstr) {
            throw new \RuntimeException($errstr, $errno);
        });

        try {
            if (!$connector->connect($ip, $login, $pass, $port, $ssl)) {
                throw new \RuntimeException("Connect failed: " . $connector->error_str);
            }
        } catch (\Throwable $e) {
            self::$messages[] = $e->getMessage();
            return false;
        } finally {
            restore_error_handler();
        }

        return $connector;
    }



    /**
     *  /system resource print
     *                  uptime: 3h33m37s
     *                 version: 6.47.1 (stable)
     *              build-time: Jul/08/2020 12:34:22
     *        factory-software: 6.36.1
     *             free-memory: 115.0MiB
     *            total-memory: 256.0MiB
     *                     cpu: MIPS 1004Kc V2.15
     *               cpu-count: 4
     *           cpu-frequency: 880MHz
     *                cpu-load: 26%
     *          free-hdd-space: 3352.0KiB
     *         total-hdd-space: 16.3MiB
     * write-sect-since-reboot: 3860
     *        write-sect-total: 3860
     *              bad-blocks: 0%
     *       architecture-name: mmips
     *              board-name: hEX
     *                platform: MikroTik
     *
     * @param $mik класс, подключенный к микротику
     * @param boolean $disconect_on_end
     * @return array
     */
    private function read_mik_resources(): bool {
        $rez = $this->connector->exec('/system/resource/print');
        if ($rez) {
            $this->resource = $rez[array_key_first($rez)];
            return true;
        } else {
            $this->resource = [];
            self::$messages[] = "Строка ответа пустая";
            return false;
        }
    }

    
    
    private function ensureResource(): void
    {
        if (!$this->resource) { $this->read_mik_resources(); }
    }
    
    
    
    public function get_cpu_count(): int {
        $this->ensureResource();
        return $this->resource['cpu-count'] ?? 0; // cpu-count: 4
    }

    public function get_cpu_frequency(): int {
        return self::parseFrequency($this->resource['cpu-frequency'] ?? 0); // cpu-frequency: 880MHz
    }

    public function get_cpu_load(): int {
        $this->ensureResource();
        return self::parsePercent($this->resource['cpu-load'] ?? 0); // cpu-load: 26%
    }

    public function get_cpu_name(): string {
        $this->ensureResource();
        return $this->resource['cpu'] ?? ''; // cpu: MIPS 1004Kc V2.15
    }

    public function get_factory(): string {
        $this->ensureResource();
        return $this->resource['factory-software'] ?? '';  // factory-software: 6.36.1
    }

    public function get_uptime(): int {
        $this->ensureResource();
        
        $pattern = '/(?:(\d+)w)?(?:(\d+)d)?(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?/';
        
        $uptime = $this->resource['uptime'];  // uptime: 3h33m37s

        if (!preg_match($pattern, $uptime, $m)) {
            return 0;
        }

        $weeks   = (int)($m[1] ?? 0);
        $days    = (int)($m[2] ?? 0);
        $hours   = (int)($m[3] ?? 0);
        $minutes = (int)($m[4] ?? 0);
        $seconds = (int)($m[5] ?? 0);

        return
            $weeks   * 7 * 24 * 3600 +
            $days    * 24 * 3600 +
            $hours   * 3600 +
            $minutes * 60 +
            $seconds;        
    }
    
    public function get_hdd_bad_blocks(): int {
        $this->ensureResource();
        return self::parsePercent($this->resource[Mik::F_RES_BAD_BLOCKS] ?? 0);  // bad-blocks: 0%
    }

    public function get_hdd_free(): int {
        $this->ensureResource();
        return self::parseSize($this->resource[Mik::F_RES_FREE_HDD_SPACE] ?? 0);  // free-hdd-space: 3352.0KiB
    }

    public function get_hdd_total(): int {
        $this->ensureResource();
        return self::parseSize($this->resource[Mik::F_RES_TOTAL_HDD_SPACE] ?? 0);  // total-hdd-space: 16.3MiB
    }
    
    public function get_memory_total(): int {
        $this->ensureResource();
        return self::parseSize($this->resource[Mik::F_RES_TOTAL_MEMORY] ?? 0);  // total-memory: 256.0MiB
    }

    public function get_memory_free(): int {
        $this->ensureResource();
        return self::parseSize($this->resource[Mik::F_RES_FREE_MEMORY] ?? 0);  // free-memory: 115.0MiB
    }



    public function get_state(): array {
        // $this->ensureResource();
        return  [
            Mik::F_RES_UPTIME           => $this->get_uptime(),         // *                  uptime: 3h33m37s
            Mik::F_RES_TOTAL_MEMORY     => $this->get_memory_total(),   // *            total-memory: 256.0MiB
            Mik::F_RES_FREE_MEMORY      => $this->get_memory_free(),    // *             free-memory: 115.0MiB
            Mik::F_RES_CPU_LOAD         => $this->get_cpu_load(),       // *                cpu-load: 26%
            Mik::F_RES_TOTAL_HDD_SPACE  => $this->get_hdd_total(),      // *         total-hdd-space: 16.3MiB
            Mik::F_RES_FREE_HDD_SPACE   => $this->get_hdd_free(),       // *          free-hdd-space: 3352.0KiB
            Mik::F_RES_BAD_BLOCKS       => $this->get_hdd_bad_blocks(), // *              bad-blocks: 0%
        ];
        
    }



    public function get_description(): array {
        $this->ensureResource();
        return [
            Mik::F_RES_PLATFORM          => $this->resource[Mik::F_RES_PLATFORM] ?? '',          // *                platform: MikroTik
            Mik::F_RES_BOARD_NAME        => $this->resource[Mik::F_RES_BOARD_NAME] ?? '',        // *              board-name: hEX
            Mik::F_RES_CPU_NAME          => $this->resource[Mik::F_RES_CPU_NAME] ?? '',          // *                     cpu: MIPS 1004Kc V2.15
            Mik::F_RES_ARCHITECTURE_NAME => $this->resource[Mik::F_RES_ARCHITECTURE_NAME] ?? '', // *       architecture-name: mmips
            Mik::F_RES_VERSION           => $this->resource[Mik::F_RES_VERSION] ?? '',           // *                 version: 6.47.1 (stable)
            Mik::F_RES_FACTORY_SOFTWARE  => $this->get_factory(),                                // *        factory-software: 6.36.1
        ];
    }



    public function get_gateways(): array {
        if (empty($this->gateways)) {
            $this->gateways = $this->connector->exec(
                    '/ip/route/print',
                    [
                        "?dst-address"=>"0.0.0.0/0"
                    ]);
            if (!$this->gateways) {
                self::$messages[] = "Возможно, нет записей в таблице /ip/route, что странно.";
            }
        }
        return $this->gateways;
    }

    
    
    public function get_hostname(): string {
        if (!$this->hostname) { 
            $res = $this->connector->exec('/system/identity/print');
            $this->hostname = $res[array_key_first($res)]['name'] ?? '';
        }
        return $this->hostname;
    }

    
    
    /**
     * Возвращает список указанной таблицы на микротике
     * Возвращает список из адресной таблицы микротика
     * @param string|null $address_list -- строка имена адресного листа
     * @param bool $disconect_on_end -- закрыть подключение к микротику по завершению
     * @return array
     */
    private function get_address_list(string $list): array {
        
        if (empty($list)) {
            self::$messages[] = "get_address_list: Таблица адресов не указана";
            return [];
        }
        if (!array_key_exists($list, $this->address_list)) {            
            try {
                $this->address_list[$list] = 
                    $this->connector->exec(
                        '/ip/firewall/address-list/print',
                        [
                            "?list"=>$list,
                        ]) ?: [];
            } catch (\TypeError $e) {
                if (str_contains($e->getMessage(), 'ord()')) {
                    return [];
                }
                throw $e;
            }
        }
        if (empty($this->address_list[$list])) { self::$messages[] = "Таблица адресов пуста" . " [".$list."]"; }
        
        if (!is_array($this->address_list[$list])) {
            debug(self::$messages, 'self::$errors');
            debug($list, '$list');
            debug($this->address_list[$list], '$this->address_list[$list]', die:0);
//            debug($this->get_list_items(list: $list, id: $this->address_list[$list]), 'get_list_items');
            throw new Exception;
        }
        
        return $this->address_list[$list];
    }
    
    
    
    
    public function get_list_items(string $list, ?string $ip = null, int|bool|null $ena = null, string|null $id = null): array {
        if (empty($list)) {
            self::$messages[] = "get_list_items: Таблица адресов не указана";
            return [];
        }
        $items = [];
        $address_list = $this->get_address_list($list);
        if (is_null($ip) && is_null($ena)) { return $address_list; }
        if (!is_null($ip)) { $ip = trim($ip); }
        if (!is_null($id)) { $id = trim($id); }
        if (!is_null($ena)) { $ena = (bool)$ena; }
        foreach ($address_list as $rec) {
            // .id  list    address   creation-time        dynamic disabled comment
            // *259 TRUSTED 10.1.1.57 nov/19/2022 00:23:41 false   false    IP EXT 509 FRANSUA
            if (!is_null($id)) { if ($rec['.id']     != $id) { continue; } }
            if (!is_null($ip)) { if ($rec['address'] != $ip) { continue; } }
            if (!is_null($ena)) { 
                $rec_ena = !mikBool($rec['disabled']);
                if ($rec_ena != $ena) { continue; } 
            }
            $items[] = $rec;
        }
        return $items;
    }


    public function in_list_item(string $list, string $ip, int|bool $ena): bool {
        if (empty($list)) {
            self::$messages[] = "in_list_item: Таблица адресов не указана";
            return false;
        }
        
        if (empty($ip)) { 
            self::$messages[] = "in_list_item: IP адрес не указан";
            return false; 
        }

        $address_list = $this->get_address_list($list);
        foreach ($address_list as $item) {
            // .id  list    address   creation-time        dynamic disabled comment
            // *259 TRUSTED 10.1.1.57 nov/19/2022 00:23:41 false   false    IP EXT 509 FRANSUA
            $item_ena = ($item['disabled'] == 'false');
            if (($item['address'] == $ip) && ($item_ena == $ena)) {
                return true;
            }
        }
        return false;
    }
    
    
    
    public function set_list_item(string $list, string $ip, int|bool $ena, string $descr): bool {
        
        if (!validate_ip($ip)) {
            self::$messages[] = __('Не верный формат строки IP') . ' ['.$ip.']';
            return false;
        }

        $result = false;

        // /ip firewall address-list set numbers=[find where list="ABON" disabled=no address="1.1.1.1"] disabled=yes
        // :put [/ip firewall address-list print where list=ABON disabled=no address=1.1.1.1]

        /**
         * Получаем запись IP-адреса
         */
        $rez = $this->connector->exec(
                '/ip/firewall/address-list/print', 
                [
                    "?list"=>$list,
                    "?address"=>$ip
                ]
            );

        if(count($rez) === 1) {
            
            /**
             * Есть одна запись. 
             * То устанавливаем её в нужное значение
             */
            $id = $rez[array_key_first($rez)]['.id'];
            $rez = $this->connector->exec('/ip/firewall/address-list/set', 
                    [
                        "numbers"=>$id,
                        "disabled"=>($ena ? Mik::OFF : Mik::ON),
                        "comment"=>($descr ?: $rez[array_key_first($rez)]["comment"])
                    ]
                );
            // debug($rez, '$rez_2', die:0);

        } elseif (count($rez) === 0) {
            
            /**
             * В таблице нет указанного IP-адреса
             * Создаём запись с указанным IP-адресом
             */
            
            self::$messages[] = __('Указанного IP-адреса в таблице нет') . '. ' . __('Создаём новую запись');
            // /ip firewall address-list add list=ABON address=1.1.1.1 comment="11 1 11"
            $rez = $this->connector->exec(
                    '/ip/firewall/address-list/add', 
                    [
                        'list'      => $list,
                        'address'   => $ip,
                        'comment'   => $descr,
                        'disabled'  => ($ena ? Mik::OFF : Mik::ON),
                    ]
                );


            if(is_string($rez)) {
                $id = $rez;
            } else {
                self::$messages[] = __('Не удалось добавить IP [%s] в таблицу [%s]', param: $ip);
                if(is_array($rez)) {
                    self::$messages[] = $rez['!trap'][0]['message'];
                }
                return false;
            }
            
        } else {
            /**
             * Несколько указанных IP адресов. 
             * Это недопустимо.
             */
            self::$messages[] = __('Критическая ошибка: В таблице несколько указанных IP адресов. Должен быть только один.');
            $result = false;
        }

        /**
         * Удаляем лист из кэша
         */
        unset($this->address_list[$list]);
        
        /**
         * Проверка выполнения операции
         */
        $rez = $this->connector->exec(
                '/ip/firewall/address-list/print', 
                [
                    "?.id"=>$id
                ]
            );
       
        return ($rez[array_key_first($rez)]['disabled'] == ($ena ? Mik::OFF : Mik::ON));
    }


    /**
     * Нормализует строку address-list к единому формату.
     * Поддерживает ключи Mik::F_LIST_* и упрощённые алиасы.
     */
    private function normalizeAddressListRow(array $row, ?string $defaultList = null): ?array
    {
        $list = trim((string) ($row[Mik::F_LIST_LIST] ?? $row['list'] ?? $defaultList ?? ''));
        $address = trim((string) ($row[Mik::F_LIST_ADDRESS] ?? $row['address'] ?? ''));
        $comment = trim((string) ($row[Mik::F_LIST_COMMENT] ?? $row['comment'] ?? ''));

        if ($list === '') {
            self::$messages[] = __('Не указано имя address-list');
            return null;
        }

        if (!(validate_ip($address) || is_ip_net($address))) {
            self::$messages[] = __('Не верный формат строки IP') . ' [' . $address . ']';
            return null;
        }

        if (array_key_exists('enabled', $row)) {
            $enabled = (bool) $row['enabled'];
        } elseif (array_key_exists(Mik::F_LIST_DISABLED, $row)) {
            $enabled = !mikBool($row[Mik::F_LIST_DISABLED]);
        } else {
            $enabled = true;
        }

        return [
            'list' => $list,
            'address' => $address,
            'enabled' => $enabled,
            'comment' => $comment,
        ];
    }


    /**
     * Добавляет записи в address-list из массива.
     * Если запись уже существует, она пропускается.
     */
    public function add_address_list_from_array(array $rows, ?string $defaultList = null): bool
    {
        $result = true;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                self::$messages[] = __('Некорректная строка address-list');
                $result = false;
                continue;
            }

            $item = $this->normalizeAddressListRow($row, $defaultList);
            if ($item === null) {
                $result = false;
                continue;
            }


            $exists = $this->get_list_items($item['list'], $item['address']);
            
            if (count($exists) > 1) {
                self::$messages[] = __('В таблице несколько одинаковых IP адресов') . ' [' . $item['list'] . ' :: ' . $item['address'] . ']';
                $result = false;
                continue;
            }

            if (count($exists) === 1) {
                continue;
            }

            $rez = $this->connector->exec(
                '/ip/firewall/address-list/add',
                [
                    'list' => $item['list'],
                    'address' => $item['address'],
                    'comment' => $item['comment'],
                    'disabled' => ($item['enabled'] ? Mik::OFF : Mik::ON),
                ]
            );

            if (!is_string($rez)) {
                self::$messages[] = __('Не удалось добавить IP [%s]', param: $item['address']) . ' ' . __('в таблицу [%s]', param: $item['list']);
                if (is_array($rez) && isset($rez['!trap'][0]['message'])) {
                    self::$messages[] = $rez['!trap'][0]['message'];
                }
                $result = false;
                continue;
            }

            unset($this->address_list[$item['list']]);
        }

        return $result;
    }

    

    /**
     * Обновляет записи address-list из массива.
     * Если записи нет, она будет создана.
     */
    public function set_address_list_from_array(array $rows, ?string $defaultList = null): bool
    {
        $result = true;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                self::$messages[] = __('Некорректная строка address-list');
                $result = false;
                continue;
            }

            $item = $this->normalizeAddressListRow($row, $defaultList);
            if ($item === null) {
                $result = false;
                continue;
            }

            if (!$this->set_list_item($item['list'], $item['address'], $item['enabled'], $item['comment'])) {
                $result = false;
            }
        }

        return $result;
    }

    

    /**
     * Полностью пересоздаёт указанные address-list из массива.
     * Все текущие записи затронутых списков удаляются без поштучной сверки,
     * после чего загружается новый набор записей.
     */
    public function sync_address_list_from_array(array $rows, ?string $defaultList = null): bool
    {
        $result = true;
        $normalizedRows = [];
        $listsToSync = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                self::$messages[] = __('Некорректная строка address-list');
                $result = false;
                continue;
            }

            $item = $this->normalizeAddressListRow($row, $defaultList);
            if ($item === null) {
                $result = false;
                continue;
            }

            $normalizedRows[] = $item;
            $listsToSync[$item['list']] = $item['list'];
        }
//        debug($normalizedRows, '$normalizedRows', die:0);
//        debug($listsToSync, '$listsToSync', die:0);

        foreach ($listsToSync as $list) {
            $existingItems = $this->get_address_list($list);

            foreach ($existingItems as $existingItem) {
                $id = $existingItem[Mik::F_LIST_ID] ?? null;
                if (empty($id)) {
                    self::$messages[] = __('Не удалось определить ID записи address-list') . ' [' . $list . ']';
                    $result = false;
                    continue;
                }

                $rez = $this->connector->exec(
                    '/ip/firewall/address-list/remove',
                    [
                        'numbers' => $id,
                    ]
                );

                if (is_array($rez) && isset($rez['!trap'][0]['message'])) {
                    self::$messages[] = $rez['!trap'][0]['message'];
                    $result = false;
                    continue;
                }

                unset($this->address_list[$list]);
            }
        }

//        debug($normalizedRows, '$normalizedRows', die:0);
        $b = $this->add_address_list_from_array($normalizedRows);
//        debug($b, '$b', die:1);
        if (!$b) {
            $result = false;
        }
//        debug($result, '$result', die:1);

        return $result;
    }

    
    /**
     *  Возвращает массив записей:
     *  [
     *      'public_ip'  => $publicIp,
     *      'private_ip' => $privateIp,
     *      'enabled'    => $enabled,
     *      'comment'    => $d['comment'] ?? '',
     *  ];
     * @return array
     */
    public function get_nat_11(): array
    {
        $rules = $this->getNatRules();

        $dst = [];
        $src = [];

        foreach ($rules as $r) {
            if (($r['chain'] ?? '') === 'dstnat' && ($r['action'] ?? '') === 'dst-nat') {
                $dst[] = $r;
            }

            if (($r['chain'] ?? '') === 'srcnat' && ($r['action'] ?? '') === 'src-nat') {
                $src[] = $r;
            }
        }

        $result = [];

        foreach ($dst as $d) {
            $publicIp  = $d['dst-address'] ?? '';
            $privateIp = $d['to-addresses'] ?? '';

            foreach ($src as $s) {
                if (
                    ($s['src-address'] ?? '') === $privateIp &&
                    ($s['to-addresses'] ?? '') === $publicIp
                ) {
                    $enabled = ($d['disabled'] === 'false') && ($s['disabled'] === 'false');

                    $result[] = [
                        'public_ip'  => $publicIp,
                        'private_ip' => $privateIp,
                        'enabled'    => $enabled,
                        'comment'    => $d['comment'] ?? '',
                    ];

                    break;
                }
            }
        }

        return $result;
    }

    
    
    public function set_nat_11(string $ip_local, string $ip_public, string $descr): bool
    {
        if (!validate_ip($ip_local) || !validate_ip($ip_public)) {
            self::$messages[] = 'Неверный IP';
            return false;
        }

        $rules = $this->getNatRules();

        $dstRuleId = null;
        $srcRuleId = null;

        $placeBefore = $this->getPlaceBeforeByPosition(self::NAT_11_POSITION);

        foreach ($rules as $r) {
            if (
                ($r['chain'] ?? '') === 'dstnat' &&
                ($r['action'] ?? '') === 'dst-nat' &&
                ($r['dst-address'] ?? '') === $ip_public
            ) {
                $dstRuleId = $r['.id'];
            }

            if (
                ($r['chain'] ?? '') === 'srcnat' &&
                ($r['action'] ?? '') === 'src-nat' &&
                ($r['src-address'] ?? '') === $ip_local
            ) {
                $srcRuleId = $r['.id'];
            }
        }

        // --- DST NAT ---
        if ($dstRuleId) {
            $this->connector->exec('/ip/firewall/nat/set', [
                'numbers'      => $dstRuleId,
                'to-addresses' => $ip_local,
                'comment'      => $descr,
            ]);
        } else {
            
            $params = [
                'chain'        => 'dstnat',
                'dst-address'  => $ip_public,
                'action'       => 'dst-nat',
                'to-addresses' => $ip_local,
                'comment'      => $descr,
            ];

            if ($placeBefore) {
                $params['place-before'] = $placeBefore;
            }            
            
            $this->connector->exec('/ip/firewall/nat/add', $params);
        }

        // --- SRC NAT ---
        if ($srcRuleId) {
            $this->connector->exec('/ip/firewall/nat/set', [
                'numbers'      => $srcRuleId,
                'to-addresses' => $ip_public,
                'comment'      => $descr,
            ]);
        } else {
            
            $params = [
                'chain'        => 'srcnat',
                'src-address'  => $ip_local,
                'action'       => 'src-nat',
                'to-addresses' => $ip_public,
                'comment'      => $descr,
            ];

            if ($placeBefore) {
                $params['place-before'] = $placeBefore;
            }            
            
            $this->connector->exec('/ip/firewall/nat/add', $params);
        }

        return true;
    }    
    
    public function get_nat_list(): array {
        return $this->getNatRules();
        
    }

    public function get_nat_maskarade(): array {
        
    }

    
    
    public function get_nat_netmap(): array
    {
        $rules = $this->getNatRules();

        $result = [];

        foreach ($rules as $r) {
            if (
                ($r['chain'] ?? '') === 'dstnat' &&
                ($r['action'] ?? '') === 'netmap'
            ) {
                $result[] = [
                    'proto'        => $r['protocol'] ?? '',
                    'port_public'  => $r['dst-port'] ?? '',
                    'ip_local'     => $r['to-addresses'] ?? '',
                    'port_local'   => $r['to-ports'] ?? '',
                    'enabled'      => !mikBool($r['disabled'] ?? 'true'),
                    'comment'      => $r['comment'] ?? '',
                ];
            }
        }

        return $result;
    }
    
    

    public function set_nat_netmap(
        string $proto,
        string $port_public,
        string $ip_local,
        string $port_local,
        string $descr
    ): array {

        if (!validate_ip($ip_local)) {
            self::$messages[] = 'Неверный IP';
            return [];
        }

        $rules = $this->getNatRules();

        $ruleId = null;

        foreach ($rules as $r) {
            if (
                ($r['chain'] ?? '') === 'dstnat' &&
                ($r['action'] ?? '') === 'netmap' &&
                ($r['protocol'] ?? '') === $proto &&
                ($r['dst-port'] ?? '') === $port_public
            ) {
                $ruleId = $r['.id'];
                break;
            }
        }

        if ($ruleId) {
            $this->connector->exec('/ip/firewall/nat/set', [
                'numbers'      => $ruleId,
                'to-addresses' => $ip_local,
                'to-ports'     => $port_local,
                'comment'      => $descr,
            ]);
        } else {
            $this->connector->exec('/ip/firewall/nat/add', [
                'chain'        => 'dstnat',
                'protocol'     => $proto,
                'dst-port'     => $port_public,
                'action'       => 'netmap',
                'to-addresses' => $ip_local,
                'to-ports'     => $port_local,
                'comment'      => $descr,
            ]);
        }

        return [
            'proto'        => $proto,
            'port_public'  => $port_public,
            'ip_local'     => $ip_local,
            'port_local'   => $port_local,
            'comment'      => $descr,
        ];
    }    



    public function in_nat_netmap(
        ?string $proto = null,
        ?string $port_public = null,
        ?string $ip_local = null,
        ?string $port_local = null,
        ?string $descr = null
    ): bool {

        $list = $this->get_nat_netmap();

        foreach ($list as $r) {

            if ($proto !== null && $r['proto'] !== $proto) { continue; }
            if ($port_public !== null && $r['port_public'] !== $port_public) { continue; }
            if ($ip_local !== null && $r['ip_local'] !== $ip_local) { continue; }
            if ($port_local !== null && $r['port_local'] !== $port_local) { continue; }
            if ($descr !== null && $r['comment'] !== $descr) { continue; }

            return true;
        }

        return false;
    }    
    
    
    
    



    public const NAT_LOCAL_NETMAP_COMMENT       = 'NAT for local netmap';
    public const NAT_LOCAL_NETMAP_CHAIN         = 'srcnat';
    public const NAT_LOCAL_NETMAP_ACTION        = 'src-nat';
    public const NAT_LOCAL_NETMAP_OUT_INTERFACE = 'bridge_LAN';


    protected function build_nat_local_netmap_rule(
        string $src_subnet,
        string $dst_subnet,
        string $to_ip,
        bool $disabled = false
    ): array
    {
        return [
            'chain'         => self::NAT_LOCAL_NETMAP_CHAIN,
            'action'        => self::NAT_LOCAL_NETMAP_ACTION,
            'comment'       => self::NAT_LOCAL_NETMAP_COMMENT,
            'src-address'   => $src_subnet,
            'dst-address'   => $dst_subnet,
            'out-interface' => self::NAT_LOCAL_NETMAP_OUT_INTERFACE,
            'to-addresses'  => $to_ip,
            'disabled'      => $disabled ? Mik::ON : Mik::OFF,
        ];
    }



    public function get_nat_local_netmap(
        string $src_subnet,
        string $dst_subnet,
        string $to_ip
    ): ?array
    {
        $rules = $this->connector->exec('/ip/firewall/nat/print', [
            '?chain'         => self::NAT_LOCAL_NETMAP_CHAIN,
            '?action'        => self::NAT_LOCAL_NETMAP_ACTION,
            '?comment'       => self::NAT_LOCAL_NETMAP_COMMENT,
            '?src-address'   => $src_subnet,
            '?dst-address'   => $dst_subnet,
            '?to-addresses'  => $to_ip,
        ]);

        return $rules[0] ?? null;
    }



    public function in_nat_local_netmap(
        string $src_subnet,
        string $dst_subnet,
        string $to_ip
    ): bool
    {
        return $this->get_nat_local_netmap(
            src_subnet : $src_subnet,
            dst_subnet : $dst_subnet,
            to_ip      : $to_ip,
        ) !== null;
    }



    public function set_nat_local_netmap(
        string $src_subnet,
        string $dst_subnet,
        string $to_ip,
        bool $disabled = false
    ): array
    {
        $rule = $this->get_nat_local_netmap(
            src_subnet : $src_subnet,
            dst_subnet : $dst_subnet,
            to_ip      : $to_ip,
        );

        if ($rule) {

            $this->connector->exec('/ip/firewall/nat/set', [
                '.id'           => $rule['.id'],
                'disabled'      => $disabled ? Mik::ON : Mik::OFF,
                'out-interface' => self::NAT_LOCAL_NETMAP_OUT_INTERFACE,
            ]);

            return $this->get_nat_local_netmap(
                src_subnet : $src_subnet,
                dst_subnet : $dst_subnet,
                to_ip      : $to_ip,
            );
        }

        $add_data = $this->build_nat_local_netmap_rule(
            src_subnet : $src_subnet,
            dst_subnet : $dst_subnet,
            to_ip      : $to_ip,
            disabled   : $disabled,
        );

        $this->connector->exec('/ip/firewall/nat/add', $add_data);

        return $this->get_nat_local_netmap(
            src_subnet : $src_subnet,
            dst_subnet : $dst_subnet,
            to_ip      : $to_ip,
        );
    }



    public function del_nat_local_netmap(
        string $src_subnet,
        string $dst_subnet,
        string $to_ip
    ): bool
    {
        $rule = $this->get_nat_local_netmap(
            src_subnet : $src_subnet,
            dst_subnet : $dst_subnet,
            to_ip      : $to_ip,
        );

        if (!$rule) {
            return false;
        }

        $this->connector->exec('/ip/firewall/nat/remove', 
                [
                    '.id' => $rule['.id'],
                ]);

        return true;
    }
    



    

    public function get_interfaces(): array {
        $rows = $this->connector->exec('/interface/print') ?: [];
        return $rows;
    }



    
    
    
    
    public function get_interface_lists(?bool $dynamic = null): array {
        $rows = $this->connector->exec('/interface/list/print') ?: [];
        $lists = [];
        foreach ($rows as $row) {
            $match =
                (is_null($dynamic) ? true : (mikBool($row['dynamic']) === $dynamic));
            
            if ($match) {
                $lists[] = $row;
            }
        }
        return $lists;
    }

    public function get_interface_list_members(): array {
        $rows = $this->connector->exec('/interface/list/member/print') ?: [];
        return $rows;
    }




























    // [chain] => input
    // [action] => drop
    // [protocol] => tcp
    // [dst-port] => 2000,67,68,123,3784,4784,443,161,8081,2828,1900
    // [invalid] => false
    // [dynamic] => false
    // [comment] => btest (2000), dhcp (67)
    public function get_filer_rules(
            ?string $chain = null,
            ?string $action = null,
            ?string $protocol = null,
            ?string $dstport = null,
            ?bool   $invalid = null,
            ?bool   $dynamic = null,
            ?string $comment = null
    ): array {
        $this->ensureFilterRules();
        if  (!empty($chain) || !empty($action) || !empty($protocol) || !empty($dstport) || !is_null($invalid) || !is_null($dynamic) || !empty($comment))
        {
            $rules = [];
            foreach ($this->filter_rules as $rule) {
                $match =
                    (!empty($chain)    ? $rule['chain']    == $chain    : true) &&
                    (!empty($action)   ? $rule['action']   == $action   : true) &&
                    (!empty($protocol) ? $rule['protocol'] == $protocol : true) &&
                    (!empty($dstport)  ? $rule['dstport']  == $dstport  : true) &&
                    (!empty($invalid)  ? mikBool($rule['invalid']) == $invalid : true) &&
                    (!empty($dynamic)  ? mikBool($rule['dynamic']) == $dynamic : true) &&
                    // compare only the beginning of the comment        
                    (!empty($comment)  ? strpos($rule['comment'], $comment) === 0 : true);                    
                if ($match) {
                    $rules[] = $rule;
                }
            }
            return $rules;
        }
        else {
            return $this->filter_rules;
        }
    }

    
    
    public function get_filer_input(
            ?string $action = null,
            ?string $protocol = null,
            ?string $dstport = null,
            ?string $invalid = null,
            ?string $dynamic = null,
            ?string $comment = null
    ): array 
    {
        // [chain] => input
        // [action] => drop
        // [protocol] => tcp
        // [dst-port] => 2000,67,68,123
        // [invalid] => false
        // [dynamic] => false
        // [comment] => btest (2000), dhcp (67)
        return $this->get_filer_rules(chain: 'input', action: $action, protocol: $protocol, dstport: $dstport, invalid: $invalid, dynamic: $dynamic, comment: $comment);
    }


    
    /**
     * Возвращает .id первого найденного правила
     * @param string $comment
     * @return string|null
     */
    public function get_filter_id(string $comment): string|null {
        $this->ensureFilterRules();
        foreach ($this->filter_rules as $rule) {
            $ruleComment = (string)($rule['comment'] ?? '');
            if (str_starts_with($ruleComment, $comment)) {
                return $rule['.id'];
            }
        }
        return null;
    }
    
    
    
    public function add_filter(
            ?string $chain = null,
            ?string $protocol = null,
            ?string $in_interface_ist = null,
            ?string $dstports = null,
            ?string $invalid = null,
            ?string $dynamic = null,
            ?string $action = null,
            ?string $comment = null): bool
    {
        $this->connector->exec(
                '/ip/firewall/filter/add',
                [
                    'chain'             => $chain, // 'input',
                    'protocol'          => $protocol, // 'tcp',
                    'in-interface-list' => $in_interface_ist, // 'WAN',
                    'dst-port'          => $dstports, // '!' . implode(',', $ports),
                    'action'            => $action, // 'drop',
                    'comment'           => $comment, // 'ABON DROP',
                ]);
        
    }



    public function get_nat_uv(): array {
        
    }



    public function get_ip_services() {
        $this->ensureIpServices();
        return $this->ip_services;
    }



    public function is_bridge(): bool {
        throw new Exception();
    }

    public function is_gateway(string $ip): bool {
        throw new Exception();
    }

    public function is_tp(): bool {
        throw new Exception();
    }




    
    
    public function get_ip_services_ports(): array
    {
        $this->ensureIpServices();
        $ports = [];
        foreach ($this->ip_services as $service) {
            if (!self::is_ip_service_enabled($service)) { 
                continue; 
            }
            if (!empty($service['port'])) { 
                $ports[] = (int)$service['port']; 
            }
        }
        $ports = array_values(array_unique($ports));
        sort($ports);
        return $ports;
    }


    public function get_ip_services_allowed_networks(): array
    {
        $this->ensureIpServices();
        $nets = [];
        foreach ($this->ip_services as $service) {

            if (!self::is_ip_service_enabled($service)) {
                continue;
            }

            $addr = trim($service['address'] ?? '');

            // пусто = открыт всем
            if ($addr === '') {
                return ['0.0.0.0/0'];
            }

            foreach (explode(',', $addr) as $net) {
                $net = trim($net);
                if ($net !== '') {
                    $nets[] = $net;
                }
            }
        }

        $nets = array_values(array_unique($nets));
        sort($nets);

        return $nets;
    }


    private static function is_ip_service_enabled(array $service): bool
    {
        // [dynamic] => false
        if ($service['disabled'] == Mik::ON) {
            return false;
        }
        
        // [invalid] => false
        if ($service['invalid'] == Mik::ON) {
            return false;
        }
        
        // динамическое текущее подключение
        // [disabled] => false
        if (($service['dynamic'] == Mik::ON) && !empty($service['connection'])) { 
            return false;
        }
        return true;
    }


    
    
    
    
    public function get_services(): array
    {
        $services = $this->connector->exec('/ip/service/print') ?: [];
        
        $rez = array_values(array_filter(
            $services,
            static fn(array $service): bool =>
                !mikBool($service['dynamic'] ?? false)
        ));
        
//        debug($rez, '$rez', die: 1);
        return $rez;
    }        
        
        

    
    
    
    
    
    public function get_certificates(): array {
        return $this->connector->exec('/certificate/print') ?: [];
    }

    public function get_certificate(string $name): array {
        return $certs = $this->connector->exec('/certificate/print', ['?name' => $name]) ?: [];
    }

    public function del_certificate(string $id): bool {
        
        $result = $this->connector->exec('/certificate/remove', ['.id' => $id]);
        
//        debug($result, 'del_certificate: $result');
        
        if (isset($result['!trap'])) {
            self::$messages[] = $result['!trap']['message'];
            return false;
        }
        
        if (isset($result['!fatal'])) {
            self::$messages[] = $result['!fatal']['message'];
            return false;
        }

        // if ($result === []) { 
        //     return true; 
        // }
        return true;

    }

    public function add_certificate(
            string $name,
            int    $key_size = 2048,
            string $key_usage = 'tls-server',
            string $trusted = 'yes',
            int    $days_valid = 1825,
            string $country = 'UA',
            string $state = 'UA',
            string $locality = 'Kiev',
            string $organization = 'RI-Network',
            string $unit = 'Tech'): false|string
    {
        $result = $this->connector->exec('/certificate/add', [
                'name' => $name,
                'common-name' => $name,
                'key-size' => $key_size,
                'key-usage' => $key_usage,
                'trusted' => $trusted,
                'days-valid' => $days_valid,
                'country' => $country,
                'state' => $state,
                'locality' => $locality,
                'organization' => $organization,
                'unit' => $unit,
            ]);

//        debug($result, 'add_certificate: $result');
        
        if (isset($result['!trap'])) {
            self::$messages[] = $result['!trap']['message'];
            return false;
        }
        
        if (isset($result['!fatal'])) {
            self::$messages[] = $result['!fatal']['message'];
            return false;
        }

        return $result; // .id

    }

    function certificate_sign(string $name): bool {
        try {
            $result = $this->connector->exec('/certificate/sign', ['number' => $name]);
        } catch (\Throwable $e) {
            self::$messages[] = $e->getMessage();
            return false;
        }

        if (is_array($result) && isset($result['!trap'])) {
            self::$messages[] = $result['!trap']['message'];
            return false;
        }

        if (is_array($result) && isset($result['!fatal'])) {
            self::$messages[] = $result['!fatal']['message'];
            return false;
        }

        return true;
    }
    

    public static function is_certificate_signed(array $cert): bool
    {
        return
            !empty($cert['serial-number'])
            && !empty($cert['fingerprint'])
            && !empty($cert['invalid-before'])
            && !empty($cert['invalid-after']);
    }    
    
    
    
    
    
    


    public function validate_filter_dns(): bool {
        throw new Exception();
    }

    public function validate_filter_flood(): bool {
        throw new Exception();
    }

    public function validate_filter_forward(): bool {
        throw new Exception();
    }

    public function validate_filter_hackers(): bool {
        throw new Exception();
    }

    public function validate_filter_input(): bool {
        throw new Exception();
    }

    public function validate_filter_output(): bool {
        throw new Exception();
    }
    
    

    private static function parseSize(string $value): int
    {
        if (!preg_match('/([\d.]+)\s*(B|KiB|MiB|GiB)/i', $value, $m)) {
            return (int)$value;
        }

        $number = (float)$m[1];
        $unit   = strtoupper($m[2]);

        return match ($unit) {
            'B'   => (int)$number,
            'KIB' => (int)($number * 1024),
            'MIB' => (int)($number * 1024 ** 2),
            'GIB' => (int)($number * 1024 ** 3),
            default => (int)$number,
        };
    }    
    

    
    private static function parsePercent(string $value): int
    {
        return (int)str_replace('%', '', trim($value));
    }


    
    private static function parseFrequency(string $value): int
    {
        if (!preg_match('/(\d+)\s*MHz/i', $value, $m)) {
            return (int)$value;
        }

        return (int)$m[1];
    }
    
    
    
    private function getNatRules(): array {
        $this->ensureNatRules();
        return $this->nat_rules;
    }    
    
    
    private function ensureIpServices(): bool
    {
        if (empty($this->ip_services)) { 
            $ipServices = $this->connector->exec('/ip/service/print');

            if (!$ipServices) {
                self::$messages[] = 'SERVICES пусты или не получены';
                $this->ip_services = [];
                return false;
            }
            $this->ip_services = $ipServices;
        }
        return true;
    }    


    
    private function ensureNatRules(): bool
    {
        if (empty($this->nat_rules)) { 
            $rules = $this->connector->exec('/ip/firewall/nat/print');

            if (!$rules) {
                self::$messages[] = 'NAT rules пусты или не получены';
                $this->nat_rules = [];
                return false;
            }
            $this->nat_rules = $rules;
        }
        return true;
    }    


    
    private function ensureFilterRules(): bool
    {
        if (empty($this->filter_rules)) { 
            $rules = $this->connector->exec('/ip/firewall/filter/print');

            if (!$rules) {
                self::$messages[] = 'Filter rules пусты или не получены';
                return false;
            }
            
            $this->filter_rules = $rules;
        }
        return true;
    }    
    
    
    
    private function getPlaceBeforeByPosition(int $position): ?string
    {
        $rules = $this->getNatRules();

        if (empty($rules)) {
            return null;
        }

        return $rules[$position]['.id'] ?? null;
    }    
    
    
    
}