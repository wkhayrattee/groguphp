<?php
/**
 * Controller Trait
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 * @github @wkhayrattee
 */

namespace Groguphp;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ControllerTrait
{
    public Container $container;
    public TemplateInterface $tpl;
    public Response $response;
    public Request $request;

    protected array $url_bag;
    protected array $parent_tpl_vars = [];
    protected string $cache_nonce;

    protected array $error_list;

    protected string $url_domain;
    protected string $url_page;
    protected string $page_uri;

    public function __construct(Container $container, array $urlParameterBag, string $page_uri)
    {
        $this->error_list = [];
        $this->url_bag = $urlParameterBag;
        $this->tpl = $container['tpl'];
        $this->container = $container;
        $this->request = $container['request'];

        $this->url_domain = $container['config']['site.scheme'] . '://' . $container['config']['site.domain'];
        $this->url_page = $this->url_domain . $page_uri;
        $this->page_uri = rtrim($page_uri, '/');

        $this->tpl->page_uri = $this->page_uri;
        $this->tpl->url_page = $this->url_page;
        $this->tpl->url_domain = $this->url_domain;

        $this->cache_nonce = $this->container['config']['cache.nonce'];

        $this->response = new Response();
    }
}
