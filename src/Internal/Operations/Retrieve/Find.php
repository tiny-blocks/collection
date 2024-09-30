<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Retrieve;

use Closure;
use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class Find implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): Find
    {
        return new Find(elements: $elements);
    }

    public function firstMatchingElement(Closure ...$predicates): mixed
    {
        foreach ($this->elements as $element) {
            foreach ($predicates as $predicate) {
                if ($predicate($element)) {
                    return $element;
                }
            }
        }

        return null;
    }
}
