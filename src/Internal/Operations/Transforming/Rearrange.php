<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transforming;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\Operation;
use TinyBlocks\Collection\Order;

final readonly class Rearrange implements Operation
{
    private function __construct(private Order $order, private ?Closure $comparator)
    {
    }

    public static function by(Order $order, ?Closure $comparator = null): Rearrange
    {
        return new Rearrange(order: $order, comparator: $comparator);
    }

    public function apply(iterable $elements): Generator
    {
        $materialized = is_array($elements) ? $elements : iterator_to_array($elements, true);

        $comparator = $this->comparator
            ?? static fn(mixed $first, mixed $second): int => $first <=> $second;

        match ($this->order) {
            Order::ASCENDING_KEY    => ksort($materialized),
            Order::DESCENDING_KEY => krsort($materialized),
            Order::ASCENDING_VALUE  => uasort($materialized, $comparator),
            Order::DESCENDING_VALUE => uasort(
                $materialized,
                static fn(mixed $first, mixed $second): int => $comparator($second, $first)
            ),
        };

        yield from $materialized;
    }
}
