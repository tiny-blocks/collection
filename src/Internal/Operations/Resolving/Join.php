<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

final readonly class Join
{
    public static function elements(iterable $elements, string $separator): string
    {
        $parts = [];

        foreach ($elements as $element) {
            $parts[] = $element;
        }

        return implode($separator, $parts);
    }
}
