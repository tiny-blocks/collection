<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Retrieve;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\CryptoCurrency;

final class CollectionFindOperationTest extends TestCase
{
    public function testFindByInEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::from(elements: []);

        /** @When attempting to find any element */
        $actual = $collection->findBy(fn(mixed $element): bool => true);

        /** @Then the result should be null */
        self::assertNull($actual);
    }

    public function testFindByReturnsNullWhenNoMatch(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::from(elements: $elements);

        /** @When attempting to find an element that doesn't match any condition */
        $actual = $collection->findBy(fn(CryptoCurrency $element): bool => $element->symbol === 'XRP');

        /** @Then the result should be null */
        self::assertNull($actual);
    }

    public function testFindByWithMultiplePredicates(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::from(elements: $elements);

        /** @When attempting to find the first element matching multiple predicates */
        $actual = $collection->findBy(
            fn(CryptoCurrency $element): bool => $element->symbol === 'BNB',
            fn(CryptoCurrency $element): bool => $element->price < 2000.0
        );

        /** @Then the result should be the expected element */
        self::assertSame($elements[2], $actual);
    }

    public function testFindByReturnsFirstMatchingElement(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::from(elements: $elements);

        /** @When attempting to find the first matching element */
        $actual = $collection->findBy(fn(CryptoCurrency $element): bool => $element->symbol === 'ETH');

        /** @Then the result should be the expected element */
        self::assertSame($elements[1], $actual);
    }

    public function testFindByWithMultiplePredicatesReturnsNullWhenNoMatch(): void
    {
        /** @Given a collection with elements */
        $elements = [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ];
        $collection = Collection::from(elements: $elements);

        /** @When attempting to find an element matching multiple predicates that do not match */
        $actual = $collection->findBy(
            fn(CryptoCurrency $element): bool => $element->symbol === 'XRP',
            fn(CryptoCurrency $element): bool => $element->price < 1000.0
        );

        /** @Then the result should be null */
        self::assertNull($actual);
    }
}
