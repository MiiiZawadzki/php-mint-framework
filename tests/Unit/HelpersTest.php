<?php

declare(strict_types=1);

use Mint\Core\Container;

describe('Helper Functions', function () {
    describe('app()', function () {
        it('returns container instance when called without arguments', function () {
            $container = app();

            expect($container)->toBeInstanceOf(Container::class);
        });

        it('returns same container instance (singleton)', function () {
            $container1 = app();
            $container2 = app();

            expect($container1)->toBe($container2);
        });

        it('resolves service when called with abstract', function () {
            app()->bind(HelperTestService::class);

            $service = app(HelperTestService::class);

            expect($service)->toBeInstanceOf(HelperTestService::class);
        });

        it('returns container singleton instance', function () {
            expect(app())->toBe(Container::getInstance());
        });
    });

    describe('app_version()', function () {
        it('returns version string', function () {
            $version = app_version();

            expect($version)->toBeString();
        });

        it('returns valid semver format or default', function () {
            $version = app_version();

            // Should match semver pattern or be default 0.0.0
            expect($version)->toMatch('/^\d+\.\d+\.\d+/');
        });
    });
});

// Test helper class

/**
 * Test service class.
 */
class HelperTestService
{
    public string $name = 'test';
}
