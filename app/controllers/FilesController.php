<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : FilesController.php
 *  Path    : app/controllers/FilesController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



namespace app\controllers;


use billing\core\base\Lang;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;

use config\MimeTypes;
use Exception;
use app\models\FileModel;
use config\SessionFields;
use config\tables\User;
use Valitron\Validator;
use config\tables\File;
use billing\core\App;


/**
 * Description of FilesController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class FilesController extends AppBaseController
{



    function validate(array $post_rec, array $file_file): array {
        $v = new Validator($post_rec);
        $v->rule('required', [File::F_USER_ID, File::F_IS_PUBLIC, File::F_SUB_TITLE]);
        $v->rule('integer', File::F_USER_ID);
        $v->rule('in', File::F_IS_PUBLIC, [0,1]);
        $v->rule('in', File::F_SUB_TITLE, array_keys(File::SUB_DIRS));

        $model = new FileModel();

        $errors = [];

        // --- проверки файла ---
        if ($file_file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Ошибка загрузки файла';
        } else {
            $allowedMime = MimeTypes::get_mime_types();
            $allowedExt  = MimeTypes::get_ext_list();

            $mime = mime_content_type($file_file['tmp_name']);
            $ext  = strtolower(pathinfo($file_file['name'], PATHINFO_EXTENSION));

            if (!$model->validate_id(table_name: User::TABLE, field_id: User::F_ID, id_value: $post_rec[File::F_USER_ID])) {
                $errors[] = "Не верный ID пользователя";
            }
            if (!in_array($mime, $allowedMime)) {
                $errors[] = "Недопустимый MIME-тип: $mime";
            }
            if (!in_array($ext, $allowedExt)) {
                $errors[] = "Недопустимое расширение: .$ext";
            }
            $max_file_size = App::$app->get_config('files_upload_max_filesize');
            if ($file_file['size'] > $max_file_size) {
                $errors[] = __("Файл слишком большой (макс. %01.2f МБ)", round($max_file_size/1024/1024, 2));
            }
        }

        if (!$v->validate()) {
            $errors = array_merge($errors, $v->errors());
        }

        return $errors;
    }



    /**
     * Перемещает файл из одной папки в другую
     *
     * @param string $from Полный путь к исходному файлу
     * @param string $to   Полный путь к файлу назначения
     * @param bool   $overwrite Разрешить перезапись, если файл уже существует
     * @return bool true при успехе, false при ошибке
     */
    function move_file(string $from, string $to, bool $overwrite = false): bool
    {
        // Проверяем, существует ли исходный файл
        if (!file_exists($from)) {
            MsgQueue::msg(MsgType::ERROR, 'Ошибка копирования: Исходного файла нет');
            return false;
        }

        // Проверяем, существует ли целевая папка
        $targetDir = dirname($to);
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                MsgQueue::msg(MsgType::ERROR, 'Ошибка копирования: не удалось создать папку');
                return false; // не удалось создать папку
            }
        }

        // Если файл уже существует
        if (file_exists($to)) {
            if ($overwrite) {
                MsgQueue::msg(MsgType::INFO, 'Файл назначения существует. Пробуем удалить.');
                unlink($to); // удаляем старый
                MsgQueue::msg(MsgType::INFO, 'Удалили.');
            } else {
                MsgQueue::msg(MsgType::ERROR, 'Ошибка копирования: не разрешено перезаписывать');
                return false; // не разрешено перезаписывать
            }
        }

        // Пытаемся переместить
        return rename($from, $to);
    }



    /**
     * Загрузка отправленного файла в нужную папку и возврат этот папки
     * @param array $post_file -- массив состояний отправленного файла
     * @param string $sub_title -- Имя подгруппы для публичных файлов
     * @param bool $is_public
     * @return string|null -- имя сохранённого файла на диске.
     */
    public function upload(array $post_file, string $sub_title = File::TITLE_DEFAULT, bool $is_public = true, bool $auto_mkdir = true): ?string
    {
        if ($post_file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = pathinfo($post_file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('', true) . '.' . $ext;

        $target_dir = File::get_abs_dir(
                        [
                            File::F_IS_PUBLIC => $is_public,
                            File::F_SUB_TITLE => $sub_title
                        ]);

        if ($auto_mkdir && !is_dir($target_dir)) {
            MsgQueue::msg(MsgType::INFO, "Папки назначения [{$target_dir}] нет.");
            MsgQueue::msg(MsgType::INFO, "Пробую создать");
            if (mkdir($target_dir, 0777, true)) {
                MsgQueue::msg(MsgType::INFO, "OK: Папка создана успешно");
            } else {
                MsgQueue::msg(MsgType::INFO, "ERROR: Не удалось создать папку");
                redirect();
            }
        }

        $target_pathname = $target_dir . '/' . $new_name;
        if (move_uploaded_file($post_file['tmp_name'], $target_pathname)) {
            return $new_name;
        }
        return null;
    }



    public function edit() {

        $model = new FileModel();
        $post_rec = $_POST[File::POST_REC];
        $file = $model->get_file($post_rec[File::F_ID]);
        $row[File::F_ID] = (int)$post_rec[File::F_ID];
        $row[File::F_READONLY] = (int)($post_rec[File::F_READONLY] ?? 0);

        $do_move_file = false;


        if ($post_rec[File::F_ORIGINAL_NAME] != $file[File::F_ORIGINAL_NAME]) {
            $row[File::F_ORIGINAL_NAME] = $post_rec[File::F_ORIGINAL_NAME];
        }
        if ((int) $post_rec[File::F_IS_PUBLIC] != $file[File::F_IS_PUBLIC]) {
            $row[File::F_IS_PUBLIC] = (int)$post_rec[File::F_IS_PUBLIC];
            $do_move_file = true;
        }
        if ($post_rec[File::F_SUB_TITLE] != $file[File::F_SUB_TITLE]) {
            $row[File::F_SUB_TITLE] = $post_rec[File::F_SUB_TITLE];
            if ($file[File::F_IS_PUBLIC]) {
                $do_move_file = true;
            }
        }
        foreach (File::F_DESCRIPTION as $f_descr) {
            if ($post_rec[$f_descr] != $file[$f_descr]) {
                $row[$f_descr] = $post_rec[$f_descr];
            }
        }

        /**
         * Нужно перемещать файл на диске
         */
        if ($do_move_file) {
            $from = File::get_abs_pathname($file);
            $to   = File::get_abs_pathname([
                File::F_STORED_NAME => $file[File::F_STORED_NAME],
                File::F_IS_PUBLIC => (int)$post_rec[File::F_IS_PUBLIC],
                File::F_SUB_TITLE => $post_rec[File::F_SUB_TITLE],
            ]);
            MsgQueue::msg(MsgType::INFO, 'Требуется перемещене файла на диске');
            MsgQueue::msg(MsgType::INFO, 'из: ' . $from);
            MsgQueue::msg(MsgType::INFO, 'в : ' . $to);
            if ($this->move_file($from, $to, overwrite: 1)) {
                MsgQueue::msg(MsgType::INFO, 'Успешно');
            } else {
                MsgQueue::msg(MsgType::INFO, 'Перемещение не удалось');
                MsgQueue::msg(MsgType::ERROR, 'Требовалось перемещение файла. Перемещение не удалось');
                $_SESSION[SessionFields::FORM_DATA] = $post_rec;
                redirect(File::URI_UPLOAD);
            }
        }

        /**
         * Сохранение в БД
         */
        if ($model->update_row_by_id(table: File::TABLE, row: $row, field_id: File::F_ID)) {
            MsgQueue::msg(MsgType::SUCCESS, 'Правки внесены');
            redirect(File::URI_INDEX);
        } else {
            MsgQueue::msg(MsgType::ERROR, 'Ошибка внесения данных');
            $_SESSION[SessionFields::FORM_DATA] = $post_rec;
            redirect(File::URI_UPLOAD);
        }
    }



    protected function check_access(array $file): bool
    {
        return true;
    }



    protected function can_delete(array $file): bool
    {
        return true;
    }



    /**
     * Список файлов (мои + публичные).
     */
    public function indexAction()
    {
        $user_id = $_SESSION[User::SESSION_USER_REC][User::F_ID] ?? null; // получаем текущего пользователя
        $model = new FileModel();
        // мои файлы
        $my_files = $model->get_files_by_user(user_id: $user_id, is_public: 0);
        // публичные файлы
        $public_files = $model->get_files_by_user(user_id: $user_id, is_public: 1);

        $this->setVariables([
            'my_files'   => $my_files,
            'pub_files'  => $public_files,
        ]);

        View::setMeta(
            title: __("Управление файлами"),
        );
    }



    public function getAction(): string
    {
        $file_id = (int)($this->route[F_ALIAS] ?? 0);
        $model = new FileModel();
        $file = $model->get_file($file_id);

        if (!$file) {
            if (App::$app->error_handler::DEBUG) {
                debug($file, '[1] getAction: _FILE:');
                throw new \Exception(code: 404, message: 'File not registered');
            }
            return '';
        }

        // Проверка доступа (если нужно)
        if (!$this->check_access($file)) {
            if (App::$app->error_handler::DEBUG) {
                debug($file, '[2] getAction: _FILE:');
                throw new \Exception(code: 403, message: 'Access denied');
            }
            return '';
        }

        $filepath = File::get_abs_pathname($file);
        if (!is_file($filepath)) {
            if (App::$app->error_handler::DEBUG) {
                debug($file, '[3] getAction: _FILE:');
                throw new \Exception(code: 404, message: "File not found on storage {$filepath}");
            }
            return '';
        }

        // Отдаём
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file[File::F_MIME]);
        header('Content-Length: ' . filesize($filepath));
        // inline — «если можешь, открой внутри себя».
        // attachment — «скачай обязательно».
        header('Content-Disposition: inline; filename="' . basename($file[File::F_ORIGINAL_NAME]) . '"');
        readfile($filepath);
        exit;
    }



    public function uploadAction()
    {
        $model = new FileModel();

        if (isset($_POST[File::POST_REC]) && is_array($_POST[File::POST_REC])) {

            /*
             * редактироване
             */
            $is_edit = isset($_POST[File::POST_REC][File::F_ID]);
            if ($is_edit) {
                $this->edit();
            }



            $post_rec = $_POST[File::POST_REC];
            $post_file = $_FILES[File::F_ORIGINAL_NAME] ?? null;
            $errors = $this->validate($post_rec, $post_file);
            if (!empty($errors)) {
                // показать форму снова
                MsgQueue::msg(MsgType::ERROR, $errors);
                $_SESSION[SessionFields::FORM_DATA] = $post_rec;
                redirect(File::URI_UPLOAD);
            }

            $is_public = (int)$post_rec[File::F_IS_PUBLIC] === 1;
            $sub_title = $post_rec[File::F_SUB_TITLE];

            $stored_name = $this->upload(post_file: $post_file, sub_title: $sub_title, is_public: $is_public);
            if (!$stored_name) {
                $errors[] = __('Не удалось сохранить файл');
                // $errors[] = "is_public = {$is_public}";
                // $errors[] = "sub_dir = {$sub_title}";
                // $errors[] = "DIR_PUBLIC = " . File::DIR_PUBLIC;
                // $errors[] = "DIR_PRIVATE = " . File::DIR_PRIVATE;
                $errors[] = "_FILE:";
                $errors[] = print_r(array_filter($post_file), true);
                MsgQueue::msg(MsgType::ERROR, $errors);
                $_SESSION[SessionFields::FORM_DATA] = $post_rec;
                redirect(File::URI_UPLOAD);
            }

            // Сохранение в БД
            $row = [
                File::F_USER_ID        => (int)$post_rec[File::F_USER_ID],
                File::F_ORIGINAL_NAME  => $post_file['name'],
                File::F_STORED_NAME    => $stored_name,
                File::F_SUB_TITLE      => $sub_title,
                File::F_IS_PUBLIC      => (int)$is_public,
                File::F_MIME           => MimeTypes::get_mime_type(pathinfo($post_file['name'], PATHINFO_EXTENSION)),
                File::F_SIZE           => $post_file['size'],
                File::F_UK_DESCRIPTION => $post_rec[File::F_UK_DESCRIPTION] ?? null,
                File::F_RU_DESCRIPTION => $post_rec[File::F_RU_DESCRIPTION] ?? null,
                File::F_EN_DESCRIPTION => $post_rec[File::F_EN_DESCRIPTION] ?? null,
            ];


            if ($model->insert_row(table: File::TABLE, row: $row)) {
                MsgQueue::msg(MsgType::SUCCESS, ["Файл добавлен", array_filter($row)]);
                redirect(File::URI_INDEX);
            } else  {
                MsgQueue::msg(MsgType::ERROR, ["Ошибка добавления файла", array_filter($row)]);
                $_SESSION[SessionFields::FORM_DATA] = $post_rec;
                redirect(File::URI_UPLOAD);
            }
        }

        if (isset($_GET[File::F_GET_ID]) && $model->validate_id(table_name: File::TABLE, field_id: File::F_ID, id_value: (int)$_GET[File::F_GET_ID])) {

            $file = $model->get_file((int)$_GET[File::F_GET_ID]);

            $this->setVariables([
                'file'   => $file,
            ]);

            View::setMeta(
                title: __("Редактирование параметров загруженного файла"),
            );
            return;
        }

        View::setMeta(
            title: __("Отправка файла на сервер"),
        );


    }



    public function deleteAction(): void
    {
        $file_id = (int)($_GET[File::F_GET_ID] ?? 0);
        $model = new FileModel();
        $file = $model->get_file($file_id);

        if (!$file_id) {
            MsgQueue::msg(MsgType::ERROR, "ID не указан");
            redirect(File::URI_INDEX);
        }

        $file = $model->get_file($file_id);
        if (!$file) {
            MsgQueue::msg(MsgType::ERROR, ["Файл не зарегистрирован ", "или некорректный ID. "]);
            redirect(File::URI_INDEX);
        }

        // Проверка прав
        if (!$this->can_delete($file)) {
            MsgQueue::msg(MsgType::ERROR, ["У вас нет прав на удаление этого файла"]);
            redirect(File::URI_INDEX);
        }

        // Формируем путь
        $path = File::get_abs_pathname($file);

        // Удаляем физический файл
        if (is_file($path)) {
            if (@unlink($path)) {
                MsgQueue::msg(MsgType::SUCCESS, "Файл {$path} успешно удалён");
            } else {
                MsgQueue::msg(MsgType::ERROR, "Ошибка удаления файла {$path}.");
            }
        } else {
            MsgQueue::msg(MsgType::ERROR, "Файла {$path} нет, удалять нечгео.");
        }

        // Удаляем запись из БД
        if ($model->delete_rows_by_field(table: File::TABLE, field_id: File::F_ID, value_id: $file_id)) {
            MsgQueue::msg(MsgType::SUCCESS, "Запись {$file_id} в базе удалена.");
        } else {
            MsgQueue::msg(MsgType::ERROR, "Ошибка удаления записи {$file_id} из базы.");
        }
        redirect(File::URI_INDEX);
    }


}