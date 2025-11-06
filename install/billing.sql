-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Ноя 05 2025 г., 23:12
-- Версия сервера: 8.0.44
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
  `sum_pay` double NOT NULL DEFAULT '0' COMMENT 'Сумма платежей и внесений на ЛС',
  `sum_cost` double NOT NULL DEFAULT '0' COMMENT 'Сумма начислений за услуги price_apply',
  `sum_PPMA` double NOT NULL DEFAULT '0' COMMENT 'PPMA - Price Per Month Active',
  `sum_PPDA` double NOT NULL DEFAULT '0' COMMENT 'PPDA - Price Per Day Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `abon_rest`:
--

-- --------------------------------------------------------

--
-- Структура таблицы `adm_menu`
--

DROP TABLE IF EXISTS `adm_menu`;
CREATE TABLE `adm_menu` (
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
-- ССЫЛКИ ТАБЛИЦЫ `adm_menu`:
--

--
-- Очистить таблицу перед добавлением данных `adm_menu`
--

TRUNCATE TABLE `adm_menu`;
--
-- Дамп данных таблицы `adm_menu`
--

INSERT INTO `adm_menu` (`id`, `parent_id`, `module_id`, `anon_visible`, `visible`, `order_num`, `ru_title`, `uk_title`, `en_title`, `ru_description`, `uk_description`, `en_description`, `url`, `is_widget`, `creation_uid`, `creation_date`, `modified_uid`, `modified_date`) VALUES
(1, 0, 23, 1, 1, 10, ':: Главная', ':: Головна', ':: Main', 'На главную страницу', '', '', '/my', 0, NULL, NULL, 461, 1756567704),
(2, 0, 28, 0, 1, 100, ':: Абоненты', ':: Абоненти', ':: Abons', '', '', '', '', 0, NULL, NULL, 1, 1757925890),
(7, 0, 27, 0, 1, 999, ':: Админка', ':: Адмінка', ':: Admin menu', '', '', '', '', 0, NULL, NULL, 1, 1756589725),
(8, 7, NULL, 0, 1, 99, 'Правка меню', 'Редагування меню', 'Editing menu', 'Форма редактирования бокового меню-аккордеона', 'Форма редагування бічного меню-акардону', 'The form of editing the lateral menu-acardone', '/admin/admin/menuedit', 0, NULL, NULL, 1, 1752717328),
(9, 7, NULL, 0, 1, 20, 'Модули', 'Модулі', 'Modules', 'Список модулей и установка прав доступа пользователей к этим модулям', 'Список модулів та встановлення прав доступу користувачів до цих модулів', 'List of modules and setting user access rights to these modules', '/admin/module/list', 0, NULL, NULL, 458, 1756412968),
(10, 7, NULL, 0, 1, 10, 'Пользователи', 'Користувачі', 'Users', 'Полный список пользователей', 'Повний список користувачів', 'A complete list of users', '/admin/admin-users/list', 0, NULL, NULL, 1, 1752946038),
(11, 7, NULL, 0, 1, 30, 'Роли', 'Ролі', 'Roles', 'Список административных роле', 'Список административных роле', 'Список административных роле', '/admin/roles', 0, NULL, NULL, 458, 1756412960),
(12, 7, NULL, 0, 1, 40, 'Роли/Пользователи', 'Ролі/Користвачі', 'Roles/Users', 'Просмотр и редактирование привязок Ролей к Пользователям', 'Просмотр и редактирование привязок Ролей к Пользователям', 'Просмотр и редактирование привязок Ролей к Пользователям', '/admin/roles/ts-users', 0, NULL, NULL, 458, 1756412948),
(13, 2, NULL, 0, 1, 1, 'Список абонентов', 'Перелік абонентів', 'List of abons', 'Список абонентов', 'Перелік абонентів', 'List of subscribers', '/abon', 0, NULL, NULL, 1, 1754420776),
(16, 0, 25, 1, 1, 20, ':: Как оплатить', ':: Як сплатити', ':: How to pay', '', '', '', '/pay', 0, NULL, NULL, 1, 1760639426),
(17, 0, 25, 0, 1, 50, '::&nbsp;Акт&nbsp;сверки', '::&nbsp;Зведення&nbsp;платежів', '::&nbsp;Account&nbsp;reconciliation', 'Акт сверки расчетов', 'Акт звіряння розрахунків', 'Account reconciliation report', '/conciliation', 0, NULL, NULL, 1, 1758504426),
(18, 0, 0, 1, 1, 30, ':: Договор', ':: Договір', ':: Agreement', 'Публичный договор', 'Публічний договір', 'Offerta', '/dogovir', 0, NULL, NULL, 368277, 1756929942),
(19, 0, 29, 0, 1, 130, ':: ТП', ':: ТП', ':: ТП', '', '', '', '', 0, NULL, NULL, 1, 1756591041),
(20, 0, 25, 0, 1, 40, '::&nbsp;История&nbsp;платежей', '::&nbsp;Історія&nbsp;платежів', '::&nbsp;Payment&nbsp;history', 'История платежей', 'Історія платежів', 'Payment history', '/payments', 0, NULL, NULL, 1, 1758504380),
(22, 0, 30, 0, 1, 70, ':: Документы', ':: Документи', ':: Documents', 'Документы', 'Документи', 'Documents', '', 0, NULL, NULL, 1, 1756591163),
(23, 0, 0, 1, 1, 35, '::&nbsp;Правила&nbsp;сети', '::&nbsp;Правила&nbsp;мережі', '::&nbsp;Network&nbsp;Rules', '', '', '', '/rules', 0, NULL, NULL, 1, 1758504325),
(24, 0, 0, 1, 1, 55, ':: Защита от DDoS', ':: Захист від DDoS', ':: DDoS protection', 'Краткая инструкция по обнаружению и удалению вредоносных программ.', 'Коротка інструкція щодо виявлення та видалення шкідливих програм.', 'A brief guide to detecting and removing malware.', '/flood', 0, NULL, NULL, 461, 1756567723),
(25, 22, NULL, 0, 1, 10, 'Документы', 'Документы', 'Документы', 'Редактор документов', 'Редактор документов', 'Редактор документов', '/docs', 0, NULL, NULL, NULL, NULL),
(26, 22, NULL, 0, 1, 20, 'Файлы', 'Файлы', 'Файлы', 'Управление публичными и личными файлами, документами, изображениями', 'Управление публичными и личными файлами, документами, изображениями', 'Управление публичными и личными файлами, документами, изображениями', '/files', 0, NULL, NULL, NULL, NULL),
(27, 19, 29, 0, 1, 10, 'Список ТП', 'Перелік ТП', 'TP List', '', '', '', '/tp', 0, NULL, NULL, 1, 1760911701),
(28, 19, 31, 0, 1, 20, 'Предприятия', 'Підприємства', 'Firms', '', '', '', '/firms', 0, NULL, NULL, 1, 1760911769),
(29, 19, 43, 0, 1, 30, 'Список ППП', 'Перелік ППП', 'POP List', '', '', '', '/ppp?active=1', 0, NULL, NULL, 1, 1760983922);

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

--
-- Очистить таблицу перед добавлением данных `adm_module_list`
--

TRUNCATE TABLE `adm_module_list`;
--
-- Дамп данных таблицы `adm_module_list`
--

INSERT INTO `adm_module_list` (`id`, `uk_title`, `ru_title`, `en_title`, `uk_description`, `ru_description`, `en_description`, `route`, `api`, `creation_uid`, `creation_date`, `modified_uid`, `modified_date`) VALUES
(3, 'Контакти', 'Контакты', 'Contacts', 'Додаткові контакти', 'Дополнительные контакты', 'Additional contacts', '', '', 1, 1725738438, 4, 1760460076),
(6, 'Прайсовий фрагмент', 'Прайсовый фрагмент', 'Price Apply', '', '', '', '', '', 1, 1725738567, 1, 1757498328),
(22, 'Підприємства', 'Предприятия', 'Firms', '', '', '', '', '', 1, 1725781712, 4, 1760460336),
(23, 'Картка користувача (My)', 'Карточка пользователя (My)', 'User Card (My)', 'Відображати мою картку користувача з усіма заповненими полями', 'Отображать мою карточку пользователя со всеми заполненными полями', 'Display my user card with all filled fields', '', '', 1, 1756463185, 1, 1757497928),
(24, 'Контакти (My)', 'Контакты (My)', 'Contacts (My)', 'Додаткові контакти зареєстрованого Користувача (Мої)', 'Дополнительные контакты зарегистрированного пользователя (Мои)', 'Additional contacts of the registered user (My)', '', '', 458, 1756472087, 4, 1760460073),
(25, 'Абонентські данні (My)', 'Абонентские данные (My)', 'Abon Data (My)', '', '', '', '', '', 458, 1756504308, 1, 1757498678),
(26, 'Прайсовий фрагмент (My)', 'Прайсовый фрагмент (My)', 'Price Apply (My)', '', '', '', '', '', 458, 1756504463, 1, 1757498500),
(27, 'Адмін меню', 'Админ меню', 'Admin menu', '', '', '', '', '', 1, 1756589706, 1, 1757496529),
(28, 'Абонентські данні', 'Абонентские данные', 'Abon Data', '', '', '', '', '', 1, 1756589835, 1, 1757498676),
(29, 'ТП', 'ТП', 'ТП', 'Модуль доступа к просмотру меню, списка и прочим функциям ТП', 'Модуль доступа к просмотру меню, списка и прочим функциям ТП', 'Модуль доступа к просмотру меню, списка и прочим функциям ТП', '', '', 1, 1756590992, 1, 1756799914),
(30, 'Документы', 'Документы', 'Документы', '', '', '', '', '', 1, 1756591086, 1, 1756591086),
(31, 'Підприємства (My)', 'Предприятия (My)', 'Firms (My)', '', '', '', '', '', 1, 1756652907, 4, 1760460333),
(32, 'Модулі', 'Модули', 'Modules', 'Доступ до функцій керування модулями', 'Доступ к функциям управления модулями', 'Access to module management functions', '', '', 1, 1756678959, 4, 1760460544),
(33, 'Підприємство. Статус', 'Предприятие. Статус', 'Firm Status', '', '', '', '', '', 1, 1756731615, 4, 1760460450),
(34, 'Акт звірки платежів (My)', 'Акт сверки платежей (My)', 'Conciliation (My)', 'Акт звірки взаємних розрахунків зареєстрованого користувача (Мої)', 'Акт сверки взаимных расчётов зарегистрированного пользователя (Мои)', 'Reconciliation report of mutual settlements of a registered user (My)', '', '', 1, 1756803986, 4, 1760460242),
(35, 'Акт звірки платежів', 'Акт сверки платежей', 'Conciliation', 'Акт звірки взаємних розрахунків', 'Акт сверки взаимных расчётов', 'The act of reconciliation of mutual settlements', '', '', 1, 1756804026, 4, 1760460244),
(36, 'Поиск', 'Поиск', 'Поиск', '', '', '', '', '', 1, 1756923436, 1, 1756923436),
(37, 'Повідомлення', 'Уведомления', 'Notify', '', '', '', '', '', 458, 1757066739, 1, 1757498598),
(38, 'Повідомлення (My)', 'Уведомления (My)', 'Notify (My)', '', '', '', '', '', 458, 1757066783, 1, 1757498595),
(39, 'Web Debug', 'Web Debug', 'Web Debug', 'Показ налагоджувальної інформації', 'Показ отладочной информации', 'Show debug information', '', '', 1, 1757370881, 1, 1760466407),
(40, 'Платежі', 'Платежи', 'Payments', 'Доступ до функцій роботи з платежами', 'Доступ к функциям работы с платежами', 'Access to payment functions', '', '', 1, 1757491880, 4, 1760460737),
(41, 'Платежі (My)', 'Платежи (My)', 'Payments (My)', 'Доступ до функцій роботи з моїми платежами (My)', 'Доступ к функциям работы с моими платежами (My)', 'Access to My Payments (My) functionality', '', '', 1, 1757492014, 4, 1760460735),
(42, 'Картка користувача', 'Карточка пользователя', 'User Card', '', '', '', '', '', 1, 1760368719, 1, 1760368719),
(43, 'ППП', 'ППП', 'PAP', 'Пункт прийому платежів (механізм отримання платежів)', 'Пункт приёма платежей (механизм получения платежей)', 'Payment acceptance point (payment receipt mechanism)', '', '', 1, 1760874151, 1, 1760874170);

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

--
-- Очистить таблицу перед добавлением данных `adm_role_list`
--

TRUNCATE TABLE `adm_role_list`;
--
-- Дамп данных таблицы `adm_role_list`
--

INSERT INTO `adm_role_list` (`id`, `uk_title`, `ru_title`, `en_title`, `uk_description`, `ru_description`, `en_description`, `creation_uid`, `creation_date`, `modified_uid`, `modified_date`) VALUES
(1, 'SuperAdmin', 'SuperAdmin', 'SuperAdmin', 'Повний доступ до всіх функцій системи (програміст, CEO)', 'Полный доступ ко всем функциям системы (программист, CEO)', 'Full access to all system functions (programmer, CEO)', 1, 1751885179, 1, 1753190260),
(2, 'Технічний адміністратор', 'Технический админ', 'Tech Admin', 'Технічний адміністратор системи (програміст)', 'Технический администратор системы (программист)', 'Technical system administrator (programmer)', 1, 1751885179, 1, 1753265896),
(3, 'Abon ON', 'Abon ON', 'Abon ON', 'Абонент, що обслуговується, платник (is_payer), включаючи тих, хто на паузі.', 'Обслуживаемый абонент, плательщик (is_payer), включая тех, кто на паузе.', 'The subscriber being served, the payer (is_payer), including those who are on pause.', 1, 1751885179, 1, 1753186769),
(4, 'Abon OFF', 'Abon OFF', 'Abon OFF', 'Вимкнений абонент (за заявкою або з іншої причини)', 'Отключенный абонент (по заявке или по другой причине)', 'Disconnected subscriber (by request or for other reason)', 1, 1751885179, 1, 1753186816),
(5, 'Owner-TP', 'Owner-TP', 'Owner-TP', 'Співвласник технічного вузла, інвестор, обслуговуючий інженер', 'Совладелец технического узла, инвестор, обслуживающий инженер', 'Co-owner of the technical unit, investor, service engineer', 1, 1751885179, 1, 1753186943),
(6, 'Ст. інженер', 'Ст. инженер', 'Senior Engineer', 'Старший інженер (той, хто набирає та командує, інструктує, забезпечує інструментами та матеріалами…)', 'Старший инженер (тот, кто набирает и командует, инструктирует, обеспечивает инструментами и материалами…)', 'Senior Engineer (the one who recruits and commands, instructs, provides tools and materials...)', 1, 1751885179, 1, 1753264217),
(7, 'Інженер', 'Инженер', 'Engineer', 'Інженер (системний адміністратор, програміст), який обслуговує тех. майданчик', 'Инженер (системный администратор, программист), обслуживающий тех. площадку', 'Engineer (system administrator, programmer) servicing the technical site', 1, 1751885179, 1, 1753266340),
(8, 'Старший мастер', 'Старший мастер', 'Старший мастер', 'Старший майстер ділянки, технічного вузла', 'Старший мастер участка, технического узла', 'Senior site foreman, technical unit', 1, 1751885179, 1, 1753266438),
(9, 'Майстер', 'Мастер', 'Foreman', 'Майстер, що обслуговує ділянку', 'Мастер, обслуживающий участок', 'Foreman servicing the site', 1, 1751885179, 1, 1753266538),
(10, 'Гол. бухгалтер', 'Гл. бухгалтер', 'Chief accountant', 'Головний бухгалтер (той, хто набирає, командує, інструктує…)', 'Главный бухгалтер (тот, кто набирает, командует, инструктирует…)', 'Chief accountant (the one who recruits, commands, instructs...)', 1, 1751885179, 1, 1753197883),
(11, 'Бухгалтер', 'Бухгалтер', 'Accountant', 'Бухгалтер', 'Бухгалтер', 'Accountant', 1, 1751885179, 1, 1753266843),
(12, 'Ст. касир', 'Ст. кассир', 'Senior cashier', 'Старший касир (той, хто набирає та керує)', 'Старший кассир (тот, кто набирает и управляет)', 'Senior Cashier (the one who types and manages)', 1, 1751885179, 1, 1753266951),
(13, 'Касир', 'Кассир', 'Cashier', 'Касир', 'Кассир', 'Cashier', 1, 1751885179, 1, 1753267539),
(14, 'Ст. агент', 'Ст. агент', 'Senior agent', 'Старший агент (тот, хто набирає і управляє)', 'Старший агент (тот, кто набирает и управляет)', 'Senior Agent (the one who recruits and manages)', 1, 1751885179, 1, 1753267444),
(15, 'Агент', 'Агент', 'Агент', 'Маркетолог, рекламний агент, кредитор (інформує про заборгованість)', 'Маркетолог, рекламный агент, кредитор (ответственный за контроль платежей)', 'Marketer, advertising agent, creditor (informing about debt)', 1, 1751885179, 1, 1760369066),
(16, 'Ст. консультант', 'Ст. консультант', 'Senior Consultant', 'Старший консультант контакт-центра, тех. підтримки (тот, хто набирає і управляє)', 'Старший консультант контакт-центра, тех. поддержки (тот, кто набирает и управляет)', 'Senior contact center consultant, tech. podderje (one who recruits and manages)', 1, 1751885179, 1, 1753267291),
(17, 'Консультант', 'Консультант', 'Консультант', 'Консультант контакт-центру, технічної підтримки', 'Консультант контакт-центра, технической поддержки', 'Contact center consultant, technical support', 1, 1751885179, 1, 1753187287);

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

--
-- Очистить таблицу перед добавлением данных `adm_role_module_permissions`
--

TRUNCATE TABLE `adm_role_module_permissions`;
--
-- Дамп данных таблицы `adm_role_module_permissions`
--

INSERT INTO `adm_role_module_permissions` (`role_id`, `module_id`, `permissions`) VALUES
(1, 3, 15),
(1, 6, 15),
(1, 22, 15),
(1, 23, 0),
(1, 24, 0),
(1, 25, 0),
(1, 26, 0),
(1, 27, 15),
(1, 28, 15),
(1, 29, 15),
(1, 30, 15),
(1, 31, 0),
(1, 32, 15),
(1, 33, 15),
(1, 34, 0),
(1, 35, 15),
(1, 36, 15),
(1, 37, 15),
(1, 38, 0),
(1, 39, 1),
(1, 40, 15),
(1, 41, 0),
(1, 42, 15),
(1, 43, 15),
(2, 3, 15),
(2, 6, 15),
(2, 22, 15),
(2, 23, 0),
(2, 24, 0),
(2, 25, 0),
(2, 26, 0),
(2, 27, 1),
(2, 28, 7),
(2, 29, 7),
(2, 30, 15),
(2, 31, 0),
(2, 32, 15),
(2, 33, 15),
(2, 34, 0),
(2, 35, 15),
(2, 36, 15),
(2, 37, 15),
(2, 38, 0),
(2, 39, 1),
(2, 40, 5),
(2, 41, 0),
(2, 42, 15),
(2, 43, 15),
(3, 3, 0),
(3, 6, 0),
(3, 22, 0),
(3, 23, 3),
(3, 24, 7),
(3, 25, 1),
(3, 26, 1),
(3, 27, 0),
(3, 28, 0),
(3, 29, 0),
(3, 30, 0),
(3, 31, 1),
(3, 32, 0),
(3, 33, 0),
(3, 34, 1),
(3, 35, 0),
(3, 36, 0),
(3, 37, 0),
(3, 38, 1),
(3, 39, 0),
(3, 40, 0),
(3, 41, 1),
(3, 42, 0),
(3, 43, 0),
(4, 3, 0),
(4, 6, 0),
(4, 22, 0),
(4, 23, 1),
(4, 24, 1),
(4, 25, 0),
(4, 26, 0),
(4, 27, 0),
(4, 28, 0),
(4, 29, 0),
(4, 30, 0),
(4, 31, 1),
(4, 32, 0),
(4, 33, 0),
(4, 34, 0),
(4, 35, 0),
(4, 36, 0),
(4, 37, 0),
(4, 38, 0),
(4, 39, 0),
(4, 40, 0),
(4, 41, 0),
(4, 42, 0),
(4, 43, 0),
(5, 3, 0),
(5, 6, 1),
(5, 22, 0),
(5, 23, 0),
(5, 24, 0),
(5, 25, 0),
(5, 26, 0),
(5, 27, 0),
(5, 28, 1),
(5, 29, 7),
(5, 30, 1),
(5, 31, 0),
(5, 32, 0),
(5, 33, 0),
(5, 34, 0),
(5, 35, 0),
(5, 36, 0),
(5, 37, 0),
(5, 38, 0),
(5, 39, 0),
(5, 40, 0),
(5, 41, 0),
(5, 42, 1),
(5, 43, 15),
(6, 3, 0),
(6, 6, 1),
(6, 22, 0),
(6, 23, 0),
(6, 24, 0),
(6, 25, 0),
(6, 26, 0),
(6, 27, 0),
(6, 28, 1),
(6, 29, 3),
(6, 30, 1),
(6, 31, 0),
(6, 32, 0),
(6, 33, 1),
(6, 34, 0),
(6, 35, 0),
(6, 36, 0),
(6, 37, 5),
(6, 38, 0),
(6, 39, 0),
(6, 40, 0),
(6, 41, 0),
(6, 42, 1),
(6, 43, 0),
(7, 3, 0),
(7, 6, 1),
(7, 22, 0),
(7, 23, 0),
(7, 24, 0),
(7, 25, 0),
(7, 26, 0),
(7, 27, 0),
(7, 28, 1),
(7, 29, 3),
(7, 30, 1),
(7, 31, 0),
(7, 32, 0),
(7, 33, 1),
(7, 34, 0),
(7, 35, 0),
(7, 36, 0),
(7, 37, 5),
(7, 38, 0),
(7, 39, 0),
(7, 40, 0),
(7, 41, 0),
(7, 42, 1),
(7, 43, 0),
(8, 3, 7),
(8, 6, 3),
(8, 22, 1),
(8, 23, 0),
(8, 24, 0),
(8, 25, 0),
(8, 26, 0),
(8, 27, 0),
(8, 28, 7),
(8, 29, 1),
(8, 30, 1),
(8, 31, 0),
(8, 32, 0),
(8, 33, 7),
(8, 34, 0),
(8, 35, 1),
(8, 36, 1),
(8, 37, 5),
(8, 38, 0),
(8, 39, 0),
(8, 40, 1),
(8, 41, 0),
(8, 42, 7),
(8, 43, 0),
(9, 3, 7),
(9, 6, 3),
(9, 22, 1),
(9, 23, 0),
(9, 24, 0),
(9, 25, 0),
(9, 26, 0),
(9, 27, 0),
(9, 28, 7),
(9, 29, 1),
(9, 30, 1),
(9, 31, 0),
(9, 32, 0),
(9, 33, 7),
(9, 34, 0),
(9, 35, 1),
(9, 36, 1),
(9, 37, 5),
(9, 38, 0),
(9, 39, 0),
(9, 40, 1),
(9, 41, 0),
(9, 42, 7),
(9, 43, 0),
(10, 3, 5),
(10, 6, 0),
(10, 22, 1),
(10, 23, 0),
(10, 24, 0),
(10, 25, 0),
(10, 26, 0),
(10, 27, 0),
(10, 28, 1),
(10, 29, 0),
(10, 30, 1),
(10, 31, 0),
(10, 32, 0),
(10, 33, 7),
(10, 34, 0),
(10, 35, 1),
(10, 36, 1),
(10, 37, 1),
(10, 38, 0),
(10, 39, 0),
(10, 40, 7),
(10, 41, 0),
(10, 42, 1),
(10, 43, 0),
(11, 3, 5),
(11, 6, 0),
(11, 22, 1),
(11, 23, 0),
(11, 24, 0),
(11, 25, 0),
(11, 26, 0),
(11, 27, 0),
(11, 28, 1),
(11, 29, 0),
(11, 30, 1),
(11, 31, 0),
(11, 32, 0),
(11, 33, 7),
(11, 34, 0),
(11, 35, 1),
(11, 36, 1),
(11, 37, 1),
(11, 38, 0),
(11, 39, 0),
(11, 40, 5),
(11, 41, 0),
(11, 42, 1),
(11, 43, 0),
(12, 3, 0),
(12, 6, 0),
(12, 22, 1),
(12, 23, 0),
(12, 24, 0),
(12, 25, 0),
(12, 26, 0),
(12, 27, 0),
(12, 28, 1),
(12, 29, 0),
(12, 30, 1),
(12, 31, 0),
(12, 32, 0),
(12, 33, 1),
(12, 34, 0),
(12, 35, 0),
(12, 36, 1),
(12, 37, 1),
(12, 38, 0),
(12, 39, 0),
(12, 40, 5),
(12, 41, 0),
(12, 42, 1),
(12, 43, 0),
(13, 3, 0),
(13, 6, 0),
(13, 22, 1),
(13, 23, 0),
(13, 24, 0),
(13, 25, 0),
(13, 26, 0),
(13, 27, 0),
(13, 28, 1),
(13, 29, 0),
(13, 30, 1),
(13, 31, 0),
(13, 32, 0),
(13, 33, 1),
(13, 34, 0),
(13, 35, 0),
(13, 36, 1),
(13, 37, 1),
(13, 38, 0),
(13, 39, 0),
(13, 40, 5),
(13, 41, 0),
(13, 42, 1),
(13, 43, 0),
(14, 3, 5),
(14, 6, 3),
(14, 22, 1),
(14, 23, 0),
(14, 24, 0),
(14, 25, 0),
(14, 26, 0),
(14, 27, 0),
(14, 28, 7),
(14, 29, 0),
(14, 30, 1),
(14, 31, 0),
(14, 32, 0),
(14, 33, 1),
(14, 34, 0),
(14, 35, 1),
(14, 36, 1),
(14, 37, 5),
(14, 38, 0),
(14, 39, 0),
(14, 40, 0),
(14, 41, 0),
(14, 42, 7),
(14, 43, 0),
(15, 3, 5),
(15, 6, 3),
(15, 22, 1),
(15, 23, 0),
(15, 24, 0),
(15, 25, 0),
(15, 26, 0),
(15, 27, 0),
(15, 28, 7),
(15, 29, 0),
(15, 30, 1),
(15, 31, 0),
(15, 32, 0),
(15, 33, 1),
(15, 34, 0),
(15, 35, 1),
(15, 36, 1),
(15, 37, 5),
(15, 38, 0),
(15, 39, 0),
(15, 40, 0),
(15, 41, 0),
(15, 42, 7),
(15, 43, 0),
(16, 3, 5),
(16, 6, 3),
(16, 22, 1),
(16, 23, 0),
(16, 24, 0),
(16, 25, 0),
(16, 26, 0),
(16, 27, 0),
(16, 28, 1),
(16, 29, 0),
(16, 30, 1),
(16, 31, 0),
(16, 32, 0),
(16, 33, 1),
(16, 34, 0),
(16, 35, 1),
(16, 36, 1),
(16, 37, 5),
(16, 38, 0),
(16, 39, 0),
(16, 40, 1),
(16, 41, 0),
(16, 42, 7),
(16, 43, 0),
(17, 3, 5),
(17, 6, 3),
(17, 22, 1),
(17, 23, 0),
(17, 24, 0),
(17, 25, 0),
(17, 26, 0),
(17, 27, 0),
(17, 28, 1),
(17, 29, 0),
(17, 30, 1),
(17, 31, 0),
(17, 32, 0),
(17, 33, 1),
(17, 34, 0),
(17, 35, 1),
(17, 36, 1),
(17, 37, 5),
(17, 38, 0),
(17, 39, 0),
(17, 40, 1),
(17, 41, 0),
(17, 42, 7),
(17, 43, 0);

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
-- Структура таблицы `contacts_types`
--

DROP TABLE IF EXISTS `contacts_types`;
CREATE TABLE `contacts_types` (
  `id` int NOT NULL,
  `name` varchar(64) NOT NULL COMMENT 'Кодовое, публичное общепринятое название',
  `uk_title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'uk - Назва типу контакту',
  `ru_title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'ru - Название типа контакта',
  `en_title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'en - Contact type name'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Типы контактов';

--
-- ССЫЛКИ ТАБЛИЦЫ `contacts_types`:
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

--
-- Очистить таблицу перед добавлением данных `devices_types`
--

TRUNCATE TABLE `devices_types`;
--
-- Дамп данных таблицы `devices_types`
--

INSERT INTO `devices_types` (`id`, `title`, `description`, `icon`, `creation_date`, `creation_uid`, `modified_date`, `modified_uid`) VALUES
(1, 'switch managed', 'Умный коммутатор', '/img/icon_dev_sw_managed_064.png', 1719098024, 1, 1720130217, 1),
(2, 'switch unmanaged', 'Мыльница', '/img/icon_sw_unmanaged_064.png', 1719098024, 1, 1720121178, 1),
(3, 'Router end users', 'Пользовательский роутер', NULL, 1719098024, 1, 1719958811, 1),
(4, 'Mikrotik', 'Оборудование mikrotik', '/img/icon_dev_mikrotik_064.png', 1719098024, 1, 1720130269, 1),
(5, 'Тестер', 'Измерительные приборы', '/img/icon_dev_tester_064.png', 1719150186, 1, 1721764484, 1),
(6, 'Зарядные устройства', 'Устройства включающие контроллер заряда', '/img/icon_dev_charger2_0064.png', 1721768417, 1, 1722019733, 1),
(7, 'Блоки питания', NULL, '/img/icon_dev_power_unit_0064.png', 1721768426, 1, 1722020302, 1);

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
  `readonly` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Установит атрибут "только чтение"',
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
  `pay_ppp_id` int UNSIGNED NOT NULL COMMENT 'Пункт приёма платежей (ППП) -- счёт, агент или предприятие куда поступили средства.',
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
-- Структура таблицы `payments_types`
--

DROP TABLE IF EXISTS `payments_types`;
CREATE TABLE `payments_types` (
  `id` int UNSIGNED NOT NULL,
  `ru_title` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ru - Тип платежа',
  `uk_title` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'uk - Название типа платежа',
  `en_title` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'en - Название типа платежа',
  `ru_description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ru - Описание типа платежа',
  `uk_description` varchar(200) NOT NULL COMMENT 'uk - Описание типа платежа',
  `en_description` varchar(200) NOT NULL COMMENT 'en - Описание типа платежа'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Типы платежей';

--
-- ССЫЛКИ ТАБЛИЦЫ `payments_types`:
--

--
-- Очистить таблицу перед добавлением данных `payments_types`
--

TRUNCATE TABLE `payments_types`;
--
-- Дамп данных таблицы `payments_types`
--

INSERT INTO `payments_types` (`id`, `ru_title`, `uk_title`, `en_title`, `ru_description`, `uk_description`, `en_description`) VALUES
(1, 'Денежное пополнение ЛС', '', '', 'Внесение средств на ЛС для оплаты услуг', '', ''),
(2, 'Корректировка ЛС', '', '', 'Начисление для корректировки остатка ЛС, компенсации', '', ''),
(3, 'Начисление за услугу', '', '', 'Начисление за дополнительную услугу (ремонт, настройка, задолженность за подключение и пр.) как правило, единоразовое начисление.', '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `phone_numbers`
--

DROP TABLE IF EXISTS `phone_numbers`;
CREATE TABLE `phone_numbers` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type_id` int NOT NULL DEFAULT '1' COMMENT 'Тип контакта, что-то типа: phone, email, telegram, viber, signal, whatsapp, nextcloud, irc, address...',
  `phone_title` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Название/Описание контакта',
  `phone_number` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Контакт: телефон, эл. почта, viber, telegram...',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Контакт скрыт, помечен для удаления',
  `creation_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Юзер, создавший запись',
  `creation_date` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата создания записис в базе',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись',
  `modified_date` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись'
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
  `order_num` int NOT NULL DEFAULT '0' COMMENT 'Порядок сортировки',
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
  `ru_title` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ru - Название типа ППП',
  `uk_title` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'uk - Название типа ППП',
  `en_title` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'en - Название типа ППП',
  `ru_description` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'ru - Описание ППП',
  `uk_description` varchar(200) NOT NULL COMMENT 'uk - Описание ППП',
  `en_description` varchar(200) NOT NULL COMMENT 'en - Описание ППП',
  `creation_date` int UNSIGNED NOT NULL COMMENT 'Дата-время создания записи',
  `creation_uid` int UNSIGNED NOT NULL COMMENT 'ID пользователя, который создал запись',
  `modified_date` int UNSIGNED NOT NULL COMMENT 'Дата-время модификации записи',
  `modified_uid` int UNSIGNED NOT NULL COMMENT 'ID пользователя, который изменил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Типы ППП';

--
-- ССЫЛКИ ТАБЛИЦЫ `ppp_types`:
--

--
-- Очистить таблицу перед добавлением данных `ppp_types`
--

TRUNCATE TABLE `ppp_types`;
--
-- Дамп данных таблицы `ppp_types`
--

INSERT INTO `ppp_types` (`id`, `ru_title`, `uk_title`, `en_title`, `ru_description`, `uk_description`, `en_description`, `creation_date`, `creation_uid`, `modified_date`, `modified_uid`) VALUES
(0, 'ХЗ', 'ХЗ', 'ХЗ', 'ХЗ', 'ХЗ', 'ХЗ', 1317416400, 1, 1317416400, 1),
(1, 'Банк', 'Банк', 'Банк', 'Р/с в банке', 'Р/с в банке', 'Р/с в банке', 1317416400, 1, 1317416400, 1),
(2, 'Карта', 'Карта', 'Карта', 'Банковская карта', 'Банковская карта', 'Банковская карта', 1317416400, 1, 1317416400, 1),
(3, 'Терминал', 'Терминал', 'Терминал', 'Платёжный терминал', 'Платёжный терминал', 'Платёжный терминал', 1317416400, 1, 1317416400, 1),
(4, 'ППП Касса', 'ППП Касса', 'ППП Касса', 'ППП Касса', 'ППП Касса', 'ППП Касса', 1317416400, 1, 1317416400, 1),
(5, 'Агент Касса', 'Агент Касса', 'Агент Касса', 'Агент Касса', 'Агент Касса', 'Агент Касса', 1317416400, 1, 1317416400, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `prices`
--

DROP TABLE IF EXISTS `prices`;
CREATE TABLE `prices` (
  `id` int UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Прайс активен, т.е. доступен к назначению.',
  `tp_id` int UNSIGNED DEFAULT NULL COMMENT 'Тариф для указанной ТП',
  `title` varchar(64) DEFAULT NULL,
  `pay_per_day` float NOT NULL DEFAULT '0',
  `pay_per_month` float NOT NULL DEFAULT '0',
  `description` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Описание тарифного пакета',
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

--
-- Очистить таблицу перед добавлением данных `prices`
--

TRUNCATE TABLE `prices`;
--
-- Дамп данных таблицы `prices`
--

INSERT INTO `prices` (`id`, `active`, `tp_id`, `title`, `pay_per_day`, `pay_per_month`, `description`, `creation_date`, `creation_uid`, `modified_date`, `modified_uid`) VALUES
(1, 1, NULL, 'M-0100', 0, 100, '100 грн/міс', 1313787600, 1, 0, NULL),
(2, 1, NULL, 'D-0100', 3.3, 0, '3,3 грн/доб (~100 грн/міс)', 1500238800, 1, 0, NULL),
(3, 1, NULL, 'D-0075', 2.5, 0, '2,5 грн/доб (~75 грн/міс)', 1514757600, 1, 1707433556, 1),
(4, 1, NULL, 'M-0075', 0, 75, '75 грн/міс', 1328047200, 1, 0, NULL),
(5, 1, NULL, 'M-0150', 0, 150, '150 грн/міс', 1381784400, 1, 0, NULL),
(6, 1, NULL, 'M-0300', 0, 300, '300 грн/міс', 1318194000, 1, 0, NULL),
(7, 1, NULL, 'M-0400', 0, 400, '400 грн/міс', 1328824800, 1, 0, NULL),
(8, 1, NULL, 'D-0300', 10, 0, '10 грн/доб (300-310 грн/міс)', 1375304400, 1, 0, NULL),
(9, 0, NULL, 'M-0050-IP', 0, 50, 'Зовнішня IP, +50 грн/міс', 1392242400, 1, 1707639325, 1),
(10, 0, NULL, 'M-0050 (off)', 0, 50, '50 грн/міс', 1366578000, 1, 0, NULL),
(11, 1, NULL, 'Службовий', 0, 0.01, 'Службовий', 1366578000, 1, 1707442856, 1),
(12, 1, NULL, 'M-1000', 0, 1000, '1000 грн/міс', 1635717600, 1, 0, NULL),
(13, 1, NULL, 'M-0200', 0, 200, '200 грн/міс', 1409518800, 1, 0, NULL),
(14, 0, NULL, 'M-0160', 0, 160, '160 грн/мес = 200 - 20% НДС', 1419502365, 1, 0, NULL),
(15, 0, NULL, 'M-0070-IP', 0, 70, 'Зовнішня IP, +70 грн/міс', 1419510229, 1, 1707445503, 1),
(16, 1, NULL, 'M-0120', 0, 120, '120 грн/міс', 1420532659, 1, 1707445320, 1),
(17, 1, NULL, 'D-0120', 4, 0, '~120 грн/міс (4 грн/доб)', 1421667673, 1, 0, NULL),
(18, 0, NULL, '.com.ua', 0, 11, 'Поддержка доменного имени 132 грн/год', 1426874413, 1, 0, NULL),
(19, 0, 40, 'HOSTING 1', 0, 8.33, 'Хостинг 1. 100 грн/рік', 1426874540, 1, 1707445546, 1),
(20, 1, NULL, 'M-0050', 0, 50, '50 грн/міс', 1440450000, 1, 0, NULL),
(21, 1, NULL, 'D-0240', 8, 0, '240-248 грн/міс, 8 грн/доб', 1453805883, 1, 0, NULL),
(22, 0, NULL, 'M-0500 2 канала', 0, 500, '500 грн/міс, 2 каналу.', 1466701762, 1, 1707639304, 1),
(23, 0, NULL, '2й канал', 0, 0, 'IP и ТП 2-го канала', 1466702205, 1, 0, NULL),
(24, 0, NULL, 'Отчисления 458', 0, -172, 'за ланетовский второй канал на R1', 1476220227, 1, 1707445450, 1),
(25, 1, NULL, 'D-0135', 4.5, 0, '4.5грн/доб', 1481645827, 1, 0, NULL),
(26, 1, NULL, 'D-0150', 5, 0, '5грн/доб, 150-155 грн/міс', 1486017454, 1, 0, NULL),
(27, 1, NULL, 'M-0500', 0, 500, '500 грн/міс', 1494970785, 1, 0, NULL),
(28, 1, NULL, 'IP-GRAY', 0, 0, 'внутр. IP адреса', 1499462426, 1, 0, NULL),
(29, 1, NULL, 'M-0600', 0, 600, '600грн/міс', 1494970785, 1, 0, NULL),
(30, 1, NULL, 'M-0180', 0, 180, '180 грн/міс', 1555924238, 1, 0, NULL),
(31, 1, NULL, 'D-0180', 6, 0, '6 грн/доб (~180 грн/міс)', 1555924238, 1, 0, NULL),
(32, 1, NULL, 'D-0210', 7, 0, '7 грн/доб (~210 грн/міс)', 1566207625, 1, 0, NULL),
(33, 1, NULL, 'D-0600', 20, 0, '600-620 грн/міс, 20 грн/доб', 1611914888, 1, 0, 1),
(34, 1, NULL, 'Отчисления M-0100', 0, -100, '-100 грн/міс', 1631686112, 1, 1707445922, 1),
(35, 1, NULL, 'M-0370', 0, 370, '370 грн/міс', 1641202483, 1, 0, 1),
(36, 1, NULL, 'M-1300', 0, 1300, '1300 грн/міс', 1654583894, 1, 0, 1),
(37, 1, NULL, 'M-0800', 0, 800, '800 грн/міс', 1663057626, 1, 0, 1),
(38, 1, NULL, 'D-0360', 12, 0, '12грн/доб. (~360грн/міс)', 1663946312, 1, 0, 1),
(39, 1, NULL, 'M-1100', 0, 1100, '1100 грн/міс', 1671378123, 1, 0, NULL),
(40, 1, NULL, 'M-1200', 0, 1200, '1200 грн/міс', 1671378253, 1, 0, NULL),
(41, 1, NULL, 'D-1200', 40, 0, '1200-1240 грн/міс, 40 грн/доб', 1671378408, 1, 0, NULL),
(42, 1, NULL, 'D-0900', 30, 0, '900-930 грн/міс, 30 грн/доб', 1671378490, 1, 0, 1),
(43, 1, NULL, 'Отчисления D-0200', -6.6, 0, '-200 грн/міс, 6.6 грн/доб', 1631686112, 1, 1707445617, 1),
(45, 1, NULL, 'D-0420', 14, 0, '420 грн/30 діб, 14 грн/доб', 0, 1, 0, 1),
(46, 1, NULL, 'D-0540', 18, 0, '18 грн/доб (540 грн/30 діб)', 1719839315, 1, 1719839315, 1),
(47, 1, NULL, 'D-0450', 15, 0, '15 грн/доб (450 грн/30 діб)', 1730992896, 1, 1730992896, 1),
(48, 1, NULL, 'D-0480', 16, 0, '16 грн/доб (480 грн/30 діб)', 1730992921, 1, 1730992921, 1),
(49, 1, NULL, 'M-0410', 0, 410, '410 грн/міс (~13.67 грн/добу)', 1731010064, 10, 1731010064, 10),
(50, 1, NULL, 'D-0660', 22, 0, '22 грн/доб (660 грн/30 діб)', 1733837006, 1, 1733837006, 1),
(51, 1, NULL, 'D-0690', 23, 0, '23 грн/доб (690 грн/30 діб)', 1733847558, 1, 1733847558, 1),
(52, 1, NULL, 'M-1750', 0, 1750, '1750 грн/міс (~58.33 грн/добу)', 1736353251, 1, 1736353251, 1),
(53, 1, NULL, 'D-1080', 36, 0, '36 грн/доб (1080 грн/30 діб)', 1737640314, 1, 1737640314, 1),
(54, 1, NULL, 'D-0720', 24, 0, '24 грн/доб (720 грн/30 діб)', 1749688090, 1, 1749688090, 1),
(55, 1, NULL, 'D-0750', 25, 0, '25 грн/доб (750 грн/30 діб)', 1749688112, 1, 1749688112, 1),
(56, 1, NULL, 'M-1500', 0, 1500, '1500 грн/міс (~50.00 грн/добу)', 1755174000, 1, 1755174000, 1);

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
  `_x_net_ip_status` int NOT NULL DEFAULT '0' COMMENT '0 - ничего не делалли; 1 - ИП запись создана; 2 - ИП заморожен; 3 - ИП активен; 4 - ИП удалён.',
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
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 — Отключен/демонтирован, 1 — Работает',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ТП демонтирована',
  `is_managed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН',
  `rang_id` int UNSIGNED DEFAULT NULL COMMENT 'Ранг узла: 1 — Абонентский узел.  2 — AP.  3 — Агрегатор AP.  4 — Bridge AP.  5 — Bridge Client.  10 — Хостинговая тех. площадка.  100 — Биллинг. ',
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
  `uplink_id` int UNSIGNED DEFAULT NULL COMMENT 'Узел "верхнего" уровня, от которого идёт сигнал к этому узлу (не обязательно маршрутизатор)',
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
  `script_mik_port` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '8728' COMMENT 'API tcp порт доступа на устройство',
  `script_mik_port_ssl` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '8729' COMMENT 'API tcp порт для SSL доступа на устройство',
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

--
-- Очистить таблицу перед добавлением данных `tp_rangs`
--

TRUNCATE TABLE `tp_rangs`;
--
-- Дамп данных таблицы `tp_rangs`
--

INSERT INTO `tp_rangs` (`id`, `title`, `description`) VALUES
(0, 'N/A', 'N/A'),
(1, 'Абон. узел', '1 — Абонентский узел (station/client/router)'),
(2, 'Маршрутизатор', '2 — Точка доступа, Узел подключения абонентов, управления доступом в интернет абонентов (AP, router)'),
(3, 'Агрегатор', '3 — Агрегатор узлов подключения (WR)'),
(4, 'ТД Мост', '4 — Bridge AP. ТД Радиоудлинитель'),
(5, 'Абон. Мост', '5 — Client Bridge. Оборудование в режиме Мост на стороне абонента'),
(10, 'Хостинг', '10 -- Хостинговая тех. площадка'),
(100, 'Биллинг', '100 — Сервер биллинга');

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
  `signal_messenger` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Имя учётной записи месенджера Signal',
  `signal_do_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отправлять сообщения на Signal',
  `whatsapp` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'Имя учётной записи месенджера WhatsApp',
  `whatsapp_do_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отправлять сообщения на WhatsApp',
  `prava` smallint NOT NULL DEFAULT '0' COMMENT '0 == клиент / пользователь, \r\n1 и более == Административные привилегии',
  `creation_uid` int UNSIGNED DEFAULT NULL COMMENT 'Кто создал запись',
  `creation_date` int NOT NULL DEFAULT '0' COMMENT 'Дата создания записи',
  `modified_uid` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Кто изменил запись',
  `modified_date` int NOT NULL DEFAULT '0' COMMENT 'Дата изменения записи в базе'
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
-- Индексы таблицы `adm_menu`
--
ALTER TABLE `adm_menu`
  ADD PRIMARY KEY (`id`);

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
-- Индексы таблицы `contacts_types`
--
ALTER TABLE `contacts_types`
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
-- Индексы таблицы `mail_list`
--
ALTER TABLE `mail_list`
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
  ADD KEY `pay_fakt` (`pay_fakt`);
ALTER TABLE `payments` ADD FULLTEXT KEY `pay_bank_no` (`pay_bank_no`);

--
-- Индексы таблицы `payments_calculates`
--
ALTER TABLE `payments_calculates`
  ADD PRIMARY KEY (`user_id`,`payment_type_id`,`payment_source_id`);

--
-- Индексы таблицы `payments_types`
--
ALTER TABLE `payments_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `phone_numbers`
--
ALTER TABLE `phone_numbers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT для таблицы `adm_menu`
--
ALTER TABLE `adm_menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID элемента меню', AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT для таблицы `adm_module_list`
--
ALTER TABLE `adm_module_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID административного модуля', AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT для таблицы `adm_role_list`
--
ALTER TABLE `adm_role_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID административной группы', AUTO_INCREMENT=18;

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
-- AUTO_INCREMENT для таблицы `contacts_types`
--
ALTER TABLE `contacts_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_list`
--
ALTER TABLE `devices_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_types`
--
ALTER TABLE `devices_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- AUTO_INCREMENT для таблицы `mail_list`
--
ALTER TABLE `mail_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT для таблицы `payments_types`
--
ALTER TABLE `payments_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID типа ППП', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `prices`
--
ALTER TABLE `prices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

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


--
-- Метаданные
--
USE `phpmyadmin`;

--
-- Метаданные для таблицы abons
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы abon_rest
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adm_menu
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adm_module_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adm_role_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adm_role_module_permissions
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adm_user_role
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adr_countries
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adr_district
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы adr_regions
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы bank_p24_acc
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы bank_p24_card
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы cards
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы cards_on_ppp
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы cards_pools
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы contacts_types
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы devices_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы devices_types
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы documents
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы files_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы firm_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы mail_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы nat_forward
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы news
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы payments
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы payments_calculates
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы payments_types
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы phone_numbers
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы ppp_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы ppp_types
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы prices
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы prices_apply
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы sf_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы sms_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы tcp_proto_numbers
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы todo_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы todo_priority
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы todo_titles
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы todo_types
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы tp_group_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы tp_list
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы tp_rangs
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы ts_abons_templates
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы ts_firms_users
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы ts_user_tp
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы users
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для таблицы users_actions
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__column_info: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__column_info&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__table_uiprefs: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__table_uiprefs&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__tracking: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__tracking&#039;

--
-- Метаданные для базы данных billing
--
-- Ошибка считывания данных таблицы phpmyadmin.pma__bookmark: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__bookmark&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__relation: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__relation&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__savedsearches: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__savedsearches&#039;
-- Ошибка считывания данных таблицы phpmyadmin.pma__central_columns: #1142 - Команда SELECT запрещена пользователю &#039;billing&#039;@&#039;localhost&#039; для таблицы &#039;pma__central_columns&#039;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
