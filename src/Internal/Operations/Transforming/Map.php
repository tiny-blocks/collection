<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class Map implements Operation
{
    private Closure $compiledTransformation;

    private function __construct(Closure ...$transformations)
    {
        $first = array_shift($transformations);

        if (is_null($first)) {
            $this->compiledTransformation = static fn(mixed $value, mixed $key): mixed => $value;

            return;
        }

        $this->compiledTransformation = array_reduce(
            $transformations,
            static fn(Closure $carry, Closure $transformation): Closure
                => static fn(mixed $value, mixed $key): mixed => $transformation($carry($value, $key), $key),
            $first
        );
    }

    public static function using(Closure ...$transformations): Map
    {
        return new Map(...$transformations);
    }

    public function apply(iterable $elements): Generator
    {
        $transformation = $this->compiledTransformation;

        foreach ($elements as $key => $value) {
            yield $key => $transformation($value, $key);
        }
    }
}
