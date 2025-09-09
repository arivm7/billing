<?php


namespace app\models;
use config\tables\User;

class AdminUsersModel extends AppBaseModel {


    public string $t_users = User::TABLE;


    function __construct(string $db_user_table = User::TABLE) {
        parent::__construct();
        $this->t_users = $db_user_table;
    }

    public function get_users_list(int $limit_start = -1, int $count = -1): array {
        if (($limit_start >= 0) and ($count > 0)) {
            return $this->get_rows_by_where(table: $this->t_users, limit: "{$limit_start},{$count}");
        }
        return $this->get_rows_by_where(table: $this->t_users);
    }


    public function get_users_count(): int {
        return $this->get_count($this->t_users);
    }



}
