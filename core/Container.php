<?php

declare(strict_types=1);

namespace Mint\Core;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;

/**
 * Dependency injection container.
 */
class Container
{
    /**
     * Singleton instance of the container.
     *
     * @var Container|null
     */
    private static ?Container $instance = null;

    /**
     * Registered bindings.
     *
     * @var array<string, Closure|string>
     */
    private array $bindings = [];

    /**
     * Registered singletons.
     *
     * @var array<string, bool>
     */
    private array $singletons = [];

    /**
     * Resolved instances.
     *
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Get the singleton container instance.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    /**
     * Register a binding in the container.
     *
     * @param string              $abstract
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function bind(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    /**
     * Register a singleton binding in the container.
     *
     * @param string              $abstract
     * @param Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = true;
    }

    /**
     * Register an existing instance in the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return void
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve a binding from the container.
     *
     * @param string $abstract
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Build a concrete class instance with auto-wired dependencies.
     *
     * @param string $concrete
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function build(string $concrete): mixed
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Cannot instantiate [{$concrete}]");
        }

        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            return new $concrete();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException("Cannot resolve [{$param->getName()}] in [{$concrete}]");
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Check if a binding or instance exists in the container.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}
