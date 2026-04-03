<?php
/*
 *  Project : my.ri.net.ua
 *  File    : Pay.php
 *  Path    : config/tables/Pay.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config\tables;

use billing\core\App;
use billing\core\base\Lang;

/**
 * Description of Pay.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Pay {

    /*
     * URI для пользовательских платежей
     */

    public const URI_PAY = '/pay';

    /*
     * URI для управления платежами
     */

    public const URI_FORM      = '/payments/form';
    public const URI_DEL       = '/payments/delete';

    /*
     * URI для личного кабинета абонента
     */
    public const URI_MY            = '/payments';
    public const URI_LIST          = '/payments/list';

    public const POST_REC = 'payment';

    public const TABLE = 'payments';

    public const F_ID = "id";                          // ID платежа в биллинге

    public const F_AGENT_ID        = "agent_id";       // ID пользователя, кто внёс запись
    public const F_ABON_ID         = "abon_id";        // Абонент, на которого зачисляется платеж
    public const F_PAY_FAKT        = "pay_fakt";       // Фактическая сумма, пришедшая на счёт
    public const F_PAY_ACNT        = "pay";            // Сумма платежа, вносимая на ЛС
    public const F_DATE            = "pay_date";       // Дата платежа
    public const F_DATE_STR        = "pay_date_str";   // Дата платежа в строковом формате
    public const F_REST            = "pay_bank_rest";  // Остаток на счету после данной транзакции (для контроля банка) 
    public const F_BANK_NO         = "pay_bank_no";    // Банковский номер операции
    public const F_TYPE_ID         = "pay_type_id";    // ИД Типа платежа
    public const F_PPP_ID          = "pay_ppp_id";     // ППП
    public const F_DESCRIPTION     = "description";    // Описание платежа
    public const F_CREATION_DATE   = "created_date";   // Дата создания записи
    public const F_CREATION_UID    = "created_uid";    // Юзер, создавший запись
    public const F_MODIFIED_DATE   = "modified_date";  // Дата изменения записи
    public const F_MODIFIED_UID    = "modified_uid";   // Кто изменил запись


    /**
     * Суффикс, добавляемый к описанию (F_DESCRIPTION) при сохранении в биллинге
     */
    public const F_SAVE_SUFFIX     = "save_suffix";    



    /**
     * Чекбокс флага для отметки записей для внесения в биллинг
     */
    public const F_POST_SAVE = 'save';



    /*
     * Поля таблицы, используемые для сравнения имеющейся и исправленной записей
     * для записи в базу только изменённых полей
     * Поля таблицы, используемые для сохранения записи
     */
    public const SAVE_FIELDS = [
        self::F_AGENT_ID,
        self::F_ABON_ID,
        self::F_PAY_FAKT,
        self::F_PAY_ACNT,
        self::F_REST,
        self::F_DATE,
        self::F_BANK_NO,
        self::F_TYPE_ID,
        self::F_PPP_ID,
        self::F_DESCRIPTION,
    ];



    public const TEXT_FIELDS = [
        self::F_DATE_STR,       // Дата платежа в строковом формате
        self::F_BANK_NO,        // Банковский номер операции
        self::F_DESCRIPTION,    // Описание платежа
    ];

    

    public const RECALC_FIELDS = [
        self::F_ABON_ID,
        self::F_PAY_FAKT,
        self::F_PAY_ACNT,
    ];



    /*
     * Вычисляемые поля
     */
    public const F_AGENT_TITLE     = "agent_title";    // Имя того, кто внёс запись (вычисляемое)
    public const F_TYPE_TITLE      = "pay_type_title"; // Имя Типа платежа (вычисляемое)
    public const F_PPP_TITLE       = "pay_ppp_title";  // Имя ППП (вычисляемое)



    /**
     * Описание полей таблицы платежей
     */
    public const FIELDS_TITLE = [
        self::F_ID => [
            'uk' => 'ID платежу в білінгу',
            'ru' => 'ID платежа в биллинге',
            'en' => 'Payment ID in billing system',
        ],
        self::F_AGENT_ID => [
            'uk' => 'ID користувача, який вніс платіж',
            'ru' => 'ID пользователя, который внёс платёж',
            'en' => 'User ID who added the payment',
        ],
        self::F_ABON_ID => [
            'uk' => 'Абонент, на якого зараховується платіж',
            'ru' => 'Абонент, на которого зачисляется платёж',
            'en' => 'Subscriber to whom the payment is credited',
        ],
        self::F_PAY_FAKT => [
            'uk' => 'Фактична сума платежу',
            'ru' => 'Фактическая сумма платежа',
            'en' => 'Actual received payment amount',
        ],
        self::F_PAY_ACNT => [
            'uk' => 'Сума, зарахована на особовий рахунок',
            'ru' => 'Сумма, зачисленная на лицевой счёт',
            'en' => 'Amount credited to personal account',
        ],
        self::F_REST => [
            'uk' => 'Залишок у банку',
            'ru' => 'Остаток в банке',
            'en' => 'Bank balance',
        ],
        self::F_DATE => [
            'uk' => 'Дата платежу',
            'ru' => 'Дата платежа',
            'en' => 'Payment date',
        ],
        self::F_DATE_STR => [
            'uk' => 'Дата платежу (рядок)',
            'ru' => 'Дата платежа (строка)',
            'en' => 'Payment date (string)',
        ],
        self::F_BANK_NO => [
            'uk' => 'Банківський номер операції',
            'ru' => 'Банковский номер операции',
            'en' => 'Bank transaction number',
        ],
        self::F_TYPE_ID => [
            'uk' => 'Тип платежу',
            'ru' => 'Тип платежа',
            'en' => 'Payment type',
        ],
        self::F_PPP_ID => [
            'uk' => 'ППП',
            'ru' => 'ППП',
            'en' => 'PPP',
        ],
        self::F_DESCRIPTION => [
            'uk' => 'Опис платежу',
            'ru' => 'Описание платежа',
            'en' => 'Payment description',
        ],
        self::F_SAVE_SUFFIX => [
            'uk' => 'Суфікс для опису платежу',
            'ru' => 'Суффикс для описания платежа',
            'en' => 'Suffix to describe the payment',
        ],
        self::F_CREATION_DATE => [
            'uk' => 'Дата створення',
            'ru' => 'Дата создания',
            'en' => 'Creation date',
        ],
        self::F_CREATION_UID => [
            'uk' => 'Хто створив запис',
            'ru' => 'Кто создал запись',
            'en' => 'Created by user',
        ],
        self::F_MODIFIED_DATE => [
            'uk' => 'Дата зміни',
            'ru' => 'Дата изменения',
            'en' => 'Last modified date',
        ],
        self::F_MODIFIED_UID => [
            'uk' => 'Хто змінив запис',
            'ru' => 'Кто изменил запись',
            'en' => 'Modified by user',
        ],
    ];



    /**
     * 
     * ТИПЫ ПЛАТЕЖЕЙ
     * 
     * Соответствует ID из таблицы payments_types
     * Фактически аблица типов не нужна,
     * поскольку типов всего три и удобнее их использовать как константы
     */
    public const TYPE_MONEY    = 1;    // Денежное пополнение ЛС | Внесение средств на ЛС для оплаты услуг
    public const TYPE_CORRECT  = 2;    // Корректировка ЛС       | Начисление для корректировки остатка ЛС, компенсац...
    public const TYPE_REQUEST  = 3;    // Начисление за услугу   | Начисление за дополнительную услугу (ремонт, настройка, задолженность за подключение и пр.) как правило, единоразовое начисление.


    
    public const TYPES_TITLE = [
        self::TYPE_MONEY => [
            'uk' => 'Грошове поповнення ОР',
            'ru' => 'Денежное пополнение ЛС',
            'en' => 'Cash replenishment of personal account',
        ],
        self::TYPE_CORRECT => [
            'uk' => 'Коригування залишку в особистому кабінеті',
            'ru' => 'Корректировка остатка в личном кабинете',
            'en' => 'Adjusting the balance in your personal account',
        ],
        self::TYPE_REQUEST => [
            'uk' => 'Нарахування за додаткову разову послугу',
            'ru' => 'Начисление за дополнительную разовую услугу',
            'en' => 'Charge for additional one-time service',
        ],
    ];



    public const TYPES_DESCR = [
        self::TYPE_MONEY => [
            'uk' => 'Внесення коштів на ОР для оплати послуг',
            'ru' => 'Внесение средств на ЛС для оплаты услуг',
            'en' => 'Depositing funds to a personal account to pay for services',
        ],
        self::TYPE_CORRECT => [
            'uk' => 'Нарахування для коригування залишку ОР, компенсації помилок та ін.',
            'ru' => 'Начисление для корректировки остатка ЛС, компенсации ошибок и пр.',
            'en' => 'Accrual for adjusting the balance of medicinal products, compensating for errors, etc.',
        ],
        self::TYPE_REQUEST => [
            'uk' => 'Нарахування за додаткову послугу (ремонт, налаштування, заборгованість за підключення тощо), як правило, одноразове нарахування',
            'ru' => 'Начисление за дополнительную услугу (ремонт, настройка, задолженность за подключение и пр.) как правило, единоразовое начисление',
            'en' => 'Charge for additional one-time service (repair, setup, debt for connection, etc.), usually a one-time charge',
        ],
    ];



    public static function field_title(string $field): string {
        return self::FIELDS_TITLE[$field][Lang::code()]
            ?? "<span title='Описание поля не найдено'>" . $field . "</span>";
    }



    public static function type_title(int $type_id): string {
        return self::TYPES_TITLE[$type_id][Lang::code()] ?? 'ERROR';
    }



    public static function type_descr(int $type_id): string {
        return self::TYPES_DESCR[$type_id][Lang::code()] ?? 'ERROR';
    }



}