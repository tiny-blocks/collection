<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Iterators;

use Generator;
use IteratorAggregate;

/**
 * A simple iterator that directly yields elements from given iterable.
 * Provides a lazy iteration mechanism to process elements on demand.
 *
 * @template Key
 * @template Value
 * @implements IteratorAggregate<Key, Value>
 */
final readonly class IterableIteratorAggregate implements IteratorAggregate
{
    public function __construct(private iterable $elements)
    {
    }

    /**
     * @return Generator<Key, Value>
     */
    public function getIterator(): Generator
    {
        yield from $this->elements;
    }
}
