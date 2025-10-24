<?php
/*
 *  Project : my.ri.net.ua
 *  File    : DocsModel.php
 *  Path    : app/models/DocsModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use config\tables\Docs;

/**
 * Description of DocsModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class DocsModel extends AbonModel {

    function get_docs(?string $sql = null): array {
        if (!$sql) {
            $sql = "SELECT * FROM `". Docs::TABLE."` WHERE `".Docs::F_IS_VISIBLE."`=1 ORDER BY `".Docs::F_DATE_PUBLICATION."` DESC";
        }
    }




}