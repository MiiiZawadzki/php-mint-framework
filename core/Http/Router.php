<?php

declare(strict_types=1);

namespace Mint\Core\Http;

use Mint\Core\Container;
use ReflectionException;

class Router
{
    /**
     * Registered routes.
     *
     * @var array<string, array<string, callable|array{0: class-string|object, 1: string}>>
     */
    private array $routes = [];

    /**
     * Dependency injection container.
     *
     * @var Container
     */
    private Container $container;

    /**
     * Create a new router instance.
     */
    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    /**
     * Register a GET route.
     *
     * @param  string  $uri
     * @param  callable|array{0: class-string|object, 1: string}  $callback
     *
     * @return void
     */
    public function get(string $uri, callable|array $callback): void
    {
        $this->addRoute('GET', $uri, $callback);
    }

    /**
     * Register a POST route.
     *
     * @param  string  $uri
     * @param  callable|array{0: class-string|object, 1: string}  $callback
     *
     * @return void
     */
    public function post(string $uri, callable|array $callback): void
    {
        $this->addRoute('POST', $uri, $callback);
    }

    /**
     * Register a PUT route.
     *
     * @param  string  $uri
     * @param  callable|array{0: class-string|object, 1: string}  $callback
     *
     * @return void
     */
    public function put(string $uri, callable|array $callback): void
    {
        $this->addRoute('PUT', $uri, $callback);
    }

    /**
     * Register a PATCH route.
     *
     * @param  string  $uri
     * @param  callable|array{0: class-string|object, 1: string}  $callback
     *
     * @return void
     */
    public function patch(string $uri, callable|array $callback): void
    {
        $this->addRoute('PATCH', $uri, $callback);
    }

    /**
     * Register a DELETE route.
     *
     * @param  string  $uri
     * @param  callable|array{0: class-string|object, 1: string}  $callback
     *
     * @return void
     */
    public function delete(string $uri, callable|array $callback): void
    {
        $this->addRoute('DELETE', $uri, $callback);
    }

    /**
     * Register a OPTIONS route.
     *
     * @param  string  $uri
     * @param  callable|array{0: class-string|object, 1: string}  $callback
     *
     * @return void
     */
    public function options(string $uri, callable|array $callback): void
    {
        $this->addRoute('OPTIONS', $uri, $callback);
    }

    /**
     * @param  string  $uri
     * @param  callable|array  $callback
     *
     * @return void
     */
    public function any(string $uri, callable|array $callback): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $method) {
            $this->addRoute($method, $uri, $callback);
        }
    }

    /**
     * @param  string  $method
     * @param  string  $uri
     * @param  callable|array  $callback
     *
     * @return void
     */
    private function addRoute(string $method, string $uri, callable|array $callback): void
    {
        // Normalize URI
        $uri = '/' . trim($uri, '/');

        // Extract parameter names
        $params = [];
        if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $uri, $matches)) {
            $params = $matches[1];
        }

        // Convert to regex pattern
        $pattern = '#^' . preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $uri) . '$#';

        $this->routes[$method][$pattern] = [
            'callback' => $callback,
            'params' => $params,
        ];
    }

    /**
     * Dispatch the request to the matched route.
     *
     * @param  Request  $request
     * @param  Response  $response
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function dispatch(Request $request, Response $response): void
    {
        $uri = '/' . trim($request->getUri(), '/');
        $method = $request->getMethod();

        if (!isset($this->routes[$method])) {
            $this->notFound($response);
            return;
        }

        foreach ($this->routes[$method] as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->executeCallback($route['callback'], $request, $response, $matches);
                return;
            }
        }

        $this->notFound($response);
    }

    /**
     * @param  callable|array  $callback
     * @param  Request  $request
     * @param  Response  $response
     * @param  array  $params
     *
     * @return void
     *
     * @throws ReflectionException
     */
    private function executeCallback(
        callable|array $callback,
        Request $request,
        Response $response,
        array $params
    ): void {
        if (is_array($callback)) {
            [$controller, $action] = $callback;
            if (is_string($controller)) {
                $controller = $this->container->make($controller);
            }
            $controller->$action($request, $response, ...$params);
            return;
        }

        $callback($request, $response, ...$params);
    }

    /**
     * @param  Response  $response
     *
     * @return void
     */
    private function notFound(Response $response): void
    {
        $response->setStatusCode(404);
        echo "404 - Not Found";
    }

    /**
     * @return \array[][]|callable[][]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
