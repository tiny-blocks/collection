<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Retrieve;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\CryptoCurrency;

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
        $collection = Collection::from(elements: $elements);

        /** @When retrieving the last element */
        $actual = $collection->last();

        /** @Then the result should be the last CryptoCurrency object */
        self::assertSame($elements[2], $actual);
    }

    public function testLastReturnsDefaultValueWhenCollectionIsEmpty(): void
    {
        /** @Given an empty collection */
        $collection = Collection::fromEmpty();

        /** @When attempting to get the last element */
        $actual = $collection->last(defaultValueIfNotFound: 'default');

        /** @Then the result should be the default value */
        self::assertSame('default', $actual);
    }

    public function testLastReturnsNullWhenCollectionIsEmptyWithoutDefaultValue(): void
    {
        /** @Given an empty collection */
        $collection = Collection::fromEmpty();

        /** @When retrieving the last element without a default value */
        $actual = $collection->last();

        /** @Then the last element should be null */
        self::assertNull($actual);
    }
}
