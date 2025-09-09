<?php
/** @var array $t */
?>
<div class="container">
    <h3>Список Абонентов</h3>
    <br>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(t: $t); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>
<br>


