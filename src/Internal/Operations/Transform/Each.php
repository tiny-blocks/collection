<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use Closure;
use Generator;
use TinyBlocks\Collection\Internal\Operations\ApplicableOperation;

final class Each implements ApplicableOperation
{
    private array $actions;

    private function __construct(Closure ...$actions)
    {
        $this->actions = $actions;
    }

    public static function from(Closure ...$actions): Each
    {
        return new Each(...$actions);
    }

    public function apply(iterable $elements): Generator
    {
        foreach ($elements as $key => $value) {
            foreach ($this->actions as $action) {
                $action($value, $key, $elements);
            }

            yield $key => $value;
        }
    }
}
