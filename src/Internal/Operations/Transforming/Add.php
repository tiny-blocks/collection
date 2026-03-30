<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class Add implements Operation
{
    private function __construct(private array $newElements)
    {
    }

    public static function these(array $newElements): Add
    {
        return new Add(newElements: $newElements);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $value) {
            yield $value;
        }

        foreach ($this->newElements as $value) {
            yield $value;
        }
    }
}
