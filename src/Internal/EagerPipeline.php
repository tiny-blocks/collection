<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class EagerPipeline implements Pipeline
{
    private function __construct(private Materialization $materialization)
    {
    }

    public static function from(iterable $source): EagerPipeline
    {
        $elements = is_array($source) ? $source : iterator_to_array($source);

        return new EagerPipeline(materialization: Materialization::from(source: $elements, stages: []));
    }

    public static function fromClosure(Closure $factory): EagerPipeline
    {
        $elements = iterator_to_array($factory());

        return new EagerPipeline(materialization: Materialization::from(source: $elements, stages: []));
    }

    public function pipe(Operation $operation): Pipeline
    {
        $elements = $this->materialization->elements();

        return new EagerPipeline(materialization: Materialization::from(source: $elements, stages: [$operation]));
    }

    public function count(): int
    {
        return count($this->materialization->elements());
    }

    public function isEmpty(): bool
    {
        return $this->materialization->elements() === [];
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->materialization->elements();

        return $elements === []
            ? $defaultValueIfNotFound
            : $elements[array_key_first($elements)];
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->materialization->elements();

        return $elements === []
            ? $defaultValueIfNotFound
            : $elements[array_key_last($elements)];
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->materialization->elements();

        return array_key_exists($index, $elements)
            ? $elements[$index]
            : $defaultValueIfNotFound;
    }

    public function process(): Generator
    {
        yield from $this->materialization->elements();
    }
}
