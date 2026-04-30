<?php
/**
 *  Project : my.ri.net.ua
 *  File    : FirmsController.php
 *  Path    : app/controllers/FirmsController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Apr 2026 22:23:47
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of FirmsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace app\controllers;

use app\models\FirmsModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Auth;
use config\tables\Employees;
use config\tables\Firm;
use config\tables\Module;
use config\tables\User;

class FirmsController extends AppBaseController {

    private const EMPLOYEE_ORIGIN_USER_ID = 'origin_user_id';


    private function requireUseAccess(): void {
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use([Module::MOD_FIRM, Module::MOD_MY_FIRM])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
            self::log_no_rights();
            redirect();
        }
    }


    private function requireViewAccess(): void {
        $this->requireUseAccess();

        if (!can_view([Module::MOD_FIRM, Module::MOD_MY_FIRM])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to view'));
            redirect();
        }
    }


    private function requireEditAccess(): void {
        $this->requireUseAccess();

        if (!can_edit([Module::MOD_FIRM, Module::MOD_MY_FIRM])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to edit'));
            redirect(Firm::URI_INDEX);
        }
    }


    private function requireDeleteAccess(): void {
        $this->requireUseAccess();

        if (!can_del([Module::MOD_FIRM, Module::MOD_MY_FIRM])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to delete'));
            redirect(Firm::URI_INDEX);
        }
    }


    private function canUseAdminFirmAccess(): bool {
        return can_use(Module::MOD_FIRM);
    }


    private function resolveListUserId(): int {
        $currentUserId = (int) App::get_user_id();
        $queryUserId = isset($_GET[User::F_ID]) ? (int) $_GET[User::F_ID] : 0;

        if ($queryUserId > 0) {
            if (!$this->canUseAdminFirmAccess()) {
                MsgQueue::msg(MsgType::ERROR_AUTO, __('Passing user_id is not allowed for this access level'));
                redirect(Firm::URI_INDEX);
            }

            return $queryUserId;
        }

        return $currentUserId;
    }


    private function ensureFirmEditable(array $firm): void {
        if ($this->canUseAdminFirmAccess()) {
            return;
        }

        if ((int) ($firm[Firm::F_OWNER_ID] ?? 0) !== (int) App::get_user_id()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to edit this enterprise'));
            redirect(Firm::URI_INDEX);
        }
    }


    private function normalizeFirmFlags(array $row): array {
        $flags = [
            Firm::F_HAS_ACTIVE,
            Firm::F_HAS_DELETE,
            Firm::F_HAS_AGENT,
            Firm::F_HAS_CLIENT,
            Firm::F_HAS_ALL_VISIBLE,
            Firm::F_HAS_ALL_LINKING,
        ];

        foreach ($flags as $flag) {
            $row[$flag] = array_key_exists($flag, $row)
                ? (($row[$flag] === '1' || $row[$flag] === 1 || $row[$flag] === 'on') ? 1 : 0)
                : 0;
        }

        return $row;
    }


    public function indexAction(): void {
        $this->requireViewAccess();

        $userId = $this->resolveListUserId();
        $model = new FirmsModel();

        $myFirms = $model->getOwnedFirmsByUserId($userId);
        $providerFirms = $model->getProviderFirmsByUserId($userId);
        $abonFirms = $model->getAbonFirmsByProviderUserId($userId);

        $title = __('Enterprises and employees');

        $this->setVariables([
            'title' => $title,
            'list_user_id' => $userId,
            'my_firms' => $myFirms,
            'provider_firms' => $providerFirms,
            'abon_firms' => $abonFirms,
            'uri_edit' => Firm::URI_EDIT,
        ]);

        View::setMeta(title: $title);
    }


    public function editAction(): void {
        $this->requireViewAccess();

        $firmId = isset($this->route[F_ALIAS]) ? (int) $this->route[F_ALIAS] : 0;
        if ($firmId <= 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Enterprise ID is not specified'));
            redirect(Firm::URI_INDEX);
        }

        $model = new FirmsModel();
        $firm = $model->getFirmById($firmId);

        if (empty($firm)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Enterprise not found'));
            redirect(Firm::URI_INDEX);
        }

        if (!$this->canUseAdminFirmAccess() && (int) ($firm[Firm::F_OWNER_ID] ?? 0) !== (int) App::get_user_id()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to view this enterprise'));
            redirect(Firm::URI_INDEX);
        }

        $employees = $model->getFirmEmployees($firmId);
        $title = __('Editing enterprise') . ' :: ' . ($firm[Firm::F_NAME_LONG] ?? ('#' . $firmId));

        $this->setVariables([
            'title' => $title,
            'firm' => $firm,
            'employees' => $employees,
            'uri_save_firm' => Firm::URI_SAVE,
            'uri_save_employee' => Firm::URI_EMPLOYEE_SAVE,
            'uri_delete_employee' => Firm::URI_EMPLOYEE_DELETE,
            'uri_index' => Firm::URI_INDEX,
            'employee_origin_user_id_field' => self::EMPLOYEE_ORIGIN_USER_ID,
        ]);

        View::setMeta(title: $title);
    }


    public function saveFirmAction(): void {
        $this->requireEditAccess();

        if (!isset($_POST[Firm::POST_REC]) || !is_array($_POST[Firm::POST_REC])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No data to save'));
            redirect(Firm::URI_INDEX);
        }

        $row = $_POST[Firm::POST_REC];
        $firmId = isset($row[Firm::F_ID]) ? (int) $row[Firm::F_ID] : 0;

        if ($firmId <= 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid enterprise ID'));
            redirect(Firm::URI_INDEX);
        }

        $model = new FirmsModel();
        $firm = $model->getFirmById($firmId);

        if (empty($firm)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Enterprise not found'));
            redirect(Firm::URI_INDEX);
        }

        $this->ensureFirmEditable($firm);

        $row[Firm::F_ID] = $firmId;
        $row[Firm::F_OWNER_ID] = $this->canUseAdminFirmAccess()
            ? (isset($row[Firm::F_OWNER_ID]) ? (int) $row[Firm::F_OWNER_ID] : (int) ($firm[Firm::F_OWNER_ID] ?? 0))
            : (int) ($firm[Firm::F_OWNER_ID] ?? 0);
        $row[Firm::F_PPP_DEFAULT_ID] = ($row[Firm::F_PPP_DEFAULT_ID] ?? '') === '' ? null : (int) $row[Firm::F_PPP_DEFAULT_ID];
        $row = $this->normalizeFirmFlags($row);

        if (($row[Firm::F_NAME_LONG] ?? '') === '' || ($row[Firm::F_NAME_SHORT] ?? '') === '') {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Enterprise name fields must not be empty'));
            redirect(Firm::URI_EDIT . '/' . $firmId);
        }
        
        if (($row[Firm::F_NAME_TITLE] ?? '') === '') {
            $row[Firm::F_NAME_TITLE] = $row[Firm::F_NAME_SHORT] ?? $row[Firm::F_NAME_LONG];
        }

        if ($model->saveFirm($row)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Enterprise data saved'));
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Failed to save enterprise data'));
        }

        redirect(Firm::URI_EDIT . '/' . $firmId);
    }


    public function saveEmployeeAction(): void {
        $this->requireEditAccess();

        if (!isset($_POST[Employees::POST_REC]) || !is_array($_POST[Employees::POST_REC])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No employee data to save'));
            redirect(Firm::URI_INDEX);
        }

        $row = $_POST[Employees::POST_REC];
        $firmId = isset($row[Employees::F_FIRM_ID]) ? (int) $row[Employees::F_FIRM_ID] : 0;
        $userId = isset($row[Employees::F_USER_ID]) ? (int) $row[Employees::F_USER_ID] : 0;
        $originUserId = isset($row[self::EMPLOYEE_ORIGIN_USER_ID]) && $row[self::EMPLOYEE_ORIGIN_USER_ID] !== ''
            ? (int) $row[self::EMPLOYEE_ORIGIN_USER_ID]
            : null;

        if ($firmId <= 0 || $userId <= 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid employee data'));
            redirect(Firm::URI_INDEX);
        }

        $model = new FirmsModel();
        $firm = $model->getFirmById($firmId);

        if (empty($firm)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Enterprise not found'));
            redirect(Firm::URI_INDEX);
        }

        $this->ensureFirmEditable($firm);

        if (!$model->validate_id(User::TABLE, $userId, User::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('User not found'));
            redirect(Firm::URI_EDIT . '/' . $firmId);
        }

        $row[Employees::F_FIRM_ID] = $firmId;
        $row[Employees::F_USER_ID] = $userId;
        $row[Employees::F_JOB_TITLE] = trim((string) ($row[Employees::F_JOB_TITLE] ?? ''));

        if ($model->saveEmployee($row, $originUserId)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Employee data saved'));
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Failed to save employee data'));
        }

        redirect(Firm::URI_EDIT . '/' . $firmId);
    }


    public function deleteEmployeeAction(): void {
        $this->requireDeleteAccess();

        $firmId = isset($_GET[Employees::F_FIRM_ID]) ? (int) $_GET[Employees::F_FIRM_ID] : 0;
        $userId = isset($_GET[Employees::F_USER_ID]) ? (int) $_GET[Employees::F_USER_ID] : 0;

        if ($firmId <= 0 || $userId <= 0) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Invalid delete parameters'));
            redirect(Firm::URI_INDEX);
        }

        $model = new FirmsModel();
        $firm = $model->getFirmById($firmId);

        if (empty($firm)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Enterprise not found'));
            redirect(Firm::URI_INDEX);
        }

        $this->ensureFirmEditable($firm);

        if ($model->deleteEmployee($firmId, $userId)) {
            MsgQueue::msg(MsgType::SUCCESS_AUTO, __('Employee removed'));
        } else {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Failed to remove employee'));
        }

        redirect(Firm::URI_EDIT . '/' . $firmId);
    }
}