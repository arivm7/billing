<?php
/*
 *  Project : my.ri.net.ua
 *  File    : LogController.php
 *  Path    : app/controllers/LogController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 14 Apr 2026
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

declare(strict_types=1);

namespace app\controllers;

use billing\core\App;
use billing\core\base\View;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\Auth;
use config\tables\Module;

/**
 * Контроллер просмотра лог-файлов приложения.
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class LogController extends AppBaseController {


    /**
     * Возвращает список файлов из каталога логов без подкаталогов.
     */
    private static function getListFiles(): array {
        if (!is_dir(DIR_LOG) || !is_readable(DIR_LOG)) {
            return [];
        }

        $listFiles = [];
        $dir = opendir(DIR_LOG);

        if ($dir === false) {
            return [];
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = DIR_LOG . '/' . $file;
            if (is_file($fullPath)) {
                $listFiles[] = $file;
            }
        }

        closedir($dir);
        return $listFiles;
    }



    /**
     * Проверяет, что имя файла соответствует формату base.log или base.log.NNN.
     */
    private static function isValidLogFileName(string $fileName): bool {
        if ($fileName === '' || basename($fileName) !== $fileName) {
            return false;
        }

        return (bool) preg_match('/^[^\/\\\\]+\.log(?:\.\d{3})?$/', $fileName);
    }



    /**
     * Возвращает базовое имя лога для файла base.log или base.log.NNN.
     */
    private static function getBaseLogName(string $fileName): ?string {
        if (preg_match('/^(.+\.log)\.(\d{3})$/', $fileName, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^.+\.log$/', $fileName) === 1) {
            return $fileName;
        }

        return null;
    }



    /**
     * Возвращает номер ротации для base.log.NNN либо null для базового файла.
     */
    private static function getRotationNumber(string $fileName): ?int {
        if (preg_match('/\.([0-9]{3})$/', $fileName, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }


    /**
     * Возвращает метаданные файла из каталога логов.
     */
    private static function getLogFileMeta(string $fileName): array {
        $fullPath = DIR_LOG . '/' . $fileName;

        return [
            'file_name' => $fileName,
            'size' => is_file($fullPath) ? (int) filesize($fullPath) : 0,
            'mtime' => is_file($fullPath) ? (int) filemtime($fullPath) : 0,
        ];
    }


    /**
     * Группирует лог-файлы: базовый лог и список его архивов.
     */
    private static function groupLogFiles(array $listFiles): array {
        $logs = [];

        foreach ($listFiles as $fileName) {
            if (!self::isValidLogFileName($fileName)) {
                continue;
            }

            $baseFile = self::getBaseLogName($fileName);
            if ($baseFile === null) {
                continue;
            }

            if (!isset($logs[$baseFile])) {
                $baseMeta = self::getLogFileMeta($baseFile);
                $logs[$baseFile] = [
                    'base_file' => $baseMeta['file_name'],
                    'size' => $baseMeta['size'],
                    'mtime' => $baseMeta['mtime'],
                    'archives' => [],
                ];
            }

            if ($fileName !== $baseFile) {
                $archiveMeta = self::getLogFileMeta($fileName);
                $logs[$baseFile]['archives'][] = [
                    'file_name' => $archiveMeta['file_name'],
                    'size' => $archiveMeta['size'],
                    'mtime' => $archiveMeta['mtime'],
                    'rotation' => self::getRotationNumber($fileName),
                ];
            }
        }

        ksort($logs, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($logs as &$logGroup) {
            usort(
                $logGroup['archives'],
                static fn(array $left, array $right): int => ($left['rotation'] ?? 0) <=> ($right['rotation'] ?? 0)
            );
        }
        unset($logGroup);

        return array_values($logs);
    }


    /**
     * Возвращает полный путь к лог-файлу внутри DIR_LOG или null.
     */
    private static function resolveLogFilePath(string $fileName): ?string {
        if (!self::isValidLogFileName($fileName)) {
            return null;
        }

        $logDirRealPath = realpath(DIR_LOG);
        if ($logDirRealPath === false) {
            return null;
        }

        $fullPath = $logDirRealPath . DIRECTORY_SEPARATOR . $fileName;
        $fileRealPath = realpath($fullPath);

        if ($fileRealPath === false || !is_file($fileRealPath) || !is_readable($fileRealPath)) {
            return null;
        }

        $prefix = $logDirRealPath . DIRECTORY_SEPARATOR;
        if (strncmp($fileRealPath, $prefix, strlen($prefix)) !== 0) {
            return null;
        }

        return $fileRealPath;
    }


    public function indexAction(): void {

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_LOGS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            redirect();
        }


        $title = __('List of log files | Список лог-файлов | Список лог-файлів');
        $logs = self::groupLogFiles(self::getListFiles());

        $this->setVariables([
            'title' => $title,
            'logs' => $logs,
        ]);

        View::setMeta(
            title: $title,
        );
    }


    public function viewAction(): void {

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in | Авторизуйтесь, пожалуйста | Авторизуйтесь, будь ласка'));
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_LOGS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights | Нет прав | Немає прав'));
            redirect();
        }

        $fileName = isset($_GET['file']) ? trim((string) $_GET['file']) : '';
        $fullPath = self::resolveLogFilePath($fileName);

        if ($fullPath === null) {
            redirect();
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            redirect();
        }

        $title = __('Viewing the log file | Просмотр лог-файла | Перегляд лог-файлу') . ' :: ' . $fileName;

        $this->setVariables([
            'title' => $title,
            'file_name' => $fileName,
            'content' => $content,
        ]);

        View::setMeta(
            title: $title,
        );
    }

}
