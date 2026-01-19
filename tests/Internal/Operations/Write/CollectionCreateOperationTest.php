<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Write;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Collection\Models\CryptoCurrency;
use TinyBlocks\Collection\Collection;

final class CollectionCreateOperationTest extends TestCase
{
    public function testEmptyCollectionShouldBeEmpty(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @Then it should be empty */
        self::assertTrue($collection->isEmpty());
    }

    public function testCreatingCollectionFromExistingCollection(): void
    {
        /** @Given a collection of cryptocurrencies */
        $collection = Collection::createFrom(elements: [
            new CryptoCurrency(name: 'Bitcoin', price: 60000.0, symbol: 'BTC'),
            new CryptoCurrency(name: 'Ethereum', price: 40000.0, symbol: 'ETH'),
        ]);

        /** @When creating another collection from the existing collection */
        $collectionB = Collection::createFrom(elements: $collection);

        /** @Then the new collection should have the same number of elements */
        self::assertCount($collection->count(), $collectionB);

        /** @And both collections should be equal */
        self::assertTrue($collectionB->equals(other: $collection));

        /** @And the elements in both collections should match */
        foreach ($collection as $index => $element) {
            self::assertEquals($element, $collectionB->getBy(index: $index));
        }

        /** @And the two collections should not be the same instance */
        self::assertNotSame($collection, $collectionB);
    }
}
