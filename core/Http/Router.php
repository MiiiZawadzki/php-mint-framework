<?php

namespace Mint\Core\Http;

class Router
{
    private array $routes = [];

    /**
     * @param string $uri
     * @param callable $callback
     * @return void
     */
    public function get(string $uri, callable $callback): void
    {
        $this->routes['GET'][$uri] = $callback;
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return void
     */
    public function post(string $uri, callable $callback): void
    {
        $this->routes['POST'][$uri] = $callback;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function dispatch(Request $request, Response $response): void
    {
        $uri = $request->getUri();
        $method = $request->getMethod();

        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri], $request, $response);
        } else {
            $response->setStatusCode(404);
            echo "404 - Not Found";
        }
    }
}
