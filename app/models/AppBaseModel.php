<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AppBaseModel.php
 *  Path    : app/models/AppBaseModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\Model;
use config\Icons;
use config\tables\Abon;
use config\tables\Module;
use config\tables\Pay;
use config\tables\Ppp;
use config\tables\PppType;
use config\tables\Price;
use config\tables\TP;
use config\tables\TSUserTp;
use config\tables\User;
use config\tables\PA;
use PAStatus;

require_once DIR_LIBS . '/datetime_functions.php';
require_once DIR_LIBS . '/billing_functions.php';

/**
 * Description of AppBaseModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class AppBaseModel extends Model
{

    /**
     * Кэш-таблица для get_pay(int $id)
     */
    private static array $CASHE_PAY_LIST = array();

    /**
     * Возвращает запись платежа их кэша $CASHE_PAY_LIST.
     * Если в кэше записи нет, то читает из базы, записывает туда, а затем возвращает.
     * @global array $CASHE_PAY_LIST
     * @param int $id
     * @return array
     */
    function get_pay(int $id): array {
        if (!array_key_exists($id, self::$CASHE_PAY_LIST)) {
            self::$CASHE_PAY_LIST[$id] = $this->get_row_by_id(Pay::TABLE, $id, Pay::F_ID);
            $this->pay_update_fields(self::$CASHE_PAY_LIST[$id]);
        }
        return self::$CASHE_PAY_LIST[$id];
    }



    /**
     * Кэш-таблица для function get_price(int $id)
     */
    private static array $CASHE_PRICE_LIST = array();

    /**
     * Возвращает запись прайса из кэша $CASHE_PRICE_LIST.
     * Если в кэше записи нет, то читает из базы, записывает туда, а затем возвращает.
     * @global array $CASHE_PRICE_LIST
     * @param int $id
     * @return array
     */
    function get_price(int $id): array {
        if (!array_key_exists($id, self::$CASHE_PRICE_LIST)) {
            self::$CASHE_PRICE_LIST[$id] = $this->get_row_by_id(Price::TABLE, $id, Price::F_ID);
        }
        return self::$CASHE_PRICE_LIST[$id];
    }



    function get_prices(int|null $tp_id = null, bool $include_null = true): array {
        if (!is_null($tp_id)) {
            $list1 = $this->get_rows_by_sql("SELECT * FROM `prices` WHERE `active` AND (`tp_id` = {$tp_id})");
        } else {
            $list1 = [];
        }

        if ($include_null) {
            $list2 = $this->get_rows_by_sql("SELECT * FROM `prices` WHERE `active` AND (`tp_id` IS NULL)");
        } else {
            $list2 = [];
        }

        return array_merge($list1, $list2);
    }


    function get_id_vector(string $sql) {
        return $this->query(sql: $sql, fetchVector: 0);
    }



    function get_my_tp_id_list(
            ?int $user_id = null,
            ?int $active = 1,
            ?int $deleted = null,
            ?int $is_managed = null,
            ?int $rang_id = null,
            ?array $tp_list_id = null): array
    {
        $user_id = $user_id ?? App::get_user_id();
     // $sql = "SELECT `".TSUserTp::F_TP_ID."` FROM `".TSUserTp::TABLE."` WHERE `".TSUserTp::F_USER_ID."` = {$user_id}";
        $sql = "SELECT "
                . "".TP::TABLE.".".TP::F_ID." "
                . "FROM `".TSUserTp::TABLE."` "
                . "LEFT JOIN ".TP::TABLE." ON ".TSUserTp::TABLE.".".TSUserTp::F_TP_ID." = ".TP::TABLE.".".TP::F_ID." "
                . "WHERE "
                . "(`".TSUserTp::F_USER_ID."`={$user_id}) "
                . (!empty($tp_list_id) ? "AND (`".TSUserTp::F_TP_ID."` IN (".implode(',', $tp_list_id).")) " : "")
                . (!is_null($active) ? "AND (`status`={$active}) " : "")
                . (!is_null($deleted) ? "AND (`deleted`={$deleted}) " : "")
                . (!is_null($is_managed) ? "AND (`is_managed`={$is_managed}) " : "")
                . (!is_null($rang_id) ? "AND (`rang_id`={$rang_id}) " : "")
                . "ORDER BY `".TP::TABLE."`.`".TP::F_TITLE."` ASC";
        return $this->get_id_vector($sql);
    }


    
    function is_my_tp(?int $tp_id, ?int $user_id = null) {
        $my_tp_id_list = $this->get_my_tp_id_list($user_id ?? App::get_user_id());
        return in_array($tp_id, $my_tp_id_list, true);
    }

    /**
     * Возвращает из базы массив со списком ТП разрешенных указанному или текущему авторизованному пользователю.
     * Если парамерт-фильтр установлен в null, то он не участвует в запросе и выбираются все значения.
     * @param int|null $user_id -- если не указан, то используется ID авторизованного пользователя
     * @param int|null $active -- 0 — Отключен/демонтирован, 1 — Работает
     * @param int|null $deleted -- ТП физически демонтирована, её больше нет.
     * @param int|null $is_managed -- Управляемая ТП, т.е. есть микротик и абоны почключены через таблицу АБОН
     * @param int|null $rang_id -- Ранг узла: 1 — Абонентский узел | 2 — AP | 3 — Агрегатор AP | 4 — Bridge AP | 5 — Bridge Client | 10 — Хостинговая тех. площадка | 100 — Биллинг
     * @param array|null $tp_list_id -- Список ID ТП для выборки/фильтрации
     * @throws \Exception
     * @return array
     */
    function get_my_tp_list(
            ?int $user_id = null,
            ?int $active = 1,
            ?int $deleted = null,
            ?int $is_managed = null,
            ?int $rang_id = null,
            ?array $tp_list_id = null
        ): array
    {
        $user_id = $user_id ?? App::get_user_id();

        if (!$this->validate_id(table_name: User::TABLE, id_value: $user_id, field_id: User::F_ID)) {
            throw new \Exception("ID[{$user_id}] No Valid");
        }
        $sql = "SELECT "
                . "".TP::TABLE.".* "
                . "FROM `".TSUserTp::TABLE."` "
                . "LEFT JOIN ".TP::TABLE." ON ".TSUserTp::TABLE.".".TSUserTp::F_TP_ID." = ".TP::TABLE.".".TP::F_ID." "
                . "WHERE "
                . "(`".TSUserTp::F_USER_ID."`={$user_id}) "
                . (!empty($tp_list_id) ? "AND (`".TSUserTp::F_TP_ID."` IN (".implode(',', $tp_list_id).")) " : "")
                . (!is_null($active) ? "AND (`status`={$active}) " : "")
                . (!is_null($deleted) ? "AND (`deleted`={$deleted}) " : "")
                . (!is_null($is_managed) ? "AND (`is_managed`={$is_managed}) " : "")
                . (!is_null($rang_id) ? "AND (`rang_id`={$rang_id}) " : "")
                . "ORDER BY `".TP::TABLE."`.`".TP::F_TITLE."` ASC";
        
        $tp_list = $this->get_rows_by_sql($sql); 
        foreach ($tp_list as $tp_one) {
            TP::untemplate($tp_one);
        }
        return $tp_list;
    }


    /**
     * Возвращает SQL-запрос для получения списка ТП.
     * Если указан $user_id, то, список ТП принадлежащих этому пользователю.
     * Если указан $id_list, то список ТП из этого списка.
     * Если не укзанны ни $user_id ни $id_list, то в качестве $user_id берётся ID текущего авторизованного пользователя.
     * @param int|null $user_id
     * @param array|null $id_list
     * @param int|null $status
     * @param int|null $deleted
     * @param int|null $managed
     * @param int|null $rang
     * @return string
     */
    function get_sql_tp_list(
            int|null    $user_id = null,    // Вернуть список для указанного пользователя из TSUserTp::TABLE
            array|null  $id_list = null,    // Массив со списком нужных ТП
            int|null    $status  = null,    // 1 — Работает, 0 — Отключен
            int|null    $deleted = null,    // 1 — ТП демонтирована, 0 — Можно вернуть в работу
            int|null    $managed = null,    // 1 — Управляемая (Mik)
            int|null    $rang    = null     // 1 — Абонентский узел. 2 — AP...
            ): string
    {
        if (empty($user_id) && empty($id_list)) {
            $user_id = App::get_user_id();
        }

        if (!is_null($id_list)) {
            return "SELECT "
                    . "* "
                    . "FROM `".TP::TABLE."` "
                    . "WHERE "
                    . "`".TP::F_ID."` IN (".implode(",", $id_list).") "
                    . (is_null($status)  ? "" : "AND (`".TP::F_ACTIVE."` = {$status}) ")
                    . (is_null($deleted) ? "" : "AND (`".TP::F_DELETED."` = {$deleted}) ")
                    . (is_null($managed) ? "" : "AND (`".TP::F_IS_MANAGED."` = {$managed}) ")
                    . (is_null($rang)    ? "" : "AND (`".TP::F_RANG_ID."` = {$rang}) ")
                    . "ORDER BY `status` DESC, `deleted` ASC, `title` ASC";
        }
        
        if (!is_null($user_id)) {
            return "SELECT "
                    . "* "
                    . "FROM `".TP::TABLE."` "
                    . "WHERE "
                    . "`".TP::F_ID."` IN (SELECT `".TSUserTp::F_TP_ID."` FROM `".TSUserTp::TABLE."` WHERE `".TSUserTp::F_USER_ID."` = {$user_id}) "
                    . (is_null($status)  ? "" : "AND (`".TP::F_ACTIVE."` = {$status}) ")
                    . (is_null($deleted) ? "" : "AND (`".TP::F_DELETED."` = {$deleted}) ")
                    . (is_null($managed) ? "" : "AND (`".TP::F_IS_MANAGED."` = {$managed}) ")
                    . (is_null($rang)    ? "" : "AND (`".TP::F_RANG_ID."` = {$rang}) ")
                    . "ORDER BY `".TP::F_ACTIVE."` DESC, `".TP::F_DELETED."` ASC, `".TP::F_TITLE."` ASC";
        }

        throw new \Exception("Не указан user_id, или не удалось его получить, и не указан id_list.");
    }


    

    /**
     * Проверяет, является ли указанный прайсовый фрагмент последним днём действия
     * @param array $pa_rec -- запись прайсового фрагмента
     * @param int   $today  -- дата "сегодня" в формате UNIX_TIMESTAMP. Если не указана, то берётся текущее время.
     * @return bool TRUE - если прайсовый фрагмент заканчивается сегодня
     */
    public function has_pa_last_day(array $pa_rec, int $today = NA): bool {

        $pa_rec['date_end'] = (($pa_rec['date_end'] > 0)
                                        ? date_only($pa_rec['date_end'])
                                        : 0);

        return $pa_rec['date_end'] == date_only($today);
    }





    /**
     * Возвращает сумму всех платежей для указанного абонента из массива полного списка платежей
     * @param int $aid -- ИД абонента
     * @return float -- возвращаемая сумма всех платежей
     */
    public function get_sum_pays_by_abon(array &$abon): float {
        $sum = 0.0;
        if (isset($abon['PAYMENTS'])) {
            foreach ($abon['PAYMENTS'] as $P) {
                $sum += $P['pay'];
            }
        }
        return $sum;
    }



    /** !!!
     * Возвращает массив для абонентов с границами прайсовых начислений:
     * $edges[abon_id]
     * $edges[abon_id]['COST_PA_SUM'] -- сумма стоимости всех прайсовых франгментов;
     * $edges[abon_id]['PPMA']        -- Активный прайс за месяц (Price per Month Active);
     * $edges[abon_id]['PPDA']        -- Активный прайс за сутки (Price per Day Active);
     * @param array $A         -- ссылка на запись абонента, в которй есть поле-массив с всеми прайсовыми фрагментами этого абонента.
     *                            В рапись этого абонента будут добавлены поля
     */
    function get_abons_edges_PA(array &$PA_list, int|null $tp_id = null) {
        $cost_sum = 0.0;
        $PPMA = 0.0;
        $PPDA = 0.0;
        $tp_list = array();
        foreach ($PA_list as &$PA) {
            $cost_sum += $PA['cost_value'];
            if (!is_null($tp_id) && $PA['net_router_id'] != $tp_id) { continue; }
            $PPMA += $PA['PPMA_value'];
            $PPDA += $PA['PPDA_value'];
        }
        $A['COST_PA_SUM'] = $cost_sum;
        $A['PPMA'] = $PPMA;
        $A['PPDA'] = $PPDA;
        return $A;
    }



    function get_abons(int $user_id): array {
        return $this->get_rows_by_field(Abon::TABLE, Abon::F_USER_ID, $user_id);
    }



    /**
     * Обновляет поле записи абонента, добавляя в него следющие поля:
     * float $A['COST_PA_SUM'] -- сумма стоимости всех прайсовых франгментов;
     * float $A['PPMA']        -- Активный прайс за месяц (Price per Month Active);
     * float $A['PPDA']        -- Активный прайс за сутки (Price per Day Active);
     * @param array $A         -- ссылка на запись абонента, в которй есть поле-массив с всеми прайсовыми фрагментами этого абонента.
     *                            В рапись этого абонента будут добавлены поля
     */
    function update_abon_sum_edges_PA(array &$A, int|null $tp_id = null) {
        $sum = 0.0;
        $PPMA = 0.0;
        $PPDA = 0.0;
        $A['TP'] = array();
        foreach ($A['PA'] as &$PA) {
            $sum += $PA['cost_value'];
            if (!is_null($tp_id) && $PA['net_router_id'] != $tp_id) { continue; }
            $PPMA += $PA['PPMA_value'];
            $PPDA += $PA['PPDA_value'];
        }
        $A['COST_PA_SUM'] = $sum;
        $A['PPMA'] = $PPMA;
        $A['PPDA'] = $PPDA;
    }



    // /**
    //  * Добавляет к записи Абонента массив со ссылками на ТП к которым этот абонент подключен.
    //  * Функция изменяет переданный массив, добавляя в него данные.
    //  * Обновляет поле записи абонента, добавляя в него следющие поля:
    //  * array $A['TP']        == массив массивов хтмл-ссылолк на форму редактирования ТР, на которых есть активные прикрепленные прайсы,
    //  *                          если все прайсовые фрагменты отключены, то сюда добавляются ТП
    //  *                          с послених отключенных прайсовых фрагментов
    //  * @param array $A       -- ссылка на запись абонента, в которй есть поле-массив с всеми прайсовыми фрагментами этого абонента.
    //  *                          В запись этого абонента будут добавлены поля
    //  * @param string $self_url -- http url указывающий на этот скрипт для формирование html ссылок
    //  */
    // function update_abon_list_TP(array &$A, string|null $self_url = null) /* void */ {
    //     if (is_null($self_url)) {
    //         $self_url = get_http_script(false);
    //     }

    //     $A['TP'] = array();
    //     foreach ($A['PA'] as &$PA) {
    //         if (get_price_apply_age($PA) <> PAStatus::PAUSE) {
    //             $tp_title = $this->get_tp($PA['net_router_id'])['title'];
    //             $A['TP'][$PA['net_router_id']] = [
    //                 $this->url_tp_mik(tp_id: $PA['net_router_id'], icon_width: 16, icon_height: 16, show_gray: true),
    //                 $this->url_tp_form(tp_id: $PA['net_router_id'], has_img: true),
    //                 "<a href=".$self_url.(str_contains($self_url, "?")?"&":"?").CMD_SHOW_TP."=".$PA['net_router_id']." title='Вывести только абонентов этой ТП: ".$tp_title."' target=_self>".$tp_title."</a>"
    //                 ];
    //         }
    //     }
    //     if (count($A['TP']) == 0) {
    //         $last = AbonModel::get_last_PA($A['id'], $A['PA']);
    //         foreach ($last['off'] as $PA) {
    //             $tp_title = $this->get_tp($PA['net_router_id'])['title'];
    //             $A['TP'][$PA['net_router_id']] = [
    //                 $this->url_tp_mik(tp_id: $PA['net_router_id'], icon_width: 16, icon_height: 16, show_gray: true),
    //                 $this->url_tp_form(tp_id: $PA['net_router_id'], has_img: true),
    //                 "<a href=".$self_url.(str_contains($self_url, "?")?"&":"?").CMD_SHOW_TP."=".$PA['net_router_id']." title='Вывести только абонентов этой ТП: ".$tp_title."' target=_self>".$tp_title."</a>"
    //                 ];
    //         }
    //     }
    // }



    function url_pay_form(int $id): string {
        // !!! требуется переписать
        $pay = $this->get_row_by_id(table_name: Pay::TABLE, id_value: $id, field_id: Pay::F_ID);
        return 
            "<a "
            . "title='PAY: ". h(print_r($pay, true))."' "
            . "href='".Pay::URI_FORM."/{$id}' "
            . "target=_blank "
            . ">"
                . "<img src='".Icons::SRC_ICON_UAH."' alt=PAY width=18 height=18>"
            . "</a>";
    }



    /**
     * Возвращает html-строку с кодом ссылки на страницу редактирования ТП
     * @param array|null $tp
     * @param int|null $tp_id
     * @param bool $has_img
     * @param int $icon_width
     * @param int $icon_height
     * @return string
     */
    function url_tp_form(int|null $tp_id = null, array|null $tp = null, bool|null $has_img = null, int $icon_width = ICON_WIDTH_DEF, int $icon_height = ICON_HEIGHT_DEF): string {
        if (is_null($tp) && !is_null($tp_id)) {
            if ($this->validate_id(TP::TABLE, $tp_id, TP::F_ID)) {
                $tp = $this->get_tp($tp_id);
            } else {
                return "";
            }
        }
        return a(   href:   TP::URI_EDIT."/{$tp[TP::F_ID]}",
                    target: TARGET_BLANK,
                    title:  __('Edit technical site parameters | Редактировать параметры технической площадки | Редагувати параметри технічного майданчика') . "[{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}",
                    text:   (is_null($has_img) || ($has_img === false) ? ($tp[TP::F_TITLE] ?? '') : null),
                    src:    (is_null($has_img) || ($has_img === true) ? Icons::SRC_TP_EDIT : null),
                    alt:    'EDIT',
                    width:  $icon_width,
                    height: $icon_height);
    }



    function url_ppp_form(string $ppp_id, bool $has_img = true, int $icon_width = ICON_WIDTH_DEF, int $icon_height = ICON_HEIGHT_DEF): string {
        $ppp = $this->get_ppp($ppp_id);
        return "<a href='".Ppp::URI_EDIT."/{$ppp_id}' title='Редактировать ППП [".$ppp_id."] ".$ppp[Ppp::F_TITLE]."' target=_blank>".($has_img?"<img src='".Icons::SRC_ICON_PPP."' alt='ППП' width=$icon_width height=$icon_height>":$ppp[Ppp::F_TITLE])."</a>";
    }



    function url_ppp_form_22(string $ppp_id): string {
        return $this->url_ppp_form($ppp_id, has_img: 0, icon_width: 22, icon_height: 22);
    }



    function url_price_form(int $price_id, bool $has_img = true, int $icon_width = 22, int $icon_height = 22, string $target = "_self"): string {
        $price = $this->get_price($price_id);
        return "<a href='/price_form.php?id={$price_id}' title='Редактировать прайс \n[".$price_id."] ".$price['title']."\n{$price['description']}' target={$target}>".($has_img?"<img src=/img/price_edit.png alt='[edit]' width=$icon_width height=$icon_height>":$price['title'])."</a>";
    }



    function url_device_type_form(int $id): string {
        $dev_type_row = $this->get_row_by_id('devices_types', $id);
        if (!is_null($dev_type_row)) {
            if (!is_null($dev_type_row['icon'])) {
                return get_html_img(
                        href: "https://my.ri.net.ua/edit_table.php?".GET_TABLE."=devices_types&".GET_ROW_ID."={$id}#EDIT",
                        src: $dev_type_row['icon'],
                        width: 64, height: 64,
                        alt: $dev_type_row['title'],
                        target: '_blank',
                        title: $dev_type_row['title']."\n".$dev_type_row['description']);
            } else {
                return "<a title='{$dev_type_row['description']}' href='https://my.ri.net.ua/edit_table.php?".GET_TABLE."=devices_types&".GET_ROW_ID."={$id}#EDIT' target=_blank >{$dev_type_row['title']}</a>";
            }
        } else {
            return "";
        }
    }



    /**
     * Возвращает строку с html-кодом ссылки на страницу редактирования микротика
     * Обязательно нужно указать $tp или $tp_id.
     * @param array|null $tp  -- Ассоциативный массив с данными ТП.
     * @param int|null $tp_id -- ID ТП для віборки из базы.
     * @param int $icon_width
     * @param int $icon_height
     * @param bool $show_gray
     * @return string
     */
    function url_tp_mik(array|null $tp = null, int|null $tp_id = null, int $icon_width = ICON_WIDTH_DEF, int $icon_height = ICON_HEIGHT_DEF, bool $show_gray = true): string {
        if (is_null($tp) && !is_null($tp_id)) {
            $tp = $this->get_tp($tp_id);
        }
        $html =
            ($tp[TP::F_IS_MANAGED]
                    ? a(
                            href: TP::URI_COMBINE.'/'.$tp[TP::F_ID],
                            target: TARGET_BLANK,
                            title: "TP [{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}". CR . __('Mikrotik control | Управление микротиком | Управління мікротиком'),
                            src: Icons::SRC_MIK_LOGO16,
                            alt:  '[MIK]',
                            width:  $icon_width,
                            height:  $icon_height)
                    : ($show_gray
                        ? get_html_img(
                            src: Icons::SRC_MIK_LOGO16_GRAY,
                            alt: '[MIK]',
                            title: "TP [{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}". CR . __('Not controlled by Mikrotik | Не управляемая микротиком | Не керована мікротиком'),
                            width: $icon_width,
                            height: $icon_height)
                        : "")
             );
        return $html;
    }



    function url_address_on_map_search(string $address, int $icon_width = 14, int $icon_height = 14): string {
        $icon = get_html_img(
                src: Icons::SRC_ICON_MAPS,
                href: 'https://www.google.com/maps/search/' . urlencode($address) . '/',
                alt: "[MAP]",
                target: TARGET_BLANK,
                title: 'Найти на карте',
                width: $icon_height,
                height: $icon_width);
        return $icon;
    }



    function url_address_on_map_place(string $address, int $icon_width = 14, int $icon_height = 14): string {
        $icon = get_html_img(
                src: Icons::SRC_ICON_MAPS,
                href: 'https://www.google.com.ua/maps/place/' . urlencode($address) . '/',
                alt: "[MAP]",
                target: TARGET_BLANK,
                title: 'Показать на Гугл-карте',
                width: $icon_height,
                height: $icon_width);
        return $icon;
    }



    function get_user_name(int $uid): string|null
    {
        if (is_null($uid) || $uid == 0) { return null; }
        return $this->get_user($uid)[User::F_NAME_FULL];
    }



    function get_user_name_short(int $uid): string|null
    {
        if (is_null($uid) || $uid == 0) { return null; }
        return $this->get_user($uid)[User::F_NAME_SHORT];
    }



    function get_module(int|null $id): array|null
    {
        if (is_null($id) || $id == 0) { return null; }
        return $this->get_row_by_id(table_name: Module::TABLE, field_id: Module::F_ID, id_value: $id);
    }



    function get_module_title(int|null $id): string
    {
        return $this->get_module(id: $id)[Module::F_TITLE[Lang::code()]] ?? '';
    }



    function get_ppp(int $ppp_id): array {
        return $this->get_row_by_id(table_name: Ppp::TABLE, field_id: Ppp::F_ID, id_value: $ppp_id);
    }



    function get_ppp_title(int $ppp_id): string {
        if (empty($ppp_id)) { return ''; }
        return $this->get_row_by_id(table_name: Ppp::TABLE, field_id: Ppp::F_ID, id_value: $ppp_id)[Ppp::F_TITLE];
    }


    function get_ppp_type_title(int $ppp_id): string {
        $ppp = $this->get_row_by_id(table_name: Ppp::TABLE, field_id: Ppp::F_ID, id_value: $ppp_id);
        $ppp_type = $this->get_row_by_id(table_name: PppType::TABLE, field_id: PppType::F_ID, id_value: $ppp[Ppp::F_TYPE_ID]);
        return $ppp_type[PppType::F_TITLE[Lang::F_CODE]];
    }

    
    
    function get_pa(int $pa_id):array {
        self::$errors = [];
        if ($this->validate_id(PA::TABLE, $pa_id, PA::F_ID)) {
            return $this->get_row_by_id(PA::TABLE, $pa_id, PA::F_ID);
        } else {
            self::$errors[] = 'get_pa: ' . __('Invalid price fragment ID | Не верный ID прайсового фрагмента | Не вірний ID прайсового фрагмента') . ' [' . $pa_id . ']';
            return [];
        }
    }



    function get_srvice_type_by_pa(array $pa): ServiceType
    {
        if ($pa[PA::F_NET_IP_SERVICE]) {
            return ServiceType::INTERNET;
        } else {
            return ServiceType::OTHER;
        }
    }

    
    
    /**
     * Возвращает список активных прайсовых фрагментов на указанной ТП
     * @param int $tp_id -- ID ТП
     * @param PAStatus $PA_AGE
     * @return array массив прайсовых фрагментов
     */
    function get_prices_apply_by_tp(int $tp_id, PAStatus $PA_AGE = PAStatus::ACTIVE_TODAY): array {
        $pa_list_raw = $this-> get_rows_by_field(
                            table: PA::TABLE,
                            field_name: PA::F_TP_ID,
                            field_value: $tp_id,
                            order_by: PA::F_ABON_ID . " ASC");
        if (is_null($PA_AGE)) {
            return $pa_list_raw;
        } else {
            $pa_list = array();
            foreach ($pa_list_raw as $pa_one) {
                if ($PA_AGE->value & get_price_apply_age($pa_one)->value) {
                    $pa_list[] = $pa_one;
                }
            }
            return $pa_list;
        }
    }



    /**
     * Таблица кэширования прайсовых фрагментов для абонентов
     */
    protected static $CACHE_PA_BY_ABON = array();

    /**
     * Возвращает из кэша self::CACHE_PA_BY_ABON[$abon_id] все прикрепленные прайсовые фрагменты
     * Если их там нет, то вносит их туда из базы и возвращает.
     * @global array self::CACHE_PA_BY_ABON -- Кэш-таблица
     * @param int $abon_id -- ID абоненета
     * @return array -- список прикрепленных прайсовых фрагментов
     */
    function get_prices_apply_by_abon($abon_id): array {

        if (!array_key_exists($abon_id, self::$CACHE_PA_BY_ABON)) {
            //echo "CACHE_PA_BY_ABON - reading...<br>";
            $SQL = "SELECT
                prices_apply.*,
                prices_apply.id                                                    AS prices_apply_id,
                DATE_FORMAT(from_unixtime(prices_apply.date_start),'%Y-%m-%d')     AS date_start_str,
                DATE_FORMAT(from_unixtime(prices_apply.date_end),  '%Y-%m-%d')     AS date_end_str,
                DATE_FORMAT(from_unixtime(prices_apply.cost_date), '%Y-%m-%d')     AS cost_date_str,
                DATE_FORMAT(from_unixtime(prices_apply.modified_date), '%Y-%m-%d') AS modified_date_str,
                prices.title                                                       AS ".PA::F_PRICE_TITLE.",
                prices.pay_per_day                                                 AS ".PA::F_PRICE_PPD.",
                prices.pay_per_month                                               AS ".PA::F_PRICE_PPM.",
                prices.description                                                 AS ".PA::F_PRICE_DESCR.",
                tp_list.title                                                      AS ".PA::FF_TP_TITLE.",
                tp_list.status                                                     AS ".PA::FF_TP_STATUS.",
                tp_list.deleted                                                    AS ".PA::FF_TP_DELETED.",
                tp_list.is_managed                                                 AS ".PA::FF_TP_IS_MANAGED."
                FROM prices_apply
                    LEFT JOIN billing.prices  ON prices_apply.prices_id     = prices.id
                    LEFT JOIN billing.tp_list ON prices_apply.net_router_id = tp_list.id
                WHERE abon_id =".$abon_id."
                ORDER BY prices_apply.date_start ASC";
            $prices = $this->get_rows_by_sql($SQL);
            self::$CACHE_PA_BY_ABON[$abon_id] = $prices;
        }
        return self::$CACHE_PA_BY_ABON[$abon_id];
    }


    
    
}
