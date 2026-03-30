<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

use Closure;

final readonly class Find
{
    public static function firstMatch(iterable $elements, array $predicates): mixed
    {
        foreach ($elements as $element) {
            if (array_any($predicates, static fn(Closure $predicate): bool => $predicate($element))) {
                return $element;
            }
        }

        return null;
    }
}
