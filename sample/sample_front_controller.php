<?php
/**
 * This will be the DirectoryIndex file of our app residing at public/
 * a.k.a the central front controller of our app - typically would be named as app.php
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 * @github @wkhayrattee
 */
use Pimple\Container;
use Groguphp\Engine;
use Symfony\Component\HttpFoundation\Request;

/**
 * ***************************************************************************************
 * SET some global defines here
 * ***************************************************************************************
 */
const CACHE_BUST_NONCE = '0.0.0.1';
const CACHE_TTL = 3600; //in secs
const KINT_SKIP_HELPERS = true;

/**
 * ***************************** *
 * Set TIMEZONE
 * Coordinated Universal Time SAME as Greenwich Mean Time (GMT)
 * ***************************** *
 */
date_default_timezone_set('UTC');

/**
 * ***************************** *
 * Some global variables
 * ***************************** *
 */
global $appContainer;
$vendor_autoloader = '../../vendor/autoload.php';

/**
 * ***************************** *
 * Primary DEFINES
 * ***************************** *
 */
define('PUBLIC_FOLDER', __DIR__ . DIRECTORY_SEPARATOR);
define('APP_FOLDER', dirname(PUBLIC_FOLDER, 2) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
define('ROOT_FOLDER', dirname(APP_FOLDER) . DIRECTORY_SEPARATOR);

/**
 * ***************************************************************************************
 * Fetching Composer Autoloader
 * ***************************************************************************************
 */
if (file_exists($vendor_autoloader)) {
    require_once $vendor_autoloader;
} else {
    die('Grogu: I cannot start the engine without the keypad');
}

/**
 * ***************************************************************************************
 * Early error handling
 * ***************************************************************************************
 */
registerCustomExceptionHandling();

/**
 * ***************************************************************************************
 * Our Custom Configs Starts here
 * ***************************************************************************************
 */
try {
    /**
     * ***************************** *
     * load .env
     * ***************************** *
     */
    Engine::deflateEnvFile();

    /**
     * ***************************** *
     * Secondary DEFINES
     * ***************************** *
     */
    Engine::initDefines();

    /**
     * ***************************** *
     * load PHP Env file
     * ***************************** *
     */
    Engine::loadPHPEnvironmentFile();

    /**
     * ***************************** *
     * HTTPS & DOMAIN settings
     * ***************************** *
     */
    Engine::doHttpScheme();

    /**
     * ***************************************** *
     * Reconcile all params for final Container
     * ***************************************** *
     */
    $appContainer = new Container(['config' => include_once CONFIG_FOLDER . 'defines.php']);
    $appContainer['routes'] = include_once CONFIG_FOLDER . 'routes.php';
    $appContainer['request'] = Request::createFromGlobals();

    /**
     * ***************************** *
     * Services container
     * ***************************** *
     */
    include_once CONFIG_FOLDER . 'services.php';

    /**
     * ***************************** *
     * Call our custom micro framework
     * ***************************** *
     */
    $app = new Engine($appContainer);
    $app->start();
} catch (Exception $error) {
    myExceptionHandler($error);
    showStaticErrorPage();
}
