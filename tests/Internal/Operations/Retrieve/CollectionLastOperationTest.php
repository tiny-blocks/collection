<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Retrieve;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SplDoublyLinkedList;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionLastOperationTest extends TestCase
{
    public function testLastReturnsLastElement(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When retrieving the last element */
        $actual = $collection->last();

        /** @Then the result should be the last CryptoCurrency object */
        self::assertSame($elements[2], $actual);
    }

    public function testLastReturnsNullWhenLastElementIsNull(): void
    {
        /** @Given a collection whose last element is null */
        $collection = Collection::createFrom(elements: ['value', null]);

        /** @When retrieving the last element with a default value */
        $actual = $collection->last(defaultValueIfNotFound: 'default');

        /** @Then the last element should be null */
        self::assertNull($actual);
    }

    public function testLastReturnsDefaultValueWhenCollectionIsEmpty(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When attempting to get the last element */
        $actual = $collection->last(defaultValueIfNotFound: 'default');

        /** @Then the result should be the default value */
        self::assertSame('default', $actual);
    }

    public function testLastReturnsLastElementFromSplDoublyLinkedList(): void
    {
        /** @Given a collection created from a SplDoublyLinkedList */
        $elements = new SplDoublyLinkedList();
        $elements->push('first');
        $elements->push('second');
        $elements->push('third');
        $collection = Collection::createFrom(elements: $elements);

        /** @When retrieving the last element */
        $actual = $collection->last();

        /** @Then the result should be the last value */
        self::assertSame('third', $actual);
    }

    public function testLastReturnsLastElementFromArrayAccessCountableIterable(): void
    {
        /** @Given a collection created from an ArrayIterator */
        $collection = Collection::createFrom(elements: new ArrayIterator(['alpha', 'beta', 'gamma']));

        /** @When retrieving the last element */
        $actual = $collection->last();

        /** @Then the result should be the last value */
        self::assertSame('gamma', $actual);
    }

    public function testLastReturnsNullWhenCollectionIsEmptyWithoutDefaultValue(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When retrieving the last element without a default value */
        $actual = $collection->last();

        /** @Then the last element should be null */
        self::assertNull($actual);
    }
}
