<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use TinyBlocks\Collection\Internal\Operations\NonApplicableOperation;

final readonly class Get implements NonApplicableOperation
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
        if ($index < 0) {
            return $defaultValueIfNotFound;
        }

        $currentIndex = 0;

        foreach ($this->elements as $value) {
            if ($currentIndex === $index) {
                return $value;
            }

            $currentIndex++;
        }

        return $defaultValueIfNotFound;
    }
}
