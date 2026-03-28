<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Resolving;

final readonly class Each
{
    public static function execute(iterable $elements, array $actions): void
    {
        foreach ($elements as $key => $value) {
            foreach ($actions as $action) {
                $action($value, $key);
            }
        }
    }
}
