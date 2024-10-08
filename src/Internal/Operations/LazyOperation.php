<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations;

use Generator;

/**
 * Defines operations that are applied lazily to the collection.
 *
 * @template Key of array-key
 * @template Value
 */
interface LazyOperation extends Operation
{
    /**
     * Apply the operation lazily to the given elements.
     *
     * @param iterable<Key, Value> $elements The collection of elements to apply the operation on.
     * @return Generator<Key, Value> A generator that yields the results of applying the operation.
     */
    public function apply(iterable $elements): Generator;
}
