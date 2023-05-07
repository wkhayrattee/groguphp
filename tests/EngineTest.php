<?php

use Groguphp\Engine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $container = getContainer();
    $this->engine = new Engine($container);
});

it('constructs without errors', function () {
    expect($this->engine)->toBeInstanceOf(Engine::class);
});

it('returns 404 response when route is not found', function () {
    $request = Request::create('/non-existent-route', 'GET');
    $response = $this->engine->handle($request);

    expect($response->getStatusCode())->toBe(404);
});

it('returns 405 response when method is not allowed', function () {
    $request = Request::create('/some-route', 'GET');
    $response = $this->engine->handle($request);

    expect($response->getStatusCode())->toBe(405);
});

it('returns 502 response for unhandled dispatcher case', function () {
    putenv('TEST_UNHANDLED_DISPATCHER=true'); // Set the environment variable to true

    $request = Request::create('/some-route', 'GET');
    $response = $this->engine->handle($request);

    putenv('TEST_UNHANDLED_DISPATCHER=false'); // Reset the environment variable to false

    expect($response->getStatusCode())->toBe(502);
});

it('successfully dispatches a matching route and method', function () {
    $container = getContainer();
    $engine = new Engine($container);

    $request = Request::create('/home', 'GET');
    $response = $engine->handle($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
});

it('returns 405 response for unsupported HTTP method', function () {
    $container = getContainer();
    $engine = new Engine($container);

    $request = Request::create('/some-route', 'GET');
    $response = $engine->handle($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_METHOD_NOT_ALLOWED);
});

it('successfully dispatches a route with a placeholder', function () {
    $container = getContainer();
    $engine = new Engine($container);

    $request = Request::create('/user/1', 'GET');
    $response = $engine->handle($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
});

it('returns 404 response for a non-existing route', function () {
    $container = getContainer();
    $engine = new Engine($container);

    $request = Request::create('/non-existing-route', 'GET');
    $response = $engine->handle($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);
});

it('handles exceptions during dispatching', function () {
    $container = getContainer();
    $engine = new Engine($container);
    $container['routes'] = function () {
        throw new Exception('An error occurred during dispatching');
    };

    $request = Request::create('/home', 'GET');

    // Expect an exception with a specific message
    expect(function () use ($engine, $request) {
        $engine->handle($request);
    })->toThrow(Exception::class)->and('An error occurred during dispatching');
});
