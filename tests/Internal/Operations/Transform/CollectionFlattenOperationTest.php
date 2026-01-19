<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Transform;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;

final class CollectionFlattenOperationTest extends TestCase
{
    public function testFlattenEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When flattening the empty collection */
        $actual = $collection->flatten();

        /** @Then the collection should remain empty */
        self::assertEmpty($actual->toArray());
    }

    public function testFlattenNestedCollections(): void
    {
        /** @Given a collection of nested collections */
        $collection = Collection::createFrom(elements: [
            Collection::createFrom(elements: [1, 2]),
            Collection::createFrom(elements: [3, 4]),
            Collection::createFrom(elements: [5, 6])
        ]);

        /** @When flattening the collection */
        $actual = $collection->flatten();

        /** @Then the collection should contain all elements in a single collection */
        self::assertSame([1, 2, 3, 4, 5, 6], $actual->toArray());
    }

    public function testFlattenNonNestedCollection(): void
    {
        /** @Given a collection without any nested elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When flattening the collection */
        $actual = $collection->flatten();

        /** @Then the collection should remain unchanged */
        self::assertSame([1, 2, 3], $actual->toArray());
    }

    public function testFlattenWithMixedNestedElements(): void
    {
        /** @Given a collection with mixed nested and non-nested elements */
        $collection = Collection::createFrom(elements: [
            1,
            Collection::createFrom(elements: [2, 3]),
            4,
            Collection::createFrom(elements: [5, 6])
        ]);

        /** @When flattening the collection */
        $actual = $collection->flatten();

        /** @Then the collection should contain all elements in a single collection */
        self::assertSame([1, 2, 3, 4, 5, 6], $actual->toArray());
    }
}
