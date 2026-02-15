<?php

declare(strict_types=1);

use Mint\Core\Container;
use Mint\Core\Facades\Route;
use Mint\Core\Http\Router;

beforeEach(function () {
    // Reset container singleton for clean state
    Container::getInstance()->singleton(Router::class);
});

describe('Route Facade', function () {
    it('registers GET route via facade', function () {
        Route::get('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('GET');
        expect(array_keys($routes['GET']))->toContain('#^/test$#');
    });

    it('registers POST route via facade', function () {
        Route::post('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('POST');
        expect(array_keys($routes['POST']))->toContain('#^/test$#');
    });

    it('registers PUT route via facade', function () {
        Route::put('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('PUT');
        expect(array_keys($routes['PUT']))->toContain('#^/test$#');
    });

    it('registers PATCH route via facade', function () {
        Route::patch('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('PATCH');
        expect(array_keys($routes['PATCH']))->toContain('#^/test$#');
    });

    it('registers DELETE route via facade', function () {
        Route::delete('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('DELETE');
        expect(array_keys($routes['DELETE']))->toContain('#^/test$#');
    });

    it('registers OPTIONS route via facade', function () {
        Route::options('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('OPTIONS');
        expect(array_keys($routes['OPTIONS']))->toContain('#^/test$#');
    });

    it('registers route for all methods using any', function () {
        Route::any('/test', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect($routes)->toHaveKey('GET');
        expect($routes)->toHaveKey('POST');
        expect($routes)->toHaveKey('PUT');
        expect($routes)->toHaveKey('PATCH');
        expect($routes)->toHaveKey('DELETE');
        expect($routes)->toHaveKey('OPTIONS');
    });

    it('registers route with parameters', function () {
        Route::get('/users/{id}', fn () => 'test');

        $router = Container::getInstance()->make(Router::class);
        $routes = $router->getRoutes();

        expect(array_keys($routes['GET']))->toContain('#^/users/([^/]+)$#');
        expect($routes['GET']['#^/users/([^/]+)$#']['params'])->toBe(['id']);
    });
});
