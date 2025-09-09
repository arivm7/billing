<?php
use config\tables\Notify;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/**
 * @var array $sms  данные одной записи из таблицы sms_list
 *                  ключи: id, abon_id, type_id, date, text, phonenumber, method
 */
?>
<!-- Перебор подключенных прайсовых фрагментов -->
<?php
//debug($abon[Notify::TABLE], '$abon[Notify::TABLE]', die: 1);
if ($abon[Notify::TABLE]) {
        echo get_html_accordion(
                table: $abon[Notify::TABLE],
                file_view: DIR_INC . '/notify_card.php',
                func_get_title: function(array $item) {
                                    return '<span class="text-secondary">' . date('Y-m-d H:i:s', $item[Notify::F_DATE]) . ' :</span>&nbsp;' . h($item[Notify::F_TEXT]);
                            }
        );
    } else {
        echo "<br><div class='alert alert-info' role='alert'>".__('Нет уведомлений для отображения')."</div>";
    }
?>
