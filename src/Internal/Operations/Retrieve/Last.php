<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use TinyBlocks\Collection\Internal\Operations\NonApplicableOperation;

final readonly class Last implements NonApplicableOperation
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
        $lastElement = $defaultValueIfNotFound;

        foreach ($this->elements as $element) {
            $lastElement = $element;
        }

        return $lastElement;
    }
}
