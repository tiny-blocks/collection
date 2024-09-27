<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;
use TinyBlocks\Collection\Models\CryptoCurrency;

final class CollectionFilterOperationTest extends TestCase
{
    public function testFilterOnLargeDataset(): void
    {
        /** @Given a large collection with ten thousand elements */
        $collection = Collection::createFrom(elements: range(1, 10000));

        /** @When filtering values greater than or equal to 9991 */
        $actual = $collection->filter(fn(int $value): bool => $value >= 9991);

        /** @Then the resulting collection should contain only the expected values */
        self::assertSame(range(9991, 10000), array_values($actual->toArray()));
    }

    #[DataProvider('filterPredicatesDataProvider')]
    public function testFilterAppliesMultiplePredicates(
        iterable $elements,
        iterable $expected,
        iterable $predicates
    ): void {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When filtering the collection with multiple predicates */
        $actual = $collection->filter(...$predicates);

        /** @Then the filtered collection deve descartar as chaves e retornar os valores esperados */
        self::assertSame(array_values((array)$expected), $actual->toArray(preserveKeys: PreserveKeys::DISCARD));
    }

    #[DataProvider('elementsDataProvider')]
    public function testFilterAppliesDefaultArrayFilter(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When filtering the collection without any predicates (using default truthy filter) */
        $actual = $collection->filter();

        /** @Then the filtered collection should contain only truthy elements */
        self::assertSame(array_values((array)$expected), $actual->toArray(preserveKeys: PreserveKeys::DISCARD));
    }

    #[DataProvider('elementsDataProviderWithKeys')]
    public function testFilterPreservesKeys(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with elements and keys */
        $collection = Collection::createFrom(elements: $elements);

        /** @When filtering the collection without any predicates (using default truthy filter) */
        $actual = $collection->filter();

        /** @Then the filtered collection should preserve keys for truthy elements */
        self::assertSame($expected, $actual->toArray());
    }

    public static function elementsDataProvider(): iterable
    {
        $bitcoin = new CryptoCurrency(name: 'Bitcoin', price: (float)rand(60000, 999999), symbol: 'BTC');

        yield 'Empty array' => [
            'elements' => [],
            'expected' => []
        ];

        yield 'Array with boolean values' => [
            'elements' => [false, true, false, true],
            'expected' => [1 => true, 3 => true]
        ];

        yield 'Array with null and numbers' => [
            'elements' => [null, 1, 2, 0],
            'expected' => [1 => 1, 2 => 2]
        ];

        yield 'Array with only falsy values' => [
            'elements' => [0, '', null, false],
            'expected' => []
        ];

        yield 'Array with objects and truthy values' => [
            'elements' => [$bitcoin, 1, 'valid string'],
            'expected' => [$bitcoin->toArray(), 1, 'valid string']
        ];
    }

    public static function elementsDataProviderWithKeys(): iterable
    {
        $bitcoin = new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC');

        yield 'Array with keys preserved' => [
            'elements' => ['a' => false, 'b' => true, 'c' => false, 'd' => true],
            'expected' => ['b' => true, 'd' => true]
        ];

        yield 'Mixed elements with keys' => [
            'elements' => ['first' => null, 'second' => 1, 'third' => $bitcoin],
            'expected' => ['second' => 1, 'third' => $bitcoin->toArray()]
        ];
    }

    public static function filterPredicatesDataProvider(): iterable
    {
        yield 'Filter with objects and custom predicate' => [
            'elements'   => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
                new CryptoCurrency(name: 'Litecoin', price: 200.0, symbol: 'LTC')
            ],
            'expected'   => [
                ['name' => 'Bitcoin', 'price' => 60000.0, 'symbol' => 'BTC'],
                ['name' => 'Ethereum', 'price' => 40000.0, 'symbol' => 'ETH']
            ],
            'predicates' => [
                static fn(CryptoCurrency $currency): bool => $currency->price > 10000
            ]
        ];

        yield 'Filter with odd numbers and values greater than 1' => [
            'elements'   => [1, 2, 3, 4, 5, 6],
            'expected'   => [2 => 3, 4 => 5],
            'predicates' => [
                static fn(int $value): bool => $value > 1,
                static fn(int $value): bool => $value % 2 !== 0
            ]
        ];

        yield 'Filter with values greater than 2 and even numbers' => [
            'elements'   => [1, 2, 3, 4, 5, 6],
            'expected'   => [3 => 4, 5 => 6],
            'predicates' => [
                static fn(int $value): bool => $value > 2,
                static fn(int $value): bool => $value % 2 === 0
            ]
        ];

        yield 'Filter out negative numbers and keep values below 10' => [
            'elements'   => [-5, 0, 5, 10, 15],
            'expected'   => [1 => 0, 2 => 5],
            'predicates' => [
                static fn(int $value): bool => $value >= 0,
                static fn(int $value): bool => $value < 10
            ]
        ];
    }
}
