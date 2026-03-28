<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class Merge implements Operation
{
    private function __construct(private iterable $other)
    {
    }

    public static function with(iterable $other): Merge
    {
        return new Merge(other: $other);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $value) {
            yield $value;
        }

        foreach ($this->other as $value) {
            yield $value;
        }
    }
}
