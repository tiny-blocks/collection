<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Write;

use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;
use TinyBlocks\Collection\Internal\Operations\Compare\Equals;

final readonly class Remove implements ApplicableOperation
{
    private function __construct(private mixed $element)
    {
    }

    public static function from(mixed $element): Remove
    {
        return new Remove(element: $element);
    }

    public function apply(iterable $elements): Generator
    {
        $equals = Equals::build();

        foreach ($elements as $element) {
            if (!$equals->compareWith(element: $this->element, otherElement: $element)) {
                yield $element;
            }
        }
    }
}
