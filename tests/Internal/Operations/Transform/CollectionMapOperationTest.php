<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Transform;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\Dragon;
use TinyBlocks\Collection\Collection;

final class CollectionMapOperationTest extends TestCase
{
    public function testMapArrayToObject(): void
    {
        /** @Given a collection of arrays */
        $collection = Collection::createFrom(elements: [
            ['name' => 'Smaug', 'description' => 'Fire-breathing dragon'],
            ['name' => 'Shenron', 'description' => 'Eternal dragon'],
            ['name' => 'Toothless', 'description' => 'Night Fury dragon']
        ]);

        /** @When mapped to convert arrays into objects */
        $actual = $collection->map(static function (iterable $data): Dragon {
            return new Dragon(name: $data['name'], description: $data['description']);
        });

        /** @Then the collection should contain transformed objects */
        $expected = Collection::createFrom(elements: [
            new Dragon(name: 'Smaug', description: 'Fire-breathing dragon'),
            new Dragon(name: 'Shenron', description: 'Eternal dragon'),
            new Dragon(name: 'Toothless', description: 'Night Fury dragon')
        ]);

        self::assertSame($expected->toArray(), $actual->toArray());
    }

    public function testMapPreservesKeys(): void
    {
        /** @Given a collection with associative array elements */
        $collection = Collection::createFrom(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When mapping the collection with a transformation */
        $actual = $collection->map(transformations: static fn(int $value): int => $value * 2);

        /** @Then the mapped collection should preserve the keys */
        self::assertSame(['a' => 2, 'b' => 4, 'c' => 6], $actual->toArray());
    }

    public function testMapEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When mapping the empty collection with transformations */
        $actual = $collection->map(
            static fn(int $value): int => $value * 2,
            static fn(int $value): int => $value + 1
        );

        /** @Then the collection should remain empty */
        self::assertEmpty($actual->toArray());
    }

    public function testMapWithSingleTransformation(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When mapping the collection with a transformation */
        $actual = $collection->map(transformations: static fn(int $value): int => $value * 2);

        /** @Then the collection should contain transformed elements */
        self::assertSame([2, 4, 6], $actual->toArray());
    }

    public function testMapWithMultipleTransformations(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /**
         * @When mapping the collection with two transformations,
         * the first transformation squares each value,
         * and the second transformation increments each value by 1.
         */
        $actual = $collection->map(
            static fn(int $value): int => $value * $value,
            static fn(int $value): int => $value + 1
        );

        /** @Then the collection should contain elements transformed by the transformations */
        self::assertSame([2, 5, 10], $actual->toArray());
    }
}
