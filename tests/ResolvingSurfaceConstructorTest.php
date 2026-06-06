<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TinyBlocks\Collection\Internal\Operations\Resolving\Each;
use TinyBlocks\Collection\Internal\Operations\Resolving\Equality;
use TinyBlocks\Collection\Internal\Operations\Resolving\Find;
use TinyBlocks\Collection\Internal\Operations\Resolving\Join;
use TinyBlocks\Collection\Internal\Operations\Resolving\Reduce;

final class ResolvingSurfaceConstructorTest extends TestCase
{
    public function testEachSurfaceWhenConstructedThroughReflectionThenIsNeverPubliclyInstantiable(): void
    {
        /** @Given the reflection of the private constructor of the Each surface */
        $constructor = new ReflectionMethod(Each::class, '__construct');

        /** @And an instance allocated without invoking the constructor */
        $instance = $constructor->getDeclaringClass()->newInstanceWithoutConstructor();

        /** @When the private constructor is invoked through reflection */
        $constructor->invoke($instance);

        /** @Then the constructor is private, so the surface is never publicly instantiable */
        self::assertTrue($constructor->isPrivate());

        /** @And the reflection produces an instance of the surface */
        self::assertSame(Each::class, $instance::class);
    }

    public function testFindSurfaceWhenConstructedThroughReflectionThenIsNeverPubliclyInstantiable(): void
    {
        /** @Given the reflection of the private constructor of the Find surface */
        $constructor = new ReflectionMethod(Find::class, '__construct');

        /** @And an instance allocated without invoking the constructor */
        $instance = $constructor->getDeclaringClass()->newInstanceWithoutConstructor();

        /** @When the private constructor is invoked through reflection */
        $constructor->invoke($instance);

        /** @Then the constructor is private, so the surface is never publicly instantiable */
        self::assertTrue($constructor->isPrivate());

        /** @And the reflection produces an instance of the surface */
        self::assertSame(Find::class, $instance::class);
    }

    public function testJoinSurfaceWhenConstructedThroughReflectionThenIsNeverPubliclyInstantiable(): void
    {
        /** @Given the reflection of the private constructor of the Join surface */
        $constructor = new ReflectionMethod(Join::class, '__construct');

        /** @And an instance allocated without invoking the constructor */
        $instance = $constructor->getDeclaringClass()->newInstanceWithoutConstructor();

        /** @When the private constructor is invoked through reflection */
        $constructor->invoke($instance);

        /** @Then the constructor is private, so the surface is never publicly instantiable */
        self::assertTrue($constructor->isPrivate());

        /** @And the reflection produces an instance of the surface */
        self::assertSame(Join::class, $instance::class);
    }

    public function testReduceSurfaceWhenConstructedThroughReflectionThenIsNeverPubliclyInstantiable(): void
    {
        /** @Given the reflection of the private constructor of the Reduce surface */
        $constructor = new ReflectionMethod(Reduce::class, '__construct');

        /** @And an instance allocated without invoking the constructor */
        $instance = $constructor->getDeclaringClass()->newInstanceWithoutConstructor();

        /** @When the private constructor is invoked through reflection */
        $constructor->invoke($instance);

        /** @Then the constructor is private, so the surface is never publicly instantiable */
        self::assertTrue($constructor->isPrivate());

        /** @And the reflection produces an instance of the surface */
        self::assertSame(Reduce::class, $instance::class);
    }

    public function testEqualitySurfaceWhenConstructedThroughReflectionThenIsNeverPubliclyInstantiable(): void
    {
        /** @Given the reflection of the private constructor of the Equality surface */
        $constructor = new ReflectionMethod(Equality::class, '__construct');

        /** @And an instance allocated without invoking the constructor */
        $instance = $constructor->getDeclaringClass()->newInstanceWithoutConstructor();

        /** @When the private constructor is invoked through reflection */
        $constructor->invoke($instance);

        /** @Then the constructor is private, so the surface is never publicly instantiable */
        self::assertTrue($constructor->isPrivate());

        /** @And the reflection produces an instance of the surface */
        self::assertSame(Equality::class, $instance::class);
    }
}
