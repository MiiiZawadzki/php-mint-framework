<?php

declare(strict_types=1);

namespace Mint\Core\Facades;

use Mint\Core\Container;
use ReflectionException;

abstract class Facade
{
    /**
     * Get the registered name of the component in the container.
     *
     * @return string
     */
    abstract protected static function getFacadeAccessor(): string;

    /**
     * Handle dynamic static calls.
     *
     * @param  string  $method
     * @param  array  $args
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = Container::getInstance()->make(static::getFacadeAccessor());

        return $instance->$method(...$args);
    }
}
