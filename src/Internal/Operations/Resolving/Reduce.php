<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

use Closure;

final class Reduce
{
    private function __construct()
    {
    }

    public static function from(mixed $initial, iterable $elements, Closure $accumulator): mixed
    {
        $carry = $initial;

        foreach ($elements as $element) {
            $carry = $accumulator($carry, $element);
        }

        return $carry;
    }
}
