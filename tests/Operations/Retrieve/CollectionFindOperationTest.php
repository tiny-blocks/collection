<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Operations\Retrieve;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionFindOperationTest extends TestCase
{
    public function testFindByInEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When attempting to find any element */
        $actual = $collection->findBy(predicates: static fn(mixed $element): bool => true);

        /** @Then the result should be null */
        self::assertNull($actual);
    }

    public function testFindByReturnsNullWhenNoMatch(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ]);

        /** @When attempting to find an element that doesn't match any condition */
        $actual = $collection->findBy(predicates: static fn(CryptoCurrency $element): bool => $element->symbol === 'XRP'
        );

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
        $collection = Collection::createFrom(elements: $elements);

        /** @When attempting to find the first element matching multiple predicates */
        $actual = $collection->findBy(
            static fn(CryptoCurrency $element): bool => $element->symbol === 'BNB',
            static fn(CryptoCurrency $element): bool => $element->price < 2000.0
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
        $collection = Collection::createFrom(elements: $elements);

        /** @When attempting to find the first matching element */
        $actual = $collection->findBy(predicates: static fn(CryptoCurrency $element): bool => $element->symbol === 'ETH'
        );

        /** @Then the result should be the expected element */
        self::assertSame($elements[1], $actual);
    }

    public function testFindByWithMultiplePredicatesReturnsNullWhenNoMatch(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
            new CryptoCurrency(name: 'Binance Coin', price: 1500.0, symbol: 'BNB')
        ]);

        /** @When attempting to find an element matching multiple predicates that do not match */
        $actual = $collection->findBy(
            static fn(CryptoCurrency $element): bool => $element->symbol === 'XRP',
            static fn(CryptoCurrency $element): bool => $element->price < 1000.0
        );

        /** @Then the result should be null */
        self::assertNull($actual);
    }
}
