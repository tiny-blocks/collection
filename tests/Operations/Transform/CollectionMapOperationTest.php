<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\Dragon;

final class CollectionMapOperationTest extends TestCase
{
    public function testMapArrayToObject(): void
    {
        /** @Given a collection of arrays */
        $collection = Collection::from(elements: [
            ['name' => 'Smaug', 'description' => 'Fire-breathing dragon'],
            ['name' => 'Shenron', 'description' => 'Eternal dragon'],
            ['name' => 'Toothless', 'description' => 'Night Fury dragon']
        ]);

        /** @When mapped to convert arrays into objects */
        $actual = $collection->map(static function (iterable $data): Dragon {
            return new Dragon(name: $data['name'], description: $data['description']);
        });

        /** @Then the collection should contain transformed objects */
        $expected = Collection::from(elements: [
            new Dragon(name: 'Smaug', description: 'Fire-breathing dragon'),
            new Dragon(name: 'Shenron', description: 'Eternal dragon'),
            new Dragon(name: 'Toothless', description: 'Night Fury dragon')
        ]);

        self::assertSame($expected->toArray(), $actual->toArray());
    }

    public function testMapPreservesKeys(): void
    {
        /** @Given a collection with associative array elements */
        $collection = Collection::from(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When mapping the collection with a transformation */
        $actual = $collection->map(static fn(int $value): int => $value * 2);

        /** @Then the mapped collection should preserve the keys */
        self::assertSame(['a' => 2, 'b' => 4, 'c' => 6], $actual->toArray());
    }

    public function testMapEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::fromEmpty();

        /** @When mapping the empty collection with transformations */
        $actual = $collection->map(
            static fn(int $value): int => $value * 2,
            static fn(int $value): int => $value + 1
        );

        /** @Then the collection should remain empty */
        self::assertSame([], $actual->toArray());
    }

    public function testMapWithSingleTransformation(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::from(elements: [1, 2, 3]);

        /** @When mapping the collection with a transformation */
        $actual = $collection->map(static fn(int $value): int => $value * 2);

        /** @Then the collection should contain transformed elements */
        self::assertSame([2, 4, 6], $actual->toArray());
    }

    #[DataProvider('mapWithMultipleTransformationsDataProvider')]
    public function testMapWithMultipleTransformations(
        iterable $elements,
        iterable $expected,
        iterable $callbacks
    ): void {
        /** @Given a collection with elements */
        $collection = Collection::from(elements: $elements);

        /** @When mapping the collection with multiple transformations */
        $actual = $collection->map(...$callbacks);

        /** @Then the collection should contain elements transformed by the transformations */
        self::assertSame($expected, $actual->toArray());
    }

    public static function mapWithMultipleTransformationsDataProvider(): iterable
    {
        yield 'Square and increment' => [
            'elements'  => [1, 2, 3],
            'expected'  => [2, 5, 10],
            'callbacks' => [
                static fn(int $value): int => $value * $value,
                static fn(int $value): int => $value + 1
            ]
        ];

        yield 'Double and increment' => [
            'elements'  => [1, 2, 3],
            'expected'  => [3, 5, 7],
            'callbacks' => [
                static fn(int $value): int => $value * 2,
                static fn(int $value): int => $value + 1
            ]
        ];
    }
}
