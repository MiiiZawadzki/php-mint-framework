<?php

declare(strict_types=1);

use Mint\Core\Container;

beforeEach(function () {
    // Create fresh container for each test
    $this->container = new Container();
});

describe('Container', function () {
    describe('getInstance', function () {
        it('returns singleton instance', function () {
            $instance1 = Container::getInstance();
            $instance2 = Container::getInstance();

            expect($instance1)->toBe($instance2);
        });
    });

    describe('bind', function () {
        it('binds a class to itself when no concrete given', function () {
            $this->container->bind(SimpleClass::class);

            $resolved = $this->container->make(SimpleClass::class);

            expect($resolved)->toBeInstanceOf(SimpleClass::class);
        });

        it('binds abstract to concrete class', function () {
            $this->container->bind(TestInterface::class, TestImplementation::class);

            $resolved = $this->container->make(TestInterface::class);

            expect($resolved)->toBeInstanceOf(TestImplementation::class);
        });

        it('binds using closure factory', function () {
            $this->container->bind('custom', fn () => new SimpleClass());

            $resolved = $this->container->make('custom');

            expect($resolved)->toBeInstanceOf(SimpleClass::class);
        });

        it('creates new instance each time for regular bindings', function () {
            $this->container->bind(SimpleClass::class);

            $instance1 = $this->container->make(SimpleClass::class);
            $instance2 = $this->container->make(SimpleClass::class);

            expect($instance1)->not->toBe($instance2);
        });
    });

    describe('singleton', function () {
        it('returns same instance on multiple calls', function () {
            $this->container->singleton(SimpleClass::class);

            $instance1 = $this->container->make(SimpleClass::class);
            $instance2 = $this->container->make(SimpleClass::class);

            expect($instance1)->toBe($instance2);
        });

        it('works with closure factory', function () {
            $this->container->singleton('service', fn () => new SimpleClass());

            $instance1 = $this->container->make('service');
            $instance2 = $this->container->make('service');

            expect($instance1)->toBe($instance2);
        });

        it('closure receives container instance', function () {
            $this->container->singleton(SimpleClass::class);
            $this->container->singleton(
                'dependent',
                fn (Container $c) => new ClassWithDependency($c->make(SimpleClass::class))
            );

            $resolved = $this->container->make('dependent');

            expect($resolved)->toBeInstanceOf(ClassWithDependency::class);
            expect($resolved->dependency)->toBeInstanceOf(SimpleClass::class);
        });
    });

    describe('instance', function () {
        it('registers existing instance', function () {
            $instance = new SimpleClass();
            $instance->value = 'preset';

            $this->container->instance(SimpleClass::class, $instance);

            $resolved = $this->container->make(SimpleClass::class);

            expect($resolved)->toBe($instance);
            expect($resolved->value)->toBe('preset');
        });

        it('instance takes precedence over binding', function () {
            $instance = new SimpleClass();
            $instance->value = 'instance';

            $this->container->bind(SimpleClass::class);
            $this->container->instance(SimpleClass::class, $instance);

            $resolved = $this->container->make(SimpleClass::class);

            expect($resolved)->toBe($instance);
        });
    });

    describe('make (auto-wiring)', function () {
        it('resolves class without dependencies', function () {
            $resolved = $this->container->make(SimpleClass::class);

            expect($resolved)->toBeInstanceOf(SimpleClass::class);
        });

        it('auto-wires single dependency', function () {
            $resolved = $this->container->make(ClassWithDependency::class);

            expect($resolved)->toBeInstanceOf(ClassWithDependency::class);
            expect($resolved->dependency)->toBeInstanceOf(SimpleClass::class);
        });

        it('auto-wires nested dependencies', function () {
            $resolved = $this->container->make(ClassWithNestedDependency::class);

            expect($resolved)->toBeInstanceOf(ClassWithNestedDependency::class);
            expect($resolved->dependency)->toBeInstanceOf(ClassWithDependency::class);
            expect($resolved->dependency->dependency)->toBeInstanceOf(SimpleClass::class);
        });

        it('auto-wires multiple dependencies', function () {
            $resolved = $this->container->make(ClassWithMultipleDependencies::class);

            expect($resolved)->toBeInstanceOf(ClassWithMultipleDependencies::class);
            expect($resolved->simple)->toBeInstanceOf(SimpleClass::class);
            expect($resolved->withDep)->toBeInstanceOf(ClassWithDependency::class);
        });

        it('uses default values for primitive parameters', function () {
            $resolved = $this->container->make(ClassWithDefaultValue::class);

            expect($resolved)->toBeInstanceOf(ClassWithDefaultValue::class);
            expect($resolved->value)->toBe('default');
        });

        it('resolves interface when bound', function () {
            $this->container->bind(TestInterface::class, TestImplementation::class);

            $resolved = $this->container->make(ClassDependingOnInterface::class);

            expect($resolved)->toBeInstanceOf(ClassDependingOnInterface::class);
            expect($resolved->service)->toBeInstanceOf(TestImplementation::class);
        });

        it('throws exception for unresolvable primitive without default', function () {
            expect(fn () => $this->container->make(ClassWithUnresolvablePrimitive::class))
                ->toThrow(RuntimeException::class, 'Cannot resolve [value]');
        });

        it('throws exception for non-instantiable class', function () {
            expect(fn () => $this->container->make(TestInterface::class))
                ->toThrow(RuntimeException::class, 'Cannot instantiate');
        });

        it('throws exception for non-existent class', function () {
            expect(fn () => $this->container->make('NonExistentClass'))
                ->toThrow(ReflectionException::class);
        });
    });

    describe('has', function () {
        it('returns false for unbound class', function () {
            expect($this->container->has('unbound'))->toBeFalse();
        });

        it('returns true for bound class', function () {
            $this->container->bind(SimpleClass::class);

            expect($this->container->has(SimpleClass::class))->toBeTrue();
        });

        it('returns true for registered instance', function () {
            $this->container->instance('key', new SimpleClass());

            expect($this->container->has('key'))->toBeTrue();
        });
    });
});

