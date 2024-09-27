<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Write;

use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

final class Create implements ApplicableOperation
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
