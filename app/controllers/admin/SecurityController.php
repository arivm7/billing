<?php
/*
 *  Project : my.ri.net.ua
 *  File    : SecurityController.php
 *  Path    : app/controllers/admin/SecurityController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers\admin;

use app\models\SecurityAttackEventModel;
use app\models\SecurityAttackTypeModel;
use app\models\SecurityBlockedIpModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Auth;
use config\tables\Module;

class SecurityController extends AdminBaseController {

    
    
    private const URI_INDEX = '/admin/security';
    private const URI_EDIT_TYPE = '/admin/security/editType';
    private const URI_SAVE_TYPE = '/admin/security/saveType';
    private const URI_DELETE_BLOCKED_IP = '/admin/security/deleteBlockedIp';
    private const URI_DELETE_ATTACK_EVENT = '/admin/security/deleteAttackEvent';



    private function requireCanUse(): void {
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
            redirect();
        }
    }



    private function requireCanView(): void {
        $this->requireCanUse();

        if (!can_view(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to view'));
            redirect();
        }
    }



    private function requireCanEdit(): void {
        $this->requireCanUse();

        if (!can_edit(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to edit'));
            redirect(self::URI_INDEX);
        }
    }



    private function requireCanDelete(): void {
        $this->requireCanUse();

        if (!can_del(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to delete'));
            redirect(self::URI_INDEX);
        }
    }



    private static function formatTimestamp(?int $timestamp): string {
        return empty($timestamp) ? '-' : date('Y-m-d H:i:s', $timestamp);
    }



    private static function formatDuration(?int $seconds): string {
        if ($seconds === null) {
            return __('NULL (forever)');
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf(
            '%d %s (%d %s %d %s %d %s %d %s)',
            $seconds,
            __('sec.'),
            $days,
            __('d.'),
            $hours,
            __('h.'),
            $minutes,
            __('min.'),
            $secs,
            __('sec.')
        );
    }



    public function indexAction(): void {
        $this->requireCanView();

        $attackTypeModel = new SecurityAttackTypeModel();
        $attackEventModel = new SecurityAttackEventModel();
        $blockedIpModel = new SecurityBlockedIpModel();

        $attackTypes = $attackTypeModel->getAll();
        foreach ($attackTypes as &$attackType) {
            $attackType['blocking_time_human'] = self::formatDuration(
                $attackType['blocking_time'] === null ? null : (int) $attackType['blocking_time']
            );
        }
        unset($attackType);

        $attackEvents = $attackEventModel->getAll();
        foreach ($attackEvents as &$attackEvent) {
            $attackEvent['date_attack_fmt'] = self::formatTimestamp((int) $attackEvent['date_attack']);
        }
        unset($attackEvent);

        $blockedIps = $blockedIpModel->getAll();
        foreach ($blockedIps as &$blockedIp) {
            $blockedIp['blocked_at_fmt'] = self::formatTimestamp((int) $blockedIp['blocked_at']);
            $blockedIp['expires_at_fmt'] = self::formatTimestamp(
                $blockedIp['expires_at'] === null ? null : (int) $blockedIp['expires_at']
            );
        }
        unset($blockedIp);

        $title = __('Security module');

        $this->setVariables([
            'title' => $title,
            'attack_types' => $attackTypes,
            'attack_events' => $attackEvents,
            'blocked_ips' => $blockedIps,
            'uri_edit_type' => self::URI_EDIT_TYPE,
            'uri_delete_blocked_ip' => self::URI_DELETE_BLOCKED_IP,
            'uri_delete_attack_event' => self::URI_DELETE_ATTACK_EVENT,
        ]);

        View::setMeta(
            title: $title,
        );
    }



    public function editTypeAction(): void {
        $this->requireCanEdit();

        $typeId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $model = new SecurityAttackTypeModel();
        $attackType = $typeId > 0 ? $model->getById($typeId) : null;

        if (empty($attackType)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Attack type not found'));
            redirect(self::URI_INDEX);
        }

        $attackType['blocking_time_human'] = self::formatDuration(
            $attackType['blocking_time'] === null ? null : (int) $attackType['blocking_time']
        );

        $title = __('Editing attack type')
               . ' :: ' . $attackType['title'];

        $this->setVariables([
            'title' => $title,
            'attack_type' => $attackType,
            'uri_save_type' => self::URI_SAVE_TYPE,
            'uri_index' => self::URI_INDEX,
        ]);

        View::setMeta(
            title: $title,
        );
    }



    public function saveTypeAction(): void {
        $this->requireCanEdit();

        if (!isset($_POST['attack_type']) || !is_array($_POST['attack_type'])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No data to save'));
            redirect(self::URI_INDEX);
        }

        $row = $_POST['attack_type'];
        $row['id'] = isset($row['id']) ? (int) $row['id'] : 0;
        $row['threshold_count'] = isset($row['threshold_count']) ? (int) $row['threshold_count'] : 0;
        $row['analytical_interval'] = isset($row['analytical_interval']) ? (int) $row['analytical_interval'] : 0;
        $row['blocking_time'] = ($row['blocking_time'] === '' || $row['blocking_time'] === null)
            ? null
            : (int) $row['blocking_time'];
        $row['title'] = trim((string) ($row['title'] ?? ''));
        $row['description'] = trim((string) ($row['description'] ?? ''));

        if ($row['id'] <= 0 || $row['title'] === '' || $row['threshold_count'] < 0 || $row['analytical_interval'] < 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid data'));
            redirect(self::URI_EDIT_TYPE . '?id=' . $row['id']);
        }

        $model = new SecurityAttackTypeModel();
        if ($model->updateType($row)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Changes saved'));
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Save error'));
        }

        redirect(self::URI_EDIT_TYPE . '?id=' . $row['id']);
    }



    public function deleteBlockedIpAction(): void {
        $this->requireCanDelete();

        $ip = trim((string) ($_GET['ip'] ?? ''));
        $eventTypeId = isset($_GET['event_type_id']) ? (int) $_GET['event_type_id'] : 0;

        if ($ip === '' || $eventTypeId <= 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid delete parameters'));
            redirect(self::URI_INDEX);
        }

        $model = new SecurityBlockedIpModel();
        if ($model->delete($ip, $eventTypeId)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Blocked IP deleted'));
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Delete error'));
        }

        redirect(self::URI_INDEX);
    }



    public function deleteAttackEventAction(): void {
        $this->requireCanDelete();

        $ip = trim((string) ($_GET['ip'] ?? ''));
        $eventTypeId = isset($_GET['event_type_id']) ? (int) $_GET['event_type_id'] : 0;

        if ($ip === '' || $eventTypeId <= 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid delete parameters'));
            redirect(self::URI_INDEX);
        }

        $model = new SecurityAttackEventModel();
        if ($model->delete($ip, $eventTypeId)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Attack event deleted'));
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Delete error'));
        }

        redirect(self::URI_INDEX);
    }



}
