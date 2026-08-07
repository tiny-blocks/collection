<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class FlatMap implements Operation
{
    public static function oneLevel(): FlatMap
    {
        return new FlatMap();
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $element) {
            foreach ($this->expand(element: $element) as $nested) {
                yield $nested;
            }
        }
    }

    private function expand(mixed $element): iterable
    {
        return is_iterable($element) ? $element : [$element];
    }
}
