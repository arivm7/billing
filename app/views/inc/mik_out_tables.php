<?php
declare(strict_types=1);
use config\Api;
use config\Mik;
use config\tables\TP;

/** @var array $out_tables */
/** @var array $mik_rec */
/** @var array $bill_rec */

$out_list = $out_tables;
//debug($out_list[Api::OUT_PA], '$out_list[Api::OUT_PA]');

?>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
    <?php foreach ($out_list as $list => $out_rec) : ?>
        <li class="nav-item" role="presentation">
            <!-- active -->
            <button class="nav-link"
                    id="tab-<?=$list;?>"
                    data-bs-toggle="tab"
                    data-bs-target="#pane-<?=$list;?>"
                    type="button"
                    role="tab"
                    aria-controls="pane-<?=$list;?>"
                    aria-selected="true">
                <?=$out_rec['title'];?><span class="text-secondary"> | <?=count($out_rec['t']);?> |</span>
            </button>
        </li>
    <?php endforeach; ?>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content border border-top-0 p-3">
    <?php foreach ($out_list as $list => $out_rec) : ?>
        <!-- show active -->
        <div class="tab-pane fade"
             id="pane-<?=$list;?>"
             role="tabpanel"
             aria-labelledby="tab-<?=$list;?>">

            <h2 class="fs-4"><?=$out_rec['caption'];?></h2>
            <?= get_html_table($out_rec['t'],
                        cell_attributes: $out_rec['cell_attributes'],
                        col_titles: $out_rec['col_titles']);?>

        </div>
    <?php endforeach; ?>
    </div>
