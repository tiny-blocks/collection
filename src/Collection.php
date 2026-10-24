<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use TinyBlocks\Collection\Internal\EagerPipeline;
use TinyBlocks\Collection\Internal\LazyPipeline;
use TinyBlocks\Collection\Internal\Operations\Resolving\Each;
use TinyBlocks\Collection\Internal\Operations\Resolving\Equality;
use TinyBlocks\Collection\Internal\Operations\Resolving\Find;
use TinyBlocks\Collection\Internal\Operations\Resolving\Join;
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
use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Mapper;
use Traversable;

/**
 * Default Collectible implementation backed by an eager or lazy evaluation pipeline.
 *
 * @template TValue
 * @implements Collectible<TValue>
 */
class Collection implements Collectible
{
    /**
     * @param Pipeline<TValue> $pipeline
     */
    private function __construct(private readonly Pipeline $pipeline)
    {
    }

    public static function createFrom(iterable $elements): static
    {
        return new static(pipeline: EagerPipeline::from(source: $elements));
    }

    public static function createLazyFrom(iterable $elements): static
    {
        return new static(pipeline: LazyPipeline::from(source: $elements));
    }

    public static function createFromEmpty(): static
    {
        return static::createFrom(elements: []);
    }

    public static function createFromClosure(Closure $factory): static
    {
        return new static(pipeline: EagerPipeline::fromClosure(factory: $factory));
    }

    public static function createLazyFromEmpty(): static
    {
        return static::createLazyFrom(elements: []);
    }

    public static function createLazyFromClosure(Closure $factory): static
    {
        return new static(pipeline: LazyPipeline::fromClosure(factory: $factory));
    }

    public function add(mixed ...$elements): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: Add::these(newElements: $elements)));
    }

    public function map(Closure ...$transformations): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: Map::using(...$transformations)));
    }

    public function each(Closure ...$actions): void
    {
        Each::on(actions: $actions, elements: $this->pipeline->process());
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        return $this->pipeline->last(defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $comparator = null): static
    {
        $operation = Rearrange::by(order: $order, comparator: $comparator);

        return new static(pipeline: $this->pipeline->pipe(operation: $operation));
    }

    public function count(): int
    {
        return $this->pipeline->count();
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        return $this->pipeline->first(defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        return $this->pipeline->getBy(index: $index, defaultValueIfNotFound: $defaultValueIfNotFound);
    }

    public function merge(Collectible $other): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: Merge::with(other: $other)));
    }

    public function slice(int $offset, int $length = -1): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: Segment::from(length: $length, offset: $offset)));
    }

    public function equals(Collectible $other): bool
    {
        return Equality::compareAll(other: $other, elements: $this->pipeline->process());
    }

    public function filter(?Closure ...$predicates): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: Filter::matching(...$predicates)));
    }

    public function findBy(Closure ...$predicates): mixed
    {
        return Find::firstMatch(elements: $this->pipeline->process(), predicates: $predicates);
    }

    public function reduce(Closure $accumulator, mixed $initial): mixed
    {
        return Reduce::from(initial: $initial, elements: $this->pipeline->process(), accumulator: $accumulator);
    }

    public function remove(mixed $element): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: Remove::element(element: $element)));
    }

    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string
    {
        $configuration = match ($keyPreservation) {
            KeyPreservation::DISCARD  => Configuration::default()->discardingKeys(),
            KeyPreservation::PRESERVE => Configuration::default()
        };

        return Mapper::create()->toJson(source: $this, configuration: $configuration);
    }

    public function flatten(): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: FlatMap::oneLevel()));
    }

    public function groupBy(Closure $classifier): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: GroupInto::by(classifier: $classifier)));
    }

    public function isEmpty(): bool
    {
        return $this->pipeline->isEmpty();
    }

    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array
    {
        $configuration = match ($keyPreservation) {
            KeyPreservation::DISCARD  => Configuration::default()->discardingKeys(),
            KeyPreservation::PRESERVE => Configuration::default()
        };

        return Mapper::create()->toArray(source: $this, configuration: $configuration);
    }

    public function contains(mixed $element): bool
    {
        return Equality::exists(element: $element, elements: $this->pipeline->process());
    }

    public function removeAll(?Closure $predicate = null): static
    {
        return new static(pipeline: $this->pipeline->pipe(operation: RemoveAll::matching(predicate: $predicate)));
    }

    public function getIterator(): Traversable
    {
        return $this->pipeline->process();
    }

    public function joinToString(string $separator): string
    {
        return Join::elements(elements: $this->pipeline->process(), separator: $separator);
    }
}
