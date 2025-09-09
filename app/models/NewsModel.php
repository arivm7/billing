<?php


namespace app\models;


use config\tables\News;

class NewsModel extends AppBaseModel {

    function get_news(?string $sql = null): array {
        if (!$sql) {
            $sql = "SELECT * FROM `".News::TABLE."` WHERE `".News::F_IS_VISIBLE."`=1 ORDER BY `".News::F_DATE_PUBLICATION."` DESC";
        }
    }




}
