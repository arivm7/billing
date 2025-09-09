<?php

namespace app\models;

use billing\core\base\Lang;
use billing\core\base\Model;
use config\Icons;
use config\tables\Module;

use config\tables\TP;
use config\tables\User;

require_once DIR_LIBS . '/datetime_functions.php';

class AppBaseModel extends Model
{


    /**
     * Кэш-таблица для function get_price(int $id)
     */
    private static array $CASHE_PRICE_LIST = array();

    /**
     * Возвращает запись прайса их кэша $CASHE_PRICE_LIST.
     * Если в кэше записи нет, то записывает туда, а за тем возвращает.
     * @global type $CASHE_PRICE_LIST
     * @param int $id
     * @return array
     */
    function get_price(int $id): array {
        if (!array_key_exists($id, self::$CASHE_PRICE_LIST)) {
            self::$CASHE_PRICE_LIST[$id] = $this->get_row_by_id("prices", $id);
        }
        return self::$CASHE_PRICE_LIST[$id];
    }



    /**
     * Кэш-таблица для function get_tp(int $id)
     */
    private static array $CASHE_TP_LIST = [];



    function get_tp(int $id) {
        //echo "get_tp(int $id)<br>";
        if (!array_key_exists($id, self::$CASHE_TP_LIST)) {
            self::$CASHE_TP_LIST[$id] = $this->get_row_by_id("tp_list", $id);
            self::$CASHE_TP_LIST[$id]["rang_title"]   = (self::$CASHE_TP_LIST[$id]["rang_id"]   > 0 ? $this->get_row_by_id("tp_rangs", self::$CASHE_TP_LIST[$id]["rang_id"])["title"] : "");
            self::$CASHE_TP_LIST[$id]["uplink_title"] = (self::$CASHE_TP_LIST[$id]["uplink_id"] > 0 ? $this->get_tp(self::$CASHE_TP_LIST[$id]["uplink_id"])["title"] : "");
        }
        return self::$CASHE_TP_LIST[$id];
    }




