<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Compare;

use PHPUnit\Framework\TestCase;
use stdClass;
use TinyBlocks\Collection\Collection;
use TinyBlocks\Collection\Models\Amount;
use TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Models\Currency;

final class CollectionCompareOperationTest extends TestCase
{
    public function testCollectionsAreEqual(): void
    {
        /** @Given two collections with identical elements */
        $collectionA = Collection::from(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
        ]);

        $collectionB = Collection::from(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
        ]);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should be equal */
        self::assertTrue($actual);
    }

    public function testCollectionsAreNotEqual(): void
    {
        /** @Given two collections with different elements */
        $collectionA = Collection::from(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC')
        ]);

        $collectionB = Collection::from(elements: [
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH')
        ]);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should not be equal */
        self::assertFalse($actual);
    }

    public function testLargeCollectionsAreEqual(): void
    {
        /** @Given two large collections with identical elements */
        $collectionA = Collection::from(elements: range(1, 10000));
        $collectionB = Collection::from(elements: range(1, 10000));

        /** @When comparing the large collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should be equal */
        self::assertTrue($actual);
    }

    public function testCustomObjectComparison(): void
    {
        /** @Given two collections with custom object comparisons */
        $collectionA = Collection::from(elements: [
            new Amount(value: 100.50, currency: Currency::USD)
        ]);

        $collectionB = Collection::from(elements: [
            new Amount(value: 100.50, currency: Currency::USD)
        ]);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should be considered equal based on object comparison */
        self::assertTrue($actual);
    }

    public function testScalarAndNonScalarComparison(): void
    {
        /** @Given two collections where one has a scalar and the other has an object */
        $collectionA = Collection::from(elements: [1]);
        $collectionB = Collection::from(elements: [new stdClass()]);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should not be equal */
        self::assertFalse($actual);
    }

    public function testCollectionsWithMixedKeysAndValues(): void
    {
        /** @Given two collections with identical elements but different key orders */
        $collectionA = Collection::from(elements: [1, 'key' => 'value', 3.5]);
        $collectionB = Collection::from(elements: [1, 'key' => 'value', 3.5]);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should be equal */
        self::assertTrue($actual);
    }

    public function testCollectionsWithDifferentNullHandling(): void
    {
        /** @Given two collections where one contains null and the other is empty */
        $collectionA = Collection::from(elements: [null]);
        $collectionB = Collection::from(elements: []);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should not be equal */
        self::assertFalse($actual);
    }

    public function testSameElementsInDifferentOrderAreNotEqual(): void
    {
        /** @Given two collections with the same elements but in different orders */
        $collectionA = Collection::from(elements: [1, 2, 3]);
        $collectionB = Collection::from(elements: [3, 2, 1]);

        /** @When comparing the collections */
        $actual = $collectionA->equals(other: $collectionB);

        /** @Then the collections should not be equal */
        self::assertFalse($actual);
    }
}
