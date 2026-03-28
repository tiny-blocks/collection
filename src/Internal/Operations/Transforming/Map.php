<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class Map implements Operation
{
    private array $transformations;

    private function __construct(Closure ...$transformations)
    {
        $this->transformations = $transformations;
    }

    public static function using(Closure ...$transformations): Map
    {
        return new Map(...$transformations);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $key => $value) {
            foreach ($this->transformations as $transformation) {
                $value = $transformation($value, $key);
            }

            yield $key => $value;
        }
    }
}
