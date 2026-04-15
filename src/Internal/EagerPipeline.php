<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final class EagerPipeline implements Pipeline
{
    private ?array $materialized = null;

    /** @var Operation[] */
    private readonly array $stages;

    private function __construct(private readonly iterable $source, array $stages = [])
    {
        $this->stages = $stages;
    }

    public static function from(iterable $source): EagerPipeline
    {
        return new EagerPipeline(source: $source);
    }

    public function pipe(Operation $operation): Pipeline
    {
        $stages = $this->stages;
        $stages[] = $operation;

        return new EagerPipeline(source: $this->source, stages: $stages);
    }

    public function count(): int
    {
        return count($this->elements());
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->elements();

        return array_key_exists($index, $elements)
            ? $elements[$index]
            : $defaultValueIfNotFound;
    }

    public function process(): Generator
    {
        yield from $this->elements();
    }

    private function elements(): array
    {
        if (!is_null($this->materialized)) {
            return $this->materialized;
        }

        $elements = $this->source;

        foreach ($this->stages as $stage) {
            $elements = $stage->apply(elements: $elements);
        }

        $this->materialized = is_array($elements) ? $elements : iterator_to_array($elements);

        return $this->materialized;
    }
}
