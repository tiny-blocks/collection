<?php

declare(strict_types=1);

namespace TinyBlocks\Collection;

use Closure;
use Countable;
use IteratorAggregate;
use TinyBlocks\Mapper\KeyPreservation;

/**
 * Immutable, type-safe collection contract with a fluent API.
 *
 * Every mutating method returns a new instance, preserving immutability.
 *
 * Two evaluation strategies are available:
 *
 *  - createFrom / createFromEmpty / createFromClosure: eager evaluation, materialized immediately.
 *  - createLazyFrom / createLazyFromEmpty / createLazyFromClosure: lazy evaluation via generators, on-demand.
 */
interface Collectible extends Countable, IteratorAggregate
{
    /**
     * Creates a collection populated with the given elements using eager evaluation.
     *
     * Elements are materialized immediately into an array, enabling
     * constant-time access by index, count, and repeated iteration.
     *
     * O(n) time, O(n) space. Iterates the input once and stores all elements.
     *
     * @param iterable $elements The elements to populate the collection with.
     * @return static A new collection containing the given elements.
     */
    public static function createFrom(iterable $elements): static;

    /**
     * Creates an empty collection using eager evaluation.
     *
     * O(1) time, O(1) space.
     *
     * @return static An empty collection.
     */
    public static function createFromEmpty(): static;

    /**
     * Creates a collection using eager evaluation from a closure that produces an iterable.
     *
     * The closure is invoked once at creation time and its result is materialized
     * immediately into an array, enabling constant-time access by index, count,
     * and repeated iteration.
     *
     * O(n) time, O(n) space. Invokes the closure and stores all yielded elements.
     *
     * @param Closure $factory A closure returning an iterable of elements.
     * @return static A new collection backed by the materialized closure result.
     */
    public static function createFromClosure(Closure $factory): static;

    /**
     * Creates a collection populated with the given elements using lazy evaluation.
     *
     * Elements are processed on-demand through generators, consuming
     * memory only as each element is yielded.
     *
     * O(1) time, O(1) space. Stores a reference to the iterable without iterating.
     *
     * @param iterable $elements The elements to populate the collection with.
     * @return static A new collection containing the given elements.
     */
    public static function createLazyFrom(iterable $elements): static;

    /**
     * Creates an empty collection using lazy evaluation.
     *
     * O(1) time, O(1) space.
     *
     * @return static An empty collection.
     */
    public static function createLazyFromEmpty(): static;

    /**
     * Creates a collection using lazy evaluation from a closure that produces an iterable.
     *
     * The closure is invoked each time the collection is iterated, enabling
     * safe re-iteration over generators or other single-use iterables.
     *
     * O(1) time, O(1) space. Stores the closure without invoking it.
     *
     * @param Closure $factory A closure returning an iterable of elements.
     * @return static A new collection backed by the given factory.
     */
    public static function createLazyFromClosure(Closure $factory): static;

    /**
     * Returns a new collection with the specified elements appended.
     *
     * Eager: O(n + m) time, O(n + m) space. Materializes all existing and new elements.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param mixed ...$elements The elements to append.
     * @return static A new collection with the additional elements.
     */
    public function add(mixed ...$elements): static;

    /**
     * Merges the elements of another Collectible into the current Collection.
     *
     * Eager: O(n + m) time, O(n + m) space. Materializes elements from both collections.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param Collectible $other The collection to merge with.
     * @return static A new collection containing elements from both collections.
     */
    public function merge(Collectible $other): static;

    /**
     * Determines whether the collection contains the specified element.
     *
     * Uses strict equality for scalars and loose equality for objects.
     *
     * O(n) time, O(1) space. Iterates until the element is found or the end is reached.
     *
     * @param mixed $element The element to search for.
     * @return bool True if the element exists, false otherwise.
     */
    public function contains(mixed $element): bool;

    /**
     * Returns the total number of elements.
     *
     * Eager: O(1) time, O(1) space. Reads the array length directly.
     * Lazy: O(n) time, O(1) space. Must iterate all elements to count.
     *
     * @return int The element count.
     */
    public function count(): int;

    /**
     * Finds the first element that satisfies any given predicate.
     * Without predicates, returns null.
     *
     * O(n * p) time, O(1) space. Iterates until a match is found. p = number of predicates.
     *
     * @param Closure ...$predicates Conditions to test each element against.
     * @return mixed The first matching element or null if no match is found.
     */
    public function findBy(Closure ...$predicates): mixed;

    /**
     * Executes side effect actions on every element without modifying the collection.
     *
     * This is a terminal operation. The collection is not returned.
     *
     * O(n * a) time, O(1) space. Iterates all elements. a = number of actions.
     *
     * @param Closure ...$actions Actions to perform on each element.
     */
    public function each(Closure ...$actions): void;

    /**
     * Compares this collection with another for element-wise equality.
     *
     * Two collections are equal when they have the same size and every
     * pair at the same position satisfies the equality comparison.
     *
     * O(n) time, O(1) space. Walks both collections in parallel, comparing element by element.
     *
     * @param Collectible $other The collection to compare against.
     * @return bool True if both collections are element-wise equal.
     */
    public function equals(Collectible $other): bool;

    /**
     * Returns a new collection with the specified element removed.
     *
     * All occurrences of the element are removed.
     *
     * Eager: O(n) time, O(n) space. Materializes a new array excluding matches.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param mixed $element The element to remove.
     * @return static A new collection without the specified element.
     */
    public function remove(mixed $element): static;

    /**
     * Returns a new collection with all elements removed that satisfy the given predicate.
     * When no predicate is provided (i.e., $predicate is null), all elements are removed.
     *
     * Eager: O(n) time, O(n) space. Materializes a new array excluding matches.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param Closure|null $predicate Condition to determine which elements to remove.
     * @return static A new collection with the matching elements removed.
     */
    public function removeAll(?Closure $predicate = null): static;

