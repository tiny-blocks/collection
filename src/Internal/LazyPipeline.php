<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class LazyPipeline implements Pipeline
{
    /** @var Operation[] */
    private array $stages;

    private function __construct(private iterable|Closure $source, array $stages = [])
    {
        $this->stages = $stages;
    }

    public static function from(iterable $source): LazyPipeline
    {
        return new LazyPipeline(source: $source);
    }

    public static function fromClosure(Closure $factory): LazyPipeline
    {
        return new LazyPipeline(source: $factory);
    }

    public function pipe(Operation $operation): Pipeline
    {
        $stages = $this->stages;
        $stages[] = $operation;

        return new LazyPipeline(source: $this->source, stages: $stages);
    }

    public function count(): int
    {
        return iterator_count($this->process());
    }

    public function getBy(int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        foreach ($this->process() as $currentIndex => $value) {
            if ($currentIndex === $index) {
                return $value;
            }
        }

        return $defaultValueIfNotFound;
    }

    public function process(): Generator
    {
        $elements = $this->source instanceof Closure
            ? ($this->source)()
            : $this->source;

        foreach ($this->stages as $stage) {
            $elements = $stage->apply(elements: $elements);
        }

        yield from $elements;
    }
}
