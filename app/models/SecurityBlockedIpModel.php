<?php
/*
 *  Project : my.ri.net.ua
 *  File    : SecurityBlockedIpModel.php
 *  Path    : app/models/SecurityBlockedIpModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

class SecurityBlockedIpModel extends AppBaseModel {

    public const TABLE = 'security_blocked_ip';


    public function getAll(): array {
        $sql = "SELECT b.*, t.`title` AS `event_type_title`
                FROM `" . self::TABLE . "` b
                LEFT JOIN `security_attack_types` t ON t.`id` = b.`event_type_id`
                ORDER BY b.`blocked_at` DESC, b.`ip` ASC";

        return $this->get_rows_by_sql($sql);
    }


    public function delete(string $ip, int $eventTypeId): bool {
        $sql = "DELETE FROM `" . self::TABLE . "`
                WHERE `ip` = ?
                  AND `event_type_id` = ?";

        return $this->db->execute($sql, [$ip, $eventTypeId]);
    }
}
