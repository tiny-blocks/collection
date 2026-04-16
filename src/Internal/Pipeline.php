<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

/**
 * Defines a processing pipeline that composes a sequence of operations
 * over a stream of elements.
 *
 * The evaluation strategy (lazy or eager) is determined by the
 * concrete implementation, encapsulating the Strategy pattern.
 *
 * Complexity notation used throughout this interface:
 *
 *  - n = number of source elements at the time of the terminal call.
 *  - P = total cost of running all chained stages over n elements (the "fused pass"). For a pipeline
 *        of pure per-element stages, P is O(n * s) where s is the number of stages. Stages with
 *        non-linear contributions (e.g., `sort` is O(n log n)) dominate P.
 */
interface Pipeline
{
    /**
     * Adds a new operation stage to the pipeline.
     *
     * Returns a new pipeline instance containing all previous stages
     * plus the given operation, preserving immutability.
     *
     * Eager: O(1) time, O(1) space. Appends the stage. Materialization deferred to first terminal access.
     * Lazy: O(1) time, O(1) space. Appends the stage without iterating.
     *
     * @param Operation $operation The operation to append as the next stage.
     * @return Pipeline A new pipeline with the added stage.
     */
    public function pipe(Operation $operation): Pipeline;

    /**
     * Returns the total number of elements in the pipeline.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P) per call (must reach the end of the pipeline). O(1) intermediate space.
     *
     * @return int The element count.
     */
    public function count(): int;

    /**
     * Returns the first element, or a default if empty.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P_first) per call. Short-circuits at the first emitted element. O(1) intermediate space.
     *
     * @param mixed $defaultValueIfNotFound Value returned when empty.
     * @return mixed The first element or the default.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Determines whether the pipeline has no elements.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P_first) per call. Short-circuits at the first emitted element. O(1) intermediate space.
     *
     * @return bool True if the pipeline is empty.
     */
    public function isEmpty(): bool;

    /**
     * Returns the last element, or a default if empty.
     *
     * Eager: amortized O(P) on first terminal call. O(1) on subsequent calls (cached).
     *        O(n) cached space.
     * Lazy: O(P) per call. Must reach the end of the pipeline. O(1) intermediate space.
     *
     * @param mixed $defaultValueIfNotFound Value returned when empty.
     * @return mixed The last element or the default.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

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
     * Executes all accumulated stages and yields the resulting elements.
     *
     * Eager: amortized O(P) on first terminal call. O(n) on subsequent calls (over cached result).
     *        O(n) cached space.
     * Lazy: O(P) per iteration. O(1) intermediate space.
     *
     * @return Generator A generator producing the processed elements.
     */
    public function process(): Generator;
}
