<?php
if (!isset($pager)) return;
/** @var \billing\core\Pagination $pager */
?>
<div class="text-center">
    <?php if ($pager->count_pages > 1) : ?>
        <?=$pager;?>
    <?php endif; ?>
</div>
