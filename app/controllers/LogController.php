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
use app\models\AbonModel;
use config\tables\Abon;



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
        // разрешено только "чистое имя файла", без директорий
        if ($fileName === '' || basename($fileName) !== $fileName) {
            return false;
        }
        // [^\/\\\\]+ -- один или более символов, кроме '/' и '\' -- защита от путей
        // имя.log
        // имя.log.NNN
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
    
    

    /**
     * Заменяет номера договоров в строке на ссылки
     *
     * @param string $line
     * @param object $model должен иметь метод validate_abon(int $id): bool
     * @return string
     */
    public static function replace_abon_links(string $line, $word_vidider = ' '): string {

        $model = new AbonModel;
        
        if (empty($line)) { return $line; }

        $words = explode($word_vidider, $line);
        
        $min_digit = App::get_config('port_min_digits');
        $max_digit = App::get_config('port_max_digits');

        foreach ($words as &$word) {

            // только цифры
            if (!preg_match('/^\d{'.$min_digit.','.$max_digit.'}$/', $word)) {
                continue;
            }

            $abon_id = (int)$word;

            // проверка валидности договора
            if (!$model->validate_abon($abon_id)) {
                continue;
            }

            // замена на ссылку
            $word = sprintf(
                '<a href="'.Abon::URI_VIEW.'/%s" target="_blank" title="%s">%s</a>',
                $abon_id,
                h(__abon($abon_id, field: Abon::F_ADDRESS)),
                $abon_id
            );
        }

        unset($word);

        return implode($word_vidider, $words);
    }    

    
    
    public static function highlite_text($text): string {
        return str_replace(
                [
                    '| <', 
                    '| «', 
                    '|&nbsp;<',
                    '|&nbsp;«',
                ], 
                [
                    '| <span class="text-primary">«</span>', 
                    '| <span class="text-primary">«</span>', 
                    '|&nbsp;<span class="text-primary">«</span>',
                    '|&nbsp;<span class="text-primary">«</span>',
                ], $text);
    }



    public function indexAction(): void {

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_LOGS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
            self::log_no_rights();
            redirect();
        }


        $title = __('List of log files');
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
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_use(Module::MOD_LOGS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights'));
            self::log_no_rights();
            redirect();
        }

        $fileName = isset($_GET['file']) ? trim((string) $_GET['file']) : '';
        $fullPath = self::resolveLogFilePath($fileName);

        if ($fullPath === null) {
            redirect();
        }

        
        $count_lines = 0;
        $content = '';

        $fh = fopen($fullPath, 'r');
        if ($fh === false) {
            redirect();
        }

        while (($line = fgets($fh)) !== false) {
            $count_lines++;

            // тут позже можно вставлять HTML-обогащение
            // например: замена договоров на ссылки
            $line = str_replace(' ', '&nbsp;', $line);
            if (!preg_match('/^errors\.log(\.\d+)?$/', $fileName)) {
                $line = self::highlite_text($line);
                $line = self::replace_abon_links($line, '&nbsp;');
            }
            
            $content .= $line . '<br>';
        }

        fclose($fh);        
        
        
//        $content = file_get_contents($fullPath);
//        if ($content === false) {
//            redirect();
//        }

        $title = __('Viewing the log file') . ' :: ' . $fileName;

        $this->setVariables([
            'title' => $title,
            'file_name' => $fileName,
            'content' => $content,
            'count_lines' => $count_lines,
        ]);

        View::setMeta(
            title: $title,
        );
    }


    public function deleteAction(): void {

        if (!App::isAuth()) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('Please log in'));
            self::log_unauthorize();
            redirect(Auth::URI_LOGIN);
        }

        if (!can_del(Module::MOD_LOGS)) {
            MsgQueue::msg(MsgType::ERROR_AUTO, __('No rights to delete'));
            self::log_no_rights();
            redirect();
        }

        $fileName = isset($_GET['file']) ? trim((string) $_GET['file']) : '';
        $fullPath = self::resolveLogFilePath($fileName);

        if ($fullPath === null) {
            MsgQueue::msg(MsgType::ERROR, __('Log file not found'));
            redirect('/log');
        }

        if (@unlink($fullPath)) {
            MsgQueue::msg(MsgType::SUCCESS, __('Log file deleted') . ': ' . $fileName);
        } else {
            MsgQueue::msg(MsgType::ERROR, __('Failed to delete log file') . ': ' . $fileName);
        }

        redirect('/log');
    }

}
