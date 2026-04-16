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
 *  - createFrom / createFromEmpty / createFromClosure: eager evaluation. The source is materialized
 *    into an array immediately at creation time. The first terminal call runs all chained stages
 *    in a single fused pass and caches the result. Subsequent terminal calls reuse the cache.
 *  - createLazyFrom / createLazyFromEmpty / createLazyFromClosure: lazy evaluation via generators.
 *    The source is stored by reference. Every terminal call re-runs the entire pipeline from the source.
 *
 * Complexity notation used throughout this interface:
 *
 *  - n  = number of source elements at the time of the terminal call.
 *  - P  = total time cost of running all chained transforming stages over n elements (the "fused pass").
 *         For a pipeline of pure per-element stages, P is O(n * s) where s is the number of stages.
 *         Stages with non-linear contributions (e.g., `sort` is O(n log n)) dominate P.
 *  - "Call site" = cost paid when the method is invoked.
 *  - "Pass contribution" = cost this stage adds to P when a terminal operation later triggers the pass.
 *
 * Streaming-breaking stages: `sort` and `groupBy` must buffer all elements before emitting any output.
 * Any stage placed after them in the same pipeline cannot stream and will see the full buffered set.
 * Place these stages last whenever possible.
 */
interface Collectible extends Countable, IteratorAggregate
{
    /**
     * Creates a collection populated with the given elements using eager evaluation.
     *
     * Elements are materialized immediately into an array, enabling the fused-pass cache on the
     * first terminal access.
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
     * The closure is invoked once at creation time and its result is materialized immediately
     * into an array, enabling the fused-pass cache on the first terminal access.
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
     * Elements are processed on-demand through generators, consuming memory only as each element is yielded.
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
     * The closure is invoked each time the collection is iterated, enabling safe re-iteration over
     * generators or other single-use iterables.
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
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(m) time, O(m) space (m = number of appended elements).
     *
     * @param mixed ...$elements The elements to append.
     * @return static A new collection with the additional elements.
     */
    public function add(mixed ...$elements): static;

    /**
     * Merges the elements of another Collectible into the current Collection.
     *
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(m) time, O(m) space (m = number of elements in `other`).
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
     * Eager: O(P + n) on first terminal call (triggers fused pass and scans the result).
     *        O(n) on subsequent calls (scans the cached result). Short-circuits when found.
     *        O(n) cached space.
     * Lazy: O(P) per call. The search is interleaved with the pass and short-circuits when found.
     *       O(1) intermediate space.
     *
     * @param mixed $element The element to search for.
     * @return bool True if the element exists, false otherwise.
     */
    public function contains(mixed $element): bool;

    /**
     * Returns the total number of elements.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P) per call (must reach the end of the pipeline). O(1) intermediate space.
     *
     * @return int The element count.
     */
    public function count(): int;

    /**
     * Finds the first element that satisfies any given predicate.
     * Without predicates, returns null.
     *
     * Eager: O(P + n * p) on first terminal call (triggers fused pass and scans the result).
     *        O(n * p) on subsequent calls. Short-circuits when found. p = number of predicates.
     *        O(n) cached space.
     * Lazy: O(P + p) per emitted element. Short-circuits when found. O(1) intermediate space.
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
     * Eager: O(P + n * a) on first terminal call. O(n * a) on subsequent calls (over cached result).
     *        O(n) cached space. a = number of actions.
     * Lazy: O(P + n * a) per call. O(1) intermediate space.
     *
     * @param Closure ...$actions Actions to perform on each element.
     */
    public function each(Closure ...$actions): void;

    /**
     * Compares this collection with another for element-wise equality.
     *
     * Two collections are equal when they have the same size and every pair at the same position
     * satisfies the equality comparison.
     *
     * Eager: O(P + n) on first terminal call. O(n) on subsequent calls (over cached result).
     *        Short-circuits at the first mismatch. O(n) cached space.
     * Lazy: O(P + n) per call. Short-circuits at the first mismatch. O(1) intermediate space.
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
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n) time, O(1) space.
     *
     * @param mixed $element The element to remove.
     * @return static A new collection without the specified element.
     */
    public function remove(mixed $element): static;

