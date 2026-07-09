<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AclModel.php
 *  Path    : app/models/AclModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 27 Jun 2026
 *  License : GPL v3
 */

namespace app\models;

use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\TP;

class AclModel extends AppBaseModel
{
    public function getAclTables(): array
    {
        $sql = "SELECT t.*, COUNT(l.`" . DevAclList::F_ID . "`) AS acl_count "
            . "FROM `" . DevAclTable::TABLE . "` t "
            . "LEFT JOIN `" . DevAclList::TABLE . "` l ON l.`" . DevAclList::F_ACL_TABLE_ID . "` = t.`" . DevAclTable::F_ID . "` "
            . "GROUP BY t.`" . DevAclTable::F_ID . "` "
            . "ORDER BY t.`" . DevAclTable::F_NAME . "` ASC";

        return $this->get_rows_by_sql($sql);
    }

    public function getAclTableById(int $id): ?array
    {
        return $this->get_row_by_id(DevAclTable::TABLE, $id, DevAclTable::F_ID);
    }

    public function getAclRecordById(int $id): ?array
    {
        return $this->get_row_by_id(DevAclList::TABLE, $id, DevAclList::F_ID);
    }

    public function aclTableNameExists(string $name, ?int $exceptId = null): bool
    {
        $sql = "SELECT `" . DevAclTable::F_ID . "` "
            . "FROM `" . DevAclTable::TABLE . "` "
            . "WHERE BINARY `" . DevAclTable::F_NAME . "` = " . $this->quote($name) . " "
            . ($exceptId !== null ? "AND `" . DevAclTable::F_ID . "` <> " . (int) $exceptId . " " : "")
            . "LIMIT 1";

        return !empty($this->get_rows_by_sql($sql));
    }

    public function aclRecordExists(int $aclTableId, ?int $tpId, string $address, ?int $exceptId = null): bool
    {
        $sql = "SELECT `" . DevAclList::F_ID . "` "
            . "FROM `" . DevAclList::TABLE . "` "
            . "WHERE `" . DevAclList::F_ACL_TABLE_ID . "` = " . (int) $aclTableId . " "
            . ($tpId === null
                ? "AND `" . DevAclList::F_TP_ID . "` IS NULL "
                : "AND `" . DevAclList::F_TP_ID . "` = " . (int) $tpId . " ")
            . "AND `" . DevAclList::F_ADDRESS . "` = " . $this->quote($address) . " "
            . ($exceptId !== null ? "AND `" . DevAclList::F_ID . "` <> " . (int) $exceptId . " " : "")
            . "LIMIT 1";

        return !empty($this->get_rows_by_sql($sql));
    }

    public function countAclRecordsByTableId(int $aclTableId): int
    {
        return (int) $this->get_count(
            DevAclList::TABLE,
            "`" . DevAclList::F_ACL_TABLE_ID . "` = " . (int) $aclTableId,
            DevAclList::F_ID
        );
    }

    public function getAclRecords(?int $aclTableId = null, string $address = '', int $limit = 50, int $offset = 0): array
    {
        $where = [];
        if ($aclTableId !== null) {
            $where[] = "l.`" . DevAclList::F_ACL_TABLE_ID . "` = " . (int) $aclTableId;
        }
        if ($address !== '') {
            $where[] = "l.`" . DevAclList::F_ADDRESS . "` LIKE " . $this->quote('%' . $address . '%');
        }

        $sql = "SELECT l.*, "
            . "t.`" . DevAclTable::F_NAME . "` AS acl_table_name, "
            . "tp.`" . TP::F_TITLE . "` AS tp_title "
            . "FROM `" . DevAclList::TABLE . "` l "
            . "LEFT JOIN `" . DevAclTable::TABLE . "` t ON t.`" . DevAclTable::F_ID . "` = l.`" . DevAclList::F_ACL_TABLE_ID . "` "
            . "LEFT JOIN `" . TP::TABLE . "` tp ON tp.`" . TP::F_ID . "` = l.`" . DevAclList::F_TP_ID . "` "
            . ($where ? "WHERE " . implode(" AND ", $where) . " " : "")
            . "ORDER BY t.`" . DevAclTable::F_NAME . "` ASC, l.`" . DevAclList::F_ADDRESS . "` ASC "
            . "LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->get_rows_by_sql($sql);
    }

    public function countAclRecords(?int $aclTableId = null, string $address = ''): int
    {
        $where = [];
        if ($aclTableId !== null) {
            $where[] = "`" . DevAclList::F_ACL_TABLE_ID . "` = " . (int) $aclTableId;
        }
        if ($address !== '') {
            $where[] = "`" . DevAclList::F_ADDRESS . "` LIKE " . $this->quote('%' . $address . '%');
        }

        return (int) $this->get_count(
            DevAclList::TABLE,
            $where ? implode(" AND ", $where) : null,
            DevAclList::F_ID
        );
    }

    public function deleteAclRecord(int $id): bool
    {
        return $this->execute(
            "DELETE FROM `" . DevAclList::TABLE . "` WHERE `" . DevAclList::F_ID . "` = ?",
            [$id]
        );
    }

    public function deleteAclTable(int $id): bool
    {
        return $this->execute(
            "DELETE FROM `" . DevAclTable::TABLE . "` WHERE `" . DevAclTable::F_ID . "` = ?",
            [$id]
        );
    }
}
