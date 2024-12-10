<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\PreserveKeys;

final class CollectionFilterOperationTest extends TestCase
{
    public function testFilterAppliesMultiplePredicates(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3, 4, 5, 6]);

        /**
         * @When filtering the collection with three predicates,
         * the first predicate filters out values less than or equal to 2,
         * the second predicate keeps only even numbers,
         * and the third predicate filters out values greater than or equal to 6.
         */
        $actual = $collection->filter(
            static fn(int $value): bool => $value > 2,
            static fn(int $value): bool => $value % 2 === 0,
            static fn(int $value): bool => $value < 6
        );

        /** @Then the filtered collection should discard the keys and return the expected values */
        self::assertSame([4], $actual->toArray(preserveKeys: PreserveKeys::DISCARD));
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
}
