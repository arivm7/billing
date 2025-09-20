<?php
/**
 *  @var string $title
 *  @var array  $table
 */
?>
<div class="container">
    <div class='d-flex justify-content-between mb-3'>
        <h2><?=__('Список модулей сайта');?></h2>
        <a href="/admin/module/form" class="btn btn-info"><?=__('Новый модуль');?></a>
    </div>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(
            t: $table,
            cell_attributes: ["id", "route_api", "valign=top", "perm", "modified"]); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>
