<?php
/**
 *  Project : s1.ri.net.ua
 *  File    : Auth.php
 *  Path    : config/Auth.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 11 Oct 2025 18:00:57
 *  License : GPL v3
 *
 *  @copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace Config;

/**
 * Класс-обёртка для констант авторизации
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class Auth
{
    /**
     * Форма регистрации нового пользователя
     */
    const URI_SIGNUP = '/auth/signup';

    /**
     * Форма login для входа в систему
     */
    const URI_LOGIN = '/auth/login';

    /**
     * Команда длявыхода из системы
     */
    const URI_LOGOUT = '/auth/logout';

}