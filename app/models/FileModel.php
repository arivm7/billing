<?php


namespace app\models;


use config\tables\File;

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
