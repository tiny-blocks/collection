<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations;

use Generator;

/**
 * Represents a single processing stage in a collection pipeline.
 *
 * Each operation encapsulates a discrete transformation that receives
 * a stream of elements and produces a new stream. Operations are
 * designed to be composed sequentially, forming a pipeline where
 * the output of one stage feeds into the next.
 *
 * Implementations must be stateless relative to the input stream,
 * meaning they should not retain references to previously seen elements
 * unless the nature of the operation requires it (e.g., sorting).
 *
 * @template TKey of int|string
 * @template TValue
 */
interface Operation
{
    /**
     * Processes the given elements and yields the resulting stream.
     *
     * The operation consumes elements on demand from the input iterable
     * and produces transformed elements through a generator, preserving
     * lazy evaluation semantics across the pipeline.
     *
     * @param iterable<TKey, TValue> $elements The input stream of elements to process.
     * @return Generator<TKey, TValue> A generator yielding the processed elements.
     */
    public function apply(iterable $elements): Generator;
}
