<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final class EagerPipeline implements Pipeline
{
    private ?array $cache = null;

    private function __construct(
        private readonly array $source,
        private readonly array $stages = []
    ) {
    }

    public static function from(iterable $source): EagerPipeline
    {
        $elements = is_array($source) ? $source : iterator_to_array($source);

        return new EagerPipeline(source: $elements);
    }

    public static function fromClosure(Closure $factory): EagerPipeline
    {
        $elements = iterator_to_array($factory());

        return new EagerPipeline(source: $elements);
    }

    public function pipe(Operation $operation): Pipeline
    {
        $stages = $this->stages;
        $stages[] = $operation;

        return new EagerPipeline(source: $this->source, stages: $stages);
    }

    public function count(): int
    {
        return count($this->materialize());
    }

    public function isEmpty(): bool
    {
        return $this->materialize() === [];
    }

    public function first(mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->materialize();

        return $elements === []
            ? $defaultValueIfNotFound
            : $elements[array_key_first($elements)];
    }

    public function last(mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->materialize();

        return $elements === []
            ? $defaultValueIfNotFound
            : $elements[array_key_last($elements)];
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        $elements = $this->materialize();

        return array_key_exists($index, $elements)
            ? $elements[$index]
            : $defaultValueIfNotFound;
    }

    public function process(): Generator
    {
        yield from $this->materialize();
    }

    private function materialize(): array
    {
        if (is_null($this->cache)) {
            $elements = $this->source;
            foreach ($this->stages as $stage) {
                $elements = $stage->apply(elements: $elements);
            }
            $this->cache = is_array($elements) ? $elements : iterator_to_array($elements);
        }

        return $this->cache;
    }
}
