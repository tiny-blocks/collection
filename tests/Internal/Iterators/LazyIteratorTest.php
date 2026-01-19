<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Collection\Internal\Iterators;

use Generator;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Collection\Internal\Iterators\LazyIterator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final class LazyIteratorTest extends TestCase
{
    public function testFromAppliesInitialOperation(): void
    {
        /** @Given elements and an operation that changes values */
        $elements = [1, 2, 3];

        $operation = new class implements LazyOperation {
            public function apply(iterable $elements): Generator
            {
                foreach ($elements as $key => $value) {
                    yield $key => $value * 2;
                }
            }
        };

        /** @When creating a LazyIterator from the elements and operation */
        $iterator = LazyIterator::from(elements: $elements, operation: $operation);

        /** @Then the yielded elements should include the operation effect */
        self::assertSame([2, 4, 6], iterator_to_array($iterator));
    }
}
