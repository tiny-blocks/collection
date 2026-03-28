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
     * Executes all accumulated stages and yields the resulting elements.
     *
     * @return Generator A generator producing the processed elements.
     */
    public function process(): Generator;
}
