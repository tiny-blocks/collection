<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Order;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

final readonly class Sort implements ApplicableOperation
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
        $temporaryElements = iterator_to_array($elements);

        $predicate = is_null($this->predicate)
            ? fn(mixed $first, mixed $second): int => $first <=> $second
            : fn($first, $second): int => ($this->predicate)($first, $second);

        $reversedPredicate = fn(mixed $first, mixed $second): int => -$predicate($first, $second);

        match ($this->order) {
            Order::ASCENDING_KEY    => ksort($temporaryElements),
            Order::DESCENDING_KEY   => krsort($temporaryElements),
            Order::ASCENDING_VALUE  => uasort($temporaryElements, $predicate),
            Order::DESCENDING_VALUE => uasort($temporaryElements, $reversedPredicate)
        };

        foreach ($temporaryElements as $key => $value) {
            yield $key => $value;
        }
    }
}
