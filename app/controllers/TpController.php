<?php
/*
 *  Project : my.ri.net.ua
 *  File    : TpController.php
 *  Path    : app/controllers/TpController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 20:53:29
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\models\TpModel;
use app\models\AbonModel;
use billing\core\App;
use billing\core\FWAbonValidator;
use billing\core\MikrotikDevice;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use billing\core\Api;
use PAStatus;
use config\FwInput;
use config\SessionFields;
use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Firm;
use config\tables\Module;
use config\tables\PA;
use config\tables\TP;
use config\tables\TSUserTp;
use config\tables\User;
use config\tables\Abon;
use config\tables\Price;
use config\Mik;
use DataTypes;
use DebugView;
use Valitron\Validator;

/**
 * Контроллер для работы с техническими площадками
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class TpController extends AppBaseController {


    public TpModel $db;


    public function __construct(array $route) {
        parent::__construct($route);
        $this->db = new TpModel();
    }



    function indexAction() {
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }
        
        if (!can_view(Module::MOD_TP)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }
            
        $my = $_SESSION[User::SESSION_USER_REC];
        $tp_list = $this->db->get_tp_list(
                user_id: $my[User::F_ID],
                deleted: 0
        );

        foreach ($tp_list as &$tp_one) {
            $pa_list = $this->db->get_prices_apply_by_tp(tp_id: $tp_one[TP::F_ID], PA_AGE: PAStatus::ACTIVE_TODAY);
            $tp_one[TP::F_COUNT_PA] = count($pa_list);
        }
        unset($tp_one);

        View::setMeta(
                title: __('List of technical units | Список технических узлов | Список технічних вузлів')
            );
        $this->setVariables([
                'tp_list' => $tp_list,
            ]);
    }



    function addAction() {
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!can_add(Module::MOD_TP)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        $tp = [
            TP::F_TITLE => '',
            TP::F_FIRM_ID => 0,
            TP::F_IS_MANAGED => 1,
            TP::F_IP => '',
            TP::F_MIK_IP => '',
            TP::F_MIK_PORT => 8728,
            TP::F_MIK_PORT_SSL => 8729,
            TP::F_MIK_LOGIN => '',
            TP::F_MIK_PASSWD => '',
        ];

        if (!empty($_SESSION[SessionFields::FORM_DATA][TP::POST_REC])) {
            $tp = array_replace($tp, $_SESSION[SessionFields::FORM_DATA][TP::POST_REC]);
            unset($_SESSION[SessionFields::FORM_DATA][TP::POST_REC]);
        }

        $firms = $this->db->getProviderFirmsByUserId(App::get_user_id());

        View::setMeta(title: __('Adding a technical site | Добавление технической площадки | Додавання технічного майданчика'));
        $this->setVariables([
            'tp' => $tp,
            'firms' => $firms,
        ]);
    }


    function createAction() {
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!can_add(Module::MOD_TP)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        if (empty($_POST[TP::POST_REC]) || !is_array($_POST[TP::POST_REC])) {
            MsgQueue::msg(MsgType::ERROR, __('No form data | Нет данных из формы | Немає даних із форми'));
            redirect(TP::URI_ADD);
        }

        $tp = $_POST[TP::POST_REC];
        $this->normalize($tp);

        $title = trim((string) ($tp[TP::F_TITLE] ?? ''));
        if ($title === '') {
            MsgQueue::msg(MsgType::ERROR, __('The title must not be empty | Название должно быть не пустым | Назва має бути не порожньою'));
            $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
            redirect(TP::URI_ADD);
        }

        if (!empty($this->db->getTpByTitle($title))) {
            MsgQueue::msg(MsgType::ERROR, __('A technical site with this name already exists | Техплощадка с таким названием уже существует | Техмайданчик з такою назвою вже існує'));
            $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
            redirect(TP::URI_ADD);
        }

        $firmId = (int) ($tp[TP::F_FIRM_ID] ?? 0);
        if (
            !$this->db->validate_id(Firm::TABLE, $firmId, Firm::F_ID)
            || empty($this->db->getActiveAgentFirmById($firmId))
        ) {
            MsgQueue::msg(MsgType::ERROR, __('Business not found or not available for selection | Предприятие не найдено или недоступно для выбора | Підприємство не знайдено або недоступне для вибору'));
            $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
            redirect(TP::URI_ADD);
        }

        $tp[TP::F_TITLE] = $title;
        $tp[TP::F_ACTIVE] = 1;
        $tp[TP::F_DELETED] = 0;
        $tp[TP::F_RANG_ID] = 2;
        $tp[TP::F_ADMIN_OWNER_ID] = App::get_user_id();
        $tp[TP::F_UPLINK_ID] = null;
        $tp[TP::F_CREATION_UID] = App::get_user_id();
        $tp[TP::F_CREATION_DATE] = time();
        $tp[TP::F_MODIFIED_UID] = App::get_user_id();
        $tp[TP::F_MODIFIED_DATE] = time();

        if ((int) $tp[TP::F_IS_MANAGED] === 1) {
            if (!filter_var($tp[TP::F_IP], FILTER_VALIDATE_IP)) {
                MsgQueue::msg(MsgType::ERROR, __('Incorrect IP address | Не верный IP-адрес | Не вірна IP-адреса'));
                $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
                redirect(TP::URI_ADD);
            }

            if (!filter_var($tp[TP::F_MIK_IP], FILTER_VALIDATE_IP)) {
                MsgQueue::msg(MsgType::ERROR, __('MikroTik IP address is incorrect | Не верный IP-адрес MikroTik | Не вірна IP-адреса MikroTik'));
                $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
                redirect(TP::URI_ADD);
            }

            if ((int) $tp[TP::F_MIK_PORT] < 0 || (string) (int) $tp[TP::F_MIK_PORT] !== (string) $tp[TP::F_MIK_PORT]) {
                MsgQueue::msg(MsgType::ERROR, __('Invalid API TCP port | Не верный API TCP порт | Невірний API TCP порт'));
                $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
                redirect(TP::URI_ADD);
            }

            if ((int) $tp[TP::F_MIK_PORT_SSL] < 0 || (string) (int) $tp[TP::F_MIK_PORT_SSL] !== (string) $tp[TP::F_MIK_PORT_SSL]) {
                MsgQueue::msg(MsgType::ERROR, __('Invalid API SSL port | Не верный API SSL порт | Невірний API SSL порт'));
                $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
                redirect(TP::URI_ADD);
            }

            if (trim((string) $tp[TP::F_MIK_LOGIN]) === '' || trim((string) $tp[TP::F_MIK_PASSWD]) === '') {
                MsgQueue::msg(MsgType::ERROR, __('MikroTik login and password are required | Логин и пароль MikroTik обязательны | Логін та пароль MikroTik обов\'язкові'));
                $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
                redirect(TP::URI_ADD);
            }

            try {
                $dev = new MikrotikDevice(tp: $tp);
                MsgQueue::msg(MsgType::INFO, implode(' | ', $dev->get_description()));
            } catch (\Throwable $e) {
                MsgQueue::msg(MsgType::ERROR, __('No access to device via API | Нет доступа к устройству по API | Немає доступу до пристрою API') . ': ' . $e->getMessage());
                $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
                redirect(TP::URI_ADD);
            }
        }

        $newId = $this->db->insert_row(TP::TABLE, $tp);
        if (!$newId) {
            MsgQueue::msg(MsgType::ERROR, __('Failed to create technical site | Не удалось создать техплощадку | Не вдалося створити техмайданчик'));
            $_SESSION[SessionFields::FORM_DATA][TP::POST_REC] = $tp;
            redirect(TP::URI_ADD);
        }

        MsgQueue::msg(MsgType::SUCCESS, __('Technical site [%s] successfully created | Техплощадка [%s] успешно создана | Техмайданчик [%s] успішно створений', $newId));
        
        $tpUserLink = [
            TSUserTp::F_USER_ID => App::get_user_id(),
            TSUserTp::F_TP_ID => (int) $newId,
            TSUserTp::F_PERCENT_OWNER => 100,
        ];

        if ($this->db->insert_row(TSUserTp::TABLE, $tpUserLink) === false) {
            MsgQueue::msg(MsgType::ERROR, __('The technical site [%s] was created, but it was not possible to bind it to the current user | Техплощадка [%s] создана, но не удалось привязать её к текущему пользователю | Техмайданчик [%s] створений, але не вдалося прив\'язати його до поточного користувача', $newId));
            redirect(TP::URI_EDIT . '/' . $newId);
        }

        MsgQueue::msg(MsgType::SUCCESS, __('The link to the current user has been successfully created | Привязка к текущему пользователю успешно создана | Прив\'язка до поточного користувача успішно створена', $newId));
        redirect(TP::URI_EDIT . '/' . $newId);
    }


    
    /**
     * /tp/fw-input
     * /tp/fw-input?tp_id=123
     * /tp/fw-input?phase=interfaces
     */
    function fwInputAction() {
        
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!can_edit(Module::MOD_TP)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        $phase = trim((string) ($_GET['phase'] ?? FwInput::PHASE_LOGIN));
        if (!FwInput::isValid($phase)) {
            $phase = FwInput::PHASE_LOGIN;
        }

        /**
         * Подключение конфига в реестр
         */
        $config = require DIR_CONFIG . '/config_mik.php';
        App::$app->merge_config($config);
        unset($config);

        $tp_id = intval(($_GET[FwInput::F_GET_TP_ID] ?? 0));
        if ($tp_id > 0) {
            $_POST['fw']['tp_id'] = $tp_id;
            $this->fwInputHandlePhase($phase);
        }

        if ($phase !== FwInput::PHASE_LOGIN && empty($_SESSION[FwInput::SESSION_FIELD])) {
            MsgQueue::msg(MsgType::WARN, __('MikroTik connection parameters have been lost | Параметры подключения к MikroTik потеряны | Параметри підключення до MikroTik втрачені'));
            redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_LOGIN);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->fwInputHandlePhase($phase);
            redirect(TP::URI_FW_INPUT . '?phase=' . $phase);
        }

        $data = $this->fwInputReadPhase($phase);

        View::setMeta(title: __('Firewall input wizard | Мастер firewall input | Майстер firewall input'));
        
        $this->view = FwInput::VIEWS[$phase] ?? FwInput::VIEWS[FwInput::PHASE_LOGIN];
        
        $this->setVariables([
            'phase' => $phase,
            'data' => $data,
        ]);
    }



    function editAction() {
        
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }
        
        if (!can_edit(Module::MOD_TP)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        $tp_id = $this->route[F_ALIAS] ?? 0;
        if (!$tp_id) {
            MsgQueue::msg(MsgType::ERROR, __('Technical site ID not specified | Не указан ID техплощадки | Не вказано ID техмайданчика'));
            redirect();
        }
        
        $my = $_SESSION[User::SESSION_USER_REC];
        $my_tp_list = $this->db->get_my_tp_id_list();

        if (!in_array($tp_id, $my_tp_list)) {
            MsgQueue::msg(MsgType::ERROR, __('Someone else\'s technical site | Чужая техплощадка | Чужий техмайданчик'));
            redirect();
        }
        
        $tp = $this->db->get_tp_raw($tp_id);
        $prices = $this->db->get_prices(tp_id: $tp_id);
        $admin_owner = $this->db->get_user($tp[TP::F_ADMIN_OWNER_ID] ?? 0);
        $uplink = (empty($tp[TP::F_UPLINK_ID]) ? null : $this->db->get_tp($tp[TP::F_UPLINK_ID]));
        $firm = ( $tp[TP::F_FIRM_ID] 
                    ? $this->db->get_row_by_id(table_name: Firm::TABLE, field_id: Firm::F_ID, id_value: $tp[TP::F_FIRM_ID])
                    : 0
                );

        $this->setVariables([
                'prices' => $prices,
                'admin_owner' => $admin_owner,
                'uplink' => $uplink,
                'firm' => $firm,
                'tp' => $tp,
                'ranges_proposed' => $this->db->get_tp_ranges_for_abon_id(),
            ]);

        View::setMeta(title: __('Editing technical site parameters | Редактирование параметров технической площадки | Редагування параметрів технічного майданчика'));

    }


    private function fwInputHandlePhase(string $phase): void
    {
        switch ($phase) {
            case FwInput::PHASE_LOGIN:
                $this->fwInputWizardConnect();
                return;
            case FwInput::PHASE_INTERFACE_LIST:
                $this->fwInputWizardInterfaces();
                return;
            case FwInput::PHASE_NEIGHBORS:
                $this->fwInputWizardNeighbor();
                return;
            case FwInput::PHASE_CERT:
                $this->fwInputWizardCertificate();
                return;
            case FwInput::PHASE_IP_SERVICES:
                $this->fwInputWizardServices();
                return;
            case FwInput::PHASE_FILTERS:
                $this->fwInputWizardFirewall();
                return;
        }
    }


    private function fwInputReadPhase(string $phase): array
    {
        return match ($phase) {
            FwInput::PHASE_LOGIN => $this->fwInputReadConnect(),
            FwInput::PHASE_INTERFACE_LIST => $this->fwInputReadInterfaces(),
            FwInput::PHASE_NEIGHBORS => $this->fwInputReadNeighbor(),
            FwInput::PHASE_CERT => $this->fwInputReadCertificate(),
            FwInput::PHASE_IP_SERVICES => $this->fwInputReadServices(),
            FwInput::PHASE_FILTERS => $this->fwInputReadFirewall(),
            default => [],
        };
    }


    private function fwInputWizardConnect(): bool
    {
        unset($_SESSION[FwInput::SESSION_FIELD]);
        
//        debug($_POST, '$_POST', die: 1);
        
        $form = $_POST['fw'] ?? [];
        if (!is_array($form) || empty($form)) {
            MsgQueue::msg(MsgType::ERROR, __('No form data | Нет данных из формы | Немає даних із форми'));
            return false;
        }

        $form = [
            'tp_id' => (int) ($form['tp_id'] ?? App::get_config('fw_input_def_tp_id')),
            'host' => trim((string) ($form['host'] ?? App::get_config('fw_input_def_host'))),
            'port' => (int) ($form['port'] ?? App::get_config('fw_input_def_port')),
            'ssl' => empty($form['ssl']) ? 0 : 1,
            'login' => trim((string) ($form['login'] ?? App::get_config('fw_input_def_login'))),
            'password' => (string) ($form['password'] ?? App::get_config('fw_input_def_password')),
        ];

        $_SESSION[SessionFields::FORM_DATA][FwInput::SESSION_FIELD] = $form;

        if (!empty($form['tp_id'])) {
            
            if (!$this->db->validate_id_tp($form['tp_id'])) {
                MsgQueue::msg(MsgType::ERROR, __('Incorrect technical site ID | Не верный ID техплощадки | Не вірний ID техмайданчика'));
                return false;
            }
            
            if (!$this->db->is_my_tp($form['tp_id'])) {
                MsgQueue::msg(MsgType::ERROR, __('Someone else\'s technical site | Чужая техплощадка | Чужий техмайданчик'));
                return false;
            }
            
            $tp = $this->db->get_tp($form['tp_id']);
            if (empty($tp)) {
                MsgQueue::msg(MsgType::ERROR, __('Error reading technical site data | Ошибка чтения данных техплощадки | Помилка читання даних техмайданчика'));
                return false;
            }

            $form['host'] = trim((string) ($tp[TP::F_MIK_IP] ?? App::get_config('fw_input_def_host')));
            if (!empty($tp[TP::F_MIK_PORT])) {
                $form['port'] = (int) ($tp[TP::F_MIK_PORT]);
                $form['ssl'] = 0;
            } elseif (!empty($tp[TP::F_MIK_PORT_SSL])) {
                $form['port'] = (int) ($tp[TP::F_MIK_PORT_SSL]);
                $form['ssl'] = 1;
            } else {
                MsgQueue::msg(MsgType::ERROR, '<span class="text-danger">'.__('Critical error in technical site parameters | Критическая ошибка в параметрах техплощадки | Критична помилка у параметрах техмайданчика').':</span> '.__('Device connection parameters are not specified | Не указаны параметры подключения к устройству | Не вказано параметри підключення до пристрою').'');
                return false;
            }
            $form['login'] = trim((string) ($tp[TP::F_MIK_LOGIN] ?? App::get_config('fw_input_def_login')));
            $form['password'] = (string) ($tp[TP::F_MIK_PASSWD] ?? App::get_config('fw_input_def_password'));
        }

        if (!validate_ip($form['host'])) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect IP address | Не верный IP-адрес | Невірна IP-адреса'));
            return false;
        }

        if ($form['port'] <= 0 || $form['port'] > 65535) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect MikroTik API port | Не верный API-порт MikroTik | Невірний API-порт MikroTik') . ' ['.$form['port'].']');
            return false;
        }

        if ($form['login'] === '' || $form['password'] === '') {
            MsgQueue::msg(MsgType::ERROR, __('MikroTik login and password are required | Логин и пароль MikroTik обязательны | Логін та пароль MikroTik обов’язкові'));
            return false;
        }

        try {
            $dev = new MikrotikDevice(
                    tp: [
                            TP::F_MIK_IP        => $form['host'],
                            TP::F_MIK_LOGIN     => $form['login'],
                            TP::F_MIK_PASSWD    => $form['password'],
                            TP::F_MIK_PORT      => ($form['ssl'] ? '' : $form['port']),
                            TP::F_MIK_PORT_SSL  => ($form['ssl'] ? $form['port'] : ''),
                        ],
                    ssl: (bool)$form['ssl']
                );
        } catch (\Throwable $exc) {
            MsgQueue::msg(MsgType::ERROR, __('Error connecting to MikroTik device via API | Ошибка подключения к устройству MikroTik по API | Помилка підключення до пристрою MikroTik API'));
            MsgQueue::msg(MsgType::ERROR, $exc->getMessage());
            return false;
        }

        $descr = $dev->get_description();

        if (empty($descr)) {
            MsgQueue::msg(MsgType::ERROR, __('Connected, but failed to read device information | Подключение выполнено, но не удалось прочитать данные устройства | Підключення виконано, але не вдалося прочитати дані пристрою'));
            return false;
        }

        
        $_SESSION[FwInput::SESSION_FIELD] = [
            'host' => $form['host'],
            'port' => $form['port'],
            'ssl' => (bool) $form['ssl'],
            'login' => $form['login'],
            'password' => $form['password'],
            'tp_id' => $form['tp_id'],
        ];

        unset($_SESSION[SessionFields::FORM_DATA][FwInput::SESSION_FIELD]);

        MsgQueue::msg(MsgType::SUCCESS, __('Successfully connected to the device | Успешно подключились к устройству | Успішно підключилися до пристрою'));
        MsgQueue::msg(MsgType::SUCCESS, implode('|', $descr));

        redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::next(FwInput::PHASE_LOGIN));
    }


    private function fwInputWizardInterfaces(): void
    {
        $connector = $this->fwInputGetConnector();
        if (!$connector) {
            return;
        }

        $cfgLists = App::get_config('interface_lists');
        $lanName = (string) ($cfgLists['lan'] ?? 'LAN');
        $wanName = (string) ($cfgLists['wan'] ?? 'WAN');

        $lists = $connector->get_interface_lists();
        $members = $connector->get_interface_list_members();
        $interfaces = $connector->get_interfaces();

        if (!is_array($lists) || !is_array($members) || !is_array($interfaces)) {
            MsgQueue::msg(MsgType::ERROR, __('Failed to read interface data | Не удалось прочитать данные интерфейсов | Не вдалося прочитати дані інтерфейсів'));
            return;
        }

        $byName = [];
        foreach ($lists as $row) {
            if (($row['dynamic'] ?? 'false') === 'true') {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $byName[$name] = $row;
            }
        }

        if (!isset($byName[$lanName])) {
            $connector->interface_list_add($lanName);
            MsgQueue::msg(MsgType::SUCCESS, __('The LAN interface list has been created | Список интерфейсов LAN создан | Список інтерфейсів LAN створено'));
        }

        if (!isset($byName[$wanName])) {
            $connector->interface_list_add($wanName);
            MsgQueue::msg(MsgType::SUCCESS, __('The WAN interface list has been created | Список интерфейсов WAN создан | Список інтерфейсів WAN створено'));
        }

        $lists = $connector->get_interface_lists();
        $members = $connector->get_interface_list_members();

        $targetLists = [$lanName, $wanName];
        foreach ($members as $member) {
            $listName = trim((string) ($member['list'] ?? ''));
            if (!in_array($listName, $targetLists, true)) {
                continue;
            }
            if (!empty($member['.id'])) {
                $connector->interface_list_member_remove((string) $member['.id']);
            }
        }

        $assignments = $_POST['iflist'] ?? [];
        if (!is_array($assignments)) {
            $assignments = [];
        }

        $addedLan = 0;
        $addedWan = 0;
        foreach ($interfaces as $iface) {
            $name = trim((string) ($iface['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $selection = trim((string) ($assignments[$name] ?? ''));
            if ($selection !== $lanName && $selection !== $wanName) {
                continue;
            }

            $connector->interface_list_member_add($name, $selection);

            if ($selection === $lanName) {
                $addedLan++;
            } elseif ($selection === $wanName) {
                $addedWan++;
            }
        }

        MsgQueue::msg(
            MsgType::SUCCESS,
            __('The interface lists have been updated | Списки интерфейсов обновлены | Списки інтерфейсів оновлено')
            . ': '
            . $lanName . '=' . $addedLan . ', '
            . $wanName . '=' . $addedWan
        );
    }


    private function fwInputWizardNeighbor(): void
    {
        $connector = $this->fwInputGetConnector();
        if (!$connector) {
            return;
        }

        $cfgLists = App::get_config('interface_lists');
        $lanName = (string) ($cfgLists['lan'] ?? 'LAN');
        $wanName = (string) ($cfgLists['wan'] ?? 'WAN');

        $lists = $connector->get_interface_lists();
        $listNames = [];
        foreach (($lists ?: []) as $row) {
            if (($row['dynamic'] ?? 'false') === 'true') {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $listNames[$name] = true;
            }
        }

        if (!isset($listNames[$lanName], $listNames[$wanName])) {
            MsgQueue::msg(MsgType::ERROR, __('The required LAN/WAN interface lists are missing | Отсутствуют требуемые списки интерфейсов LAN/WAN | Відсутні потрібні списки інтерфейсів LAN/WAN'));
            redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_INTERFACE_LIST);
        }

        $discoverList = trim((string) ($_POST['discover_interface_list'] ?? App::get_config('neighbor_discovery_default')));
        $allowedValues = [$lanName, $wanName, 'all'];
        if (!in_array($discoverList, $allowedValues, true)) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect discover-interface-list value | Не верное значение discover-interface-list | Невірне значення discover-interface-list'));
            return;
        }

        $connector->set_neighbor_discovery_interface_list($discoverList);

        MsgQueue::msg(
            MsgType::SUCCESS,
            __('Neighbor discovery settings have been updated | Настройки neighbor discovery обновлены | Налаштування neighbor discovery оновлено')
            . ': '
            . $discoverList
        );
    }


    private function fwInputWizardCertificate(): bool
    {
        $dev = $this->fwInputConnectDevice();
        if (!$dev) {
            return false;
        }

        $cfg = App::get_config('certificate');
        $certName = (string) $cfg['name'];

        $certs = $dev->get_certificates();
        $services = $dev->get_services();
        $validCertificates = $this->fwInputGetValidCertificateNames($certs, $cfg);
        
        $currentCert = $this->fwInputFindCertificateByName($certs, $certName);
        $assignedServices = $this->fwInputGetServicesUsingCertificate($services, $certName);

        if (!empty($validCertificates)) {
            MsgQueue::msg(MsgType::SUCCESS, __('The certificate is already valid | Сертификат уже валиден | Сертифікат уже валідний') . ': ' . $certName);
            return true;
        }

        if ($currentCert && !empty($assignedServices)) {
            MsgQueue::msg(
                MsgType::ERROR,
                __('The certificate is assigned to services and cannot be replaced automatically | Сертификат назначен сервисам и не может быть автоматически заменён | Сертифікат призначений сервісам і не може бути автоматично замінений')
                . ': '
                . implode(', ', $assignedServices)
            );
            return false;
        }

        if ($currentCert && $this->fwInputCertificateNeedsRecreate($currentCert, $cfg)) {
            $certId = (string) ($currentCert['.id'] ?? '');
            if ($certId !== '') {
                if ($dev->del_certificate($certId)) {
                    MsgQueue::msg(MsgType::INFO, __('The existing certificate has been removed before recreation | Имеющийся сертификат удалён перед пересозданием | Наявний сертифікат видалено перед перевідтворенням') . ': ' . $certName);
                    $currentCert = null;
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Failed to delete certificate | Не удалось удалить сертификат | Неможливо видалити сертифікат') . ': ' . $certName);
                    return false;
                }
            }
        }

        if (!$currentCert) {
            if ($dev->add_certificate(
                    name: $certName, 
                    key_size: (int) $cfg['key_size'],
                    key_usage: (string) $cfg['key_usage'], 
                    trusted: 'yes', 
                    days_valid: (int) $cfg['days_valid'], 
                    country: (string) $cfg['country'], 
                    state: (string) $cfg['state'], 
                    locality: (string) $cfg['locality'], 
                    organization: (string) $cfg['organization'],
                    unit: (string) $cfg['unit']))
            {
                MsgQueue::msg(MsgType::INFO, __('A new certificate has been created | Создан новый сертификат | Створено новий сертифікат') . ': ' . $certName);
            } else {
                MsgQueue::msg(MsgType::ERROR, __('Ошибка создания сертификата') . ': ' . $certName);
                MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages);
            }
        }

        sleep((int)$cfg['pre_sign_sleep']);
        
        $dev->certificate_sign($certName);
        
        sleep((int)$cfg['verify_sleep']);

        for ($i = 0; $i < (int) $cfg['sign_poll_tries']; $i++) {
            $certs = $dev->get_certificate($certName);
            $currentCert = $this->fwInputFindCertificateByName($certs, $certName);
            if (($currentCert['status'] ?? '') !== 'signing') {
                break;
            }
            sleep((int) $cfg['sign_poll_sleep']);
        }

        $certs = $dev->get_certificates();
        $validCertificates = $this->fwInputGetValidCertificateNames($certs, $cfg);
        $currentCert = $this->fwInputFindCertificateByName($certs, $certName);

        if (!empty($validCertificates)) {
            MsgQueue::msg(MsgType::SUCCESS, __('The certificate was created and signed successfully | Сертификат успешно создан и подписан | Сертифікат успішно створений і підписаний') . ': ' . $certName);
            return true;
        }

        MsgQueue::msg(MsgType::ERROR, __('Failed to create or validate the certificate | Не удалось создать или проверить сертификат | Не вдалося створити або перевірити сертифікат') . ': ' . $certName);
        return false;
    }


    private function fwInputWizardServices(): void
    {
        $dev = $this->fwInputConnectDevice();
        
        if (!$dev) {
            return;
        }

        $certCfg = App::get_config('certificate');
        $certName = (string) ($certCfg['name'] ?? 'cert1');
        $certs = $dev->get_certificates();
        $validCertificates = $this->fwInputGetValidCertificateNames($certs, $certCfg);
        if (empty($validCertificates)) {
            MsgQueue::msg(MsgType::ERROR, __('A valid certificate is required before configuring services | Перед настройкой сервисов требуется валидный сертификат | Перед налаштуванням сервісів потрібен валідний сертифікат'));
            redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_CERT);
        }

        $defaults = App::get_config('services');
        $services = $dev->get_services();
        $form = $_POST['svc'] ?? [];
        if (!is_array($form)) {
            MsgQueue::msg(MsgType::ERROR, __('No form data | Нет данных из формы | Немає даних із форми'));
            return;
        }

        $serviceRows = $this->fwInputBuildValidationServiceRows($services, $defaults, $form, $certName);
        $validation = $this->fwInputValidateServiceRows($serviceRows);
        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                MsgQueue::msg(MsgType::ERROR, $error);
            }
            return;
        }

        foreach ($services as $service) {
            $name = trim((string) ($service['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $row = $form[$name] ?? [];
            $serviceId = (string) ($service['.id'] ?? '');
            $port = (int) ($row['port'] ?? 0);
            $enabled = !empty($row['ena']) ? 1 : 0;
            $setData = [
                '.id' => $serviceId,
                'port' => $port,
            ];

            if (in_array($name, App::get_config('services_ssl'), true)) {
                $selectedCert = trim((string) ($row['certificate'] ?? $validCertificates[0]));
                if ($selectedCert === '') {
                    $selectedCert = $validCertificates[0];
                }
                $setData['certificate'] = $selectedCert;
            }

            $dev->ip_service_set($serviceId, $port, $setData['certificate'] ?? null);
            if ($enabled) {
                $dev->ip_service_enable($serviceId);
            } else {
                $dev->ip_service_disable($serviceId);
            }
        }

        MsgQueue::msg(MsgType::SUCCESS, __('IP services settings have been updated | Настройки IP services обновлены | Налаштування IP services оновлено'));
        MsgQueue::msg(MsgType::SUCCESS, '<span class="text-danger">'.__('IMPORTANT | ВАЖНО | ВАЖЛИВО').':</span> ' 
                . __('On the next page you need to correct the list of allowed ports | На следующей странице нужно обязательно исправить список разрешённых портов | На наступній сторінці потрібно обов\'язково виправити список дозволених портів') 
                . ': [ <span class="text-warning">FW 06 ACCEPT TCP</span> ]');
    }


    private function fwInputWizardFirewall(): void
    {
        $dev = $this->fwInputConnectDevice();
        if (!$dev) {
            return;
        }

        $cfgLists = App::get_config('interface_lists');
        $lanName = (string) ($cfgLists['lan']);
        $wanName = (string) ($cfgLists['wan']);

        $lists = $dev->get_interface_lists(dynamic: false);
        $listNames = [];
        foreach ($lists as $row) {
//            if (mikBool($row['dynamic'])) { continue; }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $listNames[$name] = true;
            }
        }

        if (!isset($listNames[$lanName], $listNames[$wanName])) {
            MsgQueue::msg(MsgType::ERROR, __('The required LAN/WAN interface lists are missing | Отсутствуют требуемые списки интерфейсов LAN/WAN | Відсутні потрібні списки інтерфейсів LAN/WAN'));
            redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_INTERFACE_LIST);
        }

        $action = trim((string) ($_POST['fwf_action'] ?? ''));
        $post = $_POST['fwf'] ?? [];
        if (!is_array($post)) {
            $post = [];
        }

        if ($action === 'delete') {
            $deleted = 0;
            foreach (($post['delete'] ?? []) as $row) {
                $ruleId = trim((string) ($row['.id'] ?? ''));
                if ($ruleId === '' || empty($row['checked'])) {
                    continue;
                }
                $dev->filter_remove($ruleId);
                $deleted++;
            }
            if ($deleted > 0) {
                MsgQueue::msg(MsgType::SUCCESS, __('Firewall rules deleted | Правила firewall удалены | Правила firewall видалені') . ': ' . $deleted);
            } else {
                MsgQueue::msg(MsgType::INFO, __('No firewall rules selected for deletion | Не выбраны правила firewall для удаления | Не вибрані правила firewall для видалення'));
            }
            return;
        }

        $services = $dev->get_services();
        // $currentRules = $dev->get_filer_input();
        $fwCfg = App::get_config('fw_input');
        $allowedTcpExtra = trim((string) ($post['allowed_tcp_extra'] ?? $this->fwInputConfigPortsToString($fwCfg['allowed_tcp_extra'] ?? [])));
        $allowedUdpExtra = trim((string) ($post['allowed_udp_extra'] ?? $this->fwInputConfigPortsToString($fwCfg['allowed_udp_extra'] ?? [])));
        $proposedRules = $this->fwInputBuildProposedRules($services, $allowedTcpExtra, $allowedUdpExtra, $wanName);

        if (empty($proposedRules['meta']['tcp_ports'])) {
            MsgQueue::msg(MsgType::ERROR, __('The resulting allowed TCP port list is empty | Итоговый список разрешённых TCP-портов пуст | Підсумковий список дозволених TCP-портів порожній'));
            return;
        }
        if (empty($proposedRules['meta']['has_management_port'])) {
            MsgQueue::msg(MsgType::ERROR, __('The allowed TCP port list must contain at least one management port | В списке разрешённых TCP-портов должен быть хотя бы один порт управления | У списку дозволених TCP-портів має бути хоча б один порт керування'));
            return;
        }

        if ($action !== 'add') {
            MsgQueue::msg(MsgType::INFO, __('No firewall changes were selected | Не выбрано действий для firewall | Не вибрано дій для firewall'));
            return;
        }

        $added = 0;
        foreach (($post['add'] ?? []) as $code => $checked) {
            if (empty($checked)) { //  || empty($proposedRules['rules'][$code])
                continue;
            }
            $rule = $proposedRules['rules'][$code];
            
            // Сверяет $rule с уже имеющимися правилами и ищет там похожее
            // if ($this->fwInputHasEquivalentRule($currentRules, $rule)) {
            //     continue;
            // }
            $dev->filter_add($rule);
            $added++;
        }
        if ($added > 0) {
            MsgQueue::msg(MsgType::SUCCESS, __('Firewall rules added | Правила firewall добавлены | Правила firewall додані') . ': ' . $added);
        } else {
            MsgQueue::msg(MsgType::INFO, __('No firewall changes were selected | Не выбрано действий для firewall | Не вибрано дій для firewall'));
        }
    }


    private function fwInputReadConnect(): array
    {
        $form = $_SESSION[SessionFields::FORM_DATA][FwInput::SESSION_FIELD] ?? [
            'tp_id'     => App::get_config('fw_input_def_tp_id'),
            'host'      => App::get_config('fw_input_def_host'),
            'port'      => App::get_config('fw_input_def_port'),
            'ssl'       => App::get_config('fw_input_def_ssl'),
            'login'     => App::get_config('fw_input_def_login'),
            'password'  => App::get_config('fw_input_def_password'),
        ];

        return [
            'title' => __('Select a device or enter connection details | Выберите устройство или введите данные для подключения | Виберіть пристрій або введіть дані для підключення'),
            'tp_list' => $this->db->get_my_tp_list(active: 1, is_managed: 1),
            'form' => $form,
            'session' => $_SESSION[FwInput::SESSION_FIELD] ?? [],
        ];
    }


    private function fwInputReadInterfaces(): array
    {
        $dev = $this->fwInputConnectDevice();
        if (!$dev) {
            MsgQueue::msg(MsgType::ERROR, __('Unable to connect to the device | Не удаось подключиться к устройству | Не вдалося підключитися до пристрою'));
            if (MikrotikDevice::$messages) { MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages); }
            redirect(TP::URI_FW_INPUT . '?'.FwInput::F_GET_PHASE.'=' . FwInput::PHASE_LOGIN);
        }

        $cfgLists = App::get_config('interface_lists');
        $lanName = (string) ($cfgLists['lan'] ?? 'LAN');
        $wanName = (string) ($cfgLists['wan'] ?? 'WAN');

        $lists = $dev->get_interface_lists(dynamic: false);
        $members = $dev->get_interface_list_members();
        $interfaces = $dev->get_interfaces();

        $listNames = [];
        foreach (($lists ?: []) as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $listNames[$name] = true;
            }
        }

        $membership = [];
        foreach (($members ?: []) as $member) {
            $ifaceName = trim((string) ($member['interface'] ?? ''));
            $listName = trim((string) ($member['list'] ?? ''));
            
            if ($ifaceName === '' || $listName === '') {
                throw new \RuntimeException(
                        'Invalid /interface/list/member: '.__('Empty fields [interface] or [list] | Пустые поля [interface] или [list] | Порожні поля [interface] або [list]').'.<br>'. CR 
                        .'Row:<pre>' . print_r($member, true).'</pre>'
                );
            }
    
            if (!isset($membership[$ifaceName])) {
                $membership[$ifaceName] = [];
            }
            $membership[$ifaceName][$listName] = true;
        }

        $ifaceRows = [];
        $conflicts = [];
        foreach ($interfaces as $iface) {
            $name = trim((string) ($iface['name'] ?? ''));
            
            if ($name === '') {
                throw new \RuntimeException(
                        __('Interface without name | Интерфейс без имени | Інтерфейс без імені').':.<br>'. CR 
                        .'Row:<pre>' . print_r($iface, true).'</pre>'
                );
            }
            
            $inLan = !empty($membership[$name][$lanName]);
            $inWan = !empty($membership[$name][$wanName]);
            if ($inLan && $inWan) {
                $conflicts[] = $name;
            }

            $selected = '--';
            if ($inLan && !$inWan) {
                $selected = $lanName;
            } elseif ($inWan && !$inLan) {
                $selected = $wanName;
            }

            $ifaceRows[] = [
                'name' => $name,
                'type' => (string) ($iface['type'] ?? ''),
                'running' => (string) ($iface['running'] ?? ''),
                'disabled' => (string) ($iface['disabled'] ?? ''),
                'selected' => $selected,
            ];
        }

        return [
            'title' => $dev->get_hostname(),
            'description' => implode('|', $dev->get_description()),
            'session' => $_SESSION[FwInput::SESSION_FIELD] ?? [],
            'lists' => array_keys($listNames),
            'interfaces' => $ifaceRows,
            'lan_name' => $lanName,
            'wan_name' => $wanName,
            'has_required_lists' => isset($listNames[$lanName], $listNames[$wanName]),
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
        ];
    }


    private function fwInputGetConnector(): MikrotikDevice|false
    {
        return $this->fwInputConnectDevice();
    }

    

    private function fwInputConnectDevice(): MikrotikDevice|false
    {
        $session = $_SESSION[FwInput::SESSION_FIELD] ?? [];
        
        $host = trim((string) ($session['host'] ?? ''));
        $ssl = !empty($session['ssl']);
        $port = (int) ($session['port'] ?? 0);
        $login = trim((string) ($session['login'] ?? ''));
        $password = (string) ($session['password'] ?? '');
        
        if ($host === '' || $port <= 0 || $port > 65535 || $login === '' || $password === '') {
            MsgQueue::msg(MsgType::ERROR, __('The MikroTik connection session is incomplete | Сессия подключения к MikroTik не полная | Сесія підключення до MikroTik не повна'));
            redirect(TP::URI_FW_INPUT . '?'.FwInput::F_GET_PHASE.'=' . FwInput::PHASE_LOGIN);
        }

        $tp_rec = [
            TP::F_MIK_IP        => $host,
            TP::F_MIK_PORT      => ($ssl ? '' : $port),
            TP::F_MIK_PORT_SSL  => ($ssl ? $port : ''),
            TP::F_MIK_LOGIN     => $login,
            TP::F_MIK_PASSWD    => $password,
        ];

        $dev = new MikrotikDevice(tp: $tp_rec, ssl: $ssl);

        if (!$dev) {
            MsgQueue::msg(MsgType::ERROR, __('No access to device via API | Нет доступа к устройству по API | Немає доступу до пристрою API'));
            foreach (MikrotikDevice::$messages as $error) {
                MsgQueue::msg(MsgType::ERROR, $error);
            }
            redirect(TP::URI_FW_INPUT . '?'.FwInput::F_GET_PHASE.'=' . FwInput::PHASE_LOGIN);
        }

        return $dev;
    }

    

    private function fwInputReadNeighbor(): array
    {
        $dev = $this->fwInputConnectDevice();
        if (!$dev) {
            MsgQueue::msg(MsgType::ERROR, __('Unable to connect to the device | Не удаось подключиться к устройству | Не вдалося підключитися до пристрою'));
            if (MikrotikDevice::$messages) { MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages); }
            redirect(TP::URI_FW_INPUT . '?'.FwInput::F_GET_PHASE.'=' . FwInput::PHASE_LOGIN);
        }

        $cfgLists = App::get_config('interface_lists');
        $lanName = (string) ($cfgLists['lan'] ?? 'LAN');
        $wanName = (string) ($cfgLists['wan'] ?? 'WAN');

        $lists = $dev->get_interface_lists(dynamic: false);
        $listNames = [];
        foreach (($lists ?: []) as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $listNames[$name] = true;
            }
        }

        $current = $dev->get_neighbor_discovery_settings();
        $currentRow = is_array($current) ? ($current[array_key_first($current)] ?? []) : [];
        $currentValue = trim((string) ($currentRow['discover-interface-list'] ?? ''));

        return [
            'title' => $dev->get_hostname(),
            'description' => implode('|', $dev->get_description()),
            'session' => $_SESSION[FwInput::SESSION_FIELD] ?? [],
            'neighbor_default' => App::get_config('neighbor_discovery_default'),
            'lan_name' => $lanName,
            'wan_name' => $wanName,
            'has_required_lists' => isset($listNames[$lanName], $listNames[$wanName]),
            'current' => $currentRow,
            'current_value' => $currentValue,
        ];
    }


    private function fwInputReadCertificate(): array
    {
        $dev = $this->fwInputConnectDevice();
        $cfg = App::get_config('certificate');
        $certName = (string) ($cfg['name'] ?? 'cert1');
        
        if (!$dev) {
            MsgQueue::msg(MsgType::ERROR, __('Unable to connect to the device | Не удаось подключиться к устройству | Не вдалося підключитися до пристрою'));
            if (MikrotikDevice::$messages) { MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages); }
            redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_LOGIN);
        }

        $certs = $dev->get_certificates();
        $services = $dev->get_services();
        $currentCert = $this->fwInputFindCertificateByName($certs, $certName);
        $validCertificates = $this->fwInputGetValidCertificateNames($certs, $cfg);

        return [
            'title' => $dev->get_hostname(),
            'description' => implode('|', $dev->get_description()),
            'session' => $_SESSION[FwInput::SESSION_FIELD] ?? [],
            'certificate' => $cfg,
            'cert_name' => $certName,
            'certs' => $certs,
            'current_cert' => $currentCert,
            'is_valid' => $currentCert ? $this->fwInputIsCertificateValid($currentCert, $cfg) : false,
            'assigned_services' => $this->fwInputGetServicesUsingCertificate($services, $certName),
            'any_valid_cert' => !empty($validCertificates),
            'valid_certificates' => $validCertificates,
        ];
    }


    private function fwInputReadServices(): array
    {
        $dev = $this->fwInputConnectDevice();
        $certCfg = App::get_config('certificate');
        $certName = (string) ($certCfg['name']);
        $defaults = App::get_config('services');

        if (!$dev) {
            MsgQueue::msg(MsgType::ERROR, __('Unable to connect to the device | Не удаось подключиться к устройству | Не вдалося підключитися до пристрою'));
            if (MikrotikDevice::$messages) { MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages); }
            redirect(TP::URI_FW_INPUT . '?phase=' . FwInput::PHASE_LOGIN);
        }

        $certs = $dev->get_certificates();
        $validCertificates = $this->fwInputGetValidCertificateNames($certs, $certCfg);

        $services = $dev->get_services();
        $serviceRows = [];
        foreach ($services as $service) {
            $name = trim((string) ($service['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $serviceRows[] = [
                'id' => (string) ($service['.id'] ?? ''),
                'name' => $name,
                'current_port' => (string) ($service['port'] ?? ''),
                'port' => (int) ($service['port'] ?? ($defaults[$name]['port'] ?? 0)),
                'enabled' => (($service['disabled'] ?? 'false') !== 'true'),
                'certificate' => trim((string) ($service['certificate'] ?? ($service['tls-certificate'] ?? ($service['certificate-name'] ?? ($validCertificates[0] ?? $certName))))),
            ];
        }
        usort($serviceRows, 
                static function (array $a, array $b): int { return strnatcmp($a['name'], $b['name']); }
            );        
        
        $validation = $this->fwInputValidateServiceRows(
            $this->fwInputBuildValidationServiceRows($services, $defaults, [], $certName)
        );

        return [
            'title' => $dev->get_hostname(),
            'description' => implode('|', $dev->get_description()),
            'session' => $_SESSION[FwInput::SESSION_FIELD] ?? [],
            'services' => $defaults,
            'service_rows' => $serviceRows,
            'valid_certificates' => $validCertificates,
            'cert_required_ok' => !empty($validCertificates),
            'cert_name' => $certName,
            'services_valid' => $validation['valid'],
            'services_errors' => $validation['errors'],
        ];
    }


    private function fwInputReadFirewall(): array
    {
        $dev = $this->fwInputConnectDevice();
        $cfgLists = App::get_config('interface_lists');
        $wanName = (string) ($cfgLists['wan'] ?? 'WAN');
        $fwCfg = App::get_config('fw_input');

        if (!$dev) {
            MsgQueue::msg(MsgType::ERROR, __('Unable to connect to the device | Не удаось подключиться к устройству | Не вдалося підключитися до пристрою'));
            if (MikrotikDevice::$messages) { MsgQueue::msg(MsgType::ERROR, MikrotikDevice::$messages); }
            redirect(TP::URI_FW_INPUT . '?'.FwInput::F_GET_PHASE.'=' . FwInput::PHASE_LOGIN);
        }

        $lists = $dev->get_interface_lists(dynamic: false);
        $listNames = [];
        foreach ($lists as $row) {
            if (($row['dynamic'] ?? 'false') === 'true') {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $listNames[$name] = true;
            }
        }

        $currentRules = $dev->get_filer_input();
        $services = $dev->get_services();
        $allowedTcpExtra = $this->fwInputConfigPortsToString($fwCfg['allowed_tcp_extra'] ?? []);
        $allowedUdpExtra = $this->fwInputConfigPortsToString($fwCfg['allowed_udp_extra'] ?? []);
        $proposed = $this->fwInputBuildProposedRules($services, $allowedTcpExtra, $allowedUdpExtra, $wanName);

        return [
            'title' => $dev->get_hostname(),
            'description' => implode('|', $dev->get_description()),
            'session' => $_SESSION[FwInput::SESSION_FIELD] ?? [],
            'fw_input' => $fwCfg,
            'current_rules' => $currentRules,
            'proposed_rules' => $proposed['rules'],
            'allowed_tcp_extra' => $allowedTcpExtra,
            'allowed_udp_extra' => $allowedUdpExtra,
            'has_required_lists' => isset($listNames[$cfgLists['lan'] ?? 'LAN'], $listNames[$wanName]),
            'meta' => $proposed['meta'],
        ];
    }


    private function fwInputFindCertificateByName(array $certs, string $name): ?array
    {
        foreach ($certs as $cert) {
            if (trim((string) ($cert['name'] ?? '')) === $name) {
                return $cert;
            }
        }
        return null;
    }


    private function fwInputGetValidCertificateNames(array $certs, array $cfg): array
    {
        $valid = [];
        foreach ($certs as $cert) {
            if ($this->fwInputIsCertificateValid($cert, $cfg)) {
                $name = trim((string) ($cert['name'] ?? ''));
                if ($name !== '') {
                    $valid[$name] = $name;
                }
            }
        }
        return array_values($valid);
    }


    private function fwInputGetServicesUsingCertificate(array $services, string $certName): array
    {
        $usedBy = [];
        foreach ($services as $service) {
            $serviceName = trim((string) ($service['name'] ?? ''));
            $certValue = trim((string) ($service['certificate'] ?? ''));
            if ($certValue === '') {
                $certValue = trim((string) ($service['tls-certificate'] ?? ''));
            }
            if ($certValue === '') {
                $certValue = trim((string) ($service['certificate-name'] ?? ''));
            }
            if ($serviceName !== '' && $certValue === $certName) {
                $usedBy[] = $serviceName;
            }
        }
        return $usedBy;
    }


    private function fwInputIsCertificateValid(array $cert, array $cfg): bool
    {
        // not signed
        if (!MikrotikDevice::is_certificate_signed($cert)) 
        {
            MsgQueue::msg(MsgType::ERROR,  $cert['name'] . ': НЕ подписан');
            return false;
        }
        
        if (!mikBool($cert['trusted'] ?? '')) {
            MsgQueue::msg(MsgType::ERROR, '$cert[trusted] !== true');
            return false;
        }
        
        if (mikBool($cert['invalid'] ?? '')) {
            MsgQueue::msg(MsgType::ERROR, '$cert[invalid] === true');
            return false;
        }
        
        // отозван
        if (mikBool($cert['revoked'] ?? '')) {
            MsgQueue::msg(MsgType::ERROR, '$cert[revoked] === true');
            return false;
        }

        $status = strtolower(trim((string) ($cert['status'] ?? '')));
        if ($status === 'expired' || $status === 'signing') {
            MsgQueue::msg(MsgType::ERROR, '$cert[status] === [' . $status . ']');
            return false;
        }

        if (!$this->fwInputCertificateUsageMatchesConfig($cert, $cfg)) {
            MsgQueue::msg(MsgType::ERROR, 'fwInputCertificateUsageMatchesConfig: [' . $cert['key-usage'] . '] != [' . $cfg['key_usage'] . ']');
            return false;
        }

//        if (($cfg['country'] ?? null) !== null && trim((string) ($cert['country'] ?? '')) !== (string) $cfg['country']) {
//            return false;
//        }
//        if (($cfg['state'] ?? null) !== null && trim((string) ($cert['state'] ?? '')) !== (string) $cfg['state']) {
//            return false;
//        }
//        if (($cfg['locality'] ?? null) !== null && trim((string) ($cert['locality'] ?? '')) !== (string) $cfg['locality']) {
//            return false;
//        }
//        if (($cfg['organization'] ?? null) !== null && trim((string) ($cert['organization'] ?? '')) !== (string) $cfg['organization']) {
//            return false;
//        }

        return true;
    }


    private function fwInputCertificateNeedsRecreate(array $cert, array $cfg): bool
    {
        if (mikBool($cert['invalid'] ?? '')) {
            return true;
        }
        
        // отозван
        if (mikBool($cert['revoked'] ?? '')) {
            return true;
        }
        
        if (!mikBool($cert['trusted'] ?? '')) {
            return true;
        }

        if (!$this->fwInputCertificateUsageMatchesConfig($cert, $cfg)) {
            return true;
        }

//        if (($cfg['country'] ?? null) !== null && trim((string) ($cert['country'] ?? '')) !== (string) $cfg['country']) {
//            return true;
//        }
//        if (($cfg['state'] ?? null) !== null && trim((string) ($cert['state'] ?? '')) !== (string) $cfg['state']) {
//            return true;
//        }
//        if (($cfg['locality'] ?? null) !== null && trim((string) ($cert['locality'] ?? '')) !== (string) $cfg['locality']) {
//            return true;
//        }
//        if (($cfg['organization'] ?? null) !== null && trim((string) ($cert['organization'] ?? '')) !== (string) $cfg['organization']) {
//            return true;
//        }

        return false;
    }


    private function fwInputCertificateUsageMatchesConfig(array $cert, array $cfg): bool
    {
        $actual = $this->fwInputNormalizeCsvValues((string) ($cert['key-usage'] ?? ''));
        $expected = $this->fwInputNormalizeCsvValues((string) ($cfg['key_usage'] ?? ''));

        if (empty($expected)) {
            return true;
        }

        foreach ($expected as $item) {
            if (!in_array($item, $actual, true)) {
                return false;
            }
        }

        return true;
    }


    private function fwInputNormalizeCsvValues(string $value): array
    {
        $parts = array_filter(array_map(
            static fn(string $item): string => strtolower(trim($item)),
            explode(',', $value)
        ));
        $parts = array_values(array_unique($parts));
        sort($parts, SORT_STRING);
        return $parts;
    }


    private function fwInputValidateServiceRows(array $serviceRows): array
    {
        $enabledPorts = [];
        $hasManagementService = false;
        $errors = [];
        $managementServices = ['www', 'www-ssl', 'winbox', 'ssh'];

        foreach ($serviceRows as $row) {
            $serviceName = (string) $row['name'];
            $port = (int) $row['port'];
            $enabled = !empty($row['enabled']);

            if ($port <= 0) {
                $errors[] = __('Incorrect service port | Не верный порт сервиса | Невірний порт сервісу') . ': ' . $serviceName . ':' . $port;
                continue;
            }

            if ($enabled) {
                if (isset($enabledPorts[$port])) {
//                    debug($serviceRows, '$serviceRows', die: 1);
                    $errors[] = __('Enabled service ports must be unique | Порты включённых сервисов должны быть уникальны | Порти увімкнених сервісів мають бути унікальні') 
                            . ':'.$port.' ' . $enabledPorts[$port] . ' - ' . $serviceName;
                } else {
                    $enabledPorts[$port] = $serviceName;
                }

                if (in_array($serviceName, $managementServices, true)) {
                    $hasManagementService = true;
                }
            }
        }

        if (!$hasManagementService) {
            $errors[] = __('At least one manual management service must remain enabled | Хотя бы один сервис ручного управления должен оставаться включённым | Хоча б один сервіс ручного керування має залишатися увімкненим');
        }

        return [
            'valid' => empty($errors),
            'errors' => array_values(array_unique($errors)),
        ];
    }


    private function fwInputBuildValidationServiceRows(
        array $services,
        array $defaults,
        array $form,
        string $certName
    ): array
    {
        $rows = [];
        foreach ($services as $service) {
            $name = trim((string) ($service['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            if (isset($defaults[$name])) {
                $row = $form[$name] ?? [];
                $rows[] = [
                    'name' => $name,
                    'port' => (int) ($row['port'] ?? ($service['port'] ?? 0)),
                    'enabled' => array_key_exists($name, $form)
                        ? !empty($row['ena'])
                        : (($service['disabled'] ?? 'false') !== 'true'),
                    'certificate' => trim((string) ($row['certificate'] ?? $certName)),
                ];
                continue;
            }

            $rows[] = [
                'name' => $name,
                'port' => (int) ($service['port'] ?? 0),
                'enabled' => (($service['disabled'] ?? 'false') !== 'true'),
                'certificate' => trim((string) ($service['certificate'] ?? ($service['tls-certificate'] ?? ($service['certificate-name'] ?? $certName)))),
            ];
        }

        return $rows;
    }


    private function fwInputConfigPortsToString(array|string $value): string
    {
        if (is_array($value)) {
            return implode(',', array_filter(array_map('trim', array_map('strval', $value))));
        }
        return trim((string) $value);
    }


    private function fwInputParsePortSpec(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $items = [];
        foreach (explode(',', $value) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $items[$item] = $item;
        }
        return array_values($items);
    }


    
    private function fwInputPlaceBefore(string $fwKey, bool $hasUdpAccept): ?string
    {
        $map = [
            'fw01' => '02',
            'fw02' => '03',
            'fw03' => '04',
            'fw04' => '05',
            'fw05' => '06',
            'fw06' => '07',
            'fw07' => $hasUdpAccept ? '08' : '09',
            'fw08' => '09',
            'fw09' => null,
        ];

        $next = $map[$fwKey] ?? null;

        if ($next === null) {
            return null;
        }

        $dev = $this->fwInputConnectDevice();
        if (!$dev) {
            throw new Exception('Не удалось восстановить сессию подключения к устройству');
        }
        $id = $dev->get_filter_id('FW ' . $next);
        return $id;
    }    
    
    
    
    private function fwInputBuildProposedRules(array $services, string $allowedTcpExtra, string $allowedUdpExtra, string $wanName): array
    {
        $fwCfg = App::get_config('fw_input');
        $enabledServicePorts = [];
        $managementPorts = [];
        foreach ($services as $service) {
            if (mikBool($service['disabled'])) {
                continue;
            }
            $name = trim((string) ($service['name'] ?? ''));
            $port = trim((string) ($service['port'] ?? ''));
            if (!empty($port)) {
                $enabledServicePorts[$port] = $port;
                if (in_array($name, ['www', 'www-ssl', 'winbox', 'ssh'], true)) {
                    $managementPorts[$port] = $port;
                }
            }
        }

        foreach ($this->fwInputParsePortSpec($allowedTcpExtra) as $port) {
            $enabledServicePorts[$port] = $port;
        }
        $tcpPorts = array_values($enabledServicePorts);
        sort($tcpPorts, SORT_NATURAL);

        $udpPorts = $this->fwInputParsePortSpec($allowedUdpExtra);
        sort($udpPorts, SORT_NATURAL);

        $tcpPortString = implode(',', $tcpPorts);
        $udpPortString = implode(',', $udpPorts);
        $hasManagementPort = false;
        foreach ($managementPorts as $port) {
            if (in_array($port, $tcpPorts, true)) {
                $hasManagementPort = true;
                break;
            }
        }

        $rules = [
            'fw01' => [
                'chain' => 'input',
                'action' => 'accept',
                'connection-state' => 'established,related,untracked',
                'comment' => 'FW 01 ACCEPT ESTABLISHED',
            ],
            'fw02' => [
                'chain' => 'input',
                'action' => 'drop',
                'connection-state' => 'invalid',
                'comment' => 'FW 02 DROP INVALID',
            ],
            'fw03' => [
                'chain' => 'input',
                'action' => 'drop',
                'in-interface-list' => $wanName,
                'src-address-list' => 'HACKERS',
                'comment' => 'FW 03 DROP HACKERS',
            ],
            'fw04' => [
                'chain' => 'input',
                'action' => 'add-src-to-address-list',
                'address-list' => '_ping_from_WAN',
                'address-list-timeout' => (string) ($fwCfg['ping_timeout'] ?? '3d'),
                'in-interface-list' => $wanName,
                'protocol' => 'icmp',
                'comment' => 'FW 04 REGISTER _ping_from_WAN',
            ],
            'fw05' => [
                'chain' => 'input',
                'action' => 'accept',
                'protocol' => 'icmp',
                'comment' => 'FW 05 ACCEPT ICMP',
            ],
            'fw06' => [
                'chain' => 'input',
                'action' => 'accept',
                'protocol' => 'tcp',
                'in-interface-list' => $wanName,
                'dst-port' => $tcpPortString,
                'comment' => 'FW 06 ACCEPT TCP',
            ],
            'fw07' => [
                'chain' => 'input',
                'action' => 'drop',
                'protocol' => 'tcp',
                'in-interface-list' => $wanName,
                'comment' => 'FW 07 DROP TCP',
            ],
            'fw09' => [
                'chain' => 'input',
                'action' => 'drop',
                'protocol' => 'udp',
                'in-interface-list' => $wanName,
                'comment' => 'FW 09 DROP UDP',
            ],
        ];

        if ($udpPortString !== '') {
            $rules['fw08'] = [
                'chain' => 'input',
                'action' => 'accept',
                'protocol' => 'udp',
                'in-interface-list' => $wanName,
                'dst-port' => $udpPortString,
                'comment' => 'FW 08 ACCEPT UDP',
            ];
        }

        $hasUdpAccept = isset($rules['fw08']);
        foreach ($rules as $fwKey => &$rule) {
            $placeBefore = $this->fwInputPlaceBefore($fwKey, $hasUdpAccept);
            if ($placeBefore !== null) {
                $rule['place-before'] = $placeBefore;
            }
        }
        unset($rule);
        
        return [
            'rules' => $rules,
            'meta' => [
                'tcp_ports' => $tcpPorts,
                'udp_ports' => $udpPorts,
                'has_management_port' => $hasManagementPort,
            ],
        ];
    }


    private function fwInputNormalizeRuleForCompare(array $rule): array
    {
        return [
            'chain' => trim((string) ($rule['chain'] ?? '')),
            'action' => trim((string) ($rule['action'] ?? '')),
            'protocol' => trim((string) ($rule['protocol'] ?? '')),
            'dst-port' => trim((string) ($rule['dst-port'] ?? '')),
            'connection-state' => trim((string) ($rule['connection-state'] ?? '')),
            'in-interface-list' => trim((string) ($rule['in-interface-list'] ?? '')),
            'src-address-list' => trim((string) ($rule['src-address-list'] ?? '')),
            'address-list' => trim((string) ($rule['address-list'] ?? '')),
            'address-list-timeout' => trim((string) ($rule['address-list-timeout'] ?? '')),
            'disabled' => trim((string) ($rule['disabled'] ?? 'false')),
        ];
    }


    private function fwInputHasEquivalentRule(array $currentRules, array $proposedRule): bool
    {
        $needle = $this->fwInputNormalizeRuleForCompare($proposedRule);
        foreach ($currentRules as $rule) {
            if ($this->fwInputNormalizeRuleForCompare($rule) === $needle) {
                return true;
            }
        }
        return false;
    }



    function saveAction() {
        if (App::$auth->isAuth) {
            if (can_edit(Module::MOD_TP)) {
                $tp_id = $this->route[F_ALIAS] ?? 0;
                if ($tp_id) {
                    $my = App::get_user();
                    $my_tp_list = $this->db->get_my_tp_id_list();
                    if (in_array($tp_id, $my_tp_list)) {
                        $tp = $_POST[TP::POST_REC];
                        $tp[TP::F_ID] = $tp_id;
                        $this->normalize($tp);
                        if (!$this->validate($tp)) {
                            MsgQueue::msg(MsgType::ERROR, __('Incorrect data | Не корректные данные | Не коректні дані'));
                            $_SESSION[SessionFields::FORM_DATA] = $tp;
                            redirect();
                        }
//                        debug($tp, '$tp', debug_view: DebugView::PRINTR);
//                        debug($tp, '$tp', debug_view: DebugView::DUMP, die: 1);
                        if ($this->db->update_row_by_id(table: TP::TABLE, field_id: TP::F_ID, row: $tp)) {
                            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Data entered successfully | Данные успешно внесены | Дані успішно внесені'));
                            redirect(TP::URI_EDIT . '/' . $tp_id);
                        } else {
                            MsgQueue::msg(MsgType::ERROR, $this->db->errorInfo());
                            $_SESSION[SessionFields::FORM_DATA] = $tp;
                            redirect(TP::URI_EDIT . '/' . $tp_id);
                        }
                    } else {
                        MsgQueue::msg(MsgType::ERROR, __('Alien technical site | Чужая техническяая площадка | Чужий технічний майданчик'));
                        redirect();
                    }
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Technical site ID not specified | Не указан ID технической площадки | Не вказано ID технічного майданчика'));
                    redirect();
                }
            } else {
                MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
                self::log_no_rights();
                redirect();
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }
    }



    /**
     * Синхронизирует ACL-таблицу с сетевым устройством MikroTik.
     *
     * Получает ID технической площадки из роута ({@see F_ALIAS})
     * и ID ACL-таблицы из GET-параметра ({@see TP::F_GET_ACL_LIST}),
     * загружает соответствующие записи из БД и отправляет их на устройство.
     *
     * Требует авторизации и прав {@see Module::MOD_SECURITY}:
     * {@see can_add()} + {@see can_del()}.
     *
     * Результат каждого этапа записывается в {@see MsgQueue}:
     * - успешное подключение и описание устройства — SUCCESS
     * - синхронизация завершена — SUCCESS с количеством записей
     * - ошибки синхронизации — ERROR с детализацией из {@see MikrotikDevice::$messages}
     *
     * Ошибки на отдельных строках не прерывают синхронизацию (stop_on_error: false).
     *
     * URL возврата после выполнения скрипта можно указать в ?TP::F_REF='url'
     * 
     * Завершается {@see redirect()} во всех случаях.
     *
     * @return void
     *
     * @throws void исключения перехватываются внутри, пишутся в MsgQueue как ERROR
     */
    function aclSyncAction() {
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!can_add(Module::MOD_SECURITY) || !can_del(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        /**
         * URL возврата после выполнения скрипта
         */
        $ref = ($_GET[TP::F_GET_REF] ?? false);
        
        $tp_id = (int) ($this->route[F_ALIAS] ?? 0);
        if (!$this->db->validate_id_tp($tp_id)) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect technical site ID | Не верный ID технической площадки | Не вірний ID технічного майданчика'));
            redirect($ref);
        }

        $aclTableId = (int) ($_GET[TP::F_GET_ACL_LIST] ?? 0);
        if (!$this->db->validate_id(DevAclTable::TABLE, $aclTableId, DevAclTable::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid ACL table ID | Не верный ID ACL-таблицы | Невірний ID ACL-таблиці'));
            redirect($ref);
        }

        $tp = $this->db->get_tp($tp_id);
        $aclTable = $this->db->getAclTableById($aclTableId);
        if (empty($aclTable)) {
            MsgQueue::msg(MsgType::ERROR, __('ACL table not found | ACL-таблица не найдена | ACL-таблиця не знайдена') . ': ' . $aclTableId);
            redirect($ref);
        }

        $aclRows = $this->db->getAclListForSync(
            aclTableId: (int) $aclTable[DevAclTable::F_ID],
            tpId: $tp_id
        );

        if (empty($aclRows)) {
            MsgQueue::msg(MsgType::INFO, __('No entries to sync | Нет записей для синхронизации | Немає записів для синхронізації') . ': ' . $aclTable[DevAclTable::F_NAME]);
            redirect($ref); // TP::URI_EDIT . '/' . $tp_id
        }

        $syncRows = [];
        foreach ($aclRows as $row) {
            $syncRows[] = [
                Mik::F_LIST_LIST    => $aclTable[DevAclTable::F_NAME],
                Mik::F_LIST_ADDRESS => $row[DevAclList::F_ADDRESS],
                Mik::F_LIST_COMMENT => $row[DevAclList::F_COMMENT] ?? '',
                Mik::F_LIST_ENABLED => $row[DevAclList::F_ENABLED] ?? true,
            ];
        }

        try {
            $device = new MikrotikDevice(tp: $tp);
            MsgQueue::msg(MsgType::SUCCESS, __('Successfully connected to the device | Успешно подключились к устройству | Успішно підключилися до пристрою') . ' [' . $tp[TP::F_TITLE] . ']');
            MsgQueue::msg(MsgType::SUCCESS, implode(' | ', $device->get_description()));
            $result = $device->sync_address_list_scoped(
                    raw_items: $syncRows, 
                    stop_on_error: false);

            if ($result === false) {
                MsgQueue::msg(MsgType::ERROR, __('Synchronization completed with errors | Синхронизация завершилась с ошибками | Синхронізація завершилася з помилками') . ': ' . $aclTable[DevAclTable::F_NAME]);
                foreach (MikrotikDevice::$messages ?? [] as $error) {
                    MsgQueue::msg(MsgType::ERROR, $error);
                }
            } else {
                MsgQueue::msg(MsgType::SUCCESS, __('ACL table successfully synchronized | ACL-таблица успешно синхронизирована | ACL-таблиця успішно синхронізована') . ': ' 
                        . $aclTable[DevAclTable::F_NAME]
                        . ' | ' . $result . ' ' . __('records | записей | записів'));
            }
        } catch (\Throwable $e) {
            MsgQueue::msg(MsgType::ERROR, __('ACL synchronization error | Ошибка синхронизации ACL | Помилка синхронізації ACL'));
            MsgQueue::msg(MsgType::ERROR, $e->getMessage());
        }

        redirect($ref); // TP::URI_EDIT . '/' . $tp_id
    }



    /**
     * Синхронизирует все ACL-таблицы (из конфига {@see App::get_config('acl_sync_all_tables')})
     * с выбранным устройством MikroTik.
     *
     * Если GET-параметр {@see TP::F_GET_ACL_SYNC_TP} содержит валидный ID технической площадки,
     * выполняет синхронизацию: загружает записи по каждой таблице из конфига,
     * формирует общий список и передаёт его в {@see MikrotikDevice::sync_address_list_scoped()}.
     *
     * Если параметр не передан или невалиден — синхронизация не выполняется,
     * отображается страница со списком площадок для выбора.
     *
     * Результат каждого этапа фиксируется в {@see MsgQueue}:
     * - количество записей к синхронизации — SUCCESS
     * - успешное подключение и описание устройства — SUCCESS
     * - итог синхронизации — SUCCESS с количеством изменённых записей
     * - ошибки — ERROR с детализацией из {@see MikrotikDevice::$messages}
     *
     * Ошибки на отдельных строках не прерывают синхронизацию (stop_on_error: false).
     *
     * Передаёт во View:
     * - `tp_list`     — список активных управляемых площадок текущего пользователя
     * - `sync_row_no` — порядковый номер синхронизированной площадки в списке (для UI)
     *
     * @return void
     * @throws void исключения перехватываются внутри, пишутся в MsgQueue как ERROR
     */
    function aclSyncAllAction() {
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!(can_add(Module::MOD_SECURITY) && can_del(Module::MOD_SECURITY))) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        /**
         * Команда для синхронизации ТП
         */
        $tp_id = ($_GET[TP::F_GET_ACL_SYNC_TP] ?? null);
        
        $tp_list = $this->db->get_my_tp_list(active: 1, is_managed: 1);

        if (empty($tp_list)) {
            MsgQueue::msg(MsgType::ERROR, __('The list of technical platforms of subscriber [%d] is empty | Список технических площадок абонента [%d] пуст | Список технічних майданчиків абонента [%d] порожній', App::get_user_id()));
            redirect();
        }

        $sync_row_no = 1;
        if ($this->db->validate_id_tp($tp_id)) {

            $tp = $this->db->get_tp($tp_id);
            
            /**
             * Синхронизируем
             */

            $acl_tables = App::get_config('acl_sync_all_tables');

            $sync_full = [];
            foreach ($acl_tables as $table_id) {
                if (!$this->db->validate_id(DevAclTable::TABLE, $table_id, DevAclTable::F_ID)) {
                    MsgQueue::msg(MsgType::ERROR, __('Invalid ACL table ID | Не верный ID ACL-таблицы | Невірний ID ACL-таблиці') . ' [' . $table_id . ']');
                    redirect();
                }

                $acl_table = $this->db->getAclTableById($table_id);

                if (empty($acl_table)) {
                    MsgQueue::msg(MsgType::ERROR, __('ACL table description entry not found | Запись описания ACL-таблицы не найдена | Запис опису ACL-таблиці не знайдено') . ' [' . $table_id. ']');
                    redirect();
                }

                $acl_rows = $this->db->getAclListForSync(aclTableId: $table_id);
                
                $sync_rows = [];
                foreach ($acl_rows as $row) {
                    $sync_rows[] = [
                        Mik::F_LIST_LIST    => $acl_table[DevAclTable::F_NAME],
                        Mik::F_LIST_ADDRESS => $row[DevAclList::F_ADDRESS],
                        Mik::F_LIST_COMMENT => $row[DevAclList::F_COMMENT] ?? '',
                        Mik::F_LIST_ENABLED => $row[DevAclList::F_ENABLED] ?? true,
                    ];
                }
                
                $sync_full = array_merge($sync_full, $sync_rows);
            }

            if (empty($sync_full)) {
                MsgQueue::msg(MsgType::ERROR, __('No entries to sync | Нет записей для синхронизации | Немає записів для синхронізації'));
                redirect();
            }            
            MsgQueue::msg(MsgType::SUCCESS, __('Records to sync | Записей для синхронизации | Записів для синхронізації') . ': ' . count($sync_full));
            
//            debug($acl_full, '$acl_full', die:1);

            try {
                $device = new MikrotikDevice(tp: $tp);
                MsgQueue::msg(MsgType::SUCCESS, __('Successfully connected to the device | Успешно подключились к устройству | Успішно підключилися до пристрою') . ' <span class="text-warning">[' . $tp[TP::F_TITLE] . ']</span>');
                MsgQueue::msg(MsgType::SUCCESS, implode(' | ', $device->get_description()));
                $result = $device->sync_address_list_scoped(
                        raw_items: $sync_full, 
                        stop_on_error: false);

                if ($result === false) {
                    MsgQueue::msg(MsgType::ERROR, __('Synchronization completed with errors | Синхронизация завершилась с ошибками | Синхронізація завершилася з помилками'));
                    foreach (MikrotikDevice::$messages ?? [] as $error) {
                        MsgQueue::msg(MsgType::ERROR, $error);
                    }
                } else {
                    MsgQueue::msg(MsgType::SUCCESS, __('ACL table successfully synchronized | ACL-таблица успешно синхронизирована | ACL-таблиця успішно синхронізована') 
                            . ' | ' . $result . ' ' . __('records | записей | записів'));
                }
            } catch (\Throwable $e) {
                MsgQueue::msg(MsgType::ERROR, __('ACL synchronization error | Ошибка синхронизации ACL | Помилка синхронізації ACL'));
                MsgQueue::msg(MsgType::ERROR, $e->getMessage());
            }
            
            foreach ($tp_list as $tp_one) {
                $sync_row_no++;
                if ($tp_one[TP::F_ID] == $tp_id) {
                    break;
                }
            }
        }

        View::setMeta(
                title: __('Updating ACLs on technical nodes | Обновление ACL-списков на технических узлах | Оновлення ACL-списків на технічних вузлах')
            );
        $this->setVariables([
                'tp_list'       => $tp_list,
                'sync_row_no'   => $sync_row_no,
            ]);
        
    }




    function deleteAction() {
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!can_del(Module::MOD_TP)) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        $tp_id = (int) ($this->route[F_ALIAS] ?? 0);
        if (!$this->db->validate_id(TP::TABLE, $tp_id, TP::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect technical site ID | Не верный ID технической площадки | Не вірний ID технічного майданчика'));
            redirect();
        }

        $my_tp_list = $this->db->get_my_tp_id_list();
        if (!in_array($tp_id, $my_tp_list)) {
            MsgQueue::msg(MsgType::ERROR, __('Alien technical site | Чужая техническяая площадка | Чужий технічний майданчик'));
            redirect();
        }

        $sql = "SELECT COUNT(`" . PA::F_ID . "`) AS COUNT
                FROM `" . PA::TABLE . "`
                WHERE `" . PA::F_TP_ID . "` = ?";
        $countPa = (int) ($this->db->query($sql, [$tp_id], fetchCell: 0) ?: 0);

        if ($countPa > 0) {
            MsgQueue::msg(MsgType::ERROR, __('It cannot be deleted because there are price fragments connected | Удалить нельзя, поскольку есть подключённые прайсовые фрагменты | Видалити не можна, оскільки є підключені прайсові фрагменти'));
            redirect(TP::URI_EDIT . '/' . $tp_id);
        }

        try {
            $this->db->execute('START TRANSACTION');

            if (!$this->db->execute(
                "DELETE FROM `" . TSUserTp::TABLE . "` WHERE `" . TSUserTp::F_TP_ID . "` = ?",
                [$tp_id]
            )) {
                throw new \Exception(__('Failed to delete user bindings to the technical site | Не удалось удалить привязки пользователей к технической площадке | Не вдалося видалити прив\'язки користувачів до технічного майданчика'));
            }

            if (!$this->db->execute(
                "DELETE FROM `" . TP::TABLE . "` WHERE `" . TP::F_ID . "` = ?",
                [$tp_id]
            )) {
                throw new \Exception(__('Failed to delete technical site entry | Не удалось удалить запись технической площадки | Не вдалося видалити запис технічного майданчика'));
            }

            $this->db->execute('COMMIT');
            MsgQueue::msg(MsgType::SUCCESS, __('The technical site was successfully deleted | Техплощадка успешно удалена | Техмайданчик успішно видалено'));
        } catch (\Throwable $e) {
            $this->db->execute('ROLLBACK');
            MsgQueue::msg(MsgType::ERROR, $e->getMessage());
        }

        redirect(TP::URI_INDEX);
    }




    function normalize(array &$data): void {

        $fields = [
            DataTypes::INT->name => [
                TP::F_ACTIVE                => 0,
                TP::F_DELETED               => 0,
                TP::F_IS_MANAGED            => 0,
                TP::F_TERRITORIAL_GROUP_ID  => 0,
                TP::F_INVEST_GROUP_ID       => 0,
            ],

            DataTypes::INT_NULABLE->name => [
                TP::F_ADMIN_OWNER_ID        => null,
                TP::F_FIRM_ID               => null,
                TP::F_RANG_ID               => null,
                TP::F_UPLINK_ID             => null,
                TP::F_DEFAULT_PRICE_ID      => null,
                TP::F_MIK_PORT              => null,
                TP::F_MIK_FTP_PORT          => null,
            ],

            DataTypes::LONG->name => [
                TP::F_ABON_ID_RANGE_START   => 0,
                TP::F_ABON_ID_RANGE_END     => 0,
            ],

            DataTypes::FLOAT->name => [
                TP::F_COST_PER_M            => 0.0,
                TP::F_COST_TP_VALUE         => 0.0,
            ],

            DataTypes::STR->name => [
                TP::F_TITLE                 => '',
                TP::F_IP                    => null,
                TP::F_LOGIN                 => null,
                TP::F_PASS                  => null,
                TP::F_URL                   => null,
                TP::F_URL_ZABBIX            => null,
                TP::F_ADDRESS               => null,
                TP::F_COORD                 => null,
                TP::F_WEB_MANAGEMENT        => null,
                TP::F_DESCRIPTION           => null,
                TP::F_COST_PER_M_DESCRIPTION => null,
                TP::F_COST_TP_DESCRIPTION   => null,
                TP::F_MIK_IP                => null,
                TP::F_MIK_LOGIN             => null,
                TP::F_MIK_PASSWD            => null,
                TP::F_MIK_FTP_IP            => null,
                TP::F_MIK_FTP_LOGIN         => null,
                TP::F_MIK_FTP_PASSWD        => null,
                TP::F_MIK_FTP_FOLDER        => null,
                TP::F_MIK_FTP_GETPATH       => null,
            ],
        ];

        foreach ($fields as $type => $rows) {
            switch ($type) {
                case DataTypes::INT->name:
                case DataTypes::LONG->name:
                    foreach ($rows as $field => $value) {
                        if (isset($data[$field])) {
                            $data[$field] = (int)$data[$field];
                        } else {
                            $data[$field] = $value;
                        }
                    }
                    break;

                case DataTypes::INT_NULABLE->name:
                    foreach ($rows as $field => $value) {
                        if (!empty($data[$field])) {
                            $data[$field] = (int)$data[$field];
                        } else {
                            $data[$field] = $value;
                        }
                    }
                    break;

                case DataTypes::FLOAT->name:
                    foreach ($rows as $field => $value) {
                        if (isset($data[$field])) {
                            $data[$field] = (float)$data[$field];
                        } else {
                            $data[$field] = $value;
                        }
                    }
                    break;

                case DataTypes::STR->name:
                    foreach ($rows as $field => $value) {
                        if (isset($data[$field])) {
                            $data[$field] = (string)$data[$field];
                        } else {
                            $data[$field] = $value;
                        }
                    }
                    break;

                default:
                    throw new \Exception('This should not happen: Invalid data type | Этого не должно быть: Не верный тип даных | Цього не повинно бути: Неправильний тип даних');
                    // break;
            }
        }

        /*
 	# 	Имя                     Тип             Сравнение           Атрибуты 	Null 	По умолчанию 	Комментарии 	Дополнительно 	Действие
	1 	id                      int                                 UNSIGNED 	Нет 	Нет                             AUTO_INCREMENT 	Изменить Изменить 	Удалить Удалить
	10 	title                   varchar(50) 	utf8mb3_general_ci 		Нет 	Нет             Название тех.площадки 		Изменить Изменить 	Удалить Удалить
	21 	description             text            utf8mb3_general_ci 		Да 	NULL            Описание ТП 		Изменить Изменить 	Удалить Удалить
	2 	status                  tinyint(1)                                      Нет 	1               0 — Отключен/демонтирован, 1 — Работает 		Изменить Изменить 	Удалить Удалить
	3 	deleted                 tinyint(1)                                      Нет 	0               ТП демонтирована 		Изменить Изменить 	Удалить Удалить
	4 	is_managed              tinyint(1)                                      Нет 	0               Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН 		Изменить Изменить 	Удалить Удалить
	5 	rang_id                 int                                 UNSIGNED 	Да 	NULL            Ранг узла: 1 — Абонентский узел. 2 — AP. 3 — Агрегатор AP. 4 — Bridge AP. 5 — Bridge Client. 10 — Хостинговая тех. площадка. 100 — Биллинг. 		Изменить Изменить 	Удалить Удалить
	6 	territorial_group_id 	int                                 UNSIGNED 	Нет 	0               ID территориальной группы технических площадок 		Изменить Изменить 	Удалить Удалить
	7 	invest_group_id 	int                                 UNSIGNED 	Нет 	0               ID инвестиционной группы распределения дивидендов 		Изменить Изменить 	Удалить Удалить
	8 	admin_owner_id  	int                                 UNSIGNED 	Да 	NULL            ID администратора-владельца 		Изменить Изменить 	Удалить Удалить
	9 	firm_id                 int                                 UNSIGNED 	Да 	NULL            ID Обслуживающего предприятия 		Изменить Изменить 	Удалить Удалить
	11 	ip                      varchar(40) 	utf8mb3_general_ci 		Да 	NULL            IP-адрес точки доступа или тех.площадки 		Изменить Изменить 	Удалить Удалить
	12 	login                   varchar(50) 	utf8mb3_general_ci 		Да 	NULL            логин для управляющего доступа 		Изменить Изменить 	Удалить Удалить
	13 	pass                    varchar(50) 	utf8mb3_general_ci 		Да 	NULL            пароль дл управляющего доступа 		Изменить Изменить 	Удалить Удалить
	14 	url                     tinytext 	utf8mb3_general_ci 		Да 	NULL            URL-строка для управления устройством (обычно через вэб) 		Изменить Изменить 	Удалить Удалить
	15 	url_zabbix              tinytext 	utf8mb3_general_ci 		Да 	NULL            URL страницы в системе мониторинга zabbix относящейся к этой ТП 		Изменить Изменить 	Удалить Удалить
	16 	address                 tinytext 	utf8mb3_general_ci 		Да 	NULL            Адрес размещения ТП 		Изменить Изменить 	Удалить Удалить
	17 	coord                   varchar(40) 	utf8mb3_general_ci 		Да 	NULL            Географические координаты ТП для отображения на картах 		Изменить Изменить 	Удалить Удалить
	18 	uplink_id Индекс 	int                                 UNSIGNED 	Да 	NULL            Узел "верхнего" уровня, от которого идёт сигнал к этому узлу (не обязательно маршрутизатор) 		Изменить Изменить 	Удалить Удалить
	19 	web_management          tinytext 	utf8mb3_general_ci 		Да 	NULL            Страница web-доступа к устройству 		Изменить Изменить 	Удалить Удалить
	20 	default_price_id  	int                                 UNSIGNED 	Да 	NULL            Прайс По_умолчанию для этой ТП 		Изменить Изменить 	Удалить Удалить
	22 	cost_per_M              float                                           Нет 	0               Стоимость Эксплуатации/аренды/абонплаты техплощадки 		Изменить Изменить 	Удалить Удалить
	23 	cost_per_M_description 	text            utf8mb3_general_ci 		Да 	NULL            Описание стоимости эксплуатации ТП 		Изменить Изменить 	Удалить Удалить
	24 	cost_tp_value           float                                           Нет 	0               Стоимость строительства/ввода в эксплуатацию ТП 		Изменить Изменить 	Удалить Удалить
	25 	cost_tp_description 	text            utf8mb3_general_ci 		Да 	NULL            Описание стоимости строительства / ввода в эксплуатацию ТП 		Изменить Изменить 	Удалить Удалить
	26 	abon_id_range_start 	bigint                                          Да 	0               Начало диапазона выдачи ID для пользователей 		Изменить Изменить 	Удалить Удалить
	27 	abon_id_range_end 	bigint                                          Да 	0               Конец диапазона выдачи ID для пользователей 		Изменить Изменить 	Удалить Удалить
	28 	script_mik_ip           tinytext 	utf8mb3_general_ci 		Да 	NULL            IP устройства 		Изменить Изменить 	Удалить Удалить
	29 	script_mik_port 	tinytext 	utf8mb3_general_ci 		Да 	NULL            tcp порт доступа на устройство 		Изменить Изменить 	Удалить Удалить
	30 	script_mik_login 	tinytext 	utf8mb3_general_ci 		Да 	NULL            login доступа на устройство 		Изменить Изменить 	Удалить Удалить
	31 	script_mik_passwd 	tinytext 	utf8mb3_general_ci 		Да 	NULL            passwd доступа на устройства 		Изменить Изменить 	Удалить Удалить
	32 	script_ftp_ip           tinytext 	utf8mb3_general_ci 		Да 	NULL            IP-адрес для ftp доступа 		Изменить Изменить 	Удалить Удалить
	33 	script_ftp_port 	tinytext 	utf8mb3_general_ci 		Да 	NULL            TCP-порт для ftp доступа 		Изменить Изменить 	Удалить Удалить
	34 	script_ftp_login 	tinytext 	utf8mb3_general_ci 		Да 	NULL            Логин для ftp доступа 		Изменить Изменить 	Удалить Удалить
	35 	script_ftp_passwd 	tinytext 	utf8mb3_general_ci 		Да 	NULL            Пасс для ftp доступа 		Изменить Изменить 	Удалить Удалить
	36 	script_ftp_folder 	tinytext 	utf8mb3_general_ci 		Да 	NULL            Имя папаки для сохранения файлов 		Изменить Изменить 	Удалить Удалить
	37 	script_ftp_getpath 	tinytext 	utf8mb3_general_ci 		Да 	NULL            Путь и шаблон на сервере для скачивания файлов 		Изменить Изменить 	Удалить Удалить
        38 	creation_date           int                                             Нет 	0               Дата создания записи о техплощадке 		Изменить Изменить 	Удалить Удалить
        39 	creation_uid Индекс 	int                                 UNSIGNED 	Нет 	0               Кто создал запись о ТП 		Изменить Изменить 	Удалить Удалить
	40 	modified_date           int                                             Нет 	0               Дата инменения записи о ТП 		Изменить Изменить 	Удалить Удалить
	41 	modified_uid Индекс 	int                                 UNSIGNED 	Нет 	0               Кто изменил запись о ТП 		Изменить Изменить 	Удалить Удалить
        */


    }


    function validate(array $data): bool {
        $v = new Validator($data);
        $v->labels([
            TP::F_ACTIVE                 => __('Status | Статус | Статус'),
            TP::F_DELETED                => __('Deleted | Удалена | Видалена'),
            TP::F_IS_MANAGED             => __('Managed | Управляемая | Керована'),
            TP::F_RANG_ID                => __('Node rank | Ранг узла | Ранг вузла'),
            TP::F_TERRITORIAL_GROUP_ID   => __('Territorial group | Территориальная группа | Територіальна група'),
            TP::F_INVEST_GROUP_ID        => __('Investment group | Инвест. группа | Інвест. група'),
            TP::F_ADMIN_OWNER_ID         => __('Admin owner | Админ-владелец | Адмін-власник'),
            TP::F_FIRM_ID                => __('Service company | Обслуживающая фирма | Обслуговуюча компанія'),
            TP::F_TITLE                  => __('Name | Название | Назва'),
            TP::F_IP                     => __('IP | IP | IP'),
            TP::F_LOGIN                  => __('Login | Логин | Логін'),
            TP::F_PASS                   => __('Password | Пароль | Пароль'),
            TP::F_URL                    => __('URL | URL | URL'),
            TP::F_URL_ZABBIX             => __('Zabbix URL | URL Zabbix | URL Zabbix'),
            TP::F_ADDRESS                => __('Address | Адрес | Адреса'),
            TP::F_COORD                  => __('Coordinates | Координаты | Координати'),
            TP::F_UPLINK_ID              => __('Uplink | Uplink | Uplink'),
            TP::F_WEB_MANAGEMENT         => __('Web management | Web управление | Web керування'),
            TP::F_DEFAULT_PRICE_ID       => __('Default price | Прайс по умолчанию | Прайс за замовчуванням'),
            TP::F_DESCRIPTION            => __('Description | Описание | Опис'),
            TP::F_COST_PER_M             => __('Maintenance cost | Стоимость эксплуатации | Вартість експлуатації'),
            TP::F_COST_TP_VALUE          => __('Construction cost | Стоимость строительства | Вартість будівництва'),
            TP::F_ABON_ID_RANGE_START    => __('Abon ID range start | Начало диапазона абон.ID | Початок діапазону абон.ID'),
            TP::F_ABON_ID_RANGE_END      => __('Abon ID range end | Конец диапазона абон.ID | Кінець діапазону абон.ID'),
            TP::F_MIK_IP                 => __('Mikrotik IP | Mikrotik IP | Mikrotik IP'),
            TP::F_MIK_PORT               => __('Mikrotik port | Mikrotik порт | Mikrotik порт'),
            TP::F_MIK_LOGIN              => __('Mikrotik login | Mikrotik логин | Mikrotik логін'),
            TP::F_MIK_PASSWD             => __('Mikrotik password | Mikrotik пароль | Mikrotik пароль'),
            TP::F_MIK_FTP_IP             => __('FTP IP | FTP IP | FTP IP'),
            TP::F_MIK_FTP_PORT           => __('FTP port | FTP порт | FTP порт'),
            TP::F_MIK_FTP_LOGIN          => __('FTP login | FTP логин | FTP логін'),
            TP::F_MIK_FTP_PASSWD         => __('FTP password | FTP пароль | FTP пароль'),
            TP::F_MIK_FTP_FOLDER         => __('FTP folder | FTP папка | FTP папка'),
            TP::F_MIK_FTP_GETPATH        => __('FTP path | FTP путь | FTP шлях')
        ]);

        // обязательные
        $v->rule('required', [
            TP::F_ACTIVE,
            TP::F_DELETED,
            TP::F_IS_MANAGED,
            TP::F_TITLE,
        ]);

        // целые числа
        $v->rule('integer', [
            TP::F_ACTIVE,
            TP::F_DELETED,
            TP::F_IS_MANAGED,
            TP::F_RANG_ID,
            TP::F_TERRITORIAL_GROUP_ID,
            TP::F_INVEST_GROUP_ID,
            TP::F_ADMIN_OWNER_ID,
            TP::F_FIRM_ID,
            TP::F_UPLINK_ID,
            TP::F_DEFAULT_PRICE_ID,
            TP::F_ABON_ID_RANGE_START,
            TP::F_ABON_ID_RANGE_END,
            TP::F_CREATION_DATE,
            TP::F_CREATION_UID,
            TP::F_MODIFIED_DATE,
            TP::F_MODIFIED_UID,
        ]);

        // дробные
        $v->rule('numeric', [
            TP::F_COST_PER_M,
            TP::F_COST_TP_VALUE
        ]);

        // строки ограниченной длины

        $v->rules(  ['lengthMax' =>
                        [
                            [TP::F_TITLE,   TP::LENGTS[TP::F_TITLE]],
                            [TP::F_LOGIN,   TP::LENGTS[TP::F_LOGIN]],
                            [TP::F_PASS,    TP::LENGTS[TP::F_PASS]],
                            [TP::F_COORD,   TP::LENGTS[TP::F_COORD]],
                            [TP::F_IP,      TP::LENGTS[TP::F_IP]],
                        ]
                    ]
                );

        // IP
        $v->rule('ip', [
            TP::F_IP,
//            TP::F_MIK_IP,
//            TP::F_MIK_FTP_IP,
        ]);

        // URL
        $v->rule('url', [
//            TP::F_URL,
            TP::F_URL_ZABBIX,
            // TP::F_WEB_MANAGEMENT,
        ]);

        // статус (0/1)
        $v->rule('in', TP::F_ACTIVE, [0, 1]);
        $v->rule('in', TP::F_DELETED, [0, 1]);
        $v->rule('in', TP::F_IS_MANAGED, [0, 1]);

        // положительные числа

        $v->rules([
            'min' => [
                [TP::F_TERRITORIAL_GROUP_ID, 0],
                [TP::F_INVEST_GROUP_ID, 0],
                [TP::F_COST_PER_M, 0],
                [TP::F_COST_TP_VALUE, 0],
            ]
        ]);

        // Проверка границ больше - меньше
        if (($data[TP::F_ABON_ID_RANGE_START] > 0) || ( $data[TP::F_ABON_ID_RANGE_END] > 0)) {
            $v->rule('min', TP::F_ABON_ID_RANGE_END,    $data[TP::F_ABON_ID_RANGE_START]+1);
            $v->rule('max', TP::F_ABON_ID_RANGE_START,  $data[TP::F_ABON_ID_RANGE_END]-1);
        }

        if(!$v->validate()) {
            // ошибки
            MsgQueue::msg(type: MsgType::ERROR, message: $v->errors());
            return false;
        }

        return true;
    }

    
    
    function make_pa_out(array &$bill_tables, MikrotikDevice $dev): array {
        
        $model = new AbonModel();
        
        $out = array();
        /*
            $out => 
            [
                [PA::TABLE] = [];
                [Abon::TABLE] = [];
                [Price::TABLE] = [];
                [Mik::T_ARP] = [];
                [Mik::T_LEASES] = [];
                [Mik::T_NAT11] = []; // !!!
            ]
         */
        
        foreach ($bill_tables[Api::BILL_PA_LIST] as $pa_one) {
            
            $rec[PA::TABLE] = $pa_one;
            $rec[User::TABLE] = $model->get_user_by_abon_id($pa_one[PA::F_ABON_ID]);
            $rec[Abon::TABLE] = $model->get_abon($pa_one[PA::F_ABON_ID]);
            $rec[Price::TABLE] = $model->get_price($pa_one[PA::F_PRICE_ID]);

            if ($pa_one[PA::F_NET_IP_SERVICE]) {
                
                /**
                 * ПРОВЕРКИ
                 */
                $rec['VALIDATE'][PA::TABLE]['IP_ON']              = (!empty($pa_one[PA::F_NET_IP]) xor !empty($pa_one[PA::F_NET_ON_ABON_IP]));
                
                $rec['VALIDATE'][PA::TABLE][PA::F_NET_IP]         = validate_ip($pa_one[PA::F_NET_IP]);
                $rec['VALIDATE'][PA::TABLE][PA::F_NET_NAT11]      = (!empty($pa_one[PA::F_NET_NAT11])      ? validate_ip($pa_one[PA::F_NET_NAT11])      : null);
                $rec['VALIDATE'][PA::TABLE][PA::F_NET_ON_ABON_IP] = (!empty($pa_one[PA::F_NET_ON_ABON_IP]) ? validate_ip($pa_one[PA::F_NET_ON_ABON_IP]) : null);

                /**
                 * NAT 1:1
                 */
                $rec[Mik::T_NAT11] = 
                        ($rec['VALIDATE'][PA::TABLE][PA::F_NET_NAT11] 
                            ? MikrotikDevice::get_nat_11_by_ip(
                                rules: $dev->get_nat_rules(),
                                ip_private: $pa_one[PA::F_NET_IP],
                                ip_public: $pa_one[PA::F_NET_NAT11])
                            : null
                        );

                /**
                 * Реальный MAC
                 */
                $rec[Mik::T_ARP] = 
                        (is_empty($pa_one[PA::F_NET_IP])
                            ? null
                            : $dev->get_arp_items(ip: $pa_one[PA::F_NET_IP])
                        );

                /**
                 * Статус в DHCP LEASES
                 */
                $rec[Mik::T_LEASES] = $dev->get_dhcp_lease_by_ip($pa_one[PA::F_NET_IP]);
                
                /**
                 * Соответствует ли МАК в ПФ и МАК в устройстве
                 */
                $rec['VALIDATE']['EQUAL_ARP'] = 
                        (empty($pa_one[PA::F_NET_MAC] ?? null)
                            ?   null // Мак в ПФ не установлен
                            :   $pa_one[PA::F_NET_MAC] === ($rec[Mik::T_ARP][0][Mik::F_ARP_MAC] ?? null)
                        );

                /**
                 * IP в ПФ и IP в ABON
                 */
                $rec['VALIDATE']['ON_ABON']['ON'] = 
                        ($dev->in_address_list_abon(ip: $pa_one[PA::F_NET_IP], ena: 1)
                            ?   true
                            :   ($dev->in_address_list_abon(ip: $pa_one[PA::F_NET_IP], ena: 0)
                                    ?   false
                                    :   null
                                )
                        );

                if ($rec['VALIDATE']['ON_ABON']['ON'] !== true) {
                    /**
                     * Поиск вариантов
                     */
                    $rec['VALIDATE']['ON_ABON']['FOUND_DOG'] = $dev->find_address_list_items([
                        Mik::F_SEARCH_LIST => Mik::L_ABON,
                        Mik::F_SEARCH_DESCR => $rec[Abon::TABLE][Abon::F_ID] . ' '
                    ]);
                    if (!empty($pa_one[PA::F_NET_IP])) {
                        $rec['VALIDATE']['ON_ABON']['FOUND_IP'] = $dev->find_address_list_items([
                            Mik::F_SEARCH_LIST => Mik::L_ABON,
                            Mik::F_SEARCH_DESCR => $rec[Abon::TABLE][Abon::F_ID] . ' '
                        ]);
                    }
                }

                /**
                 * Проверка статуса ИП-адреса в DHCP-Leases
                 */
                $rec['VALIDATE']['ON_DHCP']['IP_ON_LEASE'] = 
                        ($rec[Mik::T_LEASES] === []
                            ?   'NONE'  // Записи в dhcp lease нет. Приемлемо
                            :   ($rec[Mik::T_LEASES][Mik::F_DHCP_LEASE_BLOCKED]
                                    ?   'BLOCKED'
                                    :   ($rec[Mik::T_LEASES][Mik::F_DHCP_LEASE_DYNAMIC]
                                            ?   'DYNAMIK' // Запись в dhcp lease динамическая. Плохо
                                            :   'STATIC'  // Запись в dhcp lease статическая. Хорошо
                                        )
                                )
                        );

            }
            $out[] = $rec;
        }
        return $out;
    }
    

    
    function manageAction() {
        
        if (!App::$auth->isAuth) {
            MsgQueue::msg(MsgType::ERROR, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            self::log_unauthorize();
            redirect();
        }

        if (!(can_edit(Module::MOD_TP) && can_edit(Module::MOD_NET_DEV))) {
            MsgQueue::msg(MsgType::ERROR, __('Insufficient rights | Недостаточно прав | Недостатньо прав'));
            self::log_no_rights();
            redirect();
        }

        $tp_id = (int) ($this->route[F_ALIAS] ?? 0);
        if (!$this->db->validate_id_tp($tp_id)) {
            MsgQueue::msg(MsgType::ERROR, __('Incorrect technical site ID | Не верный ID технической площадки | Не вірний ID технічного майданчика'));
            redirect();
        }

        $bill_rec[TP::F_BILL_TP] = $this->db->get_tp($tp_id);
        $bill_rec[TP::F_BILL_PA_LIST] = $this->db->get_prices_apply_by_tp($tp_id);

        try {
            $dev = new MikrotikDevice(tp: $bill_rec[TP::F_BILL_TP]);
        } catch (\Exception $exc) {
            MsgQueue::msg(MsgType::ERROR, __('Error accessing device | Ошибка обращения к устройству | Помилка звернення до пристрою') . ': [' . $bill_rec[TP::F_BILL_TP][TP::F_ID] . '] | ' . $bill_rec[TP::F_BILL_TP][TP::F_TITLE] );
            MsgQueue::msg(MsgType::ERROR, MikrotikDevice::get_messages());
            MsgQueue::msg(MsgType::ERROR, $exc->getMessage());
            redirect();
        }

        $mik_rec[TP::F_T_HOSTNAME]  = $dev->get_hostname();
        
        $mik_rec[TP::F_T_IP_LIST]         = $dev->get_ip_address_items();
        $mik_rec[TP::F_T_INFO]            = $dev->get_description();
        $mik_rec[TP::F_T_STATE]           = $dev->get_state();
        $mik_rec[TP::F_T_ADDR_LIST_STAT]  = $dev->get_address_lists_stat();
        $mik_rec[TP::F_T_ADDR_LIST_CACHE] = $dev->get_address_list_cache();
        $mik_rec[TP::F_T_ARP_STAT]        = $dev->get_arp_stat();
        $mik_rec[TP::F_T_ARP_LIST]        = $dev->get_arp_items();
        $mik_rec[TP::F_T_GATES]           = $dev->get_gateways();
        $mik_rec[TP::F_T_NAT_LIST]        = $dev->get_nat_rules();
//        debug($mik_rec[TP::F_T_NAT_LIST], '$mik_rec[TP::F_T_NAT_LIST]', die:0);
//      $mik_rec[TP::F_T_LEASES_LIST]     = $dev->get_description() get_aligned_table(Api::get_tp_dhcp_leases_all($mik));

        $out_tables = [
            TP::F_OUT_PA    => $this->make_pa_out(bill_tables: $bill_rec, dev: $dev),
//            [
//                'title'             => '[PA]',
//                'caption'           => "<h1>Биллинг: Список прайсовых фрагментов <font color=green>PRICES_APPLY</font></h1>",
//                //                     ["id",           "abon_id", "inf", "ip_service",   "nat11",                    "trusted",      "ip",            "mac"]
//                'cell_attributes'   => ["align=center", "abon_id", "inf", "align=center", "nat11",                    "align=center", "valign=bottom", "valign=bottom"],
//                'col_titles'        => ["PA ID",        "Abon ID", "inf", "IP Service",   "NAT 1:1<br>IP у абонента", "trust",        "ip",            "mac"],
//            ],

//            OUT_ARP     =>  ['src_on'=>SRC_ARP_ACT,     'src_off'=>SRC_ARP_OFF,    'txt' => '[ARP]',    'mng_id' => 'out_arp',    'btn_id'=>'btn_put_arp',    'color'=>(isset($_GET[ANCH_ARP])    ? BLACK : GRAY),
//                                'anch'              =>  ANCH_ARP,
//                                't'                 =>  make_arp_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Таблица <font color=green>ARP</font></h1>",
//                                                    //   "aid_abon", "aid_pa", "sw_1",   "sw_comment", "ip",     "fine_1", "address", "mac-stat", "interface", "published",    "aid from ABON",                    "aid from PA",                    "sw from SW",                    "stat1"
//                                'cell_attributes'   =>  ["hidden",   "hidden", "hidden", "hidden",     "hidden", "hidden", "address", "mac-stat", "interface", "align=center", "aid from ABON",                    "aid from PA",                    "sw from SW",                    "stat1"],
//                                'col_titles'        =>  ["aid_abon", "aid_pa", "sw_1", "sw_comment",   "ip",     "fine_1", "address", "mac-stat", "interface", "published",    "aid<br>".paint("from ABON", GRAY), "aid<br>".paint("from PA", GRAY), "sw<br>".paint("ABON/SW", GRAY), "stat1"],
//                            ],
//
//            OUT_ABON    =>  ['src_on'=>SRC_A_ACT,      'src_off'=>SRC_A_OFF,      'txt' => '['.MIK_TABLE_ABON.']',   'mng_id' => 'abon_out',   'btn_id'=>'btn_abon_out',   'color'=>(isset($_GET[ANCH_ABON])   ? BLACK : GRAY),
//                                'anch'              =>  ANCH_ABON,
//                                't'                 =>  make_abon_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Список состояния абонентов таблицы <font color=green>".MIK_TABLE_ABON."</font></h1>",
//                                'cell_attributes'   =>  null,
//                                'col_titles'        =>  null,
//                            ],
//
//            OUT_LEASES  =>  ['src_on'=>SRC_LEASES_ACT, 'src_off'=>SRC_LEASES_OFF, 'txt' => '[LEASES]', 'mng_id' => 'leases_out', 'btn_id'=>'btn_leases_out', 'color'=>(isset($_GET[ANCH_LEASES]) ? BLACK : GRAY),
//                                'anch'              =>  ANCH_LEASES,
//                                't'                 =>  make_dhcp_leases_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Таблица <font color=green>DHCP-LEASES</font></h1>",
//                                //                       "astat", "address", "mac-address", "address_mac",    "last-seen"  "server", "active-address", "comment", "aid_comment", "aid_comment_stat", "aid_abon", "aid_abon_stat",  "aid_ip_pa", "aid_ip_pa_stat", "act", "rename_comment"
//                                'cell_attributes'   =>  ["",      "hidden",  "hidden",      "",               "",          "",       "",               "",        "hidden",      "",                 "hidden",   "",               "hidden",    "",               "",    ""],
//                                'col_titles'        =>  ["stat",  "address", "mac-address", "address<br>mac", "last-seen", "server", "active-address", "comment", "aid_comment", "aid<br>comment",   "aid_abon", "aid<br>ABON IP", "aid_ip_pa", "aid<br>PA IP",   "act", "rename_comment"],
//                            ],
//
//            OUT_NAT     =>  ['src_on'=>SRC_NAT_ACT,    'src_off'=>SRC_NAT_OFF,    'txt' => '[NAT]',    'mng_id' => 'nat_out',    'btn_id'=>'btn_nat_out',    'color'=>(isset($_GET[ANCH_NAT])    ? BLACK : GRAY),
//                                'anch'              =>  ANCH_NAT,
//                                't'                 =>  make_nat_out(TABLES: $TABLES, tp_id: $tp_id),
//                                'caption'           =>  "<h1>МИК: Таблица <font color=green>NAT</font></h1>",
//                                'cell_attributes'   =>  ["",      "",      "",       "",         "valign=top", "valign=top", "",        "",              "",    "",    ""],
//                                'col_titles'        =>  ["stat1", "chain", "action", "protocol", "in",         "out",        "comment", "bytes|packets", "aid", "act", "rename"],
//                            ],

        ];






        $this->setVariables([
            'out_tables' => $out_tables,
//            'mik_rec'    => $mik_rec,
//            'bill_rec'   => $bill_rec,
        ]);

        View::setMeta(__('Device management | Управление устройством | Управління пристроєм'));

//        debug($leases, '$leases', debug_view: DebugView::PRINTR, die: 0);
//        debug($nat, '$nat', debug_view: DebugView::PRINTR, die: 0);
//        debug($gates, '$gates', debug_view: DebugView::PRINTR, die: 0);
//        debug($pa, '$pa', debug_view: DebugView::PRINTR, die: 0);
//        debug($address_list, '$address_list', debug_view: DebugView::PRINTR, die: 0);
//        debug($mik_info, '$mik_info', debug_view: DebugView::PRINTR, die: 0);
//        debug($tp, '$tp', debug_view: DebugView::PRINTR, die: 0);

//        debug('end', 'end', die: 1);        
        
        
    }
    
    
    
    function testAction() {
        
//        $model = new \billing\core\base\Model();
        $model = new \app\models\AbonModel();
        

        
        
        $tp = $model->get_tp(97);
        
//        debug($tp, 'TP');
        
        $dev = new MikrotikDevice(tp: $tp);

        debug($dev, '$dev');

        echo $dev->get_hostname() . '<hr>';
        echo implode(' | ', $dev->get_description()) . '<hr>';
        echo implode(' | ', $dev->get_state()) . '<hr>';

        
        
        $filters = $dev->get_filer_rules();
        debug($filters, '$filters');
        $input = $dev->get_filer_input();
        debug($input, '$input1');
        $input = $dev->get_filer_input(protocol: 'tcp');
        debug($input, '$input2');
        $input = $dev->get_filer_input(protocol: 'udp');
        debug($input, '$input3');
        $ip_serv = $dev->get_ip_services();
        debug($ip_serv, '$ip_serv');
        $ip_ports = $dev->get_ip_services_ports();
        debug($ip_ports, '$ip_ports');
        $ip_nets = $dev->get_ip_services_allowed_networks();
        debug($ip_nets, '$ip_nets');
        $inteface_lists = $dev->get_interface_lists();
        debug($inteface_lists, '$inteface_lists');
        
        
        
//        $rule =
//            '/ip/firewall/filter add ' .
//            'chain=input ' .
//            'protocol=tcp ' .
//            'in-interface-list=WAN ' .
//            'dst-port=!' . implode(',', $ip_ports) . ' ' .
//            'action=drop ' .
//            'comment="'.$COMMENT.$VERSION.' DROP all tcp except allowed"';
//        echo $rule;
//        
//        
        $VERSION = 'v001';
        $comment_prefix = 'FW';
        $comment = 'INPUT_INVALID';
        $comment = 'INPUT_DROP_TCP';
        $comment = 'INPUT_DROP_UDP';
        $comment = 'INPUT_DROP_SCAN';
//
//        
//        add_filter(
//                chain: 'input',
//                protocol: 'tcp',
//                in_interface_ist: 'WAN',
//                dstports: '!' . implode(',', $ports),
//                ?string $invalid = null,
//                ?string $dynamic = null,
//                ?string $action = null,
//                ?string $comment = null): bool
//        {
//            $this->connector->exec(
//                    '/ip/firewall/filter/add',
//                    [
//                        'chain'             => $chain, // 'input',
//                        'protocol'          => $protocol, // 'tcp',
//                        'in-interface-list' => $in_interface_ist, // 'WAN',
//                        'dst-port'          => $dstports, // '!' . implode(',', $ports),
//                        'action'            => $action, // 'drop',
//                        'comment'           => $comment, // 'ABON DROP',
//                    ]);
//
//        }        
//        
//        
        
//        Тестирование систем валидации.
//        Нужно или разобраться как это работает или удалить нафиг
//        лучше разобраться
//        $filterRules = $dev->get_filer_rules();
//        $natRules = $dev->get_nat_list();
//        debug($filterRules, '$filterRules');
//        debug($natRules, '$natRules');
//        $v = new FWAbonValidator();
//        $v->loadFilter($filterRules);
//        $v->loadNat($natRules);
//        $errors = $v->validate();
//        if ($errors) {
//            echo "<pre>";
//            print_r($errors);
//            print_r($v->repairScript());
//            echo "</pre>";
//        }        
        
        die();
    }
    
    
}
