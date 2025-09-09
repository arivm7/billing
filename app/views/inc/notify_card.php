<?php
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/**
 * @var array $sms  данные одной записи из таблицы sms_list
 *                  ключи: id, abon_id, type_id, date, text, phonenumber, method
 */

use config\tables\Module;

// преобразуем дату
$dateFormatted = !empty($item['date']) ? date('Y-m-d H:i:s', $item['date']) : '-';

// словарь типов уведомлений
$typeNames = [
    1 => 'SMS',
    2 => 'Email',
    3 => 'Другое',
];
$typeName = $typeNames[$item['type_id']] ?? 'Неизвестно';
?>
<?php if (can_view([Module::MOD_NOTIFY, Module::MOD_MY_NOTIFY])) : ?>
    <div class="card">
        <div class="card-header">
            <h3 class="fs-5" ><span class="text-secondary small"><?=h($item['id']);?> | </span><?= h($typeName) ?> (<?= $item['type_id'] ?>)</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <!--
                <tr>
                    <th>ID</th>
                    <td><?= h($item['id']) ?></td>
                </tr>
                <tr>
                    <th>Абонент (ID)</th>
                    <td><?= h($item['abon_id']) ?></td>
                </tr>
                <tr>
                    <th>Тип уведомления</th>
                    <td><?= h($typeName) ?> (<?= $item['type_id'] ?>)</td>
                </tr>
                -->
                <tr>
                    <th>Текст сообщения</th>
                    <td><pre class="h3 fs-5 mb-0"><?= cleaner_html($item['text']) ?></pre></td>
                </tr>
                <tr>
                    <th>Получатель</th>
                    <td><?= h($item['phonenumber']) ?></td>
                </tr>
                <tr>
                    <th>Дата отправки</th>
                    <td><?= $dateFormatted ?></td>
                </tr>
                <?php if (can_view([Module::MOD_NOTIFY])) : ?>
                <tr>
                    <th>Метод отправки</th>
                    <td><?= h($item['method']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
<?php endif; ?>
