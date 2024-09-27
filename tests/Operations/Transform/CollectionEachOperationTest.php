<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Operations\Transform;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Collection;

final class CollectionEachOperationTest extends TestCase
{
    public function testEachWithMultipleActions(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::from(elements: [1, 2, 3]);

        /** @When executing multiple actions */
        $collection->each(
            fn(int &$value): int => $value += 1,
            fn(int &$value): int => $value *= 2
        );

        /** @Then the collection contains the modified elements */
        self::assertSame([4, 6, 8], $collection->toArray());
    }

    public function testRemoveActionOnFalseReturn(): void
    {
        /** @Given a collection with elements */
        $collection = Collection::from(elements: [1, 2, 3]);

        /** @When executing multiple actions where one action returns false */
        $collection->each(
            fn(int $value): bool => $value < 1,
            fn(int &$value): int => $value *= 2
        );

        /** @Then only the remaining action is processed */
        self::assertSame([2, 4, 6], $collection->toArray());
    }

    public function testPreserveKeysWithMultipleActions(): void
    {
        /** @Given a collection with associative array elements */
        $collection = Collection::from(elements: ['a' => 1, 'b' => 2, 'c' => 3]);

        /** @When executing multiple actions */
        $collection->each(
            fn(int &$value, string $key): int => $value *= 2,
            fn(int &$value, string $key): int => $value += 1
        );

        /** @Then the collection contains modified elements with preserved keys */
        self::assertSame(['a' => 3, 'b' => 5, 'c' => 7], $collection->toArray());
    }
}
