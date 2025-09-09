<?php
/** @var string $title */
/** @var array $posts */
?>
<div class="container">
    <h1 class="display-6 ali text-center"><?=$title;?></h1>
    <br>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <?= get_html_table(t: $posts); ?>
    <?php include DIR_VIEWS . '/inc/pager.php'; ?>
    <hr>
</div>
