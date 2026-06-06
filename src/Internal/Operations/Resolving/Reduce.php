<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

use Closure;

final class Reduce
{
    private function __construct()
    {
    }

    public static function from(iterable $elements, Closure $accumulator, mixed $initial): mixed
    {
        $carry = $initial;

        foreach ($elements as $element) {
            $carry = $accumulator($carry, $element);
        }

        return $carry;
    }
}
