<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations;

use Generator;

/**
 * Defines operations applied to the collection.
 *
 * @template Key of array-key
 * @template Value
 * @extends Operation
 */
interface ApplicableOperation extends Operation
{
    /**
     * Apply the operation to the given elements.
     *
     * @param iterable $elements The collection of elements to apply the operation on.
     *
     * @return Generator<Key, Value> A generator that yields the results of applying the operation.
     */
    public function apply(iterable $elements): Generator;
}
