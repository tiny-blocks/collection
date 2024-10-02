<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Compare;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class Contains implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): Contains
    {
        return new Contains(elements: $elements);
    }

    public function exists(mixed $element): bool
    {
        $equals = Equals::build();

        foreach ($this->elements as $current) {
            if ($equals->compareWith(element: $current, otherElement: $element)) {
                return true;
            }
        }

        return false;
    }
}
