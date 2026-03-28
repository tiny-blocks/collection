<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

final readonly class Get
{
    public static function byIndex(iterable $elements, int $index, mixed $defaultValueIfNotFound = null): mixed
    {
        foreach ($elements as $currentIndex => $value) {
            if ($currentIndex === $index) {
                return $value;
            }
        }

        return $defaultValueIfNotFound;
    }
}
