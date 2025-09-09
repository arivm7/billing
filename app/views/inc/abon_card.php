<!--abon_card.php-->
<?php
use app\models\PAStatus;
use config\tables\Abon;
use config\tables\PA;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);

/**
 * @var array $abon — массив с данными абонента
 *  Ключи соответствуют названиям колонок таблицы `abons`
 */

/**
 * Поддержка функции Аккордеона
 * в ней передаваемый элемент $item
 */

/** @var array $item */
/** @var array $abon */
if (isset($item) && !isset($abon)) { $abon = $item; }

?>
<div class="container-fluid">
    <ul class="nav nav-tabs justify-content-end" id="my_tab_abon_data<?=$abon[Abon::F_ID]?>" role="tablist">
      <li class="nav-item" role="presentation">
          <a class="nav-link active" id="tab1-tab" data-bs-toggle="tab" href="#tab_abon_<?=$abon[Abon::F_ID]?>" role="tab"><small><?=__('Abonent connections');?></small></a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="tab2-tab" data-bs-toggle="tab" href="#tab_pa_<?=$abon[Abon::F_ID]?>" role="tab"><small><?=__('Price charges');?></small></a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="tab2-tab" data-bs-toggle="tab" href="#tab_notify_<?=$abon[Abon::F_ID]?>" role="tab"><small><?=__('Notifications');?></small></a>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="tab_abon_<?=$abon[Abon::F_ID]?>" role="tabpanel">
            <?php require DIR_INC . '/abon_view.php'; ?>
        </div>
        <div class="tab-pane fade" id="tab_pa_<?=$abon[Abon::F_ID]?>" role="tabpanel">
            <!-- Перебор подключенных прайсовых фрагментов -->
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
                                    $right = get_html_pa_status_badge(__pa_age($pa)) . "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                                    $title = get_html_content_left_right(
                                                left:  $left,
                                                right: $right,
                                                add_class: 'w-100');
                                    return $title;
                            }
                    );
                    /* $firm = $user[Firm::TABLE][0]; */
                    /* include DIR_INC . '/firm_edit.php'; */
                } else {
                    echo "<br><div class='alert alert-info' role='alert'>".__('Активных прайсов нет')."</div>";
                }
            ?>
        </div>
        <div class="tab-pane fade" id="tab_notify_<?=$abon[Abon::F_ID]?>" role="tabpanel">
            <?php require DIR_INC . '/notify_view.php'; ?>
        </div>

    </div>
</div>
<?php
unset($abon);
unset($item);
?>
