<?php
/*
 *  Project : my.ri.net.ua
 *  File    : SecurityAttackEventModel.php
 *  Path    : app/models/SecurityAttackEventModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

class SecurityAttackEventModel extends AppBaseModel {

    public const TABLE = 'security_attack_events';


    public function getAll(): array {
        $sql = "SELECT e.*, t.`title` AS `event_type_title`
                FROM `" . self::TABLE . "` e
                LEFT JOIN `security_attack_types` t ON t.`id` = e.`event_type_id`
                ORDER BY e.`date_attack` DESC, e.`ip` ASC";

        return $this->get_rows_by_sql($sql);
    }


    public function delete(string $ip, int $eventTypeId): bool {
        $sql = "DELETE FROM `" . self::TABLE . "`
                WHERE `ip` = ?
                  AND `event_type_id` = ?";

        return $this->db->execute($sql, [$ip, $eventTypeId]);
    }
}
