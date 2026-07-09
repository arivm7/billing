<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AclController.php
 *  Path    : app/controllers/AclController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 27 Jun 2026
 *  License : GPL v3
 */

namespace app\controllers;

use app\models\AclModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Auth;
use config\SessionFields;
use config\tables\DevAclList;
use config\tables\DevAclTable;
use config\tables\Module;
use config\tables\TP;

class AclController extends AppBaseController
{
    private const PER_PAGE = 50;
    private const LOG_FILE = 'acl.logController';

    private AclModel $db;

    public function __construct(array $route)
    {
        parent::__construct($route);
        $this->db = new AclModel();
    }

    private function requireViewAccess(): void
    {
        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_view(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to view'));
            self::log_no_rights();
            redirect();
        }
    }

    private function requireAddAccess(): void
    {
        $this->requireViewAccess();
        if (!can_add(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to add'));
            redirect(DevAclList::URI_INDEX);
        }
    }

    private function requireEditAccess(): void
    {
        $this->requireViewAccess();
        if (!can_edit(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to edit'));
            redirect(DevAclList::URI_INDEX);
        }
    }

    private function requireDeleteAccess(): void
    {
        $this->requireViewAccess();
        if (!can_del(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to delete'));
            redirect(DevAclList::URI_INDEX);
        }
    }

    public function indexAction(): void
    {
        $this->requireViewAccess();

        $aclTableId = isset($_GET[DevAclList::F_ACL_TABLE_ID]) && $_GET[DevAclList::F_ACL_TABLE_ID] !== ''
            ? (int) $_GET[DevAclList::F_ACL_TABLE_ID]
            : null;
        $address = trim((string) ($_GET[DevAclList::F_ADDRESS] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $total = $this->db->countAclRecords($aclTableId, $address);
        $records = $this->db->getAclRecords($aclTableId, $address, self::PER_PAGE, $offset);

        $title = __('ACL records | Записи ACL | Записи ACL');
        $this->setVariables([
            'title' => $title,
            'records' => $records,
            'tables' => $this->db->getAclTables(),
            'filter_acl_table_id' => $aclTableId,
            'filter_address' => $address,
            'page' => $page,
            'per_page' => self::PER_PAGE,
            'total' => $total,
        ]);
        View::setMeta(title: $title);
    }

    public function addAction(): void
    {
        $this->requireAddAccess();

        $record = [
            DevAclList::F_ID => 0,
            DevAclList::F_TP_ID => ($_GET[DevAclList::F_TP_ID] ?? '') === '' ? null : (int) $_GET[DevAclList::F_TP_ID],
            DevAclList::F_ACL_TABLE_ID => (int) ($_GET[DevAclList::F_ACL_TABLE_ID] ?? 0),
            DevAclList::F_ADDRESS => trim((string) ($_GET[DevAclList::F_ADDRESS] ?? '')),
            DevAclList::F_COMMENT => trim((string) ($_GET[DevAclList::F_COMMENT] ?? '')),
            DevAclList::F_ENABLED => isset($_GET[DevAclList::F_ENABLED]) ? (int) $_GET[DevAclList::F_ENABLED] : 1,
        ];

        $this->renderEdit($record, true);
    }

    public function editAction(): void
    {
        $this->requireViewAccess();

        $id = (int) ($this->route[F_ALIAS] ?? 0);
        if (!$this->db->validate_id(DevAclList::TABLE, $id, DevAclList::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ACL record not found'));
            redirect(DevAclList::URI_INDEX);
        }

        $record = $this->db->getAclRecordById($id);
        $this->renderEdit($record ?? [], false);
    }

    private function renderEdit(array $record, bool $isNew): void
    {
        if (!empty($_SESSION[SessionFields::FORM_DATA][DevAclList::POST_REC])) {
            $record = array_replace($record, $_SESSION[SessionFields::FORM_DATA][DevAclList::POST_REC]);
            unset($_SESSION[SessionFields::FORM_DATA][DevAclList::POST_REC]);
        }

        $title = $isNew
            ? __('Add ACL record | Добавить запись ACL | Додати запис ACL')
            : __('Edit ACL record | Редактировать запись ACL | Редагувати запис ACL');

        $this->setVariables([
            'title' => $title,
            'record' => $record,
            'is_new' => $isNew,
            'tables' => $this->db->getAclTables(),
            'tp_list' => $this->db->get_my_tp_list(App::get_user_id(), null, 0),
        ]);
        View::setMeta(title: $title);
        $this->view = 'edit';
    }

    public function saveAction(): void
    {
        if (empty($_POST[DevAclList::POST_REC]) || !is_array($_POST[DevAclList::POST_REC])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No data to save'));
            redirect(DevAclList::URI_INDEX);
        }

        $row = $this->normalizeRecord($_POST[DevAclList::POST_REC]);
        $id = (int) ($row[DevAclList::F_ID] ?? 0);

        if ($id > 0) {
            $this->requireEditAccess();
        } else {
            $this->requireAddAccess();
        }

        if (!$this->validateRecord($row, $id > 0 ? $id : null)) {
            $_SESSION[SessionFields::FORM_DATA][DevAclList::POST_REC] = $row;
            redirect($id > 0 ? DevAclList::URI_EDIT . '/' . $id : DevAclList::URI_ADD);
        }

        if ($id > 0) {
            if (!$this->db->update_row_by_id(DevAclList::TABLE, $row, DevAclList::F_ID)) {
                MsgQueue::msg(MsgType::ERROR, __('Failed to save ACL record | Не удалось сохранить запись ACL | Не вдалося зберегти запис ACL'));
                $_SESSION[SessionFields::FORM_DATA][DevAclList::POST_REC] = $row;
                redirect(DevAclList::URI_EDIT . '/' . $id);
            }
            MsgQueue::msg(MsgType::SUCCESS, __('ACL record saved | Запись ACL сохранена | Запис ACL збережено'));
            redirect(DevAclList::URI_EDIT . '/' . $id);
        }

        $newId = self::createAclRecord($row);
        if ($newId === false) {
            MsgQueue::msg(MsgType::ERROR, __('Failed to create ACL record | Не удалось создать запись ACL | Не вдалося створити запис ACL'));
            $_SESSION[SessionFields::FORM_DATA][DevAclList::POST_REC] = $row;
            redirect(DevAclList::URI_ADD);
        }

        MsgQueue::msg(MsgType::SUCCESS, __('ACL record created | Запись ACL создана | Запис ACL створено'));
        redirect(DevAclList::URI_EDIT . '/' . $newId);
    }

    public static function createAclRecord(array $row): int|false
    {
        $model = new AclModel();
        unset($row[DevAclList::F_ID]);
        $row[DevAclList::F_CREATION_UID] = App::get_user_id();
        $row[DevAclList::F_CREATION_DATE] = time();
        $row[DevAclList::F_MODIFIED_UID] = App::get_user_id();
        $row[DevAclList::F_MODIFIED_DATE] = time();

        return $model->insert_row(DevAclList::TABLE, $row);
    }

    private function normalizeRecord(array $row): array
    {
        return [
            DevAclList::F_ID => (int) ($row[DevAclList::F_ID] ?? 0),
            DevAclList::F_ACL_TABLE_ID => (int) ($row[DevAclList::F_ACL_TABLE_ID] ?? 0),
            DevAclList::F_TP_ID => isset($row[DevAclList::F_TP_ID]) && $row[DevAclList::F_TP_ID] !== '' ? (int) $row[DevAclList::F_TP_ID] : null,
            DevAclList::F_ADDRESS => trim((string) ($row[DevAclList::F_ADDRESS] ?? '')),
            DevAclList::F_COMMENT => trim((string) ($row[DevAclList::F_COMMENT] ?? '')),
            DevAclList::F_ENABLED => !empty($row[DevAclList::F_ENABLED]) ? 1 : 0,
        ];
    }

    private function validateRecord(array $row, ?int $exceptId = null): bool
    {
        if (!$this->db->validate_id(DevAclTable::TABLE, (int) $row[DevAclList::F_ACL_TABLE_ID], DevAclTable::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('ACL table is required | ACL-таблица обязательна | ACL-таблиця обовʼязкова'));
            return false;
        }

        $tpId = $row[DevAclList::F_TP_ID];
        if ($tpId !== null && !$this->db->validate_id(TP::TABLE, $tpId, TP::F_ID)) {
            MsgQueue::msg(MsgType::ERROR, __('Technical site not found | Техплощадка не найдена | Техмайданчик не знайдено'));
            return false;
        }
        if ($tpId !== null && !$this->db->is_my_tp($tpId, App::get_user_id())) {
            MsgQueue::msg(MsgType::ERROR, __('Technical site is not available | Техплощадка недоступна | Техмайданчик недоступний'));
            return false;
        }

        $address = $row[DevAclList::F_ADDRESS];
        if ($address === '' || (!validate_ip($address) && !is_ip_net($address))) {
            MsgQueue::msg(MsgType::ERROR, __('Invalid IP or network | Неверный IP или сеть | Невірний IP або мережа'));
            return false;
        }

        if ($this->db->aclRecordExists((int) $row[DevAclList::F_ACL_TABLE_ID], $tpId, $address, $exceptId)) {
            MsgQueue::msg(MsgType::ERROR, __('ACL record already exists | Такая запись ACL уже существует | Такий запис ACL вже існує'));
            return false;
        }

        return true;
    }

    public function deleteAction(): void
    {
        $this->requireDeleteAccess();

        $id = (int) ($this->route[F_ALIAS] ?? 0);
        $record = $this->db->getAclRecordById($id);
        if (empty($record)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ACL record not found'));
            redirect(DevAclList::URI_INDEX);
        }

        self::log(
            msg: 'DELETE ACL RECORD: ' . print_r($record, true),
            log_filename: self::LOG_FILE,
            log_url: true
        );

        if (!$this->db->deleteAclRecord($id)) {
            MsgQueue::msg(MsgType::ERROR, __('Failed to delete ACL record | Не удалось удалить запись ACL | Не вдалося видалити запис ACL'));
            redirect(DevAclList::URI_EDIT . '/' . $id);
        }

        MsgQueue::msg(MsgType::SUCCESS, __('ACL record deleted | Запись ACL удалена | Запис ACL видалено'));
        redirect(DevAclList::URI_INDEX . '?' . DevAclList::F_ACL_TABLE_ID . '=' . (int) $record[DevAclList::F_ACL_TABLE_ID]);
    }
}
