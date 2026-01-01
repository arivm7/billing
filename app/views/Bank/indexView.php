<?php
/**
 *  Project : my.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Bank/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 30 Dec 2025 02:12:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of indexView.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


/**
 * Переменные переданные из контроллера
 * @var array $ppp_list
 */

use config\Bank;
use config\tables\Ppp;

?>

<?php foreach ($ppp_list as $api_type => $by_type_list): ?>
    <?php foreach ($by_type_list as $ppp): ?>
        <div>
            <a class="btn btn-outline-primary" href="<?= Bank::URI_API_LIST[$api_type]; ?>/<?= $ppp[Ppp::F_ID]; ?>"><?= $ppp[Ppp::F_TITLE]; ?></a>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>