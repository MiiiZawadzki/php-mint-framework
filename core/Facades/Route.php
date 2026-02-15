<?php

declare(strict_types=1);

namespace Mint\Core\Facades;

use Mint\Core\Http\Router;

/**
 * @method static void get(string $uri, callable|array $action)
 * @method static void post(string $uri, callable|array $action)
 * @method static void put(string $uri, callable|array $action)
 * @method static void delete(string $uri, callable|array $action)
 */
class Route extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
