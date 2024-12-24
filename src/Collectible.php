<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use Countable;
use IteratorAggregate;
use TinyBlocks\Mapper\KeyPreservation;
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
     * @param iterable<Element> $elements The elements to initialize the Collection with.
     * @return Collectible<Element> A new Collectible instance.
     */
    public static function createFrom(iterable $elements): Collectible;

    /**
     * Creates an empty Collectible instance.
     *
     * @return Collectible<Element> An empty Collectible instance.
     */
    public static function createFromEmpty(): Collectible;

    /**
     * Adds one or more elements to the Collection.
     *
     * @param Element ...$elements The elements to be added to the Collection.
     * @return Collectible<Element> The updated Collection.
     */
    public function add(mixed ...$elements): Collectible;

    /**
     * Checks if the Collection contains a specific element.
     *
     * @param Element $element The element to check for.
     * @return bool True if the element is found, false otherwise.
     */
    public function contains(mixed $element): bool;

    /**
     * Returns the total number of elements in the Collection.
     *
     * @return int The number of elements in the Collection.
     */
    public function count(): int;

    /**
     * Executes actions on each element in the Collection without modifying it.
     *
     * @param Closure(Element): void ...$actions The actions to perform on each element.
     * @return Collectible<Element> The original Collection for chaining.
     */
    public function each(Closure ...$actions): Collectible;

    /**
     * Compares the Collection with another Collection for equality.
     *
     * @param Collectible<Element> $other The Collection to compare with.
     * @return bool True if the collections are equal, false otherwise.
     */
    public function equals(Collectible $other): bool;

    /**
     * Filters elements in the Collection based on the provided predicates.
     * If no predicates are provided, all empty or falsy values (e.g., null, false, empty arrays) will be removed.
     *
     * @param Closure(Element): bool|null ...$predicates
     * @return Collectible<Element> The updated Collection.
     */
    public function filter(?Closure ...$predicates): Collectible;

    /**
     * Finds the first element matching the provided predicates.
     *
     * @param Closure(Element): bool ...$predicates The predicates to match.
     * @return Element|null The first matching element, or null if none is found.
     */
    public function findBy(Closure ...$predicates): mixed;

    /**
     * Retrieves the first element in the Collection or a default value if not found.
     *
     * @param Element|null $defaultValueIfNotFound The default value returns if no element is found.
     * @return Element|null The first element or the default value.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Flattens a Collection by removing any nested collections and returning a single Collection with all elements.
     *
     * @return Collectible<Element> A new Collectible instance with all elements flattened into a single Collection.
     */
    public function flatten(): Collectible;

    /**
     * Retrieves an element by its index or a default value if not found.
     *
     * @param int $index The index of the element to retrieve.
     * @param Element|null $defaultValueIfNotFound The default value returns if no element is found.
     * @return Element|null The element at the specified index or the default value.
     */
    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Returns an iterator for traversing the Collection.
     *
     * @return Traversable<Key, Value> An iterator for the Collection.
     */
    public function getIterator(): Traversable;

    /**
     * Groups the elements in the Collection based on the provided criteria.
     *
     * @param Closure(Element): Key $grouping The function to define the group key for each element.
     * @return Collectible<Key, Collectible<Key, Element, Element>, Element> A Collection of collections,
     *                                                                       grouped by the key returned by the closure.
     */
    public function groupBy(Closure $grouping): Collectible;

    /**
     * Determines if the Collection is empty.
     *
     * @return bool True if the Collection is empty, false otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Joins the elements of the Collection into a string, separated by a given separator.
     *
     * @param string $separator The string used to separate the elements.
     * @return string The concatenated string of all elements in the Collection.
     */
    public function joinToString(string $separator): string;

    /**
     * Retrieves the last element in the Collection or a default value if not found.
     *
     * @param Element|null $defaultValueIfNotFound The default value returns if no element is found.
     * @return Element|null The last element or the default value.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Applies transformations to each element in the Collection and returns a new Collection with the transformed
     * elements.
     *
     * @param Closure(Element): Element ...$transformations The transformations to apply.
     * @return Collectible<Element> A new Collection with the applied transformations.
     */
    public function map(Closure ...$transformations): Collectible;

    /**
     * Removes a specific element from the Collection.
     *
     * @param Element $element The element to remove.
     * @return Collectible<Element> The updated Collection.
     */
    public function remove(mixed $element): Collectible;

    /**
     * Removes elements from the Collection based on the provided filter.
     * If no filter is passed, all elements in the Collection will be removed.
     *
     * @param Closure(Element): bool|null $filter The filter to determine which elements to remove.
     * @return Collectible<Element> The updated Collection.
     */
    public function removeAll(?Closure $filter = null): Collectible;

    /**
     * Reduces the elements in the Collection to a single value by applying an aggregator function.
     *
     * @param Closure(mixed, Element): mixed $aggregator The function that aggregates the elements.
     *        It receives the current accumulated value and the current element.
     * @param mixed $initial The initial value to start the aggregation.
     * @return mixed The final aggregated result.
     */
    public function reduce(Closure $aggregator, mixed $initial): mixed;

    /**
     * Sorts the Collection based on the provided order and predicate.
     *
     * The order should be provided from the `Order` enum:
     *  - {@see Order::ASCENDING_KEY}: Sorts in ascending order by key.
     *  - {@see Order::DESCENDING_KEY}: Sorts in descending order by key.
     *  - {@see Order::ASCENDING_VALUE}: Sorts in ascending order by value.
     *  - {@see Order::DESCENDING_VALUE}: Sorts in descending order by value.
     *
     * By default, `Order::ASCENDING_KEY` is used.
     *
     * @param Order $order The order in which to sort the Collection.
     * @param Closure(Element, Element): int|null $predicate The predicate to use for sorting.
     * @return Collectible<Element> The updated Collection.
     */
    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $predicate = null): Collectible;

    /**
     * Returns a subset of the Collection starting at the specified index and containing the specified number of
     * elements.
     *
     * If the `length` is negative, it will exclude that many elements from the end of the Collection.
     * If the `length` is not provided or set to `-1`, it returns all elements starting from the index until the end.
     *
     * @param int $index The zero-based index at which to start the slice.
     * @param int $length The number of elements to include in the slice. If negative, remove that many from the end.
     *                    Default is `-1`, meaning all elements from the index onward will be included.
     * @return Collectible<Element> A new Collection containing the sliced elements.
     */
    public function slice(int $index, int $length = -1): Collectible;

    /**
     * Converts the Collection to an array.
     *
     * The key preservation behavior should be provided from the `PreserveKeys` enum:
     *  - {@see KeyPreservation::PRESERVE}: Preserves the array keys.
     *  - {@see KeyPreservation::DISCARD}: Discards the array keys.
     *
     * By default, `PreserveKeys::PRESERVE` is used.
     *
     * @param KeyPreservation $keyPreservation The option to preserve or discard array keys.
     * @return array<Key, Value> The resulting array.
     */
    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array;

    /**
     * Converts the Collection to a JSON string.
     *
     * The key preservation behavior should be provided from the `PreserveKeys` enum:
     *  - {@see KeyPreservation::PRESERVE}: Preserves the array keys.
     *  - {@see KeyPreservation::DISCARD}: Discards the array keys.
     *
     * By default, `PreserveKeys::PRESERVE` is used.
     *
     * @param KeyPreservation $keyPreservation The option to preserve or discard array keys.
     * @return string The resulting JSON string.
     */
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string;
}
