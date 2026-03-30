<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

final readonly class Last
{
    public static function from(iterable $elements, mixed $defaultValueIfNotFound = null): mixed
    {
        $last = $defaultValueIfNotFound;

        foreach ($elements as $element) {
            $last = $element;
        }

        return $last;
    }
}
