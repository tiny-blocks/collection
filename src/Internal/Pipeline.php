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
     * Eager pipelines provide this in O(1), lazy pipelines must iterate.
     *
     * @return int The element count.
     */
    public function count(): int;

    /**
     * Returns the element at the given zero-based index.
     *
     * Eager pipelines provide this in O(1), lazy pipelines must iterate.
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
