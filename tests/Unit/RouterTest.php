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

            $routes = getPrivateProperty($this->router, 'routes');

            expect($routes)->toHaveKey('GET');
            expect($routes['GET'])->toHaveKey('/test');
        });

        it('registers POST route', function () {
            $this->router->post('/test', fn () => 'test');

            $routes = getPrivateProperty($this->router, 'routes');

            expect($routes)->toHaveKey('POST');
            expect($routes['POST'])->toHaveKey('/test');
        });

        it('registers multiple routes', function () {
            $this->router->get('/one', fn () => 'one');
            $this->router->get('/two', fn () => 'two');
            $this->router->post('/three', fn () => 'three');

            $routes = getPrivateProperty($this->router, 'routes');

            expect($routes['GET'])->toHaveCount(2);
            expect($routes['POST'])->toHaveCount(1);
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

            // Both calls should show incrementing counter from same instance
            expect($output)->toBe('count: 1count: 2');
        });
    });
});

// Helper functions

/**
 * Get a private property value from an object.
 *
 * @param  object  $object
 * @param  string  $property
 *
 * @return mixed
 * @throws ReflectionException
 */
function getPrivateProperty(object $object, string $property): mixed
{
    $reflection = new ReflectionClass($object);
    $prop = $reflection->getProperty($property);
    $prop->setAccessible(true);

    return $prop->getValue($object);
}

/**
 * Create a mock request with the given method and URI.
 *
 * @param string $method
 * @param string $uri
 *
 * @return Request
 */
function createMockRequest(string $method, string $uri): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;

    return new Request();
}

// Test helper classes

/**
 * Test controller class.
 */
class TestController
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function index(Request $request, Response $response): void
    {
        echo 'index called';
    }
}

/**
 * Test dependency service class.
 */
class DependencyService
{
    public string $name = 'dependency';
}

/**
 * Test controller with dependency injection.
 */
class ControllerWithDependency
{
    /**
     * @param DependencyService $service
     */
    public function __construct(
        private DependencyService $service
    ) {
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function index(Request $request, Response $response): void
    {
        echo $this->service->name === 'dependency' ? 'dependency injected' : 'failed';
    }
}

/**
 * Shared service for testing singleton behavior.
 */
class SharedService
{
    public int $counter = 0;

    /**
     * @return int
     */
    public function increment(): int
    {
        return ++$this->counter;
    }
}

/**
 * Test controller using shared service.
 */
class ControllerUsingSharedService
{
    /**
     * @param SharedService $service
     */
    public function __construct(
        private SharedService $service
    ) {
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function first(Request $request, Response $response): void
    {
        echo 'count: ' . $this->service->increment();
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function second(Request $request, Response $response): void
    {
        echo 'count: ' . $this->service->increment();
    }
}
