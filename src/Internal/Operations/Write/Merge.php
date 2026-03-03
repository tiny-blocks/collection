<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Write;

use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class Merge implements LazyOperation
{
    private function __construct(private iterable $otherElements)
    {
    }

    public static function from(iterable $otherElements): Merge
    {
        return new Merge(otherElements: $otherElements);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $element) {
            yield $element;
        }

        foreach ($this->otherElements as $element) {
            yield $element;
        }
    }
}
