<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;

final readonly class RemoveAll implements Operation
{
    private function __construct(private ?Closure $predicate)
    {
    }

    public static function matching(?Closure $predicate = null): RemoveAll
    {
        return new RemoveAll(predicate: $predicate);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $key => $value) {
            if ($this->predicate === null || ($this->predicate)($value)) {
                continue;
            }

            yield $key => $value;
        }
    }
}
