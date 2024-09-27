<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Write;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

final readonly class RemoveAll implements ApplicableOperation
{
    private function __construct(private ?Closure $filter)
    {
    }

    public static function from(?Closure $filter = null): RemoveAll
    {
        return new RemoveAll(filter: $filter);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $element) {
            if ($this->filter === null || ($this->filter)($element)) {
                continue;
            }

            yield $element;
        }
    }
}
