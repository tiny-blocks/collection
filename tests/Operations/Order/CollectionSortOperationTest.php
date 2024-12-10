<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Order;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\Currency;
use TinyBlocks\Collection\Order;

final class CollectionSortOperationTest extends TestCase
{
    public function testSortEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When sorting the empty collection */
        $actual = $collection->sort();

        /** @Then the collection should remain empty */
        self::assertSame([], $actual->toArray());
    }

    #[DataProvider('ascendingKeySortDataProvider')]
    public function testSortAscendingByKey(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with unordered elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When sorting the collection in ascending order by key */
        $actual = $collection->sort();

        /** @Then the collection should be sorted by key in ascending order */
        self::assertSame($expected, $actual->toArray());
    }

    #[DataProvider('descendingKeySortDataProvider')]
    public function testSortDescendingByKey(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with unordered elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When sorting the collection in descending order by key */
        $actual = $collection->sort(order: Order::DESCENDING_KEY);

        /** @Then the collection should be sorted by key in descending order */
        self::assertSame($expected, $actual->toArray());
    }

    #[DataProvider('ascendingValueSortDataProvider')]
    public function testSortAscendingByValue(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with unordered elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When sorting the collection in ascending order by value */
        $actual = $collection->sort(order: Order::ASCENDING_VALUE);

        /** @Then the collection should be sorted by value in ascending order */
        self::assertSame($expected, $actual->toArray());
    }

    #[DataProvider('descendingValueSortDataProvider')]
    public function testSortDescendingByValue(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with unordered elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When sorting the collection in descending order by value */
        $actual = $collection->sort(order: Order::DESCENDING_VALUE);

        /** @Then the collection should be sorted by value in descending order */
        self::assertSame($expected, $actual->toArray());
    }

    public static function ascendingKeySortDataProvider(): iterable
    {
        yield 'Floats ascending by key' => [
            'elements' => ['3.1' => 'a', '1.1' => 'b', '4.1' => 'c', '5.1' => 'd', '2.1' => 'e'],
            'expected' => ['1.1' => 'b', '2.1' => 'e', '3.1' => 'a', '4.1' => 'c', '5.1' => 'd']
        ];

        yield 'Strings ascending by key' => [
            'elements' => ['c' => 'apple', 'a' => 'banana', 'b' => 'cherry'],
            'expected' => ['a' => 'banana', 'b' => 'cherry', 'c' => 'apple']
        ];

        yield 'Integers ascending by key' => [
            'elements' => [3 => 'a', 1 => 'b', 4 => 'c', 5 => 'd', 2 => 'e'],
            'expected' => [1 => 'b', 2 => 'e', 3 => 'a', 4 => 'c', 5 => 'd']
        ];
    }

    public static function ascendingValueSortDataProvider(): iterable
    {
        yield 'Floats ascending by value' => [
            'elements' => [3 => 5.5, 1 => 1.1, 4 => 3.3, 5 => 4.4, 2 => 2.2],
            'expected' => [1 => 1.1, 2 => 2.2, 4 => 3.3, 5 => 4.4, 3 => 5.5]
        ];

        yield 'Objects ascending by value' => [
            'elements' => [
                new Amount(value: 200.00, currency: Currency::USD),
                new Amount(value: 150.75, currency: Currency::EUR),
                new Amount(value: 100.50, currency: Currency::USD)
            ],
            'expected' => [
                2 => ['value' => 100.50, 'currency' => Currency::USD->name],
                1 => ['value' => 150.75, 'currency' => Currency::EUR->name],
                0 => ['value' => 200.00, 'currency' => Currency::USD->name]
            ]
        ];

        yield 'Strings ascending by value' => [
            'elements' => [3 => 'c', 1 => 'a', 4 => 'd', 5 => 'b', 2 => 'e'],
            'expected' => [1 => 'a', 5 => 'b', 3 => 'c', 4 => 'd', 2 => 'e']
        ];

        yield 'Integers ascending by value' => [
            'elements' => [3 => 5, 1 => 1, 4 => 3, 5 => 4, 2 => 2],
            'expected' => [1 => 1, 2 => 2, 4 => 3, 5 => 4, 3 => 5]
        ];
    }

    public static function descendingKeySortDataProvider(): iterable
    {
        yield 'Floats descending by key' => [
            'elements' => ['3.1' => 'a', '1.1' => 'b', '4.1' => 'c', '5.1' => 'd', '2.1' => 'e'],
            'expected' => ['5.1' => 'd', '4.1' => 'c', '3.1' => 'a', '2.1' => 'e', '1.1' => 'b']
        ];

        yield 'Strings descending by key' => [
            'elements' => ['c' => 'apple', 'a' => 'banana', 'b' => 'cherry'],
            'expected' => ['c' => 'apple', 'b' => 'cherry', 'a' => 'banana']
        ];

        yield 'Integers descending by key' => [
            'elements' => [3 => 'a', 1 => 'b', 4 => 'c', 5 => 'd', 2 => 'e'],
            'expected' => [5 => 'd', 4 => 'c', 3 => 'a', 2 => 'e', 1 => 'b']
        ];
    }

    public static function descendingValueSortDataProvider(): iterable
    {
        yield 'Floats descending by value' => [
            'elements' => [3 => 5.5, 1 => 1.1, 4 => 3.3, 5 => 4.4, 2 => 2.2],
            'expected' => [3 => 5.5, 5 => 4.4, 4 => 3.3, 2 => 2.2, 1 => 1.1]
        ];

        yield 'Objects descending by value' => [
            'elements' => [
                new Amount(value: 100.50, currency: Currency::USD),
                new Amount(value: 150.75, currency: Currency::EUR),
                new Amount(value: 200.00, currency: Currency::USD)
            ],
            'expected' => [
                2 => ['value' => 200.00, 'currency' => Currency::USD->name],
                1 => ['value' => 150.75, 'currency' => Currency::EUR->name],
                0 => ['value' => 100.50, 'currency' => Currency::USD->name]
            ]
        ];

        yield 'Strings descending by value' => [
            'elements' => [3 => 'c', 1 => 'a', 4 => 'd', 5 => 'b', 2 => 'e'],
            'expected' => [2 => 'e', 4 => 'd', 3 => 'c', 5 => 'b', 1 => 'a']
        ];

        yield 'Integers descending by value' => [
            'elements' => [3 => 5, 1 => 1, 4 => 3, 5 => 4, 2 => 2],
            'expected' => [3 => 5, 5 => 4, 4 => 3, 2 => 2, 1 => 1]
        ];
    }
}
