<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Order;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\LazyOperation;

final readonly class Sort implements LazyOperation
{
    private function __construct(private Order $order, private ?Closure $predicate)
    {
    }

    public static function from(Order $order, ?Closure $predicate = null): Sort
    {
        return new Sort(order: $order, predicate: $predicate);
    }

    public function apply(iterable $elements): Generator
    {
        $temporaryElements = [];

        foreach ($elements as $key => $value) {
            $temporaryElements[$key] = $value;
        }

        $predicate = is_null($this->predicate)
            ? static fn(mixed $first, mixed $second): int => $first <=> $second
            : $this->predicate;

        $ascendingPredicate = static fn(mixed $first, mixed $second): int => $predicate($first, $second);
        $descendingPredicate = is_null($this->predicate)
            ? static fn(mixed $first, mixed $second): int => $predicate($second, $first)
            : $predicate;

        match ($this->order) {
            Order::ASCENDING_KEY    => ksort($temporaryElements),
            Order::DESCENDING_KEY   => krsort($temporaryElements),
            Order::ASCENDING_VALUE  => uasort($temporaryElements, $ascendingPredicate),
            Order::DESCENDING_VALUE => uasort($temporaryElements, $descendingPredicate)
        };

        foreach ($temporaryElements as $key => $value) {
            yield $key => $value;
        }
    }
}
