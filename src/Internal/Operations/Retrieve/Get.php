<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class Get implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): Get
    {
        return new Get(elements: $elements);
    }

    public function elementAtIndex(int $index, mixed $defaultValueIfNotFound): mixed
    {
        foreach ($this->elements as $currentIndex => $value) {
            if ($currentIndex === $index) {
                return $value;
            }
        }

        return $defaultValueIfNotFound;
    }
}
