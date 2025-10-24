<?php
/*
 *  Project : my.ri.net.ua
 *  File    : RolesModel.php
 *  Path    : app/models/RolesModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use config\tables\Role;

/**
 * Description of RolesModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class RolesModel extends AppBaseModel {

    public function __construct() {
        parent::__construct();
        $this->table = Role::TABLE;
    }



}