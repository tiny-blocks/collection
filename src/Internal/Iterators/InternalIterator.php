<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Iterators;

use Generator;
use IteratorAggregate;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

/**
 * A generator-based iterator that applies operations lazily to collections,
 * ensuring efficient memory usage by yielding elements on demand.
 *
 * @template Key
 * @template Value
 * @implements IteratorAggregate<Key, Value>
 */
final readonly class InternalIterator implements IteratorAggregate
{
    public function __construct(private iterable $elements, private ApplicableOperation $operation)
    {
    }

    public function apply(ApplicableOperation $operation): InternalIterator
    {
        return new InternalIterator(elements: $this, operation: $operation);
    }

    /**
     * @return Generator<Key, Value>
     */
    public function getIterator(): Generator
    {
        yield from $this->operation->apply(elements: $this->elements);
    }
}
