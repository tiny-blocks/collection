<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

final class Each
{
    private function __construct()
    {
    }

    public static function on(iterable $elements, array $actions): void
    {
        foreach ($elements as $key => $value) {
            foreach ($actions as $action) {
                $action($value, $key);
            }
        }
    }
}
