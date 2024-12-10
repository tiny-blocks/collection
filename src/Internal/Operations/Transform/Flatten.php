<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class Flatten implements LazyOperation
{
    public static function instance(): Flatten
    {
        return new Flatten();
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $element) {
            if (is_iterable($element)) {
                foreach ($element as $nestedElement) {
                    yield $nestedElement;
                }

                continue;
            }

            yield $element;
        }
    }
}
