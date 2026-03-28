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
            if (is_iterable($element)) {
                foreach ($element as $nested) {
                    yield $nested;
                }

                continue;
            }

            yield $element;
        }
    }
}
