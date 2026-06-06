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

    public static function areSame(mixed $element, mixed $other): bool
    {
        if (is_object($element) !== is_object($other)) {
            return false;
        }

        return is_object($element)
            ? $element == $other
            : $element === $other;
    }

    /**
     * @param iterable<int|string, mixed> $elements
     */
    public static function exists(iterable $elements, mixed $element): bool
    {
        foreach ($elements as $current) {
            if (Equality::areSame(element: $current, other: $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Iterator<int|string, mixed> $elements
     * @param Collectible<mixed> $other
     */
    public static function compareAll(Iterator $elements, Collectible $other): bool
    {
        foreach ($other as $value) {
            if (!$elements->valid() || !Equality::areSame(element: $elements->current(), other: $value)) {
                return false;
            }

            $elements->next();
        }

        return !$elements->valid();
    }
}
