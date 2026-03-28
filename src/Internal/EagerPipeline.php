<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

/**
 * Array-backed pipeline with immediate evaluation.
 *
 * Each operation materializes results into an array immediately,
 * enabling constant-time access, count, and repeated iteration.
 * Ideal for small to medium datasets and random access scenarios.
 */
final readonly class EagerPipeline implements Pipeline
{
    private function __construct(private array $elements)
    {
    }

    public static function from(iterable $source): EagerPipeline
    {
        $elements = is_array($source) ? $source : iterator_to_array(iterator: $source);

        return new EagerPipeline(elements: $elements);
    }

    public function pipe(Operation $operation): Pipeline
    {
        $elements = iterator_to_array(iterator: $operation->apply(elements: $this->elements));

        return new EagerPipeline(elements: $elements);
    }

    public function process(): Generator
    {
        yield from $this->elements;
    }
}
