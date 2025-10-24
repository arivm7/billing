<?php
/*
 *  Project : my.ri.net.ua
 *  File    : phone_functions.php
 *  Path    : billing/libs/phone_functions.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */


$phoneNumberLengths = [
    // Код страны => [минимальная длина, максимальная длина]
    '1'   => [11, 11], // США, Канада
    '7'   => [11, 11], // Россия, Казахстан
    '20'  => [11, 12], // Египет
    '27'  => [11, 12], // ЮАР
    '30'  => [11, 12], // Греция
    '31'  => [11, 12], // Нидерланды
    '32'  => [11, 12], // Бельгия
    '33'  => [11, 11], // Франция
    '34'  => [11, 12], // Испания
    '36'  => [11, 12], // Венгрия
    '39'  => [11, 13], // Италия
    '40'  => [11, 12], // Румыния
    '41'  => [11, 12], // Швейцария
    '43'  => [11, 13], // Австрия
    '44'  => [11, 13], // Великобритания
    '45'  => [11, 11], // Дания
    '46'  => [11, 12], // Швеция
    '47'  => [11, 12], // Норвегия
    '48'  => [11, 12], // Польша
    '49'  => [11, 13], // Германия
    '52'  => [11, 13], // Мексика
    '53'  => [11, 12], // Куба
    '54'  => [11, 13], // Аргентина
    '55'  => [12, 13], // Бразилия
    '56'  => [11, 12], // Чили
    '57'  => [11, 12], // Колумбия
    '58'  => [11, 12], // Венесуэла
    '60'  => [11, 12], // Малайзия
    '61'  => [11, 12], // Австралия
    '62'  => [11, 13], // Индонезия
    '63'  => [11, 12], // Филиппины
    '64'  => [11, 12], // Новая Зеландия
    '65'  => [11, 11], // Сингапур
    '66'  => [11, 12], // Таиланд
    '81'  => [11, 12], // Япония
    '82'  => [11, 12], // Южная Корея
    '84'  => [11, 12], // Вьетнам
    '86'  => [13, 13], // Китай
    '90'  => [11, 13], // Турция
    '91'  => [12, 12], // Индия
    '92'  => [11, 12], // Пакистан
    '93'  => [11, 12], // Афганистан
    '94'  => [11, 12], // Шри-Ланка
    '95'  => [11, 12], // Мьянма
    '98'  => [11, 12], // Иран
    '212' => [11, 13], // Марокко
    '213' => [11, 12], // Алжир
    '216' => [11, 12], // Тунис
    '218' => [11, 12], // Ливия
    '234' => [11, 13], // Нигерия
    '251' => [11, 12], // Эфиопия
    '254' => [11, 12], // Кения
    '263' => [11, 12], // Зимбабве
    '351' => [11, 12], // Португалия
    '352' => [11, 12], // Люксембург
    '353' => [11, 12], // Ирландия
    '354' => [11, 12], // Исландия
    '355' => [11, 12], // Албания
    '356' => [11, 12], // Мальта
    '357' => [11, 12], // Кипр
    '358' => [11, 13], // Финляндия
    '359' => [11, 12], // Болгария
    '370' => [11, 12], // Литва
    '371' => [11, 12], // Латвия
    '372' => [11, 12], // Эстония
    '373' => [11, 12], // Молдова
    '374' => [11, 12], // Армения
    '375' => [12, 12], // Беларусь
    '380' => [12, 12], // Украина
    '381' => [11, 12], // Сербия
    '382' => [11, 12], // Черногория
    '383' => [11, 12], // Косово
    '385' => [11, 12], // Хорватия
    '386' => [11, 12], // Словения
    '387' => [11, 12], // Босния и Герцеговина
    '389' => [11, 12], // Северная Македония
];

/**
 * Проверка и приведение номера к международному формату
 * вида: +<код_страны><внутренний_номер>
 * чо-то такое: +380120123456789
 * @param string $phone
 * @return string formattedPhoneNumber
 * @throws InvalidArgumentException
 */
