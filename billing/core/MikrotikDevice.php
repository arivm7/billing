<?php
/**
 *  Project : my.ri.net.ua
 *  File    : MikrotikDevice.php
 *  Path    : billing/core/MikrotikDevice.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Jun 2026 21:56:27
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of MikrotikDevice.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace billing\core;

use MikrotikApi\MikroLink;
use config\Mik;
use config\tables\TP;
use RouterOS\Client;
use RouterOS\Query;



/**
 * Класс для работы с устройствами Mikrotik
 */
class MikrotikDevice extends NetworkDevice {
    
    const DEBUG_TIMERS = 1;
    
    const CONNECT_TIMEOUT  = 1; // Ожидание ответа при подключении
    const CONNECT_ATTEMPTS = 1; // количество попыток подключения
    const CONNECT_DELAY = 0;    // Задержка после попытки подключения
    const SOCKET_TIMEOUT = 3;
    
//    const NAT_UV_POSITION = 0;  // Позиция правил переадресации
//    const NAT_11_POSITION = 1;  // 1:1 NAT всегда сразу после них
    
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
    private ?array $resource = null;
    
    /**
     * /system/identity
     * @var string
     */
    private string $hostname = '';
    
    /**
     * /ip/route/print'
     * ->where('dst-address', '0.0.0.0/0');
     * @var array
     */
    private ?array $gateways = null;
    
    private ?array $nat_rules = null;

    private ?array $filter_rules = null;
    
    private ?array $ip_services = null;
    
    /**
     * .id  list    address   creation-time        dynamic disabled comment
     * *259 TRUSTED 10.1.1.57 nov/19/2022 00:23:41 false   false    IP EXT 509 FRANSUA  
     */
    private ?array $address_list = null;
    
    private ?array $arp = null;


    
    public Client $client;


    
    /**
     * Возвращает массив сообщений 
     * и очищает его.
     * @return type
     */
    public static function get_messages() {
        $messages = self::$messages;
        self::$messages = [];
        return $messages;
    }
    
    
    
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
        
        $client = self::mik_connector(
                ip:     $this->TP[TP::F_MIK_IP],
                login:  $this->TP[TP::F_MIK_LOGIN],
                pass:   $this->TP[TP::F_MIK_PASSWD],
                port:   ($ssl ? $this->TP[TP::F_MIK_PORT_SSL] : $this->TP[TP::F_MIK_PORT]),
                ssl:    $ssl);
        
        if (!$client) {
            throw new \Exception('Ошибка подключения к Mikrotik' . ' ['.($tp[TP::F_MIK_IP] ?? '').']' . CR . '<pre>'. print_r(self::$messages, true) . '</pre>');
        }
        
