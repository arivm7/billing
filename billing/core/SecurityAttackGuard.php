<?php
/*
 *  Project : my.ri.net.ua
 *  File    : SecurityAttackGuard.php
 *  Path    : billing/core/SecurityAttackGuard.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace billing\core;

use Exception;

/**
 * Сервис регистрации атак и проверки блокировки IP.
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class SecurityAttackGuard {

    public const EVENT_TYPE_SCAN = 1;
    public const LOG_FILENAME = 'ip_denied.log';
    public const PROGRAMMING_ERROR_LOG = 'security_attack_guard_errors.log';


    protected static function db(): Db {
        return Db::instance();
    }


    public static function isValidIp(string $ip): bool {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }


    public static function enforceRequestAccess(string $ip, int $eventTypeId = self::EVENT_TYPE_SCAN): void {
        if (!self::isValidIp($ip)) {
            self::logDeniedRequest($ip ?: 'UNKNOWN', 'INVALID OR HIDDEN IP');
            self::denyRequest('Доступ запрещён. Обратитесь к мастеру участка.');
        }

        if (self::isBlocked($ip, $eventTypeId)) {
            self::incrementBlockedIpTriggerCount($ip, $eventTypeId);
            self::logDeniedRequest($ip, 'BLOCKED BY SECURITY_ATTACK_GUARD');
            self::denyRequest('Доступ запрещён. Обратитесь к мастеру участка.');
        }
    }


    public static function isBlocked(string $ip, int $eventTypeId = self::EVENT_TYPE_SCAN): bool {
        $sql = "SELECT `blocked_at`, `expires_at`
                FROM `security_blocked_ip`
                WHERE `ip` = ?
                  AND `event_type_id` = ?
                LIMIT 1";

        $row = self::db()->query($sql, [$ip, $eventTypeId])[0] ?? null;
        if (empty($row)) {
            return false;
        }

        if ($row['expires_at'] === null) {
            return true;
        }

        return (int) $row['expires_at'] >= time();
    }


    public static function registerAttack(string $ip, int $eventTypeId = self::EVENT_TYPE_SCAN): bool {
        if (!self::isValidIp($ip)) {
            self::logDeniedRequest($ip ?: 'UNKNOWN', 'ATTACK REGISTRATION WITH INVALID IP');
            return false;
        }

        $attackType = self::getAttackType($eventTypeId);
        $now = time();
        $event = self::getAttackEvent($ip, $eventTypeId);

        if (empty($event)) {
            self::createAttackEvent($ip, $eventTypeId, $now);
            return false;
        }

        $dateAttack = (int) $event['date_attack'];
        $countAttacks = (int) $event['count_attacks'];

        if (($now - $dateAttack) <= (int) $attackType['analytical_interval']) {
            $countAttacks++;
            self::incrementAttackEvent($ip, $eventTypeId);
        } else {
            $countAttacks = 1;
            self::resetAttackEventWindow($ip, $eventTypeId, $now);
        }

        if ($countAttacks > (int) $attackType['threshold_count']) {
            $expiresAt = self::calculateExpiresAt(
                blockingTime: isset($attackType['blocking_time']) ? (int) $attackType['blocking_time'] : null,
                now: $now
            );

            if (self::upsertBlockedIp($ip, $eventTypeId, $expiresAt, $now)) {
                self::deleteAttackEvent($ip, $eventTypeId);
                return true;
            }
        }

        return false;
    }


    public static function logDeniedRequest(string $ip, string $reason): void {
        error_log(
            message: date('Y-m-d H:i:s') . ' | ' . sprintf('%-15s', $ip) . ' | ' . $reason . ' | ' . self::getFullRequestUrl() . PHP_EOL,
            message_type: 3,
            destination: DIR_LOG . '/' . self::LOG_FILENAME
        );
    }


    public static function cleanupExpiredAttackEvents(): int {
        $now = time();

        $sqlCount = "SELECT COUNT(*)
                     FROM `security_attack_events` e
                     INNER JOIN `security_attack_types` t
                         ON t.`id` = e.`event_type_id`
                     WHERE (? - e.`date_attack`) > t.`analytical_interval`";

        $expiredCount = (int) self::db()->query($sqlCount, [$now], fetchCell: 0);

        if ($expiredCount === 0) {
            return 0;
        }

        $sqlDelete = "DELETE e
                      FROM `security_attack_events` e
                      INNER JOIN `security_attack_types` t
                          ON t.`id` = e.`event_type_id`
                      WHERE (? - e.`date_attack`) > t.`analytical_interval`";

        self::db()->execute($sqlDelete, [$now]);

        return $expiredCount;
    }


    protected static function getAttackType(int $eventTypeId): array {
        $sql = "SELECT `id`, `title`, `threshold_count`, `analytical_interval`, `blocking_time`, `description`
                FROM `security_attack_types`
                WHERE `id` = ?
                LIMIT 1";

        $row = self::db()->query($sql, [$eventTypeId])[0] ?? null;

        if (empty($row)) {
            error_log(
                message: date('Y-m-d H:i:s') . ' | ' . sprintf('%-15s', $_SERVER['REMOTE_ADDR'] ?: 'UNKNOWN') . ' | UNKNOWN EVENT TYPE ID: ' . $eventTypeId . ' | ' . self::getFullRequestUrl() . PHP_EOL,
                message_type: 3,
                destination: DIR_LOG . '/' . self::PROGRAMMING_ERROR_LOG
            );
            throw new Exception("Неизвестный тип атаки [{$eventTypeId}]");
        }

        return $row;
    }


    protected static function getAttackEvent(string $ip, int $eventTypeId): ?array {
        $sql = "SELECT `ip`, `event_type_id`, `date_attack`, `count_attacks`
                FROM `security_attack_events`
                WHERE `ip` = ?
                  AND `event_type_id` = ?
                LIMIT 1";

        return self::db()->query($sql, [$ip, $eventTypeId])[0] ?? null;
    }


    protected static function createAttackEvent(string $ip, int $eventTypeId, int $now): bool {
        $sql = "INSERT INTO `security_attack_events`
                    (`ip`, `event_type_id`, `date_attack`, `count_attacks`)
                VALUES (?, ?, ?, 1)";

        return self::db()->execute($sql, [$ip, $eventTypeId, $now]);
    }


    protected static function incrementAttackEvent(string $ip, int $eventTypeId): bool {
        $sql = "UPDATE `security_attack_events`
                SET `count_attacks` = `count_attacks` + 1
                WHERE `ip` = ?
                  AND `event_type_id` = ?";

        return self::db()->execute($sql, [$ip, $eventTypeId]);
    }


    protected static function resetAttackEventWindow(string $ip, int $eventTypeId, int $now): bool {
        $sql = "UPDATE `security_attack_events`
                SET `date_attack` = ?,
                    `count_attacks` = 1
                WHERE `ip` = ?
                  AND `event_type_id` = ?";

        return self::db()->execute($sql, [$now, $ip, $eventTypeId]);
    }


    protected static function deleteAttackEvent(string $ip, int $eventTypeId): bool {
        $sql = "DELETE FROM `security_attack_events`
                WHERE `ip` = ?
                  AND `event_type_id` = ?";

        return self::db()->execute($sql, [$ip, $eventTypeId]);
    }


    protected static function upsertBlockedIp(string $ip, int $eventTypeId, ?int $expiresAt, int $now): bool {
        $sql = "INSERT INTO `security_blocked_ip`
                    (`ip`, `event_type_id`, `blocked_at`, `expires_at`)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `blocked_at` = VALUES(`blocked_at`),
                    `expires_at` = VALUES(`expires_at`)";

        return self::db()->execute($sql, [$ip, $eventTypeId, $now, $expiresAt]);
    }


    protected static function incrementBlockedIpTriggerCount(string $ip, int $eventTypeId): bool {
        $sql = "UPDATE `security_blocked_ip`
                SET `trigger_counts` = COALESCE(`trigger_counts`, 0) + 1
                WHERE `ip` = ?
                  AND `event_type_id` = ?";

        return self::db()->execute($sql, [$ip, $eventTypeId]);
    }


    protected static function calculateExpiresAt(?int $blockingTime, int $now): ?int {
        if ($blockingTime === null) {
            return null;
        }

        return $now + $blockingTime;
    }


    protected static function getFullRequestUrl(): string {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'UNKNOWN_HOST');

        return $requestUri !== '' ? $scheme . '://' . $host . $requestUri : 'UNKNOWN_URL';
    }


    protected static function denyRequest(string $message): never {
        echo $message;
        die;
    }
}
