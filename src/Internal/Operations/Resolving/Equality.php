<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

use Iterator;
use TinyBlocks\Collection\Collectible;

final class Equality
{
    private function __construct()
    {
    }

    /**
     * @param iterable<int|string, mixed> $elements
     */
    public static function exists(mixed $element, iterable $elements): bool
    {
        foreach ($elements as $current) {
            if (Equality::areSame(other: $element, element: $current)) {
                return true;
            }
        }

        return false;
    }

    public static function areSame(mixed $other, mixed $element): bool
    {
        if (is_object($element) !== is_object($other)) {
            return false;
        }

        return is_object($element)
            ? $element == $other
            : $element === $other;
    }

    /**
     * @param Collectible<mixed> $other
     * @param Iterator<int|string, mixed> $elements
     */
    public static function compareAll(Collectible $other, Iterator $elements): bool
    {
        foreach ($other as $value) {
            if (!$elements->valid() || !Equality::areSame(other: $value, element: $elements->current())) {
                return false;
            }

            $elements->next();
        }

        return !$elements->valid();
    }
}
