<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use TinyBlocks\Collection\Internal\Iterators\InternalIterator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;
use TinyBlocks\Collection\Internal\Operations\Compare\Equals;
use TinyBlocks\Collection\Internal\Operations\Filter\Filter;
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Internal\Operations\Order\Sort;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Find;
use TinyBlocks\Collection\Internal\Operations\Retrieve\First;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Get;
use TinyBlocks\Collection\Internal\Operations\Retrieve\Last;
use TinyBlocks\Collection\Internal\Operations\Transform\Each;
use TinyBlocks\Collection\Internal\Operations\Transform\Map;
use TinyBlocks\Collection\Internal\Operations\Transform\MapToArray;
use TinyBlocks\Collection\Internal\Operations\Transform\MapToJson;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;
use TinyBlocks\Collection\Internal\Operations\Write\Add;
use TinyBlocks\Collection\Internal\Operations\Write\Create;
use TinyBlocks\Collection\Internal\Operations\Write\Remove;
use TinyBlocks\Collection\Internal\Operations\Write\RemoveAll;
use Traversable;

/**
 * Represents a collection that provides a set of utility methods for operations like adding,
 * filtering, mapping, and transforming elements. Internally uses iterators to apply operations
 * lazily and efficiently.
 *
 * @template Key of array-key
 * @template Value
 * @implements Collectible<Key, Value>
 */
class Collection implements Collectible
{
    private InternalIterator $iterator;

    private function __construct(ApplicableOperation $operation, iterable $elements = [])
    {
        $this->iterator = new InternalIterator(elements: $elements, operation: $operation);
    }

    public static function createFrom(iterable $elements): Collectible
    {
        return new Collection(operation: Create::fromEmpty(), elements: $elements);
    }

    public static function createFromEmpty(): Collectible
    {
        return new Collection(operation: Create::fromEmpty());
    }

    public function add(mixed ...$elements): Collectible
    {
        $operation = Add::from(newElements: $elements);
        $this->iterator = $this->iterator->apply(operation: $operation);
        return $this;
    }

    public function count(): int
    {
        return iterator_count($this->iterator);
    }

    public function each(Closure ...$actions): Collectible
    {
        Each::from(...$actions)->execute(elements: $this->iterator);
        return $this;
    }

    public function equals(Collectible $other): bool
    {
        return Equals::from(elements: $this->iterator)->compareAll(other: $other);
    }

    public function filter(?Closure ...$predicates): Collectible
    {
        $operation = Filter::from(...$predicates);
        $this->iterator = $this->iterator->apply(operation: $operation);
        return $this;
    }

    public function findBy(Closure ...$predicates): mixed
    {
        return Find::from(elements: $this->iterator)->firstMatchingElement(...$predicates);
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        return First::from(elements: $this->iterator)->element(defaultValueIfNotFound: $defaultValueIfNotFound);
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

    public function isEmpty(): bool
    {
        $iterator = $this->iterator->getIterator();
        return !$iterator->valid();
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        return Last::from(elements: $this->iterator)->element(defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function map(Closure ...$transformations): Collectible
    {
        $operation = Map::from(...$transformations);
        $this->iterator = $this->iterator->apply(operation: $operation);
        return $this;
    }

    public function remove(mixed $element): Collectible
    {
        $operation = Remove::from(element: $element);
        $this->iterator = $this->iterator->apply(operation: $operation);
        return $this;
    }

    public function removeAll(?Closure $filter = null): Collectible
    {
        $operation = RemoveAll::from(filter: $filter);
        $this->iterator = $this->iterator->apply(operation: $operation);
        return $this;
    }

    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $predicate = null): Collectible
    {
        $operation = Sort::from(order: $order, predicate: $predicate);
        $this->iterator = $this->iterator->apply(operation: $operation);
        return $this;
    }

    public function toArray(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): array
    {
        return MapToArray::from(elements: $this->iterator->getIterator(), preserveKeys: $preserveKeys)->toArray();
    }

    public function toJson(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): string
    {
        return MapToJson::from(elements: $this->iterator->getIterator(), preserveKeys: $preserveKeys)->toJson();
    }
}
