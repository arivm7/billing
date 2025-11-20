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
     * Кэш-таблица для function get_price(int $id)
     */
    private static array $CASHE_PRICE_LIST = array();

    /**
     * Возвращает запись прайса их кэша $CASHE_PRICE_LIST.
     * Если в кэше записи нет, то записывает туда, а за тем возвращает.
     * @global array $CASHE_PRICE_LIST
     * @param int $id
     * @return array
     */
    function get_price(int $id): array {
        if (!array_key_exists($id, self::$CASHE_PRICE_LIST)) {
            self::$CASHE_PRICE_LIST[$id] = $this->get_row_by_id("prices", $id);
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


    /**
     * Кэш-таблица для function get_tp(int $id)
     */
    private static array $CASHE_TP_LIST = [];



    function get_tp(int $id): array {
        if (!array_key_exists($id, self::$CASHE_TP_LIST)) {
            self::$CASHE_TP_LIST[$id] = $this->get_row_by_id(TP::TABLE, $id, TP::F_ID);
            $this->normalize_tp(self::$CASHE_TP_LIST[$id]);
        }
        return self::$CASHE_TP_LIST[$id];
    }



    function get_id_vector(string $sql) {
        return $this->query(sql: $sql, fetchVector: 0);
    }



    function get_my_tp_id_list() {
        $user_id = $_SESSION[User::SESSION_USER_REC][User::F_ID];
        $sql = "SELECT `".TSUserTp::F_TP_ID."` FROM `".TSUserTp::TABLE."` WHERE `".TSUserTp::F_USER_ID."` = {$user_id}";
        return $this->get_id_vector($sql);
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
            int|null    $user_id = null,    // Вернуть список для указанного пользователя
            array|null  $id_list = null,    // Массив со списком нужных ТП
            int|null    $status  = null,    // 1 — Работает, 0 — Отключен
            int|null    $deleted = null,    // 1 — ТП демонтирована, 0 — Можно вернуть в работу
            int|null    $managed = null,    // 1 — Управляемая (Mik)
            int|null    $rang    = null     // 1 — Абонентский узел. 2 — AP...
            ): string
    {
        if (empty($user_id) && empty($id_list)) {
            $user_id = $_SESSION[User::SESSION_USER_REC][User::F_ID];
        }

        if (!empty ($user_id)) {
            return "SELECT "
                    . "* "
                    . "FROM `".TP::TABLE."` "
                    . "WHERE "
                    . "`".TP::F_ID."` IN (SELECT `".TSUserTp::F_TP_ID."` FROM `".TSUserTp::TABLE."` WHERE `".TSUserTp::F_USER_ID."` = {$user_id}) "
                    . (is_null($status)  ? "" : "AND (`".TP::F_STATUS."` = {$status}) ")
                    . (is_null($deleted) ? "" : "AND (`".TP::F_DELETED."` = {$deleted}) ")
                    . (is_null($managed) ? "" : "AND (`".TP::F_IS_MANAGED."` = {$managed}) ")
                    . (is_null($rang)    ? "" : "AND (`".TP::F_RANG_ID."` = {$rang}) ")
                    . "ORDER BY `".TP::F_STATUS."` DESC, `".TP::F_DELETED."` ASC, `".TP::F_TITLE."` ASC";
        }

        if (!empty($id_list)) {
            return "SELECT "
                    . "* "
                    . "FROM `".TP::TABLE."` "
                    . "WHERE "
                    . "`".TP::F_ID."` IN (".implode(",", $id_list).") "
                    . (is_null($status)  ? "" : "AND (`".TP::F_STATUS."` = {$status}) ")
                    . (is_null($deleted) ? "" : "AND (`".TP::F_DELETED."` = {$deleted}) ")
                    . (is_null($managed) ? "" : "AND (`".TP::F_IS_MANAGED."` = {$managed}) ")
                    . (is_null($rang)    ? "" : "AND (`".TP::F_RANG_ID."` = {$rang}) ")
                    . "ORDER BY `status` DESC, `deleted` ASC, `title` ASC";
        }
        throw new \Exception("Не указан user_id, или не удалось его получить, и не указан id_list.");
    }


    function normalize_tp(array &$tp): void {
        $tp[TP::F_MIK_PORT]     = (int)$tp[TP::F_MIK_PORT];     // просто переделыание в int. После исправления базы убрать !!!
        $tp[TP::F_MIK_PORT_SSL] = (int)$tp[TP::F_MIK_PORT_SSL]; // просто переделыание в int. После исправления базы убрать !!!
        $tp[TP::F_MIK_FTP_PORT] = (int)$tp[TP::F_MIK_FTP_PORT]; // просто переделыание в int. После исправления базы убрать !!!
        $tp[TP::F_RANG_TITLE]   = ($tp[TP::F_RANG_ID]   > 0 ? $this->get_row_by_id("tp_rangs", $tp["rang_id"])["title"] : "");

        $template = [];
        if (!empty($tp[TP::F_IP]) && validate_ip($tp[TP::F_IP])) {
            $template[TP::TEMPLATE_IP] = $tp[TP::F_IP];
        }

        foreach (TP::TEMPLATES_FIELDS as $field => $value) {
            if (!empty($tp[$field])) { // array_key_exists($field, $tp)
                $tp[$value] = untemplate($tp[$field], $template);
            } else {
                $tp[$value] = '';
            }
        }
        if (
                !empty($tp[TP::F_UPLINK_ID]) && 
                $this->validate_id(TP::TABLE, $tp[TP::F_UPLINK_ID], TP::F_ID)
            ) 
        {
            $tp[TP::F_UPLINK] = $this->get_tp($tp[TP::F_UPLINK_ID]);
            $tp[TP::F_UPLINK_TITLE] = $tp[TP::F_UPLINK][TP::F_TITLE];
        } else {
            $tp[TP::F_UPLINK_ID] = null;
        }
    }



    /**
     * Возвращает спискок ТП.
     * Все параметры передаются для формированя SQL с помощью get_sql_tp_list().
     * @param int|null $user_id
     * @param array|null $id_list
     * @param int|null $status
     * @param int|null $deleted
     * @param int|null $managed
     * @param int|null $rang
     * @return array
     */
    function get_tp_list(
            int|null    $user_id = null,    // Вернуть список для указанного пользователя
            array|null  $id_list = null,    // Массив со списком нужных ТП
            int|null    $status  = null,    // 1 — Работает, 0 — Отключен
            int|null    $deleted = null,    // 1 — ТП демонтирована, 0 — Можно вернуть в работу
            int|null    $managed = null,    // 1 — Управляемая (Mik)
            int|null    $rang    = null     // 1 — Абонентский узел. 2 — AP...
            ): array
    {
        $list = $this->get_rows_by_sql($this->get_sql_tp_list($user_id, $id_list, $status, $deleted, $managed, $rang));
        foreach ($list as &$tp) {
            $this->normalize_tp($tp);
        }
        return $list;
    }




    /**
     * Возвращает список ТП привязанных к Пользователю в таблице связи ts_user_tp
     * ТП кэшируются
     * @param int $uid -- user_id из таблицы связи ts_user_tp
     * @param array|null $list_tp_id
     * @param bool|null $status     -- true | false | NULL - все
     * @param bool|null $is_managed -- true | false | NULL - все
     * @param bool|null $deleted    -- true | false | NULL - все
     * @return array -- список ТП
     * @throws \Exception
     */
    function get_tp_list_by_uid(int $uid, array|null $list_tp_id = null, bool|null $status = null, bool|null $is_managed = null, bool|null $deleted = null): array {
        if (is_null($list_tp_id)) {
            $ts_list = $this->get_rows_by_field(table: 'ts_user_tp', field_name: 'user_id', field_value: $uid);
        } else {
            foreach ($list_tp_id as $tp_id) {
                if (!$this->validate_id("tp_list", $tp_id)) {
                    throw new \Exception("Передано не верное значение tp_id:<br>function get_tps_by_uid(int $uid, array $list_tp_id=null,  bool $status=null, bool $is_managed=null, bool $deleted=null)");
                }
            }
            $s = implode(",", $list_tp_id);
            // SELECT * FROM `ts_user_tp` WHERE `user_id`=1 AND (`tp_id` IN (1,2,3,4,5,6,7));
            $ts_list = $this->get_rows_by_where('ts_user_tp', "user_id={$uid} AND (tp_id IN ({$s}))");
        }
        $tp_list = array();
        foreach ($ts_list as $ts_one) {
            //echo "ts_one: <pre>"; print_r($ts_one); echo "</pre>";
            if ($ts_one['tp_id'] > 0) {
                $tp_one = $this->get_tp($ts_one['tp_id']);
                $add = true;
                if ($add and (!is_null($status))) {
                    $add = ($tp_one['status'] == $status);
                }
                if ($add and (!is_null($is_managed))) {
                    $add = ($tp_one['is_managed'] == $is_managed);
                }
                if ($add and (!is_null($deleted))) {
                    $add = ($tp_one['deleted'] == $deleted);
                }
                if ($add) {
                    $tp_list[] = $tp_one;
                }
            }
        }

        usort($tp_list, 'compare_title');

        return $tp_list;
    }



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



    /**
     * Добавляет к записи Абонента массив со ссылками на ТП к которым этот абонент подключен.
     * Функция изменяет переданный массив, добавляя в него данные.
     * Обновляет поле записи абонента, добавляя в него следющие поля:
     * array $A['TP']        == массив массивов хтмл-ссылолк на форму редактирования ТР, на которых есть активные прикрепленные прайсы,
     *                          если все прайсовые фрагменты отключены, то сюда добавляются ТП
     *                          с послених отключенных прайсовых фрагментов
     * @param array $A       -- ссылка на запись абонента, в которй есть поле-массив с всеми прайсовыми фрагментами этого абонента.
     *                          В запись этого абонента будут добавлены поля
     * @param string $self_url -- http url указывающий на этот скрипт для формирование html ссылок
     */
    function update_abon_list_TP(array &$A, string|null $self_url = null) /* void */ {
        if (is_null($self_url)) {
            $self_url = get_http_script(false);
        }

        $A['TP'] = array();
        foreach ($A['PA'] as &$PA) {
            if (get_price_apply_age($PA) <> PAStatus::PAUSE) {
                $tp_title = $this->get_tp($PA['net_router_id'])['title'];
                $A['TP'][$PA['net_router_id']] = [
                    $this->url_tp_mik(tp_id: $PA['net_router_id'], icon_width: 16, icon_height: 16, show_gray: true),
                    $this->url_tp_form(tp_id: $PA['net_router_id'], has_img: true),
                    "<a href=".$self_url.(str_contains($self_url, "?")?"&":"?").CMD_SHOW_TP."=".$PA['net_router_id']." title='Вывести только абонентов этой ТП: ".$tp_title."' target=_self>".$tp_title."</a>"
                    ];
            }
        }
        if (count($A['TP']) == 0) {
            $last = AbonModel::get_last_PA($A['id'], $A['PA']);
            foreach ($last['off'] as $PA) {
                $tp_title = $this->get_tp($PA['net_router_id'])['title'];
                $A['TP'][$PA['net_router_id']] = [
                    $this->url_tp_mik(tp_id: $PA['net_router_id'], icon_width: 16, icon_height: 16, show_gray: true),
                    $this->url_tp_form(tp_id: $PA['net_router_id'], has_img: true),
                    "<a href=".$self_url.(str_contains($self_url, "?")?"&":"?").CMD_SHOW_TP."=".$PA['net_router_id']." title='Вывести только абонентов этой ТП: ".$tp_title."' target=_self>".$tp_title."</a>"
                    ];
            }
        }
    }



    function url_pay_form(int $id): string {
        // !!! требуется переписать
        $pay = $this->get_row_by_id(table_name: Pay::TABLE, id_value: $id, field_id: Pay::F_ID);
        return "<a title='PAY: ". htmlentities(print_r($pay, true))."' href='/ad_abon1_pay.php?edit_pay={$id}' target=_blank ><img src='/img/icon_uah.svg' alt=CALL width=16 height=16></a>";
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
                    title:  __('Редактировать ТП') . "[{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}",
                    text:   (is_null($has_img) || ($has_img === false) ? ($tp[TP::F_TITLE] ?? '') : null),
                    src:    (is_null($has_img) || ($has_img === true) ? Icons::SRC_TP_EDIT : null),
                    alt:    'EDIT',
                    width:  $icon_width,
                    height: $icon_height);
    }



    function url_ppp_form(string $ppp_id, bool $has_img = true, int $icon_width = ICON_WIDTH_DEF, int $icon_height = ICON_HEIGHT_DEF): string {
        $ppp = $this->get_row_by_id('ppp_list', $ppp_id);
        return "<a href='/ppp_edit.php?ppp_id=$ppp_id' title='Редактировать ППП [".$ppp_id."] ".$ppp['title']."' target=_blank>".($has_img?"<img src='/img/ppp_icon_064.png' alt='PPP' width=$icon_width height=$icon_height>":$ppp['title'])."</a>";
    }



    function url_ppp_form_22(string $ppp_id): string {
        return $this->url_ppp_form($ppp_id, has_img: 0, icon_width: 22, icon_height: 22);
    }



    function price_frm(int $price_id, bool $has_img = true, int $icon_width = 22, int $icon_height = 22, string $target = "_self"): string {
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
                            href: "/tp_status_mik.php?tp_id={$tp[TP::F_ID]}",
                            target: TARGET_BLANK,
                            title: "TP [{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}". CR . __('Управление микротиком'),
                            src: Icons::SRC_MIK_LOGO16,
                            alt:  '[MIK]',
                            width:  $icon_width,
                            height:  $icon_height)
                    : ($show_gray
                        ? get_html_img(
                            src: Icons::SRC_MIK_LOGO16_GRAY,
                            alt: '[MIK]',
                            title: "TP [{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}". CR . __('Не управляемая микротиком'),
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

}