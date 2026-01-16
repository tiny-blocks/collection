<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Operations\Retrieve;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionGetOperationTest extends TestCase
{
    public function testGetByIndexReturnsNullForZeroIndex(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When attempting to get an element at index zero */
        $actual = $collection->getBy(index: 0);

        /** @Then the result should be the expected element */
        self::assertSame($elements[0], $actual);
    }

    public function testGetByIndexReturnsElementAtValidIndex(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When attempting to get an element at a valid index */
        $actual = $collection->getBy(index: 1);

        /** @Then the result should be the expected element */
        self::assertSame($elements[1], $actual);
    }

    public function testGetByIndexReturnsNullForNegativeIndex(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ]);

        /** @When attempting to get an element at a negative index */
        $actual = $collection->getBy(index: -1);

        /** @Then the result should be null */
        self::assertNull($actual);
    }

    public function testGetByIndexReturnsDefaultValueWhenIndexIsNegative(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ]);

        /** @And a default value when the element is not found */
        $defaultValue = 'not-found';

        /** @When attempting to get an element at a negative index */
        $actual = $collection->getBy(index: -1, defaultValueIfNotFound: $defaultValue);

        /** @Then the default value should be returned */
        self::assertSame($defaultValue, $actual);
    }

    public function testGetByIndexReturnsNullForEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFrom(elements: []);

        /** @When attempting to get an element at any index */
        $actual = $collection->getBy(index: 0);

        /** @Then the result should be null */
        self::assertNull($actual);
    }

    public function testGetByIndexReturnsNullForOutOfRangeIndex(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: (float)rand(60000, 999999), symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: (float)rand(10000, 60000), symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: (float)rand(1000, 2000), symbol: 'BNB')
        ]);

        /** @When attempting to get an element at an out-of-range index */
        $actual = $collection->getBy(index: 10);

        /** @Then the result should be null */
        self::assertNull($actual);
    }

    public function testGetByIndexReturnsDefaultValueWhenElementExistsAndDefaultIsEqual(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When attempting to get an element at a valid index with a default return value equal to the element */
        $expected = $elements[1];
        $actual = $collection->getBy(index: 1, defaultValueIfNotFound: $expected);

        /** @Then the result should be the element at the given index */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('getByIndexDataProvider')]
    public function testGetByIndexReturnsExpectedValue(int $index, iterable $elements, mixed $expected): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: $elements);

        /** @When attempting to get an element at the specified index */
        $actual = $collection->getBy(index: $index);

        /** @Then the result should be the expected value */
        self::assertEquals($expected, $actual);
    }

    public static function getByIndexDataProvider(): iterable
    {
        yield 'Valid index' => [
            'index'    => 1,
            'elements' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
                new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
            ],
            'expected' => new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
        ];

        yield 'Negative index' => [
            'index'    => -1,
            'elements' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
                new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
            ],
            'expected' => null
        ];

        yield 'Empty elements' => [
            'index'    => 0,
            'elements' => [],
            'expected' => null
        ];

        yield 'Out of range index' => [
            'index'    => 5,
            'elements' => [
                new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
                new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
            ],
            'expected' => null
        ];
    }
}
