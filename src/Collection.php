<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use TinyBlocks\Collection\Internal\EagerPipeline;
use TinyBlocks\Collection\Internal\LazyPipeline;
use TinyBlocks\Collection\Internal\Operations\Operation;
use TinyBlocks\Collection\Internal\Operations\Resolving\Each;
use TinyBlocks\Collection\Internal\Operations\Resolving\Equality;
use TinyBlocks\Collection\Internal\Operations\Resolving\Find;
use TinyBlocks\Collection\Internal\Operations\Resolving\First;
use TinyBlocks\Collection\Internal\Operations\Resolving\Get;
use TinyBlocks\Collection\Internal\Operations\Resolving\Join;
use TinyBlocks\Collection\Internal\Operations\Resolving\Last;
use TinyBlocks\Collection\Internal\Operations\Resolving\Reduce;
use TinyBlocks\Collection\Internal\Operations\Transforming\Add;
use TinyBlocks\Collection\Internal\Operations\Transforming\Filter;
use TinyBlocks\Collection\Internal\Operations\Transforming\FlatMap;
use TinyBlocks\Collection\Internal\Operations\Transforming\GroupInto;
use TinyBlocks\Collection\Internal\Operations\Transforming\Map;
use TinyBlocks\Collection\Internal\Operations\Transforming\Merge;
use TinyBlocks\Collection\Internal\Operations\Transforming\Rearrange;
use TinyBlocks\Collection\Internal\Operations\Transforming\Remove;
use TinyBlocks\Collection\Internal\Operations\Transforming\RemoveAll;
use TinyBlocks\Collection\Internal\Operations\Transforming\Segment;
use TinyBlocks\Collection\Internal\Pipeline;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;
use Traversable;

/**
 * Extensible, type-safe collection with a fluent API.
 *
 * Designed as the primary extension point — domain collections should
 * extend this class to inherit all collection behavior:
 *
 *     final class Orders extends Collection { }
 */
class Collection implements Collectible, IterableMapper
{
    use IterableMappability;

    private function __construct(private readonly Pipeline $pipeline)
    {
    }

    public static function createFrom(iterable $elements): static
    {
        return new static(pipeline: EagerPipeline::from(source: $elements));
    }

    public static function createFromEmpty(): static
    {
        return static::createFrom(elements: []);
    }

    public static function createLazyFrom(iterable $elements): static
    {
        return new static(pipeline: LazyPipeline::from(source: $elements));
    }

    public static function createLazyFromEmpty(): static
    {
        return static::createLazyFrom(elements: []);
    }

    public function getIterator(): Traversable
    {
        yield from $this->pipeline->process();
    }

    public function add(mixed ...$elements): static
    {
        return $this->pipeTo(operation: Add::these(newElements: $elements));
    }

    public function merge(Collectible $other): static
    {
        return $this->pipeTo(operation: Merge::with(other: $other));
    }

    public function contains(mixed $element): bool
    {
        return Equality::exists(elements: $this, element: $element);
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    public function findBy(Closure ...$predicates): mixed
    {
        return Find::firstMatch(elements: $this, predicates: $predicates);
    }

    public function each(Closure ...$actions): static
    {
        Each::execute(elements: $this, actions: $actions);

        return $this;
    }

    public function equals(Collectible $other): bool
    {
        return Equality::compareAll(elements: $this, other: $other);
    }

    public function remove(mixed $element): static
    {
        return $this->pipeTo(operation: Remove::element(element: $element));
    }

    public function removeAll(?Closure $predicate = null): static
    {
        return $this->pipeTo(operation: RemoveAll::matching(predicate: $predicate));
    }

    public function filter(?Closure ...$predicates): static
    {
        return $this->pipeTo(operation: Filter::matching(...$predicates));
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        return First::from(elements: $this, defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function flatten(): static
    {
        return $this->pipeTo(operation: FlatMap::oneLevel());
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        return Get::byIndex(elements: $this, index: $index, defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function groupBy(Closure $classifier): static
    {
        return $this->pipeTo(operation: GroupInto::by(classifier: $classifier));
    }

    public function isEmpty(): bool
    {
        return First::isAbsent(elements: $this);
    }

    public function joinToString(string $separator): string
    {
        return Join::elements(elements: $this, separator: $separator);
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        return Last::from(elements: $this, defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function map(Closure ...$transformations): static
    {
        return $this->pipeTo(operation: Map::using(...$transformations));
    }

    public function reduce(Closure $accumulator, mixed $initial): mixed
    {
        return Reduce::from(elements: $this, accumulator: $accumulator, initial: $initial);
    }

    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $comparator = null): static
    {
        return $this->pipeTo(operation: Rearrange::by(order: $order, comparator: $comparator));
    }

    public function slice(int $offset, int $length = -1): static
    {
        return $this->pipeTo(operation: Segment::from(offset: $offset, length: $length));
    }

    private function pipeTo(Operation $operation): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: $operation));
    }
}
