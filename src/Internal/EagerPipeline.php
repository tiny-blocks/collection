<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

/**
 * @template TValue
 * @implements Pipeline<TValue>
 */
final readonly class EagerPipeline implements Pipeline
{
    /**
     * @param Materialization<TValue> $materialization
     */
    private function __construct(private Materialization $materialization)
    {
    }

    /**
     * @template TElement
     * @param iterable<TElement> $source
     * @return EagerPipeline<TElement>
     */
    public static function from(iterable $source): EagerPipeline
    {
        $elements = is_array($source) ? $source : iterator_to_array($source);

        return new EagerPipeline(materialization: Materialization::from(source: $elements, stages: []));
    }

    /**
     * @template TElement
     * @param Closure(): iterable<TElement> $factory
     * @return EagerPipeline<TElement>
     */
    public static function fromClosure(Closure $factory): EagerPipeline
    {
        $result = $factory();
        $elements = is_array($result) ? $result : iterator_to_array($result);

        return new EagerPipeline(materialization: Materialization::from(source: $elements, stages: []));
    }

    public function pipe(Operation $operation): Pipeline
    {
        return new EagerPipeline(materialization: $this->materialization->withStage(operation: $operation));
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
        /** @var array<int, TValue> $elements */
        $elements = $this->materialization->elements();

        yield from $elements;
    }
}
