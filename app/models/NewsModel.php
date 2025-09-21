<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : NewsModel.php
 *  Path    : app/models/NewsModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use config\tables\News;

/**
 * Description of NewsModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class NewsModel extends AppBaseModel {

    function get_news(?string $sql = null): array {
        if (!$sql) {
            $sql = "SELECT * FROM `".News::TABLE."` WHERE `".News::F_IS_VISIBLE."`=1 ORDER BY `".News::F_DATE_PUBLICATION."` DESC";
        }
    }




}