<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Retrieve;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\CryptoCurrency;

final class CollectionFirstOperationTest extends TestCase
{
    public function testFirstReturnsFirstElement(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::from(elements: $elements);

        /** @When retrieving the first element */
        $actual = $collection->first();

        /** @Then the result should be the first CryptoCurrency object */
        self::assertSame($elements[0], $actual);
    }

    public function testFirstReturnsDefaultValueWhenCollectionIsEmpty(): void
    {
        /** @Given an empty collection */
        $collection = Collection::fromEmpty();

        /** @When attempting to get the first element */
        $actual = $collection->first(defaultValueIfNotFound: 'default');

        /** @Then the result should be the default value */
        self::assertSame('default', $actual);
    }

    public function testFirstReturnsNullWhenCollectionIsEmptyWithoutDefaultValue(): void
    {
        /** @Given an empty collection */
        $collection = Collection::fromEmpty();

        /** @When retrieving the first element without a default value */
        $actual = $collection->first();

        /** @Then the first element should be null */
        self::assertNull($actual);
    }
}
