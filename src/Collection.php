<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use TinyBlocks\Collection\Internal\Iterators\LazyIterator;
use TinyBlocks\Collection\Internal\Operations\Aggregate\Reduce;
use TinyBlocks\Collection\Internal\Operations\Compare\Contains;
use TinyBlocks\Collection\Internal\Operations\Compare\Equals;
use TinyBlocks\Collection\Internal\Operations\Filter\Filter;
use TinyBlocks\Collection\Internal\Operations\Order\Sort;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Find;
use TinyBlocks\Collection\Internal\Operations\Retrieve\First;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Get;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Last;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Slice;
use TinyBlocks\Collection\Internal\Operations\Transform\Each;
use TinyBlocks\Collection\Internal\Operations\Transform\Flatten;
use TinyBlocks\Collection\Internal\Operations\Transform\GroupBy;
use TinyBlocks\Collection\Internal\Operations\Transform\JoinToString;
use TinyBlocks\Collection\Internal\Operations\Transform\Map;
use TinyBlocks\Collection\Internal\Operations\Write\Add;
use TinyBlocks\Collection\Internal\Operations\Write\Create;
use TinyBlocks\Collection\Internal\Operations\Write\Remove;
use TinyBlocks\Collection\Internal\Operations\Write\RemoveAll;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;
use Traversable;

/**
 * Represents a collection that provides a set of utility methods for operations like adding,
 * filtering, mapping, and transforming elements. Internally uses iterators to apply operations
 * lazily and efficiently.
 */
class Collection implements Collectible, IterableMapper
{
    use IterableMappability;

    private LazyIterator $iterator;

    private function __construct(LazyIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public static function createFrom(iterable $elements): static
    {
        return new static(iterator: LazyIterator::from(elements: $elements, operation: Create::fromEmpty()));
    }

    public static function createFromEmpty(): static
    {
        return self::createFrom(elements: []);
    }

    public function add(mixed ...$elements): static
    {
        return new static(iterator: $this->iterator->add(operation: Add::from(newElements: $elements)));
    }

    public function contains(mixed $element): bool
    {
        return Contains::from(elements: $this->iterator)->exists(element: $element);
    }

    public function count(): int
    {
        return iterator_count($this->iterator);
    }

    public function each(Closure ...$actions): static
    {
        Each::from(...$actions)->execute(elements: $this->iterator);
        return $this;
    }

    public function equals(Collectible $other): bool
    {
        return Equals::from(elements: $this->iterator)->compareAll(other: $other);
    }

    public function filter(?Closure ...$predicates): static
    {
        return new static(iterator: $this->iterator->add(operation: Filter::from(...$predicates)));
    }

    public function findBy(Closure ...$predicates): mixed
    {
        return Find::from(elements: $this->iterator)->firstMatchingElement(...$predicates);
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        return First::from(elements: $this->iterator)->element(defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function flatten(): static
    {
        return new static(iterator: $this->iterator->add(operation: Flatten::instance()));
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        return Get::from(elements: $this->iterator)->elementAtIndex(
            index: $index,
            defaultValueIfNotFound: $defaultValueIfNotFound
        );
    }

    public function getIterator(): Traversable
    {
        yield from $this->iterator->getIterator();
    }

    public function groupBy(Closure $grouping): Collectible
    {
        return new static(iterator: $this->iterator->add(operation: GroupBy::from(grouping: $grouping)));
    }

    public function isEmpty(): bool
    {
        return !$this->iterator->getIterator()->valid();
    }

    public function joinToString(string $separator): string
    {
        return JoinToString::from(elements: $this->iterator)->joinTo(separator: $separator);
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        return Last::from(elements: $this->iterator)->element(defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function map(Closure ...$transformations): static
    {
        return new static(iterator: $this->iterator->add(operation: Map::from(...$transformations)));
    }

    public function remove(mixed $element): static
    {
        return new static(iterator: $this->iterator->add(operation: Remove::from(element: $element)));
    }

    public function removeAll(?Closure $filter = null): static
    {
        return new static(iterator: $this->iterator->add(operation: RemoveAll::from(filter: $filter)));
    }

    public function reduce(Closure $aggregator, mixed $initial): mixed
    {
        return Reduce::from(elements: $this->iterator)->execute(aggregator: $aggregator, initial: $initial);
    }

    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $predicate = null): static
    {
        return new static(
            iterator: $this->iterator->add(
                operation: Sort::from(order: $order, predicate: $predicate)
            )
        );
    }

    public function slice(int $index, int $length = -1): static
    {
        return new static(
            iterator: $this->iterator->add(
                operation: Slice::from(index: $index, length: $length)
            )
        );
    }
}
