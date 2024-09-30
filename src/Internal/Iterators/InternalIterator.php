<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Iterators;

use Generator;
use IteratorAggregate;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

/**
 * A generator-based iterator that applies operations lazily to collections,
 * ensuring efficient memory usage by yielding elements on demand.
 *
 * @template Key
 * @template Value
 * @implements IteratorAggregate<Key, Value>
 */
final class InternalIterator implements IteratorAggregate
{
    /**
     * @param iterable $elements
     * @param iterable<LazyOperation> $operations
     */
    private function __construct(private readonly iterable $elements, private iterable $operations)
    {
    }

    /**
     * @param iterable $elements
     * @param LazyOperation ...$operations
     * @return InternalIterator
     */
    public static function from(iterable $elements, LazyOperation ...$operations): InternalIterator
    {
        return new InternalIterator(elements: $elements, operations: $operations);
    }

    /**
     * @param LazyOperation $operation
     * @return InternalIterator
     */
    public function add(LazyOperation $operation): InternalIterator
    {
        $this->operations[] = $operation;
        return $this;
    }

    /**
     * @return Generator<Key, Value>
     */
    public function getIterator(): Generator
    {
        $currentElements = $this->elements;

        foreach ($this->operations as $operation) {
            $currentElements = $operation->apply(elements: $currentElements);
        }

        yield from $currentElements;
    }
}
