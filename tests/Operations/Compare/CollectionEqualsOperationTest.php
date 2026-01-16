<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Operations\Compare;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionEqualsOperationTest extends TestCase
{
    #[DataProvider('collectionsEqualDataProvider')]
    public function testCollectionsAreEqual(iterable $elementsA, iterable $elementsB): void
    {
        /** @Given two collections */
        $collectionA = Collection::createFrom(elements: $elementsA);
        $collectionB = Collection::createFrom(elements: $elementsB);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should be equal */
        self::assertTrue($actual);
    }

    #[DataProvider('collectionsNotEqualDataProvider')]
    public function testCollectionsAreNotEqual(iterable $elementsA, iterable $elementsB): void
    {
        /** @Given two collections */
        $collectionA = Collection::createFrom(elements: $elementsA);
        $collectionB = Collection::createFrom(elements: $elementsB);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should not be equal */
        self::assertFalse($actual);
    }

    public static function collectionsEqualDataProvider(): iterable
    {
        yield 'Collections are equal' => [
            'elementsA' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
            ],
            'elementsB' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
            ]
        ];

        yield 'Collections with mixed keys and values' => [
            'elementsA' => [1, 'key' => 'value', 3.5],
            'elementsB' => [1, 'key' => 'value', 3.5]
        ];
    }

    public static function collectionsNotEqualDataProvider(): iterable
    {
        yield 'Collections are not equal' => [
            'elementsA' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC')
            ],
            'elementsB' => [
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
            ]
        ];

        yield 'Scalar and non-scalar comparison' => [
            'elementsA' => [1],
            'elementsB' => [new stdClass()]
        ];

        yield 'Collections with different null handling' => [
            'elementsA' => [null],
            'elementsB' => []
        ];

        yield 'Same elements in different order are not equal' => [
            'elementsA' => [1, 2, 3],
            'elementsB' => [3, 2, 1]
        ];
    }
}
