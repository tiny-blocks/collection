<?php

declare(strict_types=1);

namespace TinyBlocks\Collection\Internal\Operations\Transform;

use Closure;
use TinyBlocks\Collection\Internal\Operations\ImmediateOperation;

final readonly class Each implements ImmediateOperation
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

    public function execute(iterable $elements): void
    {
        $runActions = static function ($actions) use ($elements): void {
            foreach ($elements as $key => $value) {
                foreach ($actions as $action) {
                    $action($value, $key);
                }
            }
        };

        $runActions($this->actions);
    }
}
