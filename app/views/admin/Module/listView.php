<?php
/**
 *  @var string $title
 *  @var array  $table
 */
?>
<div class="container">
    <h2><?=__('Список модулей сайта');?></h2>
    <a href="/admin/module/form" class="btn btn-info">Новый модуль</a>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(t: $table,
            cell_attributes: ["id", "route_api", "valign=top", "perm", "modified"]); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>
