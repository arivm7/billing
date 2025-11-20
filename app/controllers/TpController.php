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
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\SessionFields;
use config\tables\Firm;
use config\tables\Module;
use config\tables\PA;
use config\tables\TP;
use config\tables\User;
use DataTypes;
use DebugView;
use Valitron\Validator;

/**
 * Description of TpController.php
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
        if (App::$auth->isAuth) {
            if (can_view(Module::MOD_TP)) {
                $my = $_SESSION[User::SESSION_USER_REC];
                $tp_list = $this->db->get_tp_list(
                        user_id: $my[User::F_ID],
                        deleted: 0
                );

                foreach ($tp_list as &$row) {
                    $sql = "SELECT COUNT(`".PA::F_ID."`) AS COUNT FROM `".PA::TABLE."` WHERE `".PA::F_TP_ID."`={$row[TP::F_ID]}";
                    $row[TP::F_COUNT_PA] = $this->db->get_count_by_sql($sql);
                }

                View::setMeta(
                        title: __('Список технических узлов')
                    );
                $this->setVariables([
                        'tp_list' => $tp_list,
                    ]);
            } else {
                MsgQueue::msg(MsgType::ERROR, __('Недостаточно прав.'));
                redirect();
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect();
        }
    }



    function editAction() {
        if (App::$auth->isAuth) {
            if (can_edit(Module::MOD_TP)) {
                $tp_id = $this->route[F_ALIAS] ?? 0;
                if ($tp_id) {
                    $my = $_SESSION[User::SESSION_USER_REC];
                    $my_tp_list = $this->db->get_my_tp_id_list();
                    if (in_array($tp_id, $my_tp_list)) {
                        $tp = $this->db->get_tp($tp_id);
                        $prices = $this->db->get_prices(tp_id: $tp_id);
                        $admin_owner = $this->db->get_user($tp[TP::F_ADMIN_OWNER_ID]);
                        $uplink = (empty($tp[TP::F_UPLINK_ID]) ? null : $this->db->get_tp($tp[TP::F_UPLINK_ID]));
                        $firm = $this->db->get_row_by_id(table_name: Firm::TABLE, field_id: Firm::F_ID, id_value: $tp[TP::F_FIRM_ID]);
                        $this->setVariables([
                                'prices' => $prices,
                                'admin_owner' => $admin_owner,
                                'uplink' => $uplink,
                                'firm' => $firm,
                                'tp' => $tp,
                            ]);

                        View::setMeta(title: __('Редактирование параметров технической площадки'));

                    } else {
                        MsgQueue::msg(MsgType::ERROR, __('Чужая ТП'));
                        redirect();
                    }
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Не указан ID ТП'));
                    redirect();
                }
            } else {
                MsgQueue::msg(MsgType::ERROR, __('Недостаточно прав.'));
                redirect();
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect();
        }
    }



    function saveAction() {
        if (App::$auth->isAuth) {
            if (can_edit(Module::MOD_TP)) {
                $tp_id = $this->route[F_ALIAS] ?? 0;
                if ($tp_id) {
                    $my = $_SESSION[User::SESSION_USER_REC];
                    $my_tp_list = $this->db->get_my_tp_id_list();
                    if (in_array($tp_id, $my_tp_list)) {
                        $tp = $_POST[TP::POST_REC];
                        $tp[TP::F_ID] = $tp_id;
                        $this->prepare($tp);
                        if (!$this->validate($tp)) {
                            MsgQueue::msg(MsgType::ERROR, __('Не корректные данные'));
                            $_SESSION[SessionFields::FORM_DATA] = $tp;
                            redirect();
                        }
//                        debug($tp, '$tp', debug_view: DebugView::PRINTR);
//                        debug($tp, '$tp', debug_view: DebugView::DUMP, die: 1);
                        if ($this->db->update_row_by_id(table: TP::TABLE, field_id: TP::F_ID, row: $tp)) {
                            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Данные успешно внесены'));
                            redirect(TP::URI_EDIT . '/' . $tp_id);
                        } else {
                            MsgQueue::msg(MsgType::ERROR, $this->db->errorInfo());
                            $_SESSION[SessionFields::FORM_DATA] = $tp;
                            redirect(TP::URI_EDIT . '/' . $tp_id);
                        }
                    } else {
                        MsgQueue::msg(MsgType::ERROR, __('Чужая ТП'));
                        redirect();
                    }
                } else {
                    MsgQueue::msg(MsgType::ERROR, __('Не указан ID ТП'));
                    redirect();
                }
            } else {
                MsgQueue::msg(MsgType::ERROR, __('Недостаточно прав.'));
                redirect();
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Авторизуйтесь, пожалуйста'));
            redirect();
        }
    }




    function prepare(array &$data): void {

        $fields = [
            DataTypes::INT->name => [
                TP::F_STATUS                => 0,
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
                    throw new \Exception('Этого не должно быть: Не верный тип даных.');
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
            TP::F_STATUS                 => 'Статус',
            TP::F_DELETED                => 'Удалена',
            TP::F_IS_MANAGED             => 'Управляемая',
            TP::F_RANG_ID                => 'Ранг узла',
            TP::F_TERRITORIAL_GROUP_ID   => 'Территориальная группа',
            TP::F_INVEST_GROUP_ID        => 'Инвест. группа',
            TP::F_ADMIN_OWNER_ID         => 'Админ-владелец',
            TP::F_FIRM_ID                => 'Обслуживающая фирма',
            TP::F_TITLE                  => 'Название',
            TP::F_IP                     => 'IP',
            TP::F_LOGIN                  => 'Логин',
            TP::F_PASS                   => 'Пароль',
            TP::F_URL                    => 'URL',
            TP::F_URL_ZABBIX             => 'URL Zabbix',
            TP::F_ADDRESS                => 'Адрес',
            TP::F_COORD                  => 'Координаты',
            TP::F_UPLINK_ID              => 'Uplink',
            TP::F_WEB_MANAGEMENT         => 'Web управление',
            TP::F_DEFAULT_PRICE_ID       => 'Прайс по умолчанию',
            TP::F_DESCRIPTION            => 'Описание',
            TP::F_COST_PER_M             => 'Стоимость эксплуатации',
            TP::F_COST_TP_VALUE          => 'Стоимость строительства',
            TP::F_ABON_ID_RANGE_START    => 'Начало диапазона абон.ID',
            TP::F_ABON_ID_RANGE_END      => 'Конец диапазона абон.ID',
            TP::F_MIK_IP                 => 'Mikrotik IP',
            TP::F_MIK_PORT               => 'Mikrotik порт',
            TP::F_MIK_LOGIN              => 'Mikrotik логин',
            TP::F_MIK_PASSWD             => 'Mikrotik пароль',
            TP::F_MIK_FTP_IP             => 'FTP IP',
            TP::F_MIK_FTP_PORT           => 'FTP порт',
            TP::F_MIK_FTP_LOGIN          => 'FTP логин',
            TP::F_MIK_FTP_PASSWD         => 'FTP пароль',
            TP::F_MIK_FTP_FOLDER         => 'FTP папка',
            TP::F_MIK_FTP_GETPATH        => 'FTP путь'
        ]);

        // обязательные
        $v->rule('required', [
            TP::F_STATUS,
            TP::F_DELETED,
            TP::F_IS_MANAGED,
            TP::F_TITLE,
        ]);

        // целые числа
        $v->rule('integer', [
            TP::F_STATUS,
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
            TP::F_MIK_IP,
            TP::F_MIK_FTP_IP
        ]);

        // URL
        $v->rule('url', [
            TP::F_URL,
            TP::F_URL_ZABBIX,
            // TP::F_WEB_MANAGEMENT,
        ]);

        // статус (0/1)
        $v->rule('in', TP::F_STATUS, [0, 1]);
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



}