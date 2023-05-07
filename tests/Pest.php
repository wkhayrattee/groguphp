<?php

use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use Groguphp\TemplateInterface;
use Groguphp\TemplateTrait;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function getContainer(): Container
{
    $container = new Container();
    $container['request'] = Request::createFromGlobals();

    // Define a mock configuration array
    $container['config'] = [
        'site.scheme' => 'https',
        'site.domain' => 'example.com',
        'cache.nonce' => 'dummy_nonce',
    ];

    // Set a mock or a dummy implementation of the TemplateInterface for testing purposes.
    $container['tpl'] = new class implements TemplateInterface {
        use TemplateTrait;

        public string $page_uri;
        public string $url_page;
        public string $url_domain;

        public function setTemplate(string $tpl_name)
        {
            // TODO: Implement setTemplate() method.
        }

        public function getOutput(array $option_list)
        {
            // TODO: Implement getOutput() method.
        }
    };

    // Set the 'routes' container service.
    $container['routes'] = function () {
        if (filter_var(getenv('TEST_UNHANDLED_DISPATCHER'), FILTER_VALIDATE_BOOLEAN)) {
            return new class extends GroupCountBased {
                public function __construct()
                {
                    parent::__construct([[], []]); // Pass an array with two empty arrays
                }

                public function dispatch($httpMethod, $uri)
                {
                    return [999, null, null]; // Return an unhandled case (999)
                }
            };
        } else {
            return FastRoute\cachedDispatcher(function (RouteCollector $r) {
                $r->addRoute(['GET'], '/home', [MockIndexController::class, '__invoke']);
                $r->addRoute(['GET'], '/user/{id:\d+}', [MockUserController::class, '__invoke']);
                $r->addRoute(['POST'], '/some-route', ['Controller\SomeController', 'someMethod']);
            }, [
                'cacheFile' => './route.cache', // required
                'cacheDisabled' => true,
            ]);
        }
    };

    return $container;
}

class MockIndexController
{
    protected Container $container;
    protected array $url_bag;
    protected string $page_uri;

    public function __construct($container, $urlParameterBag, $pageUri)
    {
        $this->container = $container;
        $this->url_bag = $urlParameterBag;
        $this->page_uri = $pageUri;
    }

    public function __invoke(): Response
    {
        return new Response('Hello, Index!', 200);
    }
}

class MockUserController
{
    protected Container $container;
    protected array $url_bag;
    protected string $page_uri;

    public function __construct($container, $urlParameterBag, $pageUri)
    {
        $this->container = $container;
        $this->url_bag = $urlParameterBag;
        $this->page_uri = $pageUri;
    }

    public function __invoke(): Response
    {
        $userId = $this->url_bag['id'];

        return new Response("User ID: {$userId}", 200);
    }
}
