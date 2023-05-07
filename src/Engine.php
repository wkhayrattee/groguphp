<?php
/**
 * The Engine of our system
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 * @github @wkhayrattee
 */

namespace Groguphp;

use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use Exception;
use FastRoute\Dispatcher;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MyFramework
 */
class Engine implements HttpKernelInterface
{
    private Container $container;
    private TemplateInterface $tpl;

    /**
     * The below is from HttpKernelInterface
     */
    public const MAIN_REQUEST = 1;
    public const SUB_REQUEST = 2;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->tpl = $container['tpl'];
    }

    /**
     * @param Request $request
     * @param int $requestType
     * @param bool $catchException
     *
     * @return Response
     *
     * ref (v5): https://symfony.com/doc/current/create_framework/http_kernel_httpkernelinterface.html
     * also: https://symfony.com/doc/current/components/http_kernel.html
     */
    public function handle(Request $request, int $requestType = self::MAIN_REQUEST, bool $catchException = true): Response
    {
        $response = new Response();
        $dispatcher = $this->container['routes'];
        $path_info = mb_strtolower($request->getPathInfo(), 'UTF-8'); //Utility::strtolower($request->getPathInfo());
        $route_info = $dispatcher->dispatch($request->getMethod(), $path_info);
        switch ($route_info[0]) {
            case Dispatcher::NOT_FOUND:
                if (getenv('TEST_UNHANDLED_DISPATCHER') === 'true') {
                    $response = new Response('Grogu: 502 | Not found.', 502);
                } else {
                    $response = new Response('Grogu: 404 | Not found.', 404);
                }
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowed_methods = $route_info[1];
                $response = new Response('Grogu: 405 Method Not Allowed', 405);
                break;

            case Dispatcher::FOUND:
                $handler = $route_info[1];
                $vars = $route_info[2];
                $object = new $handler[0]($this->container, $vars, $path_info);
                $method_name = $handler[1];
                $this->setTemplate($method_name);
                $response = $object(); // using Action Classes instead of Controller Classes, ref => http://pmjones.io/adr/
                // seems slightly more time consuming than __invoke
//                $response = call_user_func_array([$object, $method_name], $vars);
                break;

            default:
                $response = new Response('Grogu: Something unexpected happen', 502);
        }

        return $response;
    }

    /**
     * @throws Exception
     */
    public function start()
    {
        //Start the engine now
        $appCached = new HttpCache($this, new Store($this->container['config']['folder.cache'] . 'httpcache'));
        $response = $appCached->handle($this->container['request'])->send();
        $appCached->terminate($this->container['request'], $response);
    }

    /**
     * @throws Exception
     */
    public static function deflateEnvFile()
    {
        if (!defined('APP_FOLDER')) {
            throw new Exception('Grogu: constant APP_FOLDER is not defined');
        }
        define('CONFIG_FOLDER', APP_FOLDER . 'config' . DIRECTORY_SEPARATOR);

        if (file_exists(CONFIG_FOLDER . 'env' . DIRECTORY_SEPARATOR . '.env')) {
            try {
                $dotenv = Dotenv::createImmutable(CONFIG_FOLDER . 'env');
                $dotenv->load();
                $dotenv->required('APP_ENV')->allowedValues(['local', 'dev', 'stag', 'prod']);
                $dotenv->required('ENABLE_HTTPS')->allowedValues(['ON', 'OFF']);
                $dotenv->required('IN_MAINTENANCE')->allowedValues(['ON', 'OFF']);
                $dotenv->required('TPL_EXTENSION');
            } catch (ValidationException $error) {
                die($error->getMessage());
                //if we don't handle it, we end up with server error 500, which is extra steps to inspect server log
            }
        } else {
            die('Grogu: there was an error finding the .env file at: ' . CONFIG_FOLDER . 'env');
        }
    }

    /**
     * @throws Exception
     */
    public static function initDefines()
    {
        if (!defined('APP_FOLDER')) {
            throw new Exception('Grogu: constant APP_FOLDER is not defined');
        }
        if (!defined('CONFIG_FOLDER')) {
            throw new Exception('Grogu: constant CONFIG_FOLDER is not defined');
        }

        define('VAR_FOLDER', CONFIG_FOLDER . 'var' . DIRECTORY_SEPARATOR);
        define('CACHE_FOLDER', VAR_FOLDER . 'cache' . DIRECTORY_SEPARATOR);
        define('CORE_FOLDER', VAR_FOLDER . 'Core' . DIRECTORY_SEPARATOR);
        define('LOG_FOLDER', VAR_FOLDER . 'log' . DIRECTORY_SEPARATOR);
        define('TPL_FOLDER', APP_FOLDER . 'templates' . DIRECTORY_SEPARATOR);
        define('THEME_FOLDER', TPL_FOLDER . $_ENV['THEME_NAME'] . DIRECTORY_SEPARATOR);
    }

    public static function ConfigBagParams(): array
    {
        return [
            //Folder path
            'folder.app' => (defined('APP_FOLDER') ? APP_FOLDER : ''),
            'folder.public' => (defined('PUBLIC_FOLDER') ? PUBLIC_FOLDER : ''),
            'folder.config' => (defined('CONFIG_FOLDER') ? CONFIG_FOLDER : ''),
            'folder.view' => (defined('TPL_FOLDER') ? TPL_FOLDER : ''),
            'folder.theme' => (defined('THEME_FOLDER') ? THEME_FOLDER : ''),
            'folder.view.emails' => (defined('TPL_FOLDER') ? TPL_FOLDER : '') . 'email' . DIRECTORY_SEPARATOR,
            'folder.cache' => (defined('CACHE_FOLDER') ? CACHE_FOLDER : ''),
            'folder.log' => (defined('LOG_FOLDER') ? LOG_FOLDER : ''),

            'site.scheme' => SITE_SCHEME,
            'site.domain' => SITE_DOMAIN,

            'cache.nonce' => (defined('CACHE_BUST_NONCE') ? CACHE_BUST_NONCE : ''),
        ];
    }

    /**
     * @throws Exception
     */
    public static function loadPHPEnvironmentFile(): void
    {
        if (!defined('CONFIG_FOLDER')) {
            throw new Exception('Grogu: constant CONFIG_FOLDER is not defined');
        }

        if (file_exists(CONFIG_FOLDER . 'env' . DIRECTORY_SEPARATOR . $_ENV['APP_ENV'] . '.php')) {
            require_once CONFIG_FOLDER . 'env' . DIRECTORY_SEPARATOR . $_ENV['APP_ENV'] . '.php';
        } else {
            die('Grogu: there was an error finding a php ENV file at your /path/to/env/{env}.php');
        }
    }

    public static function doHttpScheme(): void
    {
        $http_scheme = 'http';
        //set HTTPS ON
        if ($_ENV['ENABLE_HTTPS'] == 'ON') {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $_SERVER['HTTP_USER_AGENT_HTTPS'] = 'ON';
            $http_scheme = 'https';
        }
        define('SITE_SCHEME', $http_scheme);
        define('SITE_DOMAIN', trim($_SERVER['HTTP_HOST']));
    }

    /**
     * @param $templateName
     */
    private function setTemplate($templateName): void
    {
        $tpl_extension = $_ENV['TPL_EXTENSION'] ?? '.tpl.php';
        $this->tpl->setTemplate(trim($templateName) . trim($tpl_extension));
    }
}
