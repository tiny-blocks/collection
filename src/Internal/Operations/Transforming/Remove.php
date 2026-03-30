<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;
use TinyBlocks\Collection\Internal\Operations\Resolving\Equality;

final readonly class Remove implements Operation
{
    private function __construct(private mixed $element)
    {
    }

    public static function element(mixed $element): Remove
    {
        return new Remove(element: $element);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $key => $value) {
            if (!Equality::areSame(element: $this->element, other: $value)) {
                yield $key => $value;
            }
        }
    }
}
