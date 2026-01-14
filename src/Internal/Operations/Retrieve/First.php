<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class First implements ImmediateOperation
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
        if (is_array($this->elements)) {
            return array_first($this->elements) ?? $defaultValueIfNotFound;
        }

        foreach ($this->elements as $element) {
            return $element;
        }

        return $defaultValueIfNotFound;
    }
}