    /**
     * Returns a new collection with all elements removed that satisfy the given predicate.
     * When no predicate is provided (i.e., $predicate is null), all elements are removed.
     *
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n) time, O(1) space.
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
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n * p) time, O(1) space (p = number of predicates).
     *
     * @param Closure|null ...$predicates Conditions each element must meet.
     * @return static A new collection with only the matching elements.
     */
    public function filter(?Closure ...$predicates): static;

    /**
     * Returns the first element, or a default if the collection is empty.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P_first) per call. Short-circuits at the first emitted element. O(1) intermediate space.
     *
     * @param mixed $defaultValueIfNotFound Value returned when the collection is empty.
     * @return mixed The first element or the default.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Flattens nested iterables by exactly one level. Non-iterable elements are yielded as-is.
     *
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n + s) time, O(1) space (s = total nested elements across all iterables).
     *
     * @return static A new collection with elements flattened by one level.
     */
    public function flatten(): static;

    /**
     * Returns the element at the given zero-based index.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P_index) per call. Short-circuits at the requested position. O(1) intermediate space.
     *
     * @param int $index The zero-based position.
     * @param mixed $defaultValueIfNotFound Value returned when the index is out of bounds.
     * @return mixed The element at the index or the default.
     */
    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Groups elements by a key derived from each element.
     *
     * The classifier receives each element and must return the group key. The resulting collection
     * contains key-to-element-list pairs.
     *
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n) time, O(n) space. Buffers all groups before emitting. Breaks streaming.
     *
     * @param Closure $classifier Maps each element to its group key.
     * @return static A new collection of grouped elements.
     */
    public function groupBy(Closure $classifier): static;

    /**
     * Determines whether the collection has no elements.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P_first) per call. Short-circuits at the first emitted element. O(1) intermediate space.
     *
     * @return bool True if the collection is empty.
     */
    public function isEmpty(): bool;

    /**
     * Joins all elements into a string with the given separator.
     *
     * Eager: O(P + n) on first terminal call. O(n) on subsequent calls (over cached result).
     *        O(n) cached space plus O(n) for the resulting string.
     * Lazy: O(P + n) per call. O(n) for the resulting string.
     *
     * @param string $separator The delimiter placed between each element.
     * @return string The concatenated result.
     */
    public function joinToString(string $separator): string;

    /**
     * Returns the last element, or a default if the collection is empty.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P) per call. Must reach the end of the pipeline. O(1) intermediate space.
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
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n * t) time, O(1) space (t = number of transformations).
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
     * Eager: O(P + n) on first terminal call. O(n) on subsequent calls (over cached result).
     *        O(n) cached space.
     * Lazy: O(P + n) per call. O(1) intermediate space (single carry value).
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
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(n log n) time, O(n) space. Buffers all elements before emitting any output.
     * breaks streaming for any stage placed after `sort` in the same pipeline.
     *
     * @param Order $order The sorting direction.
     * @param Closure|null $comparator Custom comparison function.
     * @return static A new sorted collection.
     */
    public function sort(Order $order = Order::ASCENDING_KEY, ?Closure $comparator = null): static;

    /**
     * Extracts a contiguous segment of the collection.
     *
     * Call site: O(1) time, O(1) space. Appends a pipeline stage in both eager and lazy modes.
     * Pass contribution: O(min(offset + length, n)) time, O(1) space. Iteration short-circuits once
     * the segment is fully emitted. An early `slice(0, k)` against a generator source can avoid
     * touching the rest.
     *
     * @param int $offset Zero-based starting position.
     * @param int $length Number of elements to include. Use -1 for "until the end".
     * @return static A new collection with the extracted segment.
     */
    public function slice(int $offset, int $length = -1): static;

    /**
     * Converts the Collection to an array.
     *
     * Eager: O(P + n) on first terminal call. O(n) on subsequent calls (over cached result).
     *        O(n) cached space.
     * Lazy: O(P + n) per call. O(n) for the resulting array.
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
     * Eager: O(P + n) on first terminal call. O(n) on subsequent calls (over cached result).
     *        O(n) cached space plus O(n) for the JSON string.
     * Lazy: O(P + n) per call. O(n) for the JSON string.
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
