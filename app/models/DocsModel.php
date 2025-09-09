<?php


namespace app\models;


use config\tables\Docs;


class DocsModel extends AbonModel {

    function get_docs(?string $sql = null): array {
        if (!$sql) {
            $sql = "SELECT * FROM `". Docs::TABLE."` WHERE `".Docs::F_IS_VISIBLE."`=1 ORDER BY `".Docs::F_DATE_PUBLICATION."` DESC";
        }
    }




}
