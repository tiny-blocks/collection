<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;

final class CollectionJoinToStringTest extends TestCase
{
    public function testJoinToStringWithSeparator(): void
    {
        /** @Given a collection of integers */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When joining the collection elements with a comma separator */
        $actual = $collection->joinToString(separator: ',');

        /** @Then the result should be a string with elements joined by the separator */
        self::assertSame('1,2,3', $actual);
    }

    public function testJoinToStringWithEmptyCollection(): void
    {
        /** @Given an empty collection */
        $collection = Collection::createFromEmpty();

        /** @When joining the empty collection elements with a separator */
        $actual = $collection->joinToString(separator: ',');

        /** @Then the result should be an empty string */
        self::assertSame('', $actual);
    }

    public function testJoinToStringWithCustomSeparator(): void
    {
        /** @Given a collection of strings */
        $collection = Collection::createFrom(elements: ['apple', 'banana', 'cherry']);

        /** @When joining the collection elements with a dash separator */
        $actual = $collection->joinToString(separator: '-');

        /** @Then the result should be a string with elements joined by the custom separator */
        self::assertSame('apple-banana-cherry', $actual);
    }

    public function testJoinToStringWithSingleElement(): void
    {
        /** @Given a collection with a single element */
        $collection = Collection::createFrom(elements: ['onlyOne']);

        /** @When joining the collection elements with a space separator */
        $actual = $collection->joinToString(separator: ',');

        /** @Then the result should be the single element as a string */
        self::assertSame('onlyOne', $actual);
    }

    public function testJoinToStringWithNonStringElements(): void
    {
        /** @Given a collection of mixed elements */
        $collection = Collection::createFrom(elements: [1, 2.5, true, null]);

        /** @When joining the collection elements with a comma separator */
        $actual = $collection->joinToString(separator: ',');

        /** @Then the result should be a string representation of the elements joined by the separator */
        self::assertSame('1,2.5,1,', $actual);
    }

    public function testJoinToStringWithSpaceSeparator(): void
    {
        /** @Given a collection of integers */
        $collection = Collection::createFrom(elements: [1, 2, 3]);

        /** @When joining the collection elements with a space separator */
        $actual = $collection->joinToString(separator: ' ');

        /** @Then the result should be a string with elements joined by the space separator */
        self::assertSame('1 2 3', $actual);
    }

    public function testJoinToStringWithStringElementsAndSpaceSeparator(): void
    {
        /** @Given a collection of strings */
        $collection = Collection::createFrom(elements: ['apple', 'banana', 'cherry']);

        /** @When joining the collection elements with a space separator */
        $actual = $collection->joinToString(separator: ' ');

        /** @Then the result should be a string with elements joined by the space separator */
        self::assertSame('apple banana cherry', $actual);
    }
}
