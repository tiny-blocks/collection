<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class Last implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): Last
    {
        return new Last(elements: $elements);
    }

    public function element(mixed $defaultValueIfNotFound = null): mixed
    {
        if (is_array($this->elements)) {
            return array_last($this->elements) ?? $defaultValueIfNotFound;
        }

        $lastElement = $defaultValueIfNotFound;

        foreach ($this->elements as $element) {
            $lastElement = $element;
        }

        return $lastElement;
    }
}