function cleaningPhoneNumber(string $phone): string {

    // Удаляем все символы, кроме цифр и плюса
    $cleaned = preg_replace('/[^\d+]/', '', $phone);

    // Если номер начинается с "+"
    if (strpos($cleaned, '+') === 0) {
        $digits = '+' . preg_replace('/\D/', '', substr($cleaned, 1));

        // Извлекаем код страны
        $code = extractCountryCode($digits);

        // Проверка длины
        validatePhoneNumberLength($digits, $code);

        return $digits;
    }

    // Если номер начинается с 0 и содержит 10 цифр — украинский
    $digits = preg_replace('/\D/', '', $cleaned);
    if (preg_match('/^0\d{9}$/', $digits)) {
        $formatted = '+380' . substr($digits, 1);
        validatePhoneNumberLength($formatted, '380');
        return $formatted;
    }

    // Если начинается с 8 и содержит 11 цифр — также украинский
    if (preg_match('/^8\d{10}$/', $digits)) {
        $formatted = '+380' . substr($digits, 2);
        validatePhoneNumberLength($formatted, '380');
        return $formatted;
    }

    // Если просто 9 цифр — украинский номер без префикса
    if (preg_match('/^\d{9}$/', $digits)) {
        $formatted = '+380' . $digits;
        validatePhoneNumberLength($formatted, '380');
        return $formatted;
    }

    // Всё остальное — неполный или непонятный номер
    throw new InvalidArgumentException("Не удалось определить формат номера. Укажите номер в международном формате, например +380661234567.");
}



/**
 * Извлекает код страны (находит подходящий код в начале номера)
 * Ищет сперва 3 цифры, затем 2, затем 1
 * @global array $phoneNumberLengths
 * @param string $number
 * @return string $countryPrefix
 * @throws InvalidArgumentException
 */
function extractCountryCode(string $number): string {
    global $phoneNumberLengths;

    if (!is_array($phoneNumberLengths)) {
        throw new InvalidArgumentException("Внутренний сбой: список кодов стран не загружен.");
    }

    $numberWithoutPlus = ltrim($number, '+');

    // Пробуем сначала 3 цифры, затем 2, затем 1
    for ($i = 3; $i >= 1; $i--) {
        $countryPrefix = substr($numberWithoutPlus, 0, $i);
        if (array_key_exists($countryPrefix, $phoneNumberLengths)) {
            return $countryPrefix;
        }
    }

    throw new InvalidArgumentException("Неизвестный или неподдерживаемый код страны в номере $number.");
}



/**
 * Проверяет длину номера с учётом кода страны
 * @param string $number
 * @param string $code
 * @global array $phoneNumberLengths
 * @return bool
 * @throws InvalidArgumentException
 */
function validatePhoneNumberLength(string $number, string $code): bool {
    global $phoneNumberLengths;

    $digitsOnly = preg_replace('/\D/', '', $number);

    if (!isset($phoneNumberLengths[$code])) {
        throw new InvalidArgumentException("Код страны +$code не поддерживается для валидации.");
    }

    $len = strlen($digitsOnly);
    [$min, $max] = $phoneNumberLengths[$code];

    if ($len < $min || $len > $max) {
        throw new InvalidArgumentException("Номер телефона с кодом +$code должен содержать от $min до $max цифр. Указано: $len.");
    }

    return true;
}



/**
 * Простая проверка и упорядочивание номера телефона
 * @param string $phone
 * @return string
 * @throws InvalidArgumentException
 */
function simpleCleaningPhoneNumber(string $phone): string {
    // Удаляем всё, кроме цифр и плюса
    $cleaned = preg_replace('/[^\d+]/', '', $phone);

    // Если начинается с +
    if (strpos($cleaned, '+') === 0) {
        $number = '+' . preg_replace('/\D/', '', substr($cleaned, 1));

        // Проверим: есть ли после плюса хотя бы 11 цифр (например: +380661234567)
        if (strlen($number) < 12) {
            throw new InvalidArgumentException("Недостаточно цифр после кода страны. Убедитесь, что номер полный: например, +380661234567.");
        }

        return $number;
    }

    // Если без +: убираем всё, что не цифры
    $digits = preg_replace('/\D/', '', $cleaned);

    // Если начинается с 0 и длина 10 — это украинский мобильный
    if (preg_match('/^0\d{9}$/', $digits)) {
        return '+380' . substr($digits, 1);
    }

    // Если начинается с 8 и длина 11 — тоже украинский (старый формат)
    if (preg_match('/^8\d{10}$/', $digits)) {
        return '+380' . substr($digits, 2);
    }

    // Если ровно 9 цифр — считаем украинским без кода
    if (preg_match('/^\d{9}$/', $digits)) {
        return '+380' . $digits;
    }

    // Слишком мало цифр — бросаем исключение
    if (strlen($digits) < 10) {
        throw new InvalidArgumentException("Слишком мало цифр в номере телефона. Укажите полный номер.");
    }

    // Остальное — слишком непонятно, просим пользователя уточнить
    throw new InvalidArgumentException("Невозможно определить формат номера. Укажите номер в международном формате, например: +380661234567.");
}