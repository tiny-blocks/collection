<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class EagerPipeline implements Pipeline
{
    private function __construct(private array $elements)
    {
    }

    public static function from(iterable $source): EagerPipeline
    {
        $elements = is_array($source) ? $source : iterator_to_array($source);

        return new EagerPipeline(elements: $elements);
    }

    public function pipe(Operation $operation): Pipeline
    {
        $elements = iterator_to_array($operation->apply(elements: $this->elements));

        return new EagerPipeline(elements: $elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        return array_key_exists($index, $this->elements)
            ? $this->elements[$index]
            : $defaultValueIfNotFound;
    }

    public function process(): Generator
    {
        yield from $this->elements;
    }
}
