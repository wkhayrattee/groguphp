<?php
/**
 * Heart of the custom exception handlers
 *
 * @throws Exception
 */
function registerCustomExceptionHandling(): void
{
    if (!defined('ROOT_FOLDER')) {
        throw new \Exception('Grogu: ROOT_FOLDER is not defined');
    }
    register_shutdown_function('fatalErrorShutdownHandler');
    set_error_handler('myErrorHandler', E_ALL);
    set_exception_handler('myExceptionHandler');
}

// Misc
function fatalErrorShutdownHandler(): void
{
    //I observe that this is called also when calling die();
    //silence for now in favor for below myErrorHandler
}
function myErrorHandler($errno, $errstr): void
{
    myExceptionHandler(new \Exception($errstr, $errno));
}
function myExceptionHandler($error): void
{
    $string_to_save = "\r\n" . '## |' . date('Y-m-d H:i:s') . '| ' . print_r($error, true);
    handle_error_content($string_to_save);
    showStaticErrorPage();
}
function handle_error_content($error_content_to_save): void
{
    $fullPathToFileName = APP_FOLDER . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'runtime_error.log';
    $handle = @fopen($fullPathToFileName, 'a+');
    if ($handle === false) {
        //silence is golden
    } else {
        @fwrite($handle, $error_content_to_save . "\r\n" . "\r\n");
        fclose($handle);
    }
}
function showStaticErrorPage(): void
{
    ob_end_clean();
    echo file_get_contents(APP_FOLDER . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '/error.html');
    exit;
}
