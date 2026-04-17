<?php
/*
 *  Project : my.ri.net.ua
 *  File    : SecurityAttackTypeModel.php
 *  Path    : app/models/SecurityAttackTypeModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

class SecurityAttackTypeModel extends AppBaseModel {

    public const TABLE = 'security_attack_types';


    public function getAll(): array {
        $sql = "SELECT *
                FROM `" . self::TABLE . "`
                ORDER BY `id` ASC";

        return $this->get_rows_by_sql($sql);
    }


    public function getById(int $id): ?array {
        return $this->get_row_by_id(self::TABLE, $id);
    }


    public function updateType(array $row): bool {
        return $this->update_row_by_id(self::TABLE, $row, update_modified: false);
    }
}
