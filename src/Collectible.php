<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use Countable;
use IteratorAggregate;
use TinyBlocks\Collection\Internal\Operations\Order\Order;
use TinyBlocks\Collection\Internal\Operations\Transform\PreserveKeys;
use Traversable;

/**
 * Represents a collection that can be manipulated, iterated, and counted.
 *
 * @template Key of int|string
 * @template Value of mixed
 * @template Element of mixed
 * @extends IteratorAggregate<Key, Value>
 */
interface Collectible extends Countable, IteratorAggregate
{
    /**
     * Creates a new Collectible instance from the given elements.
     *
     * @param iterable<Element> $elements The elements to initialize the collection with.
     * @return Collectible<Element> A new Collectible instance.
     */
    public static function createFrom(iterable $elements): static;

    /**
     * Creates an empty Collectible instance.
     *
     * @return Collectible<Element> An empty Collectible instance.
     */
    public static function createFromEmpty(): static;

    /**
     * Adds one or more elements to the collection.
     *
     * @param Element ...$elements The elements to add to the collection.
     * @return Collectible<Element> The updated collection.
     */
    public function add(mixed ...$elements): Collectible;

    /**
     * Checks if the collection contains a specific element.
     *
     * @param Element $element The element to check for.
     * @return bool True if the element is found, false otherwise.
     */
    public function contains(mixed $element): bool;

    /**
     * Returns the total number of elements in the Collection.
     *
     * @return int The number of elements in the collection.
     */
    public function count(): int;

    /**
     * Executes actions on each element in the collection without modifying it.
     *
     * @param Closure(Element): void ...$actions The actions to perform on each element.
     * @return Collectible<Element> The original collection for chaining.
     */
    public function each(Closure ...$actions): Collectible;

    /**
     * Compares the collection with another collection for equality.
     *
     * @param Collectible<Element> $other The collection to compare with.
     * @return bool True if the collections are equal, false otherwise.
     */
    public function equals(Collectible $other): bool;

    /**
     * Filters elements in the collection based on the provided predicates.
     * If no predicates are provided, all empty or falsy values (e.g., null, false, empty arrays) will be removed.
     *
     * @param Closure(Element): bool|null ...$predicates
     * @return Collectible<Element> The updated collection.
     */
    public function filter(?Closure ...$predicates): Collectible;

    /**
     * Finds the first element matching the provided predicates.
     *
     * @param Closure(Element): bool ...$predicates The predicates to match.
     * @return Element|null The first matching element, or null if none found.
     */
    public function findBy(Closure ...$predicates): mixed;

    /**
     * Retrieves the first element in the collection, or a default value if not found.
     *
     * @param Element|null $defaultValueIfNotFound The default value to return if no element is found.
     * @return Element|null The first element or the default value.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Retrieves an element by its index, or a default value if not found.
     *
     * @param int $index The index of the element to retrieve.
     * @param Element|null $defaultValueIfNotFound The default value to return if no element is found.
     * @return Element|null The element at the specified index or the default value.
     */
    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Returns an iterator for traversing the collection.
     *
     * @return Traversable<Key, Value> An iterator for the collection.
     */
    public function getIterator(): Traversable;

    /**
     * Determines if the collection is empty.
     *
     * @return bool True if the collection is empty, false otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Retrieves the last element in the collection, or a default value if not found.
     *
     * @param Element|null $defaultValueIfNotFound The default value to return if no element is found.
     * @return Element|null The last element or the default value.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Applies transformations to each element in the collection and returns a new collection with the transformed
     * elements.
     *
     * @param Closure(Element): Element ...$transformations The transformations to apply.
     * @return Collectible<Element> A new collection with the applied transformations.
     */
    public function map(Closure ...$transformations): Collectible;

    /**
     * Removes a specific element from the collection.
     *
     * @param Element $element The element to remove.
     * @return Collectible<Element> The updated collection.
     */
    public function remove(mixed $element): Collectible;

    /**
     * Removes elements from the collection based on the provided filter.
     * If no filter is passed, all elements in the collection will be removed.
     *
     * @param Closure(Element): bool|null $filter The filter to determine which elements to remove.
     * @return Collectible<Element> The updated collection.
     */
    public function removeAll(?Closure $filter = null): Collectible;

    /**
     * Reduces the elements in the collection to a single value by applying an aggregator function.
     *
     * @param Closure(mixed, Element): mixed $aggregator The function that aggregates the elements.
     *        It receives the current accumulated value and the current element.
     * @param mixed $initial The initial value to start the aggregation.
     * @return mixed The final aggregated result.
     */
    public function reduce(Closure $aggregator, mixed $initial): mixed;

    /**
     * Sorts the collection based on the provided order and predicate.
     *
     * The order should be provided from the `Order` enum:
     *  - `Order::ASCENDING_KEY`: Sorts in ascending order by key.
     *  - `Order::DESCENDING_KEY`: Sorts in descending order by key.
     *  - `Order::ASCENDING_VALUE`: Sorts in ascending order by value.
     *  - `Order::DESCENDING_VALUE`: Sorts in descending order by value.
     *
     * By default, `Order::ASCENDING_KEY` is used.
     *
     * @param Order $order The order in which to sort the collection.
     * @param Closure(Element, Element): int|null $predicate The predicate to use for sorting.
     * @return Collectible<Element> The updated collection.
     */
    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $predicate = null): Collectible;

    /**
     * Converts the collection to an array.
     *
     * The key preservation behavior should be provided from the `PreserveKeys` enum:
     *  - `PreserveKeys::PRESERVE`: Preserves the array keys.
     *  - `PreserveKeys::DISCARD`: Discards the array keys.
     *
     * By default, `PreserveKeys::PRESERVE` is used.
     *
     * @param PreserveKeys $preserveKeys The option to preserve or discard array keys.
     * @return array<Key, Value> The resulting array.
     */
    public function toArray(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): array;

    /**
     * Converts the collection to a JSON string.
     *
     * The key preservation behavior should be provided from the `PreserveKeys` enum:
     *  - `PreserveKeys::PRESERVE`: Preserves the array keys.
     *  - `PreserveKeys::DISCARD`: Discards the array keys.
     *
     * By default, `PreserveKeys::PRESERVE` is used.
     *
     * @param PreserveKeys $preserveKeys The option to preserve or discard array keys.
     * @return string The resulting JSON string.
     */
    public function toJson(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): string;
}
