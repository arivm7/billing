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
 * Пречень кнопок перехода на формы внесения платежей по всем ТП
 * 
 * app/controllers/BankController.php
 *          app/views/Bank/indexView.php (этот)
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
            <a class="btn btn-outline-primary ms-3" 
                href="<?= Bank::URI_GET; ?>/<?= $ppp[Ppp::F_ID]; ?>"
                title="<?= __('Проверить платежи') ?> <?= $ppp[Ppp::F_TITLE]; ?>"
                ><?= $ppp[Ppp::F_TITLE]; ?> | GET/<?= $ppp[Ppp::F_ID]; ?></a>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>