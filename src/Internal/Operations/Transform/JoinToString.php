<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class JoinToString implements ImmediateOperation
{
    private function __construct(private iterable $elements)
    {
    }

    public static function from(iterable $elements): JoinToString
    {
        return new JoinToString(elements: $elements);
    }

    public function joinTo(string $separator): string
    {
        return implode($separator, iterator_to_array($this->elements));
    }
}
