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
 * @template Key of array-key
 * @template Value
 * @extends Countable
 * @extends IteratorAggregate<Key, Value>
 */
interface Collectible extends Countable, IteratorAggregate
{
    /**
     * Creates a new Collectible instance from the given elements.
     *
     * @param iterable $elements The elements to initialize the collection with.
     * @return Collectible<Key, Value> A new Collectible instance.
     */
    public static function createFrom(iterable $elements): Collectible;

    /**
     * Creates an empty Collectible instance.
     *
     * @return Collectible<Key, Value> An empty Collectible instance.
     */
    public static function createFromEmpty(): Collectible;

    /**
     * Adds one or more elements to the collection.
     *
     * @param mixed ...$elements The elements to add to the collection.
     * @return Collectible<Key, Value> The updated collection.
     */
    public function add(mixed ...$elements): Collectible;

    /**
     * Executes actions on each element in the collection without modifying it.
     *
     * @param Closure ...$actions The actions to perform on each element.
     * @return Collectible<Key, Value> The original collection for chaining.
     */
    public function each(Closure ...$actions): Collectible;

    /**
     * Compares the collection with another collection for equality.
     *
     * @param Collectible<Key, Value> $other The collection to compare with.
     * @return bool True if the collections are equal, false otherwise.
     */
    public function equals(Collectible $other): bool;

    /**
     * Filters elements in the collection based on the provided predicates.
     * If no predicates are provided, all empty or falsy values (e.g., null, false, empty arrays) will be removed.
     *
     * @param Closure|null ...$predicates
     * @return Collectible<Key, Value> The updated collection.
     */
    public function filter(?Closure ...$predicates): Collectible;

    /**
     * Finds the first element matching the provided predicates.
     *
     * @param Closure ...$predicates The predicates to match.
     * @return mixed The first matching element, or null if none found.
     */
    public function findBy(Closure ...$predicates): mixed;

    /**
     * Retrieves the first element in the collection, or a default value if not found.
     *
     * @param mixed $defaultValueIfNotFound The default value to return if no element is found.
     * @return mixed The first element or the default value.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Counts the number of elements in the collection.
     *
     * @return int The number of elements in the collection.
     */
    public function count(): int;

    /**
     * Retrieves an element by its index, or a default value if not found.
     *
     * @param int $index The index of the element to retrieve.
     * @param mixed $defaultValueIfNotFound The default value to return if no element is found.
     * @return mixed The element at the specified index or the default value.
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
     * @param mixed $defaultValueIfNotFound The default value to return if no element is found.
     * @return mixed The last element or the default value.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Applies transformations to each element in the collection and returns a new collection with the transformed
     * elements.
     *
     * @param Closure(Value): Value ...$transformations The transformations to apply.
     * @return Collectible<Key, Value> A new collection with the applied transformations.
     */
    public function map(Closure ...$transformations): Collectible;

    /**
     * Removes a specific element from the collection.
     *
     * @param mixed $element The element to remove.
     * @return Collectible<Key, Value> The updated collection.
     */
    public function remove(mixed $element): Collectible;

    /**
     * Removes elements from the collection based on the provided filter.
     * If no filter is passed, all elements in the collection will be removed.
     *
     * @param Closure|null $filter The filter to determine which elements to remove.
     * @return Collectible<Key, Value> The updated collection.
     */
    public function removeAll(?Closure $filter = null): Collectible;

    /**
     * Sorts the collection based on the provided order and predicate.
     *
     * @param Order $order The order in which to sort the collection.
     * @param Closure|null $predicate The predicate to use for sorting.
     * @return Collectible<Key, Value> The updated collection.
     */
    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $predicate = null): Collectible;

    /**
     * Converts the collection to an array.
     *
     * @param PreserveKeys $preserveKeys The option to preserve array keys.
     * @return array<Key, Value> The resulting array.
     */
    public function toArray(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): array;

    /**
     * Converts the collection to a JSON string.
     *
     * @param PreserveKeys $preserveKeys The option to preserve array keys.
     * @return string The resulting JSON string.
     */
    public function toJson(PreserveKeys $preserveKeys = PreserveKeys::PRESERVE): string;
}