// Test helper classes

/**
 * Simple test class.
 */
class SimpleClass
{
    public string $value = '';
}

/**
 * Test class with a single dependency.
 */
class ClassWithDependency
{
    /**
     * @param SimpleClass $dependency
     */
    public function __construct(public SimpleClass $dependency)
    {
    }
}

/**
 * Test class with nested dependency.
 */
class ClassWithNestedDependency
{
    /**
     * @param ClassWithDependency $dependency
     */
    public function __construct(public ClassWithDependency $dependency)
    {
    }
}

/**
 * Test class with multiple dependencies.
 */
class ClassWithMultipleDependencies
{
    /**
     * @param SimpleClass         $simple
     * @param ClassWithDependency $withDep
     */
    public function __construct(
        public SimpleClass $simple,
        public ClassWithDependency $withDep
    ) {
    }
}

/**
 * Test class with default parameter value.
 */
class ClassWithDefaultValue
{
    /**
     * @param string $value
     */
    public function __construct(public string $value = 'default')
    {
    }
}

/**
 * Test class with unresolvable primitive.
 */
class ClassWithUnresolvablePrimitive
{
    /**
     * @param string $value
     */
    public function __construct(public string $value)
    {
    }
}

/**
 * Test interface.
 */
interface TestInterface
{
    /**
     * @return void
     */
    public function doSomething(): void;
}

/**
 * Test interface implementation.
 */
class TestImplementation implements TestInterface
{
    /**
     * @return void
     */
    public function doSomething(): void
    {
    }
}

/**
 * Test class depending on interface.
 */
class ClassDependingOnInterface
{
    /**
     * @param TestInterface $service
     */
    public function __construct(public TestInterface $service)
    {
    }
}
