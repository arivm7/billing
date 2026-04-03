<?php
/**
 *  Project : my.ri.net.ua
 *  File    : P24card.php
 *  Path    : config/P24card.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 31 Mar 2026 15:49:28
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of P24card.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */





namespace config;

use billing\core\App;
use config\tables\Pay;

class P24card
{

    const INDEX_DATE_STR             = 0; // Дата
    const INDEX_CATEGORY             = 1; // Категорія транзакции
    const INDEX_CARD                 = 2; // Картка
    const INDEX_DESCR                = 3; // Опис операції
    const INDEX_AMOUNT               = 4; // Сума в валюті картки
    const INDEX_CARD_CURRENCY        = 5; // Валюта картки
    const INDEX_AMOUNT_TRANSACTION   = 6; // Сума в валюті транзакції
    const INDEX_TRANSACTION_CURRENCY = 7; // Валюта транзакції
    const INDEX_REST                 = 8; // Залишок на кінець періоду
    const INDEX_REST_CURRENCY        = 9; // Валюта залишку


    /**
     * Поля транзакции полученной из табличного файла
     */
    const F_DATE_STR            = 'DATE_STR';           // Дата в формате строки
    const F_DATE                = 'DATE';               // Дата unix timestamp
    const F_AMOUNT              = 'AMOUNT';             // Сума в валюте карты
    const F_AMOUNT_TRANSACTION  = 'AMOUNT_TRANSACTION'; // Сума в валюте транзакции
    const F_CURRENCY            = 'CURRENCY';           // Валюта картки
    const F_REST                = 'REST';               // Остаток на конец транзакции
    const F_BANK_NO             = 'BANK_NO';            // Формируемый уникальный номер транзакции
    const F_DESCRIPTION         = 'DESCR';              // Описание транзакции, назначение платежа



    /**
     * Название поля воода текста сырых табличных данных
     */
    const F_RAW_TEXT = 'text_raw';



    /**
     * Названия полей транзакции на трёх языках
     */
    const TRANSACTION_FIELD_TITLE = [
        self::F_DATE_STR => [
            'en' => "Date (string)",
            'ru' => "Дата (строкой)",
            'uk' => "Дата (рядком)",
        ],
        self::F_DATE => [
            'en' => "Date (timestamp)",
            'ru' => "Дата (timestamp)",
            'uk' => "Дата (timestamp)",
        ],
        self::F_AMOUNT => [
            'en' => "Amount (card currency)",
            'ru' => "Сумма (валюта карты)",
            'uk' => "Сума (валюта картки)",
        ],
        self::F_AMOUNT_TRANSACTION => [
            'en' => "Amount (transaction currency)",
            'ru' => "Сумма (валюта транзакции)",
            'uk' => "Сума (валюта транзакції)",
        ],
        self::F_CURRENCY => [
            'en' => "Card currency",
            'ru' => "Валюта карты",
            'uk' => "Валюта картки",
        ],
        self::F_REST => [
            'en' => "Balance remainder",
            'ru' => "Остаток на конец транзакции",
            'uk' => "Залишок на кінець транзакції",
        ],
        self::F_BANK_NO => [
            'en' => "Bank transaction number",
            'ru' => "Уникальный номер транзакции",
            'uk' => "Унікальний номер транзакції",
        ],
        self::F_DESCRIPTION => [
            'en' => "Description",
            'ru' => "Описание транзакции",
            'uk' => "Опис транзакції",
        ],
    ];


    /**
     * Получить название поля на заданном языке
     *
     * @param string $field Имя константы поля
     * @param string|null $lang Язык ('en', 'ru', 'uk'). Если null, используется язык приложения.
     * @return string Название поля на заданном языке
     */
    public static function field_title(string $field, ?string $lang = null): string
    {
        if (empty($lang)) { $lang = App::lang(); }

        return (empty(self::TRANSACTION_FIELD_TITLE[$field])
                    ?   ''
                    :   self::TRANSACTION_FIELD_TITLE[$field][$lang]
                        ?? self::TRANSACTION_FIELD_TITLE[$field]['ru']
                        ?? self::TRANSACTION_FIELD_TITLE[$field]['uk']
                        ?? self::TRANSACTION_FIELD_TITLE[$field]['en']
                        ?? $field
                );
    }


    /**
     * Парсинг списка транзакций из текста (например, выгрузка из банка или из таблицы)
     *
     * @param string $text_raw Исходный текст с транзакциями (разделитель строк - \n, полей - \t)
     * @return array Массив нормализованных транзакций с полями:
     *               - F_PAY_FAKT: сумма фактической оплаты
     *               - F_PAY_ACNT: сумма по счёту
     *               - F_REST: остаток на счёте
     *               - F_DATE_STR: дата строкой
     *               - F_DATE: дата в формате timestamp
     *               - F_BANK_NO: уникальный номер банка (генерируется)
     *               - F_DESCRIPTION: описание транзакции
     * @throws Exception При некорректном формате даты
     */
    public static function get_transactions(string $text_raw): array {

        $char_off = ["\"", "'"];
        $digit_off = "/[^0-9\,\.\-]*/";

        // Очищает входной текст от кавычек и лишних пробелов
        $text_raw = htmlentities(str_replace("  ", " ", (str_replace($char_off, '', trim($text_raw)))), ENT_QUOTES);
        // Разбивает текст на строки
        $lines = preg_split("/[\n]/", $text_raw);
        $transactions = [];
        foreach ($lines as $line) {
            /**
             * Для каждой строки:
             *      Разделяет по табуляции на поля
             *      Извлекает сумму, остаток, дату и описание
             *      Конвертирует дату в timestamp
             *      Генерирует уникальный номер транзакции
             */

            $line = trim($line);
            if (is_empty($line)) { continue; }
            
            // Разделяет по табуляции на поля
            $line_rec = preg_split("/[\t]/", $line);

            // Формируем запись транзакции в соответсвии с записью биллинга
            $transaction[self::F_AMOUNT]                = floatval(str_replace(",", ".", (preg_replace($digit_off, '', $line_rec[P24card::INDEX_AMOUNT]))));
            if ($transaction[self::F_AMOUNT] < 0) { continue; }
            $transaction[self::F_DATE_STR]              = str_replace($char_off, '', trim($line_rec[P24card::INDEX_DATE_STR]));
            $transaction[self::F_DATE]                  = strtotime($transaction[P24card::F_DATE_STR]);
            if ($transaction[P24card::F_DATE] === false) {
                throw new \Exception("Некорректная дата: ".h($line_rec[P24card::INDEX_DATE_STR]) . ' => ' . h($transaction[self::F_DATE_STR]) . ' Обратитесь к программистам');
            }
            $transaction[self::F_AMOUNT_TRANSACTION]    = floatval(str_replace(",", ".", (preg_replace($digit_off, '', $line_rec[P24card::INDEX_AMOUNT_TRANSACTION]))));
            $transaction[self::F_REST]                  = floatval(str_replace(",", ".", (preg_replace($digit_off, '', $line_rec[P24card::INDEX_REST]))));
            $transaction[self::F_CURRENCY]              = normal_str($line_rec[P24card::INDEX_CARD_CURRENCY]);
            $transaction[self::F_BANK_NO]               = Bank::make_bank_no($transaction[P24card::F_DATE], $transaction[P24card::F_AMOUNT], $transaction[P24card::F_REST]);
            $transaction[self::F_DESCRIPTION]           = normal_str($line_rec[P24card::INDEX_DESCR]);

            $transactions[] = $transaction;
        }

        return $transactions;
    }







}


