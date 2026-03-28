<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

final readonly class First
{
    public static function from(iterable $elements, mixed $defaultValueIfNotFound = null): mixed
    {
        foreach ($elements as $element) {
            return $element;
        }

        return $defaultValueIfNotFound;
    }

    public static function isAbsent(iterable $elements): bool
    {
        foreach ($elements as $ignored) {
            return false;
        }

        return true;
    }
}
