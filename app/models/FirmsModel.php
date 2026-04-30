<?php
/**
 *  Project : my.ri.net.ua
 *  File    : FirmsModel.php
 *  Path    : app/models/FirmsModel.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of FirmsModel.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace app\models;

use billing\core\App;
use config\tables\Abon;
use config\tables\Employees;
use config\tables\Firm;
use config\tables\PA;
use config\tables\TP;
use config\tables\User;

class FirmsModel extends AbonModel {

    public function getOwnedFirmsByUserId(int $userId): array {
        $sql = "SELECT
                    f.`" . Firm::F_ID . "`,
                    f.`" . Firm::F_NAME_LONG . "`,
                    f.`" . Firm::F_NAME_TITLE . "`,
                    f.`" . Firm::F_HAS_ACTIVE . "`,
                    f.`" . Firm::F_HAS_DELETE . "`,
                    f.`" . Firm::F_HAS_AGENT . "`,
                    f.`" . Firm::F_HAS_CLIENT . "`,
                    f.`" . Firm::F_OWNER_ID . "`
                FROM `" . Firm::TABLE . "` f
                WHERE f.`" . Firm::F_OWNER_ID . "` = ?
                ORDER BY 
                    f.`" . Firm::F_HAS_ACTIVE . "` DESC,
                    f.`" . Firm::F_HAS_DELETE . "` ASC,
                    f.`" . Firm::F_NAME_LONG . "` ASC";

        return $this->findBySql($sql, [$userId]);
    }


    public function getProviderFirmsByUserId(int $userId): array {
        $sql = "SELECT
                    f.`" . Firm::F_ID . "`,
                    f.`" . Firm::F_NAME_LONG . "`,
                    f.`" . Firm::F_NAME_TITLE . "`,
                    f.`" . Firm::F_HAS_ACTIVE . "`,
                    f.`" . Firm::F_HAS_DELETE . "`,
                    f.`" . Firm::F_HAS_AGENT . "`,
                    f.`" . Firm::F_HAS_CLIENT . "`,
                    f.`" . Firm::F_OWNER_ID . "`,
                    e.`" . Employees::F_JOB_TITLE . "`
                FROM `" . Employees::TABLE . "` e
                INNER JOIN `" . Firm::TABLE . "` f
                    ON f.`" . Firm::F_ID . "` = e.`" . Employees::F_FIRM_ID . "`
                WHERE 
                    e.`" . Employees::F_USER_ID . "` = ? AND
                    f.`" . Firm::F_HAS_AGENT . "` = 1
                ORDER BY 
                    f.`" . Firm::F_HAS_ACTIVE . "` DESC,
                    f.`" . Firm::F_NAME_LONG . "` ASC,
                    f.`" . Firm::F_ID . "` ASC,
                    f.`" . Firm::F_HAS_DELETE . "` ASC
                    ";

        return $this->findBySql($sql, [$userId]);
    }


    public function getAbonFirmsByProviderUserId(int $userId): array {
        $sql = "SELECT
                    DISTINCT
                    a.`" . Abon::F_ID . "` AS `abon_id`,
                    a.`" . Abon::F_ADDRESS . "` AS `abon_address`,
                    f2.`" . Firm::F_ID . "`,
                    f2.`" . Firm::F_NAME_LONG . "`,
                    f2.`" . Firm::F_NAME_TITLE . "`,
                    f2.`" . Firm::F_HAS_ACTIVE . "`,
                    f2.`" . Firm::F_HAS_DELETE . "`,
                    f2.`" . Firm::F_HAS_AGENT . "`,
                    f2.`" . Firm::F_HAS_CLIENT . "`,
                    f2.`" . Firm::F_OWNER_ID . "`
                FROM `" . Employees::TABLE . "` provider
                INNER JOIN `" . TP::TABLE . "` tp
                    ON tp.`" . TP::F_FIRM_ID . "` = provider.`" . Employees::F_FIRM_ID . "`
                INNER JOIN `" . PA::TABLE . "` pa
                    ON pa.`" . PA::F_TP_ID . "` = tp.`" . TP::F_ID . "`
                INNER JOIN `" . Abon::TABLE . "` a
                    ON a.`" . Abon::F_ID . "` = pa.`" . PA::F_ABON_ID . "`
                INNER JOIN `" . Employees::TABLE . "` abon_firm
                    ON abon_firm.`" . Employees::F_USER_ID . "` = a.`" . Abon::F_USER_ID . "`
                INNER JOIN `" . Firm::TABLE . "` f2
                    ON f2.`" . Firm::F_ID . "` = abon_firm.`" . Employees::F_FIRM_ID . "`
                WHERE 
                    provider.`" . Employees::F_USER_ID . "` = ? AND
                    f2.`" . Firm::F_HAS_CLIENT . "` = 1
                ORDER BY 
                    f2.`" . Firm::F_HAS_ACTIVE . "` DESC,
                    f2.`" . Firm::F_NAME_LONG . "` ASC,
                     a.`" . Abon::F_ID . "` ASC, 
                    f2.`" . Firm::F_HAS_DELETE . "` ASC
                    ";
        

        return $this->findBySql($sql, [$userId]);
    }


    public function getFirmById(int $firmId): ?array {
        return $this->get_row_by_id(Firm::TABLE, $firmId, Firm::F_ID) ?: null;
    }


    public function getFirmEmployees(int $firmId): array {
        $sql = "SELECT
                    e.`" . Employees::F_FIRM_ID . "`,
                    e.`" . Employees::F_USER_ID . "`,
                    e.`" . Employees::F_JOB_TITLE . "`,
                    e.`" . Employees::F_CREATION_UID . "`,
                    e.`" . Employees::F_CREATION_DATE . "`,
                    u.`" . User::F_LOGIN . "` AS `user_login`,
                    u.`" . User::F_NAME_SHORT . "` AS `user_name_short`
                FROM `" . Employees::TABLE . "` e
                LEFT JOIN `" . User::TABLE . "` u
                    ON u.`" . User::F_ID . "` = e.`" . Employees::F_USER_ID . "`
                WHERE e.`" . Employees::F_FIRM_ID . "` = ?
                ORDER BY e.`" . Employees::F_USER_ID . "` ASC";

        return $this->findBySql($sql, [$firmId]);
    }


    public function saveFirm(array $row): bool {
        return $this->update_row_by_id(Firm::TABLE, $row, Firm::F_ID);
    }


    public function employeeExists(int $firmId, int $userId): bool {
        $sql = "SELECT 1
                FROM `" . Employees::TABLE . "`
                WHERE `" . Employees::F_FIRM_ID . "` = ?
                  AND `" . Employees::F_USER_ID . "` = ?
                LIMIT 1";

        return !empty($this->findBySql($sql, [$firmId, $userId]));
    }


    public function saveEmployee(array $row, ?int $originUserId = null): bool {
        $firmId = (int) ($row[Employees::F_FIRM_ID] ?? 0);
        $userId = (int) ($row[Employees::F_USER_ID] ?? 0);
        $jobTitle = trim((string) ($row[Employees::F_JOB_TITLE] ?? ''));

        if ($firmId <= 0 || $userId <= 0) {
            return false;
        }

        $originUserId = $originUserId ?: null;

        if ($originUserId === null) {
            if ($this->employeeExists($firmId, $userId)) {
                $sql = "UPDATE `" . Employees::TABLE . "`
                        SET `" . Employees::F_JOB_TITLE . "` = ?
                        WHERE `" . Employees::F_FIRM_ID . "` = ?
                          AND `" . Employees::F_USER_ID . "` = ?";

                return $this->db->execute($sql, [$jobTitle, $firmId, $userId]);
            }

            $insertRow = [
                Employees::F_FIRM_ID => $firmId,
                Employees::F_USER_ID => $userId,
                Employees::F_JOB_TITLE => $jobTitle,
                Employees::F_CREATION_UID => App::get_user_id(),
                Employees::F_CREATION_DATE => time(),
            ];

            return $this->insert_row(Employees::TABLE, $insertRow) !== false;
        }

        if ($originUserId !== $userId && $this->employeeExists($firmId, $userId)) {
            return false;
        }

        $sql = "UPDATE `" . Employees::TABLE . "`
                SET `" . Employees::F_USER_ID . "` = ?,
                    `" . Employees::F_JOB_TITLE . "` = ?
                WHERE `" . Employees::F_FIRM_ID . "` = ?
                  AND `" . Employees::F_USER_ID . "` = ?";

        return $this->db->execute($sql, [$userId, $jobTitle, $firmId, $originUserId]);
    }


    public function deleteEmployee(int $firmId, int $userId): bool {
        $sql = "DELETE FROM `" . Employees::TABLE . "`
                WHERE `" . Employees::F_FIRM_ID . "` = ?
                  AND `" . Employees::F_USER_ID . "` = ?";

        return $this->db->execute($sql, [$firmId, $userId]);
    }
}