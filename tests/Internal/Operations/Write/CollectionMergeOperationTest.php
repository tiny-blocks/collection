<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Operations\Write;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;

final class CollectionMergeOperationTest extends TestCase
{
    public function testMergeEmptyCollections(): void
    {
        /** @Given two empty collections */
        $collectionA = Collection::createFromEmpty();
        $collectionB = Collection::createFromEmpty();

        /** @When merging the two collections */
        $actual = $collectionA->merge(other: $collectionB);

        /** @Then the result should be an empty collection */
        self::assertEmpty($actual->toArray());
    }

    public function testMergeIntoEmptyCollection(): void
    {
        /** @Given an empty collection and a non-empty collection */
        $collectionA = Collection::createFromEmpty();
        $collectionB = Collection::createFrom(elements: [4, 5, 6]);

        /** @When merging the non-empty collection into the empty one */
        $actual = $collectionA->merge(other: $collectionB);

        /** @Then the result should contain only the elements from the non-empty collection */
        self::assertSame([4, 5, 6], $actual->toArray());
    }

    public function testMergeWithEmptyCollection(): void
    {
        /** @Given a non-empty collection and an empty collection */
        $collectionA = Collection::createFrom(elements: [1, 2, 3]);
        $collectionB = Collection::createFromEmpty();

        /** @When merging the empty collection into the non-empty one */
        $actual = $collectionA->merge(other: $collectionB);

        /** @Then the result should contain only the original elements */
        self::assertSame([1, 2, 3], $actual->toArray());
    }

    public function testMergeTwoCollections(): void
    {
        /** @Given two collections with distinct elements */
        $collectionA = Collection::createFrom(elements: [1, 2, 3]);
        $collectionB = Collection::createFrom(elements: [4, 5, 6]);

        /** @When merging collection B into collection A */
        $actual = $collectionA->merge(other: $collectionB);

        /** @Then the result should contain all elements in order */
        self::assertSame([1, 2, 3, 4, 5, 6], $actual->toArray());
    }

    public function testMergePreservesLazyEvaluation(): void
    {
        /** @Given two collections created from generators */
        $collectionA = Collection::createFrom(
            elements: (static function () {
            yield 1;
            yield 2;
        })()
        );

        $collectionB = Collection::createFrom(
            elements: (static function () {
            yield 3;
            yield 4;
        })()
        );

        /** @When merging and retrieving only the first element */
        $actual = $collectionA->merge(other: $collectionB)->first();

        /** @Then the first element should be from collection A without materializing all elements */
        self::assertSame(1, $actual);
    }

    public function testMergeMultipleCollections(): void
    {
        /** @Given three collections */
        $collectionA = Collection::createFrom(elements: [1, 2]);
        $collectionB = Collection::createFrom(elements: [3, 4]);
        $collectionC = Collection::createFrom(elements: [5, 6]);

        /** @When chaining multiple merge operations */
        $actual = $collectionA
            ->merge(other: $collectionB)
            ->merge(other: $collectionC);

        /** @Then the result should contain all elements in order */
        self::assertSame([1, 2, 3, 4, 5, 6], $actual->toArray());
    }

    public function testMergeWithDuplicateElements(): void
    {
        /** @Given two collections with overlapping elements */
        $collectionA = Collection::createFrom(elements: [1, 2, 3]);
        $collectionB = Collection::createFrom(elements: [3, 4, 5]);

        /** @When merging the collections */
        $actual = $collectionA->merge(other: $collectionB);

        /** @Then the result should contain all elements including duplicates */
        self::assertSame([1, 2, 3, 3, 4, 5], $actual->toArray());
    }
}
