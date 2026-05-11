<?php
/*
 *  Project : my.ri.net.ua
 *  File    : TpModel.php
 *  Path    : app/models/TpModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\models;

use billing\core\App;
use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Employees;
use config\tables\Firm;
use config\tables\TP;

/**
 * Description of TpModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class TpModel extends AppBaseModel {

    public function addAclListRecord(
        int $aclTableId,
        string $address,
        int $tpId = null,
        string $comment = '',
        bool $isEnabled = true
    ): int|false 
    {
        if (!$this->validate_id(DevAclTable::TABLE, $aclTableId, DevAclTable::F_ID)) {
            self::add_error_info('Invalid ACL table ID');
            return false;
        }

        if (!is_null($tpId) && !$this->validate_id(TP::TABLE, $tpId, TP::F_ID)) {
            self::add_error_info('Invalid TP ID');
            return false;
        }

        $address = trim($address);
        if (!(validate_ip($address) || is_ip_net($address))) {
            self::add_error_info('Invalid ACL address');
            return false;
        }

        $row = [
            DevAclList::F_ACL_TABLE_ID => $aclTableId,
            DevAclList::F_TP_ID => $tpId,
            DevAclList::F_ADDRESS => $address,
            DevAclList::F_COMMENT => trim($comment),
            DevAclList::F_IS_ENABLED => $isEnabled ? 1 : 0,
            DevAclList::F_CREATION_UID => App::get_user_id(),
            DevAclList::F_CREATION_DATE => time(),
            DevAclList::F_MODIFIED_UID => App::get_user_id(),
            DevAclList::F_MODIFIED_DATE => time(),
        ];

        return $this->insert_row(DevAclList::TABLE, $row);
    }

    public function getProviderFirmsByUserId(int $userId): array
    {
        $sql = "SELECT
                    f.`" . Firm::F_ID . "`,
                    f.`" . Firm::F_NAME_LONG . "`,
                    f.`" . Firm::F_HAS_ACTIVE . "`,
                    f.`" . Firm::F_HAS_AGENT . "`
                FROM `" . Employees::TABLE . "` e
                INNER JOIN `" . Firm::TABLE . "` f
                    ON f.`" . Firm::F_ID . "` = e.`" . Employees::F_FIRM_ID . "`
                WHERE e.`" . Employees::F_USER_ID . "` = ?
                ORDER BY f.`" . Firm::F_NAME_LONG . "` ASC";

        return $this->findBySql($sql, [$userId]);
    }


    public function getTpByTitle(string $title): array|null
    {
        $sql = "SELECT *
                FROM `" . TP::TABLE . "`
                WHERE `" . TP::F_TITLE . "` = ?
                LIMIT 1";

        return $this->findBySql($sql, [$title])[0] ?? null;
    }


    public function getActiveAgentFirmById(int $firmId): array|null
    {
        $sql = "SELECT *
                FROM `" . Firm::TABLE . "`
                WHERE `" . Firm::F_ID . "` = ?
                  AND `" . Firm::F_HAS_ACTIVE . "` = 1
                  AND `" . Firm::F_HAS_AGENT . "` = 1
                LIMIT 1";

        return $this->findBySql($sql, [$firmId])[0] ?? null;
    }

    public function getAclTableById(int $id): array|null
    {
        $sql = "SELECT *
                FROM `" . DevAclTable::TABLE . "`
                WHERE `" . DevAclTable::F_ID . "` = ?
                LIMIT 1";

        return $this->findBySql($sql, [$id])[0] ?? null;
    }


    public function getAclListForSync(int $aclTableId, int $tpId): array
    {
        $sql = "SELECT *
                FROM `" . DevAclList::TABLE . "`
                WHERE `" . DevAclList::F_ACL_TABLE_ID . "` = ?
                  AND `" . DevAclList::F_IS_ENABLED . "` = 1
                  AND (" . DevAclList::F_TP_ID . " IS NULL OR " . DevAclList::F_TP_ID . " = 0 OR " . DevAclList::F_TP_ID . " = ?)
                ORDER BY `" . DevAclList::F_TP_ID . "` ASC, `" . DevAclList::F_ADDRESS . "` ASC";
        return $this->findBySql($sql, [$aclTableId, $tpId]);
    }

}
