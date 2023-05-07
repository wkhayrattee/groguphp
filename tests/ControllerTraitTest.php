<?php

use Groguphp\ControllerTrait;
use Groguphp\TemplateInterface;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

test('The controller trait initializes correctly', function () {
    $controller = new class(getContainer(), [], '/') {
        use ControllerTrait;
    };

    expect($controller->request)->toBeInstanceOf(Request::class);
    expect($controller->response)->toBeInstanceOf(Response::class);
    expect($controller->tpl)->toBeInstanceOf(TemplateInterface::class);
    expect($controller->container)->toBeInstanceOf(Container::class);
});
