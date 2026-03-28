<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

use Generator;
use TinyBlocks\Collection\Collectible;

final readonly class Equality
{
    public static function areSame(mixed $element, mixed $other): bool
    {
        if (is_object($element) !== is_object($other)) {
            return false;
        }

        return is_object($element)
            ? $element == $other
            : $element === $other;
    }

    public static function exists(iterable $elements, mixed $element): bool
    {
        foreach ($elements as $current) {
            if (Equality::areSame(element: $current, other: $element)) {
                return true;
            }
        }

        return false;
    }

    public static function compareAll(iterable $elements, Collectible $other): bool
    {
        $iteratorA = Equality::toGenerator(iterable: $elements);
        $iteratorB = Equality::toGenerator(iterable: $other);

        while ($iteratorA->valid() && $iteratorB->valid()) {
            if (!Equality::areSame(element: $iteratorA->current(), other: $iteratorB->current())) {
                return false;
            }

            $iteratorA->next();
            $iteratorB->next();
        }

        return !$iteratorA->valid() && !$iteratorB->valid();
    }

    private static function toGenerator(iterable $iterable): Generator
    {
        yield from $iterable;
    }
}
