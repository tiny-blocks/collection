<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;

final class CollectionIteratorTest extends TestCase
{
    public function testIteratorShouldBeReusedIfNoModification(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When retrieving the iterator for the first time */
        $iterator = $collection->getIterator();

        /** @And calling a method that does not modify the collection */
        $count = $collection->count();

        /** @Then the iterator should not be the same (due to lazy generation) */
        self::assertSame(3, $count);
        self::assertNotSame($iterator, $collection->getIterator());

        /** @And the collection should remain unchanged */
        self::assertSame([1, 2, 3], iterator_to_array($collection->getIterator()));
    }

    public function testIteratorShouldBeRecreatedAfterModification(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When retrieving the iterator for the first time */
        $iterator = $collection->getIterator();

        /** @And modifying the collection */
        $collection->add(elements: 4);

        /** @Then the iterator should be recreated */
        self::assertSame(4, $collection->count());
        self::assertNotSame($iterator, $collection->getIterator());

        /** @And the elements should be correct after modification */
        self::assertSame([1, 2, 3, 4], iterator_to_array($collection->getIterator()));
    }

    public function testIteratorRemainsUnchangedWithUnrelatedOperations(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When performing operations that do not modify the collection */
        $firstIterator = $collection->getIterator();

        /** @Then the iterator should remain unchanged */
        self::assertFalse($collection->isEmpty());
        self::assertSame([1, 2, 3], iterator_to_array($firstIterator));
        self::assertSame([1, 2, 3], iterator_to_array($collection->getIterator()));
    }
}
