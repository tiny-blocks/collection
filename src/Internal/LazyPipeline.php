<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

/**
 * Generator-based pipeline with deferred evaluation.
 *
 * Operations are accumulated as stages and executed only when
 * the pipeline is consumed. Ideal for large or unbounded datasets.
 */
final readonly class LazyPipeline implements Pipeline
{
    /** @var Operation[] */
    private array $stages;

    /**
     * @param Operation[] $stages
     */
    private function __construct(private iterable $source, array $stages = [])
    {
        $this->stages = $stages;
    }

    public static function from(iterable $source): LazyPipeline
    {
        return new LazyPipeline(source: $source);
    }

    public function pipe(Operation $operation): Pipeline
    {
        $stages = $this->stages;
        $stages[] = $operation;

        return new LazyPipeline(source: $this->source, stages: $stages);
    }

    public function process(): Generator
    {
        $elements = $this->source;

        foreach ($this->stages as $stage) {
            $elements = $stage->apply(elements: $elements);
        }

        yield from $elements;
    }
}
