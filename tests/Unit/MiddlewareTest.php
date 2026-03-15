<?php

declare(strict_types=1);

use Mint\Core\Container;
use Mint\Core\Http\MiddlewareInterface;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Mint\Core\Http\Router;

beforeEach(function () {
    $this->container = Container::getInstance();
    $this->router = new Router();
});

describe('Middleware', function () {
    describe('middleware pipeline', function () {
        it('executes middleware before route handler', function () {
            $order = [];

            $this->container->bind(OrderTrackingMiddleware::class, function () use (&$order) {
                return new OrderTrackingMiddleware($order);
            });

            $this->router->middleware([OrderTrackingMiddleware::class], function () use (&$order) {
                $this->router->get('/protected', function (Request $req, Response $res) use (&$order) {
                    $order[] = 'handler';
                });
            });

            $request = createMiddlewareMockRequest('GET', '/protected');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($order)->toBe(['middleware:before', 'handler', 'middleware:after']);
        });

        it('can block request in middleware', function () {
            $handlerCalled = false;

            $this->container->bind(BlockingMiddleware::class, function () {
                return new BlockingMiddleware();
            });

            $this->router->middleware([BlockingMiddleware::class], function () use (&$handlerCalled) {
                $this->router->get('/blocked', function (Request $req, Response $res) use (&$handlerCalled) {
                    $handlerCalled = true;
                });
            });

            $request = createMiddlewareMockRequest('GET', '/blocked');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            $output = ob_get_clean();

            expect($handlerCalled)->toBeFalse();
            expect($output)->toBe('blocked');
        });

        it('runs multiple middleware in order', function () {
            $order = [];

            $this->container->bind(FirstMiddleware::class, function () use (&$order) {
                return new FirstMiddleware($order);
            });
            $this->container->bind(SecondMiddleware::class, function () use (&$order) {
                return new SecondMiddleware($order);
            });

            $this->router->middleware([FirstMiddleware::class, SecondMiddleware::class], function () use (&$order) {
                $this->router->get('/multi', function (Request $req, Response $res) use (&$order) {
                    $order[] = 'handler';
                });
            });

            $request = createMiddlewareMockRequest('GET', '/multi');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($order)->toBe(['first:before', 'second:before', 'handler', 'second:after', 'first:after']);
        });

        it('does not apply middleware to routes outside the group', function () {
            $middlewareCalled = false;

            $this->container->bind(TrackingMiddleware::class, function () use (&$middlewareCalled) {
                return new TrackingMiddleware($middlewareCalled);
            });

            $this->router->get('/public', function (Request $req, Response $res) {
                echo 'public';
            });

            $this->router->middleware([TrackingMiddleware::class], function () {
                $this->router->get('/private', function (Request $req, Response $res) {
                    echo 'private';
                });
            });

            $request = createMiddlewareMockRequest('GET', '/public');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            ob_get_clean();

            expect($middlewareCalled)->toBeFalse();
        });

        it('supports nested middleware groups', function () {
            $order = [];

            $this->container->bind(FirstMiddleware::class, function () use (&$order) {
                return new FirstMiddleware($order);
            });
            $this->container->bind(SecondMiddleware::class, function () use (&$order) {
                return new SecondMiddleware($order);
            });

            $this->router->middleware([FirstMiddleware::class], function () use (&$order) {
                $this->router->middleware([SecondMiddleware::class], function () use (&$order) {
                    $this->router->get('/nested', function (Request $req, Response $res) use (&$order) {
                        $order[] = 'handler';
                    });
                });
            });

            $request = createMiddlewareMockRequest('GET', '/nested');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($order)->toBe(['first:before', 'second:before', 'handler', 'second:after', 'first:after']);
        });
    });

    describe('route registration with middleware', function () {
        it('stores middleware on route data', function () {
            $this->container->bind(BlockingMiddleware::class, function () {
                return new BlockingMiddleware();
            });

            $this->router->middleware([BlockingMiddleware::class], function () {
                $this->router->get('/test', fn() => 'test');
            });

            $routes = $this->router->getRoutes();
            $route = $routes['GET']['#^/test$#'];

            expect($route)->toHaveKey('middleware');
            expect($route['middleware'])->toBe([BlockingMiddleware::class]);
        });

        it('stores empty middleware for routes outside groups', function () {
            $this->router->get('/test', fn() => 'test');

            $routes = $this->router->getRoutes();
            $route = $routes['GET']['#^/test$#'];

            expect($route['middleware'])->toBe([]);
        });
    });
});

// Helper function

function createMiddlewareMockRequest(string $method, string $uri): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;

    return new Request();
}

// Test middleware classes

class OrderTrackingMiddleware implements MiddlewareInterface
{
    private array $order;

    public function __construct(array &$order)
    {
        $this->order = &$order;
    }

    public function handle(Request $request, callable $next): void
    {
        $this->order[] = 'middleware:before';
        $next();
        $this->order[] = 'middleware:after';
    }
}

class BlockingMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        echo 'blocked';
        // Do not call $next() — request is blocked
    }
}

class FirstMiddleware implements MiddlewareInterface
{
    private array $order;

    public function __construct(array &$order)
    {
        $this->order = &$order;
    }

    public function handle(Request $request, callable $next): void
    {
        $this->order[] = 'first:before';
        $next();
        $this->order[] = 'first:after';
    }
}

class SecondMiddleware implements MiddlewareInterface
{
    private array $order;

    public function __construct(array &$order)
    {
        $this->order = &$order;
    }

    public function handle(Request $request, callable $next): void
    {
        $this->order[] = 'second:before';
        $next();
        $this->order[] = 'second:after';
    }
}

class TrackingMiddleware implements MiddlewareInterface
{
    private bool $called;

    public function __construct(bool &$called)
    {
        $this->called = &$called;
    }

    public function handle(Request $request, callable $next): void
    {
        $this->called = true;
        $next();
    }
}
