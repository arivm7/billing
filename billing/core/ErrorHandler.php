<?php



namespace billing\core;

use Throwable;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;





class ErrorHandler {

    public const DEBUG                  = 1;            // Выводить ошибки на страницу: 1 -- debug | 0 -- Prodaction
    public const DUMP_ECHO              = 1;            // Выводить var_dump() ошибки на страницу
    public const DUMP__TO_LOG_FILE      = 1;            // Выводить var_dump() в лог-файл
    public const ERROR_TO_LOG_FILE      = 1;            // Писать ошибки в лог-файл
    public const LOG_FILENAME           = "errors.log"; // Имя лог-файла
    public const DEFAULT_RESPONSE_CODE  = 500;          // Дефолтное значение кода http-ошибки


    public function __construct() {
        if (self::DEBUG) {
            error_reporting(-1); // E_ALL  -- показывать все ошибки
        } else {
            error_reporting(0);  // E_NONE -- не показывать ошибки
        }
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exeptionHandler']);
        ob_start(); // !!!
        register_shutdown_function([$this, 'fatalErrorHandler']);
    }



    public function errorHandler(
            int $errNo,
            string $errStr,
            ?string $errFile,
            ?int $errLine)
    {

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->log(
            prefix: 'ERROR',
            message: "[{$errNo}] {$errStr}",
            file: $errFile,
            line: $errLine
        );
        if (self::DEBUG || in_array($errNo, [E_USER_ERROR, E_RECOVERABLE_ERROR])) {
                              // errno,        $errstr,         $errfile,          $errline,          $errcontext              $response_code
            $this->displayError( errno: $errNo, errstr: $errStr, errfile: $errFile, errline: $errLine, errcontext: $backtrace);
        }
        // true -- прекратить обработку ошибки
        // false -- продолжить обработку ошибки
        return true;
    }



    public function fatalErrorHandler() {
        $error = error_get_last();
        if (!empty($error) && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
            $this->log(
                prefix: 'FATAL',
                message: "[{$error['type']}] {$error['message']}",
                file: $error['file'],
                line: $error['line'],
                dump: $error);
            ob_end_clean(); // !!!
            //     displayError($errno,               $errstr,                   $errfile,                $errline,                $errcontext,         $response_code)
            $this->displayError(errno: $error['type'], errstr: $error['message'], errfile: $error['file'], errline: $error['line'], errcontext: $error);
        } else {
            ob_end_flush(); // !!!
        }
        return true;
    }



    function exeptionHandler(Throwable $e) { // \Exception \TypeError
        $this->log(
            prefix: 'EXCEPTION',
            message: "[{$e->getCode()}] {$e->getMessage()} <br>\n{$e->getTraceAsString()}",
            file: $e->getFile(),
            line: $e->getLine(),
            dump: $e);
                         // errno,              $errstr,                  $errfile,               $errline,                $errcontext     $response_code
        $this->displayError(errno: 'Исключение', errstr: $e->getMessage(), errfile: $e->getFile(), errline: $e->getLine(), errcontext: $e, response_code: (int)$e->getCode());
    }



    protected function log($prefix = '', $message = '', $file = '', $line = '', mixed $dump = null) {
        if (self::ERROR_TO_LOG_FILE || self::DUMP__TO_LOG_FILE) {
            // create a log channel
            $log = new Logger('name');
            $log->pushHandler(new StreamHandler(DIR_LOG . '/'. self::LOG_FILENAME, Level::Warning));
            // add records to the log
            // $log->warning('Foo');
            // $log->error('Bar');
            // $log->info('My logger is now ready');


            if (self::ERROR_TO_LOG_FILE) {
                $CR = chr(13) . chr(10) . PHP_EOL; // ;
                $msg =  ($prefix ? $prefix . ": " : "")
                        . "Ошибка: {$message} | Файл: {$file} | Стр.: {$line}{$CR}===={$CR}";
                $log->error($msg);
             // error_log(message: "[" . date('Y-m-d H:i:s') . "] " . $msg, message_type: 3, destination: DIR_LOG.'/'.self::LOG_FILENAME);
            }
            if (self::DUMP__TO_LOG_FILE) {
                $msg =  ($prefix ? $prefix . ": " : "")
                        . "DUMP: \n".var_dump_ret($dump)."\n====\n";
//                $log->error($msg);
                error_log(message: "[" . date('Y-m-d H:i:s') . "] " . $msg, message_type: 3, destination: DIR_LOG.'/'.self::LOG_FILENAME);
            }
        }
    }



    protected function displayError(
            int|string $errno,
            string $errstr,
            string $errfile,
            int $errline,
            mixed $errcontext = null,
            int $response_code = self::DEFAULT_RESPONSE_CODE) {

        /* errno  https://www.php.net/manual/ru/errorfunc.constants.php */

//        debug($response_code, comment: 'RESPONSE CODE');

        http_response_code($response_code);
        if ($response_code == 404 && !self::DEBUG ) {
            require DIR_VIEWS . '/Errors/404.html';
        }
        if (self::DEBUG) {
            require DIR_VIEWS . '/Errors/errorDevView.php'; // Использует перменные: $errno, $errstr, $errfile, $errline.
        } else {
            require DIR_VIEWS . '/Errors/errorProdView.php'; // НЕ использует перменные
        }
        die;
    }



}
