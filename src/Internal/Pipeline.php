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
 */
interface Pipeline
{
    /**
     * Adds a new operation stage to the pipeline.
     *
     * Returns a new pipeline instance containing all previous stages
     * plus the given operation, preserving immutability.
     *
     * @param Operation $operation The operation to append as the next stage.
     * @return Pipeline A new pipeline with the added stage.
     */
    public function pipe(Operation $operation): Pipeline;

    /**
     * Returns the total number of elements in the pipeline.
     *
     * Eager: O(1) time, O(1) space. Direct array count.
     * Lazy: O(n) time, O(1) space. Must iterate all elements.
     *
     * @return int The element count.
     */
    public function count(): int;

    /**
     * Returns the first element, or a default if empty.
     *
     * Eager: O(1) time, O(1) space. Direct array access via array_key_first.
     * Lazy: O(1) time, O(1) space. Yields once from the pipeline.
     *
     * @param mixed $defaultValueIfNotFound Value returned when empty.
     * @return mixed The first element or the default.
     */
    public function first(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Determines whether the pipeline has no elements.
     *
     * Eager: O(1) time, O(1) space. Checks if the array is empty.
     * Lazy: O(1) time, O(1) space. Checks if the generator produces a value.
     *
     * @return bool True if the pipeline is empty.
     */
    public function isEmpty(): bool;

    /**
     * Returns the last element, or a default if empty.
     *
     * Eager: O(1) time, O(1) space. Direct array access via array_key_last.
     * Lazy: O(n) time, O(1) space. Must iterate all elements.
     *
     * @param mixed $defaultValueIfNotFound Value returned when empty.
     * @return mixed The last element or the default.
     */
    public function last(mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Returns the element at the given zero-based index.
     *
     * Eager: O(1) time, O(1) space. Direct array access via array_key_exists.
     * Lazy: O(n) time, O(1) space. Must iterate up to the index.
     *
     * @param int $index The zero-based position.
     * @param mixed $defaultValueIfNotFound Value returned when the index is out of bounds.
     * @return mixed The element at the index or the default.
     */
    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed;

    /**
     * Executes all accumulated stages and yields the resulting elements.
     *
     * @return Generator A generator producing the processed elements.
     */
    public function process(): Generator;
}