        $this->client = $client;
    }



    public static function mik_connector(string $ip, string $login, string $pass, int $port, bool $ssl): Client|false {

        set_error_handler(function($errno, $errstr) {
            throw new \RuntimeException($errstr, $errno);
        });

        /**
         * List of available configuration parameters
         * 
         * Parameter    	Type 	Default 	Description
         * host         	string          	(required) Address of Mikrotik RouterOS
         * user         	string          	(required) Username
         * pass         	string          	(required) Password
         * port         	int             	RouterOS API port number for access (if not set use 8728 or 8729 if SSL enabled)
         * ssl 	bool     	false 	Enabl           ssl support (if port is not set this parameter must change default port to ssl port)
         * ssl_options   	array 	details 	See https://www.php.net/manual/en/context.ssl.php
         * legacy       	bool 	false        	Устарело, будет удалено из версии 1.5.0: Поддержка устаревшей схемы авторизации (true - до 6.43, false - после 6.43)
         * timeout      	int 	10      	Max timeout for connecting to RouterOS (in seconds)
         * socket_timeout 	int 	30      	Максимальное время ожидания чтения из RouterOS (в секундах)
         * socket_blocking 	bool 	true    	Set blocking mode on a socket stream
         * socket_options 	array 	details 	See https://www.php.net/manual/en/context.socket.php
         * attempts     	int 	10      	Count of attempts to establish TCP session
         * delay        	int 	1       	Delay between attempts in seconds
         * ssh_port     	int 	22      	Number of SSH port for exporting configuration
         * ssh_timeout  	int 	30      	Max timeout from router via SSH (in seconds)
         * ssh_private_key 	string 	~/.ssh/id_rsa 	Full path to required private key
         */
        
        try {
            return new Client([
                'host' => $ip,
                'user' => $login,
                'pass' => $pass,
                'port' => $port,
                'ssl'  => $ssl,
                'timeout' => self::CONNECT_TIMEOUT,         // Максимальное время ожидания подключения к RouterOS (в секундах)
                'attempts' => self::CONNECT_ATTEMPTS,       // Количество попыток установить TCP-сессию
                'delay' => self::CONNECT_DELAY,             // Задержка между попытками в секундах
                'socket_timeout' => self::SOCKET_TIMEOUT,   // Максимальное время ожидания чтения из RouterOS (в секундах)

            ]);
            
        } catch (\Throwable $e) {
            self::$messages[] = __('Error connecting to device | Ошибка подключения к устройству | Помилка підключення до пристрою') . ': ' . ($ssl ? 'ssl':'plain') .'://' . $ip . ':' . $port . ' @' . $login ;
            self::$messages[] = $e->getMessage();
            return false;
        } finally {
            restore_error_handler();
        }
    }
    
    
    
    private function routerRead(string $command, array $where = []): array
    {
        $query = new Query($command);

        foreach ($where as $key => $value) {
            if ($value === null) {
                continue;
            }

            $key = (string) $key;
            if (str_starts_with($key, '?')) {
                $key = substr($key, 1);
            }

            $query->where($key, (string) $value);
        }

        $response = $this->client
            ->query($query)
            ->read();

        return is_array($response) ? $response : [];
    }



    private function routerWrite(string $command, array $params = []): array
    {
        $query = new Query($command);

        foreach ($params as $key => $value) {
            if ($value === null) {
                continue;
            }

            $key = (string) $key;
            if ($key === '.id') {
                $key = 'numbers';
            }

            $query->equal($key, (string) $value);
        }

        $response = $this->client
            ->query($query)
            ->read();

        return is_array($response) ? $response : [];
    }



    private function routerWriteHasError(array $response): bool
    {
        return isset($response['after']['message']) || isset($response['!trap']) || isset($response['!fatal']);
    }



    private function routerWriteErrorMessage(array $response): string
    {
        if (isset($response['after']['message'])) {
            return (string) $response['after']['message'];
        }

        if (isset($response['!trap']['message'])) {
            return (string) $response['!trap']['message'];
        }

        if (isset($response['!fatal']['message'])) {
            return (string) $response['!fatal']['message'];
        }

        return print_r($response, true);
    }



    private function routerWriteOk(string $command, array $params = []): bool
    {
        try {
            $response = $this->routerWrite($command, $params);

            if ($this->routerWriteHasError($response)) {
                self::$messages[] = $command . ': ' . $this->routerWriteErrorMessage($response);
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            self::$messages[] = $command . ': ' . $e->getMessage();
            return false;
        }
    }



    private function routerWriteReturnId(string $command, array $params = []): string|false
    {
        try {
            $response = $this->routerWrite($command, $params);

            if ($this->routerWriteHasError($response)) {
                self::$messages[] = $command . ': ' . $this->routerWriteErrorMessage($response);
                return false;
            }

            $id = $response['after']['ret'] ?? null;
            if (!is_string($id) || trim($id) === '') {
                throw new \UnexpectedValueException(
                    $command . ': RouterOS returned success without record id. Response: '
                    . print_r($response, true)
                );
            }

            return trim($id);

        } catch (\UnexpectedValueException $e) {
            throw $e;

        } catch (\Throwable $e) {
            self::$messages[] = $command . ': ' . $e->getMessage();
            return false;
        }
    }



    const F_PARSE_SUCCESS  = 'success';
    const F_PARSE_ID       = 'id';
    const F_PARSE_MESSAGE  = 'message';
    const F_PARSE_CATEGORY = 'category';
    
    /**
     * Разбирает ответ микротика при операциях изменяющих состояние и возвращает массив ответов
     * success определяется фактом отсутствия ошибки (message), а category — уточняет её класс.
     * @param array $response
     * @return array
     */
    private static function parse_response(array $response): array
    {
        $after = $response['after'] ?? [];

        $message = $after['message'] ?? null;
        $category = $after['category'] ?? null;
        $id = $after['ret'] ?? null;

        return [
            self::F_PARSE_SUCCESS  => $message === null,
            self::F_PARSE_ID       => $id,
            self::F_PARSE_MESSAGE  => $message,
            self::F_PARSE_CATEGORY => $category,
        ];
    }    
    
    
    
    
    

    public function get_address_list_abon(?string $ip = null, int|bool|null $ena = null /*, ?int $abon_id = null*/ ): array {
        // !!! abon_id пока не задействован
        return $this->get_address_list_items(list: self::LIST_ABON, ip: $ip, ena: $ena);
    }
    
   
    public function in_address_list_abon(?string $ip, int|bool|null $ena = null /*, ?int $abon_id = null*/ ): bool {
        // !!! abon_id пока не задействован
        return $this->in_address_list_item(list: self::LIST_ABON, ip: $ip, ena: $ena);
    }
        
    
    
    public function get_list_dns(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_address_list_items(list: self::LIST_DNS, ip: $ip, ena: $ena);
    }
    
//    public function set_list_dns(string $ip, int|bool $ena, string $descr): bool {
//        return $this->set_address_list_item(self::LIST_DNS, $ip, $ena, $descr);
//    }
    
    public function in_list_dns(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_address_list_item(list: self::LIST_DNS, ip: $ip, ena: $ena);
    }



    public function get_list_hackers(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_address_list_items(list: self::LIST_HACKERS, ip: $ip, ena: $ena);
    }

//    public function set_list_hackers(string $ip, int|bool $ena, string $descr): bool {
//        return $this->set_address_list_item(self::LIST_HACKERS, $ip, $ena, $descr);
//    }

    public function in_list_hackers(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_address_list_item(list: self::LIST_HACKERS, ip: $ip, ena: $ena);
    }



    public function get_list_flood(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_address_list_items(list: self::LIST_FLOOD, ip: $ip, ena: $ena);
    }

//    public function set_list_flood(string $ip, int|bool $ena, string $descr): bool {
//        return $this->set_address_list_item(self::LIST_FLOOD, $ip, $ena, $descr);
//    }

    public function in_list_flood(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_address_list_item(list: self::LIST_FLOOD, ip: $ip, ena: $ena);
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
    private function ensureResource(): void
    {
        if (!$this->resource) { 

            $response = $this->client
                ->query(new Query('/system/resource/print'))
                ->read();

            $this->resource = $response[0];

        }
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



    public function get_gateways(): array
    {
        if (empty($this->gateways)) {
            try {
                
                $query = (new Query('/ip/route/print'))
                        ->where('dst-address', '0.0.0.0/0');
                
                $this->gateways = $this->client
                        ->query($query)
                        ->read();
                
                if (empty($this->gateways)) {
                    self::$messages[] = __('There are no default routes in the /ip/route table | В таблице /ip/route отсутствуют маршруты по умолчанию | У таблиці /ip/route відсутні маршрути за замовчуванням');
                }
            } catch (\Throwable $e) {
                $this->gateways = [];
                self::$messages[] = __('Error getting default routes | Ошибка получения маршрутов по умолчанию | Помилка отримання маршрутів за замовчуванням');
                self::$messages[] = $e->getMessage();
            }
        }
        return $this->gateways;
    }    

    
    
    public function get_hostname(): string {
        if (empty($this->hostname)) {
            try {
                $query = new Query('/system/identity/print');
                $response = $this->client->query($query)->read();
                if (empty($response)) {
                    self::$messages[] = __('RouterOS returned empty response identity | RouterOS вернул пустой ответ identity | RouterOS повернув порожню відповідь identity');
                    $this->hostname = '';
                } else {
                    $this->hostname = $response[0]['name'] ?? '';
                }
            } catch (\Throwable $e) {
                self::$messages[] = __('Hostname request error | Ошибка запроса имени хоста | Помилка запиту імені хоста');
                self::$messages[] = $e->getMessage();
                $this->hostname = '';
            }
        }
        return $this->hostname;
    }

    
    
    private function unset_address_list(string $list): void {
        $list = trim($list);
        if ($list === '') {
            throw new \Exception('unset_address_list['.$list.']: ' . __('Address list name not specified | Не указано имя адресного списка | Не вказано ім\'я адресного списку'));
        }
        if ($this->address_list !== null) {
            unset($this->address_list[$list]);
        }
    }
    
    
    
    private function ensureAddressList(string $list): void
    {
        $list = trim($list);
        if ($list === '') {
            throw new \Exception('ensureAddressList['.$list.']: ' . __('Address list name not specified | Не указано имя адресного списка | Не вказано ім\'я адресного списку'));
        }
            
        if (($this->address_list[$list] ?? null) !== null) { 
            return; 
        }

        try {

//            $t = microtime(true);
            $query = (new Query('/ip/firewall/address-list/print'))
                ->where(Mik::F_LIST_LIST, $list);

            $rows = $this->client
                ->query($query)
                ->read();

            if (!is_array($rows)) {
                throw new \RuntimeException('ensureAddressList['.$list.']: ' . __('Invalid RouterOS response | Неверный ответ RouterOS | Недійсна відповідь RouterOS'));
            }
            
            $normalized = [];
            foreach ($rows as $row) {
                $item = self::normalizeAddressListRow($row);
                if ($item !== null) {
                    $normalized[] = $item;
                }
            }
            $this->address_list[$list] = $normalized;            
            
//            MsgQueue::msg(MsgType::INFO, 'TIMER: in_list_abon: ' . round(microtime(true) - $t, 3) . ' sec');

        } catch (\Throwable $e) {
            $this->unset_address_list($list);
            throw new \RuntimeException('ensureAddressList['.$list.']: ' . $e->getMessage(), 0, $e);
        }
    }    
    
    
    
    
    /**
     * Приводит сырую строку записи адрес-листа к единому внутреннему формату.
     *
     * Принимает строки как из MikroTik API (поля .id, list, address, disabled),
     * так и из БД проекта (поля id, list, address, enabled) — формат
     * определяется по наличию {@see Mik::F_LIST_ENABLED} либо {@see Mik::F_LIST_DISABLED}.
     *
     * Валидация:
     * - имя списка (`list`) обязательно, иначе запись отбрасывается
     * - адрес должен быть валидным IP ({@see validate_ip()}) либо подсетью ({@see is_ip_net()})
     *
     * При несоответствии причина записывается в {@see self::$messages}, метод возвращает null.
     *
     * @param array $row Сырая запись с ключами по константам {@see Mik}
     *
     * @return array{
     *     id: string|null,
     *     list: string,
     *     address: string,
     *     dynamic: mixed,
     *     enabled: bool,
     *     comment: string
     * }|null Нормализованная запись либо null при невалидных данных
     */
    public static function normalizeAddressListRow(array $row): ?array
    {
        $list = trim((string) ($row[Mik::F_LIST_LIST] ?? ''));
        $address = trim((string) ($row[Mik::F_LIST_ADDRESS] ?? ''));
        $comment = trim((string) ($row[Mik::F_LIST_COMMENT] ?? ''));

        if ($list === '') {
            self::$messages[] = 'normalizeAddressListRow: ' . __('Address list name not specified | Не указано имя адрес-листа | Не вказано ім\'я адрес-листа');
            return null;
        }

        if (!(validate_ip($address) || is_ip_net($address))) {
            self::$messages[] = 'normalizeAddressListRow: ' . __('Invalid IP string format | Не верный формат строки IP | Неправильний формат рядка IP') . ' [' . $address . ']';
            return null;
        }

        if (array_key_exists(Mik::F_LIST_ENABLED, $row)) {
            $enabled = (bool) $row[Mik::F_LIST_ENABLED];
        } elseif (array_key_exists(Mik::F_LIST_DISABLED, $row)) {
            $enabled = !mikBool($row[Mik::F_LIST_DISABLED]);
        } else {
            $enabled = true;
        }
        
        $dynamic = mikBool($row[Mik::F_LIST_DYNAMIC] ?? null);


        return [
            Mik::F_LIST_ID      => $row[Mik::F_LIST_ID] ?? null,
            Mik::F_LIST_LIST    => $list,
            Mik::F_LIST_ADDRESS => $address,
            Mik::F_LIST_DYNAMIC => $dynamic,
            Mik::F_LIST_ENABLED => $enabled,
            Mik::F_LIST_COMMENT => $comment,
        ];
    }
    
    
    

    
    /**
     * Возвращает статистику по адресным листам
     * 
     * @return array
     */
    public function get_address_lists_stat(): array
    {
        
        try {

            $query = new Query('/ip/firewall/address-list/print');

            $address_list_full = $this->client
                ->query($query)
                ->read();

            if (!is_array($address_list_full)) {
                throw new \RuntimeException('get_address_lists_stat: ' . __('Invalid RouterOS response | Неверный ответ RouterOS | Недійсна відповідь RouterOS'));
            }

        } catch (\Throwable $e) {
            throw new \RuntimeException('get_address_lists_stat: ' . $e->getMessage(), 0, $e);
        }

        $this->address_list = [];
        $stats = [];

        foreach ($address_list_full as $row) {

            $list = $row[Mik::F_LIST_LIST] ?? null;

            if ($list === null || $list === '') {
                continue;
            }
            
            /**
             * Добавление записи в кэш соответствубщего адлесного листа
             */
            
            if (!isset($this->address_list[$list])) {
                $this->address_list[$list] = [];
            }

            $this->address_list[$list][] = $row;
            
            /**
             * Сбор статистики по адресным листам
             */
            
            if (!isset($stats[$list])) {
                $stats[$list] = [
                    Mik::F_STAT_TOTAL    => 0,
                    Mik::F_STAT_ENABLED  => 0,
                    Mik::F_STAT_DISABLED => 0,
                    Mik::F_STAT_DYNAMIC  => 0,
                    Mik::F_STAT_STATIC   => 0,
                ];
            }

            $enabled = !mikBool($row[Mik::F_LIST_DISABLED] ?? false);
            $dynamic = mikBool($row[Mik::F_LIST_DYNAMIC] ?? false);

            $stats[$list][Mik::F_STAT_TOTAL]++;

            if ($enabled) {
                $stats[$list][Mik::F_STAT_ENABLED]++;
            } else {
                $stats[$list][Mik::F_STAT_DISABLED]++;
            }

            if ($dynamic) {
                $stats[$list][Mik::F_STAT_DYNAMIC]++;
            } else {
                $stats[$list][Mik::F_STAT_STATIC]++;
            }
        }

        ksort($stats, SORT_NATURAL);

        return $stats;
    }    
    
    
    
    /**
     * Возвращает полный массив адресных листов
     * @return array
     */
    public function get_address_list_cache(): array 
    {
        return $this->address_list ?? []; 
    }
    
    
    
    /**
     * Возвращает список указанной таблицы на микротике
     * Возвращает список из адресной таблицы микротика
     * @param string|null $address_list -- строка имена адресного листа
     * @param bool $disconect_on_end -- закрыть подключение к микротику по завершению
     * @return array
     */
    private function get_address_list_table(string $list): array 
    {
        $list = trim($list);
        if ($list === '') {
            self::$messages[] = 'get_address_list_table: ' . __('address list not specified | список адресов не указан | список адрес не вказано');
            return [];
        }

        $this->ensureAddressList($list);

        return $this->address_list[$list] ?? [];
    }
    
    
    
    /**
     * Возвращает сырые данные из ROS
     * @param string|null $id
     * @param string|null $list
     * @param string|null $ip
     * @param int|bool|null $ena
     * @param string|null $descr
     * @return array
     * @throws \Exception
     */
    public function get_address_list_items(
        string $list,
        ?string $id = null,
        ?string $ip = null,
        int|bool|null $ena = null,
        ?string $descr = null): array
    {
        $list = trim($list);
        if ($list === '') {
            throw new \Exception('get_address_list_items: ' . __('Address table not specified | Таблица адресов не указана | Таблиця адрес не вказана'));
        }

        $this->ensureAddressList($list);

        if (($this->address_list[$list] ?? null) === null) {
            return [];
        }

        $address_list = $this->get_address_list_table($list);

        if  (   $ip === null
                && $ena === null
                && $id === null
                && $descr === null ) 
        {
            return $address_list;
        }

        if ($ip    !== null) { $ip    = trim($ip); }
        if ($id    !== null) { $id    = trim($id); }
        if ($ena   !== null) { $ena   = (bool)$ena; }
        if ($descr !== null) { $descr = mb_ltrim($descr); }

        $items = [];

        foreach ($address_list as $rec) {

            if ( $id  !== null && $rec[Mik::F_LIST_ID]      !== $id  ) { continue; }
            if ( $ip  !== null && $rec[Mik::F_LIST_ADDRESS] !== $ip  ) { continue; }
            if ( $ena !== null && $rec[Mik::F_LIST_ENABLED] !== $ena ) { continue; }

//            if ($ena !== null) {
//                $rec_ena = !mikBool($rec[Mik::F_LIST_DISABLED] ?? null);
//                if ($rec_ena !== $ena) { continue; }
//            }

            if ($descr !== null) {
                $comment = $rec[Mik::F_LIST_COMMENT] ?? '';
                if ( !str_starts_with($comment, $descr)) { continue; }
            }

            $items[] = $rec;
        }

        return $items;
    }

    
    
    public function in_address_list_item(string $list, string $ip, int|bool|null $ena = null, ?string $descr = null): bool 
    {
        $list = trim($list);
        if ($list === '') {
            self::$messages[] = "in_address_list_item: " . __('Address table not specified | Таблица адресов не указана | Таблиця адрес не вказана');
            return false;
        }
        
        $ip = trim($ip);
        if ($ip === '') { 
            self::$messages[] = "in_address_list_item: " . __('IP address not specified | IP адрес не указан | IP адреса не вказана');
            return false; 
        }
        
        return !empty(
                $this->get_address_list_items(
                    list: $list,
                    ip: $ip,
                    ena: $ena,
                    descr: $descr
                )
            );        
    }
    


    /**
     * Валидация полей поиска Address-List.
     *
     * 1. Проверяет допустимые поля
     * 2. Проверяет RouterOS-инвариант:
     *    обязательно должно быть указано:
     *      - list
     *      И
     *      - id или ip
     *
     * @throws \InvalidArgumentException
     */
    private static function validate_address_list_search_fields(array $search): void
    {
        $allowed = [
            Mik::F_SEARCH_ID,
            Mik::F_SEARCH_LIST,
            Mik::F_SEARCH_IP,
            Mik::F_SEARCH_ENA,
            Mik::F_SEARCH_DESCR,
        ];

        /**
         * 1. Проверка неизвестных полей
         */
        foreach (array_keys($search) as $field) {
            if (!in_array($field, $allowed, true)) {
                throw new \InvalidArgumentException(__('Unsupported search field | Неподдерживаемое поле поиска | Непідтримуване поле пошуку') . " [$field]");
            }
        }

        /**
         * 2. RouterOS invariant:
         * [list] AND ([id] OR [ip])
         */
        
        $id   = !is_empty($search[Mik::F_SEARCH_ID]   ?? null);
        $list = !is_empty($search[Mik::F_SEARCH_LIST] ?? null);
        $ip   = !is_empty($search[Mik::F_SEARCH_IP]   ?? null);

        if (!$list || (!$id && !$ip)) {
            throw new \InvalidArgumentException('VALIDATE SEARCH: ' . __('required | необходимы | потрібні') . ' [list] AND ([id] OR [ip])');
        }        
        
        if  (
                array_key_exists(Mik::F_SEARCH_IP, $search)
                && !validate_ip($search[Mik::F_SEARCH_IP])
                && !is_ip_net($search[Mik::F_SEARCH_IP])
            ) 
        {
            throw new \InvalidArgumentException('VALIDATE SEARCH: ' . __('Invalid IP format | Неверный формат IP | Недійсний формат IP') . ': ' . $search[Mik::F_SEARCH_IP]);
        }
        
    }



    /**
     * Проверка полей обновления
     * @throws \InvalidArgumentException
     */
    private static function validate_address_list_update_fields(array $update): void
    {
        $allowed = [
            Mik::F_UPDATE_LIST,
            Mik::F_UPDATE_IP,
            Mik::F_UPDATE_ENA,
            Mik::F_UPDATE_DESCR,
        ];

        foreach (array_keys($update) as $field) {
            if (!in_array($field, $allowed, true)) {
                throw new \InvalidArgumentException(__('Unsupported update field | Неподдерживаемое поле обновления | Непідтримуване поле оновлення') . " [$field]");
            }
        }
        
        if  (
                isset($update[Mik::F_UPDATE_IP]) && 
                !validate_ip($update[Mik::F_UPDATE_IP]) && 
                !is_ip_net($update[Mik::F_UPDATE_IP])
            ) 
        {
            throw new \InvalidArgumentException(__('Invalid IP format | Неверный формат IP | Недійсний формат IP') . ': ' . $update[Mik::F_UPDATE_IP]);
        }
        
    }    
    
    

    
    /**
     * Возвращает нормализованные записи address-list,
     * найденные по фильтру поиска.
     *
     * Поддерживаемые параметры поиска:
     * - Mik::F_SEARCH_ID
     * - Mik::F_SEARCH_LIST
     * - Mik::F_SEARCH_IP
     * - Mik::F_SEARCH_ENA
     * - Mik::F_SEARCH_DESCR
     *
     * Если совпадений нет — возвращает пустой массив.
     *
     * При ошибке входных данных возвращает false
     * и пишет сообщение в self::$messages.
     *
     * @param array $search
     *
     * @return array|false
     */
    public function find_address_list_items(array $search): array|false
    {
        $search = remove_null_fields($search);
        
        try {
            self::validate_address_list_search_fields($search);
        } catch (\Throwable $exc) {
            self::$messages[] = 'find_address_list_items: ' . __('Search query validation error | Ошибка валидации поискового запроса | Помилка валідації пошукового запиту');
            self::$messages[] = $exc->getMessage();
            return false;
        }
        
        try {

            $items = $this->get_address_list_items(
                list:  $search[Mik::F_SEARCH_LIST]  ?? null,
                ip:    $search[Mik::F_SEARCH_IP]    ?? null,
                ena:   $search[Mik::F_SEARCH_ENA]   ?? null,
                id:    $search[Mik::F_SEARCH_ID]    ?? null,
                descr: $search[Mik::F_SEARCH_DESCR] ?? null
            );

            $result = [];

            foreach ($items as $item) {
                $norm = self::normalizeAddressListRow($item);
                if ($norm === null) {
                    self::$messages[] = 'find_address_list_items: ' . __('Record normalization error | Ошибка нормализации записи | Помилка нормалізації запису');
                    self::$messages[] = print_r($item, true);
                    continue;
                }
                $result[] = $norm;
            }
            return $result;

        } catch (\Throwable $e) {

            self::$messages[] = 'find_address_list_items: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = $e->getMessage();
            return false;
        }
    }


    
    /**
     * Поиск по полям $search[]
     * Поддерживаемые поля: 'list', 'ip', 'ena', 'descr', 'id'. Поле 'list' обязательное.
     * Обновление всех найденных записей.
     * Меняются только явно указанные поля из $update[]
     * Поддерживаемые поля: 'list', 'ip', 'ena', 'descr'.
     * Значение поля NULL считается отсутствующим полем.
     * После изменения инвалидируется full-cache
     * @param array $search
     * @param array $update
     * @return int|false
     * false -- ошибка, 0 -- валидный вызов, но изменений нет, >0 -- количество обновлений
     */
    public function update_address_list_items(
            array $search,
            array $update
            ): int|false
    {
        // удаляет из входного массива все поля со значением null        
        $search = remove_null_fields($search);
        $update = remove_null_fields($update);
        
        if (isset($update[Mik::F_UPDATE_LIST]) && is_empty($update[Mik::F_UPDATE_LIST])) {
            unset($update[Mik::F_UPDATE_LIST]);
        }

        try {
            
            self::validate_address_list_search_fields($search);
            self::validate_address_list_update_fields($update);
            
        } catch (\Throwable $exc) {
            self::$messages[] = 'update_address_list_items: ' . __('Validation error | Ошибка валидации | Помилка валідації');
            self::$messages[] = $exc->getMessage();
            return false;
        }

        if ($update === []) {
            self::$messages[] = 'update_address_list_items: ' . __('nothing to update | нечего обновлять | нічого не оновлювати');
            return 0;
        }
        
        try {

            $items = $this->find_address_list_items($search);

            if ($items === false) {
                return false;
            }
            
            if ($items === []) {
                self::$messages[] = 'update_address_list_items: ' . __('No matching items | Нет подходящих записей | Немає відповідних записів');
                return 0;
            }

            /**
             * Проверка необходимости действия с полем
             */
            $has_update = fn(string $k)
                    => array_key_exists($k, $update);

            $updated = 0;

            foreach ($items as $item) {

                $query = (new Query('/ip/firewall/address-list/set'));

                $id = $item[Mik::F_LIST_ID] ?? null;
                if (is_empty($id)) {
                    self::$messages[] = 'update_address_list_items: ' . __('Missing id in found item | Отсутствует id в найденном элементе | Відсутній id у знайденому елементі');
                    self::$messages[] = '<pre>' . print_r($item, true) . '</pre>';
                    continue;
                }
                $query->equal('numbers', $id);

                if ($has_update(Mik::F_UPDATE_ENA)) {
                    $query->equal(Mik::F_LIST_DISABLED, $update[Mik::F_UPDATE_ENA] ? Mik::OFF : Mik::ON );
                }
                    
                if ($has_update(Mik::F_UPDATE_DESCR)) {
                    $query->equal(Mik::F_LIST_COMMENT, $update[Mik::F_UPDATE_DESCR]);
                }

                if ($has_update(Mik::F_UPDATE_IP)) {
                    $query->equal(Mik::F_LIST_ADDRESS, $update[Mik::F_UPDATE_IP]);
                }

                if ($has_update(Mik::F_UPDATE_LIST)) {
                    $query->equal(Mik::F_LIST_LIST, $update[Mik::F_UPDATE_LIST]);
                }

                $response = $this->client
                    ->query($query)
                    ->read();
                
                $parsed = self::parse_response($response);
                if ($parsed[self::F_PARSE_SUCCESS]) {
                    $updated++;
                } else {
                    self::$messages[] = 'update_address_list_items: ' . __('Record processing error | Ошибка обработки записи | Помилка обробки запису');
                    self::$messages[] = 'ITEM: <pre>'   . print_r($item,   true) . '</pre>';
                    self::$messages[] = 'UPDATE: <pre>' . print_r($update, true) . '</pre>';
                    self::$messages[] = 'RESULT: <pre>' . print_r($parsed, true) . '</pre>';
                }
            }

            $this->unset_address_list($search[Mik::F_SEARCH_LIST]);
            
            if (isset($update[Mik::F_UPDATE_LIST]) && $update[Mik::F_UPDATE_LIST] !== $search[Mik::F_SEARCH_LIST]) {
                $this->unset_address_list($update[Mik::F_UPDATE_LIST]);
            }            
            
            return $updated;

        } catch (\Throwable $e) {
            self::$messages[] = 'update_address_list_items:';
            self::$messages[] = $e->getMessage();
            self::$messages[] = '<pre>' . $e->getTraceAsString() . '</pre>';
            return false;
        }
    }
    
    

    /**
     * Создать или изменить одну запись address-list.
     *
     * Результат поиска:
     *   0 записей -> создание новой записи
     *   1 запись  -> обновление найденной записи
     *   >1 записей -> ошибка состояния устройства
     *
     * Поддерживаемые поля поиска:
     *   Mik::F_SEARCH_ID
     *   Mik::F_SEARCH_LIST
     *   Mik::F_SEARCH_IP
     *   Mik::F_SEARCH_ENA
     *   Mik::F_SEARCH_DESCR
     *
     * Поддерживаемые поля обновления:
     *   Mik::F_UPDATE_LIST
     *   Mik::F_UPDATE_IP
     *   Mik::F_UPDATE_ENA
     *   Mik::F_UPDATE_DESCR
     *
     * @param array $search
     * @param array $update
     * для ветки add отсутствующие, при этом нужные, данные белутся из массива поиска.
     *
     * @return bool|null
     * true  - запись создана или обновлена
     * false - ошибка RouterOS
     * null  - ошибка параметров либо опасное состояние устройства
     *
     * @throws \InvalidArgumentException
     */
    public function set_address_list_item(
        array $search,
        array $update
    ): bool|null
    {
        /**
         * Удалить из update поля со значением null
         */
        
        $search = remove_null_fields($search);
        $update = remove_null_fields($update);

        /**
         * Проверить допустимость полей search/update
         */
        
        self::validate_address_list_search_fields($search);
        self::validate_address_list_update_fields($update);
        
        /**
         * Проверить корректность параметров
         */
        if ($search === []) {
            self::$messages[] = 'set_address_list_item: ' . __('Search parameters not specified | Параметры поиска не указаны | Параметри пошуку не вказані');
            return null;
        }

        if ($update === []) {
            self::$messages[] = 'set_address_list_item: ' . __('Update options not specified | Параметры обновления не указаны | Параметри оновлення не вказані');
            return null;
        }

        /**
         * Найти записи
         */
        $items = $this->find_address_list_items($search);

        if ($items === false) {
            self::$messages[] = 'set_address_list_item: ' . __('No records were found for the specified search term | Не найдены записи для указанного поискового запроса | Не знайдено записів для вказаного пошукового запиту');
            self::$messages[] = '<pre>' . print_r($search, true) . '</pre>';
            return false;
        }

        $count = count($items);

        /**
         * >1 записей -> DEVICE ERROR
         */
        if ($count > 1) {
            self::$messages[] = 'DEVICE ERROR: set_address_list_item: multiple records found';
            self::$messages[] = 'Found records: ' . $count;
            self::$messages[] = __('Search | Поиск | Пошук') . ': ';
            self::$messages[] = '<pre>' . print_r($search, true) . '</pre>';
            return null;
        }

        /**
         * Создание
         * 0 записей -> add_address_list_item()
         */
        if ($count === 0) {

            $list  = $update[Mik::F_UPDATE_LIST]  ?? $search[Mik::F_SEARCH_LIST]  ?? null;
            $ip    = $update[Mik::F_UPDATE_IP]    ?? $search[Mik::F_SEARCH_IP]    ?? null;
            $ena   = $update[Mik::F_UPDATE_ENA]   ?? $search[Mik::F_SEARCH_ENA]   ?? true;
            $descr = $update[Mik::F_UPDATE_DESCR] ?? $search[Mik::F_SEARCH_DESCR] ?? '';

            if (is_empty($list)) {
                self::$messages[] = 'set_address_list_item: ' . __('Adding an entry | Добавление записи | Додавання запису') . ': ' . __('Address list name not specified | Имя списка Address list не указано | Ім\'я списку Address list не вказано');
                return null;
            }

            if ($ip === null) {
                self::$messages[] = 'set_address_list_item: ' . __('Adding an entry | Добавление записи | Додавання запису') . ': ' . __('IP address not specified | IP-адрес не указан | IP-адреса не вказана');
                return null;
            }

            return $this->add_address_list_item(
                list: $list,
                ip: $ip,
                ena: $ena,
                descr: $descr
            );
        }

        /**
         * Обновление
         * 1 запись -> update_address_list_items()
         */
        $list = $update[Mik::F_UPDATE_LIST]  ?? $search[Mik::F_SEARCH_LIST]  ?? null;
        $id   = $items[0][Mik::F_LIST_ID] ?? null;

        if ($id === null) {
            self::$messages[] = 'set_address_list_item: ' . __('missing internal id | отсутствует внутренний id | відсутній внутрішній id');
            return false;
        }

        return $this->update_address_list_items(
                search: [
                    Mik::F_SEARCH_LIST => $list,
                    Mik::F_SEARCH_ID => $id
                ],
                update: $update
        ) !== false;
    }    
    
    
    
    /**
     * Добавление записи в /ip/firewall/address-list
     * @param string $list
     * @param string $ip
     * @param int|bool $ena
     * @param string $descr
     * @return bool
     */
    public function add_address_list_item(
        string $list,
        string $ip,
        int|bool $ena = true,
        string $descr = '',
        bool $clear_cache = true): bool 
    {
        $list  = trim($list);
        $ip    = trim($ip);
        $descr = trim($descr);        
        $disabled = (bool)$ena ? Mik::OFF : Mik::ON;
        
        if ($list === '') {
            self::$messages[] = 'add_address_list_item: ' . __('address list not specified | список адресов не указан | список адрес не вказано');
            return false;
        }

        if ( !validate_ip($ip) && !is_ip_net($ip) ) {
            self::$messages[] = 'add_address_list_item: ' . __('Invalid IP format | Неверный формат IP | Недійсний формат IP') . " [$ip]";
            return false;
        }

        try {
            $query = (new Query(
                '/ip/firewall/address-list/add'
            ))
                ->equal(Mik::F_LIST_LIST, $list)
                ->equal(Mik::F_LIST_ADDRESS, $ip)
                ->equal(Mik::F_LIST_DISABLED, $disabled);

            if ($descr !== '') {
                $query->equal(Mik::F_LIST_COMMENT, $descr);
            }

            $response = $this->client
                ->query($query)
                ->read();

            $parsed = self::parse_response($response);

            if ($parsed[self::F_PARSE_SUCCESS]) { //  && ($parsed[self::F_PARSE_ID] !== null) -- подумать !!!
                
                $recordId = $parsed[self::F_PARSE_ID];

                if (!is_string($recordId) || trim($recordId) === '') {
                    throw new \UnexpectedValueException(
                        'add_address_list_item: RouterOS returned success without record id. Response: '
                        . print_r($response, true)
                    );
                }

                $recordId = trim($recordId);
                
                if ($clear_cache) {
                    // Очистка кэша
                    $this->unset_address_list($list);
                } else {
                    // Добавление записи в существующий кэш
                    if ($this->address_list === null) {
                        $this->address_list ??= [];
                    }
                    if (!isset($this->address_list[$list])) {
                        $this->address_list[$list] ??= [];
                    }
                    $this->address_list[$list][] = [
                        Mik::F_LIST_ID       => $recordId,
                        Mik::F_LIST_LIST     => $list,
                        Mik::F_LIST_ADDRESS  => $ip,
                        Mik::F_LIST_DISABLED => $disabled,
                        Mik::F_LIST_COMMENT  => $descr,
                        Mik::F_LIST_DYNAMIC  => Mik::OFF,
                    ];
                }
                
                return true;
                
            } else {
                self::$messages[] = 'add_address_list_item: ' . __('Error adding data | Ошибка добавления данных | Помилка додавання даних');
                self::$messages[] = 'message: ' . ($parsed[self::F_PARSE_MESSAGE] ?? '-');
                self::$messages[] = 'category: ' . ($parsed[self::F_PARSE_CATEGORY] ?? '-');                
                return false;
            }

        } catch (\UnexpectedValueException $e) {
            throw $e;

        } catch (\Throwable $e) {
            self::$messages[] = 'add_address_list_item: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = $e->getMessage();
            return false;
        }
    }
    


    /**
     * Добавляет записи в address-list из массива.
     * Если запись уже существует — пропускается.
     * Действия:
     * -- нормализовать строку через normalizeAddressListRow()
     * -- проверить наличие через in_address_list_item()
     * -- если есть — пропустить
     * -- если нет — добавить через add_address_list_item()
     * -- Посчитать успешные добавления
     *
     * Формат строки:
     * [
     *     'list'  => string,
     *     'ip'    => string,
     *     'ena'   => bool,
     *     'descr' => string
     * ]
     */
    public function add_address_list_from_array(array $list_items): int|false
    {
        if ($list_items === []) {
            self::$messages[] = 'add_address_list_from_array: ' . __('Empty input array | Пустой входной массив | Порожній вхідний масив');
            return false;
        }

        $added_count = 0;
        $lists = []; // изменённые адресные листы
        foreach ($list_items as $raw_item) {

            $item = self::normalizeAddressListRow($raw_item);

            if ($item === null) {
                self::$messages[] = 'add_address_list_from_array: ' . __('Record normalization error | Ошибка нормализации записи | Помилка нормалізації запису');
                self::$messages[] = print_r($raw_item, true);
                continue;
            }

            if ($this->in_address_list_item(
                    list:  $item[Mik::F_LIST_LIST],
                    ip:    $item[Mik::F_LIST_ADDRESS],
                    ena:   $item[Mik::F_LIST_ENABLED])) 
            {
                continue;
            }

            if ($this->add_address_list_item(
                    list:  $item[Mik::F_LIST_LIST],
                    ip:    $item[Mik::F_LIST_ADDRESS],
                    ena:   $item[Mik::F_LIST_ENABLED],
                    descr: $item[Mik::F_LIST_COMMENT],
                    clear_cache: false)) 
            {
                $added_count++;
                $lists[$item[Mik::F_LIST_LIST]] = true;
            }
        }
        
        foreach (array_keys($lists) as $list) {
            $this->unset_address_list($list);
        }
        
        return $added_count;
    }



    /**
     * Удаляет запись из RouterOS address-list по её внутреннему идентификатору.
     *
     * Выполняет команду: /ip/firewall/address-list/remove
     *
     * После успешного удаления кэш address-list:
     * - полностью инвалидируется, если $clear_cache = true;
     * - либо локально обновляется удалением соответствующей записи,
     *   если $clear_cache = false.
     *
     * Локальное обновление кэша используется для массовых операций,
     * чтобы избежать повторного чтения полного списка с устройства
     * после каждого удаления.
     *
     * При ошибке RouterOS сообщение и категория ошибки сохраняются
     * в self::$messages.
     *
     * @param string $id
     *   Внутренний идентификатор записи RouterOS
     *   (например: "*774").
     *
     * @param bool $clear_cache
     *   Очистить локальный кэш после успешного удаления.
     *
     * @return bool
     *   true  — запись успешно удалена;
     *   false — запись не удалена, идентификатор некорректен
     *           либо RouterOS вернул ошибку выполнения.
     */    
    public function remove_address_list_item(string $list, string $id, bool $clear_cache = false): bool
    {
        $list = trim($list);
        if ($list === '') {
            self::$messages[] = 'remove_address_list_item: ' . __('Address list name not specified | Имя адресного списка не указано | Ім\'я адресного списку не вказано');
            return false;
        }

        $id = trim($id);
        if ($id === '') {
            self::$messages[] = 'remove_address_list_item: ' . __('record id not specified | id записи не указан | id запису не вказано');
            return false;
        }

        try {

            $query = (new Query('/ip/firewall/address-list/remove'))
                ->equal('numbers', $id);

            $response = $this->client
                ->query($query)
                ->read();

            $parsed = self::parse_response($response);

            if ($parsed[self::F_PARSE_SUCCESS]) {

                if ($clear_cache) {
                    $this->unset_address_list($list);
                } else {
                    if (isset($this->address_list[$list])) {
                        foreach ($this->address_list[$list] as $k => $row) {
                            if (($row[Mik::F_LIST_ID] ?? null) === $id) {
                                unset($this->address_list[$list][$k]);
                                if ($this->address_list[$list] === []) {
                                    $this->unset_address_list($list);
                                }
                                break;
                            }
                        }
                    }                    
                }
                return true;
            }

            self::$messages[] = 'remove_address_list_item: ' . __('Error deleting record | Ошибка удаления записи | Помилка видалення запису');
            self::$messages[] = 'message: ' . ($parsed[self::F_PARSE_MESSAGE] ?? '-');
            self::$messages[] = 'category: ' . ($parsed[self::F_PARSE_CATEGORY] ?? '-');
            return false;

        } catch (\Throwable $e) {
            self::$messages[] = 'remove_address_list_item: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = $e->getMessage();
            return false;
        }
    }    
    




    /**
     * Scoped-safe sync address-list items.
     *
     * Синхронизирует RouterOS address-list только внутри списков,
     * присутствующих во входном массиве.
     *
     * Не затрагивает списки, отсутствующие во входных данных.
     *
     * Алгоритм:
     *  1. Группировка входных данных по list
     *  2. Группировка текущего состояния по list
     *  3. Diff выполняется внутри каждого list отдельно
     *  4. Применение add/remove внутри scope
     *  5. Кэш инвалидируется один раз в конце
     *
     * @param array $raw_items
     * @param bool $stop_on_error
     *
     * @return int|false -- количество выполненных операций
     */
    public function sync_address_list_scoped(array $raw_items, bool $stop_on_error = false): int|false
    {
        
        $make_key = static fn(string $address, bool|int $ena): string => $address . "#" . (int)$ena;
        
//        debug($raw_items, '$raw_items', die:0);
        
        if ($raw_items === []) {
            self::$messages[] = 'sync_address_list_scoped: empty input';
            return false;
        }
        
        
        
        /**
         * 1. GROUP INPUT BY LIST
         * 
         * $desired[$list][$key] = $item;
         * 
         */
        $desired = [];

        foreach ($raw_items as $raw) {

            $item = self::normalizeAddressListRow($raw);

            if ($item === null) {
                if ($stop_on_error) {
                    return false;
                }
                continue;
            }

            $list = $item[Mik::F_LIST_LIST];
            
            $key = $make_key($item[Mik::F_LIST_ADDRESS], $item[Mik::F_LIST_ENABLED]);

            $desired[$list][$key] = $item;
        }
        
        $lists = array_keys($desired);
        
        foreach ($lists as $list) {
            ksort($desired[$list]);
        }
        

        
        /**
         * 2. ГРУППИРОВАТЬ ТЕКУЩЕЕ СОСТОЯНИЕ ПО СПИСКАМ
         */
        $current = [];

        foreach ($lists as $list) {
            
            $this->ensureAddressList($list);
            
            foreach ($this->address_list[$list] as $raw) {

                $item = self::normalizeAddressListRow($raw);

                if ($item === null) {
                    if ($stop_on_error) {
                        self::$messages[] = 'sync_address_list_scoped: ' . __('After normalization, an empty entry was returned | После нормализации вернулась пустая запись | Після нормалізації повернувся порожній запис');
                        self::$messages[] = 'RAW: ' . print_r($raw, true);
                        return false;
                    }
                    continue;
                }

                $key = $make_key($item[Mik::F_LIST_ADDRESS], $item[Mik::F_LIST_ENABLED]);

                $current[$list][$key] = $item;
            }
            if (!empty($current[$list])) {
                ksort($current[$list]);
            }
        }

        
        
        /**
         * 3. SCOPED SYNC PER LIST
         */
        
        $count_completed = 0;
        
        foreach ($desired as $list => $desired_items) {

            $current_items = $current[$list] ?? [];

            $to_del = array_diff_key($current_items, $desired_items);
            $to_add = array_diff_key($desired_items, $current_items);

//            MsgQueue::msg(MsgType::SUCCESS, '<pre>' . print_r([$to_add, $to_del], true) . '</pre>');
            MsgQueue::msg(MsgType::SUCCESS, str_replace(' ', '&nbsp;', __('Таблица %10s', $list) . ' ' . __('в _базе_ содержит %4s записей', count($desired_items))));
            MsgQueue::msg(MsgType::SUCCESS, str_replace(' ', '&nbsp;', __('Таблица %10s', $list) . ' ' . __('на устр. содержит %4s записей', count($current_items))));
            MsgQueue::msg(MsgType::SUCCESS, str_replace(' ', '&nbsp;', __('План добавления %4d записей', count($to_add))));
            MsgQueue::msg(MsgType::SUCCESS, str_replace(' ', '&nbsp;', __('План ..удаления %4d записей', count($to_del))));
            
            
            
            /**
             * ADD
             */
            $count_added = 0;
            foreach ($to_add as $item) {

                $ok = $this->add_address_list_item(
                        list:   $item[Mik::F_LIST_LIST],
                        ip:     $item[Mik::F_LIST_ADDRESS],
                        ena:    $item[Mik::F_LIST_ENABLED],
                        descr:  $item[Mik::F_LIST_COMMENT] ?? '',
                        clear_cache: false
                );

                if ($ok) {
                    $count_added++;
                } elseif ($stop_on_error) {
                    self::$messages[] = 'sync_address_list_scoped: ' . __('Error adding entry | Ошибка добавления записи | Помилка додавання запису');
                    self::$messages[] = 'ITEM: ' . print_r($item, true);
                    return false;
                }
            }

            MsgQueue::msg(MsgType::SUCCESS, str_replace(' ', '&nbsp;', __('Добавлено %4d записей', $count_added)));
            
            /**
             * DELETE
             */
            $count_delete = 0;
            foreach ($to_del as $item) {
                $ok = $this->remove_address_list_item(
                        list: $item[Mik::F_LIST_LIST],
                        id: $item[Mik::F_LIST_ID],
                        clear_cache: false
                );

                if ($ok) {
                    $count_delete++;
                } elseif ($stop_on_error) {
                    self::$messages[] = 'sync_address_list_scoped: ' . __('Error deleting entry | Ошибка удаления записи | Помилка видалення запису');
                    self::$messages[] = 'ITEM: ' . print_r($item, true);
                    return false;
                }
            }

            MsgQueue::msg(MsgType::SUCCESS, str_replace(' ', '&nbsp;', __('Удалено.. %4d записей', $count_delete)));
            
            /**
             * 4. FINAL CACHE INVALIDATION (safe)
             */
            $this->unset_address_list($list);
            
            $count_completed += ($count_added + $count_delete);
            
        }

        MsgQueue::msg(MsgType::SUCCESS, __('Результат операции') . ':');
        foreach ($desired as $list => $unused) {
            $this->ensureAddressList($list);
            if (count($desired[$list]) == count($this->address_list[$list])) {
                $msg_type = MsgType::SUCCESS;
            } else {
                $msg_type = MsgType::ERROR;
            }
            MsgQueue::msg($msg_type, str_replace(' ', '&nbsp;', __('Table | Таблица | Таблиця') . ' ' . $list . ' '
                    . __('on device contains %4s entries | на устройстве содержит %4s записей | на пристрої містить %4s записів', count($this->address_list[$list])) . ', ' 
                    . '(' . __('there should be %4s entries | должно быть %4s записей | має бути %4s записів', count($desired[$list])) . ')'));
        }
        
        return $count_completed;
    }    
    
    
    
    /*
     * 
     * ========================================================================
     * Начало блока NAT
     * 
     */
    

    
    private function ensureNatRules(): void
    {
        if ($this->nat_rules !== null) {
            return;
        }

        try {

            $query = new Query('/ip/firewall/nat/print');

            $raw = $this->client
                ->query($query)
                ->read();
            
            $this->nat_rules = array_values(
                array_filter(
                    array_map(fn($row) => self::normalizeNatRuleRow($row), $raw),
                    fn($row) => is_array($row)
                )
            );

        } catch (\Throwable $e) {
            $this->nat_rules = null;
            self::$messages[] = 'ensureNatRules: ' . __('NAT rules request error | Ошибка получения NAT rules таблицы | Помилка отримання NAT rules таблиці');
            self::$messages[] = $e->getMessage();
        }
    }    
    


    public static function normalizeNatRuleRow(?array $item): ?array
    {
        if (empty($item) || !is_array($item)) {
            return null;
        }

        return [
            Mik::F_NAT_ID                   => $item[Mik::F_NAT_ID] ?? null,
            Mik::F_NAT_CHAIN                => trim((string)($item[Mik::F_NAT_CHAIN] ?? '')),
            Mik::F_NAT_ACTION               => trim((string)($item[Mik::F_NAT_ACTION] ?? '')),
            Mik::F_NAT_TO_ADDRESSES         => trim((string)($item[Mik::F_NAT_TO_ADDRESSES] ?? '')),
            Mik::F_NAT_OUT_INTERFACE_LIST   => trim((string)($item[Mik::F_NAT_OUT_INTERFACE_LIST] ?? '')),
            Mik::F_NAT_SRC_ADDRESS          => trim((string)($item[Mik::F_NAT_SRC_ADDRESS] ?? '')),
            Mik::F_NAT_IN_INTERFACE_LIST    => trim((string)($item[Mik::F_NAT_IN_INTERFACE_LIST] ?? '')),
            Mik::F_NAT_OUT_INTERFACE        => trim((string)($item[Mik::F_NAT_OUT_INTERFACE] ?? '')),
            Mik::F_NAT_DST_ADDRESS          => trim((string)($item[Mik::F_NAT_DST_ADDRESS] ?? '')),
            Mik::F_NAT_TO_PORTS             => trim((string)($item[Mik::F_NAT_TO_PORTS] ?? '')),
            Mik::F_NAT_PROTOCOL             => trim((string)($item[Mik::F_NAT_PROTOCOL] ?? '')),
            Mik::F_NAT_SRC_ADDRESS_LIST     => trim((string)($item[Mik::F_NAT_SRC_ADDRESS_LIST] ?? '')),
            Mik::F_NAT_IN_INTERFACE         => trim((string)($item[Mik::F_NAT_IN_INTERFACE] ?? '')),
            Mik::F_NAT_DST_PORT             => trim((string)($item[Mik::F_NAT_DST_PORT] ?? '')),
            Mik::F_NAT_LOG                  => mikBool($item[Mik::F_NAT_LOG] ?? false),
            Mik::F_NAT_LOG_PREFIX           => trim((string)($item[Mik::F_NAT_LOG_PREFIX] ?? '')),
            Mik::F_NAT_BYTES                => (int)($item[Mik::F_NAT_BYTES] ?? 0),
            Mik::F_NAT_PACKETS              => (int)($item[Mik::F_NAT_PACKETS] ?? 0),
            Mik::F_NAT_INVALID              => mikBool($item[Mik::F_NAT_INVALID] ?? false),
            Mik::F_NAT_DYNAMIC              => mikBool($item[Mik::F_NAT_DYNAMIC] ?? false),
            Mik::F_NAT_DISABLED             => mikBool($item[Mik::F_NAT_DISABLED] ?? false),
            Mik::F_NAT_COMMENT              => trim((string)($item[Mik::F_NAT_COMMENT] ?? '')),
            Mik::F_NAT_ENA                  => !mikBool($item[Mik::F_NAT_DISABLED] ?? false),
        ];
    }
    
    
    
    public function get_nat_rules(): array {
        $this->ensureNatRules();
        return $this->nat_rules;
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
    public static function get_nat_11(array $rules): array
    {
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

    

    /**
     * Возвращает пару правил NAT 1:1 (dstnat + srcnat)
     * для указанного соответствия private/public IP.
     *
     * @param array  $rules
     * @param string $ip_private
     * @param string $ip_public
     *
     * @return array
     *
     * @throws Exception
     *   Если не найдена ровно одна пара:
     *   1 dstnat и 1 srcnat.
     */
    public static function get_nat_11_by_ip(array $rules, string $ip_private, string $ip_public): array
    {

        if (!validate_ip($ip_private)) {
            throw new InvalidArgumentException('get_nat_11_by_ip: invalid private ip [' . $ip_private . ']');
        }

        if (!validate_ip($ip_public)) {
            throw new InvalidArgumentException('get_nat_11_by_ip: invalid public ip [' . $ip_public . ']');
        }        
        
        $result = [];

        $count_srcnat = 0;
        $count_dstnat = 0;
        
        foreach ($rules as $row) {

            if ($count_dstnat > 1 || $count_srcnat > 1) {
                break;
            }
            
            if (($row['action'] ?? null) !== 'netmap' || mikBool($row['disabled'] ?? false)) {
                continue;
            }

            $chain = $row['chain'] ?? '';

            /**
             * dstnat
             */
            if ($chain === 'dstnat') {

                if (($row['to-addresses'] ?? null) !== $ip_private) { continue; }
                if (($row['dst-address']  ?? null) !== $ip_public)  { continue; }

                $result[] = $row;
                $count_dstnat++;
                continue;
            }

            /**
             * srcnat
             */
            if ($chain === 'srcnat') {

                if (($row['src-address']  ?? null) !== $ip_private) { continue; }
                if (($row['to-addresses'] ?? null) !== $ip_public)  { continue; }

                $result[] = $row;
                $count_srcnat++;
                continue;
            }
        }

        if ($count_dstnat !== 1 || $count_srcnat !==1) {
            throw new Exception(
                    'get_nat_11_by_ip: '
                    . __('NAT 1:1 must contain exactly one srcnat and one dstnat rule | NAT 1:1 должен содержать ровно одно правило srcnat и одно правило dstnat | NAT 1:1 має містити точно одне правило srcnat і одне правило dstnat')
                    . PHP_EOL
                    . __('IP pair | IP-пара | IP пара')
                    . ': [' . $ip_private . ' <-> ' . $ip_public . ']'
                    . PHP_EOL
                    . '<pre>' . print_r($result, true). '</pre>');
        }
        
        return $result;
    }
    
    
    
    public function set_nat_11(string $ip_local, string $ip_public, string $descr): bool
    {
        if (!validate_ip($ip_local) || !validate_ip($ip_public)) {
            self::$messages[] = 'Неверный IP';
            return false;
        }

        $rules = $this->get_nat_rules();

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
    

    
    public function get_nat_maskarade(): array {
        throw new \Exception('get_nat_maskarade: Не наисан');
    }

    
    
    public function get_nat_netmap(): array
    {
        $rules = $this->get_nat_rules();

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

        $rules = $this->get_nat_rules();

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

    

    /*
     * 
     * Конец блока NAT
     * ========================================================================
     * 
     */


    

    public function get_interfaces(): array {
        return $this->routerRead('/interface/print');
    }



    
    
    
    
    public function get_interface_lists(?bool $dynamic = null): array {
        $rows = $this->routerRead('/interface/list/print');
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
        return $this->routerRead('/interface/list/member/print');
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
        return $this->filter_add([
            'chain'             => $chain,
            'protocol'          => $protocol,
            'in-interface-list' => $in_interface_ist,
            'dst-port'          => $dstports,
            'action'            => $action,
            'comment'           => $comment,
        ]);
    }



    public function get_nat_uv(): array {
        
    }



    public function interface_list_add(string $name): bool
    {
        return $this->routerWriteOk('/interface/list/add', ['name' => $name]);
    }



    public function interface_list_member_add(string $interface, string $list): bool
    {
        return $this->routerWriteOk('/interface/list/member/add', [
            'interface' => $interface,
            'list' => $list,
        ]);
    }



    public function interface_list_member_remove(string $id): bool
    {
        return $this->routerWriteOk('/interface/list/member/remove', ['.id' => $id]);
    }



    public function get_neighbor_discovery_settings(): array
    {
        return $this->routerRead('/ip/neighbor/discovery-settings/print');
    }



    public function set_neighbor_discovery_interface_list(string $list): bool
    {
        return $this->routerWriteOk('/ip/neighbor/discovery-settings/set', [
            'discover-interface-list' => $list,
        ]);
    }



    public function ip_service_set(string $id, int $port, ?string $certificate = null): bool
    {
        $params = [
            '.id' => $id,
            'port' => $port,
        ];

        if ($certificate !== null && trim($certificate) !== '') {
            $params['certificate'] = trim($certificate);
        }

        return $this->routerWriteOk('/ip/service/set', $params);
    }



    public function ip_service_enable(string $id): bool
    {
        return $this->routerWriteOk('/ip/service/enable', ['.id' => $id]);
    }



    public function ip_service_disable(string $id): bool
    {
        return $this->routerWriteOk('/ip/service/disable', ['.id' => $id]);
    }



    public function filter_remove(string $id): bool
    {
        return $this->routerWriteOk('/ip/firewall/filter/remove', ['.id' => $id]);
    }



    public function filter_add(array $rule): bool
    {
        return $this->routerWriteOk('/ip/firewall/filter/add', $rule);
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
        $services = $this->routerRead('/ip/service/print');
        
        return array_values(array_filter(
            $services,
            static fn(array $service): bool =>
                !mikBool($service['dynamic'] ?? false)
        ));
    }        
        
        

    
    
    
    
    
    public function get_certificates(): array {
        return $this->routerRead('/certificate/print');
    }

    public function get_certificate(string $name): array {
        return $this->routerRead('/certificate/print', ['name' => $name]);
    }

    public function del_certificate(string $id): bool {
        return $this->routerWriteOk('/certificate/remove', ['.id' => $id]);
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
        return $this->routerWriteReturnId('/certificate/add', [
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
    }

    function certificate_sign(string $name): bool {
        return $this->routerWriteOk('/certificate/sign', ['number' => $name]);
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
    
    
    
    private function ensureIpServices(): bool
    {
        if (empty($this->ip_services)) { 
            $ipServices = $this->routerRead('/ip/service/print');

            if (!$ipServices) {
                self::$messages[] = 'SERVICES пусты или не получены';
                $this->ip_services = [];
                return false;
            }
            $this->ip_services = $ipServices;
        }
        return true;
    }    


    
    private function ensureFilterRules(): bool
    {
        if (empty($this->filter_rules)) { 
            $rules = $this->routerRead('/ip/firewall/filter/print');

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
        $rules = $this->get_nat_rules();

        if (empty($rules)) {
            return null;
        }

        return $rules[$position]['.id'] ?? null;
    }    


    

    /*
     * 
     * ========================================================================
     * Начало блока ARP
     * 
     */

    
    
    /**
     * Загружает полный ARP-кэш с устройства при первом обращении.
     * При ошибке сохраняет сообщение в лог и устанавливает кэш в null.
     * @return void
     */
    private function ensureArp(): void
    {
        if ($this->arp !== null) {
            return;
        }

        try {

            $query = new Query('/ip/arp/print');
            
            $this->arp = $this->client
                ->query($query)
                ->read();

        } catch (\Throwable $e) {
            $this->arp = null;
            self::$messages[] = 'ensureArp: ' . __('ARP request error | Ошибка получения ARP-таблицы | Помилка отримання ARP-таблиці');
            self::$messages[] = $e->getMessage();
        }
    }



    /**
     * Нормализует запись ARP-кэша MikroTik.
     *
     * .id, address -- обязательное поле.
     * Если ROS 6, то status = null,
     * ena  = !disabled,
     * MAC в верхний регистр,
     * comment, interface, vrf  -- пустая строка, а не null.
     * 
     * Возвращает:
     * [
     *     '.id'       => '*123',
     *     'ip'        => '10.1.1.1',
     *     'mac'       => 'AA:BB:CC:DD:EE:FF',
     *     'interface' => 'bridge-lan',
     *     'ena'       => true,
     *     'dynamic'   => false,
     *     'comment'   => '',
     *     'complete'  => true,
     *     'dhcp'      => false,
     *     'invalid'   => false,
     *     'status'    => 'reachable'
     * ]
     *
     * @param array $item Сырая запись RouterOS
     * @return array|null
     */
    public static function normalizeArpRow(array $item): ?array
    {
        if (!isset($item[Mik::F_ARP_ID])) {
            return null;
        }

        if (!isset($item[Mik::F_ARP_IP])) {
            return null;
        }

        return [
            Mik::F_ARP_ID        => trim((string)$item[Mik::F_ARP_ID]),
            Mik::F_ARP_IP        => trim((string)($item[Mik::F_ARP_IP] ?? '')),
            Mik::F_ARP_MAC       => strtoupper(trim((string)($item[Mik::F_ARP_MAC] ?? ''))),
            Mik::F_ARP_INTERFACE => trim((string)($item[Mik::F_ARP_INTERFACE] ?? '')),
            Mik::F_ARP_COMMENT   => trim((string)($item[Mik::F_ARP_COMMENT] ?? '')),
            Mik::F_ARP_DYNAMIC   => mikBool($item[Mik::F_ARP_DYNAMIC] ?? false),
            Mik::F_ARP_COMPLETE  => mikBool($item[Mik::F_ARP_COMPLETE] ?? false),
            Mik::F_ARP_DHCP      => mikBool($item[Mik::F_ARP_DHCP] ?? false),
            Mik::F_ARP_INVALID   => mikBool($item[Mik::F_ARP_INVALID] ?? false),
            Mik::F_ARP_PUBLISHED => mikBool($item[Mik::F_ARP_PUBLISHED] ?? false),
            Mik::F_ARP_DISABLED  => mikBool($item[Mik::F_ARP_DISABLED] ?? false),
            Mik::F_ARP_ENA       => !mikBool($item[Mik::F_ARP_DISABLED] ?? false),
            Mik::F_ARP_STATUS    => isset($item[Mik::F_ARP_STATUS]) ? trim((string)$item[Mik::F_ARP_STATUS]) : null,
            Mik::F_ARP_VRF       => trim((string)($item[Mik::F_ARP_VRF] ?? '')
            ),
        ];
    }



    /**
     * Возвращает список ARP-записей по заданным фильтрам.
     *
     * Все фильтры работают по точному совпадению,
     * кроме $descr, который проверяется как префикс комментария.
     *
     * Если фильтры не заданы — возвращается весь ARP-кэш.
     *
     * @param string|null $id
     * @param string|null $ip
     * @param string|null $mac
     * @param string|null $interface
     * @param int|bool|null $ena
     * @param int|bool|null $dynamic
     * @param int|bool|null $complete
     * @param int|bool|null $dhcp
     * @param int|bool|null $invalid
     * @param string|null $descr
     * @return array
     */
    public function get_arp_items(
        ?string $id = null,
        ?string $ip = null,
        ?string $mac = null,
        ?string $interface = null,
        int|bool|null $ena = null,
        int|bool|null $dynamic = null,
        int|bool|null $complete = null,
        int|bool|null $dhcp = null,
        int|bool|null $invalid = null,
        ?string $descr = null): array
    {
        $this->ensureArp();

        if ($this->arp === null) {
            return [];
        }

        if (
            $id === null
            && $ip === null
            && $mac === null
            && $interface === null
            && $ena === null
            && $dynamic === null
            && $complete === null
            && $dhcp === null
            && $invalid === null
            && $descr === null
        ) {
            return $this->arp;
        }

        if ($id !== null) { $id = trim($id); }
        if ($ip !== null) { $ip = trim($ip); }
        if ($mac !== null) { $mac = strtoupper(trim($mac)); }
        if ($interface !== null) { $interface = trim($interface); }
        if ($descr !== null) { $descr = trim($descr); }
        if ($ena !== null) { $ena = (bool)$ena; }
        if ($dynamic !== null) { $dynamic = (bool)$dynamic; }
        if ($complete !== null) { $complete = (bool)$complete; }
        if ($dhcp !== null) { $dhcp = (bool)$dhcp; }
        if ($invalid !== null) { $invalid = (bool)$invalid; }

        $result = [];

        foreach ($this->arp as $rec) {

            $item = self::normalizeArpRow($rec);

            if ($item === null) { continue; }
            if ($id !== null && $item[Mik::F_ARP_ID] !== $id) { continue; }
            if ($ip !== null && $item[Mik::F_ARP_IP] !== $ip) { continue; }
            if ($mac !== null && $item[Mik::F_ARP_MAC] !== $mac) { continue; }
            if ($interface !== null && $item[Mik::F_ARP_INTERFACE] !== $interface) { continue; }
            if ($ena !== null && $item[Mik::F_ARP_ENA] !== $ena) { continue; }
            if ($dynamic !== null && $item[Mik::F_ARP_DYNAMIC] !== $dynamic) { continue; }
            if ($complete !== null && $item[Mik::F_ARP_COMPLETE] !== $complete) { continue; }
            if ($dhcp !== null && $item[Mik::F_ARP_DHCP] !== $dhcp) { continue; }
            if ($invalid !== null && $item[Mik::F_ARP_INVALID] !== $invalid) { continue; }

            if ($descr !== null) {
                $comment = $item[Mik::F_ARP_COMMENT] ?? '';
                if (!str_starts_with($comment, $descr)) { continue; }
            }

            $result[] = $item;
        }

        return $result;
    }



    /**
     * Унифицированный поиск ARP-записей.
     *
     * Поддерживаемые поля:
     *     Mik::F_ARP_ID
     *     Mik::F_ARP_IP // ARP работает только с IP-адресами, без сетей.
     *     Mik::F_ARP_MAC
     *     Mik::F_ARP_COMMENT // поиск по префиксу
     *     Mik::F_ARP_INTERFACE
     *     Mik::F_ARP_DYNAMIC
     *     Mik::F_ARP_ENA
     *     Mik::F_ARP_COMPLETE
     *     Mik::F_ARP_DHCP
     *     Mik::F_ARP_INVALID
     *
     * @param array $search
     * @return array|false
     * false — ошибка параметров или внутренняя ошибка
     * []    — ничего не найдено
     */
    public function find_arp_items(
        array $search
    ): array|false
    {
        if ($search === []) {
            self::$messages[] = 'find_arp_items: ' . __('Search parameters not specified | Не указаны параметры поиска | Не вказані параметри пошуку');
            return false;
        }

        if (
            isset($search[Mik::F_ARP_IP])
            && !is_empty($search[Mik::F_ARP_IP])
            && !validate_ip($search[Mik::F_ARP_IP])
        ) {
            self::$messages[] = 'find_arp_items: ' . __('Invalid IP format | Неверный формат IP | Недійсний формат IP') . ': ' . $search[Mik::F_ARP_IP];
            return false;
        }

//        try {
            return $this->get_arp_items(
                id:        $search[Mik::F_ARP_ID]        ?? null,
                ip:        $search[Mik::F_ARP_IP]        ?? null,
                mac:       $search[Mik::F_ARP_MAC]       ?? null,
                interface: $search[Mik::F_ARP_INTERFACE] ?? null,
                ena:       $search[Mik::F_ARP_ENA]       ?? null,
                dynamic:   $search[Mik::F_ARP_DYNAMIC]   ?? null,
                complete:  $search[Mik::F_ARP_COMPLETE]  ?? null,
                dhcp:      $search[Mik::F_ARP_DHCP]      ?? null,
                invalid:   $search[Mik::F_ARP_INVALID]   ?? null,
                descr:     $search[Mik::F_ARP_COMMENT]   ?? null
            );
//        } catch (\Throwable $e) {
//            self::$messages[] = 'find_arp_items:';
//            self::$messages[] = $e->getMessage();
//            return false;
//        }

    }
    
    
    
    /**
     * Проверка существования ARP-записи.
     *
     * Все указанные параметры участвуют в поиске.
     * Если параметр равен null — он не участвует в фильтрации.
     *
     * @param string|null $ip
     * @param string|null $mac
     * @param string|null $interface
     * @param int|bool|null $ena
     * @return bool|null
     * true  -- запись найдена
     * false -- запись не найдена
     * null  -- ошибка параметров
     */
    public function in_arp_item(
        ?string $ip = null,
        ?string $mac = null,
        ?string $interface = null,
        int|bool|null $ena = null
    ): bool|null
    {
        if ($ip !== null) {
            $ip = trim($ip);
            if ($ip === '') { $ip = null; }
        }

        if ($mac !== null) {
            $mac = trim($mac);
            if ($mac === '') { $mac = null; }
        }

        if ($interface !== null) {
            $interface = trim($interface);
            if ($interface === '') { $interface = null; }
        }        
        
        
        if (
            $ip === null && $mac === null
            && $interface === null && $ena === null
        ) {
            self::$messages[] = 'in_arp_item: ' . __('Search parameters not specified');
            return null;
        }

        if ($ip === null && $mac === null) {
            self::$messages[] = 'in_arp_item: ' . __('IP or MAC required | Требуется IP или MAC | Потрібен IP або MAC');
            return null;
        }

        if ($ip !== null && !validate_ip($ip)) {
            self::$messages[] = 'in_arp_item: ' . __('Incorrect IP address | Не верный IP-адрес | Не вірна IP-адреса');
            return null;
        }

        if ($mac !== null && !validate_mac($mac)) {
            self::$messages[] = 'in_arp_item: ' . __('Incorrect MAC address | Не верный MAC-адрес | Не вірна MAC-адреса');
            return null;
        }        

        return count(
            $this->get_arp_items(
                ip:        $ip,
                mac:       $mac,
                interface: $interface,
                ena:       $ena)) > 0;
    }
    


    /**
     * Удаляет ARP-запись по id.
     *
     * При успешном удалении:
     * - если $clear_cache=true, ARP-кэш инвалидируется полностью;
     * - если $clear_cache=false, запись удаляется из локального кэша.
     *
     * @param string $id
     * @param bool $clear_cache
     * @return bool
     * true  — запись успешно удалена
     * false — ошибка удаления или некорректные параметры
     */
    public function remove_arp_item(string $id, bool $clear_cache = false): bool
    {
        $id = trim($id);

        if ($id === '') {
            self::$messages[] = 'remove_arp_item: ' . __('record id not specified | id записи не указан | id запису не вказано');
            return false;
        }

        $this->ensureArp();

        try {

            $query = (new Query('/ip/arp/remove'))
                ->equal('numbers', $id);
            $response = $this->client
                ->query($query)
                ->read();
            
            $parsed = self::parse_response($response);
            if ($parsed[self::F_PARSE_SUCCESS]) {
                if ($clear_cache) {
                    $this->arp = null;
                } else {
                    if ($this->arp !== null) {
                        foreach ($this->arp as $k => $row) {
                            if (($row[Mik::F_ARP_ID] ?? null) === $id) {
                                unset($this->arp[$k]);
                                break;
                            }
                        }
                    }
                }
                return true;
            }

            self::$messages[] = 'remove_arp_item: ' . __('Error deleting record | Ошибка удаления записи | Помилка видалення запису');
            self::$messages[] = 'message: ' . ($parsed[self::F_PARSE_MESSAGE] ?? '-');
            self::$messages[] = 'category: ' . ($parsed[self::F_PARSE_CATEGORY] ?? '-');
            return false;

        } catch (\Throwable $e) {
            self::$messages[] = 'remove_arp_item: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = 'id: ' . $id;
            self::$messages[] = $e->getMessage();
            return false;
        }
    }



    /**
     * Удаление ARP-записей по фильтру.
     *
     * Поддерживаются те же поля поиска, что и в find_arp_items().
     *
     * @param array $search
     * @param bool $stop_on_error
     * @return int|false
     * false — ошибка поиска или прерывание по ошибке удаления
     * 0     — записи не найдены
     * >0    — количество успешно удалённых записей
     */
    public function remove_arp_items(
        array $search,
        bool $stop_on_error = false
    ): int|false
    {
        $items = $this->find_arp_items($search);

        if ($items === false) { return false; }

        if ($items === []) {
            self::$messages[] = 'remove_arp_items: ' . __('No matching items | Нет подходящих записей | Немає відповідних записів');
            return 0;
        }

        $removed = 0;

        foreach ($items as $item) {

            $id = $item[Mik::F_ARP_ID] ?? null;

            if ($id === null) {
                self::$messages[] = 'remove_arp_items: ' . __('Record id not found | Не найден id записи | Не знайдено id запису');
                self::$messages[] = print_r($item, true);
                if ($stop_on_error) { return false; }
                continue;
            }

            $ok = $this->remove_arp_item(
                id: $id,
                clear_cache: false
            );

            if ($ok) {
                $removed++;
                continue;
            }

            if ($stop_on_error) { return false; }
        }

        $this->arp = null;
        return $removed;
    }    
    
    

    /**
     * Статистика ARP-таблицы.
     *
     * Возвращает количество записей по различным признакам,
     * а также распределение по интерфейсам.
     *
     * [
     *     Mik::F_ARP_TOTAL     => 1000,
     *     Mik::F_ARP_DYNAMIC   => 950,
     *     Mik::F_ARP_ENABLED   => 955,
     *     Mik::F_ARP_DISABLED  => 5,
     *     Mik::F_ARP_PUBLISHED => 50,
     *     Mik::F_ARP_COMPLETE  => 500,
     *     Mik::F_ARP_DHCP      => 50,
     *     Mik::F_ARP_INVALID   => 5,
     *     Mik::F_ARP_INTERFACES => [
     *         'bridge-lan' => 500,
     *         'vlan10'     => 300,
     *         'ether1'     => 200,
     *     ]
     * ]
     *
     * @return array
     */
    public function get_arp_stat(): array
    {
        $this->ensureArp();

        if (empty($this->arp)) {
            return [];
        }

        $result = [
            Mik::F_ARP_TOTAL     => 0,
            Mik::F_ARP_DYNAMIC   => 0,
            Mik::F_ARP_ENABLED   => 0,
            Mik::F_ARP_DISABLED  => 0,
            Mik::F_ARP_PUBLISHED => 0,
            Mik::F_ARP_COMPLETE  => 0,
            Mik::F_ARP_DHCP      => 0,
            Mik::F_ARP_INVALID   => 0,
            Mik::F_ARP_INTERFACES => [],
        ];

        foreach ($this->arp as $row) {

            $result[Mik::F_ARP_TOTAL]++;

            if (mikBool($row[Mik::F_ARP_DYNAMIC] ?? false)) {
                $result[Mik::F_ARP_DYNAMIC]++;
            }

            if (mikBool($row[Mik::F_ARP_DISABLED] ?? false)) {
                $result[Mik::F_ARP_DISABLED]++;
            } else {
                $result[Mik::F_ARP_ENABLED]++;
            }

            if (mikBool($row[Mik::F_ARP_PUBLISHED] ?? false)) {
                $result[Mik::F_ARP_PUBLISHED]++;
            }

            if (mikBool($row[Mik::F_ARP_COMPLETE] ?? false)) {
                $result[Mik::F_ARP_COMPLETE]++;
            }

            if (mikBool($row[Mik::F_ARP_DHCP] ?? false)) {
                $result[Mik::F_ARP_DHCP]++;
            }

            if (mikBool($row[Mik::F_ARP_INVALID] ?? false)) {
                $result[Mik::F_ARP_INVALID]++;
            }

            $iface = trim((string)($row[Mik::F_ARP_INTERFACE] ?? ''));

            if ($iface !== '') {

                if (!isset($result[Mik::F_ARP_INTERFACES][$iface])) {
                    $result[Mik::F_ARP_INTERFACES][$iface] = 0;
                }

                $result[Mik::F_ARP_INTERFACES][$iface]++;
            }
        }

        ksort($result[Mik::F_ARP_INTERFACES], SORT_NATURAL);

        return $result;
    }

    
    
    private function validate_arp_item(array $item): bool
    {
        // базовый критерий (универсальный сейчас)
        if (!($item[Mik::F_ARP_COMPLETE] ?? false)) {
            return false;
        }

        if ($item[Mik::F_ARP_INVALID] ?? false) {
            return false;
        }        
        
        // ROS v7 дополнительная защита
        if ( ($item[Mik::F_ARP_STATUS] ?? null) === 'failed' ) {
            return false;
        }
        
        return true;
    }    
    
    
    
    
    /**
     * Поля возвращаемого массива resolve_arp_items()
     */
    public const F_ARP_RESOLV_STATUS = 'status'; // ARP_STATUS_ERROR | ARP_STATUS_OK_SINGLE | ARP_STATUS_OK_MULTI | ARP_STATUS_NOT_FOUND
    public const F_ARP_RESOLV_FOUND  = 'found';  // Весь список найденных arp записей
    public const F_ARP_RESOLV_ACTIVE = 'active';  // Список активных записей из найденных
    
    /**
     * Значения поля [F_ARP_RESOLV_STATUS]
     */
    public const ARP_STATUS_ERROR = 'ERROR';            // Ошибка запроса к устройству
    public const ARP_STATUS_OK_SINGLE = 'OK_SINGLE';    // Валиднная запись одна
    public const ARP_STATUS_OK_MULTI = 'OK_MULTI';      // Валидных записей несколько
    public const ARP_STATUS_NOT_FOUND = 'NOT_FOUND';    // Валидных записей нет

    
    /**
     * Возвращает массив найденных ARP-записей и выделяет активные записи
     * [
     *      F_ARP_RESOLV_STATUS => ARP_STATUS_ERROR | ARP_STATUS_OK_SINGLE | ARP_STATUS_OK_MULTI | ARP_STATUS_NOT_FOUND
     *      F_ARP_RESOLV_FOUND  => [Полный список]
     *      F_ARP_RESOLV_ACTIVE => [Список активных]
     * ]
     * @param array $search
     * @return array
     */
    public function resolve_arp_items(array $search): array
    {
        $found = $this->find_arp_items($search);

        /**
         * Ошибка запроса
         */
        if ($found === false) {
            return [
                self::F_ARP_RESOLV_STATUS => self::ARP_STATUS_ERROR,
                self::F_ARP_RESOLV_FOUND  => [],
                self::F_ARP_RESOLV_ACTIVE  => [],
            ];
        }        
        
        /**
         * Записи есть.
         * Выбираем валидные
         */
        $valid = [];
        foreach ($found as $item) {
            if ($this->validate_arp_item($item)) {
                $valid[] = $item;
            }
        }
        
        $count_valid = count($valid);
        
        if ($count_valid === 0) {
            return [
                self::F_ARP_RESOLV_STATUS => self::ARP_STATUS_NOT_FOUND,
                self::F_ARP_RESOLV_FOUND  => $found,
                self::F_ARP_RESOLV_ACTIVE  => []
            ];
        }

        if ($count_valid === 1) {
            return [
                self::F_ARP_RESOLV_STATUS => self::ARP_STATUS_OK_SINGLE,
                self::F_ARP_RESOLV_FOUND  => $found,
                self::F_ARP_RESOLV_ACTIVE  => $valid
            ];
        }

        /**
         * MULTIPLE valid entries
         * → потенциально нормальная ситуация для ARP
         */
        return [
            self::F_ARP_RESOLV_STATUS => self::ARP_STATUS_OK_MULTI,
            self::F_ARP_RESOLV_FOUND  => $found,
            self::F_ARP_RESOLV_ACTIVE  => $valid
        ];
    }    
    
    

    /*
     * 
     * Конец блока ARP
     * ========================================================================
     * 
     */



    /*
     * 
     * ========================================================================
     * Начало блока IP ADDRESSES
     * 
     */

    private ?array $ip_address = null;
    
    
    /**
     * Гарантировать, что $this->ip_address загружен и нормализован.
     * Логика:
     * если cache пуст → запрос в MikroTik
     *      normalize
     *      cache store
     * @return void
     */
    private function ensureIpAddress(): void
    {
        if ($this->ip_address !== null) {
            return;
        }

        try {

            $query = new Query('/ip/address/print');
            
            $raw = $this->client
                ->query($query)
                ->read();

            $this->ip_address = array_map(
                fn($row) => self::normalizeIpAddressRow($row),
                $raw
            );
        
        } catch (\Throwable $e) {
            $this->arp = null;
            self::$messages[] = 'ensureArp: ' . __('IP ADDRESS request error | Ошибка получения IP ADDRESS таблицы | Помилка отримання IP ADDRESS таблиці');
            self::$messages[] = $e->getMessage();
        }
    }
    
    
    
    /**
     * Возвращает статистику
     *   Например
     *   [
     *       Mik::F_STAT_TOTAL      => 100,
     *       Mik::F_STAT_ENABLED    => 95,
     *       Mik::F_STAT_DISABLED   => 5,
     *       Mik::F_STAT_DYNAMIC    => 10,
     *       Mik::F_STAT_INVALID    => 1,
     *       Mik::F_STAT_INTERFACES => [
     *           'bridge' => 40,
     *           'vlan10' => 20,
     *           'vlan20' => 40
     *       ]
     *   ]
     * @return array
     */
    public function get_ip_address_stat(): array
    {
        $this->ensureIpAddress();

        $stat = [
            Mik::F_STAT_TOTAL    => 0,
            Mik::F_STAT_ENABLED  => 0,
            Mik::F_STAT_DISABLED => 0,
            Mik::F_STAT_DYNAMIC  => 0,
            Mik::F_STAT_INVALID  => 0,
            Mik::F_STAT_INTERFACES => [],
        ];

        foreach ($this->ip_address as $item) {

            $stat[Mik::F_STAT_TOTAL]++;

            if ($item[Mik::F_ADDR_DISABLED]) {
                $stat[Mik::F_STAT_DISABLED]++;
            } else {
                $stat[Mik::F_STAT_ENABLED]++;
            }

            if (!empty($item[Mik::F_ADDR_DYNAMIC])) {
                $stat[Mik::F_STAT_DYNAMIC]++;
            }

            if (!empty($item[Mik::F_ADDR_INVALID])) {
                $stat[Mik::F_STAT_INVALID]++;
            }

            $if = $item[Mik::F_ADDR_INTERFACE] ?? 'unknown';

            $stat[Mik::F_STAT_INTERFACES][$if] =
                ($stat[Mik::F_STAT_INTERFACES][$if] ?? 0) + 1;
        }

        return $stat;
    }
    


    
    /**
     * Валидация состояния одной записи, НЕ структуры
     * 
     * Возвращает:
     *   true → валидна
     *   false → невалидна
     *   null → “неопределённое состояние / недостаточно данных”
     * Что обычно проверяется:
     * INVALID:
     *   нет address
     *   нет ip
     *   malformed prefix
     *   empty interface
     * DISABLED:
     *   disabled = true -- считать НЕ валидным
     */
    public function validate_address_item(array $item): ?bool
    {
        // обязательные поля отсутствуют
        if (
            !isset($item[Mik::F_ADDR_ADDRESS]) ||
            !isset($item[Mik::F_ADDR_INTERFACE])
        ) {
            return null;
        }

        // пустые значения
        if (
            empty($item[Mik::F_ADDR_ADDRESS]) ||
            empty($item[Mik::F_ADDR_INTERFACE])
        ) {
            return false;
        }

        // MikroTik пометил запись как invalid
        if (!empty($item[Mik::F_ADDR_INVALID])) {
            return false;
        }

        // отключённый адрес считаем невалидным
        if (!empty($item[Mik::F_ADDR_DISABLED])) {
            return false;
        }

        $address = trim((string)$item[Mik::F_ADDR_ADDRESS]);

        // должен быть адрес либо сеть
        if (!validate_ip($address) && !is_ip_net($address)) {
            return false;
        }

        // если есть выделенный IP — проверяем его
        if (
            isset($item[Mik::F_ADDR_IP]) &&
            !empty($item[Mik::F_ADDR_IP]) &&
            !validate_ip($item[Mik::F_ADDR_IP])
        ) {
            return false;
        }

        // проверяем префикс
        if (isset($item[Mik::F_ADDR_PREFIX])) {

            if (!is_numeric($item[Mik::F_ADDR_PREFIX])) {
                return false;
            }

            $prefix = (int)$item[Mik::F_ADDR_PREFIX];

            $is_ipv6 =
                isset($item[Mik::F_ADDR_IP]) &&
                strpos((string)$item[Mik::F_ADDR_IP], ':') !== false;

            if ($is_ipv6) {
                if ($prefix < 0 || $prefix > 128) {
                    return false;
                }
            } else {
                if ($prefix < 0 || $prefix > 32) {
                    return false;
                }
            }
        }

        return true;
    }
    


    public static function normalizeIpAddressRow(?array $item): ?array
    {
        if (empty($item) || !is_array($item)) {
            return null;
        }

        $address = trim((string)($item[Mik::F_ADDR_ADDRESS] ?? ''));
        $ip      = null;
        $prefix  = null;

        if (!empty($address) && str_contains($address, '/')) {

            [$ip, $prefix] = explode('/', $address, 2);

            $ip = trim($ip);

            if (!is_numeric($prefix)) {
                $prefix = null;
            } else {
                $prefix = (int)$prefix;
            }
        } else {
            $ip = $address;
        }

        return [
            Mik::F_ADDR_ID => $item[Mik::F_ADDR_ID] ?? null,
            Mik::F_ADDR_ADDRESS => $address,
            Mik::F_ADDR_IP => $ip,
            Mik::F_ADDR_PREFIX => $prefix,
            Mik::F_ADDR_NETWORK => $item[Mik::F_ADDR_NETWORK] ?? null,
            Mik::F_ADDR_INTERFACE => trim((string)($item[Mik::F_ADDR_INTERFACE] ?? '')),
            Mik::F_ADDR_COMMENT => trim((string)($item[Mik::F_ADDR_COMMENT] ?? '')),
            Mik::F_ADDR_DISABLED => mikBool($item[Mik::F_ADDR_DISABLED] ?? false),
            Mik::F_ADDR_ENA =>  !mikBool($item[Mik::F_ADDR_DISABLED] ?? false),
            Mik::F_ADDR_DYNAMIC => mikBool($item[Mik::F_ADDR_DYNAMIC] ?? false),
            Mik::F_ADDR_INVALID => mikBool($item[Mik::F_ADDR_INVALID] ?? false),
        ];
    }

    
    
    /**
     * Получить список IP-адресов, удовлетворяющих заданным условиям.
     *
     * Все параметры являются необязательными.
     * Если параметр не указан (null), соответствующий фильтр не применяется.
     *
     * Условия поиска:
     *   - id        : точное совпадение внутреннего ID MikroTik
     *   - address   : точное совпадение адреса с префиксом
     *                 (например: 10.10.10.1/24)
     *   - ip        : точное совпадение IP-адреса
     *   - network   : точное совпадение адреса сети
     *   - interface : точное совпадение имени интерфейса
     *   - ena       : состояние записи (true = включена)
     *   - dynamic   : признак динамической записи
     *   - invalid   : признак невалидной записи
     *   - descr     : комментарий начинается с указанной строки
     *                 (без учёта регистра)
     *
     * Возвращает массив нормализованных записей.
     *
     * @return array<int,array>
     */
    public function get_ip_address_items(
        ?string $id = null,
        ?string $address = null,
        ?string $ip = null,
        ?string $network = null,
        ?string $interface = null,
        int|bool|null $ena = null,
        int|bool|null $dynamic = null,
        int|bool|null $invalid = null,
        ?string $descr = null
    ): array
    {
        $this->ensureIpAddress();

        /**
         * коментарий приведённый к одному регистру
         */
        $descr_lc = $descr !== null
            ? mb_strtolower($descr)
            : null;
        
        $result = [];
        foreach ($this->ip_address as $item) {

            if ($id !== null
                && ($item[Mik::F_ADDR_ID] ?? null) !== $id) {
                continue;
            }

            if ($address !== null
                && ($item[Mik::F_ADDR_ADDRESS] ?? null) !== $address) {
                continue;
            }

            if ($ip !== null
                && ($item[Mik::F_ADDR_IP] ?? null) !== $ip) {
                continue;
            }

            if ($network !== null
                && ($item[Mik::F_ADDR_NETWORK] ?? null) !== $network) {
                continue;
            }

            if ($interface !== null
                && ($item[Mik::F_ADDR_INTERFACE] ?? null) !== $interface) {
                continue;
            }

            if ($ena !== null 
                && (bool)$item[Mik::F_ADDR_ENA] !== (bool)$ena) {
                continue;
            }

            if ($dynamic !== null
                && (bool)$dynamic !== (bool)($item[Mik::F_ADDR_DYNAMIC] ?? false)) {
                continue;
            }

            if ($invalid !== null
                && (bool)$invalid !== (bool)($item[Mik::F_ADDR_INVALID] ?? false)) {
                continue;
            }

            if ($descr !== null) {
                $comment = mb_strtolower((string)($item[Mik::F_ADDR_COMMENT] ?? ''));
                if (!str_starts_with($comment, $descr_lc)) {
                    continue;
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    

    /**
     * Проверка корректности установленных фильтров IP-адресов.
     *
     * Выполняет валидацию значений, используемых при фильтрации
     * списка адресов MikroTik.
     *
     * Проверяются:
     *   - IP-адреса;
     *   - адреса с префиксом (CIDR);
     *   - адреса сетей;
     *   - булевы параметры;
     *   - прочие поля фильтра, имеющие ограничения формата.
     *
     * В случае ошибки добавляет описание в self::$errors.
     *
     * @return bool
     *   true  - все фильтры корректны;
     *   false - обнаружены ошибки в параметрах фильтрации.
     */
    public function validate_filter_address(array $search): bool
    {
        $allowed = [
            Mik::F_ADDR_ID,
            Mik::F_ADDR_ADDRESS,
            Mik::F_ADDR_IP,
            Mik::F_ADDR_NETWORK,
            Mik::F_ADDR_INTERFACE,
            Mik::F_ADDR_ENA,
            Mik::F_ADDR_DYNAMIC,
            Mik::F_ADDR_INVALID,
            Mik::F_ADDR_COMMENT,
        ];

        foreach ($search as $field => $value) {

            if (!in_array($field, $allowed, true)) {
                throw new \Exception("validate_filter_ip_address: ".__('unsupported field | неподдерживаемое поле | непідтримуване поле')." '{$field}'");
            }

            switch ($field) {

                case Mik::F_ADDR_ID:

                    if (!is_string($value) || trim($value) === '') {
                        self::$errors[] = "validate_filter_ip_address: " . __('invalid id | недействительный id | недійсний id');
                        return false;
                    }
                    break;

                case Mik::F_ADDR_ADDRESS:

                    if (!is_string($value) || !is_ip_net($value)) {
                        self::$errors[] = "validate_filter_ip_address: ".__('invalid address | недействительный адрес | недійсна адреса')." '{$value}'";
                        return false;
                    }
                    break;

                case Mik::F_ADDR_IP:
                case Mik::F_ADDR_NETWORK:

                    if (!is_string($value) || !validate_ip($value)) {
                        self::$errors[] = "validate_filter_ip_address: ".__('invalid ip | неверный IP-адрес | недійсний ip')." '{$value}'";
                        return false;
                    }
                    break;

                case Mik::F_ADDR_INTERFACE:
                case Mik::F_ADDR_COMMENT:

                    if (!is_string($value)) {
                        self::$errors[] = "validate_filter_ip_address: ".__('invalid string field | недопустимое строковое поле | недійсне поле рядка')." '{$field}'";
                        return false;
                    }
                    break;

                case Mik::F_ADDR_ENA:
                case Mik::F_ADDR_DYNAMIC:
                case Mik::F_ADDR_INVALID:

                    if (
                        !is_bool($value) &&
                        !in_array($value, [0, 1], true)
                    ) {
                        self::$errors[] = "validate_filter_ip_address: ".__('invalid boolean field | недопустимое логическое поле | недійсне логічне поле')." '{$field}'";
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    
    
    /**
     * Поиск IP-адресов по ассоциативному массиву критериев.
     *
     * Поддерживаемые ключи:
     *
     *   Mik::F_ADDR_ID
     *   Mik::F_ADDR_ADDRESS
     *   Mik::F_ADDR_IP
     *   Mik::F_ADDR_NETWORK
     *   Mik::F_ADDR_INTERFACE
     *   Mik::F_ADDR_ENA
     *   Mik::F_ADDR_DYNAMIC
     *   Mik::F_ADDR_INVALID
     *   Mik::F_ADDR_COMMENT
     *
     * Для поля Mik::F_ADDR_COMMENT выполняется поиск по началу строки
     * без учёта регистра.
     *
     * Все условия объединяются по логике AND.
     *
     * Пример:
     *
     * <code>
     * $items = $mik->find_address_items([
     *     Mik::F_ADDR_INTERFACE => 'bridge',
     *     Mik::F_ADDR_ENA       => true,
     * ]);
     * </code>
     *
     * @param array $search Критерии поиска.
     *
     * @return array|false
     *   Массив найденных записей 
     *   либо false, если ошибка параметров.
     */
    public function find_ip_address_items(array $search): array|false
    {
        $search = remove_null_fields($search);
        
        if (!$this->validate_filter_address($search)) {
            return false;
        }
        $result = $this->get_ip_address_items(
            id        : $search[Mik::F_ADDR_ID] ?? null,
            address   : $search[Mik::F_ADDR_ADDRESS] ?? null,
            ip        : $search[Mik::F_ADDR_IP] ?? null,
            network   : $search[Mik::F_ADDR_NETWORK] ?? null,
            interface : $search[Mik::F_ADDR_INTERFACE] ?? null,
            ena       : $search[Mik::F_ADDR_ENA] ?? null,
            dynamic   : $search[Mik::F_ADDR_DYNAMIC] ?? null,
            invalid   : $search[Mik::F_ADDR_INVALID] ?? null,
            descr     : $search[Mik::F_ADDR_COMMENT] ?? null,
        );
        return $result;
    }
    
    
    
    /**
     * Получить запись IP-адреса по внутреннему идентификатору MikroTik.
     *
     * @param string $id
     *   Внутренний идентификатор записи (.id).
     *
     * @return array|null
     *   Нормализованная запись IP-адреса или null,
     *   если запись не найдена.
     */
    public function get_ip_address_item(string $id): array|null {
        return $this->find_ip_address_items([Mik::F_ADDR_ID => $id])[0] ?? null;
    }

    
    
    /**
     * Проверяет наличие IP-адреса в списке адресов MikroTik.
     *
     * Поиск выполняется по нормализованному кэшу адресов,
     * загружаемому методом ensureAddress().
     *
     * Дополнительно может выполняться фильтрация по интерфейсу
     * и состоянию записи (включена/отключена).
     *
     * @param string|null $ip
     *   Проверяемый IP-адрес.
     *
     * @param string|null $interface
     *   Имя интерфейса. Если указано, поиск выполняется только
     *   среди адресов данного интерфейса.
     *
     * @param int|bool|null $ena
     *   Состояние записи:
     *     true  - только включённые адреса;
     *     false - только отключённые адреса;
     *     null  - не учитывать состояние.
     *
     * @return bool|null
     *   true  - адрес найден;
     *   false - адрес не найден;
     *   null  - ошибка входных параметров.
     */    
    public function in_ip_address_item(
        ?string $ip,
        ?string $interface = null,
        int|bool|null $ena = null): ?bool
    {
        if (!validate_ip($ip)) {
            self::$errors[] = 'in_address_item: ' . __('invalid ip address | неверный IP-адрес | недійсна IP-адреса');
            return null;
        }

        $this->ensureIpAddress();

        foreach ($this->ip_address as $item) {

            if (($item[Mik::F_ADDR_IP] ?? null) !== $ip) {
                continue;
            }

            if ($interface !== null && ($item[Mik::F_ADDR_INTERFACE] ?? null) !== $interface) { 
                continue;
            }

            if ($ena !== null && (bool)($item[Mik::F_ADDR_ENA]) !== (bool)$ena) {
                continue;
            }
            return true;
        }
        return false;
    }

    

    /**
     * Проверяет, является ли запись IP-адреса активной.
     *
     * Активной считается запись, которая:
     *   - включена (ENA=true);
     *   - не помечена как INVALID.
     *
     * @param array $item
     *   Нормализованная запись IP-адреса.
     *
     * @return bool
     */
    public static function is_ip_address_active(array $item): bool {
        return $item[Mik::F_ADDR_ENA] && !$item[Mik::F_ADDR_INVALID];
    }
    
    
    
    /**
     * Удаляет запись IP-адреса из устройства MikroTik.
     *
     * Для удаления используется внутренний идентификатор записи (.id).
     *
     * После успешного удаления:
     *   - при $clear_cache = true локальный кэш адресов сбрасывается;
     *   - при $clear_cache = false запись удаляется только из локального кэша.
     *
     * @param string $id
     *   Внутренний идентификатор записи MikroTik (.id).
     *
     * @param bool $clear_cache
     *   Сбрасывать ли весь локальный кэш после удаления.
     *
     * @return bool|null
     *   true  - запись успешно удалена;
     *   false - ошибка удаления, запись не найдена или
     *           запрещено удаление последнего активного адреса;
     *   null  - ошибка взаимодействия с устройством.
     */
    public function remove_ip_address_item(string $id, bool $clear_cache = true): bool|null
    {
        if (empty($id)) {
            self::$messages[] = 'remove_ip_address_item: ' . __('empty id | пустой ID | порожній ID');
            return false;
        }
        
        $this->ensureIpAddress();
        
        $remove_item = $this->get_ip_address_item($id);
        
        if (empty($remove_item)) {
            self::$messages[] = 'remove_ip_address_item: ' . __('There is no record with the specified id | Записи с указанным id нет | Записи із зазначеним id немає');
            return false;
        }
        
        if (self::is_ip_address_active($remove_item)) {
            /**
             * Количество активных ip адресов
             */
            $count_ena = 0;
            foreach ($this->ip_address as $item) {
                if (self::is_ip_address_active($item)) { $count_ena++; }
            }

            /**
             * Нельзя удалять единственный активный IP-адрес.
             */
            if ($count_ena <= 1) {
                self::$messages[] = 'remove_ip_address_item: ' . __('You cannot delete the only active IP address | Нельзя удалять единственный активный ip адрес | Не можна видаляти єдину активну ip адресу');
                return false;
            }
        }
        
        try {

            $query = (new Query('/ip/address/remove'))
                ->equal('numbers', $id);
            $response = $this->client
                ->query($query)
                ->read();
            
            $parsed = self::parse_response($response);

            if ($parsed[self::F_PARSE_SUCCESS]) {

                if ($clear_cache) {
                    $this->ip_address = null;
                } else {
                    if ($this->ip_address !== null) {
                        foreach ($this->ip_address as $k => $row) {
                            if (($row[Mik::F_ADDR_ID] ?? null) === $id) {
                                unset($this->ip_address[$k]);
                                break;
                            }
                        }
                    }
                }
                return true;
            }

            self::$messages[] = 'remove_ip_address_item: ' . __('Error deleting record | Ошибка удаления записи | Помилка видалення запису');
            self::$messages[] = 'message: ' . ($parsed[self::F_PARSE_MESSAGE] ?? '-');
            self::$messages[] = 'category: ' . ($parsed[self::F_PARSE_CATEGORY] ?? '-');
            return false;

        } catch (\Throwable $e) {
            self::$messages[] = 'remove_ip_address_item: ' . __('Device operation error | Ошибка операции с устройством | Помилка операції з пристроєм');
            self::$messages[] = 'id: ' . $id;
            self::$messages[] = $e->getMessage();
            return null;
        }
    }        

    

    /**
     * Поиск интерфейсного IP-адреса по произвольному IP из сети.
     *
     * На вход получает IP-адрес, например:
     *   10.10.10.5
     *
     * Возвращает запись из списка /ip/address, сеть которой
     * содержит указанный IP.
     *
     * Например, для записи:
     *   10.10.10.1/24
     *
     * будут найдены адреса:
     *   10.10.10.1
     *   10.10.10.5
     *   10.10.10.254
     *
     * @param string $ip
     *
     * @return array|null
     *   Найденная запись или null.
     */
    public function find_ip_address_by_ip(string $ip): ?array
    {
        if (!validate_ip($ip)) {
            self::$errors[] = 'find_ip_address_by_ip: ' . __('invalid ip address | неверный IP-адрес | недійсна IP-адреса');
            return null;
        }

        $this->ensureIpAddress();

        $ip_long = ip2long($ip);

        foreach ($this->ip_address as $item) {

            $network = $item[Mik::F_ADDR_NETWORK] ?? null;
            $prefix  = $item[Mik::F_ADDR_PREFIX] ?? null;

            if (
                !validate_ip($network) ||
                !is_numeric($prefix)
            ) {
                // continue;
                throw new \Exception('find_ip_address_by_ip: ' . __('Cache structure violation | Нарушение структуры кэша | Порушення структури кешу') . '<pre>' . print_r($item, true) . '</pre>');
            }

            $mask = -1 << (32 - (int)$prefix);

            if (
                ($ip_long & $mask) ===
                (ip2long($network) & $mask)
            ) {
                return $item;
            }
        }

        return null;
    }
    
    public function resolve_interface_by_ip(string $ip): ?string
    {
        throw new \Exception('Не написана');
    }
    
    
    
    /**
     * Проверка существования сети
     */
    public function in_address_network(string $network): bool
    {
        throw new \Exception('Не написана');
    }
    
    
    /*
     * 
     * Конец блока IP ADDRESSES
     * ========================================================================
     * 
     */

    
    
    /*
     * 
     * ========================================================================
     * начало блока IP DHCP LEASES
     * 
     */


    
    private ?array $dhcp_lease = null;

    

    /**
     * Гарантировать, что $this->dhcp_lease загружен и нормализован.
     *
     * Логика:
     *   если cache пуст:
     *       запросить /ip/dhcp-server/lease/print
     *       нормализовать записи
     *       сохранить в cache
     */
    private function ensureDhcpLease(): void
    {
        if ($this->dhcp_lease !== null) {
            return;
        }

        try {
            $query = new Query('/ip/dhcp-server/lease/print');
            $raw = $this->client
                    ->query($query)
                    ->read();

            $this->dhcp_lease = array_values(
                array_filter(
                    array_map(
                        fn($row) => self::normalizeDhcpLeaseRow($row),
                        $raw
                    ),
                    fn($row) => is_array($row)
                )
            );

        } catch (\Throwable $e) {
            $this->dhcp_lease = null;
            self::$messages[] = 'ensureDhcpLease: ' . __('DHCP lease request error | Ошибка получения DHCP lease | Помилка отримання DHCP lease');
            self::$messages[] = $e->getMessage();
        }
    }
    
    
    
    /**
     * Возвращает статистику DHCP lease.
     * @return array
     */
    public function get_dhcp_lease_stat(): array
    {
        $this->ensureDhcpLease();

        $stat = [
            Mik::F_STAT_TOTAL     => 0,

            Mik::F_STAT_DYNAMIC   => 0,
            Mik::F_STAT_STATIC    => 0,

            Mik::F_STAT_ENABLED   => 0,
            Mik::F_STAT_DISABLED  => 0,
            Mik::F_STAT_BLOCKED   => 0,

            Mik::F_STAT_STATUS    => [],
            Mik::F_STAT_SERVERS   => [],
        ];

        foreach ($this->dhcp_lease as $item) {

            $stat[Mik::F_STAT_TOTAL]++;

            if (!empty($item[Mik::F_DHCP_LEASE_BLOCKED])) {
                $stat[Mik::F_STAT_BLOCKED]++;
            }            
            
            if (!empty($item[Mik::F_DHCP_LEASE_ENA])) {
                $stat[Mik::F_STAT_ENABLED]++;
            } else {
                $stat[Mik::F_STAT_DISABLED]++;
            }

            if (!empty($item[Mik::F_DHCP_LEASE_DYNAMIC])) {
                $stat[Mik::F_STAT_DYNAMIC]++;
            } else {
                $stat[Mik::F_STAT_STATIC]++;
            }

            $status = trim((string)($item[Mik::F_DHCP_LEASE_STATUS] ?? 'unknown'));
            $stat[Mik::F_STAT_STATUS][$status] =
                ($stat[Mik::F_STAT_STATUS][$status] ?? 0) + 1;

            $server = trim((string)($item[Mik::F_DHCP_LEASE_SERVER] ?? 'unknown'));
            $stat[Mik::F_STAT_SERVERS][$server] =
                ($stat[Mik::F_STAT_SERVERS][$server] ?? 0) + 1;
        }
        
        ksort($stat[Mik::F_STAT_STATUS]);
        ksort($stat[Mik::F_STAT_SERVERS]);
        
        return $stat;
    }
    

    
    /**
     * Нормализация записи lease.
     */
    public static function normalizeDhcpLeaseRow(?array $item): ?array
    {
        if (empty($item)) { return null; }

        return [
            Mik::F_DHCP_LEASE_ID                    => $item[Mik::F_DHCP_LEASE_ID] ?? null,
            Mik::F_DHCP_LEASE_ADDRESS               => trim((string)($item[Mik::F_DHCP_LEASE_ADDRESS] ?? '')),
            Mik::F_DHCP_LEASE_MAC                   => normalize_mac($item[Mik::F_DHCP_LEASE_MAC] ?? ''),
            Mik::F_DHCP_LEASE_CLIENT_ID             => trim((string)($item[Mik::F_DHCP_LEASE_CLIENT_ID] ?? '')),
            Mik::F_DHCP_LEASE_DYNAMIC               => mikBool($item[Mik::F_DHCP_LEASE_DYNAMIC] ?? false),
            Mik::F_DHCP_LEASE_BLOCKED               => mikBool($item[Mik::F_DHCP_LEASE_BLOCKED] ?? false),
            Mik::F_DHCP_LEASE_DISABLED              => mikBool($item[Mik::F_DHCP_LEASE_DISABLED] ?? false),
            Mik::F_DHCP_LEASE_ENA                   => !mikBool($item[Mik::F_DHCP_LEASE_DISABLED] ?? false),
            Mik::F_DHCP_LEASE_SERVER                => trim((string)($item[Mik::F_DHCP_LEASE_SERVER] ?? '')),
            Mik::F_DHCP_LEASE_STATUS                => strtolower(trim((string)($item[Mik::F_DHCP_LEASE_STATUS] ?? ''))),
            Mik::F_DHCP_LEASE_HOSTNAME              => trim((string)($item[Mik::F_DHCP_LEASE_HOSTNAME] ?? '')),
            Mik::F_DHCP_LEASE_COMMENT               => trim((string)($item[Mik::F_DHCP_LEASE_COMMENT] ?? '')),
            Mik::F_DHCP_LEASE_ADDRESS_LISTS         => trim((string)($item[Mik::F_DHCP_LEASE_ADDRESS_LISTS] ?? '')),
            Mik::F_DHCP_LEASE_DHCP_OPTION           => trim((string)($item[Mik::F_DHCP_LEASE_DHCP_OPTION] ?? '')),
            Mik::F_DHCP_LEASE_EXPIRES_AFTER         => trim((string)($item[Mik::F_DHCP_LEASE_EXPIRES_AFTER] ?? '')),
            Mik::F_DHCP_LEASE_LAST_SEEN             => trim((string)($item[Mik::F_DHCP_LEASE_LAST_SEEN] ?? '')),
            Mik::F_DHCP_LEASE_ACTIVE_ADDRESS        => trim((string)($item[Mik::F_DHCP_LEASE_ACTIVE_ADDRESS] ?? '')),
            Mik::F_DHCP_LEASE_ACTIVE_MAC_ADDRESS    => normalize_mac($item[Mik::F_DHCP_LEASE_ACTIVE_MAC_ADDRESS] ?? ''),
            Mik::F_DHCP_LEASE_ACTIVE_CLIENT_ID      => trim((string)($item[Mik::F_DHCP_LEASE_ACTIVE_CLIENT_ID] ?? '')),
            Mik::F_DHCP_LEASE_ACTIVE_SERVER         => trim((string)($item[Mik::F_DHCP_LEASE_ACTIVE_SERVER] ?? '')),
            Mik::F_DHCP_LEASE_ALWAYS_BROADCAST      => mikBool($item[Mik::F_DHCP_LEASE_ALWAYS_BROADCAST] ?? false),
            Mik::F_DHCP_LEASE_RADIUS                => mikBool($item[Mik::F_DHCP_LEASE_RADIUS] ?? false),
        ];
    }
    
    
    
    /**
     * Проверка одной записи lease.
     */
    public function validate_dhcp_lease_item(array $item): bool
    {
        if  (
                (
                    !isset($item[Mik::F_DHCP_LEASE_ADDRESS]) ||
                    !isset($item[Mik::F_DHCP_LEASE_MAC]) ||
                    !isset($item[Mik::F_DHCP_LEASE_STATUS])
                ) 
                ||
                (
                    empty($item[Mik::F_DHCP_LEASE_ADDRESS]) ||
                    empty($item[Mik::F_DHCP_LEASE_MAC]) ||
                    empty($item[Mik::F_DHCP_LEASE_STATUS])
                )
            )
        {
            self::$messages[] = 'validate_dhcp_lease_item: ' . __('Error in fields | Ошибка в полях | Помилка у полях') . ': [' . Mik::F_DHCP_LEASE_ADDRESS . '|' . Mik::F_DHCP_LEASE_MAC . '|' . Mik::F_DHCP_LEASE_STATUS . ']';
            return false;
        }

        if (!validate_ip($item[Mik::F_DHCP_LEASE_ADDRESS])) {
            self::$messages[] = 'validate_dhcp_lease_item: ' . __('Error in fields | Ошибка в полях | Помилка у полях') . ': [' . Mik::F_DHCP_LEASE_ADDRESS . ']';
            return false;
        }

        if (!validate_mac($item[Mik::F_DHCP_LEASE_MAC])) {
            self::$messages[] = 'validate_dhcp_lease_item: ' . __('Error in fields | Ошибка в полях | Помилка у полях') . ': [' . Mik::F_DHCP_LEASE_MAC . ']';
            return false;
        }


        if (
            isset($item[Mik::F_DHCP_LEASE_ACTIVE_ADDRESS]) &&
            !empty($item[Mik::F_DHCP_LEASE_ACTIVE_ADDRESS]) &&
            !validate_ip($item[Mik::F_DHCP_LEASE_ACTIVE_ADDRESS])
        ) {
            self::$messages[] = 'validate_dhcp_lease_item: ' . __('Error in fields | Ошибка в полях | Помилка у полях') . ': [' . Mik::F_DHCP_LEASE_ACTIVE_ADDRESS . ']';
            return false;
        }

        if (
            isset($item[Mik::F_DHCP_LEASE_ACTIVE_MAC_ADDRESS]) &&
            !empty($item[Mik::F_DHCP_LEASE_ACTIVE_MAC_ADDRESS]) &&
            !validate_mac($item[Mik::F_DHCP_LEASE_ACTIVE_MAC_ADDRESS])
        ) {
            self::$messages[] = 'validate_dhcp_lease_item: ' . __('Error in fields | Ошибка в полях | Помилка у полях') . ': [' . Mik::F_DHCP_LEASE_ACTIVE_MAC_ADDRESS . ']';
            return false;
        }

        return true;
    }
    
    
    
    /**
     * Получить список lease по фильтрам.
     */
    public function get_dhcp_lease_items(
        ?string $id = null,
        ?string $address = null,
        ?string $mac = null,
        ?string $server = null,
        ?string $status = null,
        int|bool|null $ena = null,
        int|bool|null $dynamic = null,
        int|bool|null $blocked = null,
        ?string $descr = null
    ): array
    {
        $this->ensureDhcpLease();

        $descr_lc = $descr !== null
            ? mb_strtolower(ltrim($descr))
            : null;

        $status_lc = $status !== null
            ? mb_strtolower($status)
            : null;

        $mac = $mac !== null
            ? normalize_mac($mac)
            : null;

        $result = [];

        foreach ($this->dhcp_lease as $item) {

            if (
                $id !== null &&
                ($item[Mik::F_DHCP_LEASE_ID] ?? null) !== $id
            ) {
                continue;
            }

            if (
                $address !== null &&
                ($item[Mik::F_DHCP_LEASE_ADDRESS] ?? null) !== $address
            ) {
                continue;
            }

            if (
                $mac !== null &&
                strtoupper((string)($item[Mik::F_DHCP_LEASE_MAC] ?? '')) !== $mac
            ) {
                continue;
            }

            if (
                $server !== null &&
                ($item[Mik::F_DHCP_LEASE_SERVER] ?? null) !== $server
            ) {
                continue;
            }

            if (
                $status !== null &&
                mb_strtolower((string)($item[Mik::F_DHCP_LEASE_STATUS] ?? '')) !== $status_lc
            ) {
                continue;
            }

            if (
                $ena !== null &&
                (bool)($item[Mik::F_DHCP_LEASE_ENA] ?? false) !== (bool)$ena
            ) {
                continue;
            }

            if (
                $dynamic !== null &&
                (bool)($item[Mik::F_DHCP_LEASE_DYNAMIC] ?? false) !== (bool)$dynamic
            ) {
                continue;
            }

            if (
                $blocked !== null &&
                (bool)($item[Mik::F_DHCP_LEASE_BLOCKED] ?? false) !== (bool)$blocked
            ) {
                continue;
            }

            if ($descr !== null) {

                $comment = mb_strtolower((string)($item[Mik::F_DHCP_LEASE_COMMENT] ?? ''));

                if (!str_starts_with($comment, $descr_lc)) {
                    continue;
                }
            }

            $result[] = $item;
        }

        return $result;
    }
    
    
    /**
     * Проверка фильтров.
     */
    public function validate_filter_dhcp_lease(array $search): bool
    {
        $allowed = [
            Mik::F_DHCP_LEASE_ID,
            Mik::F_DHCP_LEASE_ADDRESS,
            Mik::F_DHCP_LEASE_MAC,
            Mik::F_DHCP_LEASE_SERVER,
            Mik::F_DHCP_LEASE_STATUS,
            Mik::F_DHCP_LEASE_ENA,
            Mik::F_DHCP_LEASE_DYNAMIC,
            Mik::F_DHCP_LEASE_BLOCKED,
            Mik::F_DHCP_LEASE_COMMENT,
        ];

        foreach ($search as $field => $value) {

            if (!in_array($field, $allowed, true)) {
                throw new \Exception("validate_filter_dhcp_lease: " . __('unsupported field | неподдерживаемое поле | непідтримуване поле') . " '{$field}'");
            }

            switch ($field) {

                case Mik::F_DHCP_LEASE_ID:
                    if (!is_string($value) || trim($value) === '') {
                        self::$messages[] = 'validate_filter_dhcp_lease: ' . __('invalid id | недействительный id | недійсний id');
                        return false;
                    }
                    break;

                case Mik::F_DHCP_LEASE_ADDRESS:
                    if (!is_string($value) || !validate_ip($value)) {
                        self::$messages[] = 'validate_filter_dhcp_lease: ' . __('invalid ip address | неверный IP-адрес | недійсна IP-адреса') . " '{$value}'";
                        return false;
                    }
                    break;

                case Mik::F_DHCP_LEASE_MAC:
                    if (!is_string($value) || !validate_mac($value)) {
                        self::$messages[] = 'validate_filter_dhcp_lease: ' . __('invalid mac address | неверный MAC-адрес | недійсна MAC-адреса') . " '{$value}'";
                        return false;
                    }
                    break;

                case Mik::F_DHCP_LEASE_SERVER:
                case Mik::F_DHCP_LEASE_STATUS:
                case Mik::F_DHCP_LEASE_COMMENT:
                    if (!is_string($value)) {
                        self::$messages[] = 'validate_filter_dhcp_lease: ' . __('invalid string field | недопустимое строковое поле | недійсне поле рядка') . " '{$field}'";
                        return false;
                    }
                    break;

                case Mik::F_DHCP_LEASE_ENA:
                case Mik::F_DHCP_LEASE_DYNAMIC:
                case Mik::F_DHCP_LEASE_BLOCKED:
                    if (
                        !is_bool($value)
                        &&
                        !in_array($value, [0, 1], true)
                    ) {
                        self::$messages[] = 'validate_filter_dhcp_lease: ' . __('invalid boolean field | недопустимое логическое поле | недійсне логічне поле') . " '{$field}'";
                        return false;
                    }
                    break;
            }
        }
        return true;
    }    
    
    
    
    /**
     * Поиск lease по массиву критериев.
     */
    public function find_dhcp_lease_items(array $search): array|false
    {
        $search = remove_null_fields($search);

        if (!$this->validate_filter_dhcp_lease($search)) {
            return false;
        }

        return $this->get_dhcp_lease_items(
            id      : $search[Mik::F_DHCP_LEASE_ID] ?? null,
            address : $search[Mik::F_DHCP_LEASE_ADDRESS] ?? null,
            mac     : $search[Mik::F_DHCP_LEASE_MAC] ?? null,
            server  : $search[Mik::F_DHCP_LEASE_SERVER] ?? null,
            status  : $search[Mik::F_DHCP_LEASE_STATUS] ?? null,
            ena     : $search[Mik::F_DHCP_LEASE_ENA] ?? null,
            dynamic : $search[Mik::F_DHCP_LEASE_DYNAMIC] ?? null,
            blocked : $search[Mik::F_DHCP_LEASE_BLOCKED] ?? null,
            descr   : $search[Mik::F_DHCP_LEASE_COMMENT] ?? null,
        );
    }
    
    
    
    
    /**
     * Получить lease по id.
     */
    public function get_dhcp_lease_item(string $id): array
    {
        return $this->find_dhcp_lease_items([
            Mik::F_DHCP_LEASE_ID => $id
        ])[0] ?? [];
    }

    
    
    public function get_dhcp_lease_by_ip(string $ip): array
    {
        return $this->find_dhcp_lease_items([
            Mik::F_DHCP_LEASE_ADDRESS => $ip,
            Mik::F_DHCP_LEASE_ENA => true,
        ])[0] ?? [];
    }

    
    
    public function get_dhcp_lease_by_mac(string $mac): array
    {
        return $this->find_dhcp_lease_items([
            Mik::F_DHCP_LEASE_MAC => $mac,
            Mik::F_DHCP_LEASE_ENA => true,
        ])[0] ?? [];
    }
    
    
    
    /**
     * Проверка существования lease.
     */
    public function in_dhcp_lease_item(
        ?string $ip,
        ?string $mac = null,
        ?string $ena = null
    ): bool|null
    {
        $found = $this->find_dhcp_lease_items([
            Mik::F_DHCP_LEASE_ADDRESS => $ip,
            Mik::F_DHCP_LEASE_MAC => $mac,
            Mik::F_DHCP_LEASE_ENA => $ena,
        ]);
        
        if ($found === null) {
            return null;
        } else {
            return boolval($found);
        }
    }

    
    
    /**
     * Активна ли lease.
     */
    public static function is_dhcp_lease_active(array $item): bool
    {
        return
            !empty($item[Mik::F_DHCP_LEASE_ENA])
            &&
            !empty($item[Mik::F_DHCP_LEASE_STATUS])
            &&
            strtolower((string)($item[Mik::F_DHCP_LEASE_STATUS] ?? null)) === 'bound';
    }
    
    
    /**
     * Удалить lease.
     */
    public function remove_dhcp_lease_item(string $id, bool $clear_cache = false): bool
    {
        $id = trim($id);

        if ($id === '') {
            self::$messages[] = 'remove_dhcp_lease_item: ' . __('record id not specified | id записи не указан | id запису не вказано');
            return false;
        }

        $this->ensureDhcpLease();
        
        $item = $this->get_dhcp_lease_item($id);
        if ($item === null) {
            self::$messages[] = 'remove_dhcp_lease_item: ' . __('record not found | запись не найдена | запис не знайдено');
            return false;
        }

        try {

            $query = (new Query('/ip/dhcp-server/lease/remove'))
                ->equal('numbers', $id);

            $response = $this->client
                ->query($query)
                ->read();

            $parsed = self::parse_response($response);
            if ($parsed[self::F_PARSE_SUCCESS]) {
                if ($clear_cache) {
                    $this->dhcp_lease = null;
                } else {
                    if ($this->dhcp_lease !== null) {
                        foreach ($this->dhcp_lease as $k => $row) {
                            if (($row[Mik::F_DHCP_LEASE_ID] ?? null) === $id) 
                            {
                                unset($this->dhcp_lease[$k]);
                                break;
                            }
                        }
                    }
                }
                return true;
            }

            self::$messages[] = 'remove_dhcp_lease_item: ' . __('Error deleting record | Ошибка удаления записи | Помилка видалення запису');
            self::$messages[] = 'message: ' . ($parsed[self::F_PARSE_MESSAGE] ?? '-');
            self::$messages[] = 'category: ' . ($parsed[self::F_PARSE_CATEGORY] ?? '-');
            return false;

        } catch (\Throwable $e) {

            self::$messages[] = 'remove_dhcp_lease_item: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = 'id: ' . $id;
            self::$messages[] = $e->getMessage();
            return false;
        }
    }    


    /**
     * Сделать запись lease статической
     */
    public function set_dhcp_lease_static(string $id, bool $clear_cache = false): bool
    {
        $id = trim($id);

        if ($id === '') {
            self::$messages[] = 'set_dhcp_lease_static: ' . __('record id not specified | id записи не указан | id запису не вказано');
            return false;
        }

        $this->ensureDhcpLease();

        $item = $this->get_dhcp_lease_item($id);

        if ($item === null) {
            self::$messages[] = 'set_dhcp_lease_static: ' . __('record not found | запись не найдена | запис не знайдено');
            return false;
        }

        if ($item[Mik::F_DHCP_LEASE_DYNAMIC] === false) {
            self::$messages[] = 'set_dhcp_lease_static: ' . __('Запись уже статична');
            return true;
        }

        try {

            $query = (new Query('/ip/dhcp-server/lease/make-static'))
                ->equal('numbers', $id);

            $response = $this->client
                ->query($query)
                ->read();

            $parsed = self::parse_response($response);
            if ($parsed[self::F_PARSE_SUCCESS]) {
                if ($clear_cache) {
                    $this->dhcp_lease = null;
                } else {
                    foreach ($this->dhcp_lease as $k => $row) {
                        if (($row[Mik::F_DHCP_LEASE_ID] ?? null) === $id) {
                            unset($this->dhcp_lease[$k]);
                            break;
                        }
                    }
                }
                return true;
            }

            self::$messages[] = 'set_dhcp_lease_static: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = 'message: ' . ($parsed[self::F_PARSE_MESSAGE] ?? '-');
            self::$messages[] = 'category: ' . ($parsed[self::F_PARSE_CATEGORY] ?? '-');
            return false;

        } catch (\Throwable $e) {

            self::$messages[] = 'set_dhcp_lease_static: ' . __('Operation failed | Ошибка операции | Помилка операції');
            self::$messages[] = 'id: ' . $id;
            self::$messages[] = $e->getMessage();

            return false;
        }
    }


    /**
     * Получить активные lease.
     */
    public function get_dhcp_leases_active(): array
    {
        $this->ensureDhcpLease();
        $result = [];
        foreach ($this->dhcp_lease as $item) {
            if (self::is_dhcp_lease_active($item)) {
                $result[] = $item;
            }
        }
        return $result;
    }
    
    

    /**
     * Получить dynamic lease.
     */
    public function get_dhcp_leases_dynamic(): array
    {
        $this->ensureDhcpLease();
        $result = [];
        foreach ($this->dhcp_lease as $item) {
            if ( $item[Mik::F_DHCP_LEASE_ENA] && $item[Mik::F_DHCP_LEASE_DYNAMIC] ) 
            {
                $result[] = $item;
            }
        }
        return $result;
    }
    
    

    /**
     * Получить static lease.
     */
    public function get_dhcp_leases_static(): array
    {
        $this->ensureDhcpLease();
        $result = [];
        foreach ($this->dhcp_lease as $item) {
            if ( $item[Mik::F_DHCP_LEASE_ENA] && !$item[Mik::F_DHCP_LEASE_DYNAMIC] ) 
            {
                $result[] = $item;
            }
        }
        return $result;
    }
    
    

    public function in_dhcp_leases_ip(string $ip): bool
    {
        $this->ensureDhcpLease();
        $result = $this->find_dhcp_lease_items([
            Mik::F_DHCP_LEASE_ADDRESS => $ip,
            Mik::F_DHCP_LEASE_ENA => true,
        ]);
        return count($result ?? []) > 0;
    }



    /**
     * MAC уже присутствует?
     */
    public function in_dhcp_leases_mac(string $mac): bool
    {
        $this->ensureDhcpLease();
        $mac = normalize_mac($mac);
        $result = $this->find_dhcp_lease_items([
            Mik::F_DHCP_LEASE_MAC => $mac,
            Mik::F_DHCP_LEASE_ENA => true,
        ]);
        return count($result ?? []) > 0;
    }



    /**
     * Есть ли активная lease для MAC.
     */
    public function has_dhcp_lease_active(string $mac): bool
    {
        $this->ensureDhcpLease();
        $mac = normalize_mac($mac);
        $result = [];
        foreach ($this->dhcp_lease as $item) {
            if  (
                    $item[Mik::F_DHCP_LEASE_ENA] 
                    && $item[Mik::F_DHCP_LEASE_MAC] === $mac
                    && self::is_dhcp_lease_active($item)
                )
            {
                $result[] = $item;
            }
        }
        return count($result ?? []) > 0;
    }
    
    
    /**
     * Получить static lease.
     */
    public function has_dhcp_lease_static(string $ip): bool
    {
        $this->ensureDhcpLease();
        $result = [];
        foreach ($this->dhcp_lease as $item) {
            if  (
                    $item[Mik::F_DHCP_LEASE_ENA]
                    && $item[Mik::F_DHCP_LEASE_ADDRESS] === $ip
                    && !$item[Mik::F_DHCP_LEASE_DYNAMIC]
                )
            {
                $result[] = $item;
            }
        }
        return count($result ?? []) > 0;
    }


    
    /*
     * 
     * Конец блока IP DHCP LEASES
     * ========================================================================
     * 
     */
    
    
    
}
