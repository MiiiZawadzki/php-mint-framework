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
        $this->routes['GET'][$uri] = $callback;
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
        $this->routes['POST'][$uri] = $callback;
    }

    /**
     * Dispatch the request to the matched route.
     *
     * @param  Request  $request
     * @param  Response  $response
     *
     * @return void
     * @throws ReflectionException
     */
    public function dispatch(Request $request, Response $response): void
    {
        $uri = $request->getUri();
        $method = $request->getMethod();

        if (!isset($this->routes[$method][$uri])) {
            $response->setStatusCode(404);
            echo "404 - Not Found";
            return;
        }

        $callback = $this->routes[$method][$uri];

        // Array callback: [ControllerClass, 'method']
        if (is_array($callback)) {
            [$controller, $method] = $callback;

            // Resolve controller through container (enables DI)
            if (is_string($controller)) {
                $controller = $this->container->make($controller);
            }

            $controller->$method($request, $response);
            return;
        }

        $callback($request, $response);
    }
}