    /**
     * Возвращает список ТП привязанных к Пользователю в таблице связи ts_user_tp
     * ТП кэшируются
     * @param int $uid -- user_id из таблицы связи ts_user_tp
     * @param array|null $list_tp_id
     * @param bool|null $status -- true | false | NULL - все
     * @param bool|null $is_managed -- true | false | NULL - все
     * @param bool|null $deleted -- true | false | NULL - все
     * @return array -- список ТП
     * @throws \Exception
     */
    function get_tps_by_uid(int $uid, array|null $list_tp_id = null, bool|null $status = null, bool|null $is_managed = null, bool|null $deleted = null): array {
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


    /**
     * Для указанного абонента возвращает последние закрытый, все текущие и все будущие прайсы.
     * @param int $aid -- ИД абонента для которого ищутся правйсовые фрагменты
     * @param array $PA_LIST -- массив прайсовых фрагментов из которого делается выборка
     * @return array -- возвращает массив:
     *                  $last['off'] = array(prices_apply) -- последние закрытые
     *                  $last['cur'] = array(prices_apply) -- все текущие
     *                  $last['fut'] = array(prices_apply) -- все будущие
     */
    function get_last_PA(int $aid, array &$PA_LIST): array {
        $last['off'] = array();
        $last['cur'] = array();
        $last['fut'] = array();
        $last['off_time'] = -1;
        $last['cur_time'] = -1;
        $last['fut_time'] = -1;
        /**
         * Ищем даты последних включенных и отключенных прайсов
         */
        foreach ($PA_LIST as $pid => $pa) {
            if ($pa['abon_id'] == $aid) {
                switch ($this->get_prices_apply_age($pa)) {
                    case PRICES_APPLY_CLOSED:
                        if ($pa['date_end'] > $last['off_time']) {
                            $last['off_time'] = $pa['date_end'];
                        }
                        break;
                    case PRICES_APPLY_CURRENT:
                        if ($pa['date_start'] > $last['cur_time']) {
                            $last['cur_time'] = $pa['date_start'];
                        }
                        break;
                    case PRICES_APPLY_FUTURE:
                        if ($pa['date_start'] > $last['fut_time']) {
                            $last['fut_time'] = $pa['date_start'];
                        }
                        break;
                }
            }
        }
        /**
         * Считываем все прайсовые фрагенты по найденным датам
         */
        foreach ($PA_LIST as $pid => $pa) {
            if ($pa['abon_id'] == $aid) {
                switch ($this->get_prices_apply_age($pa)) {
                    case PRICES_APPLY_CLOSED:
                        if ($pa['date_end'] == $last['off_time']) { $last['off'][] = $pa; }
                        break;
                    case PRICES_APPLY_CURRENT:
                        $last['cur'][] = $pa;
                        break;
                    case PRICES_APPLY_FUTURE:
                        $last['fut'][] = $pa;
                        break;
                }
            }
        }
        return $last;
    }






    function has_pa_last_day(array $pa_rec, int $today = NA): bool {

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
    function get_sum_pays_by_abon(array &$abon): float {
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
        $sum = 0.0;
        $PPMA = 0.0;
        $PPDA = 0.0;
        $tp_list = array();
        foreach ($PA_list as &$PA) {
            $sum += $PA['cost_value'];
            if (!is_null($tp_id) && $PA['net_router_id'] != $tp_id) { continue; }
            $PPMA += $PA['PPMA_value'];
            $PPDA += $PA['PPDA_value'];
        }
        $A['COST_PA_SUM'] = $sum;
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
    function update_abon_list_TP(array &$A, string $self_url = null) /* void */ {
        if (is_null($self_url)) {
            $self_url = get_http_script(false);
        }

        $A['TP'] = array();
        foreach ($A['PA'] as &$PA) {
            if ($this->get_prices_apply_age($PA) <> PRICES_APPLY_CLOSED) {
                $tp_title = $this->get_tp($PA['net_router_id'])['title'];
                $A['TP'][$PA['net_router_id']] = [
                    $this->url_tp_mik(tp_id: $PA['net_router_id'], icon_width: 16, icon_height: 16, show_gray: true),
                    $this->url_tp_form(tp_id: $PA['net_router_id'], has_img: true),
                    "<a href=".$self_url.(str_contains($self_url, "?")?"&":"?").CMD_SHOW_TP."=".$PA['net_router_id']." title='Вывести только абонентов этой ТП: ".$tp_title."' target=_self>".$tp_title."</a>"
                    ];
            }
        }
        if (count($A['TP']) == 0) {
            $last = $this->get_last_PA($A['id'], $A['PA']);
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






    /**
     * Возвращает html строку '[$]' флажка, показывающую является ли абонент или пользователь плательщиком
     * @param int|null $aid
     * @param int|null $uid
     */
    function get_html_chek_payer(int|null $aid = null, int|null $uid = null) {
        $payer = false;
        if (!is_null($aid)) {
            $abon = $this->get_abon($aid);
            $payer = $abon['is_payer'];
        } elseif (!is_null($uid)) {
            $A = $this->get_rows_by_field('abons', field_name: 'user_id', field_value: $uid);
            foreach ($A as $abon) {
                if ($abon['is_payer']) {
                    $payer = true;
                    break;
                }
            }
        }
        $check0 = "<font size=-1 face=monospace color=gray>[<font color=". GRAY.">$</font>]</font>";
        $check1 = "<font size=-1 face=monospace color=gray>[<font color=".GREEN.">$</font>]</font>";

        return get_html_CHECK(has_check: $payer, title_on: 'Есть подключения в статусе "Плательщик"', title_off: 'Не "Плательщик" ', check0: $check0, check1: $check1);
    }



    /**
     * Возвращает текстовую строку-ссылку на страницу пользователя
     * @param int $user_id
     * @return string -- Строка с html-кодом
     */
    function url_user_form(int $user_id): string {
        $c = $this->get_html_chek_payer(uid: $user_id);
        return "<a href=/abon/form?user_id=$user_id target=_blank title='". $this->get_user_name($user_id)."' >$user_id</a>&nbsp;{$c}";
    }



    /**
     * Возвращает текстовую строку-ссылку на страницу абонента (пользователя
     * @param int $abon_id
     * @return string -- Строка с html-кодом
     */
    function url_abon_form(int $abon_id): string {
        if (is_null($abon_id) || $abon_id == 0 || !$this->validate_id("abons", $abon_id)) { return $abon_id; }
        $c = $this->get_html_chek_payer(aid: $abon_id);
        return a(href: "/abon/form?abon_id={$abon_id}", text: "{$abon_id}", title: $this->get_abon_address($abon_id), target: "_blank") . "&nbsp;{$c}";
     // return "<a href=/abon/form?abon_id={$abon_id} title='". $this->get_abon_address($abon_id)."' target=_blank >{$abon_id}</a>&nbsp;{$c}";
    }



    function url_pay_form(int $id): string {
        $pay = $this->get_pay_by_id($id);
        return "<a title='PAY: ". htmlentities(print_r($pay, true))."' href='/ad_abon1_pay.php?edit_pay={$id}' target=_blank ><img src='/img/icon_uah.svg' alt=CALL width=16 height=16 style='".ICON_STYLE."' ></a>";
    }



    /**
     * Возвращает html-строку с кодом ссылки на страницу редактирования ТП
     * @param string $tp_id
     * @return string
     */
    function url_tp_form(array|null $tp = null, int|null $tp_id = null, bool $has_img = false, int $icon_width = ICON_WIDTH_DEF, int $icon_height = ICON_HEIGHT_DEF): string {
        if (is_null($tp) && !is_null($tp_id)) {
            if ($this->validate_id(TP::TABLE, $tp_id, TP::F_ID)) {
                $tp = $this->get_tp($tp_id);
            } else {
                return "";
            }
        }
        return a(   href:   "/tp_form.php?tp_id={$tp[TP::F_ID]}",
                    target: TARGET_BLANK,
                    title:  __('Редактировать ТП') . "[{$tp[TP::F_ID]}] {$tp[TP::F_TITLE]}",
                    src:    Icons::SRC_TP_EDIT,
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




}
