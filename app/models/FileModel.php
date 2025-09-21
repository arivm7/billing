<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : FileModel.php
 *  Path    : app/models/FileModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use config\tables\File;

/**
 * Description of FileModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class FileModel extends AppBaseModel {



    function get_file(int $id): ?array {
        if ($this->validate_id(table_name: File::TABLE, field_id: File::F_ID, id_value: $id)) {
            return $this->get_row_by_id(table_name: File::TABLE, field_id: File::F_ID, id_value: $id);
        } else {
            return null;
        }

    }



    function get_files_by_user(int $user_id, int|null $is_public = null): array {
        $is_public_part = (is_null($is_public) ? "" : " AND (`" . File::F_IS_PUBLIC . "`={$is_public})");
        $sql = "SELECT * FROM `" . File::TABLE . "` WHERE (`" . File::F_USER_ID . "`={$user_id}){$is_public_part}";
        return $this->get_rows_by_sql($sql);
    }



}