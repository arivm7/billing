<?php
/*
 *  Project : my.ri.net.ua
 *  File    : AclListController.php
 *  Path    : app/controllers/AclListController.php
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

class AclListController extends AppBaseController
{
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
            redirect(DevAclTable::URI_INDEX);
        }
    }

    private function requireEditAccess(): void
    {
        $this->requireViewAccess();
        if (!can_edit(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to edit'));
            redirect(DevAclTable::URI_INDEX);
        }
    }

    private function requireDeleteAccess(): void
    {
        $this->requireViewAccess();
        if (!can_del(Module::MOD_SECURITY)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to delete'));
            redirect(DevAclTable::URI_INDEX);
        }
    }

    public function indexAction(): void
    {
        $this->requireViewAccess();

        $title = __('ACL tables | ACL-таблицы | ACL-таблиці');
        $this->setVariables([
            'title' => $title,
            'tables' => $this->db->getAclTables(),
        ]);
        View::setMeta(title: $title);
    }

    public function addAction(): void
    {
        $this->requireAddAccess();

        $table = [
            DevAclTable::F_ID => 0,
            DevAclTable::F_NAME => trim((string) ($_GET[DevAclTable::F_NAME] ?? '')),
            DevAclTable::F_DESCRIPTION => trim((string) ($_GET[DevAclTable::F_DESCRIPTION] ?? '')),
        ];

        $this->renderEdit($table, true);
    }

    public function editAction(): void
    {
        $this->requireViewAccess();

        $id = (int) ($this->route[F_ALIAS] ?? 0);
        if (!$this->db->validate_id(DevAclTable::TABLE, $id, DevAclTable::F_ID)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ACL table not found'));
            redirect(DevAclTable::URI_INDEX);
        }

        $this->renderEdit($this->db->getAclTableById($id) ?? [], false);
    }

    private function renderEdit(array $table, bool $isNew): void
    {
        if (!empty($_SESSION[SessionFields::FORM_DATA][DevAclTable::POST_REC])) {
            $table = array_replace($table, $_SESSION[SessionFields::FORM_DATA][DevAclTable::POST_REC]);
            unset($_SESSION[SessionFields::FORM_DATA][DevAclTable::POST_REC]);
        }

        $title = $isNew
            ? __('Add ACL table | Добавить ACL-таблицу | Додати ACL-таблицю')
            : __('Edit ACL table | Редактировать ACL-таблицу | Редагувати ACL-таблицю');

        $this->setVariables([
            'title' => $title,
            'table' => $table,
            'is_new' => $isNew,
            'record_count' => empty($table[DevAclTable::F_ID]) ? 0 : $this->db->countAclRecordsByTableId((int) $table[DevAclTable::F_ID]),
        ]);
        View::setMeta(title: $title);
        $this->view = 'edit';
    }

    public function saveAction(): void
    {
        if (empty($_POST[DevAclTable::POST_REC]) || !is_array($_POST[DevAclTable::POST_REC])) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No data to save'));
            redirect(DevAclTable::URI_INDEX);
        }

        $row = $this->normalizeTable($_POST[DevAclTable::POST_REC]);
        $id = (int) ($row[DevAclTable::F_ID] ?? 0);

        if ($id > 0) {
            $this->requireEditAccess();
        } else {
            $this->requireAddAccess();
        }

        if (!$this->validateTable($row, $id > 0 ? $id : null)) {
            $_SESSION[SessionFields::FORM_DATA][DevAclTable::POST_REC] = $row;
            redirect($id > 0 ? DevAclTable::URI_EDIT . '/' . $id : DevAclTable::URI_ADD);
        }

        if ($id > 0) {
            if (!$this->db->update_row_by_id(DevAclTable::TABLE, $row, DevAclTable::F_ID)) {
                MsgQueue::msg(MsgType::ERROR, __('Failed to save ACL table | Не удалось сохранить ACL-таблицу | Не вдалося зберегти ACL-таблицю'));
                $_SESSION[SessionFields::FORM_DATA][DevAclTable::POST_REC] = $row;
                redirect(DevAclTable::URI_EDIT . '/' . $id);
            }

            MsgQueue::msg(MsgType::SUCCESS, __('ACL table saved | ACL-таблица сохранена | ACL-таблицю збережено'));
            redirect(DevAclTable::URI_EDIT . '/' . $id);
        }

        $newId = self::createAclTable($row);
        if ($newId === false) {
            MsgQueue::msg(MsgType::ERROR, __('Failed to create ACL table | Не удалось создать ACL-таблицу | Не вдалося створити ACL-таблицю'));
            $_SESSION[SessionFields::FORM_DATA][DevAclTable::POST_REC] = $row;
            redirect(DevAclTable::URI_ADD);
        }

        MsgQueue::msg(MsgType::SUCCESS, __('ACL table created | ACL-таблица создана | ACL-таблицю створено'));
        redirect(DevAclTable::URI_EDIT . '/' . $newId);
    }

    public static function createAclTable(array $row): int|false
    {
        $model = new AclModel();
        unset($row[DevAclTable::F_ID]);
        $row[DevAclTable::F_CREATION_UID] = App::get_user_id();
        $row[DevAclTable::F_CREATION_DATE] = time();
        $row[DevAclTable::F_MODIFIED_UID] = App::get_user_id();
        $row[DevAclTable::F_MODIFIED_DATE] = time();

        return $model->insert_row(DevAclTable::TABLE, $row);
    }

    private function normalizeTable(array $row): array
    {
        return [
            DevAclTable::F_ID => (int) ($row[DevAclTable::F_ID] ?? 0),
            DevAclTable::F_NAME => trim((string) ($row[DevAclTable::F_NAME] ?? '')),
            DevAclTable::F_DESCRIPTION => trim((string) ($row[DevAclTable::F_DESCRIPTION] ?? '')),
        ];
    }

    private function validateTable(array $row, ?int $exceptId = null): bool
    {
        if ($row[DevAclTable::F_NAME] === '') {
            MsgQueue::msg(MsgType::ERROR, __('ACL table name is required | Имя ACL-таблицы обязательно | Імʼя ACL-таблиці обовʼязкове'));
            return false;
        }

        if ($this->db->aclTableNameExists($row[DevAclTable::F_NAME], $exceptId)) {
            MsgQueue::msg(MsgType::ERROR, __('ACL table with this name already exists | ACL-таблица с таким именем уже существует | ACL-таблиця з таким імʼям вже існує'));
            return false;
        }

        return true;
    }

    public function deleteAction(): void
    {
        $this->requireDeleteAccess();

        $id = (int) ($this->route[F_ALIAS] ?? 0);
        $table = $this->db->getAclTableById($id);
        if (empty($table)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('ACL table not found'));
            redirect(DevAclTable::URI_INDEX);
        }

        if ($this->db->countAclRecordsByTableId($id) > 0) {
            MsgQueue::msg(MsgType::ERROR, __('ACL table contains records. Clear it first | ACL-таблица содержит записи. Сначала очистите список | ACL-таблиця містить записи. Спочатку очистіть список'));
            redirect(DevAclTable::URI_EDIT . '/' . $id);
        }

        if (!$this->db->deleteAclTable($id)) {
            MsgQueue::msg(MsgType::ERROR, __('Failed to delete ACL table | Не удалось удалить ACL-таблицу | Не вдалося видалити ACL-таблицю'));
            redirect(DevAclTable::URI_EDIT . '/' . $id);
        }

        MsgQueue::msg(MsgType::SUCCESS, __('ACL table deleted | ACL-таблица удалена | ACL-таблицю видалено'));
        redirect(DevAclTable::URI_INDEX);
    }
}
