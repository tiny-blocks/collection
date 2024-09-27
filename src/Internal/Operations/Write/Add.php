<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Write;

use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

final readonly class Add implements ApplicableOperation
{
    private function __construct(private iterable $newElements)
    {
    }

    public static function from(iterable $newElements): Add
    {
        return new Add(newElements: $newElements);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $element) {
            yield $element;
        }

        foreach ($this->newElements as $element) {
            yield $element;
        }
    }
}
