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
 * @template Key of int|string
 * @template Value of mixed
 * @implements IteratorAggregate<Key, Value>
 */
final class LazyIterator implements IteratorAggregate
{
    /**
     * @var LazyOperation[]
     */
    private array $operations;

    /**
     * @param iterable $elements
     * @param LazyOperation $operation
     */
    private function __construct(private readonly iterable $elements, LazyOperation $operation)
    {
        $this->operations[] = $operation;
    }

    /**
     * @param iterable $elements
     * @param LazyOperation $operation
     * @return LazyIterator
     */
    public static function from(iterable $elements, LazyOperation $operation): LazyIterator
    {
        return new LazyIterator(elements: $elements, operation: $operation);
    }

    /**
     * @param LazyOperation $operation
     * @return LazyIterator
     */
    public function add(LazyOperation $operation): LazyIterator
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
