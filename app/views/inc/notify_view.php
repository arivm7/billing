<?php
/** app/views/inc/notify_view.php */
use billing\core\Pagination;
use config\tables\Notify;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/**
 * @var array $sms  данные одной записи из таблицы sms_list
 *                  ключи: id, abon_id, type_id, date, text, phonenumber, method
 */

/** @var Pagination $pager */

?>
<!-- Перебор подключенных прайсовых фрагментов -->
<?php if ($abon[Notify::TABLE]) : ?>

    <?=$pager ?? "";?>
    <?= get_html_accordion(
                table: $abon[Notify::TABLE],
                file_view: DIR_INC . '/notify_card.php',
                func_get_title: function(array $item) {
                                    return '<span class="text-secondary">' . date('Y-m-d H:i:s', $item[Notify::F_DATE]) . ' :</span>&nbsp;' . h($item[Notify::F_TEXT]);
                        }
        );?>
    <?php if (empty($pager)) : ?>
        <div class="text-center mt-3">
            <span class="badge rounded-pill text-bg-secondary p-2">
                <?= __('Shown notifications');?> <?=count($abon[Notify::TABLE]);?> / <?=$abon[Notify::F_COUNT];?>
            </span>
        </div>
    <?php else : ?>
        <?=$pager ?? "";?>
    <?php endif; ?>
<?php else : ?>
    <br>
    <div class='alert alert-info' role='alert'>
        <?= __('No notifications to display');?>
    </div>
<?php endif; ?>
