-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Сен 06 2025 г., 10:20
-- Версия сервера: 8.0.43
-- Версия PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `billing`
--
CREATE DATABASE IF NOT EXISTS `billing` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `billing`;

-- --------------------------------------------------------

--
-- Структура таблицы `abons`
--

DROP TABLE IF EXISTS `abons`;
CREATE TABLE `abons` (
  `id` int UNSIGNED NOT NULL,
  `id_hash` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'хэш эквивалент для ID',
  `user_id` int UNSIGNED NOT NULL COMMENT 'Данные пользователя',
  `address` tinytext NOT NULL COMMENT 'Адрес подключения',
  `coord_gmap` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Координаты на Гугл-карте',
  `is_payer` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Абонент "плательщик", т.е. пользуется услугой и оплачивает её (должен оплачивать)',
  `date_join` int NOT NULL COMMENT 'Дата подключения',
  `comments` text NOT NULL COMMENT 'Примечания по абоненту',
  `duty_max_warn` int NOT NULL DEFAULT '3' COMMENT 'Количество оплаченных дней, при пересечении которых отправлять предупреждение абоненту об оплате',
  `duty_max_off` int NOT NULL DEFAULT '-15' COMMENT 'Количество оплаченных дней, при пересечении которых отключать услуги',
  `duty_auto_off` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Автоматически отключать/ставить на паузу абонента при пересечении значения duty_max_off',
  `duty_wait_days` int NOT NULL DEFAULT '0' COMMENT 'Количество дней ожидания перед выключением (для ожидания оплаты при ручном включении после автоотключения)',
  `created_uid` int UNSIGNED DEFAULT NULL COMMENT 'Юзер, создавший запись',
  `created_date` int NOT NULL DEFAULT '0' COMMENT 'Дата создания записис в базе',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал заппись',
  `creation_date` int DEFAULT '0' COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int NOT NULL DEFAULT '0' COMMENT 'Дата изменения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список абонентов';

--
-- ССЫЛКИ ТАБЛИЦЫ `abons`:
--   `user_id`
--       `users` -> `id`
--   `created_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `abon_rest`
--

DROP TABLE IF EXISTS `abon_rest`;
CREATE TABLE `abon_rest` (
  `abon_id` int UNSIGNED NOT NULL COMMENT 'ID абонента',
  `sum_pay` float NOT NULL DEFAULT '0' COMMENT 'Сумма платежей и внесений на ЛС',
  `sum_cost` float NOT NULL DEFAULT '0' COMMENT 'Сумма начислений за услуги price_apply',
  `sum_PPMA` double NOT NULL DEFAULT '0' COMMENT 'PPMA - Price Per Month Active',
  `sum_PPDA` double NOT NULL DEFAULT '0' COMMENT 'PPDA - Price Per Day Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `abon_rest`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `adm_module_list`
--

DROP TABLE IF EXISTS `adm_module_list`;
CREATE TABLE `adm_module_list` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID административного модуля',
  `uk_title` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'uk - Ім''я модуля',
  `ru_title` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ru - Имя модуля',
  `en_title` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'en - Module name',
  `uk_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'uk - Опис модуля',
  `ru_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'ru - Описание модуля',
  `en_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'en - Description of the module',
  `route` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Маршрут фреймворка сайта "Контроллер/Действие"',
  `api` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Программный доступ к модулю',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `creation_date` int DEFAULT NULL COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int DEFAULT NULL COMMENT 'Дата изменения записи в базе'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список административных модулей';

--
-- ССЫЛКИ ТАБЛИЦЫ `adm_module_list`:
--   `creation_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `adm_role_list`
--

DROP TABLE IF EXISTS `adm_role_list`;
CREATE TABLE `adm_role_list` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID административной группы',
  `uk_title` text COMMENT 'Название административной группы',
  `ru_title` text COMMENT 'Название административной группы',
  `en_title` text COMMENT 'Название административной группы',
  `uk_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание административной группы',
  `ru_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание административной группы',
  `en_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание административной группы',
  `creation_uid` int UNSIGNED NOT NULL DEFAULT '0',
  `creation_date` int NOT NULL DEFAULT '0',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0',
  `modified_date` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список названий административных групп доступа';

--
-- ССЫЛКИ ТАБЛИЦЫ `adm_role_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `adm_role_module_permissions`
--

DROP TABLE IF EXISTS `adm_role_module_permissions`;
CREATE TABLE `adm_role_module_permissions` (
  `role_id` int UNSIGNED NOT NULL COMMENT 'ID административной группы',
  `module_id` int UNSIGNED NOT NULL COMMENT 'ID исполняемого модуля',
  `permissions` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 [0000] - Нет доступа | \r\n1 [0001] - Просмотр | \r\n2 [0010] - Изменение | \r\n4 [0100] - Добавление | \r\n8 [1000] - Удаление'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Таблица связи. Принадлежность модулей группам';

--
-- ССЫЛКИ ТАБЛИЦЫ `adm_role_module_permissions`:
--   `module_id`
--       `adm_module_list` -> `id`
--   `role_id`
--       `adm_role_list` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `adm_user_role`
--

DROP TABLE IF EXISTS `adm_user_role`;
CREATE TABLE `adm_user_role` (
  `user_id` int UNSIGNED NOT NULL COMMENT 'ID пользователя',
  `role_id` int UNSIGNED NOT NULL COMMENT 'ID административной группы'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Принадлежность пользователей к группам';

--
-- ССЫЛКИ ТАБЛИЦЫ `adm_user_role`:
--   `role_id`
--       `adm_role_list` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `adr_countries`
--

DROP TABLE IF EXISTS `adr_countries`;
CREATE TABLE `adr_countries` (
  `id` int UNSIGNED NOT NULL COMMENT 'ИД государства только в этой базе',
  `name_ru` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Назва російською мовою',
  `name_ua` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Назва українською мовою',
  `name_en` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Назва англійською мовою',
  `name_self` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Самоназва держави',
  `language` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Оснвна мова держави',
  `iso_no` int DEFAULT NULL COMMENT 'КОД ISO 3166-1 (числовой)',
  `iso_a2` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'КОД ISO 3166-1 (Альфа-2)',
  `iso_a3` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'КОД ISO 3166-1 (альфа-3)',
  `ccTLD` tinytext COMMENT 'Домен верхнего уровня с кодом страны (ccTLD)',
  `Emoji` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Флаг',
  `phone_code` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Телефонные коды',
  `creation_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата создания записи',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `modified_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата изменения записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `adr_countries`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `adr_district`
--

DROP TABLE IF EXISTS `adr_district`;
CREATE TABLE `adr_district` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID района в этой базе',
  `id_region` int UNSIGNED NOT NULL COMMENT 'ID региона/области',
  `id_town` int UNSIGNED NOT NULL COMMENT 'ID райцентра',
  `name_ru` tinytext COMMENT 'Название района по русски',
  `name_ua` tinytext COMMENT 'Назва українською',
  `description` text COMMENT 'Описание и доп. Информация.',
  `url_flag` varchar(200) DEFAULT NULL COMMENT 'Флаг',
  `url_coat_of_arms` varchar(200) DEFAULT NULL COMMENT 'Герб',
  `creation_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата создания записи',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `modified_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата изменения записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Райони. Территории вхоящие в регионы/области';

--
-- ССЫЛКИ ТАБЛИЦЫ `adr_district`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `adr_regions`
--

DROP TABLE IF EXISTS `adr_regions`;
CREATE TABLE `adr_regions` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID региона/области. Внутренний номер только для этой базы ',
  `id_country` int UNSIGNED NOT NULL COMMENT 'ID країни, до якої належить регіон',
  `name_ru` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Название на русском',
  `name_ua` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Назва українською',
  `town_ru` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Столица на русском',
  `town_ua` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Столиця українською',
  `ISO_3166_2` tinytext COMMENT 'Код ISO 3166-2',
  `url_flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'URL ссылка на файл Флага',
  `url_coat_of_arms` varchar(255) DEFAULT NULL COMMENT 'URL ссылка на файл Герба',
  `creation_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата создания записи',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `modified_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата изменения запис',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Регионы/области';

--
-- ССЫЛКИ ТАБЛИЦЫ `adr_regions`:
--   `creation_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--   `id_country`
--       `adr_countries` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bank_p24_acc`
--

DROP TABLE IF EXISTS `bank_p24_acc`;
CREATE TABLE `bank_p24_acc` (
  `id` int NOT NULL,
  `ppp_id` int NOT NULL COMMENT 'ID ППП, которому относится карта',
  `payment_id` int NOT NULL COMMENT 'ID абонентского платежа, на которого распределён этот платёж',
  `AUT_MY_CRF` int NOT NULL COMMENT 'ЄДРПОУ отримувача',
  `AUT_MY_MFO` int NOT NULL COMMENT 'МФО отримувача',
  `AUT_MY_ACC` int NOT NULL COMMENT 'Рахунок отримувача',
  `AUT_MY_NAM` tinytext NOT NULL COMMENT 'Назва отримувача',
  `AUT_MY_MFO_NAME` tinytext NOT NULL COMMENT 'Банк отримувача',
  `AUT_MY_MFO_CITY` tinytext NOT NULL COMMENT 'Назва міста банка',
  `AUT_CNTR_CRF` int NOT NULL COMMENT 'ЄДРПОУ контрагента',
  `AUT_CNTR_MFO` int NOT NULL COMMENT 'МФО контрагента',
  `AUT_CNTR_ACC` int NOT NULL COMMENT 'Рахунок контрагента',
  `AUT_CNTR_NAM` tinytext NOT NULL COMMENT 'Назва контрагента',
  `AUT_CNTR_MFO_NAME` tinytext NOT NULL COMMENT 'Назва банка контрагента',
  `AUT_CNTR_MFO_CITY` tinytext NOT NULL COMMENT 'Назва міста банка',
  `CCY` tinytext NOT NULL COMMENT 'Валюта',
  `FL_REAL` char(1) NOT NULL COMMENT 'Ознака реальності проводки(r,i)',
  `PR_PR` char(1) NOT NULL COMMENT 'Стан p-проводиться, t-сторнирована, r-проведена, n-забракована',
  `DOC_TYP` char(1) NOT NULL COMMENT 'Тип пл. документа',
  `NUM_DOC` tinytext NOT NULL COMMENT 'Номер документа',
  `DAT_KL` tinytext NOT NULL COMMENT '"07.01.2020",//Клієнтська дата',
  `DAT_OD` tinytext NOT NULL COMMENT '"07.01.2020",//Дата валютування',
  `OSND` text NOT NULL COMMENT 'Підстава  платежу',
  `SUM` double NOT NULL COMMENT 'Сума',
  `SUM_E` double NOT NULL COMMENT 'Сума',
  `REF` tinytext NOT NULL COMMENT 'Референс проводки',
  `REFN` tinytext NOT NULL COMMENT '№ п/п внутри проводки',
  `TIM_P` tinytext NOT NULL COMMENT '"02:58",//Час проводки',
  `DATE_TIME_DAT_OD_TIM_P` tinytext NOT NULL COMMENT '"07.01.2020 02:58:00"',
  `ID_TRANSACTION` int NOT NULL COMMENT 'ID транзакції',
  `TRANTYPE` char(1) NOT NULL COMMENT 'Тип транзакції дебет/кредит (D, C)',
  `DLR` tinytext NOT NULL COMMENT 'Референс платежу сервісу через який створювали платіж (payment_pack_ref - при створенні платежу через АПИ Автоклієнт',
  `TECHNICAL_TRANSACTION_ID` tinytext NOT NULL COMMENT '"557091731_online"'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `bank_p24_acc`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `bank_p24_card`
--

DROP TABLE IF EXISTS `bank_p24_card`;
CREATE TABLE `bank_p24_card` (
  `id` int NOT NULL,
  `payment_id` int NOT NULL DEFAULT '0' COMMENT 'ID абонентского платежа, на которого распределён этот платёж',
  `ppp_id` int NOT NULL COMMENT 'ID ППП, которому относится карта',
  `appcode` int NOT NULL COMMENT 'ID, Банковский код операции',
  `datetime` int NOT NULL COMMENT 'Дата-время операции',
  `amount` double NOT NULL COMMENT 'Сумма транзакции',
  `cardamount` double NOT NULL COMMENT 'Сумма транзакции по карте, включая комиссию',
  `rest` double NOT NULL COMMENT 'Остаток на карте',
  `terminal` tinytext NOT NULL COMMENT 'Терминал, через который осуществлён платёж',
  `description` text NOT NULL COMMENT 'Описание платежа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `bank_p24_card`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `cards`
--

DROP TABLE IF EXISTS `cards`;
CREATE TABLE `cards` (
  `id` int UNSIGNED NOT NULL,
  `card_pool_id` int UNSIGNED NOT NULL COMMENT ' код пула',
  `card_secret` varchar(20) NOT NULL COMMENT '1234 567 890',
  `abon_id` int UNSIGNED DEFAULT NULL COMMENT 'Абонент, который активировал карту (пополнился)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- ССЫЛКИ ТАБЛИЦЫ `cards`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `cards_on_ppp`
--

DROP TABLE IF EXISTS `cards_on_ppp`;
CREATE TABLE `cards_on_ppp` (
  `id` int UNSIGNED NOT NULL,
  `card_one_id` int UNSIGNED NOT NULL COMMENT 'переданная карта',
  `card_pool_id` int UNSIGNED NOT NULL COMMENT 'переданный пул карт',
  `ppp_id` int UNSIGNED NOT NULL COMMENT 'payments_source -- там списки ППП'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Карты или пулы переданные на соответствующие ППП';

--
-- ССЫЛКИ ТАБЛИЦЫ `cards_on_ppp`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `cards_pools`
--

DROP TABLE IF EXISTS `cards_pools`;
CREATE TABLE `cards_pools` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(10) NOT NULL COMMENT 'Название пула',
  `card_pay` int NOT NULL COMMENT 'Сумма пополнения',
  `date_created` int DEFAULT NULL COMMENT 'Дата генерации пула',
  `date_expire` int NOT NULL COMMENT 'Дата окончания срока действия карточек пула'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Пулы выучеров (карточек пополнения)';

--
-- ССЫЛКИ ТАБЛИЦЫ `cards_pools`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `devices_list`
--

DROP TABLE IF EXISTS `devices_list`;
CREATE TABLE `devices_list` (
  `id` int UNSIGNED NOT NULL,
  `tp_id` int UNSIGNED NOT NULL COMMENT 'ID ТП на которой включено устройство',
  `type_id` int UNSIGNED NOT NULL COMMENT 'ID типа/класса устройства.',
  `title` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Название устройства',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Описание оборудования',
  `placed_on` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Место/Адрес фактического размещения оборудования',
  `is_ip` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Это устройство имеет IP-адрес',
  `mac` tinytext COMMENT 'MAC-адрес устройства',
  `ip` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'IP адрес устройства, основной.',
  `ip_ext` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Внешний IP адрес устройства в сети',
  `ip_dev` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'IP адрес на устройстве',
  `url_http` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Доступ к устройству по http протоколу',
  `url_https` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Доступ к устройству по https протоколу',
  `url_winbox` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Доступ к устройству mikrotik через программу winbox',
  `url_ssh` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Доступ к устройству по ssh протоколу',
  `url_zabbix` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'URL страницы в системе мониторинга zabbix относящейся к этому устройству',
  `is_abon_dev` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Это устройство абонента',
  `barcode` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Штрих-код устройства',
  `qrcode` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'QR-код относящийся к устройству',
  `login` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Логин доступа на устройство',
  `pass` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Пароль доступа на устройство',
  `creation_date` int NOT NULL COMMENT 'Дата создания записи',
  `creation_uid` int UNSIGNED NOT NULL COMMENT 'Кто создал запись',
  `modified_date` int DEFAULT NULL COMMENT 'Дата изменения записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Список [активных] устройств на ТП';

--
-- ССЫЛКИ ТАБЛИЦЫ `devices_list`:
--   `tp_id`
--       `tp_list` -> `id`
--   `type_id`
--       `devices_types` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `devices_types`
--

DROP TABLE IF EXISTS `devices_types`;
CREATE TABLE `devices_types` (
  `id` int UNSIGNED NOT NULL,
  `title` tinytext NOT NULL COMMENT 'Тип/класс устройства',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Описание типа/класса устройства',
  `icon` tinytext COMMENT 'Иконка типа устройства',
  `creation_date` int NOT NULL COMMENT 'Дата создания записи',
  `creation_uid` int UNSIGNED NOT NULL COMMENT 'Кто создал запись',
  `modified_date` int DEFAULT NULL COMMENT 'Дата изменения записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Типы устройств';

--
-- ССЫЛКИ ТАБЛИЦЫ `devices_types`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int NOT NULL COMMENT 'ID новости',
  `author_id` int UNSIGNED NOT NULL COMMENT 'ID автора публикации',
  `date_creation` int DEFAULT (unix_timestamp()) COMMENT 'Дата создания новости',
  `date_publication` int DEFAULT NULL COMMENT 'Дата публикации новости',
  `date_expiration` int DEFAULT NULL COMMENT 'Дата истечения срока действия новости',
  `auto_visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Делается автоматически видимым при наступлении даты отображения',
  `is_visible` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отображаемый для всех',
  `auto_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Автоматически помечать удалённым по истечении срока действия',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отмеченный как удалённый. Не отображается в списках, кроме админки',
  `ru_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Заголовок новости',
  `uk_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Заголовок новини',
  `en_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - News headline',
  `ru_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Описание новости',
  `uk_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Опис новини',
  `en_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - News Description',
  `ru_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Текст новости',
  `uk_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Текст новини',
  `en_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - News text',
  `in_view_title` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Отображать поле _title при просмотре документа',
  `in_view_description` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Отображать поле _describtion при просмотре документа',
  `in_view_text` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Отображать поле _text при просмотре документа',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал заппись',
  `creation_date` int UNSIGNED DEFAULT (unix_timestamp()) COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата изменения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Документы, касающиеся услуг и технических изменений';

--
-- ССЫЛКИ ТАБЛИЦЫ `documents`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `files_list`
--

DROP TABLE IF EXISTS `files_list`;
CREATE TABLE `files_list` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL COMMENT 'Кто загрузил файл',
  `is_public` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = доступен напрямую, 0 = только через контроллер',
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Имя файла при загрузке',
  `local_pathname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Полный путь внутри проекта (uploads/... или storage/...)',
  `sub_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Название типа подпапки для публичных файлов, характеризующая группу или тип файла (Icon, Image, Doc, Media)',
  `mime` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'MIME-тип (image/png, application/pdf и т.п.)',
  `size` bigint UNSIGNED DEFAULT NULL COMMENT 'Размер в байтах',
  `uk_description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'uk - Описе файлу',
  `ru_description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'ru - Описание файла',
  `en_description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'en - File Description',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал заппись',
  `creation_date` int UNSIGNED DEFAULT (unix_timestamp()) COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата изменения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Файлы пользователей';

--
-- ССЫЛКИ ТАБЛИЦЫ `files_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `firm_list`
--

DROP TABLE IF EXISTS `firm_list`;
CREATE TABLE `firm_list` (
  `id` int UNSIGNED NOT NULL,
  `has_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Предприятие "активно", в списках выписки документов',
  `has_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'запись о предприятии считается удалённой',
  `has_agent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'предприятие-агент (наше)',
  `has_client` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'предприятие-клиент',
  `has_all_visible` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Видимое для всех',
  `has_all_linking` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Разрешить подключать всем',
  `name_short` tinytext NOT NULL COMMENT 'Краткое название предприятия',
  `name_long` tinytext NOT NULL COMMENT 'Полное название предприятия',
  `name_title` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Название сети для почтовой рассылки',
  `manager_job_title` tinytext COMMENT 'Должность ответственного лица',
  `manager_name_short` tinytext COMMENT 'ФИО',
  `manager_name_long` tinytext COMMENT 'Фамилия Имя Отчество',
  `cod_EDRPOU` tinytext COMMENT 'ЕДРПОУ - Єдиний державний реєстр підприємств та організацій України',
  `cod_IPN` tinytext COMMENT 'ИПН',
  `registration` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Данные о регистрации',
  `address_registration` tinytext COMMENT 'Адрес регистрации юридического лица',
  `address_office_full` tinytext COMMENT 'Адрес офиса',
  `address_post_person` tinytext COMMENT 'Від кого:  Іванов Іван Іванович',
  `address_post_index` tinytext COMMENT 'адрес почтовый',
  `address_post_ul` tinytext COMMENT 'Улица',
  `address_post_dom` tinytext COMMENT 'Дім _____ корп. _____ стр. _____ кв. _____',
  `address_post_sity` tinytext COMMENT 'Город',
  `address_post_region` tinytext COMMENT 'Республіка, край, область',
  `address_post_country` tinytext COMMENT 'Страна',
  `address_office_courier` tinytext COMMENT 'адрес доставки дл курьеров',
  `office_phones` tinytext COMMENT 'Контактные телефоны предприятия (бухгалтерии)',
  `bank_IBAN` tinytext COMMENT 'Банковский р/с IBAN',
  `bank_name` tinytext COMMENT 'название банка',
  `ppp_default_id` int UNSIGNED DEFAULT NULL COMMENT 'ППП <<по умолчанию>> используемый в форме внесения платежа.',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `creation_date` int NOT NULL DEFAULT '0' COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'ID пользователя, который изменил запись',
  `modified_date` int NOT NULL COMMENT 'Дата-время модификации записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список предприятий с реквизитами';

--
-- ССЫЛКИ ТАБЛИЦЫ `firm_list`:
--   `ppp_default_id`
--       `ppp_list` -> `id`
--   `modified_uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `invest_groups_list`
--

DROP TABLE IF EXISTS `invest_groups_list`;
CREATE TABLE `invest_groups_list` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(25) DEFAULT NULL,
  `name` varchar(25) NOT NULL,
  `payments_collector_id` int NOT NULL COMMENT 'ID владельца, принимающего платежи',
  `rko_percent` float NOT NULL DEFAULT '0' COMMENT 'Процент от входящей суммы на расчетно-кассовое обслуживание',
  `rko_fixed` float NOT NULL DEFAULT '0' COMMENT 'Фиксированная сумма расчётно-кассового обслуживания',
  `created_date` int NOT NULL COMMENT 'дата создания записи',
  `created_owner_id` int UNSIGNED NOT NULL COMMENT 'ID владельца, который создал запись',
  `modified_date` int NOT NULL COMMENT 'Дата-время модификации записи',
  `modified_owner_id` int UNSIGNED NOT NULL COMMENT 'ID владельца, который изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список групп совладельцев';

--
-- ССЫЛКИ ТАБЛИЦЫ `invest_groups_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `invest_ids_list`
--

DROP TABLE IF EXISTS `invest_ids_list`;
CREATE TABLE `invest_ids_list` (
  `id` int NOT NULL,
  `title` varchar(25) DEFAULT NULL,
  `name` varchar(25) NOT NULL COMMENT 'Имя или Название предприятия',
  `contacts` text COMMENT 'Контактные данные',
  `name_f` varchar(25) DEFAULT NULL,
  `name_i` varchar(25) DEFAULT NULL,
  `name_o` varchar(25) DEFAULT NULL,
  `bank1` text COMMENT 'Р/С в банке (1)',
  `bank2` text COMMENT 'Р/С в банке (2)',
  `card1` text COMMENT 'Банковская карта (1)',
  `card2` text COMMENT 'Банковская карта (2)',
  `creation_date` int NOT NULL COMMENT 'Дата-время создания записи',
  `creation_owner_id` int NOT NULL COMMENT 'ID владельца, который создал запись',
  `modified_date` int NOT NULL COMMENT 'Дата-время модификации записи',
  `modified_owner_id` int NOT NULL COMMENT 'ID владельца, который изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Карточки владельцев тех. площадок';

--
-- ССЫЛКИ ТАБЛИЦЫ `invest_ids_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `invest_parts_list`
--

DROP TABLE IF EXISTS `invest_parts_list`;
CREATE TABLE `invest_parts_list` (
  `id` int NOT NULL COMMENT 'ID владельца',
  `title` varchar(25) NOT NULL COMMENT 'Условнок название',
  `invest_group_id` int NOT NULL COMMENT 'ID группы совладельцев',
  `invest_id` int NOT NULL COMMENT 'ID владельца',
  `percent` double NOT NULL DEFAULT '0' COMMENT 'Процентная доля деления прибыли',
  `sum_invest` double DEFAULT '0' COMMENT 'Сумма инвестиций в группу для расчёта процентной доли',
  `creation_date` int NOT NULL,
  `creation_owner_id` int NOT NULL,
  `modified_date` int NOT NULL,
  `modified_owner_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Доли совладельцев тех. площадок';

--
-- ССЫЛКИ ТАБЛИЦЫ `invest_parts_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `mail_list`
--

DROP TABLE IF EXISTS `mail_list`;
CREATE TABLE `mail_list` (
  `id` int UNSIGNED NOT NULL,
  `abon_id` int UNSIGNED NOT NULL COMMENT 'ID абонента',
  `email` tinytext NOT NULL COMMENT 'e-mail на который фактически было отправлено письмо',
  `subject` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Тема письма',
  `body` text NOT NULL COMMENT 'Тело письма',
  `send_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата отправки письма',
  `send_uid` int UNSIGNED NOT NULL COMMENT 'ID пользователя от имени которого отправлено письмо'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `mail_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int NOT NULL COMMENT 'ID элемента меню',
  `parent_id` int DEFAULT NULL COMMENT 'ID родительского элемента меню',
  `module_id` int DEFAULT NULL COMMENT 'ID модуля которому относится пункт меню. Для подключения пункта меню в зависимости от прав доступа',
  `anon_visible` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Показывать для неавторизованных пользователей',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Пункт меню отображается',
  `order_num` int NOT NULL DEFAULT '0' COMMENT 'Порядковый номер элемента меню при отображении',
  `ru_title` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Название элемента меню',
  `uk_title` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Назва елемента меню',
  `en_title` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - The name of the menu element',
  `ru_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Описание пункта меню. Для использования в параметре title=''<description>''',
  `uk_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Опис пункту меню. Для використання у параметрі title=''<description>''',
  `en_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - Description of the menu item. For use in parameter title=''<description>''',
  `url` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'URL для href=<URL>',
  `is_widget` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Если установлено, то в URL записано имя класса виджета',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `creation_date` int DEFAULT NULL COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int DEFAULT NULL COMMENT 'Дата изменения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Меню сайта';

--
-- ССЫЛКИ ТАБЛИЦЫ `menu`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `nat_forward`
--

DROP TABLE IF EXISTS `nat_forward`;
CREATE TABLE `nat_forward` (
  `id` int UNSIGNED NOT NULL,
  `price_apply_id` int UNSIGNED NOT NULL COMMENT 'прикреплённый прайс к которому относится данный проброс',
  `proto_id` int UNSIGNED NOT NULL DEFAULT '6' COMMENT 'ID IP Протокола (tcp/udp)',
  `port_wan` smallint UNSIGNED DEFAULT NULL COMMENT 'Порт принимаемый на внешнем интерфейсе для проброса',
  `port_lan` smallint UNSIGNED DEFAULT NULL COMMENT 'Порт для проброса на адрес клиента',
  `description` mediumtext COMMENT 'Описание проброса или дополнительная информация'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Пробросы портов для абонента';

--
-- ССЫЛКИ ТАБЛИЦЫ `nat_forward`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int NOT NULL COMMENT 'ID новости',
  `author_id` int UNSIGNED NOT NULL COMMENT 'ID автора публикации',
  `date_creation` int DEFAULT (unix_timestamp()) COMMENT 'Дата создания новости',
  `date_publication` int DEFAULT NULL COMMENT 'Дата публикации новости',
  `date_expiration` int DEFAULT NULL COMMENT 'Дата истечения срока действия новости',
  `auto_visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Делается автоматически видимым при наступлении даты отображения',
  `is_visible` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отображаемый для всех',
  `auto_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Автоматически помечать удалённым по истечении срока действия',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отмеченный как удалённый. Не отображается в списках, кроме админки',
  `ru_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Заголовок новости',
  `uk_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Заголовок новини',
  `en_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - News headline',
  `ru_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Описание новости',
  `uk_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Опис новини',
  `en_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - News Description',
  `ru_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'ru - Текст новости',
  `uk_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'uk - Текст новини',
  `en_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'en - News text',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал заппись',
  `creation_date` int UNSIGNED DEFAULT (unix_timestamp()) COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int UNSIGNED DEFAULT NULL COMMENT 'Дата изменения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Новости, касающиеся услуг и технических изменений';

--
-- ССЫЛКИ ТАБЛИЦЫ `news`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int UNSIGNED NOT NULL,
  `agent_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID того, кто внёс запись',
  `abon_id` int UNSIGNED NOT NULL COMMENT 'Абонент, на которого зачисляется поалёж',
  `pay_fakt` float NOT NULL DEFAULT '0' COMMENT 'Фактическая сумма пришедшая на счёт',
  `pay` float NOT NULL DEFAULT '0' COMMENT 'Сумма платежа вносимая на ЛС',
  `pay_date` int NOT NULL COMMENT 'Дата платежа',
  `pay_bank_no` tinytext CHARACTER SET cp1251 COLLATE cp1251_bin COMMENT 'Банковский номер операции',
  `pay_type_id` int UNSIGNED NOT NULL COMMENT 'ИД Типа платежа',
  `pay_ppp_id` int UNSIGNED NOT NULL COMMENT 'ППП',
  `pay_sourse_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'На какой счёт пришёл платёж',
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Описание платежа',
  `created_date` int NOT NULL DEFAULT '0' COMMENT 'Юзер, создавший запись',
  `created_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата создания записис в базе',
  `modified_date` int NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата изменения записи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Платежи';

--
-- ССЫЛКИ ТАБЛИЦЫ `payments`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `payments_calculates`
--

DROP TABLE IF EXISTS `payments_calculates`;
CREATE TABLE `payments_calculates` (
  `user_id` int NOT NULL COMMENT 'ID Владельца',
  `payment_type_id` int NOT NULL COMMENT 'Тип считаемого платежа',
  `payment_source_id` int NOT NULL COMMENT 'Источник считаемого платежа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Параметры расчёта средств для владельцев';

--
-- ССЫЛКИ ТАБЛИЦЫ `payments_calculates`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `payments_sources`
--

DROP TABLE IF EXISTS `payments_sources`;
CREATE TABLE `payments_sources` (
  `id` int UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активен ли источник поступления средств',
  `title` varchar(30) NOT NULL COMMENT 'Куда пришёл платёж: р/с, карта',
  `description` tinytext NOT NULL,
  `agent_id` int UNSIGNED DEFAULT NULL COMMENT 'ИД Агента (пользователя, который получил и внес деньги от абонента',
  `owner_id` int UNSIGNED DEFAULT NULL COMMENT 'Владелец счёта, на который приходят деньги'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Откуда приходят платежи (через какие системы, точки продаж)';

--
-- ССЫЛКИ ТАБЛИЦЫ `payments_sources`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `payments_types`
--

DROP TABLE IF EXISTS `payments_types`;
CREATE TABLE `payments_types` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(40) NOT NULL COMMENT 'Тип платежа',
  `description` tinytext NOT NULL COMMENT 'Описание типа платежа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Типы платежей';

--
-- ССЫЛКИ ТАБЛИЦЫ `payments_types`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `phone_numbers`
--

DROP TABLE IF EXISTS `phone_numbers`;
CREATE TABLE `phone_numbers` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `phone_title` varchar(50) NOT NULL,
  `phone_number` varchar(60) NOT NULL,
  `is_deleted` smallint NOT NULL DEFAULT '0' COMMENT 'Контакт помечен для удаления',
  `creation_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Юзер, создавший запись',
  `creation_date` int NOT NULL DEFAULT '0' COMMENT 'Дата создания записис в базе',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись',
  `modified_date` int NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Списки номеров телефонов, привязанные к пользователям';

--
-- ССЫЛКИ ТАБЛИЦЫ `phone_numbers`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `ppp_list`
--

DROP TABLE IF EXISTS `ppp_list`;
CREATE TABLE `ppp_list` (
  `id` int UNSIGNED NOT NULL,
  `firm_id` int UNSIGNED NOT NULL COMMENT 'ID предприятия',
  `title` tinytext NOT NULL COMMENT 'Название источника приема платежей',
  `owner_id` int UNSIGNED DEFAULT NULL COMMENT 'ID фактического, юридического,  владельца счета/кассы, принимающего платежи',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Активен ли ППП',
  `abon_payments` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отображать ли этот ППП в списке ППП для приёма платежей от абонентов.',
  `type_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Тип ППП: 0-хз, 1-Банк, 2-Карта, 3-Терминал',
  `number_prefix` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Префиксный текст дщля описания при публикации',
  `number` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Номер счета, карты...',
  `number_info` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Дополнительные данные для счета, ФИО, ИНН...',
  `number_purpose` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Назначение платежа',
  `number_comment` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Коментарий к платежу',
  `sms_pay_info` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Текст для смс при отправке абоненту способов оплаты. Может собержать {PORT} {LOGIN} {SUM}',
  `support_phones` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Телефоны технической поддержки',
  `rko_percent` double NOT NULL DEFAULT '0' COMMENT 'Расчётно-кассовое обслуживание. Процентная ставка от оборота',
  `rko_fixed_pm` double NOT NULL DEFAULT '0' COMMENT 'Расчётно-кассовое обслуживание. Месячный платёж.',
  `tax_percent` double NOT NULL DEFAULT '0' COMMENT 'Налог. Процент',
  `tax_fixed_pm` double NOT NULL DEFAULT '0' COMMENT 'Налог. Фиксированный ежемесячный.',
  `cashing_commission` double NOT NULL DEFAULT '0' COMMENT 'Комиссия при обналичивании',
  `api_type` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'p24_card, p24_acc, p24_liqpay, p24_p24pay, p24_manual',
  `api_id` tinytext COMMENT 'merchant_id | client_id',
  `api_pass` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'merchant_pass | client_token',
  `api_url` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'api_url',
  `api_auto_pay_registration` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Автоматически регистрировать опознпнные плаьежи',
  `api_auto_retunt_comission` double NOT NULL DEFAULT '0' COMMENT 'Если установлено, то абоненту округляется вверх комиссия до указанного коэффициента',
  `api_liqpay_public` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'liqpay_public',
  `api_liqpay_private` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'liqpay_private',
  `api_liqpay_url` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'liqpay_url',
  `api_liqpay_return_comission` double NOT NULL DEFAULT '0' COMMENT 'Комиссия liqpay, возвращаемая абоненту',
  `api_24pay_ident` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Идентификатор компании в базе Приват24 для открытия формы платежа',
  `api_24pay_url` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'URL-адрес формы для оплаты',
  `creation_date` int NOT NULL,
  `creation_uid` int UNSIGNED DEFAULT NULL,
  `modified_date` int DEFAULT NULL COMMENT 'Дата-время модификации записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'ID пользователя, который изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Доступные пункты приёма платежей, р/с, карты и прочее';

--
-- ССЫЛКИ ТАБЛИЦЫ `ppp_list`:
--   `type_id`
--       `ppp_types` -> `id`
--   `firm_id`
--       `firm_list` -> `id`
--   `owner_id`
--       `users` -> `id`
--   `creation_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ppp_types`
--

DROP TABLE IF EXISTS `ppp_types`;
CREATE TABLE `ppp_types` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID типа ППП',
  `title` tinytext NOT NULL COMMENT 'Название типа ППП',
  `description` mediumtext NOT NULL COMMENT 'Описание ППП',
  `created_date` int NOT NULL COMMENT 'Дата-время создания записи',
  `created_uid` int UNSIGNED NOT NULL COMMENT 'ID пользователя, который создал запись',
  `modified_date` int NOT NULL COMMENT 'Дата-время модификации записи',
  `modified_uid` int UNSIGNED NOT NULL COMMENT 'ID пользователя, который изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Типы ППП';

--
-- ССЫЛКИ ТАБЛИЦЫ `ppp_types`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `prices`
--

DROP TABLE IF EXISTS `prices`;
CREATE TABLE `prices` (
  `id` int UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Прайс активен, т.е. доступен к назначению.',
  `title` varchar(64) DEFAULT NULL,
  `pay_per_day` float NOT NULL DEFAULT '0',
  `pay_per_month` float NOT NULL DEFAULT '0',
  `description` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание тарифного пакета',
  `created_date_` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата добавления прайса',
  `creation_date` int DEFAULT NULL COMMENT 'Дата создания записи',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'ID того, кто добавил прайс',
  `modified_date` int DEFAULT NULL COMMENT 'Дата-время изменения  записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- ССЫЛКИ ТАБЛИЦЫ `prices`:
--   `creation_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `prices_apply`
--

DROP TABLE IF EXISTS `prices_apply`;
CREATE TABLE `prices_apply` (
  `id` int UNSIGNED NOT NULL,
  `abon_id` int UNSIGNED NOT NULL COMMENT 'Абонент, которому назначен прайс',
  `prices_id` int UNSIGNED NOT NULL COMMENT 'Активированный прайс',
  `date_start` int DEFAULT NULL COMMENT 'Дата активации прайса',
  `date_end` int DEFAULT NULL COMMENT 'Дата отключения прайса',
  `price_closed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Прайс закрыт. Не только указана конечная дата, но она уже прошла и установлена стоимость прайсового фрагмента. Если конечная дата не прошла, то прайс открыт.',
  `net_name` varchar(120) NOT NULL COMMENT 'Сетевое имя абонентского устройсва',
  `net_ip_service` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'IP услуга, т.е. в параметрах имеет IP-адрес',
  `net_on_abon_ip` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'IP на оборудовании абонента, который проходит мимо микротика',
  `net_on_abon_mask` varchar(20) DEFAULT NULL COMMENT 'Сетевая маска для настройки IP-адреса на оборудовании абонента мимо микротика',
  `net_on_abon_gate` varchar(20) DEFAULT NULL COMMENT 'Сетевой шлюз для настройки IP-адреса на оборудовании абонента мимо микротика',
  `net_nat11` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'IP-адрес для проброса NAT 1:1',
  `net_ip` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'IP-адрес',
  `net_ip_trusted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'IP адрес записывается в таблицу "trusted"',
  `net_mask` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '255.255.255.0' COMMENT 'Маска подсети',
  `net_gateway` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '.254' COMMENT 'Шлюз по умолчанию',
  `net_dns1` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '8.8.8.8' COMMENT 'Первичный ДНС',
  `net_dns2` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '8.8.4.4' COMMENT 'Вторичный ДНС',
  `net_mac` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'MAC абонентского устройства',
  `net_router_id` int UNSIGNED DEFAULT '0' COMMENT 'ID маршрутизатора к которому подключён абонент',
  `coord_gmap` tinytext COMMENT 'Координаты точки предоставления услуги на Гугл-карте',
  `net_ip_status` int NOT NULL DEFAULT '0' COMMENT '0 - ничего не делалли; 1 - ИП запись создана; 2 - ИП заморожен; 3 - ИП активен; 4 - ИП удалён.',
  `cost_value` float NOT NULL DEFAULT '0' COMMENT 'Стоимость прайсового фрагмента',
  `cost_date` int DEFAULT NULL COMMENT 'дата пересчёта начисления по этайсовуму фрагментуому пр',
  `PPMA_value` float NOT NULL DEFAULT '0' COMMENT 'Price Per Montch - Значение активной абонплаты',
  `PPDA_value` float NOT NULL DEFAULT '0' COMMENT 'Price Per Day - Текущая абонплата в день',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'ID пользователя, создавшего запись',
  `creation_date` int DEFAULT NULL COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто изменил запись',
  `modified_date` int DEFAULT NULL COMMENT 'Дата изменения записи в базе'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- ССЫЛКИ ТАБЛИЦЫ `prices_apply`:
--   `prices_id`
--       `prices` -> `id`
--   `abon_id`
--       `abons` -> `id`
--   `net_router_id`
--       `tp_list` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `sf_list`
--

DROP TABLE IF EXISTS `sf_list`;
CREATE TABLE `sf_list` (
  `id` int UNSIGNED NOT NULL,
  `firm_contragent_id` int UNSIGNED DEFAULT '0' COMMENT 'предприятие-Заказчик',
  `firm_agent_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'предприятие-Исполнитель',
  `user_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Пользователь',
  `abon_id` int UNSIGNED NOT NULL COMMENT 'ID Абонент',
  `sf_no` tinytext NOT NULL COMMENT 'СФ №',
  `sf_date` text NOT NULL COMMENT 'Дата счёта',
  `sf_firm` text NOT NULL COMMENT 'Предприятие плательщик',
  `sf_count` float NOT NULL COMMENT 'Количество',
  `sf_cost_1` float NOT NULL COMMENT 'Цена за 1',
  `sf_cost_all` float NOT NULL COMMENT 'Цена всего',
  `sf_text` text NOT NULL COMMENT 'Назначение платежа',
  `sf_is_paid` tinyint(1) NOT NULL COMMENT 'Счёт оплачен',
  `akt_date` text NOT NULL COMMENT 'Дата Акта',
  `modified_date` int NOT NULL COMMENT 'Дата-время модификации записи',
  `modified_uid` int UNSIGNED NOT NULL COMMENT 'ID пользователя, который изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список выписанных счетов';

--
-- ССЫЛКИ ТАБЛИЦЫ `sf_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `sms_list`
--

DROP TABLE IF EXISTS `sms_list`;
CREATE TABLE `sms_list` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID СМС',
  `abon_id` int UNSIGNED NOT NULL COMMENT 'ID абонента, которому отсылается СМС',
  `type_id` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Тип уведомления:\r\n1 - SMS\r\n2 - Email\r\n3 - ...',
  `date` int NOT NULL COMMENT 'Дата-время отправки СМС',
  `text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Текст СМС сообщения',
  `phonenumber` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Номер телефона, на который отправили СМС',
  `method` text NOT NULL COMMENT 'Метод отправки СМС: скрипт, вэб-служба или что-то ещё'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Список отправленных СМС';

--
-- ССЫЛКИ ТАБЛИЦЫ `sms_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `tcp_proto_numbers`
--

DROP TABLE IF EXISTS `tcp_proto_numbers`;
CREATE TABLE `tcp_proto_numbers` (
  `id` int NOT NULL COMMENT 'Номер протокола',
  `name` tinytext NOT NULL COMMENT 'Сокращенное назание протокола',
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Полное название протокола',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Описание протокола'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Список протоколов, инкапсулируемых в IP';

--
-- ССЫЛКИ ТАБЛИЦЫ `tcp_proto_numbers`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `todo_list`
--

DROP TABLE IF EXISTS `todo_list`;
CREATE TABLE `todo_list` (
  `id` int UNSIGNED NOT NULL,
  `parent_id` int UNSIGNED DEFAULT NULL,
  `agent_id_create` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал задачу/заявку',
  `agent_id_modified` int UNSIGNED DEFAULT NULL COMMENT 'Кто исправлял/дополнял заявку',
  `agent_id_todo` int UNSIGNED DEFAULT NULL COMMENT 'На кого расписана заявка',
  `agent_id_ok` int UNSIGNED DEFAULT NULL COMMENT 'Кто фактически выполнил задачу',
  `for_tp_id` int UNSIGNED DEFAULT NULL COMMENT 'ID ТП которая чинится',
  `for_user_id` int UNSIGNED DEFAULT NULL COMMENT 'Пользователь, для которого задача',
  `for_abon_id` int UNSIGNED DEFAULT NULL COMMENT 'Абонент, для которого предназначена задача',
  `date_create` int DEFAULT NULL,
  `date_modified` int DEFAULT NULL,
  `date_todo` int DEFAULT NULL,
  `date_ok` int DEFAULT NULL,
  `type_id` int UNSIGNED NOT NULL DEFAULT '1' COMMENT '1 Комментарий, 2 задача, 3 подключение, 4 принести СФ, 5 принести Документы, 6 Ремонт',
  `title_id` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'ID Причины/Заголовка задачи/заметки',
  `work_time` int DEFAULT NULL COMMENT 'Время затраченное на заявку (включая время в пути)',
  `work_cost` float NOT NULL DEFAULT '0' COMMENT 'Стоимость выполнения работ по заявке (включая транспортные расходы)',
  `work_comment` text COMMENT 'Описание расходной части выполненных работ, если они требуют дополнительного описания',
  `text_todo` text NOT NULL COMMENT 'Описание задачи/заметки',
  `text_ok` text NOT NULL COMMENT 'Коментарий при закрытии заявки'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Задачи и комментарии по обслуживанию сети т абонентов';

--
-- ССЫЛКИ ТАБЛИЦЫ `todo_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `todo_priority`
--

DROP TABLE IF EXISTS `todo_priority`;
CREATE TABLE `todo_priority` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID приоритета задачи',
  `title` text NOT NULL COMMENT 'Название приоритета',
  `description` text NOT NULL COMMENT 'Оприсание приоритета',
  `color_lamp_waiting` varchar(8) NOT NULL DEFAULT 'ecffc7' COMMENT 'HEX цвет: #000000',
  `color_lamp_do` varchar(8) NOT NULL DEFAULT 'fdffc7' COMMENT 'Подсветка "к исполнению"',
  `color_lamp_expired` varchar(8) NOT NULL DEFAULT 'ff3232' COMMENT 'Подсветка "просрочено"',
  `color_lamp_closed` varchar(8) NOT NULL DEFAULT '66664d' COMMENT 'Подсветка "закрыто" (выполено или отменено)',
  `color_text` varchar(8) NOT NULL DEFAULT '030303' COMMENT 'Цвет текста (HEX цвет: #000000)',
  `color_bk` varchar(8) NOT NULL DEFAULT 'dbdbdb' COMMENT 'Цвет фона (HEX цвет: #000000)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Таблица приоритетов задач';

--
-- ССЫЛКИ ТАБЛИЦЫ `todo_priority`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `todo_titles`
--

DROP TABLE IF EXISTS `todo_titles`;
CREATE TABLE `todo_titles` (
  `id` int NOT NULL,
  `type_id` int NOT NULL DEFAULT '6' COMMENT 'Тип задачи todo_types.id',
  `title` tinytext NOT NULL COMMENT 'Заголовок заявки',
  `description` mediumtext NOT NULL COMMENT 'Описание заголовка',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Услуга активна (используется)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Заголовки задач';

--
-- ССЫЛКИ ТАБЛИЦЫ `todo_titles`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `todo_types`
--

DROP TABLE IF EXISTS `todo_types`;
CREATE TABLE `todo_types` (
  `id` int NOT NULL,
  `title` tinytext NOT NULL COMMENT 'Тип задачи',
  `description` text NOT NULL COMMENT 'Описание типа задачи'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Типы задач/описаний/коментариев';

--
-- ССЫЛКИ ТАБЛИЦЫ `todo_types`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `tp_group_list`
--

DROP TABLE IF EXISTS `tp_group_list`;
CREATE TABLE `tp_group_list` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID группы технических площадок',
  `title` varchar(128) NOT NULL COMMENT 'Название группы',
  `description` text NOT NULL COMMENT 'Описание группы'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- ССЫЛКИ ТАБЛИЦЫ `tp_group_list`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `tp_list`
--

DROP TABLE IF EXISTS `tp_list`;
CREATE TABLE `tp_list` (
  `id` int UNSIGNED NOT NULL,
  `territorial_group_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID территориальной группы технических площадок',
  `invest_group_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID инвестиционной группы распределения дивидендов',
  `admin_owner_id` int UNSIGNED DEFAULT NULL COMMENT 'ID администратора-владельца',
  `firm_id` int UNSIGNED DEFAULT NULL COMMENT 'ID Обслуживающего предприятия',
  `title` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Название тех.площадки',
  `ip` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'IP-адрес точки доступа или тех.площадки',
  `login` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'логин для управляющего доступа',
  `pass` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'пароль дл управляющего доступа',
  `url` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'URL-строка для управления устройством (обычно через вэб)',
  `url_zabbix` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'URL страницы в системе мониторинга zabbix относящейся к этой ТП',
  `address` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Адрес размещения ТП',
  `coord` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Географические координаты ТП для отображения на картах',
  `rang_id` int UNSIGNED DEFAULT NULL COMMENT 'Ранг узла: 1 — Абонентский узел.  2 — AP.  3 — Агрегатор AP.  4 — Bridge AP.  5 — Bridge Client.  10 — Хостинговая тех. площадка.  100 — Биллинг. ',
  `uplink_id` int UNSIGNED DEFAULT NULL COMMENT 'Узел "верхнего" уровня, от которого идёт сигнал к этому узлу (не обязательно маршрутизатор)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 — Отключен/демонтирован, 1 — Работает',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ТП демонтирована',
  `is_managed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН',
  `web_management` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Страница web-доступа к устройству',
  `default_price_id` int UNSIGNED DEFAULT NULL COMMENT 'Прайс По_умолчанию для этой ТП',
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание ТП',
  `cost_per_M` float NOT NULL DEFAULT '0' COMMENT 'Стоимость Эксплуатации/аренды/абонплаты техплощадки',
  `cost_per_M_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание стоимости эксплуатации ТП',
  `cost_tp_value` float NOT NULL DEFAULT '0' COMMENT 'Стоимость строительства/ввода в эксплуатацию ТП',
  `cost_tp_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание стоимости строительства / ввода в эксплуатацию ТП',
  `abon_id_range_start` bigint DEFAULT '0' COMMENT 'Начало диапазона выдачи ID для пользователей',
  `abon_id_range_end` bigint DEFAULT '0' COMMENT 'Конец диапазона выдачи ID для пользователей',
  `script_mik_ip` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'IP устройства',
  `script_mik_port` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'tcp порт доступа на устройство',
  `script_mik_login` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'login доступа на устройство',
  `script_mik_passwd` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'passwd доступа на устройства',
  `script_ftp_ip` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'IP-адрес для ftp доступа',
  `script_ftp_port` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'TCP-порт для ftp доступа',
  `script_ftp_login` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Логин для ftp доступа',
  `script_ftp_passwd` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Пасс для ftp доступа',
  `script_ftp_folder` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Имя папаки для сохранения файлов',
  `script_ftp_getpath` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Путь и шаблон на сервере для скачивания файлов',
  `creation_date` int NOT NULL DEFAULT '0' COMMENT 'Дата создания записи о техплощадке',
  `creation_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто создал запись о ТП',
  `modified_date` int NOT NULL DEFAULT '0' COMMENT 'Дата инменения записи о ТП',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись о ТП'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Технические площадки, точки доступа';

--
-- ССЫЛКИ ТАБЛИЦЫ `tp_list`:
--   `admin_owner_id`
--       `users` -> `id`
--   `firm_id`
--       `firm_list` -> `id`
--   `uplink_id`
--       `tp_list` -> `id`
--   `creation_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--   `default_price_id`
--       `prices` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `tp_rangs`
--

DROP TABLE IF EXISTS `tp_rangs`;
CREATE TABLE `tp_rangs` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(50) NOT NULL COMMENT 'Ранг узла',
  `description` tinytext NOT NULL COMMENT 'Описание ранга узла'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Ранги узлов по иерархии подачи сигнала и функциональности';

--
-- ССЫЛКИ ТАБЛИЦЫ `tp_rangs`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `ts_abons_templates`
--

DROP TABLE IF EXISTS `ts_abons_templates`;
CREATE TABLE `ts_abons_templates` (
  `id` int UNSIGNED NOT NULL,
  `ppp_id` int UNSIGNED NOT NULL COMMENT 'ППП к которому относится шаблон',
  `abon_id` int UNSIGNED NOT NULL COMMENT 'ID абонента, которому относится фрагмент текста',
  `template` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Фрагмент текста, по которому узнавать абонента',
  `modified_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто именил',
  `modified_date` int DEFAULT NULL COMMENT 'когда изменил',
  `created_uid` int UNSIGNED DEFAULT NULL COMMENT 'кто создал',
  `created_date` int DEFAULT NULL COMMENT 'когда создал'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='сопоставление текстовых фрагментов к абонентам';

--
-- ССЫЛКИ ТАБЛИЦЫ `ts_abons_templates`:
--   `ppp_id`
--       `ppp_list` -> `id`
--   `abon_id`
--       `abons` -> `id`
--   `created_uid`
--       `users` -> `id`
--   `modified_uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ts_firms_users`
--

DROP TABLE IF EXISTS `ts_firms_users`;
CREATE TABLE `ts_firms_users` (
  `firm_id` int UNSIGNED NOT NULL COMMENT 'ID предприятия',
  `user_id` int UNSIGNED NOT NULL COMMENT 'ID пользователя'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Таблица связей Предприятий и Пользователей';

--
-- ССЫЛКИ ТАБЛИЦЫ `ts_firms_users`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `ts_user_tp`
--

DROP TABLE IF EXISTS `ts_user_tp`;
CREATE TABLE `ts_user_tp` (
  `user_id` int UNSIGNED NOT NULL COMMENT 'ID Пользователя',
  `tp_id` int UNSIGNED NOT NULL COMMENT 'ID Техплощадки',
  `percent_owner` float NOT NULL DEFAULT '0' COMMENT 'Процент долевого участия (владения и получения дивидендов)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Таблица связи Пользователя и ТП';

--
-- ССЫЛКИ ТАБЛИЦЫ `ts_user_tp`:
--   `user_id`
--       `users` -> `id`
--   `tp_id`
--       `tp_list` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `login` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'собственно, сам логин юзера, varchar(25)',
  `password2` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Пароль, шифрованный в 60-байтовый хэш',
  `password` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'пароль хэшированный md5 (устаревший)',
  `salt` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT '«соль», используемая для «примеси» к паролю, varchar(3)',
  `name_short` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Краткое имя пользователя (отображаемое)',
  `name` varchar(80) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `surname` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Отчество',
  `family` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Фамилия',
  `phone_main` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Основной номер телефона',
  `do_send_sms` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Отправлять автоматические СМС-уведомления в общем списке',
  `mail_main` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'email, которые можно изменить в профиле пользователя',
  `do_send_mail` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отправлять уведомления и счета электронной почтой',
  `address_invoice` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Адрес доставки бумажных документов, уведомлений, счетов',
  `do_send_invoice` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Доставлять документы и счета в бумажном виде',
  `jabber_main` tinytext COMMENT 'xmpp jabber клиент для отправки сообщений',
  `jabber_do_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отправлять сообщения на xmpp jabber',
  `viber` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Имя учётной записи месенджера Viber',
  `viber_do_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отправлять сообщения на Viber',
  `telegram` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Имя учётной записи месенджера Telegram',
  `telegram_do_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отправлять сообщения на Telegram',
  `prava` smallint NOT NULL DEFAULT '0' COMMENT '0 == клиент / пользователь, \r\n1 и более == Административные привилегии',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `creation_date` int NOT NULL DEFAULT '0' COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись',
  `modified_date` int NOT NULL DEFAULT '0' COMMENT 'Дата изменения записи в базе',
  `_x_reg_date` int NOT NULL DEFAULT '0' COMMENT 'дата регистрации, int(11)',
  `reg_user_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'ID регистратора'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- ССЫЛКИ ТАБЛИЦЫ `users`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `users_actions`
--

DROP TABLE IF EXISTS `users_actions`;
CREATE TABLE `users_actions` (
  `id` int UNSIGNED NOT NULL COMMENT 'ID записи',
  `user_id` int UNSIGNED NOT NULL COMMENT 'ID пользователя',
  `date_login` int DEFAULT NULL COMMENT 'дата логина пользователя',
  `date_logout` int DEFAULT NULL COMMENT 'дата выхода из системы пользователя'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Статистика логинов пользователей';

--
-- ССЫЛКИ ТАБЛИЦЫ `users_actions`:
--   `user_id`
--       `users` -> `id`
--

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `abons`
--
ALTER TABLE `abons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_payer` (`is_payer`),
  ADD KEY `duty_max_warn` (`duty_max_warn`),
  ADD KEY `duty_max_off` (`duty_max_off`),
  ADD KEY `created_uid` (`created_uid`),
  ADD KEY `modified_uid` (`modified_uid`),
  ADD KEY `id_hash` (`id_hash`);

--
-- Индексы таблицы `abon_rest`
--
ALTER TABLE `abon_rest`
  ADD PRIMARY KEY (`abon_id`);

--
-- Индексы таблицы `adm_module_list`
--
ALTER TABLE `adm_module_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creation_uid` (`creation_uid`),
  ADD KEY `modified_uid` (`modified_uid`);

--
-- Индексы таблицы `adm_role_list`
--
ALTER TABLE `adm_role_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `adm_role_module_permissions`
--
ALTER TABLE `adm_role_module_permissions`
  ADD PRIMARY KEY (`role_id`,`module_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Индексы таблицы `adm_user_role`
--
ALTER TABLE `adm_user_role`
  ADD PRIMARY KEY (`role_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `adr_countries`
--
ALTER TABLE `adr_countries`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `adr_district`
--
ALTER TABLE `adr_district`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `adr_regions`
--
ALTER TABLE `adr_regions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creation_uid` (`creation_uid`),
  ADD KEY `modified_uid` (`modified_uid`),
  ADD KEY `id_country` (`id_country`);

--
-- Индексы таблицы `bank_p24_card`
--
ALTER TABLE `bank_p24_card`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cards_on_ppp`
--
ALTER TABLE `cards_on_ppp`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cards_pools`
--
ALTER TABLE `cards_pools`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `devices_list`
--
ALTER TABLE `devices_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tp_id` (`tp_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Индексы таблицы `devices_types`
--
ALTER TABLE `devices_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `files_list`
--
ALTER TABLE `files_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `firm_list`
--
ALTER TABLE `firm_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppp_default_id` (`ppp_default_id`),
  ADD KEY `modified_uid` (`modified_uid`);

--
-- Индексы таблицы `invest_groups_list`
--
ALTER TABLE `invest_groups_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `invest_ids_list`
--
ALTER TABLE `invest_ids_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `invest_parts_list`
--
ALTER TABLE `invest_parts_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `mail_list`
--
ALTER TABLE `mail_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `nat_forward`
--
ALTER TABLE `nat_forward`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `abon_id` (`abon_id`),
  ADD KEY `pay_date` (`pay_date`),
  ADD KEY `pay` (`pay`),
  ADD KEY `pay_type_id` (`pay_type_id`),
  ADD KEY `pay_ppp_id` (`pay_ppp_id`),
  ADD KEY `pay_sourse_id` (`pay_sourse_id`),
  ADD KEY `pay_fakt` (`pay_fakt`);
ALTER TABLE `payments` ADD FULLTEXT KEY `pay_bank_no` (`pay_bank_no`);

--
-- Индексы таблицы `payments_calculates`
--
ALTER TABLE `payments_calculates`
  ADD PRIMARY KEY (`user_id`,`payment_type_id`,`payment_source_id`);

--
-- Индексы таблицы `payments_sources`
--
ALTER TABLE `payments_sources`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `payments_types`
--
ALTER TABLE `payments_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `phone_numbers`
--
ALTER TABLE `phone_numbers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ppp_list`
--
ALTER TABLE `ppp_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `firm_id` (`firm_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `creation_uid` (`creation_uid`),
  ADD KEY `modified_uid` (`modified_uid`);

--
-- Индексы таблицы `ppp_types`
--
ALTER TABLE `ppp_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_uid` (`creation_uid`),
  ADD KEY `modified_uid` (`modified_uid`);

--
-- Индексы таблицы `prices_apply`
--
ALTER TABLE `prices_apply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prices_id` (`prices_id`),
  ADD KEY `net_router_id` (`net_router_id`),
  ADD KEY `date_start` (`date_start`),
  ADD KEY `date_end` (`date_end`),
  ADD KEY `_X_idx_abon_dates` (`abon_id`,`date_start` DESC,`date_end`),
  ADD KEY `idx_abon_end_start` (`abon_id`,`date_end` DESC,`date_start` DESC);

--
-- Индексы таблицы `sf_list`
--
ALTER TABLE `sf_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `firm_contragent_id` (`firm_contragent_id`),
  ADD KEY `firm_agent_id` (`firm_agent_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `abon_id` (`abon_id`),
  ADD KEY `sf_count` (`sf_count`),
  ADD KEY `sf_cost_1` (`sf_cost_1`),
  ADD KEY `sf_cost_all` (`sf_cost_all`);

--
-- Индексы таблицы `sms_list`
--
ALTER TABLE `sms_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tcp_proto_numbers`
--
ALTER TABLE `tcp_proto_numbers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `todo_list`
--
ALTER TABLE `todo_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `todo_priority`
--
ALTER TABLE `todo_priority`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `todo_titles`
--
ALTER TABLE `todo_titles`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `todo_types`
--
ALTER TABLE `todo_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tp_group_list`
--
ALTER TABLE `tp_group_list`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tp_list`
--
ALTER TABLE `tp_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_owner_id` (`admin_owner_id`),
  ADD KEY `firm_id` (`firm_id`),
  ADD KEY `uplink_id` (`uplink_id`),
  ADD KEY `creation_uid` (`creation_uid`),
  ADD KEY `modified_uid` (`modified_uid`),
  ADD KEY `default_price_id` (`default_price_id`);

--
-- Индексы таблицы `tp_rangs`
--
ALTER TABLE `tp_rangs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ts_abons_templates`
--
ALTER TABLE `ts_abons_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abon_id` (`abon_id`),
  ADD KEY `ppp_id` (`ppp_id`),
  ADD KEY `created_uid` (`created_uid`),
  ADD KEY `modified_uid` (`modified_uid`);

--
-- Индексы таблицы `ts_firms_users`
--
ALTER TABLE `ts_firms_users`
  ADD PRIMARY KEY (`firm_id`,`user_id`);

--
-- Индексы таблицы `ts_user_tp`
--
ALTER TABLE `ts_user_tp`
  ADD PRIMARY KEY (`user_id`,`tp_id`),
  ADD KEY `tp_id` (`tp_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `users_actions`
--
ALTER TABLE `users_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `abons`
--
ALTER TABLE `abons`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `adm_module_list`
--
ALTER TABLE `adm_module_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID административного модуля';

--
-- AUTO_INCREMENT для таблицы `adm_role_list`
--
ALTER TABLE `adm_role_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID административной группы';

--
-- AUTO_INCREMENT для таблицы `adr_countries`
--
ALTER TABLE `adr_countries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ИД государства только в этой базе';

--
-- AUTO_INCREMENT для таблицы `adr_district`
--
ALTER TABLE `adr_district`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID района в этой базе';

--
-- AUTO_INCREMENT для таблицы `adr_regions`
--
ALTER TABLE `adr_regions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID региона/области. Внутренний номер только для этой базы ';

--
-- AUTO_INCREMENT для таблицы `bank_p24_card`
--
ALTER TABLE `bank_p24_card`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cards_on_ppp`
--
ALTER TABLE `cards_on_ppp`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cards_pools`
--
ALTER TABLE `cards_pools`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_list`
--
ALTER TABLE `devices_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_types`
--
ALTER TABLE `devices_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID новости';

--
-- AUTO_INCREMENT для таблицы `files_list`
--
ALTER TABLE `files_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `firm_list`
--
ALTER TABLE `firm_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `invest_groups_list`
--
ALTER TABLE `invest_groups_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `invest_ids_list`
--
ALTER TABLE `invest_ids_list`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `invest_parts_list`
--
ALTER TABLE `invest_parts_list`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID владельца';

--
-- AUTO_INCREMENT для таблицы `mail_list`
--
ALTER TABLE `mail_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID элемента меню';

--
-- AUTO_INCREMENT для таблицы `nat_forward`
--
ALTER TABLE `nat_forward`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID новости';

--
-- AUTO_INCREMENT для таблицы `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `payments_sources`
--
ALTER TABLE `payments_sources`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `payments_types`
--
ALTER TABLE `payments_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `phone_numbers`
--
ALTER TABLE `phone_numbers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ppp_list`
--
ALTER TABLE `ppp_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ppp_types`
--
ALTER TABLE `ppp_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа ППП';

--
-- AUTO_INCREMENT для таблицы `prices`
--
ALTER TABLE `prices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `prices_apply`
--
ALTER TABLE `prices_apply`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `sf_list`
--
ALTER TABLE `sf_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `sms_list`
--
ALTER TABLE `sms_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID СМС';

--
-- AUTO_INCREMENT для таблицы `todo_list`
--
ALTER TABLE `todo_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `todo_priority`
--
ALTER TABLE `todo_priority`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID приоритета задачи';

--
-- AUTO_INCREMENT для таблицы `todo_titles`
--
ALTER TABLE `todo_titles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `todo_types`
--
ALTER TABLE `todo_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tp_group_list`
--
ALTER TABLE `tp_group_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID группы технических площадок';

--
-- AUTO_INCREMENT для таблицы `tp_list`
--
ALTER TABLE `tp_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tp_rangs`
--
ALTER TABLE `tp_rangs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ts_abons_templates`
--
ALTER TABLE `ts_abons_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users_actions`
--
ALTER TABLE `users_actions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID записи';

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `abons`
--
ALTER TABLE `abons`
  ADD CONSTRAINT `abons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `abons_ibfk_2` FOREIGN KEY (`created_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `abons_ibfk_3` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `adm_module_list`
--
ALTER TABLE `adm_module_list`
  ADD CONSTRAINT `adm_module_list_ibfk_1` FOREIGN KEY (`creation_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `adm_module_list_ibfk_2` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `adm_role_module_permissions`
--
ALTER TABLE `adm_role_module_permissions`
  ADD CONSTRAINT `adm_role_module_permissions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `adm_module_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `adm_role_module_permissions_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `adm_role_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `adm_user_role`
--
ALTER TABLE `adm_user_role`
  ADD CONSTRAINT `adm_user_role_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `adm_role_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `adm_user_role_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `adr_regions`
--
ALTER TABLE `adr_regions`
  ADD CONSTRAINT `adr_regions_ibfk_1` FOREIGN KEY (`creation_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `adr_regions_ibfk_2` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `adr_regions_ibfk_3` FOREIGN KEY (`id_country`) REFERENCES `adr_countries` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `devices_list`
--
ALTER TABLE `devices_list`
  ADD CONSTRAINT `devices_list_ibfk_1` FOREIGN KEY (`tp_id`) REFERENCES `tp_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `devices_list_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `devices_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `firm_list`
--
ALTER TABLE `firm_list`
  ADD CONSTRAINT `firm_list_ibfk_1` FOREIGN KEY (`ppp_default_id`) REFERENCES `ppp_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `firm_list_ibfk_2` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `ppp_list`
--
ALTER TABLE `ppp_list`
  ADD CONSTRAINT `ppp_list_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `ppp_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ppp_list_ibfk_2` FOREIGN KEY (`firm_id`) REFERENCES `firm_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ppp_list_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ppp_list_ibfk_4` FOREIGN KEY (`creation_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ppp_list_ibfk_5` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `prices`
--
ALTER TABLE `prices`
  ADD CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`creation_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `prices_apply`
--
ALTER TABLE `prices_apply`
  ADD CONSTRAINT `prices_apply_ibfk_1` FOREIGN KEY (`prices_id`) REFERENCES `prices` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `prices_apply_ibfk_2` FOREIGN KEY (`abon_id`) REFERENCES `abons` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `prices_apply_ibfk_3` FOREIGN KEY (`net_router_id`) REFERENCES `tp_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `tp_list`
--
ALTER TABLE `tp_list`
  ADD CONSTRAINT `tp_list_ibfk_1` FOREIGN KEY (`admin_owner_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tp_list_ibfk_2` FOREIGN KEY (`firm_id`) REFERENCES `firm_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tp_list_ibfk_3` FOREIGN KEY (`uplink_id`) REFERENCES `tp_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tp_list_ibfk_4` FOREIGN KEY (`creation_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tp_list_ibfk_5` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tp_list_ibfk_6` FOREIGN KEY (`default_price_id`) REFERENCES `prices` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `ts_abons_templates`
--
ALTER TABLE `ts_abons_templates`
  ADD CONSTRAINT `ts_abons_templates_ibfk_1` FOREIGN KEY (`ppp_id`) REFERENCES `ppp_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ts_abons_templates_ibfk_2` FOREIGN KEY (`abon_id`) REFERENCES `abons` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ts_abons_templates_ibfk_3` FOREIGN KEY (`created_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ts_abons_templates_ibfk_4` FOREIGN KEY (`modified_uid`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `ts_user_tp`
--
ALTER TABLE `ts_user_tp`
  ADD CONSTRAINT `ts_user_tp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ts_user_tp_ibfk_2` FOREIGN KEY (`tp_id`) REFERENCES `tp_list` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ограничения внешнего ключа таблицы `users_actions`
--
ALTER TABLE `users_actions`
  ADD CONSTRAINT `users_actions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
