<?php
/**
 * @var array $rows
 */
?>
<div class="container-fluid">
    <h2 class="display-6 ali text-center"><?=__('Редактирование ролей пользователй');?></h2>
    <br>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(t: $rows); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <hr>
</div>