    /**
     * Retains only elements satisfying all given predicates.
     *
     * Without predicates, falsy values are removed.
     *
     * Eager: O(n) time, O(n) space. Materializes a new array with matching elements.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param Closure|null ...$predicates Conditions each element must meet.
     * @return static A new collection with only the matching elements.
     */
    public function filter(?Closure ...$predicates): static;

    /**
     * Returns the first element, or a default if the collection is empty.
     *
     * Eager: O(1) time, O(1) space. Direct array access via array_key_first.
     * Lazy: O(1) time, O(1) space. Yields once from the pipeline.
     *
     * @param mixed $defaultValueIfNotFound Value returned when the collection is empty.
     * @return mixed The first element or the default.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Flattens nested iterables by exactly one level. Non-iterable elements are yielded as-is.
     *
     * Eager: O(n + s) time, O(n + s) space. s = total nested elements across all iterables.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @return static A new collection with elements flattened by one level.
     */
    public function flatten(): static;

    /**
     * Returns the element at the given zero-based index.
     *
     * Eager: O(1) time, O(1) space. Direct array access via array_key_exists.
     * Lazy: O(n) time, O(1) space. Iterates until the index is reached.
     *
     * @param int $index The zero-based position.
     * @param mixed $defaultValueIfNotFound Value returned when the index is out of bounds.
     * @return mixed The element at the index or the default.
     */
    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Groups elements by a key derived from each element.
     *
     * The classifier receives each element and must return the group key.
     * The resulting collection contains key to element-list pairs.
     *
     * Eager: O(n) time, O(n) space. Materializes all groups into an associative array.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param Closure $classifier Maps each element to its group key.
     * @return static A new collection of grouped elements.
     */
    public function groupBy(Closure $classifier): static;

    /**
     * Determines whether the collection has no elements.
     *
     * Eager: O(1) time, O(1) space. Checks the first yield from the materialized array.
     * Lazy: O(1) time, O(1) space. Yields once from the pipeline.
     *
     * @return bool True if the collection is empty.
     */
    public function isEmpty(): bool;

    /**
     * Joins all elements into a string with the given separator.
     *
     * O(n) time, O(n) space. Accumulates all elements into an intermediate array, then implodes.
     *
     * @param string $separator The delimiter placed between each element.
     * @return string The concatenated result.
     */
    public function joinToString(string $separator): string;

    /**
     * Returns the last element, or a default if the collection is empty.
     *
     * Eager: O(1) time, O(1) space. Direct array access via array_key_last.
     * Lazy: O(n) time, O(1) space. Must iterate all elements to find the last.
     *
     * @param mixed $defaultValueIfNotFound Value returned when the collection is empty.
     * @return mixed The last element or the default.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Applies one or more transformation functions to each element.
     *
     * Transformations are applied in order. Each receives the current value and key.
     *
     * Eager: O(n * t) time, O(n) space. Materializes all transformed elements. t = number of transformations.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param Closure ...$transformations Functions applied to each element.
     * @return static A new collection with the transformed elements.
     */
    public function map(Closure ...$transformations): static;

    /**
     * Reduces the collection to a single accumulated value.
     *
     * The accumulator receives the carry and the current element.
     *
     * O(n) time, O(1) space. Iterates all elements, maintaining a single carry value.
     *
     * @param Closure $accumulator Combines the carry with each element.
     * @param mixed $initial The starting value for the accumulation.
     * @return mixed The final accumulated result.
     */
    public function reduce(Closure $accumulator, mixed $initial): mixed;

    /**
     * Returns a new collection sorted by the given order and optional comparator.
     *
     * Without a comparator, the spaceship operator is used.
     *
     * Eager: O(n log n) time, O(n) space. Materializes and sorts all elements.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param Order $order The sorting direction.
     * @param Closure|null $comparator Custom comparison function.
     * @return static A new sorted collection.
     */
    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $comparator = null): static;

    /**
     * Extracts a contiguous segment of the collection.
     *
     * Eager: O(n) time, O(n) space. Materializes the segment into a new array.
     * Lazy: O(1) time, O(1) space. Appends a pipeline stage without iterating.
     *
     * @param int $offset Zero-based starting position.
     * @param int $length Number of elements to include. Use -1 for "until the end".
     * @return static A new collection with the extracted segment.
     */
    public function slice(int $offset, int $length = -1): static;

    /**
     * Converts the Collection to an array.
     *
     * O(n) time, O(n) space. Iterates all elements and stores them in an array.
     *
     * The key preservation behavior should be provided from the `KeyPreservation` enum:
     *  - {@see KeyPreservation::PRESERVE}: Preserves the array keys.
     *  - {@see KeyPreservation::DISCARD}: Discards the array keys.
     *
     * By default, `KeyPreservation::PRESERVE` is used.
     *
     * @param KeyPreservation $keyPreservation The option to preserve or discard array keys.
     * @return array The resulting array.
     */
    public function toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): array;

    /**
     * Converts the Collection to a JSON string.
     *
     * O(n) time, O(n) space. Converts to array, then encodes to JSON.
     *
     * The key preservation behavior should be provided from the `KeyPreservation` enum:
     *  - {@see KeyPreservation::PRESERVE}: Preserves the array keys.
     *  - {@see KeyPreservation::DISCARD}: Discards the array keys.
     *
     * By default, `KeyPreservation::PRESERVE` is used.
     *
     * @param KeyPreservation $keyPreservation The option to preserve or discard array keys.
     * @return string The resulting JSON string.
     */
    public function toJson(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE): string;
}
