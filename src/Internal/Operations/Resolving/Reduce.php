<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

use Closure;

final readonly class Reduce
{
    public static function from(iterable $elements, Closure $accumulator, mixed $initial): mixed
    {
        $carry = $initial;

        foreach ($elements as $element) {
            $carry = $accumulator($carry, $element);
        }

        return $carry;
    }
}
