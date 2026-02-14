<?php

declare(strict_types=1);

use Mint\Core\Container;

if (!function_exists('app')) {
    /**
     * Get container instance or resolve a service.
     *
     * @param string|null $abstract
     *
     * @return mixed
     * @throws ReflectionException
     */
    function app(?string $abstract = null): mixed
    {
        $container = Container::getInstance();

        return $abstract ? $container->make($abstract) : $container;
    }
}

if (!function_exists('app_version')) {
    /**
     * Get application version from composer.json.
     *
     * @return string
     */
    function app_version(): string
    {
        $composerPath = __DIR__ . '/composer.json';

        if (!file_exists($composerPath)) {
            return '0.0.0';
        }

        $composerData = json_decode(file_get_contents($composerPath), true);

        return $composerData['version'] ?? '0.0.0';
    }
}
