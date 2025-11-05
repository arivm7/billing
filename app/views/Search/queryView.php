<?php
/**
 *  Project : my.ri.net.ua
 *  File    : queryView.php
 *  Path    : app/views/Search/queryView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 04 Nov 2025 07:00:22
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of queryView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


use config\Search;

require_once DIR_LIBS . '/functions.php'

/** 
 * @var string $query
 * @var array $found
 */            

?>
<div class="align-items-start text-start" >
    <h2 class="text-center fs-6" >Поиск: <span class="fs-4 text-success-emphasis"><?=h($query);?></span></h2>
    <?php foreach ($found as $row) : ?>
        <!-- Вывод результатов поиска -->
        <h3 class="fs-6 mt-5">Поиск в таблице: <span class="fs-4 text-success-emphasis"><?=$row['title'];?></span>...</h3>
        <?php if ($row['count'] > 0) : ?>
            <?php if ($row['pager']) : ?>
                <?php $pager = $row['pager']; include DIR_INC . '/pager.php'; ?>
            <?php else: ?>
                <h4 class="fs-6">Показано записей: <span class="fs-4 text-success-emphasis"><?=count($row['found']);?></span> | Найдено записей: <span class="fs-4 text-success-emphasis"><?=$row['count'];?></span>.</h3>
            <?php endif; ?>
            <?= get_html_table($row['found'],
                    table_attributes: "class='table table-bordered table-striped table-hover align-middle min-w-50 w-auto text-break'",
                    col_titles:       (isset($row[Search::F_COL_TITLES]) ? $row[Search::F_COL_TITLES] : null),
                    cell_attributes:  (isset($row['cell_attributes']) ? $row['cell_attributes'] : null));
            ?>
            <?php if ($row['pager']) : ?>
                <?php $pager = $row['pager']; include DIR_INC . '/pager.php'; ?>
            <?php else: ?>
                <?php if (count($row['found']) < $row['count']) : ?>
                    <a href="?t=<?=$row[Search::F_TABLE];?>&q=<?=$query;?>" class="btn btn-outline-success btn-sm"><?=__('Смотреть полный список');?> >>></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>