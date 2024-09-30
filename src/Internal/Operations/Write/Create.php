<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Write;

use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final class Create implements LazyOperation
{
    public static function fromEmpty(): Create
    {
        return new Create();
    }

    public function apply(iterable $elements): Generator
    {
        yield from $elements;
    }
}
