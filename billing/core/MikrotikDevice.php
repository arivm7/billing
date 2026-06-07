<?php






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
    private array $resource = [];
    
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
    private array $gateways = [];
    
    private array $nat_rules = [];

    private array $filter_rules = [];
    
    private array $ip_services = [];
    
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
        return $this->get_address_list_items(self::LIST_DNS, $ip, $ena);
    }
    
    public function set_list_dns(string $ip, int|bool $ena, string $descr): bool {
        return $this->set_address_list_item(self::LIST_DNS, $ip, $ena, $descr);
    }
    
    public function in_list_dns(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_address_list_item(self::LIST_DNS, $ip, $ena);
    }



    public function get_list_hackers(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_address_list_items(self::LIST_HACKERS, $ip, $ena);
    }

    public function set_list_hackers(string $ip, int|bool $ena, string $descr): bool {
        return $this->set_address_list_item(self::LIST_HACKERS, $ip, $ena, $descr);
    }

    public function in_list_hackers(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_address_list_item(self::LIST_HACKERS, $ip, $ena);
    }



    public function get_list_flood(?string $ip = null, int|bool|null $ena = null): array {
        return $this->get_address_list_items(self::LIST_FLOOD, $ip, $ena);
    }

    public function set_list_flood(string $ip, int|bool $ena, string $descr): bool {
        return $this->set_address_list_item(self::LIST_FLOOD, $ip, $ena, $descr);
    }

    public function in_list_flood(?string $ip, int|bool|null $ena = null): bool {
        return $this->in_address_list_item(self::LIST_FLOOD, $ip, $ena);
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
    private function read_mik_resources(): bool
    {
        try {

            $response = $this->client
                ->query(new Query('/system/resource/print'))
                ->read();

            if (empty($response[0])) {
                $this->resource = [];
                self::$messages[] = __('RouterOS returned empty resource response | RouterOS вернул пустой ответ ресурса | RouterOS повернула порожню відповідь ресурсу');
                return false;
            }

            $this->resource = $response[0];

            return true;

        } catch (\Throwable $e) {

            $this->resource = [];
            self::$messages[] = __('Error reading data from device | Ошибка чтения данных из устройства | Помилка читання даних із пристрою');
            self::$messages[] = $e->getMessage();

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

            $this->address_list[$list] = $this->client
                ->query($query)
                ->read();
//            MsgQueue::msg(MsgType::INFO, 'TIMER: in_list_abon: ' . round(microtime(true) - $t, 3) . ' sec');

            if (!is_array($this->address_list[$list])) {
                throw new \RuntimeException('ensureAddressList['.$list.']: ' . __('Invalid RouterOS response | Неверный ответ RouterOS | Недійсна відповідь RouterOS'));
            }

        } catch (\Throwable $e) {
            $this->unset_address_list($list);
            throw new \RuntimeException('ensureAddressList['.$list.']: ' . $e->getMessage(), 0, $e);
        }
    }    
    
    
    
    
    /**
     * Нормализует строку address-list к единому формату.
     * Поддерживает ключи Mik::F_LIST_*
     */
    private static function normalizeAddressListRow(array $row): ?array
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

        return [
            Mik::F_LIST_ID      => $row[Mik::F_LIST_ID] ?? null,
            Mik::F_LIST_LIST    => $list,
            Mik::F_LIST_ADDRESS => $address,
            Mik::F_LIST_DYNAMIC => $row[Mik::F_LIST_DYNAMIC] ?? Mik::OFF,
            Mik::F_LIST_ENABLED => $enabled,
            Mik::F_LIST_COMMENT => $comment,
        ];
    }
    
    
    

    
    /**
     * Возвращает статистику по адресным листам
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
        if ($descr !== null) { $descr = trim($descr); }

        $items = [];

        foreach ($address_list as $rec) {

            if ( $id !== null && $rec[Mik::F_LIST_ID ] !== $id ) { continue; }
            if ( $ip !== null && $rec[Mik::F_LIST_ADDRESS] !== $ip ) { continue; }
            
            if ($ena !== null) {
                $rec_ena = !mikBool($rec[Mik::F_LIST_DISABLED]);
                if ($rec_ena !== $ena) { continue; }
            }

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
    public function remove_address_list_item(
        string $list,
        string $id,
        bool $clear_cache = true
    ): bool
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
        if ($raw_items === []) {
            self::$messages[] = 'sync_address_list_scoped: empty input';
            return false;
        }

        /**
         * 1. GROUP INPUT BY LIST
         * 
         * $desired = [
         *     ['list' => 'ABON', ...],
         *     ['list' => 'DNS', ...],
         * ];        
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
            
            $key =
                $item[Mik::F_LIST_ADDRESS] . "\0" .
                (int)$item[Mik::F_LIST_ENABLED];

            $desired[$list][$key] = $item;
        }

        $lists = array_keys($desired);
        
        /**
         * 2. ГРУППИРОВАТЬ ТЕКУЩЕЕ СОСТОЯНИЕ ПО СПИСКАМ
         */
        $current = [];

        foreach ($lists as $list) {
            
            $this->ensureAddressList($list);
            
            foreach ($this->address_list[$list] as $row) {

                $item = self::normalizeAddressListRow($row);

                if ($item === null) {
                    if ($stop_on_error) {
                        self::$messages[] = 'sync_address_list_scoped: ' . __('After normalization, an empty entry was returned | После нормализации вернулась пустая запись | Після нормалізації повернувся порожній запис');
                        self::$messages[] = 'RAW: ' . print_r($row, true);
                        return false;
                    }
                    continue;
                }

                $key =
                    $item[Mik::F_LIST_ADDRESS] . "\0" .
                    (int)$item[Mik::F_LIST_ENABLED];

                $current[$list][$key] = $item;
            }
        }

        $done = 0;

        /**
         * 3. SCOPED SYNC PER LIST
         */
        foreach ($desired as $list => $desired_items) {

            $current_items = $current[$list] ?? [];

            $to_add = array_diff_key($desired_items, $current_items);
            $to_del = array_diff_key($current_items, $desired_items);

            /**
             * ADD
             */
            foreach ($to_add as $item) {

                $ok = $this->add_address_list_item(
                        list:   $item[Mik::F_LIST_LIST],
                        ip:     $item[Mik::F_LIST_ADDRESS],
                        ena:    $item[Mik::F_LIST_ENABLED],
                        descr:  $item[Mik::F_LIST_COMMENT] ?? '',
                        clear_cache: false
                );

                if ($ok) {
                    $done++;
                } elseif ($stop_on_error) {
                    self::$messages[] = 'sync_address_list_scoped: ' . __('Error adding entry | Ошибка добавления записи | Помилка додавання запису');
                    self::$messages[] = 'ITEM: ' . print_r($item, true);
                    return false;
                }
            }

            /**
             * DELETE
             */
            foreach ($to_del as $item) {
                $ok = $this->remove_address_list_item(
                        list: $item[Mik::F_LIST_LIST],
                        id: $item[Mik::F_LIST_ID],
                        clear_cache: false
                );

                if ($ok) {
                    $done++;
                } elseif ($stop_on_error) {
                    self::$messages[] = 'sync_address_list_scoped: ' . __('Error deleting entry | Ошибка удаления записи | Помилка видалення запису');
                    self::$messages[] = 'ITEM: ' . print_r($item, true);
                    return false;
                }
            }

            /**
             * 4. FINAL CACHE INVALIDATION (safe)
             */
            $this->unset_address_list($list);
        }

        return $done;
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
    
    
    
    private function getNatRules(): array {
        $this->ensureNatRules();
        return $this->nat_rules;
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
        $rules = $this->getNatRules();

        if (empty($rules)) {
            return null;
        }

        return $rules[$position]['.id'] ?? null;
    }    


    
    /*
     * ========================================================================
     * Начало блока ARP
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
    public function remove_arp_item(
        string $id,
        bool $clear_cache = true
    ): bool
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
    public const F_ARP_RESOLV_VALID  = 'valid';  // Список валидных записей из найденных
    
    /**
     * Значения поля [F_ARP_RESOLV_STATUS]
     */
    public const ARP_STATUS_ERROR = 'ERROR';            // Ошибка запроса к устройству
    public const ARP_STATUS_OK_SINGLE = 'OK_SINGLE';    // Валиднная запись одна
    public const ARP_STATUS_OK_MULTI = 'OK_MULTI';      // Валидных записей несколько
    public const ARP_STATUS_NOT_FOUND = 'NOT_FOUND';    // Валидных записей нет

    
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
                self::F_ARP_RESOLV_VALID  => [],
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
                self::F_ARP_RESOLV_VALID  => []
            ];
        }

        if ($count_valid === 1) {
            return [
                self::F_ARP_RESOLV_STATUS => self::ARP_STATUS_OK_SINGLE,
                self::F_ARP_RESOLV_FOUND  => $found,
                self::F_ARP_RESOLV_VALID  => $valid
            ];
        }

        /**
         * MULTIPLE valid entries
         * → потенциально нормальная ситуация для ARP
         */
        return [
            self::F_ARP_RESOLV_STATUS => self::ARP_STATUS_OK_MULTI,
            self::F_ARP_RESOLV_FOUND  => $found,
            self::F_ARP_RESOLV_VALID  => $valid
        ];
    }    
    
    

    /*
     * Конец блока ARP
     * ========================================================================
     */









}

