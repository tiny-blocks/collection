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
 * Complexity notes (Big O):
 * - Unless stated otherwise, complexities refer to consuming the collection **once**.
 * - `n`: number of elements produced when consuming the collection once.
 * - Callback cost is not included (assumed O(1) per callback invocation).
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
     * Complexity: O(1) time and O(1) space to create the collection.
     * Consuming the collection is O(n) time and O(1) additional space.
     *
     * @param iterable<Element> $elements The elements to initialize the Collection with.
     * @return Collectible<Element> A new Collectible instance.
     */
    public static function createFrom(iterable $elements): Collectible;

    /**
     * Creates an empty Collectible instance.
     *
     * Complexity: O(1) time and O(1) space.
     *
     * @return Collectible<Element> An empty Collectible instance.
     */
    public static function createFromEmpty(): Collectible;

    /**
     * Adds one or more elements to the Collection.
     *
     * Complexity (when consumed): O(n + k) time and O(1) additional space,
     * where `k` is the number of elements passed to this method.
     *
     * @param Element ...$elements The elements to be added to the Collection.
     * @return Collectible<Element> The updated Collection.
     */
    public function add(mixed ...$elements): Collectible;

    /**
     * Checks if the Collection contains a specific element.
     *
     * Complexity: best-case O(1), worst-case O(n) time (early termination), O(1) space.
     *
     * @param Element $element The element to check for.
     * @return bool True if the element is found, false otherwise.
     */
    public function contains(mixed $element): bool;

    /**
     * Returns the total number of elements in the Collection.
     *
     * Complexity: O(n) time and O(1) additional space.
     *
     * @return int The number of elements in the Collection.
     */
    public function count(): int;

    /**
     * Executes actions on each element in the Collection without modifying it.
     *
     * Complexity: O(n · a) time and O(1) additional space,
     * where `a` is the number of actions passed to this method.
     *
     * @param Closure(Element): void ...$actions The actions to perform on each element.
     * @return Collectible<Element> The original Collection for chaining.
     */
    public function each(Closure ...$actions): Collectible;

    /**
     * Compares the Collection with another Collection for equality.
     *
     * Complexity: best-case O(1), worst-case O(min(n, m)) time (early termination), O(1) space,
     * where `m` is the size of the other collection.
     *
     * @param Collectible<Element> $other The Collection to compare with.
     * @return bool True if the collections are equal, false otherwise.
     */
    public function equals(Collectible $other): bool;

    /**
     * Filters elements in the Collection based on the provided predicates.
     * If no predicates are provided, all empty or falsy values (e.g., null, false, empty arrays) will be removed.
     *
     * Complexity (when consumed): O(n · p) time and O(1) additional space,
     * where `p` is the number of predicates.
     *
     * @param Closure(Element): bool|null ...$predicates
     * @return Collectible<Element> The updated Collection.
     */
    public function filter(?Closure ...$predicates): Collectible;

    /**
     * Finds the first element that matches any of the provided predicates.
     *
     * Complexity: best-case O(1), worst-case O(n · q) time (early termination), O(1) space,
     * where `q` is the number of predicates.
     *
     * @param Closure(Element): bool ...$predicates The predicates to match (evaluated as a logical OR).
     * @return Element|null The first matching element, or null if none is found.
     */
    public function findBy(Closure ...$predicates): mixed;

    /**
     * Retrieves the first element in the Collection or a default value if not found.
     *
     * Complexity: best-case O(1), worst-case O(n) time (early termination), O(1) space.
     *
     * @param Element|null $defaultValueIfNotFound The default value returns if no element is found.
     * @return Element|null The first element or the default value.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Flattens the collection by expanding iterable elements by one level (shallow flatten).
     *
     * Complexity (when consumed): O(n + s) time and O(1) additional space, where `s` is the total number of elements
     * inside nested iterables that are expanded.
     *
     * @return Collectible<Element> A new Collectible instance with elements flattened by one level.
     */
    public function flatten(): Collectible;

    /**
     * Retrieves an element by its index or a default value if not found.
     *
     * Complexity: O(n) time and O(1) additional space.
     *
     * @param int $index The index of the element to retrieve.
     * @param Element|null $defaultValueIfNotFound The default value returns if no element is found.
     * @return Element|null The element at the specified index or the default value.
     */
    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Returns an iterator for traversing the Collection.
     *
     * Complexity: O(1) time and O(1) space to obtain the iterator.
     *
     * @return Traversable<Key, Value> An iterator for the Collection.
     */
    public function getIterator(): Traversable;

    /**
     * Groups the elements in the Collection based on the provided criteria.
     *
     * Complexity (when consumed): O(n) time and O(n) additional space (materializes all groups).
     *
     * @param Closure(Element): Key $grouping The function to define the group key for each element.
     * @return Collectible<Key, list<Element>, Element> A Collection where each value is a list of elements,
     *                                                 grouped by the key returned by the closure.
     */
    public function groupBy(Closure $grouping): Collectible;

    /**
     * Determines if the Collection is empty.
     *
     * Complexity: best-case O(1), worst-case O(n) time (may need to advance until the first element is produced),
     * O(1) space.
     *
     * @return bool True if the Collection is empty, false otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Joins the elements of the Collection into a string, separated by a given separator.
     *
     * Complexity: O(n + L) time and O(L) space, where `L` is the length of the resulting string.
     *
     * @param string $separator The string used to separate the elements.
     * @return string The concatenated string of all elements in the Collection.
     */
    public function joinToString(string $separator): string;

    /**
     * Retrieves the last element in the Collection or a default value if not found.
     *
     * Complexity: O(n) time and O(1) space.
     *
     * @param Element|null $defaultValueIfNotFound The default value returns if no element is found.
     * @return Element|null The last element or the default value.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Applies transformations to each element in the Collection and returns a new Collection with the transformed
     * elements.
     *
     * Complexity (when consumed): O(n · t) time and O(1) additional space,
     * where `t` is the number of transformations.
     *
     * @param Closure(Element): Element ...$transformations The transformations to apply.
     * @return Collectible<Element> A new Collection with the applied transformations.
     */
    public function map(Closure ...$transformations): Collectible;

    /**
     * Removes a specific element from the Collection.
     *
     * Complexity (when consumed): O(n) time and O(1) additional space.
     *
     * @param Element $element The element to remove.
     * @return Collectible<Element> The updated Collection.
     */
    public function remove(mixed $element): Collectible;

    /**
     * Removes elements from the Collection based on the provided filter.
     * If no filter is passed, all elements in the Collection will be removed.
     *
     * Complexity (when consumed): O(n) time and O(1) additional space.
     *
     * @param Closure(Element): bool|null $filter The filter to determine which elements to remove.
     * @return Collectible<Element> The updated Collection.
     */
    public function removeAll(?Closure $filter = null): Collectible;

    /**
     * Reduces the elements in the Collection to a single value by applying an aggregator function.
     *
     * Complexity: O(n) time and O(1) additional space.
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
     * Complexity (when consumed): O(n log n) time and O(n) additional space (materializes elements to sort).
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
     *
     * Complexity (when consumed):
     * - If `length === 0`: O(1) time and O(1) additional space.
     * - If `length === -1`: O(n) time and O(1) additional space.
     * - If `length >= 0`: O(min(n, index + length)) time and O(1) additional space (may stop early).
     * - If `length < -1`: O(n) time and O(|length|) additional space (uses a buffer).
     *
     * @return Collectible<Element> A new Collection containing the sliced elements.
     */
    public function slice(int $index, int $length = -1): Collectible;

    /**
     * Converts the Collection to an array.
     *
     * The key preservation behavior should be provided from the `KeyPreservation` enum:
     *  - {@see KeyPreservation::PRESERVE}: Preserves the array keys.
     *  - {@see KeyPreservation::DISCARD}: Discards the array keys.
     *
     * By default, `KeyPreservation::PRESERVE` is used.
     *
     * Complexity: O(n) time and O(n) space.
     *
     * @param KeyPreservation $keyPreservation The option to preserve or discard array keys.
     * @return array<Key, Value> The resulting array.
     */
    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array;

    /**
     * Converts the Collection to a JSON string.
     *
     * The key preservation behavior should be provided from the `KeyPreservation` enum:
     *  - {@see KeyPreservation::PRESERVE}: Preserves the array keys.
     *  - {@see KeyPreservation::DISCARD}: Discards the array keys.
     *
     * By default, `KeyPreservation::PRESERVE` is used.
     *
     * Complexity: O(n + L) time and O(n + L) space, where `L` is the length of the resulting JSON.
     *
     * @param KeyPreservation $keyPreservation The option to preserve or discard array keys.
     * @return string The resulting JSON string.
     */
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string;
}
