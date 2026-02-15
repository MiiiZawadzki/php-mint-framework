<?php

declare(strict_types=1);

use Mint\Core\Container;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Mint\Core\Http\Router;

beforeEach(function () {
    $this->container = new Container();
    $this->router = new Router();
});

describe('Router', function () {
    describe('route registration', function () {
        it('registers GET route', function () {
            $this->router->get('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('GET');
            expect(array_keys($routes['GET']))->toContain('#^/test$#');
        });

        it('registers POST route', function () {
            $this->router->post('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('POST');
            expect(array_keys($routes['POST']))->toContain('#^/test$#');
        });

        it('registers PUT route', function () {
            $this->router->put('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('PUT');
            expect(array_keys($routes['PUT']))->toContain('#^/test$#');
        });

        it('registers PATCH route', function () {
            $this->router->patch('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('PATCH');
            expect(array_keys($routes['PATCH']))->toContain('#^/test$#');
        });

        it('registers DELETE route', function () {
            $this->router->delete('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('DELETE');
            expect(array_keys($routes['DELETE']))->toContain('#^/test$#');
        });

        it('registers OPTIONS route', function () {
            $this->router->options('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('OPTIONS');
            expect(array_keys($routes['OPTIONS']))->toContain('#^/test$#');
        });

        it('registers route for all methods using any', function () {
            $this->router->any('/test', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect($routes)->toHaveKey('GET');
            expect($routes)->toHaveKey('POST');
            expect($routes)->toHaveKey('PUT');
            expect($routes)->toHaveKey('PATCH');
            expect($routes)->toHaveKey('DELETE');
            expect($routes)->toHaveKey('OPTIONS');
        });

        it('registers multiple routes', function () {
            $this->router->get('/one', fn () => 'one');
            $this->router->get('/two', fn () => 'two');
            $this->router->post('/three', fn () => 'three');

            $routes = $this->router->getRoutes();

            expect($routes['GET'])->toHaveCount(2);
            expect($routes['POST'])->toHaveCount(1);
        });
    });

    describe('route parameters', function () {
        it('registers route with single parameter', function () {
            $this->router->get('/users/{id}', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect(array_keys($routes['GET']))->toContain('#^/users/([^/]+)$#');
            expect($routes['GET']['#^/users/([^/]+)$#']['params'])->toBe(['id']);
        });

        it('registers route with multiple parameters', function () {
            $this->router->get('/users/{userId}/posts/{postId}', fn () => 'test');

            $routes = $this->router->getRoutes();

            expect(array_keys($routes['GET']))->toContain('#^/users/([^/]+)/posts/([^/]+)$#');
            expect($routes['GET']['#^/users/([^/]+)/posts/([^/]+)$#']['params'])->toBe(['userId', 'postId']);
        });

        it('dispatches route with parameter to closure', function () {
            $receivedId = null;
            $this->router->get('/users/{id}', function (Request $req, Response $res, string $id) use (&$receivedId) {
                $receivedId = $id;
            });

            $request = createMockRequest('GET', '/users/123');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($receivedId)->toBe('123');
        });

        it('dispatches route with multiple parameters', function () {
            $receivedUserId = null;
            $receivedPostId = null;
            $this->router->get('/users/{userId}/posts/{postId}', function (Request $req, Response $res, string $userId, string $postId) use (&$receivedUserId, &$receivedPostId) {
                $receivedUserId = $userId;
                $receivedPostId = $postId;
            });

            $request = createMockRequest('GET', '/users/42/posts/99');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($receivedUserId)->toBe('42');
            expect($receivedPostId)->toBe('99');
        });

        it('dispatches route with parameter to controller', function () {
            $this->router->get('/items/{id}', [TestControllerWithParams::class, 'show']);

            $request = createMockRequest('GET', '/items/456');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            $output = ob_get_clean();

            expect($output)->toBe('item: 456');
        });
    });

    describe('dispatch', function () {
        it('dispatches to closure handler', function () {
            $output = '';
            $this->router->get('/', function (Request $req, Response $res) use (&$output) {
                $output = 'closure called';
            });

            $request = createMockRequest('GET', '/');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($output)->toBe('closure called');
        });

        it('dispatches to controller array with class name', function () {
            $this->router->get('/', [TestController::class, 'index']);

            $request = createMockRequest('GET', '/');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            $output = ob_get_clean();

            expect($output)->toBe('index called');
        });

        it('passes request and response to handler', function () {
            $receivedRequest = null;
            $receivedResponse = null;

            $this->router->get('/', function (Request $req, Response $res) use (&$receivedRequest, &$receivedResponse) {
                $receivedRequest = $req;
                $receivedResponse = $res;
            });

            $request = createMockRequest('GET', '/');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($receivedRequest)->toBeInstanceOf(Request::class);
            expect($receivedResponse)->toBeInstanceOf(Response::class);
        });

        it('returns 404 for non-existent route', function () {
            $request = createMockRequest('GET', '/nonexistent');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            $output = ob_get_clean();

            expect($output)->toBe('404 - Not Found');
        });

        it('returns 404 for wrong method', function () {
            $this->router->get('/test', fn () => 'test');

            $request = createMockRequest('POST', '/test');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            $output = ob_get_clean();

            expect($output)->toBe('404 - Not Found');
        });

        it('dispatches PUT request', function () {
            $output = '';
            $this->router->put('/test', function (Request $req, Response $res) use (&$output) {
                $output = 'put called';
            });

            $request = createMockRequest('PUT', '/test');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($output)->toBe('put called');
        });

        it('dispatches PATCH request', function () {
            $output = '';
            $this->router->patch('/test', function (Request $req, Response $res) use (&$output) {
                $output = 'patch called';
            });

            $request = createMockRequest('PATCH', '/test');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($output)->toBe('patch called');
        });

        it('dispatches DELETE request', function () {
            $output = '';
            $this->router->delete('/test', function (Request $req, Response $res) use (&$output) {
                $output = 'delete called';
            });

            $request = createMockRequest('DELETE', '/test');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($output)->toBe('delete called');
        });

        it('dispatches OPTIONS request', function () {
            $output = '';
            $this->router->options('/test', function (Request $req, Response $res) use (&$output) {
                $output = 'options called';
            });

            $request = createMockRequest('OPTIONS', '/test');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($output)->toBe('options called');
        });

        it('normalizes URI with trailing slash', function () {
            $output = '';
            $this->router->get('/test', function (Request $req, Response $res) use (&$output) {
                $output = 'normalized';
            });

            $request = createMockRequest('GET', '/test/');
            $response = new Response();

            $this->router->dispatch($request, $response);

            expect($output)->toBe('normalized');
        });
    });

    describe('controller DI', function () {
        it('resolves controller with dependencies via container', function () {
            $this->router->get('/', [ControllerWithDependency::class, 'index']);

            $request = createMockRequest('GET', '/');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request, $response);
            $output = ob_get_clean();

            expect($output)->toBe('dependency injected');
        });

        it('uses singleton dependencies in controller', function () {
            Container::getInstance()->singleton(SharedService::class);

            $this->router->get('/first', [ControllerUsingSharedService::class, 'first']);
            $this->router->get('/second', [ControllerUsingSharedService::class, 'second']);

            $request1 = createMockRequest('GET', '/first');
            $request2 = createMockRequest('GET', '/second');
            $response = new Response();

            ob_start();
            $this->router->dispatch($request1, $response);
            $this->router->dispatch($request2, $response);
            $output = ob_get_clean();

            expect($output)->toBe('count: 1count: 2');
        });
    });
});

// Helper function

function createMockRequest(string $method, string $uri): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;

    return new Request();
}

// Test helper classes

class TestController
{
    public function index(Request $request, Response $response): void
    {
        echo 'index called';
    }
}

class TestControllerWithParams
{
    public function show(Request $request, Response $response, string $id): void
    {
        echo 'item: ' . $id;
    }
}

class DependencyService
{
    public string $name = 'dependency';
}

class ControllerWithDependency
{
    public function __construct(private DependencyService $service)
    {
    }

    public function index(Request $request, Response $response): void
    {
        echo $this->service->name === 'dependency' ? 'dependency injected' : 'failed';
    }
}

class SharedService
{
    public int $counter = 0;

    public function increment(): int
    {
        return ++$this->counter;
    }
}

class ControllerUsingSharedService
{
    public function __construct(private SharedService $service)
    {
    }

    public function first(Request $request, Response $response): void
    {
        echo 'count: ' . $this->service->increment();
    }

    public function second(Request $request, Response $response): void
    {
        echo 'count: ' . $this->service->increment();
    }
}
