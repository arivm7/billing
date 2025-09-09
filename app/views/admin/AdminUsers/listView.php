<?php
/**
 *  @var string $title
 *  @var array  $users
 */
?>
<div class="container">
    <h1 class="display-5"><?=$title;?></h1>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(
            t: $users,
            pre_align: isset($params['pre_align']) ? $params['pre_align'] : true,
            col_titles: isset($params['col_titles']) ? $params['col_titles'] : null,
            child_col_titles: isset($params['child_col_titles']) ? $params['child_col_titles'] : true,
            cell_attributes: isset($params['cell_attributes']) ? $params['cell_attributes'] : null,
            child_cell_attributes: isset($params['child_cell_attributes']) ? $params['child_cell_attributes'] : true
        ); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
</div>
