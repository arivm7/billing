<?php
/*
 *  Project : my.ri.net.ua
 *  File    : abon_card.php
 *  Path    : app/views/inc/abon_card.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Карточка абонента с вкладками
 * Включает в себя: 
 * - Карточку абонента (abon_view.php)
 * - Таб прайсовых фрагментов (pa_view.php)
 * - Таб уведомлений (notify_view.php)
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use billing\core\App;
use config\tables\Abon;
use config\tables\PA;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/** @var array $item -- Поддержка функции Аккордеона в ней передаваемый элемент */
/** @var array $abon */

if (isset($item) && !isset($abon)) { $abon = $item; }

?>
<div class="container-fluid">
    <ul class="nav nav-tabs justify-content-end" id="my_tab_abon_data<?=$abon[Abon::F_ID]?>" role="tablist">
        <!-- Таб абонентского подключения -->
        <li class="nav-item" role="presentation">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab_abon_<?=$abon[Abon::F_ID]?>" role="tab"><small><?=__('Abonent connection');?></small></a>
        </li>
        <!-- Таб прайсовых фрагментов -->
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" href="#tab_pa_<?=$abon[Abon::F_ID]?>" role="tab"><small><?=__('Price charges');?></small></a>
        </li>
        <!-- Таб уведомлений -->
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" href="#tab_notify_<?=$abon[Abon::F_ID]?>" role="tab"><small><?=__('Notifications');?></small></a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- [ Контент вкладки абонентского подключения ] -->
        <div class="tab-pane fade show active" id="tab_abon_<?=$abon[Abon::F_ID]?>" role="tabpanel">
            <?php require DIR_INC . '/abon_view.php'; ?>
        </div>
        <!-- [ Контент вкладки прайсовых фрагментов ] -->
        <div class="tab-pane fade" id="tab_pa_<?=$abon[Abon::F_ID]?>" role="tabpanel">
            <!-- Перебор подключенных прайсовых фрагментов -->
            <div class="container-fluid mt-4">
                <!-- 
                <div class="text-end">
                    <form>
                        <div class="form-check form-check-inline" action="/config/pa">
                            <input class="form-check-input" type="checkbox" name="pa_filter_active" id="pa_filter_active" <?=(App::get_config('pa_show_filter')['active'] ? "checked" : "");?> value="1">
                            <label class="form-check-label" for="pa_filter_active">Active</label>
                        </div>                        
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="pa_filter_paused" id="pa_filter_paused" <?=(App::get_config('pa_show_filter')['paused'] ? "checked" : "");?> value="1">
                            <label class="form-check-label" for="pa_filter_paused">Paused</label>
                        </div>                        
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="pa_filter_closed" id="pa_filter_closed" <?=(App::get_config('pa_show_filter')['closed'] ? "checked" : "");?> value="1">
                            <label class="form-check-label" for="pa_filter_closed">Closed</label>
                        </div>                        
                        <button type="submit" class="btn btn-sm btn-outline-info">[>]</button>
                    </form>
                </div> 
                -->
                <?php
                    if ($abon[PA::TABLE]) {
                        echo get_html_accordion(
                                table: $abon[PA::TABLE],
                                file_view: DIR_INC . '/pa_view.php',
                                func_get_title: function(array $pa) {
                                        $left = "<span class='text font-monospace text-secondary small'>"
                                                    . ($pa[PA::F_DATE_START] ? date(DATE_FORMAT, $pa[PA::F_DATE_START]) : '____-__-__') . ' | '
                                                    . ($pa[PA::F_DATE_END] ? date(DATE_FORMAT, $pa[PA::F_DATE_END]) : '____-__-__') . ' | '
                                                . "</span>"
                                                . $pa[PA::F_NET_NAME];
                                        $right = get_html_pa_status(__pa_age($pa)) . "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                                        $title = get_html_content_left_right(
                                                    left:  $left,
                                                    right: $right,
                                                    add_class: 'w-100');
                                        return $title;
                                }
                        );
                    } else {
                        echo "<br><div class='alert alert-info' role='alert'>".__('No active prices')."</div>";
                    }
                ?>
            </div>
        </div>
        <!-- [ Контент вкладки уведомлений ] -->
        <div class="tab-pane fade" id="tab_notify_<?=$abon[Abon::F_ID]?>" role="tabpanel">
            <div class="container-fluid mt-4">
            <?php require DIR_INC . '/notify_view.php'; ?>
            </div>
        </div>

    </div>
</div>
<?php
unset($abon);
unset($item);
?>