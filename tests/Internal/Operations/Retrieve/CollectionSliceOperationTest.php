<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Retrieve;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionSliceOperationTest extends TestCase
{
    public function testSliceReturnsSubsetOfElements(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB'),
            new CryptoCurrency(name: 'Cardano', price: 2.0, symbol: 'ADA')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When slicing the collection */
        $actual = $collection->slice(index: 1, length: 2);

        /** @Then the result should contain the sliced elements */
        self::assertSame([
            1 => $elements[1]->toArray(),
            2 => $elements[2]->toArray()
        ], $actual->toArray());
    }

    public function testSliceReturnsEmptyWhenIndexExceedsCollectionSize(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When slicing the collection with an index that exceeds the collection size */
        $actual = $collection->slice(index: 5, length: 2);

        /** @Then the result should be an empty array */
        self::assertEmpty($actual->toArray());
    }

    public function testSliceWithZeroLengthReturnsEmpty(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When slicing with length 0 */
        $actual = $collection->slice(index: 1, length: 0);

        /** @Then the result should be an empty array */
        self::assertEmpty($actual->toArray());
    }

    public function testSliceWithLengthOne(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When slicing with length 1 */
        $actual = $collection->slice(index: 1, length: 1);

        /** @Then the result should contain only one element */
        self::assertSame([1 => $elements[1]->toArray()], $actual->toArray());
    }

    public function testSliceWithNegativeTwoLength(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB'),
            new CryptoCurrency(name: 'Cardano', price: 2.0, symbol: 'ADA')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When slicing with length -2 */
        $actual = $collection->slice(index: 1, length: -2);

        /** @Then the result should contain only the first element after the index */
        self::assertSame([1 => $elements[1]->toArray()], $actual->toArray());
    }

    public function testSliceWithoutPassingLength(): void
    {
        /** @Given a collection of CryptoCurrency objects */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB'),
            new CryptoCurrency(name: 'Cardano', price: 2.0, symbol: 'ADA')
        ];
        $collection = Collection::createFrom(elements: $elements);

        /** @When slicing without a passing length (defaults to -1) */
        $actual = $collection->slice(index: 1);

        /** @Then the result should contain all elements starting from the index */
        self::assertSame([
            1 => $elements[1]->toArray(),
            2 => $elements[2]->toArray(),
            3 => $elements[3]->toArray()
        ], $actual->toArray());
    }
}
