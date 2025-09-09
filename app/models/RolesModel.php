<?php



namespace app\models;

use config\tables\Role;


class RolesModel extends AppBaseModel {

    public function __construct() {
        parent::__construct();
        $this->table = Role::TABLE;
    }



}
