<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use TinyBlocks\Collection\Internal\Operations\NonApplicableOperation;

final readonly class First implements NonApplicableOperation
{
    public function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): First
    {
        return new First(elements: $elements);
    }

    public function element(mixed $defaultValueIfNotFound = null): mixed
    {
        foreach ($this->elements as $element) {
            return $element;
        }

        return $defaultValueIfNotFound;
    }
}
